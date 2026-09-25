<?php
/**
 * DNS Migrator
 *
 * DomainMonger PHP 8.3 / WHMCS 9 rebuild.
 * Migrates DNS records between Register DNS, DNSPlus, and cPanel.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use DomainMonger\DnsMigrator\Engine;
use DomainMonger\DnsMigrator\MigrationException;
use WHMCS\Database\Capsule;

require_once __DIR__ . '/lib/DnsMigrationEngine.php';

function dnsmigrator_config(): array
{
    return [
        'name' => 'DNS Migrator',
        'description' => 'Migrate DNS records between RegistrarDNS, DNSPlus, and cPanel.',
        'version' => '3.0',
        'author' => 'DomainMonger',
        'language' => 'english',
        'fields' => [
            'neo_url' => [
                'FriendlyName' => 'RegistrarDNS API URL',
                'Type' => 'text',
                'Size' => '45',
                'Default' => 'https://httpapi.com/api',
                'Description' => 'Use the production or test LogicBoxes/ResellerClub HTTP API URL.',
            ],
            'neo_username' => [
                'FriendlyName' => 'RegistrarDNS Reseller ID',
                'Type' => 'text',
                'Size' => '25',
            ],
            'neo_key' => [
                'FriendlyName' => 'RegistrarDNS API Key',
                'Type' => 'password',
                'Size' => '45',
            ],
        ],
    ];
}

function dnsmigrator_activate(): array
{
    dnsmigrator_cleanup_legacy_files();
    dnsmigrator_cleanup_legacy_settings();

    return [
        'status' => 'success',
        'description' => 'DNS Migrator is ready for RegistrarDNS, DNSPlus, and cPanel DNS record migrations.',
    ];
}

function dnsmigrator_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'DNS Migrator has been deactivated.',
    ];
}

function dnsmigrator_upgrade(array $vars): void
{
    $installed = (string) ($vars['version'] ?? '0');
    if (version_compare($installed, '2.0', '<')) {
        dnsmigrator_cleanup_legacy_files();
        dnsmigrator_cleanup_legacy_settings();
    }
}

function dnsmigrator_cleanup_legacy_settings(): void
{
    try {
        Capsule::table('tbladdonmodules')
            ->where('module', 'dnsmigrator')
            ->whereIn('setting', [
                'dnsp_url',
                'dnsp_username',
                'dnsp_password',
                'dnsp_dusername',
                'dnsp_dns',
                'opensrs_dns',
                'neo_dns',
                'cpanel_dns',
                'cloudns_dns',
            ])
            ->delete();
    } catch (Throwable $e) {
        // Obsolete settings are harmless if cleanup cannot run.
    }
}

function dnsmigrator_cleanup_legacy_files(): array
{
    $targets = [
        __DIR__ . '/libs',
        __DIR__ . '/readme.txt',
        __DIR__ . '/.ftpquota',
        __DIR__ . '/.DS_Store',
    ];

    $failed = [];
    foreach ($targets as $target) {
        if (!file_exists($target)) {
            continue;
        }
        if (!dnsmigrator_remove_path($target)) {
            $failed[] = basename($target);
        }
    }

    return $failed;
}

function dnsmigrator_remove_path(string $path): bool
{
    if (is_link($path) || is_file($path)) {
        return @unlink($path);
    }

    if (!is_dir($path)) {
        return true;
    }

    $items = @scandir($path);
    if (!is_array($items)) {
        return false;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        if (!dnsmigrator_remove_path($path . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return @rmdir($path);
}

function dnsmigrator_output(array $vars): void
{
    $cleanupFailures = dnsmigrator_cleanup_legacy_files();
    $moduleLink = (string) ($vars['modulelink'] ?? 'addonmodules.php?module=dnsmigrator');
    $systems = Engine::systems();
    $step = (string) ($_POST['dns_step'] ?? 'start');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_token('WHMCS.admin.default');
    }

    $rawDomains = isset($_POST['domains'])
        ? (string) $_POST['domains']
        : (string) ($_SESSION['dnsmigrator_last_domains'] ?? '');
    $from = (string) ($_POST['from_system'] ?? 'register');
    $to = (string) ($_POST['to_system'] ?? 'dnsplus');
    $replace = isset($_POST['replace_records']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['domains'])) {
        $_SESSION['dnsmigrator_last_domains'] = $rawDomains;
    }

    echo dnsmigrator_styles();
    echo '<div class="dm-dns-migrator">';
    echo '<div class="dm-dns-title"><div><h2>DNS Migrator</h2>';
    echo '<p>Migrate DNS records between RegistrarDNS, DNSPlus, and cPanel.</p></div></div>';

    if ($cleanupFailures) {
        echo dnsmigrator_alert(
            'warning',
            'The rebuilt addon is active, but these obsolete legacy items could not be deleted automatically: '
            . dnsmigrator_escape(implode(', ', $cleanupFailures))
            . '. They are no longer loaded.'
        );
    }

    try {
        $engine = new Engine($vars, dirname(__DIR__, 3));

        if ($step === 'preview') {
            $domains = dnsmigrator_parse_domains($rawDomains);
            $errors = dnsmigrator_validate_request($domains, $from, $to, $systems);
            if ($errors) {
                foreach ($errors as $error) {
                    echo dnsmigrator_alert('danger', $error);
                }
                echo dnsmigrator_start_form($moduleLink, $rawDomains, $from, $to, $replace, $systems);
            } else {
                $preview = $engine->preview($domains, $from, $to);
                $nonce = bin2hex(random_bytes(18));
                $_SESSION['dnsmigrator_previews'][$nonce] = [
                    'created' => time(),
                    'domains' => $domains,
                    'from' => $from,
                    'to' => $to,
                    'replace' => $replace,
                ];
                echo dnsmigrator_preview_form($moduleLink, $preview, $nonce, $from, $to, $replace, $systems);
            }
        } elseif ($step === 'execute') {
            $nonce = preg_replace('/[^a-f0-9]/i', '', (string) ($_POST['preview_id'] ?? '')) ?? '';
            $stored = $_SESSION['dnsmigrator_previews'][$nonce] ?? null;

            if (!is_array($stored) || (int) ($stored['created'] ?? 0) < time() - 1800) {
                echo dnsmigrator_alert('danger', 'The migration preview expired. Preview the migration again before running it.');
                echo dnsmigrator_start_form($moduleLink, $rawDomains, $from, $to, $replace, $systems);
            } else {
                unset($_SESSION['dnsmigrator_previews'][$nonce]);
                $results = $engine->migrate(
                    (array) $stored['domains'],
                    (string) $stored['from'],
                    (string) $stored['to'],
                    (bool) $stored['replace']
                );
                echo dnsmigrator_results(
                    $moduleLink,
                    $results,
                    (string) $stored['from'],
                    (string) $stored['to'],
                    (bool) $stored['replace'],
                    $systems
                );
            }
        } else {
            echo dnsmigrator_start_form($moduleLink, $rawDomains, $from, $to, $replace, $systems);
        }
    } catch (Throwable $e) {
        echo dnsmigrator_alert('danger', $e->getMessage());
        echo dnsmigrator_start_form($moduleLink, $rawDomains, $from, $to, $replace, $systems);
    }

    echo dnsmigrator_script();
    echo '</div>';
}

function dnsmigrator_parse_domains(string $input): array
{
    $input = str_replace(["\r\n", "\r", ',', ';', "\t"], "\n", $input);
    $parts = preg_split('/\s+/', $input) ?: [];
    $domains = [];

    foreach ($parts as $part) {
        $domain = strtolower(trim((string) $part));
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
        $domain = preg_replace('#[/:].*$#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '.');
        if ($domain !== '') {
            $domains[$domain] = $domain;
        }
    }

    return array_values($domains);
}

function dnsmigrator_validate_request(array $domains, string $from, string $to, array $systems): array
{
    $errors = [];
    if (!$domains) {
        $errors[] = 'Enter at least one domain.';
    }
    if (count($domains) > 100) {
        $errors[] = 'Process no more than 100 domains in one migration.';
    }
    if (!isset($systems[$from]) || !isset($systems[$to])) {
        $errors[] = 'Select supported source and destination DNS systems.';
    }
    if ($from === $to) {
        $errors[] = 'The source and destination DNS systems must be different.';
    }

    foreach ($domains as $domain) {
        if (!preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i',
            $domain
        )) {
            $errors[] = 'Invalid domain: ' . $domain;
        }
    }

    return array_values(array_unique($errors));
}

function dnsmigrator_start_form(
    string $moduleLink,
    string $domains,
    string $from,
    string $to,
    bool $replace,
    array $systems
): string {
    $optionsFrom = dnsmigrator_system_options($systems, $from);
    $optionsTo = dnsmigrator_system_options($systems, $to);

    return '<form method="post" action="' . dnsmigrator_escape($moduleLink) . '" class="dm-dns-card dm-dns-form">'
        . '<input type="hidden" name="token" value="' . dnsmigrator_escape(generate_token('plain')) . '">'
        . '<input type="hidden" name="dns_step" value="preview">'
        . '<div class="dm-dns-card-head">DNS Record Migration</div>'
        . '<div class="dm-dns-card-body">'
        . '<div class="dm-dns-grid">'
        . '<div class="dm-dns-field dm-dns-domains"><label for="dm-dns-domains">Domains</label>'
        . '<textarea id="dm-dns-domains" name="domains" rows="10" placeholder="example.com&#10;example.net">'
        . dnsmigrator_escape($domains) . '</textarea>'
        . '<div class="dm-dns-help">Enter one domain per line. Duplicate domains are removed automatically.</div></div>'
        . '<div class="dm-dns-route">'
        . '<div class="dm-dns-field"><label for="dm-dns-from">From</label><select id="dm-dns-from" name="from_system">'
        . $optionsFrom . '</select></div>'
        . '<div class="dm-dns-arrow" aria-hidden="true">&rarr;</div>'
        . '<div class="dm-dns-field"><label for="dm-dns-to">To</label><select id="dm-dns-to" name="to_system">'
        . $optionsTo . '</select></div>'
        . '<label class="dm-dns-check"><input type="checkbox" name="replace_records" value="1"'
        . ($replace ? ' checked' : '') . '> <span>Replace destination DNS records before copying</span></label>'
        . '<div class="dm-dns-note">SOA records and the destination system’s root nameserver records are always preserved. This addon copies DNS records only and does not change authoritative nameservers.</div>'
        . '</div></div>'
        . '<div class="dm-dns-actions"><button type="submit" class="btn dm-dns-primary" data-dns-submit>'
        . '<span class="dm-dns-spinner" aria-hidden="true"></span><span class="dm-dns-button-text">Preview Migration</span>'
        . '</button></div>'
        . '</div></form>';
}

function dnsmigrator_preview_form(
    string $moduleLink,
    array $preview,
    string $nonce,
    string $from,
    string $to,
    bool $replace,
    array $systems
): string {
    $ready = 0;
    $rows = '';
    foreach ($preview as $domain => $row) {
        $ok = !empty($row['ok']);
        if ($ok && (int) $row['records'] > 0) {
            $ready++;
        }
        $status = $ok ? 'Ready' : 'Failed';
        $badge = $ok ? 'success' : 'danger';
        $types = !empty($row['types']) ? implode(', ', (array) $row['types']) : '—';
        $rows .= '<tr><td><strong>' . dnsmigrator_escape((string) $domain) . '</strong></td>'
            . '<td><span class="dm-dns-badge ' . $badge . '">' . $status . '</span></td>'
            . '<td>' . (int) $row['records'] . '</td>'
            . '<td>' . dnsmigrator_escape($types) . '</td>'
            . '<td>' . (int) $row['skipped'] . '</td>'
            . '<td>' . dnsmigrator_escape((string) $row['message']) . '</td></tr>';
    }

    $route = dnsmigrator_escape($systems[$from] ?? $from) . ' &rarr; '
        . dnsmigrator_escape($systems[$to] ?? $to);

    $html = '<div class="dm-dns-card"><div class="dm-dns-card-head">Migration Preview</div><div class="dm-dns-card-body">'
        . '<div class="dm-dns-summary"><strong>' . $route . '</strong><span>'
        . ($replace ? 'Destination records will be replaced.' : 'Existing destination records will be retained.')
        . '</span></div>'
        . '<div class="dm-dns-table-wrap"><table class="dm-dns-table"><thead><tr>'
        . '<th>Domain</th><th>Status</th><th>Records</th><th>Types</th><th>Skipped</th><th>Details</th>'
        . '</tr></thead><tbody>' . $rows . '</tbody></table></div>';

    if ($ready > 0) {
        $html .= '<form method="post" action="' . dnsmigrator_escape($moduleLink) . '" class="dm-dns-confirm-form">'
            . '<input type="hidden" name="token" value="' . dnsmigrator_escape(generate_token('plain')) . '">'
            . '<input type="hidden" name="dns_step" value="execute">'
            . '<input type="hidden" name="preview_id" value="' . dnsmigrator_escape($nonce) . '">'
            . '<div class="dm-dns-actions">'
            . '<a class="btn dm-dns-secondary" href="' . dnsmigrator_escape($moduleLink) . '">Back</a>'
            . '<button type="submit" class="btn dm-dns-primary" data-dns-submit>'
            . '<span class="dm-dns-spinner" aria-hidden="true"></span><span class="dm-dns-button-text">Run Migration</span>'
            . '</button></div></form>';
    } else {
        $html .= dnsmigrator_alert('danger', 'No domains are ready to migrate.')
            . '<div class="dm-dns-actions"><a class="btn dm-dns-secondary" href="'
            . dnsmigrator_escape($moduleLink) . '">Back</a></div>';
    }

    return $html . '</div></div>';
}

function dnsmigrator_results(
    string $moduleLink,
    array $results,
    string $from,
    string $to,
    bool $replace,
    array $systems
): string {
    $rows = '';
    foreach ($results as $domain => $row) {
        $status = (string) ($row['status'] ?? 'failed');
        $label = $status === 'success' ? 'Success' : ($status === 'partial' ? 'Partial' : 'Failed');
        $badge = $status === 'success' ? 'success' : ($status === 'partial' ? 'warning' : 'danger');
        $details = (array) ($row['details'] ?? []);
        $detailHtml = '—';
        if ($details) {
            $items = '';
            foreach ($details as $detail) {
                $items .= '<li>' . dnsmigrator_escape((string) $detail) . '</li>';
            }
            $detailHtml = '<details><summary>View details</summary><ul>' . $items . '</ul></details>';
        }

        $rows .= '<tr><td><strong>' . dnsmigrator_escape((string) $domain) . '</strong></td>'
            . '<td><span class="dm-dns-badge ' . $badge . '">' . $label . '</span></td>'
            . '<td>' . (int) ($row['added'] ?? 0) . '</td>'
            . '<td>' . (int) ($row['skipped'] ?? 0) . '</td>'
            . '<td>' . (int) ($row['failed'] ?? 0) . '</td>'
            . '<td>' . $detailHtml . '</td></tr>';
    }

    $route = dnsmigrator_escape($systems[$from] ?? $from) . ' &rarr; '
        . dnsmigrator_escape($systems[$to] ?? $to);

    return '<div class="dm-dns-card"><div class="dm-dns-card-head">Migration Results</div>'
        . '<div class="dm-dns-card-body"><div class="dm-dns-summary"><strong>' . $route . '</strong><span>'
        . ($replace ? 'Destination records were replaced before copying.' : 'Existing destination records were retained.')
        . '</span></div>'
        . '<div class="dm-dns-table-wrap"><table class="dm-dns-table"><thead><tr>'
        . '<th>Domain</th><th>Status</th><th>Added</th><th>Skipped</th><th>Failed</th><th>Details</th>'
        . '</tr></thead><tbody>' . $rows . '</tbody></table></div>'
        . '<div class="dm-dns-actions"><a class="btn dm-dns-primary" href="'
        . dnsmigrator_escape($moduleLink) . '">New Migration</a></div></div></div>';
}

function dnsmigrator_system_options(array $systems, string $selected): string
{
    $html = '';
    foreach ($systems as $value => $label) {
        $html .= '<option value="' . dnsmigrator_escape((string) $value) . '"'
            . ($selected === $value ? ' selected' : '') . '>'
            . dnsmigrator_escape((string) $label) . '</option>';
    }
    return $html;
}

function dnsmigrator_alert(string $type, string $message): string
{
    return '<div class="dm-dns-alert ' . dnsmigrator_escape($type) . '">'
        . dnsmigrator_escape($message) . '</div>';
}

function dnsmigrator_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dnsmigrator_styles(): string
{
    return <<<'HTML'
<style>
.dm-dns-migrator{max-width:1400px;margin:0 auto 30px;color:#273b50}.dm-dns-title{display:flex;align-items:center;justify-content:space-between;margin:0 0 18px}.dm-dns-title h2{margin:0;color:#163a5f;font-size:26px}.dm-dns-title p{margin:5px 0 0;color:#667789}.dm-dns-card{margin:0 0 18px;border:1px solid #d7e0e8;border-radius:7px;background:#fff;overflow:hidden;box-shadow:0 1px 2px rgba(22,58,95,.04)}.dm-dns-card-head{padding:12px 16px;background:#163a5f;color:#fff;font-size:16px;font-weight:700}.dm-dns-card-body{padding:18px}.dm-dns-grid{display:grid;grid-template-columns:minmax(420px,1.35fr) minmax(330px,.65fr);gap:22px}.dm-dns-field label{display:block;margin:0 0 6px;font-weight:700;color:#163a5f}.dm-dns-field textarea,.dm-dns-field select{width:100%;border:1px solid #c8d3dd;border-radius:6px;background:#fff;color:#273b50;padding:9px 10px;box-sizing:border-box}.dm-dns-field textarea:focus,.dm-dns-field select:focus{border-color:#f58220;outline:0;box-shadow:0 0 0 .14rem rgba(245,130,32,.18)}.dm-dns-help{margin-top:6px;color:#6a7987;font-size:12px}.dm-dns-route{display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:end;align-content:start}.dm-dns-arrow{padding:0 2px 10px;color:#163a5f;font-size:24px;font-weight:700}.dm-dns-check{grid-column:1/-1;display:flex;align-items:flex-start;gap:8px;margin-top:8px;font-weight:600;color:#273b50}.dm-dns-check input{margin-top:3px;accent-color:#f58220}.dm-dns-note{grid-column:1/-1;padding:11px 12px;border:1px solid #d7e7f5;border-radius:6px;background:#eef6fc;color:#163a5f;font-size:13px;line-height:1.45}.dm-dns-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}.dm-dns-primary,.dm-dns-primary:focus{border-color:#f58220!important;background:#f58220!important;color:#fff!important;font-weight:700}.dm-dns-primary:hover{border-color:#d8741f!important;background:#d8741f!important;color:#fff!important}.dm-dns-secondary,.dm-dns-secondary:focus{border-color:#163a5f!important;background:#163a5f!important;color:#fff!important;font-weight:700}.dm-dns-secondary:hover{border-color:#214e7a!important;background:#214e7a!important;color:#fff!important}.dm-dns-alert{margin:0 0 16px;padding:11px 14px;border:1px solid transparent;border-radius:6px}.dm-dns-alert.danger{border-color:#e1b9b8;background:#f9e9e8;color:#7e302e}.dm-dns-alert.warning{border-color:#ead8a3;background:#fff7dc;color:#6c5620}.dm-dns-summary{display:flex;justify-content:space-between;gap:16px;align-items:center;margin:0 0 14px;padding:10px 12px;border-radius:6px;background:#eef3f7;color:#163a5f}.dm-dns-summary span{font-size:13px}.dm-dns-table-wrap{overflow:auto}.dm-dns-table{width:100%;border-collapse:collapse}.dm-dns-table th{padding:9px 10px;background:#163a5f;color:#fff;text-align:left;white-space:nowrap}.dm-dns-table td{padding:9px 10px;border-bottom:1px solid #e1e7ec;vertical-align:top}.dm-dns-table tbody tr:hover{background:#fff8f1}.dm-dns-badge{display:inline-block;min-width:64px;padding:3px 8px;border-radius:12px;text-align:center;font-size:12px;font-weight:700}.dm-dns-badge.success{background:#dff2e4;color:#27633a}.dm-dns-badge.warning{background:#fff1bf;color:#735a08}.dm-dns-badge.danger{background:#f4d9d8;color:#8a3431}.dm-dns-table details summary{cursor:pointer;color:#163a5f;font-weight:700}.dm-dns-table details ul{margin:8px 0 0;padding-left:18px}.dm-dns-spinner{display:none;width:14px;height:14px;margin-right:7px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;vertical-align:-2px;animation:dmDnsSpin .7s linear infinite}.dm-dns-primary.dm-dns-processing .dm-dns-spinner{display:inline-block}.dm-dns-primary:disabled{opacity:.82!important;cursor:wait!important}@keyframes dmDnsSpin{to{transform:rotate(360deg)}}@media(max-width:900px){.dm-dns-grid{grid-template-columns:1fr}.dm-dns-summary{align-items:flex-start;flex-direction:column}.dm-dns-route{grid-template-columns:1fr}.dm-dns-arrow{display:none}} 
</style>
HTML;
}

function dnsmigrator_script(): string
{
    return <<<'HTML'
<script>
(function(){
    document.querySelectorAll('.dm-dns-migrator form').forEach(function(form){
        form.addEventListener('submit', function(){
            var button = form.querySelector('[data-dns-submit]');
            if (!button || button.disabled) { return; }
            button.disabled = true;
            button.classList.add('dm-dns-processing');
            var text = button.querySelector('.dm-dns-button-text');
            if (text) { text.textContent = 'Processing…'; }
        });
    });
})();
</script>
HTML;
}

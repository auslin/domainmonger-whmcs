<?php
/**
 * Bulk Domain Manager
 *
 * PHP 8 / WHMCS 9 rebuild for DomainMonger.
 * Scope is intentionally limited to:
 * - Nameserver updates
 * - Registrar lock / unlock
 * - Disable WHOIS privacy
 * - Update EPP/Auth code
 * - WHOIS/contact updates with NEO account preflight
 * - Move domain lists between WHMCS or NEO customer accounts
 * - Renew domains with an explicit term, update WHMCS Registration Period, then sync
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

function multibulkupdater_config(): array
{
    return [
        'name' => 'Bulk Domain Manager',
        'description' => 'Manage domain settings, renewals, WHOIS/contact information, account moves, existing-domain imports, and DNSPlus zone copies for multiple domains.',
        'version' => '4.3',
        'author' => 'DomainMonger',
        'language' => 'english',
        'fields' => [
            'epp' => [
                'FriendlyName' => 'Default EPP/Auth Code',
                'Type' => 'text',
                'Size' => '40',
            ],
            'ns1' => ['FriendlyName' => 'Default Nameserver 1', 'Type' => 'text', 'Size' => '40'],
            'ns2' => ['FriendlyName' => 'Default Nameserver 2', 'Type' => 'text', 'Size' => '40'],
            'ns3' => ['FriendlyName' => 'Default Nameserver 3', 'Type' => 'text', 'Size' => '40'],
            'ns4' => ['FriendlyName' => 'Default Nameserver 4', 'Type' => 'text', 'Size' => '40'],
            'ns5' => ['FriendlyName' => 'Default Nameserver 5', 'Type' => 'text', 'Size' => '40'],
            'default_regperiod' => [
                'FriendlyName' => 'Default Registration Period',
                'Type' => 'dropdown',
                'Options' => '1,2,3,4,5,6,7,8,9,10',
                'Default' => '1',
                'Description' => 'Default years shown in Move Domains and Renew Domains.',
            ],
        ],
    ];
}

function multibulkupdater_default_registration_period(array $vars): int
{
    $value = trim((string) ($vars['default_regperiod'] ?? '1'));
    if ($value === '' || !ctype_digit($value)) {
        return 1;
    }

    return max(1, min(10, (int) $value));
}

function multibulkupdater_activate(): array
{
    multibulkupdater_cleanup_legacy_files();

    return [
        'status' => 'success',
        'description' => 'Bulk Domain Manager is ready.',
    ];
}

function multibulkupdater_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'Bulk Domain Manager has been deactivated.',
    ];
}

function multibulkupdater_upgrade(array $vars): void
{
    $installedVersion = (string) ($vars['version'] ?? '0');

    if (version_compare($installedVersion, '2.0.0', '<')) {
        multibulkupdater_cleanup_legacy_files();

        try {
            Capsule::table('tbladdonmodules')
                ->where('module', 'multibulkupdater')
                ->whereIn('setting', [
                    'osusername', 'oskey', 'ostest', 'eppca', 'tag', 'tepp',
                    'wfn', 'wln', 'wemail', 'wcompany', 'wadr1', 'wadr2',
                    'wadr3', 'wcity', 'wstate', 'wzip', 'wcountry', 'wphone', 'wfax',
                ])
                ->delete();
        } catch (Throwable $e) {
            // The obsolete settings are harmless if the database cleanup cannot run.
        }
    }
}

function multibulkupdater_cleanup_legacy_files(): array
{
    $base = __DIR__;
    $targets = [
        $base . '/libs',
        $base . '/multibulkupdaterx2.php',
        $base . '/multibulkupdaterx3.php',
        $base . '/multibulkupdater_519.php',
        $base . '/multibulkupdater_520.php',
        $base . '/.DS_Store',
        $base . '/lang/.DS_Store',
    ];

    $failures = [];
    foreach ($targets as $target) {
        if (!file_exists($target)) {
            continue;
        }

        if (!multibulkupdater_remove_path($target)) {
            $failures[] = str_replace($base . '/', '', $target);
        }
    }

    return $failures;
}

function multibulkupdater_remove_path(string $path): bool
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
        if (!multibulkupdater_remove_path($path . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return @rmdir($path);
}

function multibulkupdater_output(array $vars): void
{
    $cleanupFailures = multibulkupdater_cleanup_legacy_files();
    $moduleLink = (string) ($vars['modulelink'] ?? 'addonmodules.php?module=multibulkupdater');
    $page = (string) ($_REQUEST['mbu_page'] ?? 'bulk');
    if ($page === 'whois') {
        multibulkupdater_whois_output($vars);
        return;
    }
    if ($page === 'move') {
        multibulkupdater_move_output($vars);
        return;
    }
    if ($page === 'add') {
        multibulkupdater_add_output($vars);
        return;
    }

    $step = (string) ($_POST['mbu_step'] ?? 'start');
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['bulk_action'] ?? '') === 'update_whois') {
        $_POST['mbu_page'] = 'whois';
        $_POST['mbu_step'] = 'start';
        multibulkupdater_whois_output($vars);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['bulk_action'] ?? '') === 'move_domains') {
        $_POST['mbu_page'] = 'move';
        $_POST['mbu_step'] = 'start';
        multibulkupdater_move_output($vars);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['bulk_action'] ?? '') === 'add_domains') {
        $_POST['mbu_page'] = 'add';
        $_POST['mbu_step'] = 'start';
        multibulkupdater_add_output($vars);
        return;
    }

    $storedDomains = (string) ($_SESSION['multibulkupdater_domains'] ?? '');
    $rawDomains = isset($_POST['domains']) ? (string) $_POST['domains'] : $storedDomains;
    $action = (string) ($_POST['bulk_action'] ?? 'nameservers');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_token('WHMCS.admin.default');
    }

    $domains = multibulkupdater_parse_domains($rawDomains);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['multibulkupdater_domains'] = $rawDomains;
    }

    echo multibulkupdater_styles();
    echo '<div class="mbu-wrap">';
    echo '<div class="mbu-header"><div><h2>Bulk Domain Manager</h2>';
    echo '<p>Manage nameservers, renewals, registrar locks, WHOIS privacy, EPP/Auth codes, WHOIS contacts, account moves, existing-domain imports, and DNSPlus zone copies for multiple domains.</p></div></div>';
    echo multibulkupdater_action_switcher($moduleLink, $rawDomains, $action);

    if ($cleanupFailures) {
        echo multibulkupdater_alert(
            'warning',
            'The PHP 8 rebuild is active, but these obsolete legacy files could not be deleted automatically: '
            . multibulkupdater_escape(implode(', ', $cleanupFailures))
            . '. They are no longer loaded by this addon.'
        );
    }

    if ($step === 'confirm') {
        if (!$domains) {
            $errors[] = 'Enter at least one domain.';
        }
        if (!array_key_exists($action, multibulkupdater_actions())) {
            $errors[] = 'Select a supported action.';
        }

        $payload = multibulkupdater_collect_payload($action, $_POST, $vars, $errors);
        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', $error);
            }
            echo multibulkupdater_start_form($moduleLink, $rawDomains, $action, $vars);
        } else {
            echo multibulkupdater_confirm_form($moduleLink, $rawDomains, $domains, $action, $payload);
        }
    } elseif ($step === 'execute') {
        if (!$domains) {
            $errors[] = 'No domains were submitted.';
        }
        if (!array_key_exists($action, multibulkupdater_actions())) {
            $errors[] = 'The submitted action is not supported.';
        }

        $payload = multibulkupdater_collect_payload($action, $_POST, $vars, $errors);
        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', $error);
            }
            echo multibulkupdater_start_form($moduleLink, $rawDomains, $action, $vars);
        } else {
            $results = multibulkupdater_execute($domains, $action, $payload);
            echo multibulkupdater_results($moduleLink, $action, $results);
        }
    } else {
        echo multibulkupdater_start_form($moduleLink, $rawDomains, $action, $vars);
    }

    echo multibulkupdater_submit_script();
    echo '</div>';
}



function multibulkupdater_action_switcher(string $moduleLink, string $rawDomains, string $currentAction): string
{
    if (!array_key_exists($currentAction, multibulkupdater_actions())) {
        $currentAction = 'nameservers';
    }

    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-action-switcher" id="mbu-action-switcher">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="bulk">';
    $html .= '<input type="hidden" name="mbu_step" value="start">';
    $html .= '<textarea class="mbu-hidden" name="domains" id="mbu-action-switcher-domains">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<label for="mbu-nav-action">Action</label><select id="mbu-nav-action" name="bulk_action">';
    foreach (multibulkupdater_actions() as $value => $label) {
        $selected = $value === $currentAction ? ' selected' : '';
        $html .= '<option value="' . multibulkupdater_escape($value) . '"' . $selected . '>' . multibulkupdater_escape($label) . '</option>';
    }
    $html .= '</select></form>';
    $html .= '<script>(function(){var f=document.getElementById("mbu-action-switcher"),s=document.getElementById("mbu-nav-action"),h=document.getElementById("mbu-action-switcher-domains");if(!f||!s){return;}s.addEventListener("change",function(){var d=document.getElementById("mbu-domains")||document.getElementById("mbu-whois-domains")||document.getElementById("mbu-move-domains")||document.getElementById("mbu-add-domains");if(h&&d){h.value=d.value;}f.submit();});}());</script>';
    return $html;
}


function multibulkupdater_whois_output(array $vars): void
{
    @set_time_limit(0);
    $moduleLink = (string) ($vars['modulelink'] ?? 'addonmodules.php?module=multibulkupdater');
    $step = (string) ($_POST['mbu_step'] ?? $_GET['mbu_step'] ?? 'start');
    $errors = [];
    $presets = multibulkupdater_whois_presets();
    $storedDomains = (string) ($_SESSION['multibulkupdater_whois_domains'] ?? '');
    $rawDomains = isset($_POST['domains']) ? (string) $_POST['domains'] : $storedDomains;
    $domains = multibulkupdater_parse_domains($rawDomains);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_token('WHMCS.admin.default');
        $_SESSION['multibulkupdater_whois_domains'] = $rawDomains;
    }

    echo multibulkupdater_styles();
    echo '<div class="mbu-wrap">';
    echo '<div class="mbu-header"><div><h2>Bulk Domain Manager</h2>';
    echo '<p>Update WHOIS/contact information for a list of domains in one NEO account.</p></div></div>';
    echo multibulkupdater_action_switcher($moduleLink, $rawDomains, 'update_whois');

    if ($step === 'whois_results') {
        $results = (array) ($_SESSION['multibulkupdater_whois_results'] ?? []);
        if ($results) {
            echo multibulkupdater_results($moduleLink . '&mbu_page=whois', 'whois', $results);
        } else {
            echo multibulkupdater_alert('info', 'No WHOIS update results are available.');
            echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, []);
        }
    } elseif ($step === 'save_whois_preset') {
        $contact = multibulkupdater_whois_collect_contact($_POST, $errors, false);
        $presetName = trim((string) ($_POST['preset_name'] ?? ''));
        if ($presetName === '') {
            $errors[] = 'Enter a preset name.';
        }
        if (!$errors) {
            multibulkupdater_whois_save_preset($presetName, $contact);
            $presets = multibulkupdater_whois_presets();
            echo multibulkupdater_alert('success', 'WHOIS preset saved.');
        } else {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
        }
        echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
    } elseif ($step === 'delete_whois_preset') {
        $presetKey = trim((string) ($_POST['preset_key'] ?? ''));
        if ($presetKey !== '') {
            multibulkupdater_whois_delete_preset($presetKey);
            $presets = multibulkupdater_whois_presets();
            echo multibulkupdater_alert('success', 'WHOIS preset deleted.');
        }
        echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
    } elseif ($step === 'whois_resend') {
        if (!$domains) {
            echo multibulkupdater_alert('danger', 'Enter at least one domain.');
            echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
        } else {
            $results = multibulkupdater_whois_resend_verification($domains);
            echo multibulkupdater_results($moduleLink . '&mbu_page=whois', 'whois_resend', $results);
            echo multibulkupdater_alert('info', 'Resend Verification does not change WHOIS information. It only asks NEO to immediately resend the current IRTP approval emails to all approval participants.');
        }
    } elseif ($step === 'whois_confirm') {
        if (!$domains) {
            $errors[] = 'Enter at least one domain.';
        }
        $contact = multibulkupdater_whois_collect_contact($_POST, $errors, true);
        $roles = multibulkupdater_whois_collect_roles($_POST, $errors);
        $optOut = multibulkupdater_truthy($_POST['lock_optout'] ?? '0');
        $autoResend = multibulkupdater_truthy($_POST['auto_resend'] ?? '0');

        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
            echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
        } else {
            $preflight = multibulkupdater_whois_preflight($domains, $contact, $roles);
            if (!$preflight['ok']) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($preflight['message']));
                if (!empty($preflight['rows'])) {
                    echo multibulkupdater_whois_preflight_table($preflight['rows']);
                }
                echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
            } else {
                $_SESSION['multibulkupdater_whois_preflight'] = $preflight;
                echo multibulkupdater_whois_confirm_form($moduleLink, $rawDomains, $contact, $roles, $optOut, $autoResend, $preflight);
            }
        }
    } elseif ($step === 'whois_execute') {
        if (!$domains) {
            $errors[] = 'No domains were submitted.';
        }
        $contact = multibulkupdater_whois_collect_contact($_POST, $errors, true);
        $roles = multibulkupdater_whois_collect_roles($_POST, $errors);
        $optOut = multibulkupdater_truthy($_POST['lock_optout'] ?? '0');
        $autoResend = multibulkupdater_truthy($_POST['auto_resend'] ?? '0');
        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
            echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
        } else {
            // Re-run the account/contact preflight immediately before execution.
            // This prevents a stale confirmation page from updating domains that
            // were moved to another NEO customer in the meantime.
            $preflight = multibulkupdater_whois_preflight($domains, $contact, $roles);
            if (!$preflight['ok']) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($preflight['message']));
                echo multibulkupdater_whois_preflight_table($preflight['rows']);
                echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, $_POST);
            } else {
                $results = multibulkupdater_whois_execute($preflight, $contact, $roles, $optOut, $autoResend);
                $_SESSION['multibulkupdater_whois_results'] = $results;
                $resultsUrl = $moduleLink . '&mbu_page=whois&mbu_step=whois_results';
                echo '<script>window.location.replace(' . json_encode($resultsUrl, JSON_UNESCAPED_SLASHES) . ');</script>';
                echo '<noscript>' . multibulkupdater_results($moduleLink . '&mbu_page=whois', 'whois', $results) . '</noscript>';
            }
        }
    } else {
        echo multibulkupdater_whois_start_form($moduleLink, $rawDomains, $presets, []);
    }

    echo multibulkupdater_submit_script();
    echo '</div>';
}

function multibulkupdater_whois_preset_setting(): string
{
    return 'whois_presets_json';
}

function multibulkupdater_whois_presets(): array
{
    try {
        $raw = Capsule::table('tbladdonmodules')
            ->where('module', 'multibulkupdater')
            ->where('setting', multibulkupdater_whois_preset_setting())
            ->value('value');
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    } catch (Throwable $e) {
        return [];
    }
}

function multibulkupdater_whois_save_preset(string $name, array $contact): void
{
    $presets = multibulkupdater_whois_presets();
    $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? '', '-'));
    if ($key === '') {
        $key = 'preset-' . substr(sha1($name . microtime(true)), 0, 8);
    }
    $presets[$key] = ['name' => $name, 'contact' => $contact];

    Capsule::table('tbladdonmodules')->updateOrInsert(
        ['module' => 'multibulkupdater', 'setting' => multibulkupdater_whois_preset_setting()],
        ['value' => json_encode($presets, JSON_UNESCAPED_SLASHES)]
    );
}

function multibulkupdater_whois_delete_preset(string $key): void
{
    $presets = multibulkupdater_whois_presets();
    unset($presets[$key]);
    Capsule::table('tbladdonmodules')->updateOrInsert(
        ['module' => 'multibulkupdater', 'setting' => multibulkupdater_whois_preset_setting()],
        ['value' => json_encode($presets, JSON_UNESCAPED_SLASHES)]
    );
}

function multibulkupdater_whois_fields(): array
{
    return [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'company' => 'Company Name',
        'email' => 'Email Address',
        'address1' => 'Address 1',
        'address2' => 'Address 2',
        'address3' => 'Address 3',
        'city' => 'City',
        'state' => 'State / Province',
        'postcode' => 'ZIP / Postal Code',
        'country' => 'Country',
        'phone' => 'Phone Number',
        'fax' => 'Fax Number',
    ];
}

function multibulkupdater_whois_required_fields(): array
{
    return ['first_name', 'last_name', 'email', 'address1', 'city', 'state', 'postcode', 'country', 'phone'];
}

function multibulkupdater_whois_countries(): array
{
    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $countryFile = $root . '/includes/countries.php';
    $countries = [];
    if (is_file($countryFile)) {
        include $countryFile;
    }
    if (!is_array($countries) || !$countries) {
        $countries = ['US' => 'United States'];
    }
    asort($countries, SORT_NATURAL | SORT_FLAG_CASE);
    return $countries;
}

function multibulkupdater_whois_country_calling_code(string $country): string
{
    $country = strtoupper(trim($country));
    if ($country === '') {
        return '';
    }

    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $dataFile = $root . '/vendor/punic/punic/src/data/telephoneCodeData.php';
    if (!is_file($dataFile)) {
        return '';
    }

    $codes = require $dataFile;
    if (!is_array($codes) || empty($codes[$country]) || !is_array($codes[$country])) {
        return '';
    }

    return preg_replace('/\D+/', '', (string) reset($codes[$country])) ?? '';
}

function multibulkupdater_whois_collect_contact(array $source, array &$errors, bool $required): array
{
    $contact = [];
    foreach (multibulkupdater_whois_fields() as $key => $label) {
        $contact[$key] = trim((string) ($source['whois_' . $key] ?? ''));
    }

    if ($required) {
        foreach (multibulkupdater_whois_required_fields() as $key) {
            if ($contact[$key] === '') {
                $errors[] = multibulkupdater_whois_fields()[$key] . ' is required.';
            }
        }
    }
    if ($contact['email'] !== '' && !filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($contact['country'] !== '') {
        $contact['country'] = strtoupper($contact['country']);
        if (!preg_match('/^[A-Z]{2}$/', $contact['country'])) {
            $errors[] = 'Select a valid country.';
        }
    }

    // Phone/Fax country codes are derived from the contact country so the
    // operator does not have to enter the same country information twice.
    $contact['phone_cc'] = multibulkupdater_whois_country_calling_code($contact['country'] ?? '');
    $contact['fax_cc'] = $contact['fax'] !== '' ? $contact['phone_cc'] : '';
    if ($contact['country'] !== '' && $contact['phone_cc'] === '') {
        $errors[] = 'Could not determine the telephone country code for ' . $contact['country'] . '.';
    }

    return $contact;
}

function multibulkupdater_whois_collect_roles(array $source, array &$errors): array
{
    $roles = [];
    foreach (['Registrant', 'Admin', 'Tech', 'Billing'] as $role) {
        $key = 'role_' . strtolower($role);
        if (multibulkupdater_truthy($source[$key] ?? '0')) {
            $roles[] = $role;
        }
    }
    if (!$roles) {
        $errors[] = 'Select at least one WHOIS contact role.';
    }
    return $roles;
}

function multibulkupdater_truthy($value): bool
{
    return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
}

function multibulkupdater_whois_start_form(string $moduleLink, string $rawDomains, array $presets, array $source): string
{
    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card" id="mbu-whois-form">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="whois">';
    $html .= '<input type="hidden" name="mbu_step" id="mbu-whois-step" value="start">';
    $html .= '<div class="mbu-card-title">Update WHOIS</div><div class="mbu-card-body">';
    $html .= '<label for="mbu-whois-domains">Domains</label>';
    $html .= '<textarea id="mbu-whois-domains" name="domains" rows="10" placeholder="example.com&#10;example.net">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<div class="mbu-help">All submitted domains must resolve to the same NEO customer account. BDM checks this before any update is submitted.</div>';

    $html .= '<div class="mbu-fields"><h4>WHOIS Contact</h4>';
    $html .= '<div class="mbu-whois-presets"><span class="mbu-preset-label">Apply preset:</span>';
    if ($presets) {
        foreach ($presets as $key => $preset) {
            $name = (string) ($preset['name'] ?? $key);
            $contact = is_array($preset['contact'] ?? null) ? $preset['contact'] : [];
            $html .= '<button type="button" class="btn mbu-whois-preset" data-preset-key="' . multibulkupdater_escape((string) $key) . '"';
            foreach (multibulkupdater_whois_fields() as $field => $label) {
                $html .= ' data-' . multibulkupdater_escape(str_replace('_', '-', $field)) . '="' . multibulkupdater_escape((string) ($contact[$field] ?? '')) . '"';
            }
            $html .= '>' . multibulkupdater_escape($name) . '</button>';
        }
    } else {
        $html .= '<span class="mbu-help-inline">No saved presets yet.</span>';
    }
    $html .= '</div>';
    $html .= '<div class="mbu-help mbu-preset-help">Choose a saved preset or enter contact information manually. To create a reusable preset, fill the fields, enter a Preset Name, and click Save Preset.</div>';

    $values = [];
    foreach (multibulkupdater_whois_fields() as $field => $label) {
        if ($field === 'country') {
            $values[$field] = strtoupper(trim((string) ($source['whois_country'] ?? 'US')));
            if ($values[$field] === '') {
                $values[$field] = 'US';
            }
        } else {
            $values[$field] = (string) ($source['whois_' . $field] ?? '');
        }
    }
    $requiredFields = array_flip(multibulkupdater_whois_required_fields());
    $html .= '<div class="mbu-required-note"><span class="mbu-required">*</span> Required for WHOIS updates</div>';
    $html .= '<div class="mbu-grid mbu-whois-grid">';
    foreach (multibulkupdater_whois_fields() as $field => $label) {
        $requiredMark = isset($requiredFields[$field]) ? ' <span class="mbu-required" aria-label="required">*</span>' : '';
        $html .= '<div><label for="mbu-whois-' . multibulkupdater_escape($field) . '">' . multibulkupdater_escape($label) . $requiredMark . '</label>';
        if ($field === 'country') {
            $html .= '<select id="mbu-whois-country" name="whois_country">';
            foreach (multibulkupdater_whois_countries() as $countryCode => $countryName) {
                $selected = strtoupper((string) $countryCode) === $values['country'] ? ' selected' : '';
                $html .= '<option value="' . multibulkupdater_escape(strtoupper((string) $countryCode)) . '"' . $selected . '>' . multibulkupdater_escape((string) $countryName) . '</option>';
            }
            $html .= '</select>';
        } else {
            $type = $field === 'email' ? 'email' : 'text';
            $html .= '<input id="mbu-whois-' . multibulkupdater_escape($field) . '" type="' . $type . '" name="whois_' . multibulkupdater_escape($field) . '" value="' . multibulkupdater_escape($values[$field]) . '" autocomplete="off">';
        }
        $html .= '</div>';
    }
    $html .= '</div>';

    $html .= '<div class="mbu-preset-save-row"><div><label for="mbu-preset-name">Preset Name</label><input id="mbu-preset-name" type="text" name="preset_name" value="' . multibulkupdater_escape((string) ($source['preset_name'] ?? '')) . '" placeholder="Example: DomainMonger"></div>';
    $html .= '<button type="submit" data-mbu-step="save_whois_preset" class="btn btn-default">Save Preset</button>';
    if ($presets) {
        $html .= '<select name="preset_key" class="mbu-delete-preset-select"><option value="">Delete preset…</option>';
        foreach ($presets as $key => $preset) {
            $html .= '<option value="' . multibulkupdater_escape((string) $key) . '">' . multibulkupdater_escape((string) ($preset['name'] ?? $key)) . '</option>';
        }
        $html .= '</select><button type="submit" data-mbu-step="delete_whois_preset" class="btn mbu-danger-button" onclick="return this.form.preset_key.value !== \'\' && confirm(\'Delete this WHOIS preset?\');">Delete</button>';
    }
    $html .= '</div></div>';

    $html .= '<div class="mbu-fields"><h4>Apply Contact To</h4><div class="mbu-role-row">';
    foreach (['Registrant', 'Admin', 'Tech', 'Billing'] as $role) {
        $key = 'role_' . strtolower($role);
        $checked = !array_key_exists($key, $source) || multibulkupdater_truthy($source[$key]) ? ' checked' : '';
        $html .= '<input type="hidden" name="' . $key . '" value="0">';
        $html .= '<label class="mbu-check"><input type="checkbox" name="' . $key . '" value="1"' . $checked . '> <span>' . $role . '</span></label>';
    }
    $html .= '</div><div class="mbu-help">BDM updates only contact roles returned by the registrar for each domain. TLDs that do not use a selected role are skipped for that role.</div></div>';

    $optOutChecked = !array_key_exists('lock_optout', $source) || multibulkupdater_truthy($source['lock_optout']);
    $autoResendChecked = !array_key_exists('auto_resend', $source) || multibulkupdater_truthy($source['auto_resend']);
    $html .= '<div class="mbu-fields"><h4>Change of Registrant</h4>';
    $html .= '<input type="hidden" name="lock_optout" value="0">';
    $html .= '<label class="mbu-lock-row" for="mbu-lock-optout"><span class="mbu-switch"><input id="mbu-lock-optout" type="checkbox" name="lock_optout" value="1"' . ($optOutChecked ? ' checked' : '') . '><span class="mbu-switch-slider"></span></span><span><strong>Opt out of the 60-day transfer lock</strong><small>Matches the client-facing WHOIS update option. Applies when the registrar/TLD permits the opt-out.</small></span></label>';
    $html .= '<input type="hidden" name="auto_resend" value="0">';
    $html .= '<label class="mbu-lock-row mbu-lock-row-spaced" for="mbu-auto-resend"><span class="mbu-switch"><input id="mbu-auto-resend" type="checkbox" name="auto_resend" value="1"' . ($autoResendChecked ? ' checked' : '') . '><span class="mbu-switch-slider"></span></span><span><strong>Immediately resend verification emails after a pending Registrant change</strong><small>Enabled by default. NEO resends the IRTP authorization email to all participants, avoiding the normal email queue delay.</small></span></label>';
    $html .= '<div class="mbu-help">BDM predicts verification using the domain TLD and lifecycle status. Active gTLD Registrant Name, Company, or Email changes normally require IRTP approval from the old/losing and new/gaining registrants. gTLDs in WHMCS Grace are labeled as not expected to trigger IRTP. ccTLDs and other registry-specific TLDs show their applicable registry label instead. The NEO API response remains authoritative after submission.</div>';
    $html .= '<div class="mbu-inline-action"><button type="submit" data-mbu-step="whois_resend" class="btn btn-default mbu-submit-button" data-processing-text="Resending Verification…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Resend Pending Verification</span></button><span class="mbu-help-inline">Uses only the domain list above. It does not modify WHOIS information.</span></div></div>';

    $html .= '<div class="mbu-actions"><button type="submit" data-mbu-step="whois_confirm" class="btn btn-primary mbu-submit-button" data-processing-text="Checking NEO & WHOIS…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Review WHOIS Update</span></button></div>';
    $html .= '</div></form>';
    $html .= multibulkupdater_whois_script();
    return $html;
}

function multibulkupdater_whois_script(): string
{
    return <<<'HTML'
<script>
(function () {
    const buttons = Array.prototype.slice.call(document.querySelectorAll('.mbu-whois-preset'));
    const fields = [
        'first_name','last_name','company','email','address1','address2','address3','city','state','postcode','country','phone','fax'
    ];
    function clearActive() {
        buttons.forEach(function (button) { button.classList.remove('mbu-preset-active'); });
    }
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            fields.forEach(function (field) {
                const input = document.getElementById('mbu-whois-' + field);
                if (!input) return;
                const attr = 'data-' + field.replace(/_/g, '-');
                input.value = button.getAttribute(attr) || '';
            });
            clearActive();
            button.classList.add('mbu-preset-active');
        });
    });
    fields.forEach(function (field) {
        const input = document.getElementById('mbu-whois-' + field);
        if (!input) return;
        input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input', clearActive);
    });

    const form = document.getElementById('mbu-whois-form');
    const step = document.getElementById('mbu-whois-step');
    if (form && step) {
        form.addEventListener('submit', function (event) {
            const submitter = event.submitter;
            if (submitter && submitter.dataset && submitter.dataset.mbuStep) {
                step.value = submitter.dataset.mbuStep;
            }
        });
    }
}());
</script>
HTML;
}

function multibulkupdater_whois_load_epp_manager(): void
{
    if (function_exists('dm_epp_find_credentials') && function_exists('dm_epp_http_request')) {
        return;
    }
    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $file = $root . '/includes/hooks/domainmonger_epp_authcode_manager.php';
    if (is_file($file)) {
        require_once $file;
    }
}

function multibulkupdater_whois_neo_domain_details($domainRow, array $selectedRoles = []): array
{
    $registrar = strtolower(trim((string) ($domainRow->registrar ?? '')));
    if ($registrar === '' || strpos($registrar, 'netearthone') === false) {
        return ['ok' => false, 'customer_id' => '', 'registrar' => $registrar, 'message' => 'Domain is not assigned to a NetEarthOne/NEO registrar module.'];
    }

    multibulkupdater_whois_load_epp_manager();
    if (!function_exists('dm_epp_find_credentials') || !function_exists('dm_epp_http_request') || !function_exists('dm_epp_api_base')) {
        return ['ok' => false, 'customer_id' => '', 'registrar' => $registrar, 'message' => 'NEO credential/API helper is not available.'];
    }

    $credentials = dm_epp_find_credentials($registrar);
    if (!is_array($credentials) || trim((string) ($credentials['authUserId'] ?? '')) === '' || trim((string) ($credentials['apiKey'] ?? '')) === '') {
        return ['ok' => false, 'customer_id' => '', 'registrar' => $registrar, 'message' => 'NEO API credentials could not be resolved from the registrar configuration.'];
    }

    [$ok, $response, $error] = dm_epp_http_request('GET', dm_epp_api_base($credentials) . '/domains/details-by-name.json', [
        'auth-userid' => (string) $credentials['authUserId'],
        'api-key' => (string) $credentials['apiKey'],
        'domain-name' => strtolower(trim((string) $domainRow->domain)),
        'options' => 'All',
    ]);

    if (!$ok || !is_array($response)) {
        return ['ok' => false, 'customer_id' => '', 'registrar' => $registrar, 'message' => $error !== '' ? $error : 'NEO did not return domain details.'];
    }

    $customerId = trim((string) ($response['customerid'] ?? $response['customer-id'] ?? ''));
    $orderId = trim((string) ($response['orderid'] ?? $response['entityid'] ?? $response['order-id'] ?? ''));
    if ($customerId === '') {
        return ['ok' => false, 'customer_id' => '', 'registrar' => $registrar, 'message' => 'NEO domain details did not include a customer ID.'];
    }
    if ($orderId === '' || !preg_match('/^\d+$/', $orderId)) {
        return ['ok' => false, 'customer_id' => $customerId, 'registrar' => $registrar, 'message' => 'NEO domain details did not include a valid registrar order ID.'];
    }

    $contactIds = [
        'Registrant' => trim((string) ($response['registrantcontactid'] ?? '')),
        'Admin' => trim((string) ($response['admincontactid'] ?? '')),
        'Tech' => trim((string) ($response['techcontactid'] ?? '')),
        'Billing' => trim((string) ($response['billingcontactid'] ?? '')),
    ];

    $roleTypes = [];
    $roleContactDetails = [];
    foreach ($selectedRoles as $role) {
        $contactId = (string) ($contactIds[$role] ?? '');
        if ($contactId === '' || !preg_match('/^\d+$/', $contactId) || (int) $contactId <= 0) {
            return [
                'ok' => false,
                'customer_id' => $customerId,
                'registrar' => $registrar,
                'message' => $role . ' contact is not available for this domain.',
            ];
        }

        [$contactOk, $contactDetails, $contactError] = dm_epp_http_request('GET', dm_epp_api_base($credentials) . '/contacts/details.json', [
            'auth-userid' => (string) $credentials['authUserId'],
            'api-key' => (string) $credentials['apiKey'],
            'contact-id' => $contactId,
        ]);
        if (!$contactOk || !is_array($contactDetails)) {
            return [
                'ok' => false,
                'customer_id' => $customerId,
                'registrar' => $registrar,
                'message' => $contactError !== '' ? $contactError : 'Could not determine the current ' . $role . ' contact type.',
            ];
        }
        $contactType = trim((string) ($contactDetails['type'] ?? ''));
        if ($contactType === '') {
            return [
                'ok' => false,
                'customer_id' => $customerId,
                'registrar' => $registrar,
                'message' => 'NEO did not return the current ' . $role . ' contact type.',
            ];
        }
        if (strcasecmp($contactType, 'Contact') !== 0) {
            return [
                'ok' => false,
                'customer_id' => $customerId,
                'registrar' => $registrar,
                'message' => $role . ' uses special NEO contact type ' . $contactType . '. BDM WHOIS currently blocks special-TLD contact types rather than guessing required registry fields.',
            ];
        }
        $roleTypes[$role] = $contactType;
        $roleContactDetails[$role] = $contactDetails;
    }

    return [
        'ok' => true,
        'customer_id' => $customerId,
        'order_id' => $orderId,
        'registrar' => $registrar,
        'contact_ids' => $contactIds,
        'role_types' => $roleTypes,
        'contact_details' => $roleContactDetails,
        'message' => '',
    ];
}

function multibulkupdater_whois_decode_role($value): array
{
    if (is_array($value)) {
        return $value;
    }
    if (is_object($value)) {
        return (array) $value;
    }
    $raw = trim((string) $value);
    if ($raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function multibulkupdater_whois_pick(array $contact, array $keys): string
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $contact) && trim((string) $contact[$key]) !== '') {
            return trim((string) $contact[$key]);
        }
    }
    return '';
}

function multibulkupdater_whois_current_summary(array $contact): array
{
    $first = multibulkupdater_whois_pick($contact, ['First_Name', 'First Name', 'firstname', 'FirstName']);
    $last = multibulkupdater_whois_pick($contact, ['Last_Name', 'Last Name', 'lastname', 'LastName']);
    $name = trim($first . ' ' . $last);
    if ($name === '') {
        $name = multibulkupdater_whois_pick($contact, ['Name', 'name']);
    }
    return [
        'name' => $name,
        'company' => multibulkupdater_whois_pick($contact, ['Organisation_Name', 'Organization_Name', 'Company', 'Company Name', 'company']),
        'email' => multibulkupdater_whois_pick($contact, ['Email', 'Email Address', 'emailaddr', 'email']),
    ];
}

function multibulkupdater_whois_identity_value(string $value): string
{
    $value = preg_replace('/\s+/u', ' ', trim($value));
    return mb_strtolower((string) $value, 'UTF-8');
}

function multibulkupdater_whois_tld_key(string $domain): string
{
    $domain = strtolower(trim($domain, " .\t\n\r\0\x0B"));
    foreach (['co.za'] as $compoundTld) {
        if ($domain === $compoundTld || substr($domain, -strlen('.' . $compoundTld)) === '.' . $compoundTld) {
            return $compoundTld;
        }
    }
    $parts = array_values(array_filter(explode('.', $domain), static fn(string $part): bool => $part !== ''));
    return $parts ? (string) end($parts) : '';
}

function multibulkupdater_whois_is_cctld(string $domain): bool
{
    $domain = strtolower(trim($domain, " .\t\n\r\0\x0B"));
    $parts = array_values(array_filter(explode('.', $domain), static fn(string $part): bool => $part !== ''));
    if (!$parts) {
        return false;
    }
    $last = (string) end($parts);
    return strlen($last) === 2 && ctype_alpha($last);
}

function multibulkupdater_whois_verification_profile(
    string $domainName,
    string $whmcsStatus,
    bool $registrantSelected,
    bool $identityTrigger,
    bool $companyChanged,
    string $losingEmail,
    string $gainingEmail,
    array $roles
): array {
    $tld = multibulkupdater_whois_tld_key($domainName);
    $status = strtolower(trim($whmcsStatus));
    $isGrace = in_array($status, ['grace', 'grace period'], true);
    $anyContactUpdate = !empty($roles);

    $old = $losingEmail !== '' ? $losingEmail : 'unknown';
    $new = $gainingEmail !== '' ? $gainingEmail : 'unknown';
    $standardApproval = 'Yes — 2 approvals required. Old: ' . $old . '; New: ' . $new;

    // Registry-specific TLD behavior. These labels deliberately do not set
    // verification_required because that flag controls gTLD IRTP lock/resend logic.
    if ($tld === 'fr') {
        if ($registrantSelected && $identityTrigger) {
            return [
                'required' => false,
                'text' => 'Registry-specific — .FR requires losing and gaining Registrant email authorization. Old: ' . $old . '; New: ' . $new,
            ];
        }
        return ['required' => false, 'text' => 'No — .FR Registrant authorization is not expected for these fields.'];
    }
    if ($tld === 'co.za' && $anyContactUpdate) {
        return [
            'required' => false,
            'text' => 'Registry-specific — .CO.ZA contact update; registry processing can take 5 days and other domain updates are blocked while it is pending.',
        ];
    }
    if ($tld === 'nz' && $anyContactUpdate) {
        return [
            'required' => false,
            'text' => $registrantSelected
                ? 'Registry-specific — .NZ Registrant contact change; a new UDAI is generated. No standard gTLD IRTP.'
                : 'Registry-specific — .NZ contact rules apply. No standard gTLD IRTP.',
        ];
    }
    if ($tld === 'ca' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .CA Registrant eligibility/contact rules apply. No standard gTLD IRTP.'];
    }
    if ($tld === 'nl' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .NL contact/legal-form rules apply. No standard gTLD IRTP.'];
    }
    if ($tld === 'at' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .AT restricts Name, Company, and Country changes. No standard gTLD IRTP.'];
    }
    if ($tld === 'es' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .ES registered-domain contact changes require .ES-specific contact handling. No standard gTLD IRTP.'];
    }
    if ($tld === 'de' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .DE contact/presence and registry DNS-validation rules apply. No standard gTLD IRTP.'];
    }
    if ($tld === 'us' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .US Nexus information may be required for the new Registrant. No standard gTLD IRTP.'];
    }
    if ($tld === 'asia' && $anyContactUpdate) {
        if ($registrantSelected && $identityTrigger) {
            if ($isGrace) {
                return ['required' => false, 'text' => 'No — renewal grace period; gTLD IRTP is not expected. .ASIA registrant eligibility information may still be required.'];
            }
            return ['required' => true, 'text' => $standardApproval . ' .ASIA registrant eligibility information may also be required.'];
        }
        return ['required' => false, 'text' => 'Registry-specific — .ASIA registrant eligibility information may be required in addition to normal contact data.'];
    }
    if ($tld === 'eu' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .EU registrant eligibility/residency rules apply. No standard gTLD IRTP.'];
    }
    if ($tld === 'au' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .AU Registrant eligibility rules apply. No standard gTLD IRTP.'];
    }
    if ($tld === 'uk' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .UK contact changes use registry-specific handling. No standard gTLD IRTP.'];
    }
    if ($tld === 'ru' && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — .RU uses special registrant contact fields. No standard gTLD IRTP.'];
    }

    // Sponsored/special gTLDs can have registry review in addition to IRTP.
    if ($tld === 'jobs' && $registrantSelected && $identityTrigger) {
        if ($isGrace) {
            return ['required' => false, 'text' => 'No — renewal grace period; gTLD IRTP is not expected.' . ($companyChanged ? ' .JOBS registry re-verification may still apply to the organization change.' : '')];
        }
        return ['required' => true, 'text' => $standardApproval . ($companyChanged ? ' .JOBS registry re-verification may also apply to the organization change.' : '')];
    }
    if ($tld === 'coop' && $registrantSelected && $identityTrigger) {
        if ($isGrace) {
            return ['required' => false, 'text' => 'No — renewal grace period; gTLD IRTP is not expected.' . ($companyChanged ? ' .COOP eligibility review may still apply to the organization change.' : '')];
        }
        return ['required' => true, 'text' => $standardApproval . ($companyChanged ? ' .COOP eligibility review may also apply to the organization change.' : '')];
    }

    // Unknown ccTLDs should never be presented as standard gTLD IRTP.
    if (multibulkupdater_whois_is_cctld($domainName) && $anyContactUpdate) {
        return ['required' => false, 'text' => 'Registry-specific — no standard gTLD IRTP; TLD-specific contact rules apply.'];
    }

    if ($registrantSelected && $identityTrigger) {
        if ($isGrace) {
            return ['required' => false, 'text' => 'No — renewal grace period (gTLD IRTP verification not expected).'];
        }
        return ['required' => true, 'text' => $standardApproval];
    }

    return ['required' => false, 'text' => 'No'];
}

function multibulkupdater_whois_preflight(array $domains, array $newContact, array $roles): array
{
    @set_time_limit(0);
    $rows = [];
    $accountKeys = [];
    $fatal = false;

    foreach ($domains as $domainName) {
        try {
            $domain = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'account' => '', 'current_email' => '', 'new_email' => $newContact['email'], 'verification' => '', 'message' => 'WHMCS database lookup failed: ' . $e->getMessage()];
            $fatal = true;
            continue;
        }
        if (!$domain) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'account' => '', 'current_email' => '', 'new_email' => $newContact['email'], 'verification' => '', 'message' => 'Domain not found in WHMCS.'];
            $fatal = true;
            continue;
        }

        $account = multibulkupdater_whois_neo_domain_details($domain, $roles);
        if (!$account['ok']) {
            $verificationOnError = '';
            $accountMessage = (string) ($account['message'] ?? 'WHOIS preflight failed.');
            if (stripos($accountMessage, 'special NEO contact type') !== false) {
                $profile = multibulkupdater_whois_verification_profile(
                    $domainName,
                    (string) ($domain->status ?? ''),
                    in_array('Registrant', $roles, true),
                    false,
                    false,
                    '',
                    (string) ($newContact['email'] ?? ''),
                    $roles
                );
                $verificationOnError = (string) ($profile['text'] ?? '');
            }
            $rows[] = ['domain' => $domainName, 'ok' => false, 'account' => '', 'current_email' => '', 'new_email' => $newContact['email'], 'verification' => $verificationOnError, 'message' => $accountMessage];
            $fatal = true;
            continue;
        }
        $accountKey = (string) $account['registrar'] . ':' . (string) $account['customer_id'];
        $accountKeys[$accountKey] = true;

        $registrantSelected = in_array('Registrant', $roles, true);
        $currentRegistrant = (array) (($account['contact_details']['Registrant'] ?? []));
        if ($registrantSelected && !$currentRegistrant) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'account' => (string) $account['customer_id'], 'current_email' => '', 'new_email' => $newContact['email'], 'verification' => '', 'message' => 'NEO did not return the current Registrant contact details needed for verification review.'];
            $fatal = true;
            continue;
        }
        $summary = $currentRegistrant
            ? multibulkupdater_whois_current_summary($currentRegistrant)
            : ['name' => '', 'company' => '', 'email' => ''];
        $newName = trim($newContact['first_name'] . ' ' . $newContact['last_name']);
        $nameChanged = multibulkupdater_whois_identity_value((string) $summary['name']) !== multibulkupdater_whois_identity_value($newName);
        $companyChanged = multibulkupdater_whois_identity_value((string) $summary['company']) !== multibulkupdater_whois_identity_value((string) $newContact['company']);
        $emailChanged = multibulkupdater_whois_identity_value((string) $summary['email']) !== multibulkupdater_whois_identity_value((string) $newContact['email']);
        $trigger = $registrantSelected && ($nameChanged || $companyChanged || $emailChanged);
        $losingEmail = trim((string) $summary['email']);
        $gainingEmail = trim((string) $newContact['email']);
        $verificationProfile = multibulkupdater_whois_verification_profile(
            $domainName,
            (string) ($domain->status ?? ''),
            $registrantSelected,
            $trigger,
            $companyChanged,
            $losingEmail,
            $gainingEmail,
            $roles
        );
        $verificationText = (string) ($verificationProfile['text'] ?? 'No');
        $verificationRequired = !empty($verificationProfile['required']);

        $rolesToUpdate = $roles;

        $rows[] = [
            'domain' => $domainName,
            'domain_id' => (int) $domain->id,
            'ok' => true,
            'account' => (string) $account['customer_id'],
            'registrar' => (string) $account['registrar'],
            'current_email' => $summary['email'],
            'new_email' => $newContact['email'],
            'verification' => $verificationText,
            'verification_required' => $verificationRequired,
            'whmcs_status' => (string) ($domain->status ?? ''),
            'losing_email' => $losingEmail,
            'gaining_email' => $gainingEmail,
            'roles' => $rolesToUpdate,
            'order_id' => (string) $account['order_id'],
            'contact_ids' => (array) $account['contact_ids'],
            'message' => 'Ready',
        ];
    }

    if (count($accountKeys) > 1) {
        $fatal = true;
        foreach ($rows as &$row) {
            if (!empty($row['ok'])) {
                $row['ok'] = false;
                $row['message'] = 'Domain belongs to a different NEO customer account than other domains in this list.';
            }
        }
        unset($row);
    }

    if ($fatal) {
        return ['ok' => false, 'message' => count($accountKeys) > 1 ? 'WHOIS update stopped: all domains must belong to the same NEO customer account. No changes were submitted.' : 'WHOIS preflight failed. No changes were submitted.', 'rows' => $rows];
    }

    $customerId = '';
    if ($rows) {
        $customerId = (string) ($rows[0]['account'] ?? '');
    }
    return ['ok' => true, 'message' => '', 'customer_id' => $customerId, 'rows' => $rows];
}

function multibulkupdater_whois_preflight_table(array $rows): string
{
    $html = '<div class="mbu-card mbu-preflight-card"><div class="mbu-card-title">WHOIS Preflight</div><div class="mbu-card-body"><div class="table-responsive"><table class="datatable table table-striped"><thead><tr><th>Domain</th><th>NEO Account</th><th>Current Registrant Email</th><th>New Registrant Email</th><th>Verification</th><th>Result</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $status = !empty($row['ok']) ? '<span class="mbu-status-success">Ready</span>' : '<span class="mbu-status-failed">Blocked</span>';
        $html .= '<tr><td>' . multibulkupdater_escape((string) ($row['domain'] ?? '')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['account'] ?? '')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['current_email'] ?? '')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['new_email'] ?? '')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['verification'] ?? '')) . '</td>';
        $html .= '<td>' . $status . '<div class="mbu-table-note">' . multibulkupdater_escape((string) ($row['message'] ?? '')) . '</div></td></tr>';
    }
    $html .= '</tbody></table></div></div></div>';
    return $html;
}

function multibulkupdater_whois_confirm_form(string $moduleLink, string $rawDomains, array $contact, array $roles, bool $optOut, bool $autoResend, array $preflight): string
{
    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="whois"><input type="hidden" name="mbu_step" value="whois_execute">';
    $html .= '<textarea name="domains" class="mbu-hidden">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    foreach ($contact as $key => $value) {
        $html .= '<input type="hidden" name="whois_' . multibulkupdater_escape($key) . '" value="' . multibulkupdater_escape((string) $value) . '">';
    }
    foreach ($roles as $role) {
        $html .= '<input type="hidden" name="role_' . strtolower($role) . '" value="1">';
    }
    if ($optOut) {
        $html .= '<input type="hidden" name="lock_optout" value="1">';
    }
    if ($autoResend) {
        $html .= '<input type="hidden" name="auto_resend" value="1">';
    }
    $html .= '<div class="mbu-card-title">Confirm WHOIS Update</div><div class="mbu-card-body">';
    $html .= multibulkupdater_alert('warning', 'No WHOIS changes have been submitted yet. Review the NEO account and verification recipients below before running the update.');
    $html .= '<div class="mbu-summary"><strong>NEO customer account:</strong> ' . multibulkupdater_escape((string) ($preflight['customer_id'] ?? '')) . '<br><strong>Contact roles:</strong> ' . multibulkupdater_escape(implode(', ', $roles)) . '<br><strong>60-day transfer lock:</strong> ' . ($optOut ? 'Opt out when permitted' : 'Do not opt out') . '<br><strong>Immediate verification resend:</strong> ' . ($autoResend ? 'Yes — after NEO accepts a pending Registrant change' : 'No') . '</div>';
    $html .= multibulkupdater_whois_contact_summary($contact);
    $html .= multibulkupdater_whois_preflight_table($preflight['rows']);
    $html .= '<div class="mbu-actions"><a class="btn btn-default" href="' . multibulkupdater_escape($moduleLink . '&mbu_page=whois') . '">Cancel</a><button type="submit" class="btn btn-primary mbu-submit-button" data-processing-text="Updating WHOIS…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Run WHOIS Update</span></button></div>';
    $html .= '</div></form>';
    return $html;
}

function multibulkupdater_whois_contact_summary(array $contact): string
{
    $name = trim($contact['first_name'] . ' ' . $contact['last_name']);
    $address = array_filter([$contact['address1'], $contact['address2'], $contact['address3'], $contact['city'], $contact['state'], $contact['postcode'], $contact['country']], static fn($v) => trim((string) $v) !== '');
    return '<div class="mbu-summary"><strong>New WHOIS contact:</strong> ' . multibulkupdater_escape($name) . ($contact['company'] !== '' ? ' — ' . multibulkupdater_escape($contact['company']) : '') . '<br><strong>Email:</strong> ' . multibulkupdater_escape($contact['email']) . '<br><strong>Address:</strong> ' . multibulkupdater_escape(implode(', ', $address)) . '<br><strong>Phone:</strong> +' . multibulkupdater_escape($contact['phone_cc']) . ' ' . multibulkupdater_escape($contact['phone']) . '</div>';
}

function multibulkupdater_whois_api_credentials(string $registrar): array
{
    multibulkupdater_whois_load_epp_manager();
    if (!function_exists('dm_epp_find_credentials') || !function_exists('dm_epp_http_request') || !function_exists('dm_epp_api_base')) {
        return ['ok' => false, 'message' => 'NEO credential/API helper is not available.'];
    }
    $credentials = dm_epp_find_credentials($registrar);
    if (!is_array($credentials) || trim((string) ($credentials['authUserId'] ?? '')) === '' || trim((string) ($credentials['apiKey'] ?? '')) === '') {
        return ['ok' => false, 'message' => 'NEO API credentials could not be resolved from the registrar configuration.'];
    }
    return ['ok' => true, 'credentials' => $credentials, 'message' => ''];
}

function multibulkupdater_whois_digits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

function multibulkupdater_whois_response_message($response): string
{
    if (is_array($response)) {
        $parts = [];
        $labels = [
            'message' => 'Message',
            'actionstatusdesc' => 'Status',
            'irtp_status' => 'IRTP Status',
            'losing-foa-status' => 'Losing Registrant Approval',
            'gaining-foa-status' => 'Gaining Registrant Approval',
            'sixty-day-lock-status' => '60-Day Lock',
        ];
        foreach ($labels as $key => $label) {
            if (isset($response[$key]) && trim((string) $response[$key]) !== '') {
                $parts[] = $label . ': ' . trim((string) $response[$key]);
            }
        }
        if ($parts) {
            return implode('; ', $parts);
        }
        $encoded = json_encode($response, JSON_UNESCAPED_SLASHES);
        return $encoded !== false ? substr($encoded, 0, 500) : 'Registrar returned an unrecognized response.';
    }
    if (is_scalar($response)) {
        return trim((string) $response);
    }
    return 'Registrar returned an unrecognized response.';
}

function multibulkupdater_whois_create_neo_contact(array $credentials, string $customerId, array $contact): array
{
    $phone = multibulkupdater_whois_digits((string) $contact['phone']);
    $fax = multibulkupdater_whois_digits((string) $contact['fax']);
    if (strlen($phone) < 4 || strlen($phone) > 12) {
        return ['ok' => false, 'message' => 'Phone Number must contain 4-12 digits for the NEO contact API.'];
    }
    if ($fax !== '' && (strlen($fax) < 4 || strlen($fax) > 12)) {
        return ['ok' => false, 'message' => 'Fax Number must contain 4-12 digits for the NEO contact API.'];
    }

    $params = [
        'auth-userid' => (string) $credentials['authUserId'],
        'api-key' => (string) $credentials['apiKey'],
        'name' => trim((string) $contact['first_name'] . ' ' . (string) $contact['last_name']),
        'email' => (string) $contact['email'],
        'address-line-1' => (string) $contact['address1'],
        'city' => (string) $contact['city'],
        'state' => (string) $contact['state'],
        'country' => (string) $contact['country'],
        'zipcode' => (string) $contact['postcode'],
        'phone-cc' => (string) $contact['phone_cc'],
        'phone' => $phone,
        'customer-id' => $customerId,
        'type' => 'Contact',
    ];
    if (trim((string) $contact['company']) !== '') {
        $params['company'] = (string) $contact['company'];
    }
    if (trim((string) $contact['address2']) !== '') {
        $params['address-line-2'] = (string) $contact['address2'];
    }
    if (trim((string) $contact['address3']) !== '') {
        $params['address-line-3'] = (string) $contact['address3'];
    }
    if ($fax !== '') {
        $params['fax-cc'] = (string) $contact['fax_cc'];
        $params['fax'] = $fax;
    }

    [$ok, $response, $error] = dm_epp_http_request('POST', dm_epp_api_base($credentials) . '/contacts/add.json', $params);
    if (!$ok) {
        return ['ok' => false, 'message' => $error !== '' ? $error : 'NEO could not create the replacement WHOIS contact.'];
    }

    $contactId = '';
    if (is_numeric($response)) {
        $contactId = (string) $response;
    } elseif (is_array($response)) {
        foreach (['entityid', 'contactid', 'contact-id', 'id'] as $key) {
            if (isset($response[$key]) && is_numeric($response[$key])) {
                $contactId = (string) $response[$key];
                break;
            }
        }
    }
    if ($contactId === '' || (int) $contactId <= 0) {
        return ['ok' => false, 'message' => 'NEO created no usable contact ID. Response: ' . multibulkupdater_whois_response_message($response)];
    }

    return ['ok' => true, 'contact_id' => $contactId, 'message' => ''];
}

function multibulkupdater_whois_response_is_pending_irtp($response): bool
{
    if (!is_array($response)) {
        return false;
    }
    foreach (['actionstatusdesc', 'irtp_status', 'irtp-status', 'status'] as $key) {
        $value = strtolower(trim((string) ($response[$key] ?? '')));
        if ($value !== '' && (strpos($value, 'pending registrant approval') !== false || strpos($value, 'pending') !== false)) {
            return true;
        }
    }
    return false;
}

function multibulkupdater_whois_resend_order(array $credentials, string $orderId): array
{
    if ($orderId === '' || !preg_match('/^\d+$/', $orderId) || (int) $orderId <= 0) {
        return ['ok' => false, 'message' => 'NEO order ID is missing or invalid.'];
    }

    [$ok, $response, $error] = dm_epp_http_request('POST', dm_epp_api_base($credentials) . '/domains/irtp/verification/resend.json', [
        'auth-userid' => (string) $credentials['authUserId'],
        'api-key' => (string) $credentials['apiKey'],
        'order-id' => $orderId,
    ]);

    if (!$ok) {
        return ['ok' => false, 'message' => $error !== '' ? $error : multibulkupdater_whois_response_message($response)];
    }
    if (is_array($response) && strcasecmp(trim((string) ($response['status'] ?? '')), 'ERROR') === 0) {
        return ['ok' => false, 'message' => trim((string) ($response['message'] ?? 'NEO rejected the IRTP resend request.'))];
    }

    $detail = multibulkupdater_whois_response_message($response);
    return [
        'ok' => true,
        'message' => ($detail !== '' && $detail !== 'Registrar returned an unrecognized response.')
            ? $detail
            : 'NEO accepted the IRTP resend request for all approval participants.',
    ];
}

function multibulkupdater_whois_resend_verification(array $domains): array
{
    @set_time_limit(0);
    $results = [];

    foreach ($domains as $domainName) {
        try {
            $domain = Capsule::table('tbldomains')->where('domain', $domainName)->first();
            if (!$domain) {
                $results[] = multibulkupdater_result($domainName, false, 'Domain not found in WHMCS.');
                continue;
            }

            $details = multibulkupdater_whois_neo_domain_details($domain, []);
            if (empty($details['ok'])) {
                $results[] = multibulkupdater_result($domainName, false, (string) ($details['message'] ?? 'Could not resolve NEO domain details.'));
                continue;
            }

            $credentialResult = multibulkupdater_whois_api_credentials((string) ($details['registrar'] ?? ''));
            if (empty($credentialResult['ok'])) {
                $results[] = multibulkupdater_result($domainName, false, (string) ($credentialResult['message'] ?? 'NEO API credentials could not be resolved.'));
                continue;
            }

            $resend = multibulkupdater_whois_resend_order((array) $credentialResult['credentials'], (string) ($details['order_id'] ?? ''));
            if (empty($resend['ok'])) {
                $results[] = multibulkupdater_result($domainName, false, 'Verification resend failed: ' . (string) ($resend['message'] ?? 'Unknown NEO error.'));
                continue;
            }

            $results[] = multibulkupdater_result($domainName, true, 'Verification resend requested for both approval participants. NEO: ' . (string) $resend['message']);
        } catch (Throwable $e) {
            $results[] = multibulkupdater_result($domainName, false, $e->getMessage());
        }
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;
    if (function_exists('logActivity')) {
        logActivity('Bulk Domain Manager: IRTP verification resend completed for ' . count($results) . ' domain(s): ' . $successCount . ' successful, ' . $failureCount . ' failed.');
    }
    return $results;
}

function multibulkupdater_whois_execute(array $preflight, array $contact, array $roles, bool $optOut, bool $autoResend): array
{
    @set_time_limit(0);
    $results = [];
    $rows = (array) ($preflight['rows'] ?? []);
    if (!$rows) {
        return [];
    }

    $firstReady = null;
    foreach ($rows as $row) {
        if (!empty($row['ok'])) {
            $firstReady = $row;
            break;
        }
    }
    if (!$firstReady) {
        foreach ($rows as $row) {
            $results[] = multibulkupdater_result((string) ($row['domain'] ?? ''), false, (string) ($row['message'] ?? 'Preflight failed.'));
        }
        return $results;
    }

    $credentialResult = multibulkupdater_whois_api_credentials((string) ($firstReady['registrar'] ?? ''));
    if (empty($credentialResult['ok'])) {
        foreach ($rows as $row) {
            $results[] = multibulkupdater_result((string) ($row['domain'] ?? ''), false, (string) $credentialResult['message']);
        }
        return $results;
    }
    $credentials = (array) $credentialResult['credentials'];

    $created = multibulkupdater_whois_create_neo_contact($credentials, (string) ($preflight['customer_id'] ?? ''), $contact);
    if (empty($created['ok'])) {
        foreach ($rows as $row) {
            $results[] = multibulkupdater_result((string) ($row['domain'] ?? ''), false, 'WHOIS update stopped before changing any domain: ' . (string) $created['message']);
        }
        return $results;
    }
    $newContactId = (string) $created['contact_id'];

    $roleParamMap = [
        'Registrant' => 'reg-contact-id',
        'Admin' => 'admin-contact-id',
        'Tech' => 'tech-contact-id',
        'Billing' => 'billing-contact-id',
    ];

    foreach ($rows as $row) {
        if (empty($row['ok'])) {
            $results[] = multibulkupdater_result((string) ($row['domain'] ?? ''), false, (string) ($row['message'] ?? 'Preflight failed.'));
            continue;
        }

        $ids = (array) ($row['contact_ids'] ?? []);
        $params = [
            'auth-userid' => (string) $credentials['authUserId'],
            'api-key' => (string) $credentials['apiKey'],
            'order-id' => (string) ($row['order_id'] ?? ''),
        ];
        foreach ($roleParamMap as $role => $param) {
            $id = in_array($role, $roles, true) ? $newContactId : (string) ($ids[$role] ?? '');
            if ($id === '' || !preg_match('/^\d+$/', $id) || (int) $id <= 0) {
                $results[] = multibulkupdater_result((string) $row['domain'], false, 'NEO did not provide a valid ' . $role . ' contact ID.');
                continue 2;
            }
            $params[$param] = $id;
        }
        if ($optOut && !empty($row['verification_required']) && in_array('Registrant', $roles, true)) {
            $params['sixty-day-lock-optout'] = 'true';
        }

        try {
            [$ok, $response, $error] = dm_epp_http_request('POST', dm_epp_api_base($credentials) . '/domains/modify-contact.json', $params);
            if (!$ok) {
                $message = $error !== '' ? $error : multibulkupdater_whois_response_message($response);
                $results[] = multibulkupdater_result((string) $row['domain'], false, $message);
                continue;
            }

            $verificationLabel = trim((string) ($row['verification'] ?? 'No'));
            if ($verificationLabel === '') {
                $verificationLabel = 'No';
            }
            if (!empty($row['verification_required'])) {
                $message = 'WHOIS contact update submitted. Verification: ' . $verificationLabel . '. Both approval links must be completed.';
            } else {
                $message = 'WHOIS information updated. Verification: ' . $verificationLabel . '.';
            }
            $apiDetail = multibulkupdater_whois_response_message($response);
            if ($apiDetail !== '' && $apiDetail !== 'Registrar returned an unrecognized response.') {
                $message .= ' NEO: ' . $apiDetail;
            }
            if ($autoResend && !empty($row['verification_required']) && multibulkupdater_whois_response_is_pending_irtp($response)) {
                $resend = multibulkupdater_whois_resend_order($credentials, (string) ($row['order_id'] ?? ''));
                if (!empty($resend['ok'])) {
                    $message .= ' Immediate resend: Success — NEO was asked to resend both approval emails now.';
                } else {
                    $message .= ' Immediate resend: Failed — ' . (string) ($resend['message'] ?? 'Unknown NEO error.') . ' The WHOIS change itself was still accepted.';
                }
            } elseif ($autoResend && !empty($row['verification_required'])) {
                $message .= ' Immediate resend: Not attempted because NEO did not report a pending IRTP approval state.';
            }
            $results[] = multibulkupdater_result((string) $row['domain'], true, $message);
        } catch (Throwable $e) {
            $results[] = multibulkupdater_result((string) $row['domain'], false, $e->getMessage());
        }
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;
    if (function_exists('logActivity')) {
        logActivity('Bulk Domain Manager: Update WHOIS completed for ' . count($results) . ' domain(s): ' . $successCount . ' successful, ' . $failureCount . ' failed.');
    }
    return $results;
}



function multibulkupdater_add_output(array $vars): void
{
    @set_time_limit(0);
    $moduleLink = (string) ($vars['modulelink'] ?? 'addonmodules.php?module=multibulkupdater');
    $defaultRegPeriod = multibulkupdater_default_registration_period($vars);
    $step = (string) ($_POST['mbu_step'] ?? 'start');
    $errors = [];
    $storedDomains = (string) ($_SESSION['multibulkupdater_add_domains'] ?? '');
    $rawDomains = isset($_POST['domains']) ? (string) $_POST['domains'] : $storedDomains;
    $domains = multibulkupdater_parse_domains($rawDomains);
    $destination = trim((string) ($_POST['add_destination'] ?? ''));
    $registrar = strtolower(trim((string) ($_POST['add_registrar'] ?? '')));
    $registrars = multibulkupdater_add_registrars();
    $presetGroups = multibulkupdater_move_preset_groups();
    $whmcsPresets = is_array($presetGroups['whmcs'] ?? null) ? $presetGroups['whmcs'] : [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_token('WHMCS.admin.default');
        $_SESSION['multibulkupdater_add_domains'] = $rawDomains;
    }

    echo multibulkupdater_styles();
    echo '<div class="mbu-wrap">';
    echo '<div class="mbu-header"><div><h2>Bulk Domain Manager</h2>';
    echo '<p>Add existing registrar domains to a WHMCS account, assign the registrar, then run the existing Sync Domain workflow.</p></div></div>';
    echo multibulkupdater_action_switcher($moduleLink, $rawDomains, 'add_domains');

    if ($step === 'add_confirm') {
        $years = multibulkupdater_add_registration_period($_POST, $defaultRegPeriod, $errors);
        if (!$domains) {
            $errors[] = 'Enter at least one domain.';
        }
        if ($destination === '') {
            $errors[] = 'Enter or select a destination WHMCS account.';
        }
        if ($registrar === '') {
            $errors[] = 'Select a registrar.';
        } elseif (!array_key_exists($registrar, $registrars)) {
            $errors[] = 'Select an active registrar.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
            echo multibulkupdater_add_start_form($moduleLink, $rawDomains, $whmcsPresets, $registrars, $_POST, $defaultRegPeriod);
        } else {
            $preflight = multibulkupdater_add_preflight($domains, $destination, $registrar);
            if (empty($preflight['ok'])) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape((string) ($preflight['message'] ?? 'The domain import preflight failed.')));
                if (!empty($preflight['rows'])) {
                    echo multibulkupdater_add_preflight_table((array) $preflight['rows']);
                }
                echo multibulkupdater_add_start_form($moduleLink, $rawDomains, $whmcsPresets, $registrars, $_POST, $defaultRegPeriod);
            } else {
                if (!empty($preflight['message'])) {
                    echo multibulkupdater_alert('warning', multibulkupdater_escape((string) $preflight['message']));
                }
                echo multibulkupdater_add_confirm_form($moduleLink, $rawDomains, $destination, $registrar, $years, $preflight);
            }
        }
    } elseif ($step === 'add_execute') {
        $years = multibulkupdater_add_registration_period($_POST, $defaultRegPeriod, $errors);
        if (!$domains) {
            $errors[] = 'No domains were submitted.';
        }
        if ($destination === '') {
            $errors[] = 'No destination WHMCS account was submitted.';
        }
        if ($registrar === '') {
            $errors[] = 'No registrar was submitted.';
        } elseif (!array_key_exists($registrar, $registrars)) {
            $errors[] = 'The selected registrar is not active.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
            echo multibulkupdater_add_start_form($moduleLink, $rawDomains, $whmcsPresets, $registrars, $_POST, $defaultRegPeriod);
        } else {
            // Re-run destination, registrar, payment-method, and duplicate checks immediately before execution.
            $preflight = multibulkupdater_add_preflight($domains, $destination, $registrar);
            if (empty($preflight['ok'])) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape((string) ($preflight['message'] ?? 'The domain import preflight failed.')));
                if (!empty($preflight['rows'])) {
                    echo multibulkupdater_add_preflight_table((array) $preflight['rows']);
                }
                echo multibulkupdater_add_start_form($moduleLink, $rawDomains, $whmcsPresets, $registrars, $_POST, $defaultRegPeriod);
            } else {
                $results = multibulkupdater_add_execute($domains, $preflight, $years);
                echo multibulkupdater_add_results($moduleLink . '&mbu_page=add', $results);
            }
        }
    } else {
        echo multibulkupdater_add_start_form($moduleLink, $rawDomains, $whmcsPresets, $registrars, [], $defaultRegPeriod);
    }

    echo multibulkupdater_submit_script();
    echo '</div>';
}

function multibulkupdater_add_registrars(): array
{
    $registrars = [];

    try {
        $rows = Capsule::table('tblregistrars')
            ->select('registrar')
            ->distinct()
            ->orderBy('registrar')
            ->get();

        foreach ($rows as $row) {
            $module = strtolower(trim((string) ($row->registrar ?? '')));
            if ($module !== '') {
                $registrars[$module] = multibulkupdater_add_registrar_label($module);
            }
        }
    } catch (Throwable $e) {
        // Fall through to the Local API fallback below.
    }

    if (!$registrars) {
        try {
            $response = localAPI('GetRegistrars', []);
            $source = is_array($response) ? ($response['registrars'] ?? []) : [];
            $collect = static function ($value) use (&$collect, &$registrars): void {
                if (is_string($value)) {
                    $module = strtolower(trim($value));
                    if ($module !== '' && preg_match('/^[a-z][a-z0-9]*$/', $module)) {
                        $registrars[$module] = multibulkupdater_add_registrar_label($module);
                    }
                    return;
                }
                if (!is_array($value)) {
                    return;
                }
                if (!empty($value['module']) && is_string($value['module'])) {
                    $module = strtolower(trim($value['module']));
                    if ($module !== '') {
                        $registrars[$module] = multibulkupdater_add_registrar_label($module);
                    }
                    return;
                }
                foreach ($value as $nested) {
                    $collect($nested);
                }
            };
            $collect($source);
        } catch (Throwable $e) {
            // The empty list is handled by the form/preflight.
        }
    }

    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    foreach (array_keys($registrars) as $module) {
        if (!preg_match('/^[a-z][a-z0-9]*$/', $module)
            || !is_file($root . '/modules/registrars/' . $module . '/' . $module . '.php')
        ) {
            unset($registrars[$module]);
        }
    }

    asort($registrars, SORT_NATURAL | SORT_FLAG_CASE);
    return $registrars;
}

function multibulkupdater_add_registrar_label(string $module): string
{
    $labels = [
        'netearthone' => 'NetEarthOne',
        'netearthonercm' => 'NetEarthOne RCM',
        'resellerclub' => 'ResellerClub',
        'resellerclubrcm' => 'ResellerClub RCM',
    ];

    if (isset($labels[$module])) {
        return $labels[$module];
    }

    return ucwords(str_replace(['_', '-'], ' ', $module));
}

function multibulkupdater_add_registration_period(array $source, int $defaultRegPeriod, array &$errors): int
{
    $raw = trim((string) ($source['add_regperiod'] ?? $defaultRegPeriod));
    if ($raw === '' || !ctype_digit($raw)) {
        $errors[] = 'Select a valid Registration Period.';
        return $defaultRegPeriod;
    }

    $years = (int) $raw;
    if ($years < 1 || $years > 10) {
        $errors[] = 'Registration Period must be between 1 and 10 years.';
        return $defaultRegPeriod;
    }

    return $years;
}

function multibulkupdater_add_payment_method(int $clientId): array
{
    $defaultGateway = '';
    try {
        $client = Capsule::table('tblclients')->where('id', $clientId)->first();
        $defaultGateway = strtolower(trim((string) ($client->defaultgateway ?? '')));
    } catch (Throwable $e) {
        return ['ok' => false, 'module' => '', 'message' => 'WHMCS client payment-method lookup failed: ' . $e->getMessage()];
    }

    try {
        $response = localAPI('GetPaymentMethods', []);
    } catch (Throwable $e) {
        return ['ok' => false, 'module' => '', 'message' => 'WHMCS payment-method lookup failed: ' . $e->getMessage()];
    }

    $items = is_array($response) ? ($response['paymentmethods']['paymentmethod'] ?? []) : [];
    if (is_array($items) && isset($items['module'])) {
        $items = [$items];
    }

    $modules = [];
    foreach ((array) $items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $module = strtolower(trim((string) ($item['module'] ?? '')));
        if ($module !== '') {
            $modules[$module] = $module;
        }
    }

    if ($defaultGateway !== '' && isset($modules[$defaultGateway])) {
        return ['ok' => true, 'module' => $defaultGateway, 'message' => ''];
    }

    if ($modules) {
        $module = (string) reset($modules);
        return ['ok' => true, 'module' => $module, 'message' => ''];
    }

    return ['ok' => false, 'module' => '', 'message' => 'No active WHMCS payment method is available for the internal no-invoice domain order.'];
}

function multibulkupdater_add_preflight(array $domains, string $destination, string $registrar): array
{
    $target = multibulkupdater_move_whmcs_client($destination);
    if (empty($target['ok'])) {
        return ['ok' => false, 'destination' => [], 'registrar' => $registrar, 'paymentmethod' => '', 'rows' => [], 'message' => (string) ($target['message'] ?? 'Destination WHMCS account was not found.')];
    }

    if (strcasecmp((string) ($target['status'] ?? ''), 'Closed') === 0) {
        return ['ok' => false, 'destination' => $target, 'registrar' => $registrar, 'paymentmethod' => '', 'rows' => [], 'message' => 'The destination WHMCS account is Closed and cannot receive a new domain order.'];
    }

    $registrars = multibulkupdater_add_registrars();
    if (!isset($registrars[$registrar])) {
        return ['ok' => false, 'destination' => $target, 'registrar' => $registrar, 'paymentmethod' => '', 'rows' => [], 'message' => 'The selected registrar is not active in WHMCS.'];
    }

    $payment = multibulkupdater_add_payment_method((int) $target['id']);
    if (empty($payment['ok'])) {
        return ['ok' => false, 'destination' => $target, 'registrar' => $registrar, 'paymentmethod' => '', 'rows' => [], 'message' => (string) ($payment['message'] ?? 'WHMCS payment-method lookup failed.')];
    }

    $rows = [];
    $readyCount = 0;
    $existingCount = 0;

    foreach ($domains as $domainName) {
        $domainName = strtolower(rtrim(trim((string) $domainName), '.'));
        if ($domainName === '' || !multibulkupdater_valid_hostname($domainName)) {
            $rows[] = [
                'domain' => $domainName,
                'ok' => false,
                'existing' => false,
                'destination_label' => (string) $target['label'],
                'registrar_label' => (string) $registrars[$registrar],
                'message' => 'Invalid domain name.',
            ];
            continue;
        }

        try {
            $existing = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $rows[] = [
                'domain' => $domainName,
                'ok' => false,
                'existing' => false,
                'destination_label' => (string) $target['label'],
                'registrar_label' => (string) $registrars[$registrar],
                'message' => 'WHMCS database lookup failed: ' . $e->getMessage(),
            ];
            continue;
        }

        if ($existing) {
            $ownerId = (int) ($existing->userid ?? 0);
            $ownerLabel = $ownerId > 0 ? multibulkupdater_move_whmcs_client_label($ownerId) : 'Unknown account';
            $rows[] = [
                'domain' => $domainName,
                'ok' => false,
                'existing' => true,
                'destination_label' => (string) $target['label'],
                'registrar_label' => (string) $registrars[$registrar],
                'message' => 'Already exists in WHMCS as Domain #' . (int) ($existing->id ?? 0) . ' under ' . $ownerLabel . '. It will be skipped.',
            ];
            $existingCount++;
            continue;
        }

        $rows[] = [
            'domain' => $domainName,
            'ok' => true,
            'existing' => false,
            'destination_label' => (string) $target['label'],
            'registrar_label' => (string) $registrars[$registrar],
            'message' => 'Ready to add and sync.',
        ];
        $readyCount++;
    }

    if ($readyCount < 1) {
        return [
            'ok' => false,
            'destination' => $target,
            'registrar' => $registrar,
            'registrar_label' => (string) $registrars[$registrar],
            'paymentmethod' => (string) $payment['module'],
            'rows' => $rows,
            'message' => 'No submitted domains are eligible to be added.',
        ];
    }

    $message = $existingCount > 0
        ? $existingCount . ' domain' . ($existingCount === 1 ? '' : 's') . ' already exist in WHMCS and will be skipped.'
        : '';

    return [
        'ok' => true,
        'destination' => $target,
        'registrar' => $registrar,
        'registrar_label' => (string) $registrars[$registrar],
        'paymentmethod' => (string) $payment['module'],
        'rows' => $rows,
        'ready_count' => $readyCount,
        'existing_count' => $existingCount,
        'message' => $message,
    ];
}

function multibulkupdater_add_start_form(string $moduleLink, string $rawDomains, array $presets, array $registrars, array $source, int $defaultRegPeriod = 1): string
{
    $destination = trim((string) ($source['add_destination'] ?? ''));
    $selectedRegistrar = strtolower(trim((string) ($source['add_registrar'] ?? '')));
    $periodRaw = trim((string) ($source['add_regperiod'] ?? $defaultRegPeriod));
    $years = ctype_digit($periodRaw) ? max(1, min(10, (int) $periodRaw)) : $defaultRegPeriod;

    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card" id="mbu-add-form">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="add">';
    $html .= '<input type="hidden" name="mbu_step" id="mbu-add-step" value="start">';
    $html .= '<div class="mbu-card-title">Add Existing Domains</div><div class="mbu-card-body">';
    $html .= '<label for="mbu-add-domains">Domains</label>';
    $html .= '<textarea id="mbu-add-domains" name="domains" rows="10" placeholder="example.com&#10;example.net">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<div class="mbu-help">Enter domains that already exist at the selected registrar but are not yet in WHMCS. Existing WHMCS domains are detected and skipped.</div>';

    $html .= '<div class="mbu-fields"><h4>WHMCS Destination</h4>';
    $html .= '<div class="mbu-move-presets"><span class="mbu-preset-label">Apply preset:</span>';
    if ($presets) {
        foreach ($presets as $key => $preset) {
            $presetDestination = (string) ($preset['destination'] ?? '');
            $name = (string) ($preset['name'] ?? $key);
            $html .= '<button type="button" class="btn mbu-add-preset" data-destination="' . multibulkupdater_escape($presetDestination) . '">' . multibulkupdater_escape($name) . '</button>';
        }
    } else {
        $html .= '<span class="mbu-help-inline">No saved WHMCS account presets yet. WHMCS presets from Move Domains will appear here.</span>';
    }
    $html .= '</div>';
    $html .= '<div class="mbu-grid mbu-move-grid"><div><label for="mbu-add-destination">Destination Account <span class="mbu-required" aria-label="required">*</span></label>';
    $html .= '<input id="mbu-add-destination" type="text" name="add_destination" value="' . multibulkupdater_escape($destination) . '" autocomplete="off" placeholder="Client ID or account email"></div>';
    $html .= '<div><label for="mbu-add-registrar">Registrar <span class="mbu-required" aria-label="required">*</span></label><select id="mbu-add-registrar" name="add_registrar">';
    $html .= '<option value="">Select registrar…</option>';
    foreach ($registrars as $module => $label) {
        $html .= '<option value="' . multibulkupdater_escape((string) $module) . '"' . ($selectedRegistrar === (string) $module ? ' selected' : '') . '>' . multibulkupdater_escape((string) $label) . '</option>';
    }
    $html .= '</select></div></div>';
    $html .= '<div class="mbu-help">Enter the destination Client ID or account email. The account is resolved and shown for review before anything is added.</div>';
    if (!$registrars) {
        $html .= multibulkupdater_alert('danger', 'No active WHMCS registrar modules were found.');
    }
    $html .= '</div>';

    $html .= '<div class="mbu-fields"><h4>WHMCS Domain Settings</h4>';
    $html .= '<div class="mbu-grid"><div><label for="mbu-add-regperiod">Registration Period</label><select id="mbu-add-regperiod" name="add_regperiod">';
    for ($year = 1; $year <= 10; $year++) {
        $html .= '<option value="' . $year . '"' . ($years === $year ? ' selected' : '') . '>' . $year . ' Year' . ($year === 1 ? '' : 's') . '</option>';
    }
    $html .= '</select></div></div>';
    $html .= '<div class="mbu-help">BDM creates a WHMCS-native internal domain order with no invoice, no payment/charge attempt, and no client email. It accepts the order without sending a registrar registration/transfer request or auto-setup, assigns the selected registrar, then runs Sync Domain.</div>';
    $html .= '</div>';

    $html .= '<div class="mbu-actions"><button type="submit" data-mbu-step="add_confirm" class="btn btn-primary mbu-submit-button" data-processing-text="Checking Domains…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Review Domain Import</span></button></div>';
    $html .= '</div></form>';
    $html .= multibulkupdater_add_script();
    return $html;
}

function multibulkupdater_add_script(): string
{
    return <<<'HTML'
<script>
(function () {
    'use strict';
    const form = document.getElementById('mbu-add-form');
    const destination = document.getElementById('mbu-add-destination');
    const step = document.getElementById('mbu-add-step');
    if (!form || !destination || !step) {
        return;
    }
    const presets = form.querySelectorAll('.mbu-add-preset');
    function clearPreset() {
        presets.forEach(function (button) { button.classList.remove('mbu-preset-active'); });
    }
    presets.forEach(function (button) {
        button.addEventListener('click', function () {
            destination.value = button.dataset.destination || '';
            clearPreset();
            button.classList.add('mbu-preset-active');
        });
    });
    destination.addEventListener('input', clearPreset);
    form.addEventListener('submit', function (event) {
        const submitter = event.submitter;
        if (submitter && submitter.dataset && submitter.dataset.mbuStep) {
            step.value = submitter.dataset.mbuStep;
        }
    });
}());
</script>
HTML;
}

function multibulkupdater_add_preflight_table(array $rows): string
{
    $html = '<div class="mbu-card mbu-preflight-card"><div class="mbu-card-title">Domain Import Preflight</div><div class="mbu-card-body"><div class="table-responsive"><table class="datatable table table-striped"><thead><tr>';
    $html .= '<th>Domain</th><th>Destination Account</th><th>Registrar</th><th>Details</th><th>Status</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $ready = !empty($row['ok']);
        $existing = !empty($row['existing']);
        $status = $ready
            ? '<span class="mbu-status-success">Ready</span>'
            : ($existing ? '<span class="mbu-status-neutral">Already Exists</span>' : '<span class="mbu-status-failed">Blocked</span>');
        $html .= '<tr><td>' . multibulkupdater_escape((string) ($row['domain'] ?? '')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['destination_label'] ?? '—')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['registrar_label'] ?? '—')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['message'] ?? '')) . '</td>';
        $html .= '<td>' . $status . '</td></tr>';
    }
    $html .= '</tbody></table></div></div></div>';
    return $html;
}

function multibulkupdater_add_confirm_form(string $moduleLink, string $rawDomains, string $destination, string $registrar, int $years, array $preflight): string
{
    $target = (array) ($preflight['destination'] ?? []);
    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="add"><input type="hidden" name="mbu_step" value="add_execute">';
    $html .= '<input type="hidden" name="add_destination" value="' . multibulkupdater_escape($destination) . '">';
    $html .= '<input type="hidden" name="add_registrar" value="' . multibulkupdater_escape($registrar) . '">';
    $html .= '<input type="hidden" name="add_regperiod" value="' . (int) $years . '">';
    $html .= '<textarea name="domains" class="mbu-hidden">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<div class="mbu-card-title">Confirm Domain Import</div><div class="mbu-card-body">';
    $html .= multibulkupdater_alert('warning', 'This imports existing domains into WHMCS with no invoice, no payment/charge attempt, no client email, no registrar registration/transfer request, and no auto-setup. WHMCS creates and accepts an internal domain order only so the domain record is created natively, then BDM runs Sync Domain.');
    $html .= '<div class="mbu-summary"><strong>Destination:</strong> ' . multibulkupdater_escape((string) ($target['label'] ?? $destination));
    $html .= '<br><strong>Registrar:</strong> ' . multibulkupdater_escape((string) ($preflight['registrar_label'] ?? $registrar));
    $html .= '<br><strong>Registration Period:</strong> ' . (int) $years . ' Year' . ($years === 1 ? '' : 's');
    $html .= '<br><strong>Eligible to Add:</strong> ' . (int) ($preflight['ready_count'] ?? 0);
    if (!empty($preflight['existing_count'])) {
        $html .= ' &nbsp; <strong>Already in WHMCS:</strong> ' . (int) $preflight['existing_count'] . ' (skipped)';
    }
    $html .= '</div>';
    $html .= multibulkupdater_add_preflight_table((array) ($preflight['rows'] ?? []));
    $html .= '<div class="mbu-actions"><a class="btn btn-default" href="' . multibulkupdater_escape($moduleLink . '&mbu_page=add') . '">Cancel</a>';
    $html .= '<button type="submit" class="btn btn-primary mbu-submit-button" data-processing-text="Adding Domains…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Add &amp; Sync Domains</span></button></div>';
    $html .= '</div></form>';
    return $html;
}

function multibulkupdater_add_domain_id(array $response, string $domainName, int $clientId): int
{
    $raw = trim((string) ($response['domainids'] ?? ''));
    if ($raw !== '') {
        foreach (preg_split('/[^0-9]+/', $raw) ?: [] as $part) {
            if ($part !== '' && ctype_digit($part) && (int) $part > 0) {
                return (int) $part;
            }
        }
    }

    try {
        $domain = Capsule::table('tbldomains')
            ->where('userid', $clientId)
            ->where('domain', $domainName)
            ->orderByDesc('id')
            ->first();
        return $domain ? (int) ($domain->id ?? 0) : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

function multibulkupdater_add_rollback_order(int $orderId): string
{
    if ($orderId < 1) {
        return 'No WHMCS order was available to roll back.';
    }

    try {
        $cancel = localAPI('CancelOrder', ['orderid' => $orderId, 'noemail' => true]);
    } catch (Throwable $e) {
        return 'Order #' . $orderId . ' could not be cancelled for rollback: ' . $e->getMessage();
    }

    if (!is_array($cancel) || strtolower((string) ($cancel['result'] ?? '')) !== 'success') {
        $message = is_array($cancel) ? trim((string) ($cancel['message'] ?? $cancel['error'] ?? '')) : '';
        return 'Order #' . $orderId . ' could not be cancelled for rollback' . ($message !== '' ? ': ' . $message : '.');
    }

    try {
        $delete = localAPI('DeleteOrder', ['orderid' => $orderId]);
    } catch (Throwable $e) {
        return 'Order #' . $orderId . ' was cancelled but could not be deleted: ' . $e->getMessage();
    }

    if (is_array($delete) && strtolower((string) ($delete['result'] ?? '')) === 'success') {
        return 'The incomplete WHMCS order was rolled back.';
    }

    $message = is_array($delete) ? trim((string) ($delete['message'] ?? $delete['error'] ?? '')) : '';
    return 'Order #' . $orderId . ' was cancelled but could not be deleted' . ($message !== '' ? ': ' . $message : '.');
}


function multibulkupdater_add_billing_guard(int $orderId, array $addResponse): array
{
    $invoiceId = (int) ($addResponse['invoiceid'] ?? 0);

    try {
        $order = Capsule::table('tblorders')->where('id', $orderId)->first();
        if ($order && (int) ($order->invoiceid ?? 0) > 0) {
            $invoiceId = (int) $order->invoiceid;
        }
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'invoice_id' => $invoiceId,
            'has_transaction' => false,
            'message' => 'The no-billing safety check could not verify the order: ' . $e->getMessage(),
        ];
    }

    if ($invoiceId < 1) {
        return ['ok' => true, 'invoice_id' => 0, 'has_transaction' => false, 'message' => ''];
    }

    $hasTransaction = false;
    try {
        $hasTransaction = Capsule::table('tblaccounts')->where('invoiceid', $invoiceId)->exists();
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'invoice_id' => $invoiceId,
            'has_transaction' => false,
            'message' => 'WHMCS unexpectedly created Invoice #' . $invoiceId . ', and BDM could not verify whether any payment transaction exists: ' . $e->getMessage(),
        ];
    }

    if ($hasTransaction) {
        return [
            'ok' => false,
            'invoice_id' => $invoiceId,
            'has_transaction' => true,
            'message' => 'WHMCS unexpectedly created Invoice #' . $invoiceId . ' and a payment transaction is attached. BDM stopped before accepting the order. Manual review is required; no automatic deletion/refund was attempted.',
        ];
    }

    return [
        'ok' => false,
        'invoice_id' => $invoiceId,
        'has_transaction' => false,
        'message' => 'WHMCS unexpectedly created Invoice #' . $invoiceId . ' even though noinvoice=true was requested. BDM stopped before accepting the order.',
    ];
}

function multibulkupdater_add_cleanup_unexpected_invoice(int $invoiceId): string
{
    if ($invoiceId < 1) {
        return '';
    }

    try {
        $delete = localAPI('DeleteInvoice', ['invoiceid' => $invoiceId]);
    } catch (Throwable $e) {
        return ' Unexpected Invoice #' . $invoiceId . ' could not be deleted: ' . $e->getMessage();
    }

    if (is_array($delete) && strtolower((string) ($delete['result'] ?? '')) === 'success') {
        return ' Unexpected Invoice #' . $invoiceId . ' was deleted.';
    }

    $message = is_array($delete) ? trim((string) ($delete['message'] ?? $delete['error'] ?? '')) : '';
    return ' Unexpected Invoice #' . $invoiceId . ' could not be deleted' . ($message !== '' ? ': ' . $message : '.');
}

function multibulkupdater_add_execute(array $domains, array $preflight, int $years): array
{
    @set_time_limit(0);
    $results = [];
    $destination = (array) ($preflight['destination'] ?? []);
    $clientId = (int) ($destination['id'] ?? 0);
    $registrar = strtolower(trim((string) ($preflight['registrar'] ?? '')));
    $paymentMethod = trim((string) ($preflight['paymentmethod'] ?? ''));
    $years = max(1, min(10, $years));

    foreach ($domains as $domainName) {
        $domainName = strtolower(rtrim(trim((string) $domainName), '.'));

        try {
            $existing = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => false,
                'message' => 'WHMCS database lookup failed: ' . $e->getMessage(),
                'domain_id' => 0,
                'client_id' => $clientId,
                'registrar' => $registrar,
            ];
            continue;
        }

        if ($existing) {
            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => true,
                'message' => 'Already exists in WHMCS as Domain #' . (int) ($existing->id ?? 0) . '; no duplicate was created.',
                'domain_id' => (int) ($existing->id ?? 0),
                'client_id' => (int) ($existing->userid ?? 0),
                'registrar' => (string) ($existing->registrar ?? ''),
            ];
            continue;
        }

        try {
            $add = localAPI('AddOrder', [
                'clientid' => $clientId,
                'paymentmethod' => $paymentMethod,
                'domain' => [$domainName],
                'domaintype' => ['register'],
                'regperiod' => [$years],
                'noinvoice' => true,
                'noinvoiceemail' => true,
                'noemail' => true,
            ]);
        } catch (Throwable $e) {
            $add = ['result' => 'error', 'message' => $e->getMessage()];
        }

        if (!is_array($add) || strtolower((string) ($add['result'] ?? '')) !== 'success') {
            $message = is_array($add)
                ? trim((string) ($add['message'] ?? $add['error'] ?? 'WHMCS could not create the domain order.'))
                : 'WHMCS returned an invalid AddOrder response.';
            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => false,
                'message' => $message !== '' ? $message : 'WHMCS could not create the domain order.',
                'domain_id' => 0,
                'client_id' => $clientId,
                'registrar' => $registrar,
            ];
            continue;
        }

        $orderId = (int) ($add['orderid'] ?? 0);
        $domainId = multibulkupdater_add_domain_id($add, $domainName, $clientId);
        if ($orderId < 1 || $domainId < 1) {
            $rollback = $orderId > 0 ? multibulkupdater_add_rollback_order($orderId) : 'No order ID was returned for rollback.';
            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => false,
                'message' => 'WHMCS created an incomplete domain order but did not return a usable Domain ID. ' . $rollback,
                'domain_id' => $domainId,
                'client_id' => $clientId,
                'registrar' => $registrar,
            ];
            continue;
        }

        $billingGuard = multibulkupdater_add_billing_guard($orderId, $add);
        if (empty($billingGuard['ok'])) {
            $guardMessage = (string) ($billingGuard['message'] ?? 'The no-billing safety check failed.');
            $invoiceId = (int) ($billingGuard['invoice_id'] ?? 0);
            $hasTransaction = !empty($billingGuard['has_transaction']);

            if (!$hasTransaction) {
                $rollback = multibulkupdater_add_rollback_order($orderId);
                $invoiceCleanup = multibulkupdater_add_cleanup_unexpected_invoice($invoiceId);
                $guardMessage .= ' ' . $rollback . $invoiceCleanup;
            }

            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => false,
                'message' => trim($guardMessage),
                'domain_id' => $hasTransaction ? $domainId : 0,
                'client_id' => $clientId,
                'registrar' => $registrar,
                'order_id' => $orderId,
                'invoice_id' => $invoiceId,
            ];
            continue;
        }

        try {
            $accept = localAPI('AcceptOrder', [
                'orderid' => $orderId,
                'registrar' => $registrar,
                'sendregistrar' => false,
                'autosetup' => false,
                'sendemail' => false,
            ]);
        } catch (Throwable $e) {
            $accept = ['result' => 'error', 'message' => $e->getMessage()];
        }

        if (!is_array($accept) || strtolower((string) ($accept['result'] ?? '')) !== 'success') {
            $acceptMessage = is_array($accept)
                ? trim((string) ($accept['message'] ?? $accept['error'] ?? 'WHMCS could not accept the internal domain order.'))
                : 'WHMCS returned an invalid AcceptOrder response.';
            $rollback = multibulkupdater_add_rollback_order($orderId);
            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => false,
                'message' => ($acceptMessage !== '' ? $acceptMessage : 'WHMCS could not accept the internal domain order.') . ' ' . $rollback,
                'domain_id' => 0,
                'client_id' => $clientId,
                'registrar' => $registrar,
            ];
            continue;
        }

        $updateWarning = '';
        try {
            $update = localAPI('UpdateClientDomain', [
                'domainid' => $domainId,
                'registrar' => $registrar,
                'regperiod' => $years,
                'status' => 'Active',
            ]);
        } catch (Throwable $e) {
            $update = ['result' => 'error', 'message' => $e->getMessage()];
        }

        if (!is_array($update) || strtolower((string) ($update['result'] ?? '')) !== 'success') {
            $updateWarning = is_array($update)
                ? trim((string) ($update['message'] ?? $update['error'] ?? 'WHMCS post-import domain update returned an error.'))
                : 'WHMCS returned an invalid post-import domain update response.';
        }

        try {
            $domain = Capsule::table('tbldomains')->where('id', $domainId)->first();
        } catch (Throwable $e) {
            $domain = null;
            $updateWarning = trim($updateWarning . ' Final WHMCS domain verification failed: ' . $e->getMessage());
        }

        $verified = $domain
            && (int) ($domain->userid ?? 0) === $clientId
            && strcasecmp((string) ($domain->registrar ?? ''), $registrar) === 0;

        if (!$verified) {
            $results[] = [
                'domain' => $domainName,
                'success' => false,
                'skipped' => false,
                'message' => 'The WHMCS domain record was created, but its destination account or registrar could not be verified. Order #' . $orderId . ' / Domain #' . $domainId . ' requires review.' . ($updateWarning !== '' ? ' ' . $updateWarning : ''),
                'domain_id' => $domainId,
                'client_id' => $clientId,
                'registrar' => $registrar,
                'order_id' => $orderId,
            ];
            continue;
        }

        $message = 'Added as Domain #' . $domainId . ' via internal Order #' . $orderId . '. No invoice or payment charge was created; registrar registration was not sent. Sync queued.';
        if ($updateWarning !== '') {
            $message .= ' Post-import note: ' . $updateWarning;
        }

        $results[] = [
            'domain' => $domainName,
            'success' => true,
            'skipped' => false,
            'message' => $message,
            'domain_id' => $domainId,
            'client_id' => $clientId,
            'registrar' => $registrar,
            'order_id' => $orderId,
        ];
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => !empty($row['success'])));
    $skippedCount = count(array_filter($results, static fn(array $row): bool => !empty($row['skipped'])));
    $failureCount = count($results) - $successCount - $skippedCount;
    if (function_exists('logActivity')) {
        logActivity(
            'Bulk Domain Manager: Add Existing Domains completed for ' . count($results) . ' domain(s): '
            . $successCount . ' added, ' . $skippedCount . ' skipped, ' . $failureCount . ' failed. '
            . 'Destination client #' . $clientId . ', registrar ' . $registrar . '. Successful additions queued for Sync Domain.'
        );
    }

    return $results;
}

function multibulkupdater_add_results(string $moduleLink, array $results): string
{
    $addedCount = count(array_filter($results, static fn(array $row): bool => !empty($row['success'])));
    $skippedCount = count(array_filter($results, static fn(array $row): bool => !empty($row['skipped'])));
    $failureCount = count($results) - $addedCount - $skippedCount;
    $syncItems = [];

    $html = '<div class="mbu-card"><div class="mbu-card-title">Add Existing Domains Results</div><div class="mbu-card-body">';
    $html .= '<div class="mbu-result-summary"><span class="mbu-success">' . $addedCount . ' added</span>';
    $html .= '<span>' . $skippedCount . ' skipped</span>';
    $html .= '<span class="mbu-failure">' . $failureCount . ' failed</span>';
    if ($addedCount > 0) {
        $html .= '<span id="mbu-renew-sync-summary" class="mbu-status-neutral">Sync pending</span>';
    }
    $html .= '</div>';
    $html .= '<div class="table-responsive"><table class="datatable table table-striped"><thead><tr>';
    $html .= '<th>Domain</th><th>Added</th><th>Registrar</th><th>Sync</th><th>Details</th></tr></thead><tbody>';

    foreach ($results as $row) {
        $success = !empty($row['success']);
        $skipped = !empty($row['skipped']);
        $domainId = (int) ($row['domain_id'] ?? 0);
        $clientId = (int) ($row['client_id'] ?? 0);
        $syncId = 'mbu-renew-sync-' . $domainId;
        $detailId = 'mbu-renew-detail-' . $domainId;

        if ($success && $domainId > 0 && $clientId > 0) {
            $syncItems[] = ['id' => $domainId, 'clientId' => $clientId];
        }

        $html .= '<tr><td>' . multibulkupdater_escape((string) ($row['domain'] ?? '')) . '</td>';
        if ($success) {
            $html .= '<td><span class="mbu-status-success">Complete</span></td>';
        } elseif ($skipped) {
            $html .= '<td><span class="mbu-status-neutral">Skipped</span></td>';
        } else {
            $html .= '<td><span class="mbu-status-failed">Failed</span></td>';
        }
        $html .= '<td>' . multibulkupdater_escape(multibulkupdater_add_registrar_label((string) ($row['registrar'] ?? ''))) . '</td>';
        if ($success && $domainId > 0 && $clientId > 0) {
            $html .= '<td><span id="' . multibulkupdater_escape($syncId) . '" class="mbu-status-neutral">Pending</span></td>';
        } else {
            $html .= '<td><span class="mbu-status-neutral">Not Run</span></td>';
        }
        $html .= '<td id="' . multibulkupdater_escape($detailId) . '">' . multibulkupdater_escape((string) ($row['message'] ?? '')) . '</td></tr>';
    }

    $html .= '</tbody></table></div>';
    $html .= '<div class="mbu-actions"><a class="btn btn-primary" href="' . multibulkupdater_escape($moduleLink) . '">Add More Domains</a></div>';
    $html .= '</div></div>';

    if ($syncItems) {
        $json = json_encode($syncItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json !== false) {
            $html .= multibulkupdater_renew_sync_script($json);
        }
    }

    return $html;
}

function multibulkupdater_move_output(array $vars): void
{
    @set_time_limit(0);
    $moduleLink = (string) ($vars['modulelink'] ?? 'addonmodules.php?module=multibulkupdater');
    $defaultRegPeriod = multibulkupdater_default_registration_period($vars);
    $step = (string) ($_POST['mbu_step'] ?? 'start');
    $errors = [];
    $storedDomains = (string) ($_SESSION['multibulkupdater_move_domains'] ?? '');
    $rawDomains = isset($_POST['domains']) ? (string) $_POST['domains'] : $storedDomains;
    $domains = multibulkupdater_parse_domains($rawDomains);
    $mode = strtolower(trim((string) ($_POST['move_mode'] ?? 'whmcs')));
    if (!in_array($mode, ['whmcs', 'neo'], true)) {
        $mode = 'whmcs';
    }
    $presetGroups = multibulkupdater_move_preset_groups();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_token('WHMCS.admin.default');
        $_SESSION['multibulkupdater_move_domains'] = $rawDomains;
    }

    echo multibulkupdater_styles();
    echo '<div class="mbu-wrap">';
    echo '<div class="mbu-header"><div><h2>Bulk Domain Manager</h2>';
    echo '<p>Move a list of domains to another WHMCS account or another NEO customer account.</p></div></div>';
    echo multibulkupdater_action_switcher($moduleLink, $rawDomains, 'move_domains');

    if ($step === 'save_move_preset') {
        $destination = trim((string) ($_POST['move_destination'] ?? ''));
        $presetName = trim((string) ($_POST['preset_name'] ?? ''));
        if ($presetName === '') {
            $errors[] = 'Enter a preset name.';
        }
        if ($destination === '') {
            $errors[] = 'Enter a destination account before saving the preset.';
        }
        if (!$errors) {
            multibulkupdater_move_save_preset($presetName, $mode, $destination);
            $presetGroups = multibulkupdater_move_preset_groups();
            echo multibulkupdater_alert('success', 'Move destination preset saved.');
        } else {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
        }
        echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, $_POST, $defaultRegPeriod);
    } elseif ($step === 'delete_move_preset') {
        $presetKey = trim((string) ($_POST['preset_key'] ?? ''));
        if ($presetKey !== '') {
            multibulkupdater_move_delete_preset($presetKey, $mode);
            $presetGroups = multibulkupdater_move_preset_groups();
            echo multibulkupdater_alert('success', 'Move destination preset deleted.');
        }
        echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, $_POST, $defaultRegPeriod);
    } elseif ($step === 'move_confirm') {
        $destination = trim((string) ($_POST['move_destination'] ?? ''));
        $moveOptions = multibulkupdater_move_options($_POST, $mode, $defaultRegPeriod, $errors);
        if (!$domains) {
            $errors[] = 'Enter at least one domain.';
        }
        if ($destination === '') {
            $errors[] = 'Enter or select a destination account.';
        }
        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
            echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, $_POST, $defaultRegPeriod);
        } else {
            $preflight = multibulkupdater_move_preflight($domains, $mode, $destination);
            if (!$preflight['ok']) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape((string) $preflight['message']));
                if (!empty($preflight['rows'])) {
                    echo multibulkupdater_move_preflight_table((array) $preflight['rows'], $mode);
                }
                echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, $_POST, $defaultRegPeriod);
            } else {
                echo multibulkupdater_move_confirm_form($moduleLink, $rawDomains, $mode, $destination, $preflight, $moveOptions);
            }
        }
    } elseif ($step === 'move_execute') {
        $destination = trim((string) ($_POST['move_destination'] ?? ''));
        $moveOptions = multibulkupdater_move_options($_POST, $mode, $defaultRegPeriod, $errors);
        if (!$domains) {
            $errors[] = 'No domains were submitted.';
        }
        if ($destination === '') {
            $errors[] = 'No destination account was submitted.';
        }
        if ($errors) {
            foreach ($errors as $error) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape($error));
            }
            echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, $_POST, $defaultRegPeriod);
        } else {
            // Re-run the complete source/destination preflight immediately before execution.
            $preflight = multibulkupdater_move_preflight($domains, $mode, $destination);
            if (!$preflight['ok']) {
                echo multibulkupdater_alert('danger', multibulkupdater_escape((string) $preflight['message']));
                echo multibulkupdater_move_preflight_table((array) $preflight['rows'], $mode);
                echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, $_POST, $defaultRegPeriod);
            } else {
                $results = $mode === 'neo'
                    ? multibulkupdater_move_execute_neo($preflight, $moveOptions)
                    : multibulkupdater_move_execute_whmcs($preflight, $moveOptions);
                echo multibulkupdater_results($moduleLink . '&mbu_page=move', 'move', $results);
            }
        }
    } else {
        echo multibulkupdater_move_start_form($moduleLink, $rawDomains, $presetGroups, [], $defaultRegPeriod);
    }

    echo multibulkupdater_submit_script();
    echo '</div>';
}


function multibulkupdater_move_options(array $source, string $mode, int $defaultRegPeriod, array &$errors): array
{
    $periodRaw = trim((string) ($source['move_regperiod'] ?? $defaultRegPeriod));
    if ($periodRaw === '' || !ctype_digit($periodRaw)) {
        $errors[] = 'Select a valid Registration Period.';
        $period = $defaultRegPeriod;
    } else {
        $period = (int) $periodRaw;
        if ($period < 1 || $period > 10) {
            $errors[] = 'Registration Period must be between 1 and 10 years.';
            $period = $defaultRegPeriod;
        }
    }

    return [
        'regperiod' => $period,
        'update_regperiod' => !empty($source['move_update_regperiod']),
        'update_price_tier' => $mode === 'whmcs' && !empty($source['move_update_price_tier']),
    ];
}

function multibulkupdater_move_preset_setting(string $mode): string
{
    return $mode === 'neo' ? 'move_presets_neo_json' : 'move_presets_whmcs_json';
}

function multibulkupdater_move_legacy_preset_setting(): string
{
    return 'move_presets_json';
}

function multibulkupdater_move_presets(string $mode): array
{
    $mode = $mode === 'neo' ? 'neo' : 'whmcs';
    try {
        $setting = multibulkupdater_move_preset_setting($mode);
        $row = Capsule::table('tbladdonmodules')
            ->where('module', 'multibulkupdater')
            ->where('setting', $setting)
            ->first();

        if ($row) {
            $decoded = json_decode((string) ($row->value ?? ''), true);
            return is_array($decoded) ? $decoded : [];
        }

        // One-time lazy migration from the original combined preset store.
        $legacyRaw = Capsule::table('tbladdonmodules')
            ->where('module', 'multibulkupdater')
            ->where('setting', multibulkupdater_move_legacy_preset_setting())
            ->value('value');
        $legacy = json_decode((string) $legacyRaw, true);
        $presets = [];
        if (is_array($legacy)) {
            foreach ($legacy as $key => $preset) {
                if (!is_array($preset)) {
                    continue;
                }
                $presetMode = strtolower(trim((string) ($preset['mode'] ?? 'whmcs')));
                if (!in_array($presetMode, ['whmcs', 'neo'], true)) {
                    $presetMode = 'whmcs';
                }
                if ($presetMode === $mode) {
                    $presets[(string) $key] = $preset;
                }
            }
        }

        Capsule::table('tbladdonmodules')->updateOrInsert(
            ['module' => 'multibulkupdater', 'setting' => $setting],
            ['value' => json_encode($presets, JSON_UNESCAPED_SLASHES)]
        );
        return $presets;
    } catch (Throwable $e) {
        return [];
    }
}

function multibulkupdater_move_preset_groups(): array
{
    return [
        'whmcs' => multibulkupdater_move_presets('whmcs'),
        'neo' => multibulkupdater_move_presets('neo'),
    ];
}

function multibulkupdater_move_save_preset(string $name, string $mode, string $destination): void
{
    $mode = $mode === 'neo' ? 'neo' : 'whmcs';
    $presets = multibulkupdater_move_presets($mode);
    $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? '', '-'));
    if ($key === '') {
        $key = 'preset-' . substr(sha1($name . microtime(true)), 0, 8);
    }
    $presets[$key] = [
        'name' => $name,
        'mode' => $mode,
        'destination' => trim($destination),
    ];
    Capsule::table('tbladdonmodules')->updateOrInsert(
        ['module' => 'multibulkupdater', 'setting' => multibulkupdater_move_preset_setting($mode)],
        ['value' => json_encode($presets, JSON_UNESCAPED_SLASHES)]
    );
}

function multibulkupdater_move_delete_preset(string $key, string $mode): void
{
    $mode = $mode === 'neo' ? 'neo' : 'whmcs';
    $presets = multibulkupdater_move_presets($mode);
    unset($presets[$key]);
    Capsule::table('tbladdonmodules')->updateOrInsert(
        ['module' => 'multibulkupdater', 'setting' => multibulkupdater_move_preset_setting($mode)],
        ['value' => json_encode($presets, JSON_UNESCAPED_SLASHES)]
    );
}

function multibulkupdater_move_start_form(string $moduleLink, string $rawDomains, array $presetGroups, array $source, int $defaultRegPeriod = 1): string
{
    $mode = strtolower(trim((string) ($source['move_mode'] ?? 'whmcs')));
    if (!in_array($mode, ['whmcs', 'neo'], true)) {
        $mode = 'whmcs';
    }
    $destination = trim((string) ($source['move_destination'] ?? ''));
    $periodRaw = trim((string) ($source['move_regperiod'] ?? $defaultRegPeriod));
    $moveRegPeriod = ctype_digit($periodRaw) ? max(1, min(10, (int) $periodRaw)) : $defaultRegPeriod;
    $moveUpdateRegPeriod = !empty($source['move_update_regperiod']);
    $moveUpdatePriceTier = !empty($source['move_update_price_tier']);

    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card" id="mbu-move-form">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="move">';
    $html .= '<input type="hidden" name="mbu_step" id="mbu-move-step" value="start">';
    $html .= '<div class="mbu-card-title">Move Domains</div><div class="mbu-card-body">';
    $html .= '<label for="mbu-move-domains">Domains</label>';
    $html .= '<textarea id="mbu-move-domains" name="domains" rows="10" placeholder="example.com&#10;example.net">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<div class="mbu-help">The current WHMCS or NEO account is looked up automatically for every domain. Domains do not need to start in the same source account.</div>';

    $html .= '<div class="mbu-fields"><h4>Move Destination</h4>';
    foreach (['whmcs' => 'WHMCS', 'neo' => 'NEO'] as $presetMode => $presetLabel) {
        $modePresets = is_array($presetGroups[$presetMode] ?? null) ? $presetGroups[$presetMode] : [];
        $html .= '<div class="mbu-move-mode-preset-group" data-move-preset-mode="' . multibulkupdater_escape($presetMode) . '"' . ($presetMode === $mode ? '' : ' style="display:none"') . '>';
        $html .= '<div class="mbu-move-presets"><span class="mbu-preset-label">Apply preset:</span>';
        if ($modePresets) {
            foreach ($modePresets as $key => $preset) {
                $presetDestination = (string) ($preset['destination'] ?? '');
                $name = (string) ($preset['name'] ?? $key);
                $html .= '<button type="button" class="btn mbu-move-preset" data-mode="' . multibulkupdater_escape($presetMode) . '" data-destination="' . multibulkupdater_escape($presetDestination) . '">' . multibulkupdater_escape($name) . '</button>';
            }
        } else {
            $html .= '<span class="mbu-help-inline">No saved ' . multibulkupdater_escape($presetLabel) . ' move presets yet.</span>';
        }
        $html .= '</div></div>';
    }
    $html .= '<div class="mbu-help mbu-preset-help">WHMCS and NEO presets are stored separately. Only presets for the selected Move In account type are shown. The source account is always looked up from each domain at review/execution time.</div>';
    $html .= '<div class="mbu-grid mbu-move-grid"><div><label for="mbu-move-mode">Move In</label><select id="mbu-move-mode" name="move_mode">';
    $html .= '<option value="whmcs"' . ($mode === 'whmcs' ? ' selected' : '') . '>WHMCS Account</option>';
    $html .= '<option value="neo"' . ($mode === 'neo' ? ' selected' : '') . '>NEO Account</option>';
    $html .= '</select></div>';
    $html .= '<div><label for="mbu-move-destination">Destination Account <span class="mbu-required" aria-label="required">*</span></label>';
    $html .= '<input id="mbu-move-destination" type="text" name="move_destination" value="' . multibulkupdater_escape($destination) . '" autocomplete="off"></div></div>';
    $html .= '<div id="mbu-move-whmcs-help" class="mbu-help">WHMCS: enter the destination Client ID or the account email address. BDM will resolve and display the exact account before moving anything.</div>';
    $html .= '<div id="mbu-move-neo-help" class="mbu-help">NEO: enter the destination Customer ID or NEO username/email. Existing registrar contacts are retained. NEO may also move services associated with the domain under its native move operation.</div>';

    $html .= '<div class="mbu-fields" id="mbu-move-domain-settings"><h4>WHMCS Domain Settings</h4>';
    $html .= '<div class="mbu-grid"><div><label for="mbu-move-regperiod">Registration Period</label><select id="mbu-move-regperiod" name="move_regperiod">';
    for ($year = 1; $year <= 10; $year++) {
        $html .= '<option value="' . $year . '"' . ($moveRegPeriod === $year ? ' selected' : '') . '>' . $year . '</option>';
    }
    $html .= '</select></div></div>';
    $html .= '<div class="mbu-copy-options">';
    $html .= '<label class="mbu-check"><input type="checkbox" name="move_update_regperiod" value="1"' . ($moveUpdateRegPeriod ? ' checked' : '') . '> Update Registration Period</label>';
    $html .= '<label class="mbu-check" id="mbu-move-price-tier-row"><input id="mbu-move-update-price-tier" type="checkbox" name="move_update_price_tier" value="1"' . ($moveUpdatePriceTier ? ' checked' : '') . '> Update to price tier on account</label>';
    $html .= '</div>';
    $html .= '<div class="mbu-help">Registration Period defaults to ' . (int) $defaultRegPeriod . ' year' . ($defaultRegPeriod === 1 ? '' : 's') . '. The price-tier option uses WHMCS native recurring-price recalculation after a WHMCS account move and is unavailable for NEO-only moves.</div>';
    $html .= '</div>';

    $html .= '<div class="mbu-preset-save-row"><div><label for="mbu-move-preset-name">Preset Name</label><input id="mbu-move-preset-name" type="text" name="preset_name" value="' . multibulkupdater_escape((string) ($source['preset_name'] ?? '')) . '" placeholder="Example: Main WHMCS Account"></div>';
    $html .= '<button type="submit" data-mbu-step="save_move_preset" class="btn btn-default">Save Preset</button>';
    foreach (['whmcs', 'neo'] as $deleteMode) {
        $modePresets = is_array($presetGroups[$deleteMode] ?? null) ? $presetGroups[$deleteMode] : [];
        if (!$modePresets) {
            continue;
        }
        $html .= '<div class="mbu-move-delete-preset-group" data-move-delete-mode="' . multibulkupdater_escape($deleteMode) . '"' . ($deleteMode === $mode ? '' : ' style="display:none"') . '>';
        $html .= '<select name="preset_key" class="mbu-delete-preset-select"' . ($deleteMode === $mode ? '' : ' disabled') . '><option value="">Delete preset…</option>';
        foreach ($modePresets as $key => $preset) {
            $html .= '<option value="' . multibulkupdater_escape((string) $key) . '">' . multibulkupdater_escape((string) ($preset['name'] ?? $key)) . '</option>';
        }
        $html .= '</select><button type="submit" data-mbu-step="delete_move_preset" class="btn mbu-danger-button" onclick="var s=this.parentNode.querySelector(\'select\'); return s && s.value !== \'\' && confirm(\'Delete this move preset?\');">Delete</button></div>';
    }
    $html .= '</div></div>';

    $html .= '<div class="mbu-actions"><button type="submit" data-mbu-step="move_confirm" class="btn btn-primary mbu-submit-button" data-processing-text="Checking Accounts…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Review Domain Move</span></button></div>';
    $html .= '</div></form>';
    $html .= multibulkupdater_move_script();
    return $html;
}

function multibulkupdater_move_script(): string
{
    return <<<'HTML'
<script>
(function () {
    const form = document.getElementById('mbu-move-form');
    const mode = document.getElementById('mbu-move-mode');
    const destination = document.getElementById('mbu-move-destination');
    const step = document.getElementById('mbu-move-step');
    const whmcsHelp = document.getElementById('mbu-move-whmcs-help');
    const neoHelp = document.getElementById('mbu-move-neo-help');
    const presets = Array.prototype.slice.call(document.querySelectorAll('.mbu-move-preset'));
    const presetGroups = Array.prototype.slice.call(document.querySelectorAll('.mbu-move-mode-preset-group'));
    const deleteGroups = Array.prototype.slice.call(document.querySelectorAll('.mbu-move-delete-preset-group'));
    const priceTierRow = document.getElementById('mbu-move-price-tier-row');
    const priceTier = document.getElementById('mbu-move-update-price-tier');
    if (!form || !mode || !destination || !step) return;

    function updateMode() {
        const isNeo = mode.value === 'neo';
        destination.placeholder = isNeo ? 'NEO Customer ID or username/email' : 'WHMCS Client ID or account email';
        if (whmcsHelp) whmcsHelp.style.display = isNeo ? 'none' : 'block';
        if (neoHelp) neoHelp.style.display = isNeo ? 'block' : 'none';
        if (priceTierRow) priceTierRow.style.display = isNeo ? 'none' : 'flex';
        if (priceTier) priceTier.disabled = isNeo;
        presetGroups.forEach(function (group) {
            group.style.display = group.dataset.movePresetMode === mode.value ? 'block' : 'none';
        });
        deleteGroups.forEach(function (group) {
            const active = group.dataset.moveDeleteMode === mode.value;
            group.style.display = active ? 'flex' : 'none';
            const select = group.querySelector('select');
            if (select) select.disabled = !active;
        });
    }
    function clearPreset() {
        presets.forEach(function (button) { button.classList.remove('mbu-preset-active'); });
    }
    presets.forEach(function (button) {
        button.addEventListener('click', function () {
            if ((button.dataset.mode || 'whmcs') !== mode.value) return;
            destination.value = button.dataset.destination || '';
            clearPreset();
            button.classList.add('mbu-preset-active');
        });
    });
    mode.addEventListener('change', function () { clearPreset(); updateMode(); });
    destination.addEventListener('input', clearPreset);
    form.addEventListener('submit', function (event) {
        const submitter = event.submitter;
        if (submitter && submitter.dataset && submitter.dataset.mbuStep) {
            step.value = submitter.dataset.mbuStep;
        }
    });
    updateMode();
}());
</script>
HTML;
}

function multibulkupdater_move_whmcs_client(string $identifier): array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return ['ok' => false, 'message' => 'Destination WHMCS account is blank.'];
    }
    try {
        $query = Capsule::table('tblclients');
        if (preg_match('/^\d+$/', $identifier)) {
            $client = $query->where('id', (int) $identifier)->first();
        } else {
            $client = $query->where('email', $identifier)->first();
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'WHMCS destination lookup failed: ' . $e->getMessage()];
    }
    if (!$client) {
        return ['ok' => false, 'message' => 'Destination WHMCS account was not found.'];
    }
    $name = trim((string) ($client->firstname ?? '') . ' ' . (string) ($client->lastname ?? ''));
    $email = trim((string) ($client->email ?? ''));
    $status = trim((string) ($client->status ?? ''));
    $label = ($name !== '' ? $name : $email) . ' (#' . (int) $client->id . ')';
    if ($email !== '' && stripos($label, $email) === false) {
        $label .= ' — ' . $email;
    }
    if ($status !== '') {
        $label .= ' — ' . $status;
    }
    return [
        'ok' => true,
        'id' => (int) $client->id,
        'email' => $email,
        'name' => $name,
        'status' => $status,
        'label' => $label,
        'message' => '',
    ];
}

function multibulkupdater_move_whmcs_client_label(int $clientId): string
{
    $result = multibulkupdater_move_whmcs_client((string) $clientId);
    return !empty($result['ok']) ? (string) $result['label'] : '#' . $clientId;
}

function multibulkupdater_move_payment_assignment(int $domainId): bool
{
    try {
        if (!Capsule::schema()->hasTable('mod_domainmonger_item_paymethod_assignments')) {
            return false;
        }
        return Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->where('item_type', 'domain')
            ->where('item_id', $domainId)
            ->exists();
    } catch (Throwable $e) {
        return false;
    }
}

function multibulkupdater_move_preflight_whmcs(array $domains, string $destination): array
{
    $target = multibulkupdater_move_whmcs_client($destination);
    if (empty($target['ok'])) {
        return ['ok' => false, 'mode' => 'whmcs', 'destination' => [], 'rows' => [], 'message' => (string) $target['message']];
    }

    $rows = [];
    $blocked = 0;
    foreach ($domains as $domainName) {
        try {
            $domain = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'WHMCS database lookup failed: ' . $e->getMessage()];
            $blocked++;
            continue;
        }
        if (!$domain) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'Domain not found in WHMCS.'];
            $blocked++;
            continue;
        }
        $sourceId = (int) ($domain->userid ?? 0);
        if ($sourceId <= 0) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'Domain does not have a valid current WHMCS owner.'];
            $blocked++;
            continue;
        }
        $skip = $sourceId === (int) $target['id'];
        $contactId = (int) ($domain->contactid ?? 0);
        $hasPaymentAssignment = multibulkupdater_move_payment_assignment((int) $domain->id);
        $notes = [];
        if ($skip) {
            $notes[] = 'Already in destination account; no change will be made.';
        } else {
            if ($contactId > 0) {
                $notes[] = 'WHMCS contact override #' . $contactId . ' will reset to Account Default.';
            }
            if ($hasPaymentAssignment) {
                $notes[] = 'Per-domain Payment Routing assignment will be removed so the old account payment method cannot follow the domain.';
            }
            if (!$notes) {
                $notes[] = 'Domain ownership only; registrar/WHOIS is unchanged.';
            }
        }
        $rows[] = [
            'domain' => $domainName,
            'domain_id' => (int) $domain->id,
            'source_id' => $sourceId,
            'source_label' => multibulkupdater_move_whmcs_client_label($sourceId),
            'destination_id' => (int) $target['id'],
            'destination_label' => (string) $target['label'],
            'contact_id' => $contactId,
            'payment_assignment' => $hasPaymentAssignment,
            'skip' => $skip,
            'ok' => true,
            'message' => implode(' ', $notes),
        ];
    }

    return [
        'ok' => $blocked === 0,
        'mode' => 'whmcs',
        'destination' => $target,
        'rows' => $rows,
        'message' => $blocked === 0 ? '' : 'Move stopped before making any changes because ' . $blocked . ' domain' . ($blocked === 1 ? '' : 's') . ' could not pass WHMCS preflight.',
    ];
}

function multibulkupdater_move_neo_customer(array $credentials, string $identifier): array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return ['ok' => false, 'message' => 'Destination NEO account is blank.'];
    }
    $params = [
        'auth-userid' => (string) $credentials['authUserId'],
        'api-key' => (string) $credentials['apiKey'],
    ];
    if (preg_match('/^\d+$/', $identifier)) {
        $url = dm_epp_api_base($credentials) . '/customers/details-by-id.json';
        $params['customer-id'] = $identifier;
    } else {
        $url = dm_epp_api_base($credentials) . '/customers/details.json';
        $params['username'] = $identifier;
    }
    [$ok, $response, $error] = dm_epp_http_request('GET', $url, $params);
    if (!$ok || !is_array($response)) {
        return ['ok' => false, 'message' => $error !== '' ? $error : 'NEO destination account was not found.'];
    }
    $customerId = trim((string) ($response['customerid'] ?? $response['customer-id'] ?? ''));
    if ($customerId === '' || !preg_match('/^\d+$/', $customerId)) {
        return ['ok' => false, 'message' => 'NEO customer lookup did not return a valid Customer ID.'];
    }
    $username = trim((string) ($response['username'] ?? $response['useremail'] ?? ''));
    $name = trim((string) ($response['name'] ?? ''));
    $company = trim((string) ($response['company'] ?? ''));
    $status = trim((string) ($response['customerstatus'] ?? ''));
    $label = '#' . $customerId;
    if ($username !== '') {
        $label .= ' — ' . $username;
    }
    if ($name !== '' || $company !== '') {
        $label .= ' — ' . trim($name . ($company !== '' ? ' / ' . $company : ''));
    }
    if ($status !== '') {
        $label .= ' — ' . $status;
    }
    return [
        'ok' => true,
        'id' => $customerId,
        'username' => $username,
        'name' => $name,
        'company' => $company,
        'status' => $status,
        'label' => $label,
        'message' => '',
    ];
}

function multibulkupdater_move_preflight_neo(array $domains, string $destination): array
{
    multibulkupdater_whois_load_epp_manager();
    if (!function_exists('dm_epp_find_credentials') || !function_exists('dm_epp_http_request') || !function_exists('dm_epp_api_base')) {
        return ['ok' => false, 'mode' => 'neo', 'destination' => [], 'rows' => [], 'message' => 'NEO credential/API helper is not available.'];
    }

    $rows = [];
    $blocked = 0;
    $credentialSignature = '';
    $batchCredentials = null;
    foreach ($domains as $domainName) {
        try {
            $domain = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'WHMCS database lookup failed: ' . $e->getMessage()];
            $blocked++;
            continue;
        }
        if (!$domain) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'Domain not found in WHMCS.'];
            $blocked++;
            continue;
        }
        $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
        if ($registrar === '' || strpos($registrar, 'netearthone') === false) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'Domain is not assigned to a NetEarthOne/NEO registrar module.'];
            $blocked++;
            continue;
        }
        if (preg_match('/\.nz$/i', $domainName)) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => '.NZ orders cannot be moved between NEO customer accounts.'];
            $blocked++;
            continue;
        }
        $credentials = dm_epp_find_credentials($registrar);
        if (!is_array($credentials) || trim((string) ($credentials['authUserId'] ?? '')) === '' || trim((string) ($credentials['apiKey'] ?? '')) === '') {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'NEO API credentials could not be resolved from the registrar configuration.'];
            $blocked++;
            continue;
        }
        $signature = trim((string) $credentials['authUserId']) . '|' . dm_epp_api_base($credentials);
        if ($credentialSignature === '') {
            $credentialSignature = $signature;
            $batchCredentials = $credentials;
        } elseif ($credentialSignature !== $signature) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => 'This domain uses a different NEO reseller/API account. Run it in a separate move batch.'];
            $blocked++;
            continue;
        }
        $details = multibulkupdater_whois_neo_domain_details($domain, []);
        if (empty($details['ok'])) {
            $rows[] = ['domain' => $domainName, 'ok' => false, 'message' => (string) ($details['message'] ?? 'Could not resolve the current NEO account.')];
            $blocked++;
            continue;
        }
        $rows[] = [
            'domain' => $domainName,
            'domain_id' => (int) $domain->id,
            'registrar' => $registrar,
            'source_id' => (string) $details['customer_id'],
            'source_label' => '#' . (string) $details['customer_id'],
            'ok' => true,
            'skip' => false,
            'message' => 'Existing NEO contacts will be retained.',
        ];
    }

    if ($blocked > 0 || !$batchCredentials) {
        return [
            'ok' => false,
            'mode' => 'neo',
            'destination' => [],
            'rows' => $rows,
            'message' => 'Move stopped before making any changes because ' . $blocked . ' domain' . ($blocked === 1 ? '' : 's') . ' could not pass NEO preflight.',
        ];
    }

    $target = multibulkupdater_move_neo_customer($batchCredentials, $destination);
    if (empty($target['ok'])) {
        return ['ok' => false, 'mode' => 'neo', 'destination' => [], 'rows' => $rows, 'message' => 'Destination NEO account could not be resolved: ' . (string) $target['message']];
    }

    $sourceCache = [];
    foreach ($rows as &$row) {
        $sourceId = (string) $row['source_id'];
        if (!isset($sourceCache[$sourceId])) {
            $sourceCache[$sourceId] = multibulkupdater_move_neo_customer($batchCredentials, $sourceId);
        }
        if (!empty($sourceCache[$sourceId]['ok'])) {
            $row['source_label'] = (string) $sourceCache[$sourceId]['label'];
        }
        $row['destination_id'] = (string) $target['id'];
        $row['destination_label'] = (string) $target['label'];
        $row['skip'] = $sourceId === (string) $target['id'];
        if ($row['skip']) {
            $row['message'] = 'Already in destination NEO account; no change will be made.';
        }
    }
    unset($row);

    return [
        'ok' => true,
        'mode' => 'neo',
        'destination' => $target,
        'credentials_signature' => $credentialSignature,
        'rows' => $rows,
        'message' => '',
    ];
}

function multibulkupdater_move_preflight(array $domains, string $mode, string $destination): array
{
    return $mode === 'neo'
        ? multibulkupdater_move_preflight_neo($domains, $destination)
        : multibulkupdater_move_preflight_whmcs($domains, $destination);
}

function multibulkupdater_move_preflight_table(array $rows, string $mode): string
{
    $html = '<div class="mbu-card mbu-preflight-card"><div class="mbu-card-title">Move Preflight</div><div class="mbu-card-body"><div class="table-responsive"><table class="datatable table table-striped"><thead><tr>';
    $html .= '<th>Domain</th><th>Current Account</th><th>Destination Account</th><th>Move Details</th><th>Status</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $ready = !empty($row['ok']);
        $skip = !empty($row['skip']);
        $status = !$ready ? '<span class="mbu-status-failed">Blocked</span>' : ($skip ? '<span class="mbu-status-neutral">No Change</span>' : '<span class="mbu-status-success">Ready</span>');
        $html .= '<tr><td>' . multibulkupdater_escape((string) ($row['domain'] ?? '')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['source_label'] ?? '—')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['destination_label'] ?? '—')) . '</td>';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['message'] ?? '')) . '</td>';
        $html .= '<td>' . $status . '</td></tr>';
    }
    $html .= '</tbody></table></div></div></div>';
    return $html;
}

function multibulkupdater_move_confirm_form(string $moduleLink, string $rawDomains, string $mode, string $destination, array $preflight, array $options): string
{
    $target = (array) ($preflight['destination'] ?? []);
    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_page" value="move"><input type="hidden" name="mbu_step" value="move_execute">';
    $html .= '<input type="hidden" name="move_mode" value="' . multibulkupdater_escape($mode) . '">';
    $html .= '<input type="hidden" name="move_destination" value="' . multibulkupdater_escape($destination) . '">';
    $html .= '<input type="hidden" name="move_regperiod" value="' . (int) ($options['regperiod'] ?? 1) . '">';
    if (!empty($options['update_regperiod'])) {
        $html .= '<input type="hidden" name="move_update_regperiod" value="1">';
    }
    if (!empty($options['update_price_tier']) && $mode === 'whmcs') {
        $html .= '<input type="hidden" name="move_update_price_tier" value="1">';
    }
    $html .= '<textarea name="domains" class="mbu-hidden">' . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<div class="mbu-card-title">Confirm Domain Move</div><div class="mbu-card-body">';
    if ($mode === 'neo') {
        $html .= multibulkupdater_alert('warning', 'This moves the selected registrar orders to another NEO customer. NEO retains the existing contacts, and its native move can include products/services associated with the domain.');
    } else {
        $html .= multibulkupdater_alert('warning', 'This changes the WHMCS owner of the selected domain records. Registrar ownership/WHOIS is not changed. Historical invoices are not moved.');
    }
    $html .= '<div class="mbu-summary"><strong>Move in:</strong> ' . ($mode === 'neo' ? 'NEO' : 'WHMCS') . '<br><strong>Destination:</strong> ' . multibulkupdater_escape((string) ($target['label'] ?? $destination));
    $html .= '<br><strong>Registration Period:</strong> ' . (int) ($options['regperiod'] ?? 1) . ' &nbsp; <strong>Update:</strong> ' . (!empty($options['update_regperiod']) ? 'Yes' : 'No');
    if ($mode === 'whmcs') {
        $html .= '<br><strong>Update to account price tier:</strong> ' . (!empty($options['update_price_tier']) ? 'Yes' : 'No');
    }
    $html .= '</div>';
    $html .= multibulkupdater_move_preflight_table((array) ($preflight['rows'] ?? []), $mode);
    $html .= '<div class="mbu-actions"><a class="btn btn-default" href="' . multibulkupdater_escape($moduleLink . '&mbu_page=move') . '">Cancel</a>';
    $html .= '<button type="submit" class="btn btn-primary mbu-submit-button" data-processing-text="Moving Domains…"><span class="mbu-button-spinner" aria-hidden="true"></span><span class="mbu-button-label">Move Domains</span></button></div>';
    $html .= '</div></form>';
    return $html;
}

function multibulkupdater_update_registration_period(int $domainId, int $years): array
{
    $years = max(1, min(10, $years));
    try {
        $response = localAPI('UpdateClientDomain', [
            'domainid' => $domainId,
            'regperiod' => $years,
        ]);
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }

    if (is_array($response) && strtolower((string) ($response['result'] ?? '')) === 'success') {
        return ['ok' => true, 'message' => ''];
    }

    $message = is_array($response)
        ? trim((string) ($response['message'] ?? $response['error'] ?? 'WHMCS Registration Period update failed.'))
        : 'WHMCS returned an invalid Registration Period update response.';
    return ['ok' => false, 'message' => $message !== '' ? $message : 'WHMCS Registration Period update failed.'];
}

function multibulkupdater_recalculate_domain_price(int $domainId): array
{
    try {
        $response = localAPI('UpdateClientDomain', [
            'domainid' => $domainId,
            'autorecalc' => true,
        ]);
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }

    if (is_array($response) && strtolower((string) ($response['result'] ?? '')) === 'success') {
        return ['ok' => true, 'message' => ''];
    }

    $message = is_array($response)
        ? trim((string) ($response['message'] ?? $response['error'] ?? 'WHMCS price-tier recalculation failed.'))
        : 'WHMCS returned an invalid price-tier recalculation response.';
    return ['ok' => false, 'message' => $message !== '' ? $message : 'WHMCS price-tier recalculation failed.'];
}

function multibulkupdater_move_execute_whmcs(array $preflight, array $options): array
{
    @set_time_limit(0);
    $results = [];
    $destination = (array) ($preflight['destination'] ?? []);
    $destinationId = (int) ($destination['id'] ?? 0);
    $years = max(1, min(10, (int) ($options['regperiod'] ?? 1)));
    $updateRegPeriod = !empty($options['update_regperiod']);
    $updatePriceTier = !empty($options['update_price_tier']);

    foreach ((array) ($preflight['rows'] ?? []) as $row) {
        $domainName = (string) ($row['domain'] ?? '');
        if (empty($row['ok'])) {
            $results[] = multibulkupdater_result($domainName, false, (string) ($row['message'] ?? 'Preflight failed.'));
            continue;
        }

        $domainId = (int) ($row['domain_id'] ?? 0);
        $alreadyAtDestination = !empty($row['skip']);
        $ownershipMoved = false;
        $message = '';

        if ($alreadyAtDestination) {
            $message = 'Already in ' . (string) ($destination['label'] ?? ('WHMCS client #' . $destinationId)) . '; ownership move skipped.';
        } else {
            try {
                Capsule::connection()->transaction(function () use ($row, $destinationId): void {
                    $affected = Capsule::table('tbldomains')
                        ->where('id', (int) $row['domain_id'])
                        ->where('userid', (int) $row['source_id'])
                        ->update(['userid' => $destinationId, 'contactid' => 0]);
                    if ($affected !== 1) {
                        throw new RuntimeException('Domain ownership changed after preflight; no move was applied.');
                    }
                    if (Capsule::schema()->hasTable('mod_domainmonger_item_paymethod_assignments')) {
                        Capsule::table('mod_domainmonger_item_paymethod_assignments')
                            ->where('item_type', 'domain')
                            ->where('item_id', (int) $row['domain_id'])
                            ->delete();
                    }
                });
                $ownershipMoved = true;
                $message = 'Moved in WHMCS from ' . (string) $row['source_label'] . ' to ' . (string) $row['destination_label'] . '.';
                if ((int) ($row['contact_id'] ?? 0) > 0) {
                    $message .= ' Contact override reset to Account Default.';
                }
                if (!empty($row['payment_assignment'])) {
                    $message .= ' Old per-domain Payment Routing assignment removed.';
                }
            } catch (Throwable $e) {
                $results[] = multibulkupdater_result($domainName, false, $e->getMessage());
                continue;
            }
        }

        $postUpdateOk = true;
        $periodUpdateOk = true;
        if ($updateRegPeriod) {
            $periodUpdate = multibulkupdater_update_registration_period($domainId, $years);
            $periodUpdateOk = !empty($periodUpdate['ok']);
            if ($periodUpdateOk) {
                $message .= ' Registration Period updated to ' . $years . '.';
            } else {
                $postUpdateOk = false;
                $message .= ' Registration Period update failed: ' . (string) ($periodUpdate['message'] ?? 'Unknown WHMCS error.') . '.';
            }
        } else {
            $message .= ' Registration Period left unchanged.';
        }

        if ($updatePriceTier) {
            if ($updateRegPeriod && !$periodUpdateOk) {
                $message .= ' Price-tier update skipped because the requested Registration Period update failed.';
            } else {
                $priceUpdate = multibulkupdater_recalculate_domain_price($domainId);
                if (!empty($priceUpdate['ok'])) {
                    $message .= ' Recurring amount recalculated for the destination account price tier.';
                } else {
                    $postUpdateOk = false;
                    $message .= ' Price-tier update failed: ' . (string) ($priceUpdate['message'] ?? 'Unknown WHMCS error.') . '.';
                }
            }
        }

        $results[] = multibulkupdater_result($domainName, $postUpdateOk, $message);
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;
    if (function_exists('logActivity')) {
        logActivity('Bulk Domain Manager: WHMCS domain move completed for ' . count($results) . ' domain(s): ' . $successCount . ' successful, ' . $failureCount . ' failed/partial. Destination client #' . $destinationId . '.');
    }
    return $results;
}

function multibulkupdater_move_execute_neo(array $preflight, array $options): array
{
    @set_time_limit(0);
    $results = [];
    $destination = (array) ($preflight['destination'] ?? []);
    $destinationId = (string) ($destination['id'] ?? '');
    $years = max(1, min(10, (int) ($options['regperiod'] ?? 1)));
    $updateRegPeriod = !empty($options['update_regperiod']);

    foreach ((array) ($preflight['rows'] ?? []) as $row) {
        $domainName = (string) ($row['domain'] ?? '');
        if (empty($row['ok'])) {
            $results[] = multibulkupdater_result($domainName, false, (string) ($row['message'] ?? 'Preflight failed.'));
            continue;
        }

        $domainId = (int) ($row['domain_id'] ?? 0);
        $message = '';
        if (!empty($row['skip'])) {
            $message = 'Already in ' . (string) ($destination['label'] ?? ('NEO customer #' . $destinationId)) . '; NEO move skipped.';
        } else {
            $registrar = (string) ($row['registrar'] ?? '');
            $credentials = dm_epp_find_credentials($registrar);
            if (!is_array($credentials) || trim((string) ($credentials['authUserId'] ?? '')) === '' || trim((string) ($credentials['apiKey'] ?? '')) === '') {
                $results[] = multibulkupdater_result($domainName, false, 'NEO API credentials could not be resolved at execution time.');
                continue;
            }
            $params = [
                'auth-userid' => (string) $credentials['authUserId'],
                'api-key' => (string) $credentials['apiKey'],
                'domain-name' => $domainName,
                'existing-customer-id' => (string) ($row['source_id'] ?? ''),
                'new-customer-id' => $destinationId,
                'default-contact' => 'oldcontact',
            ];
            try {
                [$ok, $response, $error] = dm_epp_http_request('POST', dm_epp_api_base($credentials) . '/products/move.json', $params);
                if (!$ok) {
                    $results[] = multibulkupdater_result($domainName, false, $error !== '' ? $error : multibulkupdater_whois_response_message($response));
                    continue;
                }
                $detail = multibulkupdater_whois_response_message($response);
                $message = 'Moved in NEO from ' . (string) $row['source_label'] . ' to ' . (string) $row['destination_label'] . '. Existing contacts retained.';
                if ($detail !== '' && $detail !== 'Registrar returned an unrecognized response.' && stripos($detail, '"status":"Success"') === false) {
                    $message .= ' NEO: ' . $detail;
                }
            } catch (Throwable $e) {
                $results[] = multibulkupdater_result($domainName, false, $e->getMessage());
                continue;
            }
        }

        $postUpdateOk = true;
        if ($updateRegPeriod) {
            $periodUpdate = multibulkupdater_update_registration_period($domainId, $years);
            if (!empty($periodUpdate['ok'])) {
                $message .= ' WHMCS Registration Period updated to ' . $years . '.';
            } else {
                $postUpdateOk = false;
                $message .= ' WHMCS Registration Period update failed: ' . (string) ($periodUpdate['message'] ?? 'Unknown WHMCS error.') . '.';
            }
        } else {
            $message .= ' WHMCS Registration Period left unchanged.';
        }

        $results[] = multibulkupdater_result($domainName, $postUpdateOk, $message);
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;
    if (function_exists('logActivity')) {
        logActivity('Bulk Domain Manager: NEO domain move completed for ' . count($results) . ' domain(s): ' . $successCount . ' successful, ' . $failureCount . ' failed/partial. Destination NEO customer #' . $destinationId . '.');
    }
    return $results;
}

function multibulkupdater_actions(): array
{
    return [
        'nameservers' => 'Update Nameservers',
        'renew_domains' => 'Renew Domains',
        'lock' => 'Lock Domains',
        'unlock' => 'Unlock Domains',
        'disable_privacy' => 'Disable WHOIS Privacy',
        'update_epp' => 'Update EPP/Auth Code',
        'update_whois' => 'Update WHOIS',
        'move_domains' => 'Move Domains',
        'add_domains' => 'Add Existing Domains',
        'copy_dnsplus_zone' => 'Copy DNSPlus Zone',
    ];
}

function multibulkupdater_parse_domains(string $input): array
{
    $input = str_replace(["\r\n", "\r", ',', ';', "\t"], "\n", $input);
    $parts = preg_split('/\s+/', $input) ?: [];
    $domains = [];

    foreach ($parts as $part) {
        $domain = strtolower(trim((string) $part));
        if ($domain === '') {
            continue;
        }

        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
        $domain = preg_replace('#/.*$#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '.');

        if (strpos($domain, '@') !== false) {
            $domain = substr($domain, strrpos($domain, '@') + 1);
        }

        if ($domain !== '') {
            $domains[$domain] = $domain;
        }
    }

    return array_values($domains);
}


function multibulkupdater_dnsplus_service_info(int $serviceId, int $clientId = 0): array
{
    if ($serviceId < 1) {
        return [];
    }

    try {
        $row = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
            ->where('tblhosting.id', $serviceId)
            ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cloudns'])
            ->select(
                'tblhosting.id as service_id',
                'tblhosting.userid as client_id',
                'tblhosting.domainstatus as service_status',
                'tblhosting.packageid as product_id',
                'tblhosting.server as server_id',
                'tblproducts.name as product_name',
                'tblclients.firstname',
                'tblclients.lastname',
                'tblclients.companyname'
            )
            ->first();
    } catch (Throwable $e) {
        return [];
    }

    if (!$row) {
        return [];
    }

    $info = [
        'service_id' => (int) ($row->service_id ?? 0),
        'client_id' => (int) ($row->client_id ?? 0),
        'service_status' => (string) ($row->service_status ?? ''),
        'product_id' => (int) ($row->product_id ?? 0),
        'server_id' => (int) ($row->server_id ?? 0),
        'product_name' => trim((string) ($row->product_name ?? '')),
        'client_name' => trim((string) (($row->firstname ?? '') . ' ' . ($row->lastname ?? ''))),
        'company_name' => trim((string) ($row->companyname ?? '')),
    ];

    if ($clientId > 0 && $info['client_id'] !== $clientId) {
        return [];
    }

    return $info;
}

function multibulkupdater_dnsplus_zone_service_info(string $zone): array
{
    $zone = strtolower(rtrim(trim($zone), '.'));
    if ($zone === '') {
        return [];
    }

    try {
        $mapping = Capsule::table('mod_cloudns_zones')
            ->where('name', $zone)
            ->select('serviceid')
            ->first();
    } catch (Throwable $e) {
        return [];
    }

    if (!$mapping || (int) ($mapping->serviceid ?? 0) < 1) {
        return [];
    }

    return multibulkupdater_dnsplus_service_info((int) $mapping->serviceid);
}

function multibulkupdater_dnsplus_module_params(int $serviceId): array
{
    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $moduleFunctions = $root . '/includes/modulefunctions.php';
    if (!is_file($moduleFunctions)) {
        throw new RuntimeException('WHMCS module functions are unavailable.');
    }

    require_once $moduleFunctions;
    if (!function_exists('ModuleBuildParams')) {
        throw new RuntimeException('WHMCS could not prepare the DNSPlus service connection.');
    }

    $params = ModuleBuildParams($serviceId);
    if (!is_array($params)) {
        throw new RuntimeException('WHMCS returned invalid DNSPlus service parameters.');
    }

    return $params;
}

function multibulkupdater_load_cloudns_module(): void
{
    if (class_exists('Cloudns_Core')) {
        return;
    }

    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $module = $root . '/modules/servers/cloudns/cloudns.php';
    if (!is_file($module)) {
        throw new RuntimeException('The DNSPlus module is unavailable.');
    }
    require_once $module;
}

function multibulkupdater_collect_payload(string $action, array $source, array $vars, array &$errors): array
{
    if ($action === 'renew_domains') {
        $defaultRegPeriod = multibulkupdater_default_registration_period($vars);
        $yearsRaw = trim((string) ($source['renew_years'] ?? $defaultRegPeriod));
        if ($yearsRaw === '' || !ctype_digit($yearsRaw)) {
            $errors[] = 'Select a valid renewal period.';
            return ['years' => $defaultRegPeriod, 'update_regperiod' => !empty($source['renew_update_regperiod'])];
        }

        $years = (int) $yearsRaw;
        if ($years < 1 || $years > 10) {
            $errors[] = 'Renewal period must be between 1 and 10 years.';
            $years = $defaultRegPeriod;
        }

        return [
            'years' => $years,
            'update_regperiod' => !empty($source['renew_update_regperiod']),
        ];
    }

    if ($action === 'nameservers') {
        $nameservers = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = 'ns' . $i;
            $value = strtolower(trim((string) ($source[$key] ?? '')));
            if ($value === '') {
                continue;
            }
            $value = rtrim($value, '.');
            if (!multibulkupdater_valid_hostname($value)) {
                $errors[] = 'Nameserver ' . $i . ' is not a valid hostname.';
            }
            $nameservers[] = $value;
        }

        if (count($nameservers) < 2) {
            $errors[] = 'Enter at least two nameservers.';
        }

        return ['nameservers' => array_values(array_unique($nameservers))];
    }

    if ($action === 'update_epp') {
        $epp = trim((string) ($source['epp'] ?? $vars['epp'] ?? ''));
        $validationError = multibulkupdater_validate_epp($epp);
        if ($validationError !== '') {
            $errors[] = $validationError;
        }
        return ['epp' => $epp];
    }

    if ($action === 'copy_dnsplus_zone') {
        $sourceZone = strtolower(rtrim(trim((string) ($source['source_zone'] ?? '')), '.'));
        if ($sourceZone === '' || !multibulkupdater_valid_hostname($sourceZone)) {
            $errors[] = 'Enter a valid source DNSPlus zone.';
        }

        $sourceService = [];
        if ($sourceZone !== '' && multibulkupdater_valid_hostname($sourceZone)) {
            $sourceService = multibulkupdater_dnsplus_zone_service_info($sourceZone);
            if (!$sourceService) {
                $errors[] = 'The source zone is not attached to a DNSPlus Product/Service in WHMCS.';
            } elseif (strcasecmp((string) ($sourceService['service_status'] ?? ''), 'Active') !== 0) {
                $errors[] = 'The source zone DNSPlus Product/Service must be Active.';
            }
        }

        $clientRaw = trim((string) ($source['whmcs_client_id'] ?? ''));
        $serviceRaw = trim((string) ($source['dnsplus_service_id'] ?? ''));
        $clientOverride = $clientRaw !== '';
        $serviceOverride = $serviceRaw !== '';
        $clientId = $sourceService ? (int) ($sourceService['client_id'] ?? 0) : 0;
        $serviceId = $sourceService ? (int) ($sourceService['service_id'] ?? 0) : 0;
        $serviceInfo = $sourceService;
        $ownershipInherited = !$clientOverride && !$serviceOverride;

        if ($clientOverride && (!ctype_digit($clientRaw) || (int) $clientRaw < 1)) {
            $errors[] = 'Enter a valid destination WHMCS Account #, or leave it blank to use the source zone account.';
        }
        if ($serviceOverride && (!ctype_digit($serviceRaw) || (int) $serviceRaw < 1)) {
            $errors[] = 'Enter a valid destination DNSPlus Product/Service #, or leave it blank to use the source zone service.';
        }

        if ($serviceOverride && ctype_digit($serviceRaw) && (int) $serviceRaw > 0) {
            $serviceId = (int) $serviceRaw;
            $serviceInfo = multibulkupdater_dnsplus_service_info($serviceId);
            if (!$serviceInfo) {
                $errors[] = 'The destination DNSPlus Product/Service # was not found.';
            } else {
                $resolvedClientId = (int) ($serviceInfo['client_id'] ?? 0);
                if ($clientOverride && ctype_digit($clientRaw) && (int) $clientRaw > 0 && (int) $clientRaw !== $resolvedClientId) {
                    $errors[] = 'DNSPlus Product/Service #' . $serviceId . ' belongs to WHMCS Account #' . $resolvedClientId . ', not Account #' . (int) $clientRaw . '. No zones were created or changed.';
                }
                $clientId = $resolvedClientId;
                if (strcasecmp((string) ($serviceInfo['service_status'] ?? ''), 'Active') !== 0) {
                    $errors[] = 'The destination DNSPlus Product/Service must be Active.';
                }
            }
            $ownershipInherited = false;
        } elseif ($clientOverride && ctype_digit($clientRaw) && (int) $clientRaw > 0) {
            $requestedClientId = (int) $clientRaw;
            if ($sourceService && $requestedClientId !== (int) ($sourceService['client_id'] ?? 0)) {
                $errors[] = "When copying to a different WHMCS Account, also enter that account's DNSPlus Product/Service #.";
            } else {
                $clientId = $requestedClientId;
            }
            $ownershipInherited = false;
        }

        if ($sourceService && $serviceInfo) {
            $sourceServerId = (int) ($sourceService['server_id'] ?? 0);
            $destinationServerId = (int) ($serviceInfo['server_id'] ?? 0);
            if ($sourceServerId > 0 && $destinationServerId > 0 && $sourceServerId !== $destinationServerId) {
                $errors[] = 'The source and destination DNSPlus services use different DNSPlus API accounts. Server-side zone copy is not available between them.';
            }
        }

        $mode = (string) ($source['copy_mode'] ?? 'replace_matching');
        if (!in_array($mode, ['replace_matching', 'add_alongside', 'replace_entire'], true)) {
            $errors[] = 'Select a valid DNSPlus copy mode.';
            $mode = 'replace_matching';
        }

        return [
            'source_zone' => $sourceZone,
            'source_service_info' => $sourceService,
            'whmcs_client_id' => $clientId,
            'dnsplus_service_id' => $serviceId,
            'service_info' => $serviceInfo,
            'ownership_inherited' => $ownershipInherited,
            'create_if_missing' => isset($source['create_if_missing']) && (string) $source['create_if_missing'] === '1',
            'mode' => $mode,
            'follow_domain' => isset($source['follow_domain']) && (string) $source['follow_domain'] === '1',
            'copy_wr' => isset($source['copy_wr']) && (string) $source['copy_wr'] === '1',
            'copy_forwards' => isset($source['copy_forwards']) && (string) $source['copy_forwards'] === '1',
            'copy_hsts' => isset($source['copy_hsts']) && (string) $source['copy_hsts'] === '1',
        ];
    }

    return [];
}

function multibulkupdater_valid_hostname(string $hostname): bool
{
    if (strlen($hostname) > 253 || strpos($hostname, '.') === false) {
        return false;
    }

    return (bool) preg_match(
        '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i',
        $hostname
    );
}

function multibulkupdater_configured_nameserver_slots(array $vars): array
{
    $slots = [];
    for ($i = 1; $i <= 5; $i++) {
        $value = strtolower(trim((string) ($vars['ns' . $i] ?? '')));
        $slots[] = $value === '' ? '' : rtrim($value, '.');
    }

    return $slots;
}

function multibulkupdater_nameserver_groups(array $vars): array
{
    $groups = [
        'register' => [
            'label' => 'RegistrarDNS',
            'nameservers' => [
                'ns5.domainmonger.com',
                'ns6.domainmonger.com',
                'ns7.domainmonger.com',
                'ns8.domainmonger.com',
                '',
            ],
        ],
        'dnsplus' => [
            'label' => 'DNSPlus',
            'nameservers' => [
                'ns31.domainmonger.com',
                'ns32.domainmonger.com',
                'ns33.domainmonger.com',
                'ns34.domainmonger.com',
                '',
            ],
        ],
        'cpanel' => [
            'label' => 'cPanel',
            'nameservers' => [
                'ns50a.domainmonger.com',
                'ns50b.domainmonger.com',
                '',
                '',
                '',
            ],
        ],
    ];

    $configured = multibulkupdater_configured_nameserver_slots($vars);
    if (count(array_filter($configured, static fn(string $value): bool => $value !== '')) >= 2) {
        $groups['configured'] = [
            'label' => 'Configured Default',
            'nameservers' => $configured,
            'title' => 'Uses the Default Nameserver settings saved in WHMCS Addon Modules',
        ];
    }

    /**
     * Single source for BDM nameserver groups.
     * Any future BDM group added here is automatically available to other
     * DomainMonger admin tools that consume this helper.
     */
    return $groups;
}

function multibulkupdater_validate_epp(string $code): string
{
    if ($code === '') {
        return 'Enter the new EPP/Auth code.';
    }
    if (strlen($code) < 8 || strlen($code) > 64) {
        return 'EPP/Auth codes must be between 8 and 64 characters.';
    }
    if (!preg_match('/^[A-Za-z0-9._~!@#%^+=:\-]+$/', $code)) {
        return 'Use only letters, numbers, and these safe symbols: . _ ~ ! @ # % ^ + = : -';
    }

    return '';
}

function multibulkupdater_start_form(string $moduleLink, string $rawDomains, string $action, array $vars): string
{
    $nameserverGroups = multibulkupdater_nameserver_groups($vars);
    $hasConfiguredNameserverGroup = array_key_exists('configured', $nameserverGroups);

    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_step" id="mbu-main-step" value="confirm">';
    $html .= '<input type="hidden" name="mbu_page" id="mbu-main-page" value="bulk">';
    $listTitle = $action === 'copy_dnsplus_zone' ? 'Destination DNSPlus Zones' : 'Bulk Domain List';
    $listLabel = $action === 'copy_dnsplus_zone' ? 'Destination Zones' : 'Domains';
    $listHelp = $action === 'copy_dnsplus_zone'
        ? 'Enter destination DNS zone names, one per line. They do not need to exist in tbldomains. Missing zones can be created under the selected DNSPlus service.'
        : 'Enter one domain per line. Commas, spaces, and pasted columns are also accepted. Duplicate domains are removed.';
    $html .= '<div class="mbu-card-title">' . multibulkupdater_escape($listTitle) . '</div><div class="mbu-card-body">';
    $html .= '<label for="mbu-domains">' . multibulkupdater_escape($listLabel) . '</label>';
    $html .= '<textarea id="mbu-domains" name="domains" rows="12" placeholder="example.com&#10;example.net">'
        . multibulkupdater_escape($rawDomains) . '</textarea>';
    $html .= '<div class="mbu-help">' . multibulkupdater_escape($listHelp) . '</div>';

    $html .= '<input type="hidden" id="mbu-action" name="bulk_action" value="' . multibulkupdater_escape($action) . '">';

    $defaultRegPeriod = multibulkupdater_default_registration_period($vars);
    $renewYears = isset($_POST['renew_years']) && ctype_digit((string) $_POST['renew_years'])
        ? max(1, min(10, (int) $_POST['renew_years']))
        : $defaultRegPeriod;
    $renewUpdateRegPeriod = !isset($_POST['mbu_step']) || isset($_POST['renew_update_regperiod']);
    $html .= '<div id="mbu-renew-fields" class="mbu-fields">';
    $html .= '<h4>Renewal Period</h4>';
    $html .= '<label for="mbu-renew-years">Years</label><select id="mbu-renew-years" name="renew_years">';
    for ($year = 1; $year <= 10; $year++) {
        $html .= '<option value="' . $year . '"' . ($renewYears === $year ? ' selected' : '') . '>' . $year . ' Year' . ($year === 1 ? '' : 's') . '</option>';
    }
    $html .= '</select>';
    $html .= '<div class="mbu-copy-options"><label class="mbu-check"><input type="checkbox" name="renew_update_regperiod" value="1"' . ($renewUpdateRegPeriod ? ' checked' : '') . '> Update Registration Period</label></div>';
    $html .= '<div class="mbu-help">Defaults to ' . (int) $defaultRegPeriod . ' year' . ($defaultRegPeriod === 1 ? '' : 's') . ' every time Renew Domains is opened. The selected value is always sent to DomainRenew. WHMCS Registration Period is changed only when Update Registration Period is selected.</div>';
    $html .= '</div>';

    $html .= '<div id="mbu-nameserver-fields" class="mbu-fields">';
    $html .= '<h4>New Nameservers</h4>';
    $html .= '<div class="mbu-ns-presets" aria-label="Nameserver presets">';
    $html .= '<span class="mbu-preset-label">Apply preset:</span>';
    foreach ($nameserverGroups as $presetKey => $group) {
        $html .= '<button type="button" class="btn mbu-ns-preset" data-preset="' . multibulkupdater_escape((string) $presetKey) . '"';
        foreach ((array) ($group['nameservers'] ?? []) as $index => $nameserver) {
            if ($index >= 5) {
                break;
            }
            $html .= ' data-ns' . ($index + 1) . '="' . multibulkupdater_escape((string) $nameserver) . '"';
        }
        if (!empty($group['title'])) {
            $html .= ' title="' . multibulkupdater_escape((string) $group['title']) . '"';
        }
        $html .= '>' . multibulkupdater_escape((string) ($group['label'] ?? $presetKey)) . '</button>';
    }
    $html .= '</div>';
    if ($hasConfiguredNameserverGroup) {
        $html .= '<div class="mbu-help mbu-preset-help">Choose a preset to fill the fields, or enter nameservers manually. Configured Default uses the values saved in System Settings &gt; Addon Modules. Nothing is selected by default.</div>';
    } else {
        $html .= '<div class="mbu-help mbu-preset-help">Choose a built-in preset or enter nameservers manually. Save at least two Default Nameserver values in System Settings &gt; Addon Modules to add a Configured Default preset. Nothing is selected by default.</div>';
    }
    $html .= '<div class="mbu-grid">';
    for ($i = 1; $i <= 5; $i++) {
        $key = 'ns' . $i;
        $value = isset($_POST[$key]) ? (string) $_POST[$key] : '';
        $html .= '<div><label for="mbu-' . $key . '">Nameserver ' . $i . '</label>';
        $html .= '<input id="mbu-' . $key . '" type="text" name="' . $key . '" value="'
            . multibulkupdater_escape($value) . '" autocomplete="off"></div>';
    }
    $html .= '</div></div>';

    $html .= '<div id="mbu-epp-field" class="mbu-fields">';
    $html .= '<h4>New EPP/Auth Code</h4>';
    $html .= '<label for="mbu-epp">Apply this code to every submitted domain</label>';
    $html .= '<input id="mbu-epp" type="text" name="epp" value="'
        . multibulkupdater_escape((string) ($vars['epp'] ?? '')) . '" autocomplete="off">';
    $html .= '<div class="mbu-help">8–64 characters. The code is not written to the activity log.</div></div>';

    $html .= '<div id="mbu-copy-dnsplus-fields" class="mbu-fields">';
    $html .= '<h4>DNSPlus Zone Copy</h4>';
    $html .= '<div class="mbu-grid">';
    $sourceZoneRequired = $action === 'copy_dnsplus_zone' ? ' required' : '';
    $html .= '<div><label for="mbu-source-zone">Source Zone <span class="text-danger">*</span></label><input id="mbu-source-zone" type="text" name="source_zone" value="' . multibulkupdater_escape((string) ($_POST['source_zone'] ?? '')) . '" placeholder="source-domain.com" autocomplete="off"' . $sourceZoneRequired . '></div>';
    $html .= '<div><label for="mbu-client-id">WHMCS Account # <span class="mbu-help-inline">(optional override)</span></label><input id="mbu-client-id" type="text" name="whmcs_client_id" value="' . multibulkupdater_escape((string) ($_POST['whmcs_client_id'] ?? '')) . '" inputmode="numeric" placeholder="Use source zone account" autocomplete="off"></div>';
    $html .= '<div><label for="mbu-dnsplus-service-id">DNSPlus Product/Service # <span class="mbu-help-inline">(optional override)</span></label><input id="mbu-dnsplus-service-id" type="text" name="dnsplus_service_id" value="' . multibulkupdater_escape((string) ($_POST['dnsplus_service_id'] ?? '')) . '" inputmode="numeric" placeholder="Use source zone service" autocomplete="off"></div>';
    $html .= '<div><label for="mbu-copy-mode">Copy Mode</label><select id="mbu-copy-mode" name="copy_mode">';
    $selectedMode = (string) ($_POST['copy_mode'] ?? 'replace_matching');
    foreach (['replace_matching' => 'Replace Matching Records', 'add_alongside' => 'Add Alongside Existing Records', 'replace_entire' => 'Replace Entire Zone'] as $modeValue => $modeLabel) {
        $html .= '<option value="' . multibulkupdater_escape($modeValue) . '"' . ($selectedMode === $modeValue ? ' selected' : '') . '>' . multibulkupdater_escape($modeLabel) . '</option>';
    }
    $html .= '</select></div></div>';
    $html .= '<div class="mbu-copy-options">';
    $html .= '<label class="mbu-check"><input type="checkbox" name="create_if_missing" value="1"' . (!isset($_POST['mbu_step']) || isset($_POST['create_if_missing']) ? ' checked' : '') . '> Create missing destination zones and attach them to this DNSPlus service</label>';
    $html .= '<label class="mbu-check"><input type="checkbox" name="follow_domain" value="1"' . (!isset($_POST['mbu_step']) || isset($_POST['follow_domain']) ? ' checked' : '') . '> Update records to follow each destination domain</label>';
    $html .= '<label class="mbu-check"><input type="checkbox" name="copy_wr" value="1"' . (!isset($_POST['mbu_step']) || isset($_POST['copy_wr']) ? ' checked' : '') . '> Copy Web Redirect (WR) records</label>';
    $html .= '<label class="mbu-check"><input type="checkbox" name="copy_forwards" value="1"' . (!isset($_POST['mbu_step']) || isset($_POST['copy_forwards']) ? ' checked' : '') . '> Copy Mail Forwards</label>';
    $html .= '<label class="mbu-check"><input type="checkbox" name="copy_hsts" value="1"' . (isset($_POST['copy_hsts']) ? ' checked' : '') . '> Copy HSTS state when FreeSSL is already active on the destination</label>';
    $html .= '</div>';
    $html .= "<div class=\"mbu-help\">Leave WHMCS Account # and DNSPlus Product/Service # blank to use the source zone's current account and service. To copy into a different account/product, enter the destination DNSPlus Product/Service #; its WHMCS account is inferred automatically, or can be entered as an additional check. Existing destination zones must already be attached to the resolved destination service. Replace Entire Zone deletes destination DNS records and Mail Forwards before copying.</div>";
    $html .= '</div>';

    $html .= '<div class="mbu-actions"><button type="submit" class="btn btn-primary">Review Domains</button></div>';
    $html .= '</div></form>';
    $html .= multibulkupdater_action_script();

    return $html;
}

function multibulkupdater_confirm_form(string $moduleLink, string $rawDomains, array $domains, string $action, array $payload): string
{
    $label = multibulkupdater_actions()[$action];
    $html = '<form method="post" action="' . multibulkupdater_escape($moduleLink) . '" class="mbu-card">';
    $html .= '<input type="hidden" name="token" value="' . multibulkupdater_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="mbu_step" value="execute">';
    $html .= '<input type="hidden" name="bulk_action" value="' . multibulkupdater_escape($action) . '">';
    $html .= '<textarea name="domains" class="mbu-hidden">' . multibulkupdater_escape($rawDomains) . '</textarea>';

    if ($action === 'renew_domains') {
        $html .= '<input type="hidden" name="renew_years" value="' . (int) ($payload['years'] ?? 1) . '">';
        if (!empty($payload['update_regperiod'])) {
            $html .= '<input type="hidden" name="renew_update_regperiod" value="1">';
        }
    } elseif ($action === 'nameservers') {
        foreach ($payload['nameservers'] as $index => $nameserver) {
            $html .= '<input type="hidden" name="ns' . ($index + 1) . '" value="' . multibulkupdater_escape($nameserver) . '">';
        }
    } elseif ($action === 'update_epp') {
        $html .= '<input type="hidden" name="epp" value="' . multibulkupdater_escape($payload['epp']) . '">';
    } elseif ($action === 'copy_dnsplus_zone') {
        foreach (['source_zone', 'whmcs_client_id', 'dnsplus_service_id'] as $key) {
            $html .= '<input type="hidden" name="' . multibulkupdater_escape($key) . '" value="' . multibulkupdater_escape((string) ($payload[$key] ?? '')) . '">';
        }
        $html .= '<input type="hidden" name="copy_mode" value="' . multibulkupdater_escape((string) $payload['mode']) . '">';
        foreach (['create_if_missing', 'follow_domain', 'copy_wr', 'copy_forwards', 'copy_hsts'] as $key) {
            if (!empty($payload[$key])) {
                $html .= '<input type="hidden" name="' . multibulkupdater_escape($key) . '" value="1">';
            }
        }
    }

    $html .= '<div class="mbu-card-title">Confirm ' . multibulkupdater_escape($label) . '</div><div class="mbu-card-body">';
    if ($action === 'copy_dnsplus_zone') {
        $warning = $payload['mode'] === 'replace_entire'
            ? 'Replace Entire Zone is destructive. Existing DNS records and Mail Forwards in each destination zone will be removed before the source is copied.'
            : 'This will contact DNSPlus for ' . count($domains) . ' destination zone' . (count($domains) === 1 ? '' : 's') . '. Review the ownership and copy mode before continuing.';
        $html .= multibulkupdater_alert('warning', multibulkupdater_escape($warning));
    } else {
        $html .= multibulkupdater_alert('warning', 'This will contact the registrar for ' . count($domains) . ' domain' . (count($domains) === 1 ? '' : 's') . '. Review the list before continuing.');
    }

    if ($action === 'renew_domains') {
        $years = (int) ($payload['years'] ?? 1);
        $html .= '<div class="mbu-summary"><strong>Renewal Period:</strong> ' . $years . ' Year' . ($years === 1 ? '' : 's') . '<br>';
        $html .= '<strong>Update WHMCS Registration Period:</strong> ' . (!empty($payload['update_regperiod']) ? 'Yes — set to ' . $years : 'No — leave unchanged') . '<br>';
        $html .= '<strong>After registrar renewal:</strong> run Sync Domain for each renewed domain.</div>';
    } elseif ($action === 'nameservers') {
        $html .= '<div class="mbu-summary"><strong>Nameservers:</strong> '
            . multibulkupdater_escape(implode(', ', $payload['nameservers'])) . '</div>';
    } elseif ($action === 'update_epp') {
        $html .= '<div class="mbu-summary"><strong>EPP/Auth code:</strong> ' . str_repeat('•', max(8, strlen($payload['epp']))) . '</div>';
    } elseif ($action === 'copy_dnsplus_zone') {
        $service = $payload['service_info'] ?? [];
        $modeLabels = ['replace_matching' => 'Replace Matching Records', 'add_alongside' => 'Add Alongside Existing Records', 'replace_entire' => 'Replace Entire Zone'];
        $clientLabel = trim((string) ($service['client_name'] ?? ''));
        if (!empty($service['company_name'])) {
            $clientLabel .= ($clientLabel !== '' ? ' — ' : '') . (string) $service['company_name'];
        }
        $sourceService = $payload['source_service_info'] ?? [];
        $html .= '<div class="mbu-summary"><strong>Source Zone:</strong> ' . multibulkupdater_escape((string) $payload['source_zone']) . '<br>';
        if ($sourceService) {
            $html .= '<strong>Source Ownership:</strong> WHMCS #' . (int) ($sourceService['client_id'] ?? 0) . ' — ' . multibulkupdater_escape((string) ($sourceService['product_name'] ?? '')) . ' — Service #' . (int) ($sourceService['service_id'] ?? 0) . '<br>';
        }
        $html .= '<strong>Destination WHMCS Account:</strong> #' . (int) $payload['whmcs_client_id'] . ($clientLabel !== '' ? ' — ' . multibulkupdater_escape($clientLabel) : '') . (!empty($payload['ownership_inherited']) ? ' <span class="mbu-help-inline">(inherited from source)</span>' : '') . '<br>';
        $html .= '<strong>Destination DNSPlus Product:</strong> ' . multibulkupdater_escape((string) ($service['product_name'] ?? '')) . ' — Service #' . (int) $payload['dnsplus_service_id'] . (!empty($payload['ownership_inherited']) ? ' <span class="mbu-help-inline">(inherited from source)</span>' : '') . '<br>';
        $html .= '<strong>Copy Mode:</strong> ' . multibulkupdater_escape($modeLabels[$payload['mode']] ?? $payload['mode']) . '<br>';
        $html .= '<strong>Create Missing Zones:</strong> ' . (!empty($payload['create_if_missing']) ? 'Yes' : 'No') . ' &nbsp; <strong>Follow Domain:</strong> ' . (!empty($payload['follow_domain']) ? 'Yes' : 'No') . '<br>';
        $html .= '<strong>WR:</strong> ' . (!empty($payload['copy_wr']) ? 'Copy' : 'Skip') . ' &nbsp; <strong>Mail Forwards:</strong> ' . (!empty($payload['copy_forwards']) ? 'Copy' : 'Skip') . ' &nbsp; <strong>HSTS:</strong> ' . (!empty($payload['copy_hsts']) ? 'Copy when available' : 'Skip') . '</div>';
    }

    $html .= '<div class="mbu-domain-list">';
    foreach ($domains as $domain) {
        $html .= '<div>' . multibulkupdater_escape($domain) . '</div>';
    }
    $html .= '</div>';

    $html .= '<div class="mbu-actions">';
    $html .= '<a class="btn btn-default" href="' . multibulkupdater_escape($moduleLink) . '">Cancel</a>';
    $html .= '<button type="submit" class="btn btn-primary mbu-submit-button" data-processing-text="Processing…">';
    $html .= '<span class="mbu-button-spinner" aria-hidden="true"></span>';
    $html .= '<span class="mbu-button-label">' . ($action === 'copy_dnsplus_zone' ? 'Copy Zones' : ($action === 'renew_domains' ? 'Renew Domains' : 'Run Bulk Update')) . '</span></button></div>';
    $html .= '</div></form>';

    return $html;
}

function multibulkupdater_execute(array $domains, string $action, array $payload): array
{
    @set_time_limit(0);
    $results = [];

    if ($action === 'copy_dnsplus_zone') {
        return multibulkupdater_execute_dnsplus_copy($domains, $payload);
    }

    if ($action === 'renew_domains') {
        return multibulkupdater_execute_renewals($domains, (int) ($payload['years'] ?? 1), !empty($payload['update_regperiod']));
    }

    if ($action === 'update_epp') {
        multibulkupdater_load_epp_manager();
    }

    foreach ($domains as $domainName) {
        try {
            $domain = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $results[] = multibulkupdater_result($domainName, false, 'WHMCS database lookup failed: ' . $e->getMessage());
            continue;
        }

        if (!$domain) {
            $results[] = multibulkupdater_result($domainName, false, 'Domain not found in WHMCS.');
            continue;
        }

        try {
            if ($action === 'nameservers') {
                $params = ['domainid' => (int) $domain->id];
                foreach ($payload['nameservers'] as $index => $nameserver) {
                    $params['ns' . ($index + 1)] = $nameserver;
                }
                $response = localAPI('DomainUpdateNameservers', $params);
                $results[] = multibulkupdater_api_result($domainName, $response, 'Nameservers updated.');
            } elseif ($action === 'lock' || $action === 'unlock') {
                $shouldLock = $action === 'lock';
                $response = localAPI('DomainUpdateLockingStatus', [
                    'domainid' => (int) $domain->id,
                    'lockstatus' => $shouldLock ? 1 : 0,
                ]);
                $results[] = multibulkupdater_lock_result(
                    $domainName,
                    (int) $domain->id,
                    $response,
                    $shouldLock
                );
            } elseif ($action === 'disable_privacy') {
                $response = localAPI('DomainToggleIdProtect', [
                    'domainid' => (int) $domain->id,
                    'idprotect' => 0,
                ]);
                $results[] = multibulkupdater_api_result($domainName, $response, 'WHOIS privacy disabled.');
            } elseif ($action === 'update_epp') {
                if (!function_exists('dm_epp_modify_auth_code')) {
                    $results[] = multibulkupdater_result($domainName, false, 'The DomainMonger EPP/Auth code manager is not available.');
                    continue;
                }

                [$ok, $message] = dm_epp_modify_auth_code($domain, $payload['epp']);
                $results[] = multibulkupdater_result($domainName, (bool) $ok, (string) $message);
            }
        } catch (Throwable $e) {
            $results[] = multibulkupdater_result($domainName, false, $e->getMessage());
        }
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;

    if (function_exists('logActivity')) {
        logActivity(
            'Bulk Domain Manager: ' . (multibulkupdater_actions()[$action] ?? $action)
            . ' completed for ' . count($results) . ' domain(s): '
            . $successCount . ' successful, ' . $failureCount . ' failed.'
        );
    }

    return $results;
}

function multibulkupdater_execute_renewals(array $domains, int $years, bool $updateRegPeriod): array
{
    $years = max(1, min(10, $years));
    $results = [];

    foreach ($domains as $domainName) {
        try {
            $domain = Capsule::table('tbldomains')->where('domain', $domainName)->first();
        } catch (Throwable $e) {
            $results[] = array_merge(
                multibulkupdater_result($domainName, false, 'WHMCS database lookup failed: ' . $e->getMessage()),
                [
                    'domain_id' => 0,
                    'client_id' => 0,
                    'renewed' => false,
                    'regperiod_requested' => $updateRegPeriod,
                    'regperiod_updated' => false,
                    'years' => $years,
                ]
            );
            continue;
        }

        if (!$domain) {
            $results[] = array_merge(
                multibulkupdater_result($domainName, false, 'Domain not found in WHMCS.'),
                [
                    'domain_id' => 0,
                    'client_id' => 0,
                    'renewed' => false,
                    'regperiod_requested' => $updateRegPeriod,
                    'regperiod_updated' => false,
                    'years' => $years,
                ]
            );
            continue;
        }

        $domainId = (int) ($domain->id ?? 0);
        $clientId = (int) ($domain->userid ?? 0);

        try {
            $renew = localAPI('DomainRenew', [
                'domainid' => $domainId,
                'regperiod' => $years,
            ]);
        } catch (Throwable $e) {
            $renew = ['result' => 'error', 'message' => $e->getMessage()];
        }

        if (!is_array($renew) || strtolower((string) ($renew['result'] ?? '')) !== 'success') {
            $message = is_array($renew)
                ? trim((string) ($renew['message'] ?? $renew['error'] ?? 'Registrar renewal failed.'))
                : 'WHMCS returned an invalid DomainRenew response.';
            $results[] = array_merge(
                multibulkupdater_result($domainName, false, $message !== '' ? $message : 'Registrar renewal failed.'),
                [
                    'domain_id' => $domainId,
                    'client_id' => $clientId,
                    'renewed' => false,
                    'regperiod_requested' => $updateRegPeriod,
                    'regperiod_updated' => false,
                    'years' => $years,
                ]
            );
            continue;
        }

        $regperiodUpdated = false;
        $regperiodMessage = '';
        if ($updateRegPeriod) {
            $periodUpdate = multibulkupdater_update_registration_period($domainId, $years);
            $regperiodUpdated = !empty($periodUpdate['ok']);
            if (!$regperiodUpdated) {
                $regperiodMessage = (string) ($periodUpdate['message'] ?? 'WHMCS Registration Period update failed.');
            }
        }

        $message = 'Registrar renewal completed for ' . $years . ' year' . ($years === 1 ? '' : 's') . '.';
        if (!$updateRegPeriod) {
            $message .= ' WHMCS Registration Period left unchanged.';
        } elseif ($regperiodUpdated) {
            $message .= ' WHMCS Registration Period updated to ' . $years . ' year' . ($years === 1 ? '' : 's') . '.';
        } else {
            $message .= ' Registration Period update failed'
                . ($regperiodMessage !== '' ? ': ' . $regperiodMessage : '.')
                . ' Sync will still run because the registrar renewal already completed.';
        }

        $results[] = array_merge(
            multibulkupdater_result($domainName, !$updateRegPeriod || $regperiodUpdated, $message),
            [
                'domain_id' => $domainId,
                'client_id' => $clientId,
                'renewed' => true,
                'regperiod_requested' => $updateRegPeriod,
                'regperiod_updated' => $regperiodUpdated,
                'years' => $years,
            ]
        );
    }

    $renewedCount = count(array_filter($results, static fn(array $row): bool => !empty($row['renewed'])));
    $renewFailureCount = count($results) - $renewedCount;
    $regperiodFailureCount = count(array_filter(
        $results,
        static fn(array $row): bool => !empty($row['renewed']) && !empty($row['regperiod_requested']) && empty($row['regperiod_updated'])
    ));

    if (function_exists('logActivity')) {
        logActivity(
            'Bulk Domain Manager: Renew Domains submitted for ' . count($results) . ' domain(s) at '
            . $years . ' year' . ($years === 1 ? '' : 's') . ': '
            . $renewedCount . ' registrar renewal(s) successful, '
            . $renewFailureCount . ' renewal(s) failed, '
            . ($updateRegPeriod
                ? $regperiodFailureCount . ' WHMCS Registration Period update(s) failed. '
                : 'WHMCS Registration Period update not requested. ')
            . 'Successful registrar renewals will run the native Sync Domain command from the results page.'
        );
    }

    return $results;
}

function multibulkupdater_execute_dnsplus_copy(array $destinations, array $payload): array
{
    $serviceId = (int) ($payload['dnsplus_service_id'] ?? 0);
    $clientId = (int) ($payload['whmcs_client_id'] ?? 0);
    $sourceZone = strtolower(rtrim(trim((string) ($payload['source_zone'] ?? '')), '.'));
    $serviceInfo = multibulkupdater_dnsplus_service_info($serviceId, $clientId);

    if (!$serviceInfo || strcasecmp((string) ($serviceInfo['service_status'] ?? ''), 'Active') !== 0) {
        return [multibulkupdater_result($sourceZone !== '' ? $sourceZone : 'DNSPlus', false, 'The selected DNSPlus service is no longer an active service under the specified WHMCS account.')];
    }

    try {
        $params = multibulkupdater_dnsplus_module_params($serviceId);
        multibulkupdater_load_cloudns_module();
        $cloudns = Cloudns_Core::inst($params);
    } catch (Throwable $e) {
        return [multibulkupdater_result($sourceZone !== '' ? $sourceZone : 'DNSPlus', false, $e->getMessage())];
    }

    $options = [
        'mode' => (string) ($payload['mode'] ?? 'replace_matching'),
        'follow_domain' => !empty($payload['follow_domain']),
        'copy_wr' => !empty($payload['copy_wr']),
        'copy_forwards' => !empty($payload['copy_forwards']),
        'copy_hsts' => !empty($payload['copy_hsts']),
    ];
    $createIfMissing = !empty($payload['create_if_missing']);
    $results = [];

    foreach ($destinations as $destination) {
        $destination = strtolower(rtrim(trim((string) $destination), '.'));
        if ($destination === '' || !multibulkupdater_valid_hostname($destination)) {
            $results[] = multibulkupdater_result($destination !== '' ? $destination : '(blank)', false, 'Invalid destination DNS zone name.');
            continue;
        }
        if ($destination === $sourceZone) {
            $results[] = multibulkupdater_result($destination, false, 'The destination cannot be the same as the source zone.');
            continue;
        }

        try {
            $prepared = $cloudns->Copyzone->prepareDestination($destination, $createIfMissing);
            if (!is_array($prepared) || (string) ($prepared['status'] ?? '') !== 'success') {
                $message = is_array($prepared) ? (string) ($prepared['description'] ?? 'The destination zone could not be prepared.') : 'The destination zone could not be prepared.';
                $results[] = multibulkupdater_result($destination, false, $message);
                continue;
            }

            $copy = $cloudns->Copyzone->copy($sourceZone, $destination, $options);
            if (!is_array($copy) || (string) ($copy['status'] ?? '') !== 'success') {
                $message = is_array($copy) ? (string) ($copy['description'] ?? 'DNSPlus did not confirm the zone copy.') : 'DNSPlus did not confirm the zone copy.';
                if (!empty($prepared['created'])) {
                    $rollback = $cloudns->Copyzone->rollbackCreatedDestination($destination);
                    $rollbackOk = is_array($rollback) && (string) ($rollback['status'] ?? '') === 'success';
                    if ($rollbackOk) {
                        $message = 'Copy failed and the newly created destination zone was rolled back: ' . $message;
                    } else {
                        $rollbackMessage = is_array($rollback) ? (string) ($rollback['description'] ?? 'unknown rollback error') : 'unknown rollback error';
                        $message = 'Destination zone was created and attached, the copy failed, and automatic rollback also failed (' . $rollbackMessage . '): ' . $message;
                    }
                }
                $results[] = multibulkupdater_result($destination, false, $message);
                continue;
            }

            $details = [];
            if (!empty($prepared['created'])) {
                $details[] = 'zone created and attached';
            }
            $details[] = 'DNS records copied';
            if (!empty($payload['copy_wr']) && !empty($copy['wr_added'])) {
                $details[] = (int) $copy['wr_added'] . ' WR added separately';
            }
            if (!empty($payload['copy_forwards'])) {
                $details[] = (int) ($copy['forwards_added'] ?? 0) . ' Mail Forward' . ((int) ($copy['forwards_added'] ?? 0) === 1 ? '' : 's') . ' added';
            }
            if (!empty($payload['copy_hsts'])) {
                $hsts = (string) ($copy['hsts'] ?? 'not-requested');
                if ($hsts === 'active' || $hsts === 'inactive') {
                    $details[] = 'HSTS ' . $hsts;
                } elseif ($hsts === 'destination-freessl-inactive') {
                    $details[] = 'HSTS skipped (destination FreeSSL inactive)';
                } elseif ($hsts === 'source-unavailable') {
                    $details[] = 'HSTS skipped (source state unavailable)';
                }
            }
            $results[] = multibulkupdater_result($destination, true, ucfirst(implode('; ', $details)) . '.');
        } catch (Throwable $e) {
            $results[] = multibulkupdater_result($destination, false, $e->getMessage());
        }
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;
    if (function_exists('logActivity')) {
        logActivity(
            'Bulk Domain Manager: DNSPlus zone copy from ' . $sourceZone . ' completed for ' . count($results)
            . ' destination zone(s): ' . $successCount . ' successful, ' . $failureCount . ' failed. '
            . 'Destination WHMCS client #' . $clientId . ', DNSPlus service #' . $serviceId . '.'
        );
    }

    return $results;
}

function multibulkupdater_load_epp_manager(): void
{
    if (function_exists('dm_epp_modify_auth_code')) {
        return;
    }

    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $file = $root . '/includes/hooks/domainmonger_epp_authcode_manager.php';
    if (is_file($file)) {
        require_once $file;
    }
}

function multibulkupdater_lock_result(string $domain, int $domainId, $response, bool $shouldLock): array
{
    $successMessage = $shouldLock ? 'Domain locked.' : 'Domain unlocked.';

    if (is_array($response) && strtolower((string) ($response['result'] ?? '')) === 'success') {
        return multibulkupdater_result($domain, true, $successMessage);
    }

    $originalMessage = is_array($response)
        ? trim((string) ($response['message'] ?? $response['error'] ?? 'Registrar update failed.'))
        : 'WHMCS returned an invalid registrar response.';

    // Some registrar modules complete the lock change but return a generic
    // "Registrar Error Message" response to DomainUpdateLockingStatus.
    // Verify the live registrar state before reporting the operation as failed.
    for ($attempt = 0; $attempt < 2; $attempt++) {
        if ($attempt > 0) {
            usleep(350000);
        }

        $verification = localAPI('DomainGetLockingStatus', [
            'domainid' => $domainId,
        ]);

        if (!is_array($verification)
            || strtolower((string) ($verification['result'] ?? '')) !== 'success'
            || !array_key_exists('lockstatus', $verification)
        ) {
            continue;
        }

        if (multibulkupdater_lock_status_matches($verification['lockstatus'], $shouldLock)) {
            return multibulkupdater_result(
                $domain,
                true,
                $successMessage . ' Verified after the registrar returned an incorrect error response.'
            );
        }

        break;
    }

    return multibulkupdater_result(
        $domain,
        false,
        $originalMessage !== '' ? $originalMessage : 'Registrar update failed.'
    );
}

function multibulkupdater_lock_status_matches($status, bool $shouldLock): bool
{
    if (is_bool($status)) {
        return $status === $shouldLock;
    }

    if (is_int($status) || is_float($status)) {
        return ((int) $status === 1) === $shouldLock;
    }

    $normalized = strtolower(trim((string) $status));
    $normalized = str_replace([' ', '-', '_'], '', $normalized);

    $lockedValues = [
        '1', 'true', 'yes', 'on', 'enabled', 'locked', 'lock',
        'clienttransferprohibited',
    ];
    $unlockedValues = [
        '0', 'false', 'no', 'off', 'disabled', 'unlocked', 'unlock',
    ];

    if ($shouldLock) {
        return in_array($normalized, $lockedValues, true);
    }

    return in_array($normalized, $unlockedValues, true);
}

function multibulkupdater_api_result(string $domain, $response, string $successMessage): array
{
    if (!is_array($response)) {
        return multibulkupdater_result($domain, false, 'WHMCS returned an invalid registrar response.');
    }

    if (strtolower((string) ($response['result'] ?? '')) === 'success') {
        return multibulkupdater_result($domain, true, $successMessage);
    }

    $message = trim((string) ($response['message'] ?? $response['error'] ?? 'Registrar update failed.'));
    return multibulkupdater_result($domain, false, $message !== '' ? $message : 'Registrar update failed.');
}

function multibulkupdater_result(string $domain, bool $success, string $message): array
{
    return [
        'domain' => $domain,
        'success' => $success,
        'message' => trim($message),
    ];
}

function multibulkupdater_renew_results(string $moduleLink, array $results): string
{
    $renewed = array_values(array_filter($results, static fn(array $row): bool => !empty($row['renewed'])));
    $renewFailed = count($results) - count($renewed);
    $regperiodFailed = count(array_filter(
        $renewed,
        static fn(array $row): bool => !empty($row['regperiod_requested']) && empty($row['regperiod_updated'])
    ));

    $syncItems = [];
    foreach ($renewed as $row) {
        $domainId = (int) ($row['domain_id'] ?? 0);
        $clientId = (int) ($row['client_id'] ?? 0);
        if ($domainId > 0 && $clientId > 0) {
            $syncItems[] = [
                'id' => $domainId,
                'clientId' => $clientId,
                'domain' => (string) ($row['domain'] ?? ('Domain #' . $domainId)),
            ];
        }
    }

    $html = '<div class="mbu-card"><div class="mbu-card-title">Renew Domain Results</div><div class="mbu-card-body">';
    $html .= '<div class="mbu-result-summary" id="mbu-renew-summary">';
    $html .= '<span class="mbu-success">' . count($renewed) . ' renewed</span>';
    if ($renewFailed > 0) {
        $html .= '<span class="mbu-failure">' . $renewFailed . ' renewal failed</span>';
    }
    if ($regperiodFailed > 0) {
        $html .= '<span class="mbu-failure">' . $regperiodFailed . ' Registration Period update failed</span>';
    }
    if ($syncItems) {
        $html .= '<span class="mbu-status-neutral" id="mbu-renew-sync-summary">Syncing 0 of ' . count($syncItems) . '</span>';
    }
    $html .= '</div>';

    if ($syncItems) {
        $html .= multibulkupdater_alert('info', 'Registrar renewals are complete. The native WHMCS Sync Domain command will now run automatically for each successfully renewed domain. Keep this page open until Sync shows Complete.');
    }

    $html .= '<div class="table-responsive"><table class="datatable table table-striped"><thead><tr>';
    $html .= '<th>Domain</th><th>Renewal</th><th>Registration Period</th><th>Sync</th><th>Result</th></tr></thead><tbody>';

    foreach ($results as $row) {
        $domainId = (int) ($row['domain_id'] ?? 0);
        $renewOk = !empty($row['renewed']);
        $regperiodRequested = !empty($row['regperiod_requested']);
        $regperiodOk = !empty($row['regperiod_updated']);
        $years = max(1, (int) ($row['years'] ?? 1));
        $syncId = 'mbu-renew-sync-' . $domainId;
        $detailId = 'mbu-renew-detail-' . $domainId;

        $html .= '<tr data-mbu-renew-domain-id="' . $domainId . '">';
        $html .= '<td>' . multibulkupdater_escape((string) ($row['domain'] ?? '')) . '</td>';
        $html .= '<td><span class="' . ($renewOk ? 'mbu-status-success' : 'mbu-status-failed') . '">' . ($renewOk ? 'Renewed' : 'Failed') . '</span></td>';
        if ($renewOk) {
            if (!$regperiodRequested) {
                $html .= '<td><span class="mbu-status-neutral">Not Changed</span></td>';
            } else {
                $html .= '<td><span class="' . ($regperiodOk ? 'mbu-status-success' : 'mbu-status-failed') . '">'
                    . ($regperiodOk ? $years . ' Year' . ($years === 1 ? '' : 's') : 'Update Failed') . '</span></td>';
            }
            $html .= '<td><span id="' . multibulkupdater_escape($syncId) . '" class="mbu-status-neutral">Pending</span></td>';
        } else {
            $html .= '<td><span class="mbu-status-neutral">Not Changed</span></td>';
            $html .= '<td><span class="mbu-status-neutral">Not Run</span></td>';
        }
        $html .= '<td id="' . multibulkupdater_escape($detailId) . '">' . multibulkupdater_escape((string) ($row['message'] ?? '')) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    $html .= '<div class="mbu-actions"><a class="btn btn-primary" href="' . multibulkupdater_escape($moduleLink) . '">Run Another Update</a></div>';
    $html .= '</div></div>';

    if ($syncItems) {
        $json = json_encode($syncItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json !== false) {
            $html .= multibulkupdater_renew_sync_script($json);
        }
    }

    return $html;
}

function multibulkupdater_renew_sync_script(string $itemsJson): string
{
    $script = <<<'HTML'
<script>
(function () {
    'use strict';

    const items = __MBU_RENEW_ITEMS__;

    function normalise(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function controlLabel(control) {
        const value = control.tagName === 'INPUT' ? control.value : control.textContent;
        return normalise(value || control.getAttribute('aria-label') || control.getAttribute('title'));
    }

    function findSyncControl(doc) {
        const controls = doc.querySelectorAll('button, input[type="submit"], input[type="button"], a');
        for (let i = 0; i < controls.length; i += 1) {
            if (controlLabel(controls[i]) === 'sync domain') {
                return controls[i];
            }
        }
        return null;
    }

    function findWhmcsStatusControl(doc) {
        if (!doc) {
            return null;
        }
        const selectors = [
            'select[name="status"]',
            'select[name="domainstatus"]',
            'select#status',
            'input[name="status"]',
            'input[name="domainstatus"]'
        ];
        for (let i = 0; i < selectors.length; i += 1) {
            const control = doc.querySelector(selectors[i]);
            if (control) {
                return control;
            }
        }
        return null;
    }

    function readWhmcsStatusState(doc) {
        const control = findWhmcsStatusControl(doc);
        if (!control) {
            return {label: '', value: ''};
        }
        const value = String(control.value || '').replace(/\s+/g, ' ').trim();
        let label = value;
        if (control.tagName === 'SELECT' && control.selectedIndex >= 0) {
            const option = control.options[control.selectedIndex];
            label = option ? (option.textContent || option.value || value) : value;
        }
        return {label: String(label || '').replace(/\s+/g, ' ').trim(), value: value};
    }

    function sameStatusState(left, right) {
        const leftValue = normalise(left && left.value);
        const rightValue = normalise(right && right.value);
        if (leftValue && rightValue) {
            return leftValue === rightValue;
        }
        return normalise(left && left.label) === normalise(right && right.label);
    }

    function shouldPersistStatusChange(beforeState, afterState) {
        return !!(beforeState && afterState && beforeState.label && afterState.label && !sameStatusState(beforeState, afterState));
    }

    function findSaveChangesControl(doc, form) {
        if (!doc || !form) {
            return null;
        }
        const controls = doc.querySelectorAll('button, input[type="submit"], input[type="button"]');
        for (let i = 0; i < controls.length; i += 1) {
            if (controls[i].form !== form) {
                continue;
            }
            const label = controlLabel(controls[i]);
            if (label === 'save changes' || label.indexOf('save changes') !== -1) {
                return controls[i];
            }
        }
        return null;
    }

    function noticeSnapshot(doc) {
        const snapshot = {};
        const notices = doc.querySelectorAll('.alert-danger, .alert-error, .errorbox, .alert-warning, .alert-success, .successbox');
        for (let i = 0; i < notices.length; i += 1) {
            const message = String(notices[i].textContent || '').replace(/\s+/g, ' ').trim();
            if (message) {
                snapshot[message] = true;
            }
        }
        return snapshot;
    }

    function resultNotice(doc, ignoredMessages) {
        const selectors = ['.alert-danger', '.alert-error', '.errorbox', '.alert-warning', '.alert-success', '.successbox'];
        for (let i = 0; i < selectors.length; i += 1) {
            const notices = doc.querySelectorAll(selectors[i]);
            for (let j = 0; j < notices.length; j += 1) {
                const message = String(notices[j].textContent || '').replace(/\s+/g, ' ').trim();
                if (!message || (ignoredMessages && ignoredMessages[message])) {
                    continue;
                }
                const lower = message.toLowerCase();
                const failed = selectors[i].indexOf('danger') !== -1 || selectors[i].indexOf('error') !== -1 || /\b(error|failed|failure|unable)\b/.test(lower);
                const warning = selectors[i].indexOf('warning') !== -1;
                return {type: failed ? 'error' : (warning ? 'warning' : 'success'), message: message.substring(0, 500)};
            }
        }
        return null;
    }

    function alertResult(message) {
        const clean = String(message || '').replace(/\s+/g, ' ').trim();
        if (!clean) {
            return null;
        }
        return {type: /\b(error|failed|failure|unable)\b/.test(clean.toLowerCase()) ? 'error' : 'success', message: clean.substring(0, 500)};
    }

    function appendDetail(message, addition) {
        message = String(message || '').replace(/\s+/g, ' ').trim();
        return (message ? message + ' ' : '') + addition;
    }

    function syncOne(item) {
        return new Promise(function (resolve) {
            const frame = document.createElement('iframe');
            let commandStarted = false;
            let actionDocument = null;
            let finished = false;
            let observer = null;
            let timer = null;
            let baselineNotices = {};
            let capturedAlert = '';
            let statusBeforeState = {label: '', value: ''};
            let saveStarted = false;
            let saveDocument = null;
            let saveExpectedState = null;
            let pendingSaveResult = null;

            function domainPageUrl(extra) {
                return 'clientsdomains.php?userid=' + encodeURIComponent(item.clientId)
                    + '&domainid=' + encodeURIComponent(item.id)
                    + (extra || '');
            }

            function complete(result) {
                if (finished) {
                    return;
                }
                result = result || {type: 'error', message: 'The Sync Domain result was unavailable.'};
                finished = true;
                window.clearTimeout(timer);
                if (observer) {
                    observer.disconnect();
                }
                if (frame.parentNode) {
                    frame.parentNode.removeChild(frame);
                }
                resolve(result);
            }

            function verifySavedStatus() {
                if (finished || !saveStarted) {
                    return;
                }
                const result = pendingSaveResult || {type: 'error', message: 'The Sync Domain result was unavailable.'};
                try {
                    const savedState = readWhmcsStatusState(frame.contentDocument);
                    if (sameStatusState(savedState, saveExpectedState)) {
                        result.message = appendDetail(result.message, 'WHMCS status saved.');
                        complete(result);
                        return;
                    }
                    result.type = 'error';
                    result.message = appendDetail(result.message, 'The status change was returned by Sync Domain but WHMCS did not save it.');
                    complete(result);
                } catch (error) {
                    result.type = 'error';
                    result.message = appendDetail(result.message, 'The saved WHMCS status could not be verified.');
                    complete(result);
                }
            }

            function requestFreshSaveVerification(attempt) {
                if (finished || !saveStarted) {
                    return;
                }
                try {
                    const currentDocument = frame.contentDocument;
                    if (currentDocument !== saveDocument) {
                        return;
                    }
                    if (currentDocument && currentDocument.readyState === 'loading' && attempt < 20) {
                        window.setTimeout(function () { requestFreshSaveVerification(attempt + 1); }, 250);
                        return;
                    }
                    frame.src = domainPageUrl('&mbuRenewVerify=' + encodeURIComponent(String(Date.now())));
                } catch (error) {
                    verifySavedStatus();
                }
            }

            function persistStatusChange(result, afterState) {
                let doc = null;
                try {
                    doc = frame.contentDocument;
                } catch (ignore) {
                }
                const statusControl = findWhmcsStatusControl(doc);
                const form = statusControl ? statusControl.form : null;
                const saveControl = findSaveChangesControl(doc, form);
                if (!statusControl || !form || !saveControl) {
                    result.type = 'error';
                    result.message = appendDetail(result.message, 'The status changed on the Domain page, but its native Save Changes control was unavailable.');
                    complete(result);
                    return;
                }
                saveStarted = true;
                saveDocument = doc;
                saveExpectedState = {label: afterState.label, value: afterState.value};
                pendingSaveResult = result;
                if (observer) {
                    observer.disconnect();
                }
                try {
                    saveControl.click();
                } catch (error) {
                    result.type = 'error';
                    result.message = appendDetail(result.message, 'The native Save Changes action could not be started.');
                    complete(result);
                    return;
                }
                window.setTimeout(function () { requestFreshSaveVerification(0); }, 750);
            }

            function finish(result) {
                if (finished || saveStarted) {
                    return;
                }
                result = result || {type: 'error', message: 'The Sync Domain result was unavailable.'};
                let afterState = {label: '', value: ''};
                try {
                    afterState = commandStarted ? readWhmcsStatusState(frame.contentDocument) : afterState;
                } catch (ignore) {
                }
                if (result.type === 'success' && shouldPersistStatusChange(statusBeforeState, afterState)) {
                    persistStatusChange(result, afterState);
                    return;
                }
                complete(result);
            }

            function inspectAfterCommand() {
                if (finished) {
                    return;
                }
                try {
                    const doc = frame.contentDocument;
                    if (!doc || doc.readyState === 'loading') {
                        return;
                    }
                    const notice = resultNotice(doc, baselineNotices) || alertResult(capturedAlert);
                    if (notice) {
                        finish(notice);
                    }
                } catch (error) {
                    finish({type: 'error', message: 'The Sync Domain result could not be read.'});
                }
            }

            function runCommand(doc) {
                let attempts = 0;
                function locate() {
                    if (finished || commandStarted) {
                        return;
                    }
                    const control = findSyncControl(doc);
                    if (!control && attempts < 10) {
                        attempts += 1;
                        window.setTimeout(locate, 200);
                        return;
                    }
                    if (!control) {
                        finish({type: 'error', message: 'The individual Domain page does not offer a Sync Domain command.'});
                        return;
                    }
                    statusBeforeState = readWhmcsStatusState(doc);
                    if (control.disabled || control.getAttribute('aria-disabled') === 'true') {
                        finish({type: 'error', message: 'The Sync Domain command is disabled for this domain.'});
                        return;
                    }
                    commandStarted = true;
                    actionDocument = doc;
                    baselineNotices = noticeSnapshot(doc);
                    const form = control.form || (control.closest ? control.closest('form') : null);
                    if (form) {
                        form.removeAttribute('target');
                    }
                    control.removeAttribute('target');
                    try {
                        frame.contentWindow.confirm = function () { return true; };
                        frame.contentWindow.alert = function (message) { capturedAlert = String(message || ''); };
                        frame.contentWindow.open = function (url) {
                            if (url) {
                                frame.contentWindow.location.href = url;
                            }
                            return frame.contentWindow;
                        };
                    } catch (ignore) {
                    }
                    if (window.MutationObserver && doc.body) {
                        observer = new MutationObserver(inspectAfterCommand);
                        observer.observe(doc.body, {childList: true, subtree: true, characterData: true});
                    }
                    try {
                        control.click();
                    } catch (error) {
                        finish({type: 'error', message: 'The existing Sync Domain command could not be started.'});
                        return;
                    }
                    window.setTimeout(function () {
                        if (finished) {
                            return;
                        }
                        try {
                            if (frame.contentDocument !== actionDocument) {
                                return;
                            }
                        } catch (ignore) {
                        }
                        inspectAfterCommand();
                    }, 500);
                }
                locate();
            }

            frame.setAttribute('aria-hidden', 'true');
            frame.tabIndex = -1;
            frame.style.display = 'none';
            frame.addEventListener('load', function () {
                if (finished) {
                    return;
                }
                try {
                    const doc = frame.contentDocument;
                    if (!doc) {
                        finish({type: 'error', message: 'The individual Domain page could not be loaded.'});
                        return;
                    }
                    if (!commandStarted) {
                        runCommand(doc);
                        return;
                    }
                    if (saveStarted) {
                        window.setTimeout(verifySavedStatus, 350);
                        return;
                    }
                    window.setTimeout(function () {
                        const notice = resultNotice(frame.contentDocument, baselineNotices) || alertResult(capturedAlert);
                        finish(notice || {type: 'success', message: 'The existing Sync Domain command completed.'});
                    }, 350);
                } catch (error) {
                    finish({type: 'error', message: 'The individual Domain page could not be accessed.'});
                }
            });
            timer = window.setTimeout(function () {
                if (saveStarted) {
                    const saveResult = pendingSaveResult || {type: 'error', message: ''};
                    saveResult.type = 'error';
                    saveResult.message = appendDetail(saveResult.message, 'The WHMCS status save did not finish within 60 seconds.');
                    complete(saveResult);
                    return;
                }
                finish({type: 'error', message: commandStarted
                    ? 'The Sync Domain command did not finish within 60 seconds.'
                    : 'The individual Domain page did not load within 60 seconds.'});
            }, 60000);
            frame.src = domainPageUrl('');
            document.body.appendChild(frame);
        });
    }

    function setSyncStatus(item, result) {
        const badge = document.getElementById('mbu-renew-sync-' + item.id);
        const detail = document.getElementById('mbu-renew-detail-' + item.id);
        if (badge) {
            badge.className = result.type === 'success' ? 'mbu-status-success' : 'mbu-status-failed';
            badge.textContent = result.type === 'success' ? 'Complete' : 'Failed';
        }
        if (detail && result.message) {
            const prior = String(detail.textContent || '').trim();
            detail.textContent = prior + (prior ? ' ' : '') + 'Sync: ' + result.message;
        }
    }

    async function run() {
        let completed = 0;
        let failed = 0;
        const summary = document.getElementById('mbu-renew-sync-summary');
        for (let i = 0; i < items.length; i += 1) {
            const item = items[i];
            const badge = document.getElementById('mbu-renew-sync-' + item.id);
            if (badge) {
                badge.className = 'mbu-status-neutral';
                badge.textContent = 'Syncing…';
            }
            const result = await syncOne(item);
            setSyncStatus(item, result);
            completed += 1;
            if (result.type !== 'success') {
                failed += 1;
            }
            if (summary) {
                summary.className = failed > 0 ? 'mbu-status-failed' : 'mbu-status-neutral';
                summary.textContent = 'Syncing ' + completed + ' of ' + items.length + (failed > 0 ? ' — ' + failed + ' failed' : '');
            }
        }
        if (summary) {
            summary.className = failed > 0 ? 'mbu-status-failed' : 'mbu-status-success';
            summary.textContent = failed > 0
                ? 'Sync complete — ' + failed + ' failed'
                : 'Sync complete';
        }
    }

    if (items.length) {
        run();
    }
}());
</script>
HTML;
    return str_replace('__MBU_RENEW_ITEMS__', $itemsJson, $script);
}

function multibulkupdater_results(string $moduleLink, string $action, array $results): string
{
    if ($action === 'renew_domains') {
        return multibulkupdater_renew_results($moduleLink, $results);
    }

    $successCount = count(array_filter($results, static fn(array $row): bool => $row['success']));
    $failureCount = count($results) - $successCount;

    $title = $action === 'whois' ? 'WHOIS Update Results' : ($action === 'whois_resend' ? 'Verification Resend Results' : ($action === 'move' ? 'Move Domain Results' : ($action === 'copy_dnsplus_zone' ? 'DNSPlus Copy Results' : 'Bulk Update Results')));
    $html = '<div class="mbu-card"><div class="mbu-card-title">' . multibulkupdater_escape($title) . '</div><div class="mbu-card-body">';
    $html .= '<div class="mbu-result-summary"><span class="mbu-success">' . $successCount . ' successful</span>';
    $html .= '<span class="mbu-failure">' . $failureCount . ' failed</span></div>';
    $html .= '<div class="table-responsive"><table class="datatable table table-striped"><thead><tr>';
    $html .= '<th>Domain</th><th>Status</th><th>Result</th></tr></thead><tbody>';

    foreach ($results as $row) {
        $status = $row['success'] ? 'Success' : 'Failed';
        $class = $row['success'] ? 'mbu-status-success' : 'mbu-status-failed';
        $html .= '<tr><td>' . multibulkupdater_escape($row['domain']) . '</td>';
        $html .= '<td><span class="' . $class . '">' . $status . '</span></td>';
        $html .= '<td>' . multibulkupdater_escape($row['message']) . '</td></tr>';
    }

    $html .= '</tbody></table></div>';
    $html .= '<div class="mbu-actions"><a class="btn btn-primary" href="' . multibulkupdater_escape($moduleLink) . '">Run Another Update</a></div>';
    $html .= '</div></div>';

    return $html;
}

function multibulkupdater_alert(string $type, string $message): string
{
    $allowed = ['success', 'info', 'warning', 'danger'];
    if (!in_array($type, $allowed, true)) {
        $type = 'info';
    }

    return '<div class="alert alert-' . $type . '" role="alert">' . $message . '</div>';
}

function multibulkupdater_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function multibulkupdater_submit_script(): string
{
    return <<<'HTML'
<script>
(function () {
    const forms = document.querySelectorAll('.mbu-card');
    forms.forEach(function (form) {
        if (form.tagName !== 'FORM') {
            return;
        }

        form.addEventListener('submit', function (event) {
            if (form.dataset.mbuSubmitting === '1') {
                return;
            }

            const submittedButton = event.submitter;
            const button = submittedButton && submittedButton.classList && submittedButton.classList.contains('mbu-submit-button')
                ? submittedButton
                : form.querySelector('.mbu-submit-button[type="submit"]');
            if (!button || (submittedButton && submittedButton !== button && !submittedButton.classList.contains('mbu-submit-button'))) {
                return;
            }

            form.dataset.mbuSubmitting = '1';
            button.disabled = true;
            button.classList.add('mbu-is-processing');
            button.setAttribute('aria-busy', 'true');

            const label = button.querySelector('.mbu-button-label');
            if (label) {
                label.textContent = button.dataset.processingText || 'Processing…';
            }
        });
    });
}());
</script>
HTML;
}

function multibulkupdater_action_script(): string
{
    return <<<'HTML'
<script>
(function () {
    const action = document.getElementById('mbu-action');
    const renew = document.getElementById('mbu-renew-fields');
    const nameservers = document.getElementById('mbu-nameserver-fields');
    const epp = document.getElementById('mbu-epp-field');
    const copyDnsPlus = document.getElementById('mbu-copy-dnsplus-fields');
    const form = action ? action.closest('form') : null;
    const page = document.getElementById('mbu-main-page');
    const step = document.getElementById('mbu-main-step');
    if (!action || !renew || !nameservers || !epp || !copyDnsPlus || !form || !page || !step) {
        return;
    }
    const presetButtons = Array.prototype.slice.call(document.querySelectorAll('.mbu-ns-preset'));
    const nameserverInputs = [];
    for (let i = 1; i <= 5; i += 1) {
        const input = document.getElementById('mbu-ns' + i);
        if (input) {
            nameserverInputs.push(input);
        }
    }

    function clearPresetSelection() {
        presetButtons.forEach(function (button) {
            button.classList.remove('mbu-preset-active');
            button.setAttribute('aria-pressed', 'false');
        });
    }

    function applyPreset(button) {
        const values = [];
        for (let i = 1; i <= 5; i += 1) {
            values.push(button.dataset['ns' + i] || '');
        }

        values.forEach(function (value, index) {
            if (nameserverInputs[index]) {
                nameserverInputs[index].value = value;
            }
        });

        clearPresetSelection();
        button.classList.add('mbu-preset-active');
        button.setAttribute('aria-pressed', 'true');
    }

    presetButtons.forEach(function (button) {
        button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', function () {
            applyPreset(button);
        });
    });

    nameserverInputs.forEach(function (input) {
        input.addEventListener('input', clearPresetSelection);
    });

    function updateFields() {
        renew.style.display = action.value === 'renew_domains' ? 'block' : 'none';
        nameservers.style.display = action.value === 'nameservers' ? 'block' : 'none';
        epp.style.display = action.value === 'update_epp' ? 'block' : 'none';
        copyDnsPlus.style.display = action.value === 'copy_dnsplus_zone' ? 'block' : 'none';
    }
    action.addEventListener('change', function () {
        if (action.value === 'update_whois') {
            page.value = 'whois';
            step.value = 'start';
            form.submit();
            return;
        }
        if (action.value === 'move_domains') {
            page.value = 'move';
            step.value = 'start';
            form.submit();
            return;
        }
        page.value = 'bulk';
        step.value = 'confirm';
        updateFields();
    });
    updateFields();
}());
</script>
HTML;
}

function multibulkupdater_styles(): string
{
    return <<<'HTML'
<style>
.mbu-wrap{max-width:1150px;margin:0 auto 30px}.mbu-action-switcher{display:flex;align-items:center;gap:12px;margin:0 0 18px}.mbu-action-switcher label{margin:0;color:#24384d;font-weight:600}.mbu-action-switcher select{width:100%;max-width:420px;border:1px solid #b8c2cc;border-radius:6px;background:#fff;padding:9px 11px;color:#1f2933;box-sizing:border-box}.mbu-action-switcher select:focus{border-color:#f58220;outline:0;box-shadow:0 0 0 .14rem rgba(245,130,32,.18)}.mbu-header{display:flex;justify-content:space-between;align-items:center;margin:0 0 18px}.mbu-header h2{margin:0 0 4px;color:#163a5f;font-weight:700}.mbu-header p{margin:0;color:#59636e}.mbu-card{border:1px solid #d8dee5;border-radius:8px;background:#fff;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04)}.mbu-card-title{background:#163a5f;color:#fff;font-size:16px;font-weight:700;padding:12px 16px}.mbu-card-body{padding:18px}.mbu-card label{display:block;color:#24384d;font-weight:600;margin:0 0 6px}.mbu-card textarea,.mbu-card input[type=text],.mbu-card select{display:block;width:100%;border:1px solid #b8c2cc;border-radius:6px;background:#fff;padding:9px 11px;color:#1f2933;box-sizing:border-box}.mbu-card textarea:focus,.mbu-card input[type=text]:focus,.mbu-card select:focus{border-color:#f58220;outline:0;box-shadow:0 0 0 .14rem rgba(245,130,32,.18)}.mbu-card select{max-width:420px;margin-bottom:18px}.mbu-help{font-size:12px;color:#68737e;margin:6px 0 18px}.mbu-fields{margin-top:18px;padding:16px;background:#f7f9fb;border:1px solid #e2e7ec;border-radius:7px}.mbu-fields h4{margin:0 0 14px;color:#163a5f;font-weight:700}.mbu-ns-presets{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin:0 0 4px}.mbu-preset-label{color:#24384d;font-weight:600;margin-right:2px}.mbu-ns-preset{background:#163a5f;border:1px solid #163a5f;color:#fff;padding:6px 12px}.mbu-ns-preset:hover,.mbu-ns-preset:focus{background:#214e7a;border-color:#214e7a;color:#fff}.mbu-ns-preset.mbu-preset-active{background:#d8741f;border-color:#d8741f;color:#fff}.mbu-preset-help{margin-bottom:14px}.mbu-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.mbu-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}.mbu-actions .btn-primary{background:#f58220;border-color:#f58220;color:#fff}.mbu-actions .btn-primary:hover,.mbu-actions .btn-primary:focus{background:#d8741f;border-color:#d8741f}.mbu-submit-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-width:150px}.mbu-button-spinner{display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:mbu-spin .75s linear infinite}.mbu-submit-button.mbu-is-processing .mbu-button-spinner{display:inline-block}.mbu-submit-button:disabled,.mbu-submit-button:disabled:hover,.mbu-submit-button:disabled:focus{background:#d8741f!important;border-color:#d8741f!important;color:#fff!important;opacity:.82;cursor:wait}.mbu-actions .btn-default{background:#163a5f;border-color:#163a5f;color:#fff}.mbu-actions .btn-default:hover,.mbu-actions .btn-default:focus{background:#214e7a;border-color:#214e7a;color:#fff}.mbu-domain-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));column-gap:18px;row-gap:2px;margin-top:12px}.mbu-domain-list div{min-width:0;padding:1px 0;border:0;background:transparent;line-height:1.25;overflow-wrap:anywhere}.mbu-summary{padding:10px 12px;background:#eef5fb;border-left:4px solid #163a5f;color:#163a5f;margin:12px 0}.mbu-hidden{display:none!important}.mbu-result-summary{display:flex;gap:12px;margin:0 0 16px}.mbu-result-summary span{display:inline-block;padding:7px 11px;border-radius:999px;font-weight:700}.mbu-success{background:#e7f5ea;color:#25713a}.mbu-failure{background:#f8e9e8;color:#9a3634}.mbu-status-success,.mbu-status-failed,.mbu-status-neutral{display:inline-block;padding:4px 9px;border-radius:999px;font-size:12px;font-weight:700}.mbu-status-success{background:#e7f5ea;color:#25713a}.mbu-status-failed{background:#f8e9e8;color:#9a3634}.mbu-status-neutral{background:#edf0f3;color:#59636e}.mbu-card table th{background:#163a5f!important;color:#fff!important}.mbu-card table tbody tr:hover{background:#fff8f1!important}.mbu-whois-presets,.mbu-move-presets{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:4px}.mbu-whois-preset,.mbu-move-preset{background:#163a5f;border:1px solid #163a5f;color:#fff}.mbu-whois-preset:hover,.mbu-whois-preset:focus,.mbu-move-preset:hover,.mbu-move-preset:focus{background:#214e7a;border-color:#214e7a;color:#fff}.mbu-whois-preset.mbu-preset-active,.mbu-move-preset.mbu-preset-active{background:#d8741f;border-color:#d8741f;color:#fff}.mbu-copy-options{display:grid;grid-template-columns:1fr 1fr;gap:10px 18px;margin:14px 0}.mbu-copy-options .mbu-check{align-items:flex-start}.mbu-copy-options .mbu-check input{margin-top:3px}.mbu-help-inline{font-size:12px;color:#68737e}.mbu-preset-save-row{display:flex;align-items:flex-end;gap:10px;margin-top:16px}.mbu-preset-save-row>div{flex:1 1 320px}.mbu-preset-save-row .btn-default{background:#163a5f;border-color:#163a5f;color:#fff}.mbu-delete-preset-select{max-width:220px!important;margin:0!important}.mbu-move-delete-preset-group{display:flex;align-items:flex-end;gap:10px}.mbu-danger-button{background:#b94a48;border-color:#b94a48;color:#fff}.mbu-danger-button:hover,.mbu-danger-button:focus{background:#a43f3d;border-color:#a43f3d;color:#fff}.mbu-role-row{display:flex;flex-wrap:wrap;gap:10px 20px}.mbu-check{display:inline-flex!important;align-items:center;gap:6px;margin:0!important;font-weight:600!important}.mbu-check input{margin:0}.mbu-lock-row{display:flex!important;align-items:flex-start;gap:12px;margin:0!important}.mbu-lock-row small{display:block;color:#68737e;font-weight:400;margin-top:3px}.mbu-lock-row-spaced{margin-top:14px!important}.mbu-inline-action{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px}.mbu-inline-action .btn-default{background:#163a5f;border-color:#163a5f;color:#fff}.mbu-inline-action .btn-default:hover,.mbu-inline-action .btn-default:focus{background:#214e7a;border-color:#214e7a;color:#fff}.mbu-inline-action .mbu-help-inline{max-width:560px}.mbu-switch{position:relative;display:inline-flex;flex:0 0 auto;width:42px;height:24px}.mbu-switch input{position:absolute;opacity:0;width:1px;height:1px}.mbu-switch-slider{position:absolute;inset:0;background:#d8e0e8;border-radius:999px;cursor:pointer}.mbu-switch-slider:before{content:"";position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:transform .15s ease}.mbu-switch input:checked+.mbu-switch-slider{background:#f58220}.mbu-switch input:checked+.mbu-switch-slider:before{transform:translateX(18px)}.mbu-preflight-card{margin-top:16px}.mbu-table-note{font-size:11px;color:#68737e;margin-top:3px;max-width:340px}.mbu-whois-grid input[type=email]{display:block;width:100%;border:1px solid #b8c2cc;border-radius:6px;background:#fff;padding:9px 11px;color:#1f2933;box-sizing:border-box}.mbu-whois-grid input[type=email]:focus{border-color:#f58220;outline:0;box-shadow:0 0 0 .14rem rgba(245,130,32,.18)}.mbu-whois-grid select,.mbu-move-grid select{max-width:none;margin-bottom:0}.mbu-move-grid input[type=text]{margin-bottom:0}.mbu-required{color:#b94a48;font-weight:700}.mbu-required-note{font-size:12px;color:#68737e;margin:0 0 10px}@keyframes mbu-spin{to{transform:rotate(360deg)}}@media(max-width:800px){.mbu-copy-options{grid-template-columns:1fr}.mbu-action-switcher{align-items:stretch;flex-direction:column}.mbu-action-switcher select{max-width:none}.mbu-grid,.mbu-domain-list{grid-template-columns:1fr}.mbu-ns-presets{align-items:stretch}.mbu-preset-label{width:100%}.mbu-ns-preset{flex:1 1 120px}.mbu-actions{flex-wrap:wrap}.mbu-actions .btn{width:100%}.mbu-preset-save-row{align-items:stretch;flex-direction:column}.mbu-delete-preset-select{max-width:none!important;width:100%!important}.mbu-role-row{flex-direction:column}.mbu-lock-row{align-items:flex-start}}
</style>
HTML;
}

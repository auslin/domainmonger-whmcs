<?php
/**
 * DomainMonger Patch 1875 (updates Patch 1874)
 * BDM nameserver group selector on WHMCS Admin Domain pages, including dynamic loading and current-group detection.
 *
 * Update-safe implementation:
 * - Does not modify admin/clientsdomains.php or a stock admin template.
 * - Reads the nameserver groups from the Bulk Domain Manager's shared helper.
 * - Replaces the visible "Reset to default nameservers" control only in the
 *   rendered admin page. WHMCS core functionality remains untouched on disk.
 * - Apply only fills WHMCS's native NS1-NS5 fields; the normal Save Changes
 *   button remains responsible for saving/updating the registrar.
 * - The selector reflects the saved group when the current NS1-NS5 values
 *   exactly match one of the BDM nameserver groups.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

function dm1873_is_clientsdomains_page(array $vars): bool
{
    $filename = strtolower((string) ($vars['filename'] ?? ''));
    $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));

    return in_array($filename, ['clientsdomains', 'clientsdomains.php'], true)
        || $script === 'clientsdomains.php';
}

function dm1873_bdm_settings(): array
{
    $settings = [];

    try {
        $rows = Capsule::table('tbladdonmodules')
            ->where('module', 'multibulkupdater')
            ->whereIn('setting', ['ns1', 'ns2', 'ns3', 'ns4', 'ns5'])
            ->get(['setting', 'value']);

        foreach ($rows as $row) {
            $key = (string) ($row->setting ?? '');
            if ($key !== '') {
                $settings[$key] = (string) ($row->value ?? '');
            }
        }
    } catch (Throwable $e) {
        // Built-in BDM groups still work if the optional configured defaults
        // cannot be read for any reason.
    }

    return $settings;
}

function dm1873_bdm_nameserver_groups(): array
{
    $moduleFile = ROOTDIR . '/modules/addons/multibulkupdater/multibulkupdater.php';
    if (!is_file($moduleFile)) {
        return [];
    }

    require_once $moduleFile;
    if (!function_exists('multibulkupdater_nameserver_groups')) {
        return [];
    }

    $groups = multibulkupdater_nameserver_groups(dm1873_bdm_settings());
    if (!is_array($groups)) {
        return [];
    }

    $safe = [];
    foreach ($groups as $key => $group) {
        if (!is_array($group)) {
            continue;
        }

        $label = trim((string) ($group['label'] ?? $key));
        $rawNameservers = (array) ($group['nameservers'] ?? []);
        $nameservers = [];
        for ($i = 0; $i < 5; $i++) {
            $value = strtolower(trim((string) ($rawNameservers[$i] ?? '')));
            $nameservers[] = $value === '' ? '' : rtrim($value, '.');
        }

        if ($label === '' || count(array_filter($nameservers, static fn(string $value): bool => $value !== '')) < 2) {
            continue;
        }

        $safe[(string) $key] = [
            'label' => $label,
            'nameservers' => $nameservers,
        ];
    }

    return $safe;
}

add_hook('AdminAreaFooterOutput', 1873, static function (array $vars): string {
    if (!dm1873_is_clientsdomains_page($vars)) {
        return '';
    }

    $groups = dm1873_bdm_nameserver_groups();
    if (!$groups) {
        return '';
    }

    $groupsJson = json_encode(
        $groups,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    if (!is_string($groupsJson)) {
        return '';
    }

    $html = <<<'HTML'
<style>
#dm1873-ns-group-control {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
#dm1873-ns-group-select {
    width: auto;
    min-width: 210px;
    max-width: 360px;
    background: #fff;
    color: #163a5f;
}
#dm1873-ns-group-apply {
    background: #f58220;
    border-color: #f58220;
    color: #fff;
}
#dm1873-ns-group-apply:hover,
#dm1873-ns-group-apply:focus {
    background: #d8741f;
    border-color: #d8741f;
    color: #fff;
}

#dm1873-ns-group-label-cell {
    vertical-align: top !important;
}
#dm1873-ns-group-label-cell .dm1876-ns-group-label {
    display: block;
    height: 34px;
    line-height: 34px;
}
#dm1873-ns-group-help {
    display: block;
    flex-basis: 100%;
    margin-top: 2px;
    color: #68737e;
    font-size: 12px;
}
</style>
<script>
(function () {
    'use strict';

    const groups = __DM1873_GROUPS__;

    function normalizedText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function findInput(index, form) {
        const selectors = [
            'input[name="ns' + index + '"]',
            'input[name="nameserver' + index + '"]',
            '#ns' + index,
            '#nameserver' + index
        ];
        for (let i = 0; i < selectors.length; i += 1) {
            const input = form.querySelector(selectors[i]);
            if (input) {
                return input;
            }
        }
        return null;
    }

    function normalizedNameserver(value) {
        return String(value || '').trim().toLowerCase().replace(/\.+$/, '');
    }

    function currentNameservers(form) {
        const values = [];
        for (let i = 1; i <= 5; i += 1) {
            const input = findInput(i, form);
            values.push(input ? normalizedNameserver(input.value) : '');
        }
        return values;
    }

    function groupNameservers(group) {
        const raw = group && Array.isArray(group.nameservers) ? group.nameservers : [];
        const values = [];
        for (let i = 0; i < 5; i += 1) {
            values.push(normalizedNameserver(raw[i] || ''));
        }
        return values;
    }

    function sameNameservers(left, right) {
        for (let i = 0; i < 5; i += 1) {
            if ((left[i] || '') !== (right[i] || '')) {
                return false;
            }
        }
        return true;
    }

    function syncGroupSelection(form) {
        const select = document.getElementById('dm1873-ns-group-select');
        if (!select || !form || select.closest('form') !== form) {
            return;
        }

        const current = currentNameservers(form);
        let matchedKey = '';

        Object.keys(groups).some(function (key) {
            if (sameNameservers(current, groupNameservers(groups[key]))) {
                matchedKey = key;
                return true;
            }
            return false;
        });

        if (select.value !== matchedKey) {
            select.value = matchedKey;
        }
    }

    function bindNameserverChangeDetection(form) {
        for (let i = 1; i <= 5; i += 1) {
            const input = findInput(i, form);
            if (!input || input.dataset.dm1875GroupDetectionBound === '1') {
                continue;
            }
            input.dataset.dm1875GroupDetectionBound = '1';
            input.addEventListener('input', function () {
                syncGroupSelection(form);
            });
            input.addEventListener('change', function () {
                syncGroupSelection(form);
            });
        }
    }

    function findResetContainer(form) {
        const candidates = Array.prototype.slice.call(
            form.querySelectorAll('label, span, td, th, div, p')
        ).filter(function (element) {
            return normalizedText(element.textContent).indexOf('reset to default nameservers') !== -1;
        });

        candidates.sort(function (a, b) {
            return normalizedText(a.textContent).length - normalizedText(b.textContent).length;
        });

        for (let i = 0; i < candidates.length; i += 1) {
            const candidate = candidates[i];
            const row = candidate.closest('tr, .form-group, .form-check, .control-group, .row');
            if (row && row !== form) {
                return row;
            }
        }
        return null;
    }

    function buildControl() {
        const wrap = document.createElement('div');
        wrap.id = 'dm1873-ns-group-control';

        const select = document.createElement('select');
        select.id = 'dm1873-ns-group-select';
        select.className = 'form-control';
        select.setAttribute('aria-label', 'Nameserver Group');

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select nameserver group…';
        select.appendChild(placeholder);

        Object.keys(groups).forEach(function (key) {
            const group = groups[key];
            const option = document.createElement('option');
            option.value = key;
            option.textContent = group.label || key;
            select.appendChild(option);
        });

        const button = document.createElement('button');
        button.type = 'button';
        button.id = 'dm1873-ns-group-apply';
        button.className = 'btn btn-primary';
        button.textContent = 'Apply';

        const help = document.createElement('span');
        help.id = 'dm1873-ns-group-help';
        help.textContent = 'Apply fills the existing nameserver fields. Click Save Changes to update the domain.';

        wrap.appendChild(select);
        wrap.appendChild(button);
        wrap.appendChild(help);
        return wrap;
    }

    var installTimer = null;

    function install() {
        const firstInput = document.querySelector('input[name="ns1"], input[name="nameserver1"], #ns1, #nameserver1');
        if (!firstInput) {
            return;
        }

        const form = firstInput.closest('form');
        if (!form) {
            return;
        }

        const existingControl = document.getElementById('dm1873-ns-group-control');
        if (existingControl) {
            if (existingControl.isConnected && existingControl.closest('form') === form) {
                bindNameserverChangeDetection(form);
                syncGroupSelection(form);
                return;
            }
            if (existingControl.parentNode) {
                existingControl.parentNode.removeChild(existingControl);
            }
        }

        const inputs = [];
        for (let i = 1; i <= 5; i += 1) {
            inputs.push(findInput(i, form));
        }
        if (!inputs[0] || !inputs[1]) {
            return;
        }

        const control = buildControl();
        const resetContainer = findResetContainer(form);

        if (resetContainer && resetContainer.tagName === 'TR' && resetContainer.cells && resetContainer.cells.length >= 2) {
            resetContainer.cells[0].textContent = '';
            resetContainer.cells[0].id = 'dm1873-ns-group-label-cell';
            const alignedLabel = document.createElement('span');
            alignedLabel.className = 'dm1876-ns-group-label';
            alignedLabel.textContent = 'Nameserver Group';
            resetContainer.cells[0].appendChild(alignedLabel);
            while (resetContainer.cells[1].firstChild) {
                resetContainer.cells[1].removeChild(resetContainer.cells[1].firstChild);
            }
            resetContainer.cells[1].appendChild(control);
            for (let cellIndex = 2; cellIndex < resetContainer.cells.length; cellIndex += 1) {
                resetContainer.cells[cellIndex].style.display = 'none';
            }
        } else if (resetContainer) {
            while (resetContainer.firstChild) {
                resetContainer.removeChild(resetContainer.firstChild);
            }

            const label = document.createElement('label');
            label.setAttribute('for', 'dm1873-ns-group-select');
            label.textContent = 'Nameserver Group';
            label.style.display = 'block';
            label.style.marginBottom = '6px';

            resetContainer.appendChild(label);
            resetContainer.appendChild(control);
        } else {
            const anchor = firstInput.closest('tr, .form-group, .control-group, .row') || firstInput.parentElement;
            if (!anchor || !anchor.parentNode) {
                return;
            }

            const fallback = document.createElement('div');
            fallback.className = 'form-group';
            const label = document.createElement('label');
            label.setAttribute('for', 'dm1873-ns-group-select');
            label.textContent = 'Nameserver Group';
            label.style.display = 'block';
            label.style.marginBottom = '6px';
            fallback.appendChild(label);
            fallback.appendChild(control);
            anchor.parentNode.insertBefore(fallback, anchor);
        }

        const select = document.getElementById('dm1873-ns-group-select');
        const apply = document.getElementById('dm1873-ns-group-apply');
        if (!select || !apply) {
            return;
        }

        bindNameserverChangeDetection(form);
        syncGroupSelection(form);

        apply.addEventListener('click', function () {
            const key = select.value;
            const group = groups[key];
            if (!group || !Array.isArray(group.nameservers)) {
                return;
            }

            for (let i = 0; i < 5; i += 1) {
                const input = inputs[i];
                if (!input) {
                    continue;
                }
                input.value = group.nameservers[i] || '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            syncGroupSelection(form);
        });
    }

    function scheduleInstall() {
        if (installTimer !== null) {
            window.clearTimeout(installTimer);
        }
        installTimer = window.setTimeout(function () {
            installTimer = null;
            install();
        }, 40);
    }

    function startWatching() {
        install();

        if (window.MutationObserver && document.body) {
            const observer = new MutationObserver(function (mutations) {
                for (let i = 0; i < mutations.length; i += 1) {
                    if (mutations[i].type === 'childList' && (mutations[i].addedNodes.length || mutations[i].removedNodes.length)) {
                        scheduleInstall();
                        break;
                    }
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

        // WHMCS can also redraw the selected domain after DataTables/AJAX events.
        // These delayed checks are harmless when no nameserver form is present.
        window.setTimeout(install, 150);
        window.setTimeout(install, 600);
        window.setTimeout(install, 1500);

        if (window.jQuery) {
            window.jQuery(document).on('ajaxComplete.dm1873 shown.bs.tab.dm1873', scheduleInstall);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startWatching);
    } else {
        startWatching();
    }
}());
</script>
HTML;

    return str_replace('__DM1873_GROUPS__', $groupsJson, $html);
});

<?php
/**
 * DomainMonger WHOIS contact 60-day transfer lock opt-out toggle.
 *
 * Patch 796:
 * Adds a visible client-facing toggle to the standard WHMCS Contact
 * Information page. When enabled, it syncs with WHMCS's existing IRTP
 * opt-out fields and also submits the LogicBoxes/NetEarthOne opt-out
 * parameter:
 *
 * sixty-day-lock-optout=true
 *
 * Scope:
 * manage/clientarea.php?action=domaincontacts&domainid=...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_lock_optout_is_page')) {
    function dm_domaincontacts_lock_optout_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'clientarea.php' || strpos($uri, '/manage/clientarea.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

if (!function_exists('dm_domaincontacts_lock_optout_selected')) {
    function dm_domaincontacts_lock_optout_selected(): bool
    {
        $value = $_REQUEST['dm_sixty_day_lock_optout'] ?? $_REQUEST['sixty-day-lock-optout'] ?? $_REQUEST['irtpOptOut'] ?? '';

        if (is_array($value)) {
            $value = reset($value);
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}

/*
 * If the visible toggle is submitted, seed several names before WHMCS/module
 * processing has a chance to read request values.
 */
if (dm_domaincontacts_lock_optout_is_page() && strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) === 'POST' && dm_domaincontacts_lock_optout_selected()) {
    $fields = [
        'dm_sixty_day_lock_optout' => '1',
        'irtpOptOut' => '1',
        'irtpOptOutReason' => 'Client opted out of the 60-day transfer lock before saving Registrant contact changes.',
        'sixty-day-lock-optout' => 'true',
        'sixty_day_lock_optout' => 'true',
        'sixtyDayLockOptOut' => 'true',
        'lockOptOut' => 'true',
        'transferLockOptOut' => 'true',
    ];

    foreach ($fields as $key => $value) {
        $_POST[$key] = $value;
        $_REQUEST[$key] = $value;
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_domaincontacts_lock_optout_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-domaincontacts-lock-optout-style-v796">
.dm-domaincontacts-lock-optout {
    background: #fff8f3;
    border: 1px solid #f1c29c;
    border-radius: 8px;
    color: #163a5f;
    /* Patch 1204: restore the visual gap between the contact form and the
     * 60-day transfer-lock option that was lost during the emergency restore. */
    margin: 18px auto 16px auto;
    max-width: 780px;
    padding: 14px 16px;
    text-align: left;
}

.dm-domaincontacts-lock-optout-title {
    align-items: center;
    color: #163a5f;
    display: flex;
    font-size: 14px;
    font-weight: 700;
    gap: 10px;
    line-height: 1.3;
    margin: 0 0 6px 0;
}

.dm-domaincontacts-lock-optout-desc {
    color: #163a5f;
    font-size: 13px;
    line-height: 1.45;
    margin: 0 0 0 48px;
}

.dm-domaincontacts-lock-switch {
    display: inline-flex;
    flex: 0 0 auto;
    height: 24px;
    position: relative;
    width: 42px;
}

.dm-domaincontacts-lock-switch input {
    height: 1px;
    opacity: 0;
    position: absolute;
    width: 1px;
}

.dm-domaincontacts-lock-slider {
    background: #d8e0e8;
    border-radius: 999px;
    cursor: pointer;
    inset: 0;
    position: absolute;
    transition: background-color .15s ease;
}

.dm-domaincontacts-lock-slider:before {
    background: #ffffff;
    border-radius: 50%;
    bottom: 3px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
    content: "";
    height: 18px;
    left: 3px;
    position: absolute;
    transition: transform .15s ease;
    width: 18px;
}

.dm-domaincontacts-lock-switch input:checked + .dm-domaincontacts-lock-slider {
    background: #f58220;
}

.dm-domaincontacts-lock-switch input:checked + .dm-domaincontacts-lock-slider:before {
    transform: translateX(18px);
}

.dm-domaincontacts-lock-switch input:focus + .dm-domaincontacts-lock-slider {
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .25);
}

@media (max-width: 575px) {
    .dm-domaincontacts-lock-optout-desc {
        margin-left: 0;
    }

    .dm-domaincontacts-lock-optout-title {
        align-items: flex-start;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_domaincontacts_lock_optout_is_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-domaincontacts-lock-optout-js-v796">
(function () {
    'use strict';

    var optOutReason = 'Client opted out of the 60-day transfer lock before saving Registrant contact changes.';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function ensureHidden(form, name, value) {
        var input;

        if (!form) {
            return null;
        }

        input = form.querySelector('input[name="' + name.replace(/"/g, '\\"') + '"]');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            form.appendChild(input);
        }

        input.value = value;

        return input;
    }

    function syncExistingWhmcsFields(checked) {
        var whmcsOptOut = document.getElementById('irtpOptOut');
        var whmcsReason = document.getElementById('irtpOptOutReason');
        var modalOptOut = document.getElementById('modalIrtpOptOut');
        var modalReason = document.getElementById('modalReason');

        if (whmcsOptOut) {
            whmcsOptOut.value = checked ? '1' : '0';
        }

        if (whmcsReason) {
            whmcsReason.value = checked ? optOutReason : '';
        }

        if (modalOptOut) {
            modalOptOut.checked = checked;
        }

        if (modalReason && checked && !modalReason.value) {
            modalReason.value = optOutReason;
        }
    }

    function submitValues(form, checked) {
        if (!form) {
            return;
        }

        ensureHidden(form, 'dm_sixty_day_lock_optout', checked ? '1' : '0');

        if (checked) {
            ensureHidden(form, 'irtpOptOut', '1');
            ensureHidden(form, 'irtpOptOutReason', optOutReason);
            ensureHidden(form, 'sixty-day-lock-optout', 'true');
            ensureHidden(form, 'sixty_day_lock_optout', 'true');
            ensureHidden(form, 'sixtyDayLockOptOut', 'true');
            ensureHidden(form, 'lockOptOut', 'true');
            ensureHidden(form, 'transferLockOptOut', 'true');
        }
    }

    function findButtonRow(form) {
        var rows;
        var i;
        var row;

        rows = form.querySelectorAll('p.text-center, .text-center, .form-actions, .modal-footer');

        for (i = 0; i < rows.length; i += 1) {
            row = rows[i];

            if (row.querySelector('input[type="submit"], button[type="submit"]')) {
                return row;
            }
        }

        return null;
    }

    function addToggle() {
        var form = document.getElementById('frmDomainContactModification');
        var buttonRow;
        var wrap;
        var checkbox;

        if (!form || form.querySelector('#dmSixtyDayLockOptOut')) {
            return;
        }

        buttonRow = findButtonRow(form);
        if (!buttonRow) {
            return;
        }

        wrap = document.createElement('div');
        wrap.className = 'dm-domaincontacts-lock-optout';
        wrap.innerHTML =
            '<label class="dm-domaincontacts-lock-optout-title" for="dmSixtyDayLockOptOut">' +
                '<span class="dm-domaincontacts-lock-switch">' +
                    '<input id="dmSixtyDayLockOptOut" name="dm_sixty_day_lock_optout" type="checkbox" value="1">' +
                    '<span class="dm-domaincontacts-lock-slider" aria-hidden="true"></span>' +
                '</span>' +
                '<span>Opt out of the 60-day transfer lock</span>' +
            '</label>' +
            '<p class="dm-domaincontacts-lock-optout-desc">' +
                'Use this when changing the Registrant first name, last name, or email address and you do not want the domain placed in a 60-day transfer lock. This only applies when the registry/registrar allows the opt-out.' +
            '</p>';

        buttonRow.parentNode.insertBefore(wrap, buttonRow);

        checkbox = document.getElementById('dmSixtyDayLockOptOut');

        checkbox.addEventListener('change', function () {
            syncExistingWhmcsFields(checkbox.checked);
            submitValues(form, checkbox.checked);
        });

        form.addEventListener('submit', function () {
            syncExistingWhmcsFields(checkbox.checked);
            submitValues(form, checkbox.checked);
        }, true);

        /*
         * If WHMCS opens its IRTP confirmation modal, keep the modal checkbox
         * aligned with the page-level toggle.
         */
        document.addEventListener('click', function () {
            window.setTimeout(function () {
                if (checkbox.checked) {
                    syncExistingWhmcsFields(true);
                }
            }, 50);
        });

        syncExistingWhmcsFields(false);
        submitValues(form, false);
    }

    ready(addToggle);
    window.setTimeout(addToggle, 150);
    window.setTimeout(addToggle, 500);
    window.setTimeout(addToggle, 1000);
}());
</script>
HTML;
});

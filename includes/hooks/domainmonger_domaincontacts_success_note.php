<?php
/**
 * DomainMonger domain contact successful-save verification note.
 *
 * Patch 794:
 * Adds the approved verification note below "Changes Saved Successfully!" on
 * the standard WHMCS Contact Information page.
 *
 * Scope:
 * manage/clientarea.php?action=domaincontacts&domainid=...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_success_note_is_page')) {
    function dm_domaincontacts_success_note_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'clientarea.php' || strpos($uri, '/manage/clientarea.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_domaincontacts_success_note_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-domaincontacts-success-note-style-v794">
.dm-domaincontacts-verification-note {
    border-top: 1px solid rgba(22, 58, 95, 0.16);
    color: #163a5f;
    font-size: 13px;
    line-height: 1.45;
    margin-top: 10px;
    padding-top: 9px;
}

.dm-domaincontacts-verification-note strong {
    color: #163a5f;
    display: block;
    font-weight: 700;
    margin-bottom: 5px;
}

.dm-domaincontacts-verification-note p {
    margin: 0 0 7px 0;
}

.dm-domaincontacts-verification-note p:last-child {
    margin-bottom: 0;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_domaincontacts_success_note_is_page()) {
        return '';
    }

    $para1 = htmlspecialchars(
        'If the Registrant first name, last name, or email address was changed, the update may not appear until verification is completed. Please check the verification emails and click all required links.',
        ENT_QUOTES,
        'UTF-8'
    );

    $para2 = htmlspecialchars(
        'If the email address was changed, check both the current and new email addresses. If the email address was not changed, both verification emails will be sent to the same address, and both links must be clicked.',
        ENT_QUOTES,
        'UTF-8'
    );

    return <<<HTML
<script id="dm-domaincontacts-success-note-js-v794">
(function () {
    'use strict';

    var para1 = '{$para1}';
    var para2 = '{$para2}';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalize(text) {
        return (text || '').replace(/\\s+/g, ' ').trim();
    }

    function isSuccessAlert(el) {
        var cls;
        var text;

        if (!el) {
            return false;
        }

        cls = (el.className || '').toString();
        text = normalize(el.textContent);

        return /alert-success|success/i.test(cls)
            && /Changes Saved Successfully!/i.test(text);
    }

    function addNote(alertEl) {
        var note;

        if (!alertEl || alertEl.getAttribute('data-dm-domaincontacts-success-note') === '1') {
            return false;
        }

        note = document.createElement('div');
        note.className = 'dm-domaincontacts-verification-note';
        note.innerHTML = '<strong>Verification May Be Required</strong>'
            + '<p>' + para1 + '</p>'
            + '<p>' + para2 + '</p>';

        alertEl.appendChild(note);
        alertEl.setAttribute('data-dm-domaincontacts-success-note', '1');

        return true;
    }

    function applyNote(root) {
        var alerts;
        var i;
        var changed = false;

        root = root || document;

        alerts = root.querySelectorAll ? root.querySelectorAll('.alert-success, .alert.alert-success, .successbox, .success, .alert') : [];

        for (i = 0; i < alerts.length; i += 1) {
            if (isSuccessAlert(alerts[i]) && addNote(alerts[i])) {
                changed = true;
            }
        }

        return changed;
    }

    function startObserver() {
        if (!window.MutationObserver || !document.body) {
            return;
        }

        new MutationObserver(function (mutations) {
            var i;
            var j;

            for (i = 0; i < mutations.length; i += 1) {
                for (j = 0; j < mutations[i].addedNodes.length; j += 1) {
                    if (mutations[i].addedNodes[j].nodeType === 1) {
                        applyNote(mutations[i].addedNodes[j]);
                    }
                }
            }

            applyNote(document);
        }).observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    ready(function () {
        applyNote(document);
        startObserver();
    });

    window.setTimeout(function () { applyNote(document); }, 150);
    window.setTimeout(function () { applyNote(document); }, 500);
    window.setTimeout(function () { applyNote(document); }, 1000);
}());
</script>
HTML;
});

<?php
/**
 * DomainMonger WHMCS v9 styling cleanup: Dashboard Register Domains spacing.
 *
 * Scope:
 * - WHMCS dashboard/client area home only.
 *
 * Purpose:
 * - Add breathing room between the white domain input and orange Register button
 *   in the Dashboard "Register Domains" widget.
 * - Keep this page-specific and avoid touching the register-page namespinner/cart.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_is_clientarea_dashboard_page')) {
    function domainmonger_is_clientarea_dashboard_page(array $vars = []): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($requestUri, 'dmv9support=1') !== false) {
            return false;
        }

        if (stripos($requestUri, '/cart.php') !== false) {
            return false;
        }

        if (isset($vars['templatefile']) && stripos((string) $vars['templatefile'], 'clientareahome') !== false) {
            return true;
        }

        if (isset($vars['filename']) && stripos((string) $vars['filename'], 'clientarea') !== false) {
            $action = $_REQUEST['action'] ?? null;
            if ($action === null || (string) $action === '') {
                return true;
            }
        }

        if (stripos($requestUri, '/manage/clientarea.php') !== false && stripos($requestUri, 'action=') === false) {
            return true;
        }

        return false;
    }
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!domainmonger_is_clientarea_dashboard_page(is_array($vars) ? $vars : [])) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-dashboard-register-domain-spacing">
/* Dashboard only: spacing between domain search input and Register button. */
body .dm-dashboard-register-domain-widget .input-group,
body .dm-dashboard-register-domain-widget .input-group.input-group-sm,
body .dm-dashboard-register-domain-widget form .input-group {
    display: flex !important;
    align-items: stretch !important;
    width: 100% !important;
    gap: 8px !important;
}

body .dm-dashboard-register-domain-widget .input-group > input.form-control,
body .dm-dashboard-register-domain-widget .input-group > .form-control {
    flex: 1 1 auto !important;
    width: auto !important;
    min-width: 0 !important;
    border-radius: 4px !important;
}

body .dm-dashboard-register-domain-widget .input-group > .input-group-btn,
body .dm-dashboard-register-domain-widget .input-group > span.input-group-btn,
body .dm-dashboard-register-domain-widget .input-group > div.input-group-btn {
    display: flex !important;
    align-items: stretch !important;
    width: auto !important;
    white-space: nowrap !important;
}

body .dm-dashboard-register-domain-widget .input-group > .input-group-btn:first-of-type,
body .dm-dashboard-register-domain-widget .dm-dashboard-register-domain-button-wrap {
    margin-left: 8px !important;
}

body .dm-dashboard-register-domain-widget .input-group > .input-group-btn + .input-group-btn {
    margin-left: 0 !important;
}

body .dm-dashboard-register-domain-widget .dm-dashboard-register-domain-button,
body .dm-dashboard-register-domain-widget .dm-dashboard-transfer-domain-button,
body .dm-dashboard-register-domain-widget .input-group .btn {
    border-radius: 4px !important;
    line-height: 1.35 !important;
    box-shadow: none !important;
}

body .dm-dashboard-register-domain-widget .dm-dashboard-register-domain-button {
    background: #f58220 !important;
    background-color: #f58220 !important;
    border-color: #f58220 !important;
    color: #ffffff !important;
}

body .dm-dashboard-register-domain-widget .dm-dashboard-register-domain-button:hover,
body .dm-dashboard-register-domain-widget .dm-dashboard-register-domain-button:focus,
body .dm-dashboard-register-domain-widget .dm-dashboard-register-domain-button:active {
    background: #214e7a !important;
    background-color: #214e7a !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
}
</style>
<script>
(function () {
    function normalizeText(value) {
        return ((value || '') + '').replace(/\s+/g, ' ').trim();
    }

    function closestPanel(node) {
        while (node && node !== document.body) {
            if (node.classList && (
                node.classList.contains('panel') ||
                node.classList.contains('card') ||
                node.classList.contains('tile') ||
                node.classList.contains('domain-register')
            )) {
                return node;
            }
            node = node.parentElement;
        }
        return null;
    }

    function isDashboardRegisterDomainWidget(panel, button) {
        if (!panel) {
            return false;
        }

        var panelText = normalizeText(panel.textContent);

        if (
            panelText.indexOf('Register Domains') !== -1 ||
            panelText.indexOf('Register a New Domain') !== -1 ||
            panelText.indexOf('Register New Domain') !== -1
        ) {
            return true;
        }

        /* Fallback for language/title changes: dashboard widget with Register + Transfer controls. */
        var hasRegister = false;
        var hasTransfer = false;
        panel.querySelectorAll('button, input[type="submit"], a.btn').forEach(function (innerButton) {
            var innerLabel = normalizeText(innerButton.value || innerButton.textContent || '');
            if (innerLabel === 'Register') {
                hasRegister = true;
            }
            if (innerLabel === 'Transfer') {
                hasTransfer = true;
            }
        });

        return !!button && hasRegister && hasTransfer;
    }

    function applyDomainMongerDashboardDomainSpacing() {
        document.querySelectorAll('button, input[type="submit"], a.btn').forEach(function (button) {
            var label = normalizeText(button.value || button.textContent || '');
            if (label !== 'Register') {
                return;
            }

            var panel = closestPanel(button);
            if (!isDashboardRegisterDomainWidget(panel, button)) {
                return;
            }

            panel.classList.add('dm-dashboard-register-domain-widget');
            button.classList.add('dm-dashboard-register-domain-button');

            if (button.parentElement) {
                button.parentElement.classList.add('dm-dashboard-register-domain-button-wrap');
            }

            panel.querySelectorAll('button, input[type="submit"], a.btn').forEach(function (innerButton) {
                var innerLabel = normalizeText(innerButton.value || innerButton.textContent || '');
                if (innerLabel === 'Transfer') {
                    innerButton.classList.add('dm-dashboard-transfer-domain-button');
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyDomainMongerDashboardDomainSpacing);
    } else {
        applyDomainMongerDashboardDomainSpacing();
    }

    window.setTimeout(applyDomainMongerDashboardDomainSpacing, 250);
})();
</script>
HTML;
});

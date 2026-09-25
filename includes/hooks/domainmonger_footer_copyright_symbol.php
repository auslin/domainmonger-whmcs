<?php
/**
 * DomainMonger WHMCS footer copyright symbol cleanup.
 *
 * The shared Stellar integration footer contains a legacy encoded copyright
 * character that can render as a question mark on WHMCS pages. Do not edit
 * the protected integration footer directly; normalize the rendered footer
 * text client-side instead.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 90, function (array $vars) {
    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (isset($_GET['dmv9support']) && (string) $_GET['dmv9support'] === '1') {
        return '';
    }

    return <<<'HTML'
<script>
(function () {
    function dmFixFooterCopyrightSymbol() {
        var footerText = document.querySelector('.copyrightmain-text');
        if (!footerText) {
            return;
        }

        var html = footerText.innerHTML;
        if (html.indexOf('Domainmonger') === -1 || html.indexOf('2026') === -1) {
            return;
        }

        footerText.innerHTML = html.replace(
            /(<strong>\s*Domainmonger\s*<\/strong>)\s*(?:\?|�|Â©|©|&copy;)\s*(2026\s*-\s*All rights reserved)/i,
            '$1 &copy; $2'
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', dmFixFooterCopyrightSymbol);
    } else {
        dmFixFooterCopyrightSymbol();
    }
})();
</script>
HTML;
});

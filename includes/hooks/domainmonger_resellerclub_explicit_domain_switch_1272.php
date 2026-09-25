<?php
/**
 * DomainMonger Patch 1272
 *
 * Makes the converted ResellerClub domain switcher match the ClouDNS action:
 * choose a domain first, then click an explicit orange Switch button.
 *
 * This deliberately does not replace the unified menu hook. It works with the
 * current rendered selector and its existing per-page destination URLs, which
 * preserves all confirmed converted routes and page-specific navigation.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 9999, static function ($vars) {
    return <<<'HTML'
<style id="dm-rc-explicit-domain-switch-1272-css">
#dm-rc-unified-nav-1152 .dm-rc-switch-wrap {
    flex: 0 1 455px !important;
}

#dm-rc-unified-nav-1152 .dm-rc-switch-wrap > label {
    display: none !important;
}

#dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272 {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 0 0 auto !important;
    min-width: 72px !important;
    height: 38px !important;
    min-height: 38px !important;
    margin: 0 !important;
    padding: 7px 15px !important;
    border: 1px solid #f58220 !important;
    border-radius: 4px !important;
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    box-shadow: none !important;
    color: #ffffff !important;
    font: inherit !important;
    font-size: 13px !important;
    line-height: 1.2 !important;
    font-weight: 800 !important;
    text-decoration: none !important;
    cursor: pointer !important;
}

#dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272:hover,
#dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272:focus {
    border-color: #d8741f !important;
    background: #d8741f !important;
    background-color: #d8741f !important;
    color: #ffffff !important;
    outline: 0 !important;
    box-shadow: none !important;
}

#dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272:disabled {
    opacity: .65 !important;
    cursor: default !important;
}

@media (max-width: 480px) {
    #dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272 {
        width: 100% !important;
    }
}
</style>
<script id="dm-rc-explicit-domain-switch-1272-js">
(function () {
    'use strict';

    function enhanceDomainSwitcher() {
        var nav = document.getElementById('dm-rc-unified-nav-1152');
        if (!nav) {
            return;
        }

        var wrap = nav.querySelector('.dm-rc-switch-wrap');
        var select = document.getElementById('dm-rc-domain-switch-1152');
        if (!wrap || !select) {
            return;
        }

        var label = wrap.querySelector('label');
        if (label && label.parentNode) {
            label.parentNode.removeChild(label);
        }

        select.setAttribute('aria-label', 'Choose domain');

        if (select.getAttribute('data-dm-explicit-switch-1272') !== '1') {
            select.setAttribute('data-dm-explicit-switch-1272', '1');

            // The original unified menu listens for change and navigates at once.
            // A capture listener runs first and keeps the new selection in place
            // until the user confirms it with the Switch button.
            select.addEventListener('change', function (event) {
                event.stopImmediatePropagation();
            }, true);
        }

        var button = document.getElementById('dm-rc-domain-switch-button-1272');
        if (!button) {
            button = document.createElement('button');
            button.type = 'button';
            button.id = 'dm-rc-domain-switch-button-1272';
            button.className = 'dm-rc-domain-switch-button-1272';
            button.textContent = 'Switch';
            button.setAttribute('aria-label', 'Switch to selected domain');
            wrap.appendChild(button);

            button.addEventListener('click', function () {
                var destination = String(select.value || '');
                if (destination && !select.disabled) {
                    window.location.href = destination;
                }
            });
        }

        button.disabled = !!select.disabled || select.options.length < 2;
    }

    function start() {
        enhanceDomainSwitcher();

        var attempts = 0;
        var timer = window.setInterval(function () {
            attempts += 1;
            enhanceDomainSwitcher();
            if (attempts >= 30) {
                window.clearInterval(timer);
            }
        }, 200);

        if (window.MutationObserver) {
            var observer = new MutationObserver(enhanceDomainSwitcher);
            observer.observe(document.documentElement, { childList: true, subtree: true });
            window.setTimeout(function () {
                observer.disconnect();
            }, 8000);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
</script>
HTML;
});

<?php
/**
 * DomainMonger photographic subbanner first-paint guard — Patch 1326.
 *
 * The Stellar theme initially paints the shared .subbanner with its dark
 * fallback plus a black overlay while the final DomainMonger style sheet and
 * background image are still loading. This supplies the already-approved navy
 * presentation in the document head before the banner can first paint.
 *
 * Presentation only. It does not move the banner, change its text, modify the
 * integration folder, alter color-mode storage, or touch page functionality.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, static function ($vars) {
    return <<<'HTML'
<link rel="preload" as="image" href="/wp-content/themes/stellar/libs/images/background.jpg">
<style id="domainmonger-subbanner-first-paint-1326">
/*
 * Match the confirmed final banner treatment during the very first paint.
 * The image and normal theme rules remain responsible for the finished banner.
 */
html {
    --background-banner: #163a5f;
}

body.wordpressbody.whmcsbody .subbanner,
.wordpressbody .subbanner {
    background-color: #163a5f !important;
}

body.wordpressbody.whmcsbody .subbanner > .background,
.wordpressbody .subbanner > .background {
    background: rgba(22, 58, 95, 0.88) !important;
}
</style>
HTML;
});

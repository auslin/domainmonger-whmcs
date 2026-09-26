{* DM Patch 1460: early canonical-host fallback restored from confirmed Restore 557. *}
<script id="domainmonger-whmcs-canonical-host-1460">
(function () {
    'use strict';

    if (window.location.hostname === 'www.domainmonger.com') {
        window.location.replace(
            'https://domainmonger.com'
            + window.location.pathname
            + window.location.search
            + window.location.hash
        );
    }
}());
</script>

<!-- Styling -->
{* {\WHMCS\View\Asset::fontCssInclude('open-sans-family.css')} *}
<link href="{assetPath file='all.min.css'}?v={$versionHash}" rel="stylesheet">
<link href="{assetPath file='theme.min.css'}?v={$versionHash}" rel="stylesheet">
{* <link href="{$WEB_ROOT}/assets/css/fontawesome-all.min.css" rel="stylesheet"> *}
{assetExists file="custom.css"}
<link href="{$__assetPath__}" rel="stylesheet">
{/assetExists}
<link href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-controls-v171.css?v=197" rel="stylesheet">

<script>
    var csrfToken = '{$token}',
        markdownGuide = '{lang|addslashes key="markdown.title"}',
        locale = '{if !empty($mdeLocale)}{$mdeLocale}{else}en{/if}',
        saved = '{lang|addslashes key="markdown.saved"}',
        saving = '{lang|addslashes key="markdown.saving"}',
        whmcsBaseUrl = "{\WHMCS\Utility\Environment\WebHelper::getBaseUrl()}";
    {if $captcha}{$captcha->getPageJs()}{/if}
</script>
<script src="{assetPath file='scripts.min.js'}?v={$versionHash}"></script>

{if $templatefile == "viewticket" && !$loggedin}
  <meta name="robots" content="noindex" />
{/if}

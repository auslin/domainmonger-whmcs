<?php
/**
 * DomainMonger Knowledgebase search spacing.
 *
 * Patch 535
 * - Adds a small gap between the Knowledgebase search input and Search button.
 * - Scoped to Knowledgebase pages only.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeaderOutput', 535, function ($vars) {
    $templateFile = isset($vars['templatefile']) ? strtolower((string) $vars['templatefile']) : '';
    $filename = isset($vars['filename']) ? strtolower((string) $vars['filename']) : '';

    if ($templateFile !== 'knowledgebase' && $filename !== 'knowledgebase') {
        return '';
    }

    return <<<HTML
<style id="domainmonger-knowledgebase-search-spacing-535">
body.whmcsbody .dm-kb-search.input-group,
body.whmcsbody .kb-search.input-group {
    align-items: stretch !important;
}

body.whmcsbody .dm-kb-search .input-group-append,
body.whmcsbody .kb-search .input-group-append {
    margin-left: 8px !important;
}

body.whmcsbody .dm-kb-search #inputKnowledgebaseSearch,
body.whmcsbody .kb-search #inputKnowledgebaseSearch {
    border-top-right-radius: 4px !important;
    border-bottom-right-radius: 4px !important;
}

body.whmcsbody .dm-kb-search #btnKnowledgebaseSearch,
body.whmcsbody .kb-search #btnKnowledgebaseSearch {
    border-top-left-radius: 4px !important;
    border-bottom-left-radius: 4px !important;
    white-space: nowrap !important;
}
</style>
HTML;
});

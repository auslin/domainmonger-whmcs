<?php
/**
 * DomainMonger WHMCS contextual page titles.
 *
 * Patch 532
 * - Keeps the true client-area home page as Dashboard.
 * - Uses specific page names for WHMCS and ClouDNS module pages that were
 *   falling back to the generic Dashboard title.
 * - Does not modify language override files.
 * - Uses templatefile fallback and forced override so register/transfer domain routes do not show as Shopping Cart.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_whmcs_request_value_530')) {
    function domainmonger_whmcs_request_value_530(string $key): string
    {
        if (!isset($_REQUEST[$key])) {
            return '';
        }

        $value = $_REQUEST[$key];
        if (is_array($value)) {
            return '';
        }

        return trim((string) $value);
    }
}

if (!function_exists('domainmonger_whmcs_is_generic_title_530')) {
    function domainmonger_whmcs_is_generic_title_530(string $title): bool
    {
        $normal = strtolower(trim($title));

        return $normal === ''
            || $normal === 'dashboard'
            || $normal === 'client area'
            || $normal === 'my dashboard';
    }
}

if (!function_exists('domainmonger_whmcs_cloudns_title_530')) {
    function domainmonger_whmcs_cloudns_title_530(string $customAction): string
    {
        $action = strtolower(trim($customAction));

        $map = [
            '' => 'DNS Records',
            'zones' => 'DNS Records',
            'zone-settings' => 'DNS Records',
            'delete-record' => 'DNS Records',
            'delete-zone' => 'DNS Records',
            'add-new-record' => 'Add DNS Record',
            'add-record' => 'Add DNS Record',
            'edit-record' => 'Edit DNS Record',
            'do-edit-record' => 'Edit DNS Record',

            'mail-forwarding' => 'Mail Forwards',
            'mail-forwarding-add-mx' => 'Mail Forwards',
            'delete-forward' => 'Mail Forwards',
            'add-new-forwarding' => 'Add Mail Forward',
            'edit-forward' => 'Edit Mail Forward',
            'do-edit-forward' => 'Edit Mail Forward',

            'dnssec' => 'DNSSEC',
            'dnssec-show' => 'DNSSEC',
            'dnssec-settings' => 'DNSSEC',
            'dnssec-activate' => 'DNSSEC',
            'dnssec-deactivate' => 'DNSSEC',
            'dnssec-waiting' => 'DNSSEC',

            'soa-settings' => 'SOA',
            'edit-soa-settings' => 'SOA',
            'statistics' => 'Statistics',
            'free-ssl' => 'Free SSL',
            'freessl-activate' => 'Free SSL',
            'freessl-deactivate' => 'Free SSL',
            'freessl-change-issuer' => 'Free SSL',
            'import' => 'Import Zone File',
            'import-records' => 'Import Zone File',
            'export-zone-file' => 'Export Zone File',
            'zone-transfers' => 'Zone Transfers',
            'zone-transfers-add' => 'Zone Transfers',
            'zone-transfers-delete' => 'Zone Transfers',
            'update-status' => 'Status',
            'update' => 'Status',
            'parked-templates' => 'Parked Templates',
            'parked-templates-apply' => 'Parked Templates',
            'add-new-zone' => 'Add Zone',
            'add-existing-zone' => 'Add Zone',

            'bind-settings' => 'BIND Settings',
            'add-master-servers' => 'Master Servers',
            'delete-master-servers' => 'Master Servers',
            'activate-dynamic-url' => 'Dynamic URL',
            'create-monitoring-check' => 'Failover Monitoring',
            'get-failover-settings' => 'Failover Monitoring',
            'failover-activate' => 'Failover Monitoring',
            'failover-deactivate' => 'Failover Monitoring',
            'failover-edit' => 'Failover Monitoring',
            'failover-action-log' => 'Failover Action Log',
            'failover-monitoring-log' => 'Failover Monitoring Log',
            'failover-monitoring-notifications' => 'Failover Notifications',
            'failover-add-notification' => 'Failover Notifications',
            'failover-delete-notification' => 'Failover Notifications',
        ];

        return $map[$action] ?? '';
    }
}

if (!function_exists('domainmonger_whmcs_contextual_title_530')) {
    function domainmonger_whmcs_contextual_title_530(array $vars = []): string
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        // Keep the isolated WHMCS v9 support/testing route untouched.
        if (stripos($requestUri, 'dmv9support=1') !== false) {
            return '';
        }

        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        $pathLower = strtolower($path);
        $filename = isset($vars['filename']) ? strtolower((string) $vars['filename']) : '';
        $templateFile = isset($vars['templatefile']) ? strtolower((string) $vars['templatefile']) : '';
        $action = strtolower(domainmonger_whmcs_request_value_530('action'));
        $cartAction = strtolower(domainmonger_whmcs_request_value_530('a'));
        $domainMode = strtolower(domainmonger_whmcs_request_value_530('domain'));
        $customAction = domainmonger_whmcs_request_value_530('customAction');

        // ClouDNS module pages are served through productdetails and should use
        // their module page names instead of the generic client-area title.
        if ($action === 'productdetails' && $customAction !== '') {
            $cloudnsTitle = domainmonger_whmcs_cloudns_title_530($customAction);
            if ($cloudnsTitle !== '') {
                return $cloudnsTitle;
            }
        }

        if ($action === 'productdetails' && ($customAction === '' || strtolower($customAction) === 'zones')) {
            if ($templateFile === 'clientareaproductdetails' || strpos($pathLower, '/clientarea.php') !== false) {
                $cloudnsTitle = domainmonger_whmcs_cloudns_title_530($customAction);
                if ($cloudnsTitle !== '') {
                    return $cloudnsTitle;
                }
            }
        }

        $templateMap = [
            'clientareahome' => 'Dashboard',
            'clientareaproducts' => 'My Services',
            'clientareaproductdetails' => 'Product Details',
            'clientareadomains' => 'My Domains',
            'clientareadomainsx' => 'My Domains',
            'clientareainvoices' => 'My Invoices',
            'clientareaquotes' => 'My Quotes',
            'clientareaemails' => 'Email History',
            'clientareadetails' => 'Account Details',
            'clientareaaddfunds' => 'Add Funds',
            'clientareasecurity' => 'Account Security',
            'account-paymentmethods' => 'Payment Methods',
            'account-contacts-manage' => 'Contacts/Sub-Accounts',
            'account-user-management' => 'User Management',
            'account-user-permissions' => 'Manage Permissions',
            'user-profile' => 'Your Profile',
            'user-password' => 'Change Password',
            'user-security' => 'Account Security',
            'supportticketslist' => 'Support Tickets',
            'supportticketsubmit-stepone' => 'Open Ticket',
            'supportticketsubmit-customfields' => 'Open Ticket',
            'supportticketsubmit-kbsuggestions' => 'Open Ticket',
            'supportticketsubmit-confirm' => 'Ticket Submitted',
            'downloads' => 'Downloads',
            'downloadscat' => 'Downloads',
            'downloaddenied' => 'Downloads',
            'knowledgebase' => 'Knowledgebase',
            'knowledgebasecat' => 'Knowledgebase',
            'knowledgebasearticle' => 'Knowledgebase',
            'announcements' => 'Announcements',
            'serverstatus' => 'Network Status',
            'viewinvoice' => 'Invoice',
            'viewquote' => 'Quote',
            'managessl' => 'Manage SSL',
        ];

        if (isset($templateMap[$templateFile])) {
            return $templateMap[$templateFile];
        }

        if ($filename === 'clientarea') {
            if ($action === '') {
                return 'Dashboard';
            }

            $actionMap = [
                'services' => 'My Services',
                'productdetails' => 'Product Details',
                'domains' => 'My Domains',
                'domaindetails' => 'Domain Details',
                'invoices' => 'My Invoices',
                'quotes' => 'My Quotes',
                'emails' => 'Email History',
                'details' => 'Account Details',
                'contacts' => 'Contacts/Sub-Accounts',
                'addfunds' => 'Add Funds',
                'security' => 'Account Security',
                'masspay' => 'Mass Payment',
            ];

            if (isset($actionMap[$action])) {
                return $actionMap[$action];
            }
        }

        if ($filename === 'cart' || strpos($pathLower, '/cart.php') !== false) {
            if (($cartAction === 'add' && $domainMode === 'register') || $templateFile === 'domainregister') {
                return 'Register Domains';
            }
            if (($cartAction === 'add' && $domainMode === 'transfer') || $templateFile === 'domaintransfer') {
                return 'Transfer Domains';
            }
            if ($cartAction === 'checkout') {
                return 'Checkout';
            }
            if ($cartAction === 'confdomains') {
                return 'Configure Domains';
            }

            return 'Shopping Cart';
        }

        $pathMap = [
            '/account/paymentmethods' => 'Payment Methods',
            '/account/contacts' => 'Contacts/Sub-Accounts',
            '/account/users' => 'User Management',
            '/user/profile' => 'Your Profile',
            '/user/password' => 'Change Password',
            '/user/security' => 'Account Security',
            '/supporttickets.php' => 'Support Tickets',
            '/submitticket.php' => 'Open Ticket',
            '/downloads.php' => 'Downloads',
            '/knowledgebase.php' => 'Knowledgebase',
            '/announcements.php' => 'Announcements',
            '/serverstatus.php' => 'Network Status',
            '/viewinvoice.php' => 'Invoice',
            '/viewquote.php' => 'Quote',
            '/viewticket.php' => 'Support Ticket',
            '/managessl.php' => 'Manage SSL',
        ];

        foreach ($pathMap as $needle => $title) {
            if (strpos($pathLower, $needle) !== false) {
                return $title;
            }
        }

        return '';
    }
}

add_hook('ClientAreaPage', 1, function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    $title = domainmonger_whmcs_contextual_title_530($vars);

    if ($title === '') {
        return [];
    }

    $currentTitle = isset($vars['pagetitle']) ? (string) $vars['pagetitle'] : '';

    // Keep specific WHMCS titles unless this is one of the known module routes,
    // one of the domain cart routes that WHMCS labels as Shopping Cart,
    // or the current title is generic.
    $customAction = domainmonger_whmcs_request_value_530('customAction');
    $action = strtolower(domainmonger_whmcs_request_value_530('action'));
    $cartAction = strtolower(domainmonger_whmcs_request_value_530('a'));
    $domainMode = strtolower(domainmonger_whmcs_request_value_530('domain'));
    $templateFile = isset($vars['templatefile']) ? strtolower((string) $vars['templatefile']) : '';

    $forceDomainCartTitle = (
        ($cartAction === 'add' && ($domainMode === 'register' || $domainMode === 'transfer'))
        || $templateFile === 'domainregister'
        || $templateFile === 'domaintransfer'
    );

    $force = ($action === 'productdetails' && $customAction !== '') || $forceDomainCartTitle;

    if (!$force && !domainmonger_whmcs_is_generic_title_530($currentTitle)) {
        return [
            'dmPageTitle' => $currentTitle,
        ];
    }

    return [
        'pagetitle' => $title,
        'dmPageTitle' => $title,
    ];
});

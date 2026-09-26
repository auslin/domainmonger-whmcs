<?php
/**
 * WHMCS language overrides for v9.0.5 cart/checkout text.
 *
 * These nested keys are referenced by WHMCS v9.0.5 templates such as:
 * /templates/orderforms/standard_cart/checkout.tpl
 * /templates/orderforms/standard_cart_2/checkout.tpl
 */

if (!isset($_LANG['cart']) || !is_array($_LANG['cart'])) {
    $_LANG['cart'] = [];
}

$_LANG['cart']['availableCreditBalance'] = 'You have :amount in available account credit.';
$_LANG['cart']['applyCreditAmountNoFurtherPayment'] = 'Apply :amount from your credit balance. No further payment will be required.';
$_LANG['cart']['applyCreditAmount'] = 'Apply :amount from your credit balance and pay the remaining amount.';
$_LANG['cart']['applyCreditSkip'] = 'Do not apply credit to this order.';

if (!isset($_LANG['switchAccount']) || !is_array($_LANG['switchAccount'])) {
    $_LANG['switchAccount'] = [];
}

$_LANG['switchAccount']['title'] = 'Choose Account';
$_LANG['switchAccount']['choose'] = 'Choose the account you want to use.';
$_LANG['switchAccount']['noneFound'] = 'No accounts were found.';
$_LANG['switchAccount']['createInstructions'] = 'Create a new order to continue.';
$_LANG['switchAccount']['forcedSwitchRequest'] = 'You need to switch accounts to continue.';
$_LANG['switchAccount']['cancelAndReturn'] = 'Cancel and return';

if (!isset($_LANG['billing']) || !is_array($_LANG['billing'])) {
    $_LANG['billing'] = [];
}

if (!isset($_LANG['billing']['type']) || !is_array($_LANG['billing']['type'])) {
    $_LANG['billing']['type'] = [];
}

$_LANG['billing']['type']['invoiceadjustment'] = 'Invoice Adjustment';

if (!isset($_LANG['generatePassword']) || !is_array($_LANG['generatePassword'])) {
    $_LANG['generatePassword'] = [];
}

$_LANG['generatePassword']['lengthValidationError'] = 'Please enter a password length between 8 and 64 characters.';
$_LANG['generatePassword']['btnLabel'] = 'Generate Password';
$_LANG['generatePassword']['btnShort'] = 'Generate';
$_LANG['generatePassword']['copyAndInsert'] = 'Copy to Clipboard and Insert';
$_LANG['generatePassword']['generatedPw'] = 'Generated Password';
$_LANG['generatePassword']['generateNew'] = 'Generate New Password';
$_LANG['generatePassword']['pwLength'] = 'Password Length';
$_LANG['generatePassword']['title'] = 'Generate Password';

/*
 * Patch 1399: Active-template language entries.
 * Merges only englishx.php entries referenced by the active child template,
 * Twenty-One parent template, or standard_cart_2 order form.
 */
$_LANG['contactDetails'] = 'Contact Details';
$_LANG['createNewSupportRequest'] = 'Create New Support Request';
$_LANG['navUserManagement'] = 'User Management';

if (!isset($_LANG['domainChecker']) || !is_array($_LANG['domainChecker'])) {
    $_LANG['domainChecker'] = [];
}

$_LANG['domainChecker']['contactSupport'] = 'Contact Support';

if (!isset($_LANG['domainCheckerSalesGroup']) || !is_array($_LANG['domainCheckerSalesGroup'])) {
    $_LANG['domainCheckerSalesGroup'] = [];
}

$_LANG['domainCheckerSalesGroup']['hot'] = 'Hot';
$_LANG['domainCheckerSalesGroup']['new'] = 'New';
$_LANG['domainCheckerSalesGroup']['sale'] = 'Sale';

if (!isset($_LANG['domainSearch']) || !is_array($_LANG['domainSearch'])) {
    $_LANG['domainSearch'] = [];
}

if (!isset($_LANG['domainSearch']['errors']) || !is_array($_LANG['domainSearch']['errors'])) {
    $_LANG['domainSearch']['errors'] = [];
}

$_LANG['domainSearch']['domainOrAiInstruction'] = 'Enter a domain, keyword, or short description.';
$_LANG['domainSearch']['domainOrAiPrompt'] = 'Describe the domain you want';
$_LANG['domainSearch']['errors']['noSuggestions'] = 'No domain suggestions were found.';
$_LANG['domainSearch']['exactMatch'] = 'Exact Match';
$_LANG['domainSearch']['maxLength'] = 'Max Length';
$_LANG['domainSearch']['safeSearch'] = 'Safe Search';
$_LANG['domainSearch']['tlds'] = 'TLDs';
$_LANG['domainSearch']['topSuggestion'] = 'Top Suggestion';

if (!isset($_LANG['orderForm']) || !is_array($_LANG['orderForm'])) {
    $_LANG['orderForm'] = [];
}

$_LANG['orderForm']['exploreNow'] = 'Hosting Packages';
$_LANG['orderForm']['extendExclusions'] = 'Excludes some TLDs, recently renewed domains';
$_LANG['orderForm']['transferDomain'] = 'Transfer Domain';
$_LANG['orderForm']['transferExtend'] = 'Transfer and extend domain 1 year!';
$_LANG['orderForm']['transferToUs'] = 'Transfer your domains to us';

if (!isset($_LANG['paymentMethods']) || !is_array($_LANG['paymentMethods'])) {
    $_LANG['paymentMethods'] = [];
}

$_LANG['paymentMethods']['actions'] = 'Actions';
$_LANG['paymentMethods']['addedSuccess'] = 'Payment method added successfully.';
$_LANG['paymentMethods']['addFailed'] = 'Payment method could not be added.';
$_LANG['paymentMethods']['addNewBank'] = 'Add New Bank Account';
$_LANG['paymentMethods']['addNewCC'] = 'Add New Credit Card';
$_LANG['paymentMethods']['areYouSure'] = 'Are you sure?';
$_LANG['paymentMethods']['cardDescription'] = 'Card Description';
$_LANG['paymentMethods']['close'] = 'Close';
$_LANG['paymentMethods']['default'] = 'Default';
$_LANG['paymentMethods']['defaultUpdateFailed'] = 'Default payment method could not be updated.';
$_LANG['paymentMethods']['defaultUpdateSuccess'] = 'Default payment method updated successfully.';
$_LANG['paymentMethods']['delete'] = 'Delete';
$_LANG['paymentMethods']['deleteFailed'] = 'Payment method could not be deleted.';
$_LANG['paymentMethods']['deletePaymentMethodConfirm'] = 'Are you sure you want to delete this payment method?';
$_LANG['paymentMethods']['deleteSuccess'] = 'Payment method deleted successfully.';
$_LANG['paymentMethods']['description'] = 'Description';
$_LANG['paymentMethods']['descriptionInput'] = 'Payment Method Description';
$_LANG['paymentMethods']['edit'] = 'Edit';
$_LANG['paymentMethods']['fieldRequired'] = 'This field is required.';
$_LANG['paymentMethods']['intro'] = 'Manage saved payment methods for your account.';
$_LANG['paymentMethods']['name'] = 'Name';
$_LANG['paymentMethods']['noPaymentMethodsCreated'] = 'No payment methods have been added yet.';
$_LANG['paymentMethods']['saveChanges'] = 'Save Changes';
$_LANG['paymentMethods']['saveFailed'] = 'Payment method could not be saved.';
$_LANG['paymentMethods']['setAsDefault'] = 'Set as Default';
$_LANG['paymentMethods']['status'] = 'Status';
$_LANG['paymentMethods']['title'] = 'Payment Methods';
$_LANG['paymentMethods']['type'] = 'Type';
$_LANG['paymentMethods']['updateSuccess'] = 'Payment method updated successfully.';

if (!isset($_LANG['paymentMethodsManage']) || !is_array($_LANG['paymentMethodsManage'])) {
    $_LANG['paymentMethodsManage'] = [];
}

$_LANG['paymentMethodsManage']['accountHolderName'] = 'Account Holder Name';
$_LANG['paymentMethodsManage']['accountNumber'] = 'Account Number';
$_LANG['paymentMethodsManage']['accountNumberNotValid'] = 'The account number is not valid.';
$_LANG['paymentMethodsManage']['accountType'] = 'Account Type';
$_LANG['paymentMethodsManage']['addNewAddress'] = 'Add New Address';
$_LANG['paymentMethodsManage']['addNewBillingAddress'] = 'Add New Billing Address';
$_LANG['paymentMethodsManage']['addPaymentMethod'] = 'Add Payment Method';
$_LANG['paymentMethodsManage']['bankAccount'] = 'Bank Account';
$_LANG['paymentMethodsManage']['bankName'] = 'Bank Name';
$_LANG['paymentMethodsManage']['cardNumberNotValid'] = 'The card number is not valid.';
$_LANG['paymentMethodsManage']['checking'] = 'Checking';
$_LANG['paymentMethodsManage']['creditCard'] = 'Credit Card';
$_LANG['paymentMethodsManage']['cvcNumberNotValid'] = 'The CVC number is not valid.';
$_LANG['paymentMethodsManage']['editPaymentMethod'] = 'Edit Payment Method';
$_LANG['paymentMethodsManage']['expiryDateNotValid'] = 'The expiry date is not valid.';
$_LANG['paymentMethodsManage']['invalidCardDetails'] = 'The card details entered are invalid.';
$_LANG['paymentMethodsManage']['optional'] = 'Optional';
$_LANG['paymentMethodsManage']['routingNumberNotValid'] = 'The routing number is not valid.';
$_LANG['paymentMethodsManage']['savings'] = 'Savings';
$_LANG['paymentMethodsManage']['sortCodeRoutingNumber'] = 'Sort Code / Routing Number';
$_LANG['paymentMethodsManage']['unsupportedCardType'] = 'Unsupported card type.';

if (!isset($_LANG['pricing']) || !is_array($_LANG['pricing'])) {
    $_LANG['pricing'] = [];
}

$_LANG['pricing']['browseExtByCategory'] = 'Browse extensions by category';

if (!isset($_LANG['userManagement']) || !is_array($_LANG['userManagement'])) {
    $_LANG['userManagement'] = [];
}

$_LANG['userManagement']['accountOwnerPermissionsInfo'] = 'The account owner has all permissions and cannot be removed.';
$_LANG['userManagement']['actions'] = 'Actions';
$_LANG['userManagement']['allPermissions'] = 'All Permissions';
$_LANG['userManagement']['cancelInvite'] = 'Cancel Invite';
$_LANG['userManagement']['cancelInviteInfo'] = 'The pending invitation will no longer be usable.';
$_LANG['userManagement']['cancelInviteSure'] = 'Are you sure you want to cancel this invitation?';
$_LANG['userManagement']['choosePermissions'] = 'Choose Permissions';
$_LANG['userManagement']['emailAddress'] = 'Email Address';
$_LANG['userManagement']['inviteNewUser'] = 'Invite New User';
$_LANG['userManagement']['inviteNewUserDescription'] = 'Invite a new user to access and manage this account.';
$_LANG['userManagement']['inviteSent'] = 'Invite Sent';
$_LANG['userManagement']['lastLogin'] = 'Last Login';
$_LANG['userManagement']['managePermissions'] = 'Manage Permissions';
$_LANG['userManagement']['pendingInvites'] = 'Pending Invites';
$_LANG['userManagement']['permissions'] = 'Permissions';
$_LANG['userManagement']['removeAccess'] = 'Remove Access';
$_LANG['userManagement']['removeAccessInfo'] = 'The user will no longer be able to access this account.';
$_LANG['userManagement']['removeAccessSure'] = 'Are you sure you want to remove this user\'s access?';
$_LANG['userManagement']['resendInvite'] = 'Resend Invite';
$_LANG['userManagement']['sendInvite'] = 'Send Invite';
$_LANG['userManagement']['settings'] = 'Manage your security question, linked accounts, and two-factor authentication settings.';
$_LANG['userManagement']['usersFound'] = 'Users with Account Access';

if (!isset($_LANG['userProfile']) || !is_array($_LANG['userProfile'])) {
    $_LANG['userProfile'] = [];
}

$_LANG['userProfile']['changeEmail'] = 'Change Email Address';
$_LANG['userProfile']['notVerified'] = 'Not Verified';
$_LANG['userProfile']['profile'] = 'Profile';
$_LANG['userProfile']['verified'] = 'Verified';

// WHMCS network issue notification language keys.
$_LANG['networkIssuesAware'] = 'We are aware of a potentially service impacting issue.';
$_LANG['learnmore'] = 'Learn more';

// WHMCS profile and navigation language keys.
$_LANG['viewMore'] = 'View More...';
$_LANG['navContacts'] = 'Contacts';
$_LANG['navAccountSecurity'] = 'Account Security';
$_LANG['yourProfile'] = 'Your Profile';

// WHMCS client homepage and domain-search language keys.
if (!isset($_LANG['clientHomePanels']) || !is_array($_LANG['clientHomePanels'])) {
    $_LANG['clientHomePanels'] = [];
}

$_LANG['clientHomePanels']['productsAndServices'] = 'Browse our Products/Services';
$_LANG['transferYourDomain'] = 'Transfer Your Domain';
$_LANG['browseProducts'] = 'Browse Products';
$_LANG['secureYourDomain'] = 'Secure your domain name by registering it today';
$_LANG['transferExtend'] = 'Transfer now to extend your domain by 1 year';
$_LANG['howCanWeHelp'] = 'How can we help today';

if (!isset($_LANG['homepage']) || !is_array($_LANG['homepage'])) {
    $_LANG['homepage'] = [];
}

$_LANG['homepage']['submitTicket'] = 'Submit a Ticket';
$_LANG['homepage']['yourAccount'] = 'Your Account';
$_LANG['homepage']['manageServices'] = 'Manage Services';
$_LANG['homepage']['manageDomains'] = 'Manage Domains';
$_LANG['homepage']['supportRequests'] = 'Support Requests';
$_LANG['homepage']['makeAPayment'] = 'Make a Payment';

// WHMCS Sitejet Builder language keys.
if (!isset($_LANG['sitejetBuilder']) || !is_array($_LANG['sitejetBuilder'])) {
    $_LANG['sitejetBuilder'] = [];
}

$_LANG['sitejetBuilder']['dashboardPanelTitle'] = 'Sitejet Builder';
$_LANG['sitejetBuilder']['chooseWebsite'] = 'Choose a website to manage:';

// DomainMonger My Details section headings.
$_LANG['clientareaaccountdetails'] = 'Account Details';
$_LANG['clientareaaddress'] = 'Address';


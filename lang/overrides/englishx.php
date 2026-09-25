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

/**
 * WHMCS v9.0.5 payment methods page language overrides.
 * These nested keys are referenced by Twenty-One/Nexus account-paymentmethods.tpl.
 */
if (!isset($_LANG['paymentMethods']) || !is_array($_LANG['paymentMethods'])) {
    $_LANG['paymentMethods'] = [];
}

$_LANG['paymentMethods']['title'] = 'Payment Methods';
$_LANG['paymentMethods']['intro'] = 'Manage saved payment methods for your account.';
$_LANG['paymentMethods']['addNewCC'] = 'Add New Credit Card';
$_LANG['paymentMethods']['addNewBank'] = 'Add New Bank Account';
$_LANG['paymentMethods']['name'] = 'Name';
$_LANG['paymentMethods']['description'] = 'Description';
$_LANG['paymentMethods']['status'] = 'Status';
$_LANG['paymentMethods']['actions'] = 'Actions';
$_LANG['paymentMethods']['default'] = 'Default';
$_LANG['paymentMethods']['setAsDefault'] = 'Set as Default';
$_LANG['paymentMethods']['edit'] = 'Edit';
$_LANG['paymentMethods']['delete'] = 'Delete';
$_LANG['paymentMethods']['noPaymentMethodsCreated'] = 'No payment methods have been added yet.';
$_LANG['paymentMethods']['areYouSure'] = 'Are you sure?';
$_LANG['paymentMethods']['deletePaymentMethodConfirm'] = 'Are you sure you want to delete this payment method?';
$_LANG['paymentMethods']['addedSuccess'] = 'Payment method added successfully.';
$_LANG['paymentMethods']['addFailed'] = 'Payment method could not be added.';
$_LANG['paymentMethods']['updateSuccess'] = 'Payment method updated successfully.';
$_LANG['paymentMethods']['saveFailed'] = 'Payment method could not be saved.';
$_LANG['paymentMethods']['defaultUpdateSuccess'] = 'Default payment method updated successfully.';
$_LANG['paymentMethods']['defaultUpdateFailed'] = 'Default payment method could not be updated.';
$_LANG['paymentMethods']['deleteSuccess'] = 'Payment method deleted successfully.';
$_LANG['paymentMethods']['deleteFailed'] = 'Payment method could not be deleted.';
$_LANG['paymentMethods']['close'] = 'Close';
$_LANG['paymentMethods']['saveChanges'] = 'Save Changes';
$_LANG['paymentMethods']['type'] = 'Type';
$_LANG['paymentMethods']['fieldRequired'] = 'This field is required.';
$_LANG['paymentMethods']['cardDescription'] = 'Card Description';
$_LANG['paymentMethods']['descriptionInput'] = 'Payment Method Description';

if (!isset($_LANG['paymentMethodsManage']) || !is_array($_LANG['paymentMethodsManage'])) {
    $_LANG['paymentMethodsManage'] = [];
}

$_LANG['paymentMethodsManage']['editPaymentMethod'] = 'Edit Payment Method';
$_LANG['paymentMethodsManage']['addPaymentMethod'] = 'Add Payment Method';
$_LANG['paymentMethodsManage']['invalidCardDetails'] = 'The card details entered are invalid.';
$_LANG['paymentMethodsManage']['creditCard'] = 'Credit Card';
$_LANG['paymentMethodsManage']['bankAccount'] = 'Bank Account';
$_LANG['paymentMethodsManage']['optional'] = 'Optional';
$_LANG['paymentMethodsManage']['unsupportedCardType'] = 'Unsupported card type.';
$_LANG['paymentMethodsManage']['cardNumberNotValid'] = 'The card number is not valid.';
$_LANG['paymentMethodsManage']['expiryDateNotValid'] = 'The expiry date is not valid.';
$_LANG['paymentMethodsManage']['cvcNumberNotValid'] = 'The CVC number is not valid.';
$_LANG['paymentMethodsManage']['accountType'] = 'Account Type';
$_LANG['paymentMethodsManage']['checking'] = 'Checking';
$_LANG['paymentMethodsManage']['savings'] = 'Savings';
$_LANG['paymentMethodsManage']['accountHolderName'] = 'Account Holder Name';
$_LANG['paymentMethodsManage']['bankName'] = 'Bank Name';
$_LANG['paymentMethodsManage']['sortCodeRoutingNumber'] = 'Sort Code / Routing Number';
$_LANG['paymentMethodsManage']['routingNumberNotValid'] = 'The routing number is not valid.';
$_LANG['paymentMethodsManage']['accountNumber'] = 'Account Number';
$_LANG['paymentMethodsManage']['accountNumberNotValid'] = 'The account number is not valid.';
$_LANG['paymentMethodsManage']['addNewAddress'] = 'Add New Address';
$_LANG['paymentMethodsManage']['addNewBillingAddress'] = 'Add New Billing Address';

// WHMCS v9 account navigation labels.
$_LANG['navUserManagement'] = 'User Management';
$_LANG['navUSerMAnagement'] = 'User Management';
$_LANG['navAccountSecurity'] = 'Account Security';
$_LANG['navUserManagementShort'] = 'Users';
$_LANG['navAccountSecurityShort'] = 'Security';

// WHMCS v9 submit ticket language labels.
$_LANG['createNewSupportRequest'] = 'Create New Support Request';

// WHMCS v9 user management language labels.
if (!isset($_LANG['userManagement']) || !is_array($_LANG['userManagement'])) {
    $_LANG['userManagement'] = [];
}

$_LANG['userManagement']['title'] = 'User Management';
$_LANG['userManagement']['usersFound'] = 'Users with Account Access';
$_LANG['userManagement']['emailAddress'] = 'Email Address';
$_LANG['userManagement']['lastLogin'] = 'Last Login';
$_LANG['userManagement']['actions'] = 'Actions';
$_LANG['userManagement']['managePermissions'] = 'Manage Permissions';
$_LANG['userManagement']['removeAccess'] = 'Remove Access';
$_LANG['userManagement']['pendingInvites'] = 'Pending Invites';
$_LANG['userManagement']['inviteSent'] = 'Invite Sent';
$_LANG['userManagement']['resendInvite'] = 'Resend Invite';
$_LANG['userManagement']['cancelInvite'] = 'Cancel Invite';
$_LANG['userManagement']['accountOwnerPermissionsInfo'] = 'The account owner has all permissions and cannot be removed.';
$_LANG['userManagement']['inviteNewUser'] = 'Invite New User';
$_LANG['userManagement']['inviteNewUserDescription'] = 'Invite a new user to access and manage this account.';
$_LANG['userManagement']['allPermissions'] = 'All Permissions';
$_LANG['userManagement']['choosePermissions'] = 'Choose Permissions';
$_LANG['userManagement']['sendInvite'] = 'Send Invite';
$_LANG['userManagement']['removeAccessSure'] = 'Are you sure you want to remove this user\'s access?';
$_LANG['userManagement']['removeAccessInfo'] = 'The user will no longer be able to access this account.';
$_LANG['userManagement']['cancelInviteSure'] = 'Are you sure you want to cancel this invitation?';
$_LANG['userManagement']['cancelInviteInfo'] = 'The pending invitation will no longer be usable.';
$_LANG['userManagement']['permissions'] = 'Permissions';
$_LANG['userManagement']['settings'] = 'Manage your security question, linked accounts, and two-factor authentication settings.';

// WHMCS v9 account menu labels.
$_LANG['navContacts'] = 'Contacts/Sub-Accounts';
$_LANG['yourProfile'] = 'Your Profile';

/*
 * Patch 42/43: User Profile language fixes.
 * Fixes raw nested language keys shown on the User Profile page.
 */
if (!isset($_LANG['userProfile']) || !is_array($_LANG['userProfile'])) {
    $_LANG['userProfile'] = [];
}

$_LANG['userProfile']['profile'] = 'Profile';
$_LANG['userProfile']['changeEmail'] = 'Change Email Address';
$_LANG['userProfile']['notVerified'] = 'Not Verified';
$_LANG['userProfile']['verified'] = 'Verified';


/*
 * Patch 44: Change Password / password generator language fixes.
 * Fixes raw GeneratePassword.BtnLabel / generatePassword.btnLabel button text
 * and related password generator modal labels used by this page.
 */
if (!isset($_LANG['generatePassword']) || !is_array($_LANG['generatePassword'])) {
    $_LANG['generatePassword'] = [];
}

$_LANG['generatePassword']['btnLabel'] = 'Generate Password';
$_LANG['generatePassword']['btnShort'] = 'Generate';
$_LANG['generatePassword']['title'] = 'Generate Password';
$_LANG['generatePassword']['pwLength'] = 'Password Length';
$_LANG['generatePassword']['generatedPw'] = 'Generated Password';
$_LANG['generatePassword']['generateNew'] = 'Generate New Password';
$_LANG['generatePassword']['copyAndInsert'] = 'Copy to Clipboard and Insert';
$_LANG['generatePassword']['lengthValidationError'] = 'Please enter a password length between 8 and 64 characters.';

// Compatibility aliases for installs/templates that reference the capitalized key form.
if (!isset($_LANG['GeneratePassword']) || !is_array($_LANG['GeneratePassword'])) {
    $_LANG['GeneratePassword'] = [];
}

$_LANG['GeneratePassword']['BtnLabel'] = 'Generate Password';
$_LANG['GeneratePassword']['BtnShort'] = 'Generate';
$_LANG['GeneratePassword']['Title'] = 'Generate Password';
$_LANG['GeneratePassword']['PwLength'] = 'Password Length';
$_LANG['GeneratePassword']['GeneratedPw'] = 'Generated Password';
$_LANG['GeneratePassword']['GenerateNew'] = 'Generate New Password';
$_LANG['GeneratePassword']['CopyAndInsert'] = 'Copy to Clipboard and Insert';
$_LANG['GeneratePassword']['LengthValidationError'] = 'Please enter a password length between 8 and 64 characters.';

/*
 * Patch 74: Register Domain language fixes.
 * Fixes raw WHMCS v9 domain-search labels shown on the Register Domain page.
 */
if (!isset($_LANG['domainSearch']) || !is_array($_LANG['domainSearch'])) {
    $_LANG['domainSearch'] = [];
}

$_LANG['domainSearch']['domainOrAiPrompt'] = 'Describe the domain you want';
$_LANG['domainSearch']['domainOrAiInstruction'] = 'Enter a domain, keyword, or short description.';
$_LANG['domainSearch']['domainOrAilnstruction'] = 'Enter a domain, keyword, or short description.';
$_LANG['domainSearch']['safeSearch'] = 'Safe Search';
$_LANG['domainSearch']['tlds'] = 'TLDs';
$_LANG['domainSearch']['maxLength'] = 'Max Length';
$_LANG['domainSearch']['topSuggestion'] = 'Top Suggestion';
$_LANG['domainSearch']['exactMatch'] = 'Exact Match';

if (!isset($_LANG['domainSearch']['errors']) || !is_array($_LANG['domainSearch']['errors'])) {
    $_LANG['domainSearch']['errors'] = [];
}

$_LANG['domainSearch']['errors']['noSuggestions'] = 'No domain suggestions were found.';

if (!isset($_LANG['domainCheckerSalesGroup']) || !is_array($_LANG['domainCheckerSalesGroup'])) {
    $_LANG['domainCheckerSalesGroup'] = [];
}

$_LANG['domainCheckerSalesGroup']['hot'] = 'Hot';
$_LANG['domainCheckerSalesGroup']['new'] = 'New';
$_LANG['domainCheckerSalesGroup']['sale'] = 'Sale';

// Compatibility aliases for templates/scripts that reference capitalized domain-search keys.
if (!isset($_LANG['DomainSearch']) || !is_array($_LANG['DomainSearch'])) {
    $_LANG['DomainSearch'] = [];
}

$_LANG['DomainSearch']['DomainOrAiPrompt'] = 'Describe the domain you want';
$_LANG['DomainSearch']['DomainOrAiInstruction'] = 'Enter a domain, keyword, or short description.';
$_LANG['DomainSearch']['DomainOrAilnstruction'] = 'Enter a domain, keyword, or short description.';
$_LANG['DomainSearch']['SafeSearch'] = 'Safe Search';
$_LANG['DomainSearch']['Tlds'] = 'TLDs';
$_LANG['DomainSearch']['MaxLength'] = 'Max Length';


/*
 * Patch 99: Register Domain promo language cleanup.
 * Keeps the v9 language keys readable on the domain registration page.
 */
if (!isset($_LANG['orderForm']) || !is_array($_LANG['orderForm'])) {
    $_LANG['orderForm'] = [];
}

$_LANG['orderForm']['transferExtend'] = 'Transfer and extend domain 1 year!';
$_LANG['orderForm']['transferDomain'] = 'Transfer Domain';
$_LANG['orderForm']['extendExclusions'] = 'Excludes some TLDs, recently renewed domains';
$_LANG['orderForm']['exploreNow'] = 'Hosting Packages';

if (!isset($_LANG['domainChecker']) || !is_array($_LANG['domainChecker'])) {
    $_LANG['domainChecker'] = [];
}

$_LANG['domainChecker']['contactSupport'] = 'Contact Support';


$_LANG['orderForm']['transferToUs'] = 'Transfer your domains to us';

/* Patch 104: Register Domain pricing language cleanup. */
if (!isset($_LANG['pricing']) || !is_array($_LANG['pricing'])) {
    $_LANG['pricing'] = [];
}

$_LANG['pricing']['browseExtByCategory'] = 'Browse extensions by category';

/*
 * Patch 194: Contacts/Sub-Accounts language fixes.
 * Fixes raw contactDetails and tax.taxLabel keys shown on the contact management page.
 */
$_LANG['contactDetails'] = 'Contact Details';

if (!isset($_LANG['tax']) || !is_array($_LANG['tax'])) {
    $_LANG['tax'] = [];
}

$_LANG['tax']['taxLabel'] = 'Tax ID';

<?php
/**
 * DomainMonger Patch 1872
 * Client Payment Methods: "Make Default CC" state-aware on add/edit
 * plus durable tooltips for payment-method/card action icons.
 *
 * Update-safe design:
 * - Hook-only implementation; no WHMCS core or stock template edits.
 * - Native WHMCS card-creation flow remains authoritative.
 * - Add: Default assignment uses WHMCS UpdatePayMethod after a newly-created card
 *   is detected, rather than writing tblpaymethods directly.
 * - Edit: Uses WHMCS editMode/payMethod state; an existing card is checked only
 *   when already default, and a checked non-default card is made default after save.
 * - Direct DB access is read-only and used only to establish the pre-create
 *   Pay Method ID baseline.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm1871_client_id')) {
    function dm1871_client_id(): int
    {
        try {
            $currentUser = new \WHMCS\Authentication\CurrentUser();
            $client = $currentUser->client();
            if ($client && (int) ($client->id ?? 0) > 0) {
                return (int) $client->id;
            }
        } catch (Throwable $e) {
            // Fall through to the established client-area session aliases.
        }

        return (int) (
            $_SESSION['uid']
            ?? $_SESSION['clientid']
            ?? $_SESSION['clientareauserid']
            ?? 0
        );
    }
}

if (!function_exists('dm1871_is_payment_methods_request')) {
    function dm1871_is_payment_methods_request(array $vars = []): bool
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');

        if (strpos($requestUri, 'dmv9support=1') !== false) {
            return false;
        }

        if (stripos($requestUri, '/account/paymentmethods') !== false) {
            return true;
        }

        foreach (['templatefile', 'filename', 'pagetitle'] as $key) {
            if (!empty($vars[$key]) && stripos((string) $vars[$key], 'payment') !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('dm1871_max_paymethod_id')) {
    function dm1871_max_paymethod_id(int $clientId): int
    {
        if ($clientId <= 0) {
            return 0;
        }

        try {
            return (int) Capsule::table('tblpaymethods')
                ->where('userid', $clientId)
                ->whereNull('deleted_at')
                ->max('id');
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('dm1871_stage_default_intent')) {
    function dm1871_stage_default_intent(int $clientId): void
    {
        if ($clientId <= 0) {
            return;
        }

        $now = time();
        $existing = $_SESSION['dm1871_default_cc_intent'] ?? null;

        // Do not move the baseline forward while a card-add attempt is already
        // in progress. This is important for tokenised/remote gateway flows that
        // can make more than one request before returning to Payment Methods.
        if (
            is_array($existing)
            && (int) ($existing['client_id'] ?? 0) === $clientId
            && (int) ($existing['expires_at'] ?? 0) > $now
        ) {
            return;
        }

        $_SESSION['dm1871_default_cc_intent'] = [
            'client_id' => $clientId,
            'baseline_id' => dm1871_max_paymethod_id($clientId),
            'created_at' => $now,
            'expires_at' => $now + 900,
        ];
    }
}

if (!function_exists('dm1871_clear_default_intent')) {
    function dm1871_clear_default_intent(): void
    {
        unset($_SESSION['dm1871_default_cc_intent']);
    }
}

if (!function_exists('dm1871_clear_edit_default_intent')) {
    function dm1871_clear_edit_default_intent(): void
    {
        unset($_SESSION['dm1871_edit_default_cc_intent']);
    }
}

if (!function_exists('dm1871_stage_edit_default_intent')) {
    function dm1871_stage_edit_default_intent(int $clientId, int $payMethodId): void
    {
        if ($clientId <= 0 || $payMethodId <= 0) {
            dm1871_clear_edit_default_intent();
            return;
        }

        // Validate ownership and card type through WHMCS before staging.
        $result = localAPI('GetPayMethods', [
            'clientid' => $clientId,
        ]);

        if (($result['result'] ?? '') !== 'success') {
            dm1871_clear_edit_default_intent();
            return;
        }

        $valid = false;
        foreach ((array) ($result['paymethods'] ?? []) as $payMethod) {
            if (!is_array($payMethod) || (int) ($payMethod['id'] ?? 0) !== $payMethodId) {
                continue;
            }

            $type = (string) ($payMethod['type'] ?? '');
            if (in_array($type, ['CreditCard', 'RemoteCreditCard'], true)) {
                $valid = true;
            }
            break;
        }

        if (!$valid) {
            dm1871_clear_edit_default_intent();
            return;
        }

        $now = time();
        $_SESSION['dm1871_edit_default_cc_intent'] = [
            'client_id' => $clientId,
            'paymethodid' => $payMethodId,
            'created_at' => $now,
            'expires_at' => $now + 900,
        ];
    }
}

if (!function_exists('dm1871_apply_edit_default_if_ready')) {
    /**
     * Apply a staged existing-card default change only after WHMCS has returned
     * to the Payment Methods list following the native edit/save flow.
     *
     * @return array{status:string,paymethodid:int,message:string}
     */
    function dm1871_apply_edit_default_if_ready(int $clientId): array
    {
        $intent = $_SESSION['dm1871_edit_default_cc_intent'] ?? null;
        if (!is_array($intent)) {
            return ['status' => 'none', 'paymethodid' => 0, 'message' => ''];
        }

        $now = time();
        $payMethodId = (int) ($intent['paymethodid'] ?? 0);
        if (
            (int) ($intent['client_id'] ?? 0) !== $clientId
            || (int) ($intent['expires_at'] ?? 0) <= $now
            || $payMethodId <= 0
        ) {
            dm1871_clear_edit_default_intent();
            return ['status' => 'none', 'paymethodid' => 0, 'message' => ''];
        }

        // Revalidate ownership/type before changing the default.
        $methods = localAPI('GetPayMethods', [
            'clientid' => $clientId,
        ]);
        if (($methods['result'] ?? '') !== 'success') {
            dm1871_clear_edit_default_intent();
            return ['status' => 'error', 'paymethodid' => $payMethodId, 'message' => 'Unable to verify the edited credit card.'];
        }

        $valid = false;
        foreach ((array) ($methods['paymethods'] ?? []) as $payMethod) {
            if (!is_array($payMethod) || (int) ($payMethod['id'] ?? 0) !== $payMethodId) {
                continue;
            }
            $type = (string) ($payMethod['type'] ?? '');
            $valid = in_array($type, ['CreditCard', 'RemoteCreditCard'], true);
            break;
        }

        if (!$valid) {
            dm1871_clear_edit_default_intent();
            return ['status' => 'error', 'paymethodid' => $payMethodId, 'message' => 'Unable to verify the edited credit card.'];
        }

        $result = localAPI('UpdatePayMethod', [
            'clientid' => $clientId,
            'paymethodid' => $payMethodId,
            'set_as_default' => true,
        ]);

        dm1871_clear_edit_default_intent();

        if (($result['result'] ?? '') === 'success') {
            return ['status' => 'success', 'paymethodid' => $payMethodId, 'message' => ''];
        }

        return [
            'status' => 'error',
            'paymethodid' => $payMethodId,
            'message' => (string) ($result['message'] ?? 'WHMCS could not make the edited card the default payment method.'),
        ];
    }
}

if (!function_exists('dm1871_find_new_credit_card')) {
    /**
     * Return the newest CreditCard/RemoteCreditCard created after the staged
     * baseline. GetPayMethods is authoritative for the Pay Method type.
     */
    function dm1871_find_new_credit_card(int $clientId, int $baselineId): int
    {
        if ($clientId <= 0) {
            return 0;
        }

        $result = localAPI('GetPayMethods', [
            'clientid' => $clientId,
        ]);

        if (($result['result'] ?? '') !== 'success') {
            return 0;
        }

        $candidateId = 0;
        foreach ((array) ($result['paymethods'] ?? []) as $payMethod) {
            if (!is_array($payMethod)) {
                continue;
            }

            $id = (int) ($payMethod['id'] ?? 0);
            if ($id <= $baselineId || $id <= $candidateId) {
                continue;
            }

            $type = (string) ($payMethod['type'] ?? '');
            if (!in_array($type, ['CreditCard', 'RemoteCreditCard'], true)) {
                continue;
            }

            $candidateId = $id;
        }

        return $candidateId;
    }
}

if (!function_exists('dm1871_apply_default_if_ready')) {
    /**
     * @return array{status:string,paymethodid:int,message:string}
     */
    function dm1871_apply_default_if_ready(int $clientId): array
    {
        $intent = $_SESSION['dm1871_default_cc_intent'] ?? null;
        if (!is_array($intent)) {
            return ['status' => 'none', 'paymethodid' => 0, 'message' => ''];
        }

        $now = time();
        if (
            (int) ($intent['client_id'] ?? 0) !== $clientId
            || (int) ($intent['expires_at'] ?? 0) <= $now
        ) {
            dm1871_clear_default_intent();
            return ['status' => 'none', 'paymethodid' => 0, 'message' => ''];
        }

        $baselineId = max(0, (int) ($intent['baseline_id'] ?? 0));
        $payMethodId = dm1871_find_new_credit_card($clientId, $baselineId);
        if ($payMethodId <= 0) {
            return ['status' => 'pending', 'paymethodid' => 0, 'message' => ''];
        }

        $result = localAPI('UpdatePayMethod', [
            'clientid' => $clientId,
            'paymethodid' => $payMethodId,
            'set_as_default' => true,
        ]);

        if (($result['result'] ?? '') === 'success') {
            dm1871_clear_default_intent();
            return ['status' => 'success', 'paymethodid' => $payMethodId, 'message' => ''];
        }

        // The card itself was created successfully; do not keep retrying a
        // default-change failure indefinitely. The client can still use the
        // existing star action on the Payment Methods page.
        dm1871_clear_default_intent();
        return [
            'status' => 'error',
            'paymethodid' => $payMethodId,
            'message' => (string) ($result['message'] ?? 'WHMCS could not make the new card the default payment method.'),
        ];
    }
}

add_hook('ClientAreaPaymentMethods', 1, function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    if (!dm1871_is_payment_methods_request($vars)) {
        return [];
    }

    $clientId = dm1871_client_id();
    if ($clientId <= 0) {
        return [];
    }

    $intentAction = strtolower(trim((string) ($_GET['dm1871_default_cc_intent'] ?? '')));
    if ($intentAction === 'stage') {
        dm1871_stage_default_intent($clientId);
        return [];
    }
    if ($intentAction === 'clear') {
        dm1871_clear_default_intent();
        return [];
    }

    $editIntentAction = strtolower(trim((string) ($_GET['dm1871_edit_default_cc_intent'] ?? '')));
    if ($editIntentAction === 'stage') {
        dm1871_stage_edit_default_intent($clientId, (int) ($_GET['paymethodid'] ?? 0));
        return [];
    }
    if ($editIntentAction === 'clear') {
        dm1871_clear_edit_default_intent();
        return [];
    }

    $apply = dm1871_apply_default_if_ready($clientId);
    if (($apply['status'] ?? '') === 'error') {
        $_SESSION['dm1871_default_cc_notice'] = [
            'type' => 'warning',
            'message' => 'The credit card was added, but it could not be made the default automatically. You can use the star icon to set it as default.',
        ];
    }

    // Existing-card default changes are applied only once WHMCS has returned
    // to the list after the native edit flow. Never apply them on the edit form
    // itself or during the save POST.
    $requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $editMode = !empty($vars['editMode']);
    if (!$editMode && $requestMethod === 'GET') {
        $editApply = dm1871_apply_edit_default_if_ready($clientId);
        if (($editApply['status'] ?? '') === 'error') {
            $_SESSION['dm1871_default_cc_notice'] = [
                'type' => 'warning',
                'message' => 'The credit card was updated, but it could not be made the default automatically. You can use the star icon to set it as default.',
            ];
        }
    }

    return [];
});

add_hook('ClientAreaFooterOutput', 90, function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    if (!dm1871_is_payment_methods_request($vars)) {
        return '';
    }

    $notice = $_SESSION['dm1871_default_cc_notice'] ?? null;
    unset($_SESSION['dm1871_default_cc_notice']);

    $noticeHtml = '';
    if (is_array($notice) && !empty($notice['message'])) {
        $safeMessage = htmlspecialchars((string) $notice['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $noticeHtml = '<div id="dm1871-default-cc-notice" class="alert alert-warning" role="alert">' . $safeMessage . '</div>';
    }

    $noticeJson = json_encode($noticeHtml, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if ($noticeJson === false) {
        $noticeJson = '""';
    }

    // WHMCS exposes authoritative edit state to this hook. Use it instead of
    // guessing from routed URLs, which can vary between WHMCS versions.
    $editMode = !empty($vars['editMode']);
    $editPayMethodId = 0;
    $editPayMethodIsDefault = false;
    $editPayMethodIsCreditCard = false;
    $payMethod = $vars['payMethod'] ?? null;
    if ($editMode && is_object($payMethod)) {
        try {
            $editPayMethodId = (int) ($payMethod->id ?? 0);
        } catch (Throwable $e) {
            $editPayMethodId = 0;
        }
        try {
            $editPayMethodIsDefault = (bool) $payMethod->isDefaultPayMethod();
        } catch (Throwable $e) {
            $editPayMethodIsDefault = false;
        }
        try {
            $editPayMethodIsCreditCard = (bool) $payMethod->isCreditCard();
        } catch (Throwable $e) {
            $editPayMethodIsCreditCard = false;
        }
    }

    $editModeJson = $editMode ? 'true' : 'false';
    $editPayMethodIdJson = (string) max(0, $editPayMethodId);
    $editPayMethodIsDefaultJson = $editPayMethodIsDefault ? 'true' : 'false';
    $editPayMethodIsCreditCardJson = $editPayMethodIsCreditCard ? 'true' : 'false';

    return <<<HTML
<style id="domainmonger-client-payment-default-1871-css">
#dm1871-make-default-row {
    margin-top: 2px;
    margin-bottom: 14px;
}
#dm1871-make-default-row .dm1871-check-label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin: 0;
    cursor: pointer;
    font-weight: 400;
}
#dm1871-make-default-row input[type="checkbox"] {
    margin: 0;
}
#payMethodList .dm1871-card-icon-tooltip {
    cursor: help;
}
</style>
<script id="domainmonger-client-payment-default-1871-js">
(function () {
    'use strict';

    var noticeHtml = {$noticeJson};
    var pageEditMode = {$editModeJson};
    var editPayMethodId = {$editPayMethodIdJson};
    var editPayMethodIsDefault = {$editPayMethodIsDefaultJson};
    var editPayMethodIsCreditCard = {$editPayMethodIsCreditCardJson};

    function intentUrl(action) {
        var url = new URL(window.location.href);
        url.searchParams.set('dm1871_default_cc_intent', action);
        url.searchParams.set('dm1871_ts', String(Date.now()));
        return url.toString();
    }

    function sendIntent(action) {
        try {
            return fetch(intentUrl(action), {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function () { return null; });
        } catch (e) {
            return Promise.resolve(null);
        }
    }

    function editIntentUrl(action) {
        var url = new URL(window.location.href);
        url.searchParams.set('dm1871_edit_default_cc_intent', action);
        url.searchParams.set('paymethodid', String(editPayMethodId || 0));
        url.searchParams.set('dm1871_ts', String(Date.now()));
        return url.toString();
    }

    function sendEditIntent(action) {
        try {
            return fetch(editIntentUrl(action), {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function () { return null; });
        } catch (e) {
            return Promise.resolve(null);
        }
    }

    function isCreditCardSelected(form) {
        var selected = form.querySelector('input[name="type"]:checked');
        if (!selected) {
            return true;
        }
        return String(selected.value || '').toLowerCase() !== 'bankacct';
    }

    function installMakeDefaultOption() {
        var form = document.getElementById('frmManagePaymentMethod');
        if (!form || form.querySelector('#dm1871MakeDefaultCc')) {
            return;
        }

        // On Edit, trust WHMCS's server-side editMode/payMethod state rather
        // than attempting to infer mode from the routed form action.
        if (pageEditMode && (!editPayMethodIsCreditCard || editPayMethodId <= 0)) {
            return;
        }

        var submitContainer = form.querySelector('.submit-container');
        if (!submitContainer) {
            return;
        }

        var row = document.createElement('div');
        row.id = 'dm1871-make-default-row';
        row.className = 'form-group row';
        row.innerHTML = ''
            + '<div class="col-md-8 offset-md-4">'
            + '  <label class="dm1871-check-label" for="dm1871MakeDefaultCc">'
            + '    <input type="checkbox" id="dm1871MakeDefaultCc"' + ((!pageEditMode || editPayMethodIsDefault) ? ' checked' : '') + '>'
            + '    <span>Make Default CC</span>'
            + '  </label>'
            + '</div>';

        submitContainer.parentNode.insertBefore(row, submitContainer);

        var checkbox = document.getElementById('dm1871MakeDefaultCc');

        function syncState() {
            if (pageEditMode) {
                row.style.display = '';

                // Cleanup for Patch 1871: an edit page could previously be
                // mistaken for Add and leave a new-card intent behind. Never
                // allow an existing-card edit to affect a later card creation.
                sendIntent('clear');

                if (!checkbox.checked || editPayMethodIsDefault) {
                    sendEditIntent('clear');
                }
                return;
            }

            var cardSelected = isCreditCardSelected(form);
            row.style.display = cardSelected ? '' : 'none';

            if (!cardSelected || !checkbox.checked) {
                sendIntent('clear');
            }
        }

        function stageIntentSynchronously() {
            if (pageEditMode) {
                // A card that is already default needs no API action. For a
                // non-default existing card, stage the explicit choice so it is
                // applied only after WHMCS completes the native edit/save flow.
                if (!checkbox.checked || editPayMethodIsDefault || editPayMethodId <= 0) {
                    return;
                }

                try {
                    var editXhr = new XMLHttpRequest();
                    editXhr.open('GET', editIntentUrl('stage'), false);
                    editXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    editXhr.send(null);
                } catch (e) {
                    // Never block WHMCS's native edit flow for this convenience.
                }
                return;
            }

            if (!checkbox.checked || !isCreditCardSelected(form)) {
                return;
            }

            // Stage the pre-create Pay Method baseline immediately before the
            // native WHMCS card flow begins. Synchronous XHR is intentional
            // here: it is a tiny same-origin GET and guarantees the baseline
            // exists before local or tokenised gateway handlers continue.
            try {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', intentUrl('stage'), false);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.send(null);
            } catch (e) {
                // Card creation must never be blocked by this convenience
                // feature. If staging fails, WHMCS continues normally.
            }
        }

        checkbox.addEventListener('change', syncState);

        if (!pageEditMode) {
            Array.prototype.forEach.call(form.querySelectorAll('input[name="type"]'), function (typeInput) {
                typeInput.addEventListener('change', syncState);
            });
        }

        // Capture both the button click (important for assisted/tokenised
        // gateway JavaScript) and the native form submit. The server keeps the
        // first baseline/intent for the active attempt, so the second event is safe.
        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.addEventListener('click', stageIntentSynchronously, true);
        }
        form.addEventListener('submit', stageIntentSynchronously, true);

        // Add defaults checked. Edit mirrors WHMCS's current default state.
        syncState();
    }

    function installPaymentMethodTooltips() {
        var table = document.getElementById('payMethodList');
        if (!table) {
            return;
        }

        Array.prototype.forEach.call(table.querySelectorAll('tr'), function (row) {
            var cells = row.querySelectorAll('td');
            if (cells.length < 2) {
                return;
            }

            var cardIcon = cells[0].querySelector('i');
            if (cardIcon && !cardIcon.getAttribute('data-dm1871-tooltip-ready')) {
                var name = String(cells[1].textContent || '').replace(/\s+/g, ' ').trim();
                if (name) {
                    cardIcon.setAttribute('title', name);
                    cardIcon.setAttribute('aria-label', name);
                    cardIcon.setAttribute('tabindex', '0');
                    cardIcon.setAttribute('data-toggle', 'tooltip');
                    cardIcon.setAttribute('data-placement', 'top');
                    cardIcon.setAttribute('data-dm1871-tooltip-ready', '1');
                    cardIcon.classList.add('dm1871-card-icon-tooltip');
                }
            }
        });

        Array.prototype.forEach.call(table.querySelectorAll('a[title], button[title]'), function (button) {
            if (!button.getAttribute('data-dm1871-tooltip-ready')) {
                button.setAttribute('data-toggle', 'tooltip');
                button.setAttribute('data-placement', 'top');
                button.setAttribute('data-dm1871-tooltip-ready', '1');
            }
        });

        if (window.jQuery && jQuery.fn && typeof jQuery.fn.tooltip === 'function') {
            jQuery(table).find('[data-toggle="tooltip"][data-dm1871-tooltip-ready="1"]').each(function () {
                var item = jQuery(this);
                if (!item.data('bs.tooltip')) {
                    item.tooltip({ container: 'body', trigger: 'hover focus' });
                }
            });
        }
    }

    function showNotice() {
        if (!noticeHtml) {
            return;
        }
        var cardBody = document.querySelector('#payMethodList')
            ? document.querySelector('#payMethodList').closest('.card-body')
            : document.querySelector('.card .card-body');
        if (cardBody && !document.getElementById('dm1871-default-cc-notice')) {
            cardBody.insertAdjacentHTML('afterbegin', noticeHtml);
        }
    }

    function start() {
        installMakeDefaultOption();
        installPaymentMethodTooltips();
        showNotice();

        if (window.MutationObserver) {
            var observer = new MutationObserver(function () {
                installPaymentMethodTooltips();
            });
            observer.observe(document.documentElement, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
HTML;
});

{include file="orderforms/standard_cart/common.tpl"}

<div id="order-standard_cart">

    <div class="row dm-domain-transfer-layout">

        <div class="col-md-3 sidebar hidden-xs hidden-sm dm-domain-transfer-sidebar">

            {include file="orderforms/standard_cart/sidebar-categories.tpl"}

        </div>

        <div class="col-md-9 dm-domain-transfer-main">

            {include file="orderforms/standard_cart/sidebar-categories-collapsed.tpl"}

            <form method="post" action="cart.php" id="frmDomainTransfer">
                <input type="hidden" name="a" value="addDomainTransfer">

                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="panel panel-default dm-domain-transfer-panel">
                            <div class="panel-body">
                                <div id="dmTransferRows" class="dm-transfer-rows">
                                    <div class="row dm-transfer-row" data-dm-transfer-row>
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="inputTransferDomain">{lang key='domainname'}</label>
                                                <input type="text" class="form-control dm-transfer-domain" name="domain" id="inputTransferDomain" value="{$lookupTerm}" placeholder="{lang key='yourdomainplaceholder'}.{lang key='yourtldplaceholder'}" data-toggle="tooltip" data-placement="left" data-trigger="manual" title="{lang key='orderForm.enterDomain'}" />
                                            </div>
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="inputAuthCode" style="width:100%;">
                                                    {lang key='orderForm.authCode'}
                                                    <a href="" data-toggle="tooltip" data-placement="left" title="{lang key='orderForm.authCodeTooltip'}" class="pull-right"><i class="fas fa-question-circle"></i> {lang key='orderForm.help'}</a>
                                                </label>
                                                <input type="text" class="form-control dm-transfer-epp" name="epp" id="inputAuthCode" placeholder="{lang key='orderForm.authCodePlaceholder'}" data-toggle="tooltip" data-placement="left" data-trigger="manual" title="{lang key='orderForm.required'}" />
                                            </div>
                                        </div>
                                        <div class="col-sm-2 dm-transfer-remove-cell">
                                            <div class="form-group dm-transfer-remove-wrap">
                                                <label class="dm-transfer-remove-spacer">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-block dm-remove-transfer-row hidden" aria-label="Remove domain transfer row">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="dm-transfer-add-row-wrap">
                                    <button type="button" id="dmAddTransferRow" class="btn btn-default btn-sm dm-add-transfer-row">
                                        <i class="fas fa-plus" aria-hidden="true"></i> Add more domains
                                    </button>
                                </div>

                                <div id="dmMultiTransferStatus" class="alert alert-info slim-alert text-center hidden"></div>
                                <div id="transferUnavailable" class="alert alert-warning slim-alert text-center w-hidden"></div>
                                {if $captcha->isEnabled() && !$captcha->recaptcha->isEnabled()}
                                    <div class="captcha-container" id="captchaContainer">
                                        <div class="default-captcha">
                                            <p>{lang key="cartSimpleCaptcha"}</p>
                                            <div>
                                                <img id="inputCaptchaImage" src="{$systemurl}includes/verifyimage.php" />
                                                <input id="inputCaptcha" type="text" name="code" maxlength="5" class="form-control input-sm" data-toggle="tooltip" data-placement="right" data-trigger="manual" title="{lang key='orderForm.required'}" />
                                            </div>
                                        </div>
                                    </div>
                                {elseif $captcha->isEnabled() && $captcha->recaptcha->isEnabled() && !$captcha->recaptcha->isInvisible()}
                                    <div class="form-group recaptcha-container" id="captchaContainer"></div>
                                {/if}
                            </div>

                            <div class="panel-footer text-right">
                                <a href="cart.php?a=confdomains" id="dmContinueTransfer" class="btn btn-primary dm-transfer-continue hidden">
                                    Go to Cart
                                </a>
                                <button type="submit" id="btnTransferDomain" class="btn btn-primary btn-transfer{$captcha->getButtonClass($captchaForm)}">
                                    <span class="loader hidden dm-transfer-submit-loader" id="addTransferLoader" style="display:none;">
                                        <i class="fas fa-fw fa-spinner fa-spin"></i>
                                    </span>
                                    <span id="addToCart">{lang key="orderForm.addToCart"}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>


        </div>
    </div>
</div>

{literal}
<style>
#order-standard_cart .dm-domain-transfer-panel {
    margin-top: 0;
}
#order-standard_cart .dm-domain-transfer-panel .panel-body {
    padding-top: 20px;
}
#order-standard_cart .dm-transfer-row {
    margin-bottom: 8px;
}
#order-standard_cart .dm-transfer-row .form-group {
    margin-bottom: 10px;
}
#order-standard_cart .dm-transfer-remove-cell {
    padding-top: 0;
}
#order-standard_cart .dm-transfer-remove-wrap {
    margin-bottom: 10px;
}
#order-standard_cart .dm-transfer-remove-spacer {
    display: block;
    visibility: hidden;
    margin: 0 0 5px;
    line-height: 20px;
}
#order-standard_cart .dm-remove-transfer-row {
    margin-top: 0;
}
#order-standard_cart #addTransferLoader,
#order-standard_cart .dm-transfer-submit-loader {
    display: none;
}
#order-standard_cart #addTransferLoader.dm-transfer-loading,
#order-standard_cart .dm-transfer-submit-loader.dm-transfer-loading {
    display: inline-block;
}
#order-standard_cart .dm-transfer-add-row-wrap {
    margin: 4px 0 18px;
}
#order-standard_cart .dm-add-transfer-row {
    border-color: #163a5f;
    color: #163a5f;
    background: #fff;
}
#order-standard_cart .dm-add-transfer-row:hover,
#order-standard_cart .dm-add-transfer-row:focus {
    background: #fdf0e6;
    border-color: #d8741f;
    color: #163a5f;
}
#order-standard_cart .dm-remove-transfer-row {
    background: #b94a48;
    border-color: #b94a48;
}
#order-standard_cart .dm-transfer-continue {
    margin-right: 8px;
    background: #f58220;
    border-color: #f58220;
    color: #fff;
}
#order-standard_cart .dm-transfer-continue:hover,
#order-standard_cart .dm-transfer-continue:focus {
    background: #d8741f;
    border-color: #d8741f;
    color: #fff;
}
@media (max-width: 767px) {
    #order-standard_cart .dm-transfer-remove-cell {
        margin-bottom: 14px;
    }
    #order-standard_cart .dm-transfer-remove-spacer {
        display: none;
    }
}
</style>
<script>
(function () {
    'use strict';

    var form = document.getElementById('frmDomainTransfer');
    var rowsWrap = document.getElementById('dmTransferRows');
    var addBtn = document.getElementById('dmAddTransferRow');
    var submitBtn = document.getElementById('btnTransferDomain');
    var submitText = document.getElementById('addToCart');
    var continueBtn = document.getElementById('dmContinueTransfer');
    var loader = document.getElementById('addTransferLoader');
    var statusBox = document.getElementById('dmMultiTransferStatus');
    var unavailableBox = document.getElementById('transferUnavailable');

    if (!form || !rowsWrap || !addBtn) {
        return;
    }

    function setMessage(box, message, type) {
        if (!box) {
            return;
        }
        box.className = 'alert slim-alert text-center alert-' + type;
        box.innerHTML = message;
        box.classList.remove('hidden');
    }

    function hideMessage(box) {
        if (box) {
            box.classList.add('hidden');
            box.innerHTML = '';
        }
    }

    function showContinueButton() {
        if (continueBtn) {
            continueBtn.classList.remove('hidden');
        }
    }

    function makeRow() {
        var row = document.createElement('div');
        row.className = 'row dm-transfer-row';
        row.setAttribute('data-dm-transfer-row', '');
        row.innerHTML = '' +
            '<div class="col-sm-5">' +
                '<div class="form-group">' +
                    '<label>Domain Name</label>' +
                    '<input type="text" class="form-control dm-transfer-domain" placeholder="example.com" />' +
                '</div>' +
            '</div>' +
            '<div class="col-sm-5">' +
                '<div class="form-group">' +
                    '<label>Authorization Code</label>' +
                    '<input type="text" class="form-control dm-transfer-epp" placeholder="Authorization code" />' +
                '</div>' +
            '</div>' +
            '<div class="col-sm-2 dm-transfer-remove-cell">' +
                '<div class="form-group dm-transfer-remove-wrap">' +
                    '<label class="dm-transfer-remove-spacer">&nbsp;</label>' +
                    '<button type="button" class="btn btn-danger btn-block dm-remove-transfer-row">Remove</button>' +
                '</div>' +
            '</div>';
        return row;
    }

    function getRows() {
        return Array.prototype.slice.call(rowsWrap.querySelectorAll('[data-dm-transfer-row]'));
    }

    function getEntries() {
        var entries = [];
        getRows().forEach(function (row) {
            var domainInput = row.querySelector('.dm-transfer-domain');
            var eppInput = row.querySelector('.dm-transfer-epp');
            var domain = domainInput ? domainInput.value.replace(/^\s+|\s+$/g, '') : '';
            var epp = eppInput ? eppInput.value.replace(/^\s+|\s+$/g, '') : '';
            if (domain || epp) {
                entries.push({
                    domain: domain,
                    epp: epp,
                    domainInput: domainInput,
                    eppInput: eppInput
                });
            }
        });
        return entries;
    }

    function setButtonLoading(isLoading) {
        if (!submitBtn) {
            return;
        }
        submitBtn.disabled = isLoading;
        submitBtn.classList.toggle('disabled', isLoading);
        if (loader) {
            loader.classList.toggle('hidden', !isLoading);
            loader.classList.toggle('dm-transfer-loading', isLoading);
            loader.style.display = isLoading ? 'inline-block' : 'none';
        }
        if (submitText) {
            submitText.style.display = isLoading ? 'none' : '';
        }
    }

    function validateEntries(entries) {
        var invalid = null;
        entries.some(function (entry) {
            if (!entry.domain) {
                invalid = { input: entry.domainInput, message: 'Please enter a domain name for every transfer row.' };
                return true;
            }
            if (!entry.epp) {
                invalid = { input: entry.eppInput, message: 'Please enter an authorization code for every transfer row.' };
                return true;
            }
            return false;
        });
        if (invalid) {
            setMessage(unavailableBox, invalid.message, 'warning');
            if (invalid.input) {
                invalid.input.focus();
            }
            return false;
        }
        return true;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }

    function normalizeRows() {
        var rows = getRows();

        rows.forEach(function (row, index) {
            var domainInput = row.querySelector('.dm-transfer-domain');
            var eppInput = row.querySelector('.dm-transfer-epp');
            var removeButton = row.querySelector('.dm-remove-transfer-row');

            if (domainInput) {
                if (index === 0) {
                    domainInput.setAttribute('name', 'domain');
                    domainInput.setAttribute('id', 'inputTransferDomain');
                } else {
                    domainInput.removeAttribute('name');
                    domainInput.removeAttribute('id');
                }
            }

            if (eppInput) {
                if (index === 0) {
                    eppInput.setAttribute('name', 'epp');
                    eppInput.setAttribute('id', 'inputAuthCode');
                } else {
                    eppInput.removeAttribute('name');
                    eppInput.removeAttribute('id');
                }
            }

            if (removeButton) {
                removeButton.classList.toggle('hidden', rows.length <= 1);
            }
        });
    }

    function removeSuccessfulRows(results) {
        results.forEach(function (result) {
            if (!result.added || !result.entry || !result.entry.domainInput) {
                return;
            }

            var row = result.entry.domainInput.closest('[data-dm-transfer-row]');
            if (row && row.parentNode) {
                row.parentNode.removeChild(row);
            }
        });

        if (getRows().length === 0) {
            rowsWrap.appendChild(makeRow());
        }

        normalizeRows();
    }

    function failureSummary(failures) {
        var html = '<strong>' + failures.length + ' domain' + (failures.length === 1 ? '' : 's')
            + ' could not be added:</strong>';
        html += '<ul class="text-left" style="margin:10px 0 0 20px;">';

        failures.forEach(function (failure) {
            html += '<li><strong>' + escapeHtml(failure.entry.domain) + '</strong> &mdash; '
                + escapeHtml(failure.message) + '</li>';
        });

        html += '</ul>';
        return html;
    }

    function postTransfer(entry, index, total) {
        var token = '';
        if (typeof csrfToken !== 'undefined' && csrfToken) {
            token = csrfToken;
        }

        var payload = {
            a: 'addDomainTransfer',
            domain: entry.domain,
            epp: entry.epp
        };
        if (token) {
            payload.token = token;
        }

        setMessage(statusBox, 'Adding transfer ' + index + ' of ' + total + ': ' + entry.domain, 'info');

        return new Promise(function (resolve, reject) {
            var request;

            try {
                request = WHMCS.http.jqClient.post(
                    form.getAttribute('action') || 'cart.php',
                    payload,
                    null,
                    'json'
                );
            } catch (requestError) {
                requestError.entry = entry;
                reject(requestError);
                return;
            }

            request.done(function (response) {
                if (!response || typeof response !== 'object') {
                    var invalidResponseError = new Error('Unexpected response from the transfer service.');
                    invalidResponseError.entry = entry;
                    reject(invalidResponseError);
                    return;
                }

                var result = response.result;
                var message = '';

                if (result === 'added') {
                    resolve({
                        added: true,
                        entry: entry,
                        message: ''
                    });
                    return;
                }

                if (result && result.epp === 1) {
                    message = entry.epp
                        ? 'The authorization code could not be accepted.'
                        : 'An authorization code is required.';
                } else if (result && result.unavailable) {
                    message = result.unavailable;
                } else if (result && result.domainErrorMessage) {
                    message = result.domainErrorMessage;
                } else if (result && result.isAvailable === true && result.isRegistered === false) {
                    message = 'You cannot transfer a domain that is not registered.';
                } else {
                    message = 'Unable to add this transfer to the cart.';
                }

                resolve({
                    added: false,
                    entry: entry,
                    message: message
                });
            });

            request.fail(function (jqXHR, textStatus, errorThrown) {
                var reason = errorThrown || textStatus || 'request failed';
                var requestFailure = new Error('Transfer request failed (' + reason + ').');
                requestFailure.entry = entry;
                reject(requestFailure);
            });
        });
    }

    addBtn.addEventListener('click', function () {
        hideMessage(statusBox);
        hideMessage(unavailableBox);
        rowsWrap.appendChild(makeRow());
        normalizeRows();
        var rows = getRows();
        var newDomain = rows[rows.length - 1].querySelector('.dm-transfer-domain');
        if (newDomain) {
            newDomain.focus();
        }
    });

    rowsWrap.addEventListener('click', function (event) {
        var target = event.target;
        if (!target || !target.classList.contains('dm-remove-transfer-row')) {
            return;
        }
        var row = target.closest('[data-dm-transfer-row]');
        if (row && getRows().length > 1) {
            row.parentNode.removeChild(row);
            normalizeRows();
            hideMessage(statusBox);
            hideMessage(unavailableBox);
        }
    });

    form.addEventListener('submit', function (event) {
        var entries = getEntries();
        var captchaRequired = document.getElementById('captchaContainer')
            || (submitBtn && /captcha|recaptcha/i.test(submitBtn.className));

        /* Preserve WHMCS's native CAPTCHA flow for a single transfer. */
        if (captchaRequired && entries.length <= 1) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        hideMessage(statusBox);
        hideMessage(unavailableBox);

        if (captchaRequired) {
            setMessage(unavailableBox, 'Multiple transfers are not available when captcha is required. Please submit one domain at a time.', 'warning');
            return false;
        }

        if (entries.length === 0) {
            setMessage(unavailableBox, 'Please enter a domain name and authorization code.', 'warning');
            return false;
        }

        if (!validateEntries(entries)) {
            return false;
        }

        if (!window.WHMCS || !WHMCS.http || !WHMCS.http.jqClient) {
            setMessage(unavailableBox, 'Unable to start domain transfers because the WHMCS cart scripts did not load.', 'warning');
            return false;
        }

        setButtonLoading(true);

        var results = [];
        var currentIndex = 0;

        function processNext() {
            if (currentIndex >= entries.length) {
                return Promise.resolve();
            }

            var entryIndex = currentIndex;
            var entry = entries[currentIndex];
            currentIndex += 1;

            return postTransfer(entry, entryIndex + 1, entries.length).then(function (result) {
                results.push(result);
                return processNext();
            });
        }

        processNext().then(function () {
            var added = results.filter(function (result) {
                return result.added;
            });
            var failed = results.filter(function (result) {
                return !result.added;
            });

            if (failed.length === 0) {
                setMessage(statusBox, 'Transfers added. Opening domain configuration...', 'success');
                window.location = 'cart.php?a=confdomains';
                return;
            }

            setButtonLoading(false);
            hideMessage(statusBox);
            removeSuccessfulRows(results);

            if (added.length > 0) {
                setMessage(
                    statusBox,
                    '<strong>' + added.length + ' of ' + entries.length + ' domains were added to your cart.</strong>',
                    'success'
                );
                showContinueButton();
            }

            setMessage(unavailableBox, failureSummary(failed), 'warning');
        }).catch(function (error) {
            var added = results.filter(function (result) {
                return result.added;
            });
            var message = error && error.message
                ? error.message
                : 'A system error interrupted the transfer requests.';
            var failedDomain = error && error.entry && error.entry.domain
                ? error.entry.domain
                : 'the current domain';
            var unprocessedCount = entries.length - results.length;

            setButtonLoading(false);
            hideMessage(statusBox);
            removeSuccessfulRows(results);

            if (added.length > 0) {
                setMessage(
                    statusBox,
                    '<strong>' + added.length + ' of ' + entries.length + ' domains were added to your cart.</strong>',
                    'success'
                );
                showContinueButton();
            }

            setMessage(
                unavailableBox,
                '<strong>Transfer processing stopped at ' + escapeHtml(failedDomain) + '.</strong> '
                    + escapeHtml(message) + ' '
                    + escapeHtml(unprocessedCount) + ' domain' + (unprocessedCount === 1 ? ' was' : 's were')
                    + ' not completed and remain in the form.',
                'warning'
            );
        });

        return false;
    }, true);

    normalizeRows();
}());
</script>
{/literal}

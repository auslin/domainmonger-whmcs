{if $successful}
    {include file="$template/includes/alert.tpl" type="success" msg="{lang key='changessavedsuccessfully'}" textcenter=true}
{/if}

{if $errormessage}
    {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage}
{/if}

{if in_array('state', $optionalFields)}
    <script>
        var stateNotRequired = true;
    </script>
{/if}

<script type="text/javascript" src="{$BASE_PATH_JS}/StatesDropdown.js"></script>

<div class="dm-my-details-page">

    <form method="post" action="?action=details" role="form" class="dm-my-details-form">

        <div class="card dm-my-details-card">
            <div class="card-header">
                <h3 class="card-title mb-0">{lang key='clientareanavdetails'}</h3>
            </div>
            <div class="card-body">
                <div class="row dm-my-details-grid">
                    <div class="col-md-6">

                        <h4 class="dm-my-details-section-title">{lang key='clientareaaccountdetails'}</h4>

                        <div class="form-group">
                            <label for="inputFirstName" class="col-form-label">{lang key='clientareafirstname'}</label>
                            <input type="text" name="firstname" id="inputFirstName" value="{$clientfirstname}"{if in_array('firstname', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputLastName" class="col-form-label">{lang key='clientarealastname'}</label>
                            <input type="text" name="lastname" id="inputLastName" value="{$clientlastname}"{if in_array('lastname', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputCompanyName" class="col-form-label">{lang key='clientareacompanyname'}</label>
                            <input type="text" name="companyname" id="inputCompanyName" value="{$clientcompanyname}"{if in_array('companyname', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputEmail" class="col-form-label">{lang key='clientareaemail'}</label>
                            <input type="email" name="email" id="inputEmail" value="{$clientemail}"{if in_array('email', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                    </div>

                    <div class="col-md-6">

                        <h4 class="dm-my-details-section-title">{lang key='clientareaaddress'}</h4>

                        <div class="form-group">
                            <label for="inputAddress1" class="col-form-label">{lang key='clientareaaddress1'}</label>
                            <input type="text" name="address1" id="inputAddress1" value="{$clientaddress1}"{if in_array('address1', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputAddress2" class="col-form-label">{lang key='clientareaaddress2'}</label>
                            <input type="text" name="address2" id="inputAddress2" value="{$clientaddress2}"{if in_array('address2', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputCity" class="col-form-label">{lang key='clientareacity'}</label>
                            <input type="text" name="city" id="inputCity" value="{$clientcity}"{if in_array('city', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputState" class="col-form-label">{lang key='clientareastate'}</label>
                                <input type="text" name="state" id="inputState" value="{$clientstate}"{if in_array('state', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputPostcode" class="col-form-label">{lang key='clientareapostcode'}</label>
                                <input type="text" name="postcode" id="inputPostcode" value="{$clientpostcode}"{if in_array('postcode', $uneditablefields)} disabled="disabled"{/if} class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label" for="country">{lang key='clientareacountry'}</label>
                            {$clientcountriesdropdown}
                        </div>

                        <div class="form-group">
                            <label for="inputPhone" class="col-form-label">{lang key='clientareaphonenumber'}</label>
                            <input type="tel" name="phonenumber" id="inputPhone" value="{$clientphonenumber}"{if in_array('phonenumber',$uneditablefields)} disabled=""{/if} class="form-control" />
                        </div>

                    </div>

                    <div class="col-md-6">

                        <h4 class="dm-my-details-section-title">{lang key='billingdetails'}</h4>

                        <div class="form-group">
                            <label for="inputPaymentMethod" class="col-form-label">{lang key='paymentmethod'}</label>
                            <select name="paymentmethod" id="inputPaymentMethod" class="form-control custom-select">
                                <option value="none">{lang key='paymentmethoddefault'}</option>
                                {foreach $paymentmethods as $method}
                                <option value="{$method.sysname}"{if $method.sysname eq $defaultpaymentmethod} selected="selected"{/if}>{$method.name}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="inputBillingContact" class="col-form-label">{lang key='defaultbillingcontact'}</label>
                            <select name="billingcid" id="inputBillingContact" class="form-control custom-select">
                                <option value="0">{lang key='usedefaultcontact'}</option>
                                {foreach $contacts as $contact}
                                <option value="{$contact.id}"{if $contact.id eq $billingcid} selected="selected"{/if}>{$contact.name}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="inputAccountLanguage" class="col-form-label">{lang key='clientarealanguage'}</label>
                            <select name="accountLanguage" id="inputAccountLanguage" class="form-control custom-select"
                                {if in_array('language', $uneditablefields)} disabled="disabled"{/if}>
                                <option value="">{lang key='default'}</option>
                                {foreach $languages as $language}
                                    <option value="{$language}"{if $language eq $clientLanguage} selected="selected"{/if}
                                        >{$language|ucfirst}</option>
                                {/foreach}
                            </select>
                        </div>

                        {if $showTaxIdField}
                            <div class="form-group">
                                <label for="inputTaxId" class="col-form-label">{lang key=$taxIdLabel}</label>
                                <input type="text" name="tax_id" id="inputTaxId" class="form-control" value="{$clientTaxId}"{if in_array('tax_id', $uneditablefields)} disabled="disabled"{/if} />
                            </div>
                        {/if}

                    </div>

                    {if $customfields}
                        <div class="col-md-6">
                            <h4 class="dm-my-details-section-title">{lang key='additionalInfo'}</h4>

                            {foreach $customfields as $customfield}
                                <div class="form-group">
                                    <label class="col-form-label" for="customfield{$customfield.id}">{$customfield.name}</label>
                                    <div class="control dm-my-details-custom-field">
                                        {$customfield.input} {$customfield.description}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}

                </div>
            </div>
        </div>

        {if !empty($accountDetailsExtraFields)}
            <div class="card dm-my-details-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">{lang key='orderForm.additionalInformation'}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {foreach $accountDetailsExtraFields as $field}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label" for="{$field.name}">{$field.label|escape}{if $field.required} <span class="text-danger">*</span>{/if}</label>
                                    <div class="control dm-my-details-custom-field">
                                        {$field.input}
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        {/if}

        {if $emailPreferencesEnabled}
            <div class="card dm-my-details-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">{lang key='clientareacontactsemails'}</h3>
                </div>
                <div class="card-body">
                    <div class="controls form-check dm-my-details-check-list">
                        {foreach $emailPreferences as $emailType => $value}
                            <label>
                                <input type="hidden" name="email_preferences[{$emailType}]" value="0">
                                <input type="checkbox" class="form-check-input" name="email_preferences[{$emailType}]" id="{$emailType}Emails" value="1"{if $value} checked="checked"{/if} />
                                {lang key="emailPreferences."|cat:$emailType}
                            </label>{if !($value@last)}<br />{/if}
                        {/foreach}
                    </div>
                </div>
            </div>
        {/if}

        {if $showMarketingEmailOptIn}
            <div class="card dm-my-details-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">{lang key='emailMarketing.joinOurMailingList'}</h3>
                </div>
                <div class="card-body">
                    <p>{$marketingEmailOptInMessage}</p>
                    <input type="checkbox" name="marketingoptin" value="1"{if $marketingEmailOptIn} checked{/if} class="no-icheck toggle-switch-success form-check-input" data-size="small" data-on-text="{lang key='yes'}" data-off-text="{lang key='no'}">
                </div>
            </div>
        {/if}

        <div class="form-group text-center dm-my-details-actions">
            <input class="btn btn-primary" type="submit" name="save" value="{lang key='clientareasavechanges'}" />
            <input class="btn btn-default" type="reset" value="{lang key='cancel'}" />
        </div>

    </form>

</div>

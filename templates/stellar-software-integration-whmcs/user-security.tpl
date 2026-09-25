<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-security-settings-v47.css?v=1479">

{include file="$template/includes/flashmessage.tpl"}

<div class="dm-security-settings-page">
    {* DM Patch 614: removed duplicate Account Security intro card; order is 2FA, Security Question, Single Sign-On. *}
    {if $twoFactorAuthAvailable}
        <div class="dm-security-card card dm-two-factor-card">
            <div class="card-body dm-security-card-body">
                <div class="dm-security-section-header dm-two-factor-header">
                    <h3 class="card-title dm-security-title">{lang key='twofactorauth'}</h3>
                    <div class="dm-two-factor-status">
                        {if $twoFactorAuthEnabled}
                            <span class="badge badge-success label label-success dm-security-badge dm-security-badge-success dm-two-factor-enabled-badge">
                                {lang key='enabled'}
                            </span>
                        {else}
                            <span class="badge badge-secondary label label-default dm-security-badge dm-security-badge-muted dm-two-factor-disabled-badge">
                                {lang key='disabled'}
                            </span>
                        {/if}
                    </div>
                </div>

                <div class="dm-security-section-content dm-two-factor-content">
                    {if $twoFactorAuthEnabled}
                        <p class="dm-two-factor-current dm-two-factor-current-enabled">
                            {lang key='twofacurrently'} <strong>{lang key='enabled'|strtolower}</strong>
                        </p>

                        <div class="dm-security-actions dm-two-factor-actions">
                            <a href="{routePath('account-security-two-factor-disable')}" class="btn btn-danger open-modal dm-btn-danger dm-two-factor-disable-btn" data-modal-title="{lang key='twofadisable'}" data-modal-class="twofa-setup" data-btn-submit-label="{lang key='twofadisable'}" data-btn-submit-color="danger" data-btn-submit-id="btnDisable2FA">
                                {lang key='twofadisableclickhere'}
                            </a>
                        </div>
                    {else}
                        <p class="dm-two-factor-current dm-two-factor-current-disabled">
                            {lang key='twofacurrently'} <strong>{lang key='disabled'|strtolower}</strong>
                        </p>

                        <div class="dm-two-factor-alert">
                            {if $twoFactorAuthRequired}
                                {include file="$template/includes/alert.tpl" type="warning" msg="{lang key="clientAreaSecurityTwoFactorAuthRequired"}"}
                            {else}
                                {include file="$template/includes/alert.tpl" type="warning" msg="{lang key="clientAreaSecurityTwoFactorAuthRecommendation"}"}
                            {/if}
                        </div>

                        <div class="dm-security-actions dm-two-factor-actions">
                            <a href="{routePath('account-security-two-factor-enable')}" class="btn btn-success open-modal dm-btn-primary dm-two-factor-enable-btn" data-modal-title="{lang key='twofaenable'}" data-modal-class="twofa-setup" data-btn-submit-id="btnEnable2FA">
                                {lang key='twofaenableclickhere'}
                            </a>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    {/if}
    {if $securityQuestions->count() > 0}
        <div class="dm-security-card card dm-security-question-card">
            <div class="card-body dm-security-card-body">
                <div class="dm-security-section-header">
                    <h3 class="card-title dm-security-title">{lang key='clientareanavsecurityquestions'}</h3>
                </div>

                <form method="post" action="{routePath('user-security-question')}" class="dm-security-form">
                    {if $user->hasSecurityQuestion()}
                        <div class="form-group dm-form-group">
                            <label for="inputCurrentAns" class="col-form-label dm-form-label">{$user->getSecurityQuestion()}</label>
                            <input type="password" name="currentsecurityqans" id="inputCurrentAns" class="form-control dm-form-control" autocomplete="off" />
                        </div>
                    {/if}

                    <div class="form-group dm-form-group">
                        <label for="inputSecurityQid" class="col-form-label dm-form-label">{lang key='clientareasecurityquestion'}</label>
                        <select name="securityqid" id="inputSecurityQid" class="form-control custom-select dm-form-control">
                            {foreach $securityQuestions as $question}
                                <option value="{$question->id}">
                                    {$question->question}
                                </option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="row dm-security-form-row">
                        <div class="col-md-6">
                            <div class="form-group dm-form-group">
                                <label for="inputSecurityAns1" class="col-form-label dm-form-label">{lang key='clientareasecurityanswer'}</label>
                                <input type="password" name="securityqans" id="inputSecurityAns1" class="form-control dm-form-control" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group dm-form-group">
                                <label for="inputSecurityAns2" class="col-form-label dm-form-label">{lang key='clientareasecurityconfanswer'}</label>
                                <input type="password" name="securityqans2" id="inputSecurityAns2" class="form-control dm-form-control" autocomplete="off" />
                            </div>
                        </div>
                    </div>

                    <div class="dm-security-actions">
                        <input class="btn btn-primary dm-btn-primary" type="submit" name="submit" value="{lang key='clientareasavechanges'}" />
                        <input class="btn btn-default btn-secondary dm-btn-secondary" type="reset" value="{lang key='cancel'}" />
                    </div>
                </form>
            </div>
        </div>
    {/if}

    {if $showSsoSetting || $dmCombinedSecurityPage}
        <div class="dm-security-card card dm-sso-card dm-security-sso-card">
            <div class="card-body dm-security-card-body dm-sso-card-body-wrap">
                <div class="dm-security-section-header dm-sso-section-header">
                    <h3 class="card-title dm-security-title dm-sso-title">{lang key='sso.title'}</h3>
                </div>

                <div class="dm-security-section-content dm-sso-section-content">
                    <div class="dm-sso-summary">
                        {lang key='sso.summary'}
                    </div>

                    <form id="frmSingleSignOn" class="dm-sso-form">
                        <input type="hidden" name="token" value="{$token}" />
                        <input type="hidden" name="action" value="security" />
                        <input type="hidden" name="toggle_sso" value="1" />

                        <div class="dm-sso-toggle-row">
                            <input type="checkbox" name="allow_sso" class="toggle-switch-success dm-sso-toggle" id="inputAllowSso"{if $isSsoEnabled} checked{/if}>
                            <span class="dm-sso-status-text" id="ssoStatusTextEnabled"{if !$isSsoEnabled} style="display: none;"{/if}>
                                {lang key='sso.enabled'}
                            </span>
                            <span class="dm-sso-status-text" id="ssoStatusTextDisabled"{if $isSsoEnabled} style="display: none;"{/if}>
                                {lang key='sso.disabled'}
                            </span>
                        </div>
                    </form>

                    <p class="dm-sso-notice">{lang key='sso.disablenotice'}</p>
                </div>
            </div>
        </div>
    {/if}

    {if $linkableProviders}
        <div class="dm-security-card card dm-linked-accounts-card">
            <div class="card-body dm-security-card-body">
                <div class="dm-security-section-header">
                    <h3 class="card-title dm-security-title">{lang key='remoteAuthn.titleLinkedAccounts'}</h3>
                </div>

                <div class="dm-security-section-content dm-linked-accounts-content">
                    {include file="$template/includes/linkedaccounts.tpl" linkContext="clientsecurity" }

                    <div class="dm-security-linked-table">
                        {include file="$template/includes/linkedaccounts.tpl" linkContext="linktable" }
                    </div>
                </div>
            </div>
        </div>
    {/if}

</div>

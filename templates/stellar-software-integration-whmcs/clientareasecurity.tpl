{if $showSsoSetting}
    <div class="card dm-sso-card">
        <div class="dm-sso-card-header">
            <h3 class="card-title dm-sso-title">{lang key='sso.title'}</h3>
        </div>

        <div class="card-body dm-sso-card-body">
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
{/if}

<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-change-password-v47.css?v=47">

{include file="$template/includes/flashmessage.tpl"}

<div class="dm-change-password-page">
    <div class="dm-password-card card">
        <div class="card-body dm-password-card-body">
            <form class="using-password-strength dm-password-form" method="post" action="{routePath('user-password')}" role="form">
                <input type="hidden" name="submit" value="true" />

                <div class="row dm-password-form-row">
                    <div class="col-md-7 col-lg-6">
                        <div class="form-group dm-form-group">
                            <label for="inputExistingPassword" class="col-form-label dm-form-label">{lang key='existingpassword'}</label>
                            <input type="password" class="form-control dm-form-control" name="existingpw" id="inputExistingPassword" autocomplete="off" />
                        </div>
                    </div>
                </div>

                <div id="newPassword1" class="form-group has-feedback dm-form-group dm-password-generator-group">
                    <div class="row dm-password-form-row dm-password-generator-row">
                        <div class="col-md-10 col-lg-9 dm-password-generator-inline-col">
                            <label for="inputNewPassword1" class="col-form-label dm-form-label">{lang key='newpassword'}</label>
                            <div class="dm-password-generator-inline-controls">
                                <div class="dm-password-input-wrap">
                                    <input type="password" class="form-control dm-form-control" name="newpw" id="inputNewPassword1" autocomplete="off" />
                                    {include file="$template/includes/pwstrength.tpl" maximumPasswordLength=$maximumPasswordLength}
                                </div>
                                <button type="button" class="btn btn-default generate-password dm-btn-secondary dm-generate-password-btn" data-targetfields="inputNewPassword1,inputNewPassword2">
                                    {lang key='generatePassword.btnLabel'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row dm-password-form-row">
                    <div class="col-md-7 col-lg-6">
                        <div id="newPassword2" class="form-group has-feedback dm-form-group">
                            <label for="inputNewPassword2" class="col-form-label dm-form-label">{lang key='confirmnewpassword'}</label>
                            <input type="password" class="form-control dm-form-control" name="confirmpw" id="inputNewPassword2" autocomplete="off" />
                            <div id="inputNewPassword2Msg" class="dm-password-match-message"></div>
                        </div>
                    </div>
                </div>

                <div class="dm-password-actions">
                    <input class="btn btn-primary dm-btn-primary" type="submit" value="{lang key='clientareasavechanges'}" />
                    <input class="btn btn-default btn-secondary dm-btn-secondary" type="reset" value="{lang key='cancel'}" />
                </div>
            </form>
        </div>
    </div>
</div>

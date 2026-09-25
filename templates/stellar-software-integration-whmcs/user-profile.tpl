<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-user-profile-v42c.css?v=42c">

{include file="$template/includes/flashmessage.tpl"}

<div class="dm-user-profile-page">
    <div class="dm-profile-card card">
        <div class="card-body dm-profile-card-body">
            <form method="post" action="{routePath('user-profile-save')}" class="dm-profile-form">
                <div class="row dm-profile-form-row">
                    <div class="col-md-6">
                        <div class="form-group dm-form-group">
                            <label for="inputFirstName" class="col-form-label dm-form-label">
                                {lang key='clientareafirstname'}
                            </label>
                            <input
                                type="text"
                                name="firstname"
                                id="inputFirstName"
                                value="{$user->firstName}"
                                class="form-control dm-form-control"
                                {if in_array('firstname', $uneditableFields)}disabled="disabled"{/if}
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group dm-form-group">
                            <label for="inputLastName" class="col-form-label dm-form-label">
                                {lang key='clientarealastname'}
                            </label>
                            <input
                                type="text"
                                name="lastname"
                                id="inputLastName"
                                value="{$user->lastName}"
                                class="form-control dm-form-control"
                                {if in_array('lastname', $uneditableFields)}disabled="disabled"{/if}
                            >
                        </div>
                    </div>
                </div>

                <div class="dm-profile-actions">
                    <input class="btn btn-primary dm-btn-primary" id="btnSaveNameChanges" type="submit" name="save" value="{lang key='clientareasavechanges'}" />
                    <input class="btn btn-default btn-secondary dm-btn-secondary" type="reset" value="{lang key='cancel'}" />
                </div>
            </form>
        </div>
    </div>

    <div class="dm-profile-card card">
        <div class="card-body dm-profile-card-body">
            <div class="dm-profile-section-header dm-profile-email-header">
                <h3 class="card-title dm-profile-title">{lang key='userProfile.changeEmail'}</h3>
                <div class="dm-profile-email-status">
                    {if $user->needsToCompleteEmailVerification()}
                        <span class="label label-default badge badge-secondary dm-profile-badge dm-profile-badge-muted">{lang key='userProfile.notVerified'}</span>
                    {elseif $user->emailVerified()}
                        <span class="label label-success badge badge-success dm-profile-badge dm-profile-badge-success">{lang key='userProfile.verified'}</span>
                    {/if}
                </div>
            </div>

            <form method="post" action="{routePath('user-profile-email-save')}" class="dm-profile-form">
                <div class="row dm-profile-form-row">
                    <div class="col-md-6">
                        <div class="form-group dm-form-group">
                            <label for="inputEmail" class="col-form-label dm-form-label">
                                {lang key='clientareaemail'}
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="inputEmail"
                                value="{$user->email}"
                                class="form-control dm-form-control"
                                {if in_array('email', $uneditableFields)}disabled="disabled"{/if}
                            >
                        </div>

                        {if !in_array('email', $uneditableFields)}
                            <div class="form-group dm-form-group">
                                <label for="emailChangePasswordConfirmation" class="col-form-label dm-form-label">
                                    {lang key='existingpassword'}
                                </label>
                                <input
                                    type="password"
                                    name="existing_password"
                                    id="emailChangePasswordConfirmation"
                                    class="form-control dm-form-control"
                                >
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="dm-profile-actions">
                    <input class="btn btn-primary dm-btn-primary" id="btnSaveEmailChanges" type="submit" name="save" value="{lang key='clientareasavechanges'}" />
                    <input class="btn btn-default btn-secondary dm-btn-secondary" type="reset" value="{lang key='cancel'}" />
                </div>
            </form>
        </div>
    </div>
</div>

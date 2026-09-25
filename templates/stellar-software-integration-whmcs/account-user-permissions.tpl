{include file="$template/includes/flashmessage.tpl"}

<style id="dm-user-permissions-checklist-677">
/* Patch 677: User Management > Manage Permissions checklist layout + orange checkbox fix. */
body.whmcs-templatefile-account-user-permissions .dm-user-permissions-card .card-title,
body.whmcs-templatefile-account-user-permissions .dm-user-permissions-heading {
    text-align: center;
}

body.whmcs-templatefile-account-user-permissions .dm-user-permissions-email {
    margin: 0 0 18px;
    text-align: left;
}

body.whmcs-templatefile-account-user-permissions .dm-permission-checklist {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 22px;
    margin: 16px 0 20px;
}

body.whmcs-templatefile-account-user-permissions .dm-permission-checklist-item {
    display: flex !important;
    align-items: flex-start !important;
    gap: 10px;
    min-width: 0;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.35;
    color: #111827;
    font-weight: 600;
    cursor: pointer;
    position: relative;
}

body.whmcs-templatefile-account-user-permissions .dm-permission-checkbox {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    display: inline-block !important;
    position: static !important;
    flex: 0 0 16px !important;
    width: 16px !important;
    min-width: 16px !important;
    max-width: 16px !important;
    height: 16px !important;
    min-height: 16px !important;
    max-height: 16px !important;
    margin: 2px 0 0 !important;
    padding: 0 !important;
    border: 1.5px solid #222 !important;
    border-radius: 3px !important;
    background-color: #fff !important;
    background-image: none !important;
    box-shadow: none !important;
    opacity: 1 !important;
    cursor: pointer;
}

body.whmcs-templatefile-account-user-permissions .dm-permission-checkbox:checked {
    border-color: #222 !important;
    background-color: #f58220 !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath d='M3.4 8.1l3 3.1 6.2-6.4' fill='none' stroke='%23ffffff' stroke-width='2.3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: 13px 13px !important;
}

body.whmcs-templatefile-account-user-permissions .dm-permission-checkbox:focus {
    outline: 2px solid rgba(245, 130, 32, 0.28) !important;
    outline-offset: 2px !important;
}

body.whmcs-templatefile-account-user-permissions .dm-permission-title {
    display: block;
    min-width: 0;
    padding: 0 !important;
    margin: 0 !important;
}

body.whmcs-templatefile-account-user-permissions .dm-user-permissions-actions {
    margin: 4px 0 0;
}

@media (max-width: 767px) {
    body.whmcs-templatefile-account-user-permissions .dm-permission-checklist {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="card dm-user-permissions-card">
    <div class="card-body">
        <h3 class="card-title">{lang key='userManagement.managePermissions'}</h3>

        <p class="dm-user-permissions-email">{$user->email}</p>

        <p class="h5 dm-user-permissions-heading">{lang key="userManagement.permissions"}</p>

        <form method="post" action="{routePath('account-users-permissions-save', $user->id)}">

            <div class="dm-permission-checklist" role="list">
                {foreach $permissions as $permission}
                    <label class="dm-permission-checklist-item" role="listitem">
                        <input type="checkbox" class="dm-permission-checkbox" name="perms[{$permission.key}]" value="1"{if $userPermissions->hasPermission($permission.key)} checked{/if}>
                        <span class="dm-permission-title">{$permission.title}</span>
                    </label>
                {/foreach}
            </div>

            <p class="dm-user-permissions-actions">
                <button type="submit" class="btn btn-primary">
                    {lang key="clientareasavechanges"}
                </button>
                <a href="{routePath('account-users')}" class="btn btn-default">
                    {lang key="clientareacancel"}
                </a>
            </p>

        </form>

    </div>
</div>

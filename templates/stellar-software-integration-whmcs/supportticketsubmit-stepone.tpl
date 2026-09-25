<div class="card dm-submit-ticket-card dm-submit-ticket-departments">
    <div class="card-header">
        <h3 class="card-title mb-0">{lang key="createNewSupportRequest"}</h3>
    </div>
    <div class="card-body">
        <p class="dm-submit-ticket-intro">{lang key='supportticketsheader'}</p>

        <div class="dm-ticket-department-list">
            {foreach $departments as $num => $department}
                <a href="{$smarty.server.PHP_SELF}?step=2&amp;deptid={$department.id}" class="dm-ticket-department-card">
                    <span class="dm-ticket-department-icon"><i class="fas fa-envelope"></i></span>
                    <span class="dm-ticket-department-content">
                        <span class="dm-ticket-department-name">{$department.name}</span>
                        {if $department.description}
                            <span class="dm-ticket-department-description">{$department.description}</span>
                        {/if}
                    </span>
                    <span class="dm-ticket-department-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
            {foreachelse}
                <div class="dm-submit-ticket-empty">
                    {include file="$template/includes/alert.tpl" type="info" msg="{lang key='nosupportdepartments'}" textcenter=true}
                </div>
            {/foreach}
        </div>
    </div>
</div>

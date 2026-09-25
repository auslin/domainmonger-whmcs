<div class="div-service-item" data-href="clientarea.php?action=productdetails&id={$service->id}">
    <div class="div-service-status">
        <span class="label label-placeholder">
            {$statusProperties[array_key_first($statusProperties)]['translation']}
        </span>
        <span class="label label-{$statusProperties[$service->domainStatus]['modifier']}"
              title="{$statusProperties[$service->domainStatus]['translation']}"
        >
            {$statusProperties[$service->domainStatus]['translation']}
        </span>
    </div>
    <div class="div-service-name">
        <span class="font-weight-bold">
            {$service->product->productGroup->name} - {$service->product->name}
        </span>
        <span class="text-domain">{$service->domain}</span>
    </div>
    <div class="div-service-buttons dm-service-actions dm-service-actions-simple">
        {assign var=dmRenderedWhm value=false}
        {assign var=dmRenderedCpanel value=false}
        {assign var=dmRenderedWebmail value=false}

        {* Render exactly one WHM action when WHMCS exposes one for a reseller service. *}
        {if $primaryServiceBtn && !$dmRenderedWhm && (stripos($primaryServiceBtn['identifier'], 'whm') !== false || stripos($primaryServiceBtn['display'], 'whm') !== false)}
            <button type="button"
                    class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-whm{if !$primaryServiceBtn['active']} disabled{/if}"
                    data-serviceid="{$primaryServiceBtn['serviceid']}"
                    data-identifier="{$primaryServiceBtn['identifier']}"
                    data-active="{$primaryServiceBtn['active']}"
                    {if !$primaryServiceBtn['active']}disabled="disabled"{/if}
            >
                <span class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
                <i class="fas fa-server" aria-hidden="true"></i>
                WHM
            </button>
            {assign var=dmRenderedWhm value=true}
        {/if}
        {foreach $accentPrimaryServiceBtns as $accentPrimaryServiceBtn}
            {if !$dmRenderedWhm && (stripos($accentPrimaryServiceBtn['identifier'], 'whm') !== false || stripos($accentPrimaryServiceBtn['display'], 'whm') !== false)}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-whm{if !$accentPrimaryServiceBtn['active']} disabled{/if}"
                        data-serviceid="{$accentPrimaryServiceBtn['serviceid']}"
                        data-identifier="{$accentPrimaryServiceBtn['identifier']}"
                        data-active="{$accentPrimaryServiceBtn['active']}"
                        {if !$accentPrimaryServiceBtn['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-server" aria-hidden="true"></i>
                    WHM
                </button>
                {assign var=dmRenderedWhm value=true}
            {/if}
        {/foreach}
        {foreach $secondaryButtons as $secondaryButton}
            {if !$dmRenderedWhm && (stripos($secondaryButton['identifier'], 'whm') !== false || stripos($secondaryButton['display'], 'whm') !== false)}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-whm{if !$secondaryButton['active']} disabled{/if}"
                        data-serviceid="{$secondaryButton['serviceid']}"
                        data-identifier="{$secondaryButton['identifier']}"
                        data-active="{$secondaryButton['active']}"
                        {if !$secondaryButton['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-server" aria-hidden="true"></i>
                    WHM
                </button>
                {assign var=dmRenderedWhm value=true}
            {/if}
        {/foreach}
        {foreach $buttonData as $buttonDatum}
            {if !$dmRenderedWhm && (stripos($buttonDatum['identifier'], 'whm') !== false || stripos($buttonDatum['display'], 'whm') !== false)}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-whm{if !$buttonDatum['active']} disabled{/if}"
                        data-serviceid="{$buttonDatum['serviceid']}"
                        data-identifier="{$buttonDatum['identifier']}"
                        data-active="{$buttonDatum['active']}"
                        {if !$buttonDatum['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-server" aria-hidden="true"></i>
                    WHM
                </button>
                {assign var=dmRenderedWhm value=true}
            {/if}
        {/foreach}

        {* Render exactly one cPanel action, excluding any WHM action whose identifier also mentions cPanel. *}
        {if $primaryServiceBtn && !$dmRenderedCpanel && (stripos($primaryServiceBtn['identifier'], 'cpanel') !== false || stripos($primaryServiceBtn['display'], 'cpanel') !== false) && stripos($primaryServiceBtn['identifier'], 'whm') === false && stripos($primaryServiceBtn['display'], 'whm') === false}
            <button type="button"
                    class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-cpanel{if !$primaryServiceBtn['active']} disabled{/if}"
                    data-serviceid="{$primaryServiceBtn['serviceid']}"
                    data-identifier="{$primaryServiceBtn['identifier']}"
                    data-active="{$primaryServiceBtn['active']}"
                    {if !$primaryServiceBtn['active']}disabled="disabled"{/if}
            >
                <span class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
                <i class="fas fa-server" aria-hidden="true"></i>
                cPanel
            </button>
            {assign var=dmRenderedCpanel value=true}
        {/if}
        {foreach $accentPrimaryServiceBtns as $accentPrimaryServiceBtn}
            {if !$dmRenderedCpanel && (stripos($accentPrimaryServiceBtn['identifier'], 'cpanel') !== false || stripos($accentPrimaryServiceBtn['display'], 'cpanel') !== false) && stripos($accentPrimaryServiceBtn['identifier'], 'whm') === false && stripos($accentPrimaryServiceBtn['display'], 'whm') === false}
                <button type="button"
                        class="btn btn-primary btn-sm btn-custom-action dm-service-action-accent dm-service-action-cpanel{if !$accentPrimaryServiceBtn['active']} disabled{/if}"
                        data-serviceid="{$accentPrimaryServiceBtn['serviceid']}"
                        data-identifier="{$accentPrimaryServiceBtn['identifier']}"
                        data-active="{$accentPrimaryServiceBtn['active']}"
                        {if !$accentPrimaryServiceBtn['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-server" aria-hidden="true"></i>
                    cPanel
                </button>
                {assign var=dmRenderedCpanel value=true}
            {/if}
        {/foreach}
        {foreach $secondaryButtons as $secondaryButton}
            {if !$dmRenderedCpanel && (stripos($secondaryButton['identifier'], 'cpanel') !== false || stripos($secondaryButton['display'], 'cpanel') !== false) && stripos($secondaryButton['identifier'], 'whm') === false && stripos($secondaryButton['display'], 'whm') === false}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-cpanel{if !$secondaryButton['active']} disabled{/if}"
                        data-serviceid="{$secondaryButton['serviceid']}"
                        data-identifier="{$secondaryButton['identifier']}"
                        data-active="{$secondaryButton['active']}"
                        {if !$secondaryButton['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-server" aria-hidden="true"></i>
                    cPanel
                </button>
                {assign var=dmRenderedCpanel value=true}
            {/if}
        {/foreach}
        {foreach $buttonData as $buttonDatum}
            {if !$dmRenderedCpanel && (stripos($buttonDatum['identifier'], 'cpanel') !== false || stripos($buttonDatum['display'], 'cpanel') !== false) && stripos($buttonDatum['identifier'], 'whm') === false && stripos($buttonDatum['display'], 'whm') === false}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-cpanel{if !$buttonDatum['active']} disabled{/if}"
                        data-serviceid="{$buttonDatum['serviceid']}"
                        data-identifier="{$buttonDatum['identifier']}"
                        data-active="{$buttonDatum['active']}"
                        {if !$buttonDatum['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-server" aria-hidden="true"></i>
                    cPanel
                </button>
                {assign var=dmRenderedCpanel value=true}
            {/if}
        {/foreach}

        {* Render exactly one Webmail action, using the first matching WHMCS action data found. *}
        {if $primaryServiceBtn && !$dmRenderedWebmail && (stripos($primaryServiceBtn['identifier'], 'webmail') !== false || stripos($primaryServiceBtn['display'], 'webmail') !== false)}
            <button type="button"
                    class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-webmail{if !$primaryServiceBtn['active']} disabled{/if}"
                    data-serviceid="{$primaryServiceBtn['serviceid']}"
                    data-identifier="{$primaryServiceBtn['identifier']}"
                    data-active="{$primaryServiceBtn['active']}"
                    {if !$primaryServiceBtn['active']}disabled="disabled"{/if}
            >
                <span class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
                <i class="fas fa-envelope" aria-hidden="true"></i>
                Webmail
            </button>
            {assign var=dmRenderedWebmail value=true}
        {/if}
        {foreach $accentPrimaryServiceBtns as $accentPrimaryServiceBtn}
            {if !$dmRenderedWebmail && (stripos($accentPrimaryServiceBtn['identifier'], 'webmail') !== false || stripos($accentPrimaryServiceBtn['display'], 'webmail') !== false)}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-webmail{if !$accentPrimaryServiceBtn['active']} disabled{/if}"
                        data-serviceid="{$accentPrimaryServiceBtn['serviceid']}"
                        data-identifier="{$accentPrimaryServiceBtn['identifier']}"
                        data-active="{$accentPrimaryServiceBtn['active']}"
                        {if !$accentPrimaryServiceBtn['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    Webmail
                </button>
                {assign var=dmRenderedWebmail value=true}
            {/if}
        {/foreach}
        {foreach $secondaryButtons as $secondaryButton}
            {if !$dmRenderedWebmail && (stripos($secondaryButton['identifier'], 'webmail') !== false || stripos($secondaryButton['display'], 'webmail') !== false)}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-webmail{if !$secondaryButton['active']} disabled{/if}"
                        data-serviceid="{$secondaryButton['serviceid']}"
                        data-identifier="{$secondaryButton['identifier']}"
                        data-active="{$secondaryButton['active']}"
                        {if !$secondaryButton['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    Webmail
                </button>
                {assign var=dmRenderedWebmail value=true}
            {/if}
        {/foreach}
        {foreach $buttonData as $buttonDatum}
            {if !$dmRenderedWebmail && (stripos($buttonDatum['identifier'], 'webmail') !== false || stripos($buttonDatum['display'], 'webmail') !== false)}
                <button type="button"
                        class="btn btn-default btn-sm btn-custom-action dm-service-action-btn dm-service-action-webmail{if !$buttonDatum['active']} disabled{/if}"
                        data-serviceid="{$buttonDatum['serviceid']}"
                        data-identifier="{$buttonDatum['identifier']}"
                        data-active="{$buttonDatum['active']}"
                        {if !$buttonDatum['active']}disabled="disabled"{/if}
                >
                    <span class="loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    Webmail
                </button>
                {assign var=dmRenderedWebmail value=true}
            {/if}
        {/foreach}

        <button class="btn btn-default btn-sm btn-view-details">
            <i aria-hidden="true" class="far fa-info-circle" title="{lang key="clientareaviewdetails"}"></i>
            <span class="sr-only">{lang key="clientareaviewdetails"}</span>
            <span>{lang key="clientareaviewdetails"}</span>
        </button>
    </div>
</div>

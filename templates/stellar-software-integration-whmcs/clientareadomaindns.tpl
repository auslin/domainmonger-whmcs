{*
    DomainMonger safety wrapper for WHMCS native Domain DNS page.

    WHMCS loads this exact filename automatically:
        clientareadomaindns.tpl

    Keep the real editable/customized template in:
        clientareadomaindns_mod.tpl

    This reduces the risk of losing the custom working copy during future
    template refreshes or manual file replacements. If this wrapper is ever
    overwritten, restore this wrapper and the _mod file can remain unchanged.
*}
{include file="$template/clientareadomaindns_mod.tpl"}

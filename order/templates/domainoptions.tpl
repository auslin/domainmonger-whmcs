{if $status eq "available"}

<div class="domainavailable">{$LANG.cartdomainavailableoptions}</div>

<br />

<table cellspacing="0" cellpadding="0" align="center">{if $regenabled == "on"}
{if $regoptionscount}<tr><td><input type="radio" name="domainoption" value="register" id="domopreg" onclick="loadproductconfig('')"></td><td><label for="domopreg">{$LANG.cartdomainavailableregister}</label> <select name="regperiod" onchange="selectbox('domopreg');recalctotals();loadproductconfig()">{foreach key=period item=regoption from=$regoptions}{if $regoption.register}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/if}{/foreach}</select></td></tr>{/if}{/if}{if $owndomainenabled == "on"}
<tr><td><input type="radio" name="domainoption" value="owndomain" id="domopown" onclick="loadproductconfig('')"></td><td><label for="domopown">{$LANG.cartdomainavailablemanual}</label></td></tr>{/if}
</table>

{elseif $status eq "unavailable"}

<div class="domainunavailable">{$LANG.cartdomainunavailableoptions}</div>

<br />

<table cellspacing="0" cellpadding="0" align="center">{if $transferenabled == "on"}
{if $transferoptionscount}<tr><td><input type="radio" name="domainoption" value="transfer" id="domoptrans" onclick="loadproductconfig('')"></td><td><label for="domoptrans">{$LANG.cartdomainunavailabletransfer}</label> <select name="regperiod" onchange="selectbox('domoptrans');recalctotals();loadproductconfig()">{foreach key=period item=regoption from=$transferoptions}{if $regoption.transfer}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.transfer}</option>{/if}{/foreach}</select></td></tr>{/if}{/if}{if $owndomainenabled == "on"}
<tr><td><input type="radio" name="domainoption" value="owndomain" id="domopown" onclick="loadproductconfig('')"></td><td><label for="domopown">{$LANG.cartdomainunavailablemanual}</label></td></tr>{/if}
</table>

{else}

<div class="domaininvalid">{$LANG.cartdomaininvalid}</div><br />

{/if}
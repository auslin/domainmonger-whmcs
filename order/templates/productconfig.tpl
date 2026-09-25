<p>{if $pricing.type eq "free"}
<input type="hidden" name="billingcycle" value="free" />
{elseif $pricing.type eq "onetime"}
<input type="hidden" name="billingcycle" value="onetime" />
{else}
<h2>{$LANG.orderbillingcycle}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>
<select name="billingcycle" onchange="cyclechange()">
{if $pricing.monthly}<option value="monthly"{if $billingcycle eq "monthly"} selected="selected"{/if}>{$pricing.monthly}</option>{/if}
{if $pricing.quarterly}<option value="quarterly"{if $billingcycle eq "quarterly"} selected="selected"{/if}>{$pricing.quarterly}</option>{/if}
{if $pricing.semiannually}<option value="semiannually"{if $billingcycle eq "semiannually"} selected="selected"{/if}>{$pricing.semiannually}</option>{/if}
{if $pricing.annually}<option value="annually"{if $billingcycle eq "annually"} selected="selected"{/if}>{$pricing.annually}</option>{/if}
{if $pricing.biennially}<option value="biennially"{if $billingcycle eq "biennially"} selected="selected"{/if}>{$pricing.biennially}</option>{/if}
{if $pricing.triennially}<option value="triennially"{if $billingcycle eq "triennially"} selected="selected"{/if}>{$pricing.triennially}</option>{/if}
</select>
</td></tr>
</table>
{/if}</p>

{if $productinfo.type eq "server"}
<h2>{$LANG.cartconfigserver}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td width="100">{$LANG.serverhostname}:</td><td><input type="text" name="hostname" size="15" value="{$server.hostname}" /> eg. server1(.yourdomain.com)</td></tr>
<tr class="rowcolor2"><td>{$LANG.serverns1prefix}:</td><td><input type="text" name="ns1prefix" size="10" value="{$server.ns1prefix}" /> eg. ns1(.yourdomain.com)</td></tr>
<tr class="rowcolor1"><td>{$LANG.serverns2prefix}:</td><td><input type="text" name="ns2prefix" size="10" value="{$server.ns2prefix}" /> eg. ns2(.yourdomain.com)</td></tr>
<tr class="rowcolor2"><td>{$LANG.serverrootpw}:</td><td><input type="password" name="rootpw" size="20" value="{$server.rootpw}" /></td></tr>
</table>
<br />
{/if}

{if $configoptions}
<h2>{$LANG.cartconfigurationoptions}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
{foreach key=num item=configoption from=$configoptions}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td width="100">{$configoption.optionname}:</td><td>
{if $configoption.optiontype eq 1}
<select name="configoption[{$configoption.id}]" onchange="recalctotals()">
{foreach key=num2 item=options from=$configoption.options}
<option value="{$options.id}"{if $configoption.selectedvalue eq $options.id} selected="selected"{/if}>{$options.nameonly}</option>
{/foreach}
</select>
{elseif $configoption.optiontype eq 2}
{foreach key=num2 item=options from=$configoption.options}
<input type="radio" name="configoption[{$configoption.id}]" value="{$options.id}"{if $configoption.selectedvalue eq $options.id} checked="checked"{/if} onclick="recalctotals()"> {$options.name}<br />
{/foreach}
{elseif $configoption.optiontype eq 3}
<input type="checkbox" name="configoption[{$configoption.id}]" value="1"{if $configoption.selectedqty} checked{/if} onclick="recalctotals()"> {$configoption.options.0.name}
{elseif $configoption.optiontype eq 4}
<input type="text" name="configoption[{$configoption.id}]" value="{$configoption.selectedqty}" size="5" onchange="recalctotals()"> x {$configoption.options.0.name}
{/if}
</td></tr>
{/foreach}
</table>
<br />
{/if}

{if $addons}
<h2>{$LANG.cartaddons}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
{foreach key=num item=addon from=$addons}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td width="25"><input type="checkbox" name="addon[{$addon.id}]" id="a{$addon.id}"{if $addon.status} checked{/if} onclick="recalctotals()" /></td><td><label for="a{$addon.id}"><strong>{$addon.name}</strong> - {$addon.description} ({$addon.pricing})</label></td></tr>
{/foreach}
</table>
<br />
{/if}

{if $customfields}
<h2>{$LANG.orderadditionalrequiredinfo}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
{foreach key=num item=customfield from=$customfields}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td width="100">{$customfield.name}:</td><td>{$customfield.input} {$customfield.description}</td></tr>
{/foreach}
</table>
<br />
{/if}

{if $domaindnsmanagement || $domainemailforwarding || $domainidprotection || $domainadditionalfields}
<h2>{$LANG.cartdomainsconfig}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
{if $domaineppcodereq}<tr class="{cycle values="rowcolor1,rowcolor2"}"><td>{$LANG.domaineppcode} <input type="text" name="eppcode" size="20" value="{$domaineppcode}" /> {$LANG.domaineppcodedesc}</td></tr>{/if}
{if $domaindnsmanagement}<tr class="{cycle values="rowcolor1,rowcolor2"}"><td><input type="checkbox" name="dnsmanagement"{if $domaindnsmanagementselected} checked{/if} onclick="recalctotals()" /> {$LANG.domaindnsmanagement} ({$domaindnsmanagement})</td></tr>{/if}
{if $domainemailforwarding}<tr class="{cycle values="rowcolor1,rowcolor2"}"><td><input type="checkbox" name="emailforwarding"{if $domainemailforwardingselected} checked{/if} onclick="recalctotals()" /> {$LANG.domainemailforwarding} ({$domainemailforwarding})</td></tr>{/if}
{if $domainidprotection}<tr class="{cycle values="rowcolor1,rowcolor2"}"><td><input type="checkbox" name="idprotection"{if $domainidprotectionselected} checked{/if} onclick="recalctotals()" /> {$LANG.domainidprotection} ({$domainidprotection})</td></tr>{/if}
{foreach from=$domainadditionalfields key=domainfieldname item=domainfield}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td>{$domainfieldname}: {$domainfield}</td></tr>
{/foreach}
</table>
{/if}

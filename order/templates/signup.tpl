{if !$loggedin}

<h2>{$LANG.signup}</h2>

<p><input type="radio" name="signuptype" value="new" onclick="signupnew()" checked /> {$LANG.cartnewcustomer} <input type="radio" name="signuptype" value="existing" onclick="signupexisting()" /> {$LANG.cartexistingcustomer}</p>

<div id="newsignup">
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="topborder"><td colspan="2"></td></tr>
<tr class="rowcolor1"><td width="125">{$LANG.clientareafirstname}:</td><td><input type="text" name="firstname" id="firstname" size="20" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.clientarealastname}:</td><td><input type="text" name="lastname" id="lastname" size="20" /></td></tr>
<tr class="rowcolor1"><td>{$LANG.clientareacompanyname}:</td><td><input type="text" name="companyname" size="20" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.clientareaemail}:</td><td><input type="text" name="email" id="email" size="35" /></td></tr>
<tr class="rowcolor1"><td>{$LANG.clientareaaddress1}:</td><td><input type="text" name="address1" id="address1" size="25" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.clientareaaddress2}:</td><td><input type="text" name="address2" size="25" /></td></tr>
<tr class="rowcolor1"><td>{$LANG.clientareacity}:</td><td><input type="text" name="city" id="city" size="25" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.clientareastate}:</td><td><input type="text" name="state" id="state" size="25" onchange="recalctotals()" /></td></tr>
<tr class="rowcolor1"><td>{$LANG.clientareapostcode}:</td><td><input type="text" name="postcode" id="postcode" size="20" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.clientareacountry}:</td><td>{$countrydropdown|replace:'<select':'<select onchange="recalctotals()"'}</td></tr>
<tr class="rowcolor1"><td>{$LANG.clientareaphonenumber}:</td><td><input type="text" name="phonenumber" id="phonenumber" size="20" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.loginpassword}:</td><td><input type="password" name="password1" id="password1" size="20" /></td></tr>
<tr class="rowcolor1"><td>{$LANG.clientareaconfirmpassword}:</td><td><input type="password" name="password2" id="password2" size="20" /></td></tr>
{foreach key=num item=customfield from=$clientcustomfields}
<tr class="{cycle values="rowcolor2,rowcolor1"}"><td width="125">{$customfield.name}:</td><td>{$customfield.input} {$customfield.description}</td></tr>
{/foreach}
{if $securityqs}
<tr class="{cycle values="rowcolor2,rowcolor1"}"><td width="125">{$LANG.clientareasecurityquestion}:</td><td><select name="securityqid">
{foreach from=$securityqs item=question}
	<option value={$question.id}>{$question.question}</option>
{/foreach}
</select></td></tr>
<tr><td class="{cycle values="rowcolor2,rowcolor1"}">{$LANG.clientareasecurityanswer}:</td><td><input type="password" name="securityqans" size="20" /></td></tr>
{/if}
</table>
</div>

<div id="existinglogin" style="display:none;">
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="topborder"><td colspan="2"></td></tr>
<tr class="rowcolor1"><td width="125">{$LANG.loginemail}:</td><td><input type="text" name="username" id="loginemail" size="30" /></td></tr>
<tr class="rowcolor2"><td>{$LANG.loginpassword}:</td><td><input type="password" name="password" id="loginpw" size="20" /></td></tr>
</table>
</div>

<br />

{/if}

<h2>{$LANG.orderpaymentmethod}</h2>

<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>{foreach key=num item=gateway from=$gateways}<input type="radio" name="paymentmethod" value="{$gateway.sysname}" id="gateway{$num}"{if $selectedgateway eq $gateway.sysname} checked{/if} /><label for="gateway{$num}">{$gateway.name}</label> {/foreach}</td></tr>
</table>

{if $accepttos}<p align="center"><input type="checkbox" name="accepttos" id="accepttos" onclick="toggleaccepttos()" /> <label for="accepttos">{$LANG.ordertostickconfirm} <a href="{$tosurl}" target="_blank">{$LANG.ordertos}</a></label><p>{/if}

<p align="center"><input type="submit" value="{$LANG.checkout}" name="checkout" id="checkoutbtn"{if $accepttos} disabled="true"{/if} title="{$LANG.ordererrortermsofservice}" onclick="checkoutvalidate();return false" /></p>

<div id="checkoutloading" align="center" style="display:none;">Processing Your Order... Please Wait...</div>

<p><img align="left" src="images/padlock.gif" border="0" vspace="5" alt="Secure Transaction" style="padding-right: 10px;" /> {$LANG.ordersecure} (<strong>{$ipaddress}</strong>) {$LANG.ordersecure2}</p>
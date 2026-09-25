{include file="$template/header.tpl"}

<script type="text/javascript" src="includes/jscript/jquery.js"></script>
<script type="text/javascript" src="order/templates/jqueryfloat.js"></script>
<script type="text/javascript" src="order/templates/jqueryvalidate.js"></script>
<script type="text/javascript" src="order/templates/ordering.js"></script>
<link rel="stylesheet" href="order/templates/style.css" type="text/css" />

{literal}
<script type="text/javascript">
jQuery(document).ready(function(){
        jQuery("#orderfrm").validate({
      rules: {
        firstname: "required",
        lastname: "required",
        email: {
          required: true,
          email: true
        },
        address1: "required",
        city: "required",
        state: "required",
        postcode: "required",
        phonenumber: {
          required: true,
          phonenumber: true
        },
        password1: "required",
        password2: {
          equalTo: "#password1"
        }
      }
    });
    jQuery("form").bind("keypress", function(e) { if (e.keyCode == 13) { return false; } });
    jQuery.post("order/index.php", { a: "getloading" },
    function(data){
        loadinghtml = data;
    });
    jQuery("#cartsummary").makeFloat({x:"current",y:"current"});
    {/literal}{if $pid}
    jQuery("#gid{$gid}").attr('checked', true);
    loadproducts('{$gid}','{$pid}');
    {elseif $gid}
    jQuery("#gid{$gid}").attr('checked', true);
    loadproducts('{$gid}');
    {/if}{literal}
});
</script>
{/literal}

<table width="100%" align="center" cellpadding="0" cellspacing="0">
<tr><td height="400" valign="top">

<div id="checkouterrormsg" class="errormsg" style="text-align:left;{if $outofstock}display:block;{/if}">
{if $outofstock}{$LANG.outofstockdescription}{/if}
</div>

<form method="post" action="cart.php?a=checkout" name="orderfrm" id="orderfrm">
<input type="hidden" name="checkout" value="true" />

{if $skip}<div style="display:none;">{/if}

<h2>{$LANG.cartchooseproduct}</h2>

<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>{foreach from=$groups item=group}<input type="radio" name="gid" value="{$group.gid}" id="gid{$group.gid}" onclick="loadproducts('{$group.gid}')" /> <label for="gid{$group.gid}">{$group.name}</label> {/foreach}</td></tr>
</table>

{if $hiddenproduct}<input type="hidden" name="pid" value="{$pid}" />{/if}

<div id="productslist" style="display:none;"></div>

{if $skip}</div>{/if}

<div id="productconfig1" style="display:none;"></div>

<div id="productconfig2" style="display:none;"></div>

</form>

<noscript>
<br /><br />
<p align="center"><strong>This order form requires JavaScript to be enabled.<br />
Please wait while we redirect you to our non-javascript order process...</strong><br /><br />
<a href="cart.php">Click here to continue...</a></p>
<meta http-equiv="refresh" content="5;url=cart.php" />
</noscript>

</td><td width="210" valign="top" align="right">

<div id="cartsummary">

<div class="heading"><div class="text">{$LANG.ordersummary}</div><div id="loader"><img src="order/images/summaryloading.gif" border="0" /></div><div style="clear: both;"></div></div>

<br />

<div align="center">{$LANG.ordersummarybegin}</div>

<br /><br />

</div>

</td></tr></table>

{include file="$template/footer.tpl"}
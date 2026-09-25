<h2>{$LANG.domainname}</h2>

<p>{$LANG.cartenterdomain}</p>

<table align="center" cellspacing="0" cellpadding="0"><tr><td>www. <input type="text" name="domain" id="domain" size="40" onkeypress="$('#domainloader').show();" /></td><td>&nbsp;</td><td width="20"><div id="domainloader" style="display:none"><img src="order/images/summaryloading.gif" border="0" /></td></tr></table>

<br />

{literal}
<script language="javascript">
var currentcheckcontent,lastcheckcontent;
function checkdomainentry() {
    currentcheckcontent = jQuery("#domain").val();
    if (currentcheckcontent!=lastcheckcontent && currentcheckcontent!="") {
        validatedomain();
        jQuery('#domainloader').hide();
        lastcheckcontent = currentcheckcontent;
	}
    setTimeout('checkdomainentry();', 3000);
}
checkdomainentry();
</script>
{/literal}

<div id="domainresults" style="display:none;"></div>
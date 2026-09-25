{php}

$sql='SELECT value FROM `tblconfiguration` WHERE `setting` = "SystemSSLURL"';
$result=mysql_query($sql);	
$row = mysql_fetch_array($result);
if(count($row)<1 )
{
$sql='SELECT value FROM `tblconfiguration` WHERE `setting` = "SystemURL"';
$result=mysql_query($sql);	
$row = mysql_fetch_array($result);
}
$whmcsurlfull=$row['value'];
{/php}
{include file="$template/header.tpl"}

<div class="contentbox"><a href="{php}echo $whmcsurlfull;{/php}clientarea.php?action=details">{$LANG.clientareanavdetails}</a> |  <a href="{php}echo $whmcsurlfull;{/php}clientarea.php?action=contacts">{$LANG.clientareanavcontacts}</a> | <a href="{php}echo $whmcsurlfull;{/php}clientarea.php?action=addcontact">{$LANG.clientareanavaddcontact}</a> | <a href="{php}echo $whmcsurlfull;{/php}clientarea.php?action=creditcard">{$LANG.clientareanavchangecc}</a> | <a href="{php}echo $whmcsurlfull;{/php}clientarea.php?action=changepw">{$LANG.clientareanavchangepw}</a> | <a href="{php}echo $whmcsurlfull;{/php}clientarea.php?action=changesq">{$LANG.clientareanavsecurityquestions}</a>| <a href="{php}echo $whmcsurlfull;{/php}username/">Username</a> </div>
{php}if($_SESSION['uid']!=null){ 

echo '<center>';
echo $_SESSION['usernameerror'];
echo '</center>';


{/php}
<form method="post" action="">  
  <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" border="0" align="center" class="frame">
    <tr>
      <td><table border="0" align="center" cellpadding="10" cellspacing="0">
          <tr>
            <td width="150" align="right" class="fieldarea">USERNAME:</td>
            <td><input type="text" name="wuser" size="50" value="{php} echo $_SESSION['wuser'];{/php}"></td>
          </tr>
         
          <tr>
            <td width="150" align="right" class="fieldarea">&nbsp;</td>
            <td> <input type="submit" value="Save" name="submit" /></td>
          </tr>
        </table></td>
    </tr>
  </table><br/>
  
  </form>
{php} } {/php}
{include file="$template/footer.tpl"}
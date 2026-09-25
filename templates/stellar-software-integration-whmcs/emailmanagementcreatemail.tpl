<style>
.dm-rc-email-page .table {
    width: 100%;
    max-width: 100%;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.dm-rc-email-page .table td,
.dm-rc-email-page .table th {
    vertical-align: top;
}
.dm-rc-email-page .btn {
    white-space: nowrap;
}
.dm-rc-email-page .label,
.dm-rc-email-page .badge {
    display: inline-block;
    line-height: 1.25;
}
.dm-rc-email-page input[type="text"],
.dm-rc-email-page input[type="email"],
.dm-rc-email-page input[type="password"],
.dm-rc-email-page select,
.dm-rc-email-page textarea {
    max-width: 100%;
}
@media (max-width: 767px) {
    .dm-rc-email-page {
        overflow-x: auto;
    }
    .dm-rc-email-page .table {
        min-width: 640px;
    }
}
</style>
<div class="dm-rc-email-page">
{literal}
	<script language="javascript" type="text/javascript">function showlangtable(){jQuery("#tblfrm").slideToggle();}</script>
{/literal}

{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcmail_createemaildesc}

{if $mailcreateaccountsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$mailcreateaccountsuccess}
    </ul>
</div>

<br/>
<table class="table table-bordered table-hover">
	<tr>
		<td style="vertical-align:top">
			Email Account
		</td>
		<td style="vertical-align:top">
			{$your_newemail}
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.clientareapassword}</strong>
		</td>
		<td>
			<strong>{$your_password}</strong>
		</td>
	</tr>
	<tr>
		<td>
			<strong>POP Server</strong>
		</td>
		<td>
			{$popserver}
		</td>
	</tr>
	<tr>
		<td>
			<strong>IMAP Server</strong>
		</td>
		<td>
			{$imapserver}
		</td>
	</tr>
	<tr>
		<td>
			<strong>SMTP Server</strong>
		</td>
		<td>
			{$smtpserver}
		</td>
	</tr>
	<tr>
		<td>
			<strong>Webmail</strong>
		</td>
		<td>
			<a href="{$webmailurl}" target="_blank">{$webmailurl}</a>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<form method="post" action="emailmanagement.php?action=modifyaccount">
				<input type="hidden" name="page" value="{$smarty.post.page}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input name="freemailhosting" value="{$freemailhosting}" type="hidden"/>
				<input type="hidden" name="emailaddress" value="{$your_newemail}"/>
				<input type="hidden" name="mailtype" value="mailaccount"/>
				<p align="center"><input type="submit" value="Save Changes" class="btn btn-primary"/></p>
			</form>
		</td>
	</tr>
</table>
{/if}

{if $mailcreateaccounterror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$mailcreateaccounterror}
    </ul>
</div>
{/if}

<br />
<form method="post" action="emailmanagement.php?action=createaccount">
	<input type="hidden" name="page" value="{$smarty.post.page}"/>
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input name="freemailhosting" value="{$freemailhosting}" type="hidden"/>
	<input type="hidden" name="createaccount" value="true"/>
	<table class="table table-bordered table-hover">
		<tr>
			<td style="vertical-align:top">
				<strong>{$LANG.clientareafirstname}</strong>
			</td>
			<td style="vertical-align:top">
				<input class="form-control" name="firstname" type="text" value="{if $mailcreateaccounterror}{$smarty.post.firstname}{/if}" size="20"/>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.clientarealastname}</strong>
			</td>
			<td>
				<input class="form-control" name="lastname" type="text" value="{if $mailcreateaccounterror}{$smarty.post.lastname}{/if}" size="20"/>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.clientareaemail}</strong>
			</td>
			<td>
				<div class="input-group">
					<input class="form-control" name="email" type="text" value="{if $mailcreateaccounterror}{$smarty.post.email}{/if}" size="20"/>
					<span class="input-group-addon" id="basic-addon2">@{$domain}</span>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_bulkcolpasswd}</strong>
			</td>
			<td>
				<input class="form-control" name="password" type="text" value="{if $mailcreateaccounterror}{$smarty.post.password}{/if}" size="20"/><br />
				{$LANG.rcmail_passwordtitledesc}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_alternatemailaddress}</strong>
			</td>
			<td>
				<input class="form-control" name="notifymail" type="text" value="{if $mailcreateaccounterror}{$smarty.post.notifymail}{/if}" size="40"/><br/><strong>{$LANG.rcmail_importantnotetitle}</strong>: {$LANG.rcmail_importantnotedesc}
			</td>
		</tr>
		<tr>
			<td>
				<strong>Language</strong>
			</td>
			<td>
				<select class="form-control" name="language-code">
				<option value="en">{$LANG.rcmail_langenglish}</option>
				<option value="es">{$LANG.rcmail_langspanish}</option>
				<option value="es-MX">{$LANG.rcmail_langspanishla}</option>
				<option value="pt-PT">{$LANG.rcmail_langportuguese}</option>
				<option value="de">{$LANG.rcmail_langgerman}</option>
				<option value="tr">{$LANG.rcmail_langturkish}</option>
				<option value="ru">{$LANG.rcmail_langrussian}</option>	
				<option value="zh-CN">{$LANG.rcmail_langchinese}</option>	
				</select>
			</td>
		</tr>
		<tr>
		<td>
			<strong>{$LANG.clientareacountry}</strong>
		</td>
		<td>
			{$dropdowncountries}
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<p align="center"><input type="submit" value="Create Email Account" class="btn btn-success"/></p>
		</td>
	</tr>
</table>
</form>

<br />
{if $freemailhosting neq "true"}
<a name="bulk"></a>
<div class="page-header">
    <h2>{$LANG.rcmail_bulkuseraddtitle}</h2>
</div>

{if $bulkerrormessage}
<br />
<div class="alert alert-danger">
	<p>{$LANG.clientareaerrors}</p>
    <ul>
        {$bulkerrormessage}
    </ul>
</div>
{/if}

<ol>
	<li>
		<p>{$LANG.rcmail_bulkdesc1}:</p><br />
		<p>{$LANG.rcmail_bulkdesc2}</p>
		<br />

		<div id="tblfrm" style="display:none;">
			<table class="table table-bordered table-hover">
				<tr>
					<td>Language Name</td>
					<td>Language Code</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langchinese}</td>
					<td>zh-CN</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langenglish}</td>
					<td>en</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langgerman}</td>
					<td>de</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langportuguese}</td>
					<td>pt-PT</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langrussian}</td>
					<td>ru</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langspanishla}</td>
					<td>es-MX</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langspanish}</td>
					<td>es</td>
				</tr>
				<tr>
					<td>{$LANG.rcmail_langturkish}</td>
					<td>tr</td>
				</tr>
			</table>
		</div>
		
		<p>{$LANG.rcmail_bulkdesc3}:</p><br />
		<p><strong>{$LANG.rcmail_bulkexptitle1}</strong>: {$LANG.rcmail_bulkexpdesc1}<br />
	    <strong>{$LANG.rcmail_bulkexptitle2}</strong>: {$LANG.rcmail_bulkexpdesc2}</p>
		<div style="vertical-align:middle;padding-top:10px;"> <img style="padding-right:10px;" src="templates/{$template}/img/csv.gif" border="0" /><a href="templates/{$template}/downloadexample.csv">{$LANG.rcmail_bulkexpddownload}</a></div>
	</li>
	<br />
	<li>
		<strong>{$LANG.rcmail_bulkcsvupload}</strong>
		<form method="post" enctype="multipart/form-data" action="emailmanagement.php?action=createaccount#bulk">
		<input type="hidden" name="bulkcreate" value="true" />
		<input type="hidden" name="page" value="{$smarty.post.page}"/>
		<input type="hidden" name="domainid" value="{$domainid}"/>
		<input type="hidden" name="domain" value="{$domain}"/>
		<input name="freemailhosting" value="{$freemailhosting}" type="hidden"/>
		<input type="file" name="csv" value="" class="form-control" /><br />
		<input type="submit" name="submit" value="Upload CSV" class="btn btn-success" />
		</form>
	</li>
</ol>

{if $bulkmailcreateaccountsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$bulkmailcreateaccountsuccess}
    </ul>
</div>

<form method="post" action="emailmanagement.php?action=createaccount">
	<input type="hidden" name="download" value="true"/>
	<input type="hidden" name="page" value="{$smarty.post.page}"/>
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input name="freemailhosting" value="{$freemailhosting}" type="hidden"/>
	<p><input type="submit" value="Download CSV Template" class="btn btn-success" /></p>
	
	<table class="table table-bordered table-hover">
		<tr>
			<td>{$LANG.rcmail_bulkcolresult}</td>
			<td>{$LANG.rcmail_bulkcolemail}</td>
			<td>{$LANG.rcmail_bulkcolfirstname}</td>
			<td>{$LANG.rcmail_bulkcollastname}</td>
			<td>{$LANG.rcmail_bulkcolpasswd}</td>
			<td>{$LANG.rcmail_bulkcolcountry}</td>
			<td>{$LANG.rcmail_bulkcollang}</td>
			<td>{$LANG.rcmail_bulkcolaltmail}</td>
		</tr>
		{foreach key=num item=service from=$csvresultdata}
		<tr>
		<td>
			<img src="templates/{$template}/img/{if $service.key8 eq "ok"}ok.png{else}ko.png{/if}" alt="{$service.key8}" border="0">
			<input type="hidden" name="result[]" value="{$service.key8},{$service.key1},{$service.key2},{$service.key3},{$service.key4},{$service.key5},{$service.key6},{$service.key7}"/>
		</td>
		<td>{$service.key1}</td>
		<td>{$service.key2}</td>
		<td>{$service.key3}</td>
		<td>{$service.key4}</td>
		<td>{$service.key5}</td>
		<td>{$service.key6}</td>
		<td>{$service.key7}</td>
		</tr>
		{/foreach}
	</table>
</form>
{/if}
{/if}
</div>

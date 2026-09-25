{if $irtp_success}
	<div class="alert alert-success">
		<p>{$LANG.moduleactionsuccess}</p>
		<ul>
			<p>{$LANG.lcdrm_irtpsuccess} {$LANG.lcdrm_toword} <strong>{$current_regc_email}</strong> {$LANG.lcdrm_andword} <strong>{$new_regc_email}</strong></p>
		</ul>
	</div>
{/if}

{if $irtp_error}
	<div class="alert alert-danger">
		<p>{$LANG.moduleactionfailed}</p>
		<ul>
			<p>{$irtp_error}</p>
		</ul>
	</div>
{/if}

<div class="alert alert-warning">
	<p><strong>{$LANG.lcdrm_irtppendingtitle}</strong></p><br />
	<ul>
		<table class="table">
			<tr>
				<th>{$LANG.lcdrm_irtpregistranttitle}</th>
				<th>{$LANG.lcdrm_irtpstatustitle}</th>
			</tr>
			<tr>
				<td>{$LANG.lcdrm_currentregistranttitle} ({$current_regc_email})</td>
				<td>{$current_regc_status}</td>
			</tr>
			<tr>
				<td>{$LANG.lcdrm_newregistranttitle} ({$new_regc_email})</td>
				<td>{$new_regc_status}</td>
			</tr>
		</table>
		{if $noraabutton}
		<form method="post" action="clientarea.php?action=domaindetails">
			<input type="hidden" name="domain" value="{$isdomain}"/>
			<input type="hidden" name="id" value="{$isdomainid}"/>
			<input type="hidden" name="irtp" value="resend"/>
			<p><input class="btn btn-success" type="submit" value="{$LANG.lcdrm_irtpsendbutton}"/></p>
		</form>
		{/if}
	</ul>
</div>

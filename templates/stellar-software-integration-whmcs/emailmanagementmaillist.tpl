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
<script>
	jQuery(document).ready(function(){
		jQuery("div a").click(function() {
			jQuery(this).parent().children().removeClass("active");
			jQuery(this).addClass("active");
		});
	});
</script>

<script language="javascript" type="text/javascript">
	function confirmMemberDelete(){literal}{{/literal}return confirm("{$LANG.rcmail_memberdeletewarning}");{literal}}{/literal}
	function confirmModeratorDelete(){literal}{{/literal}return confirm("{$LANG.rcmail_moderatordeletewarning}");{literal}}{/literal}
</script>

{if $addsubscribersuccess}
<br />
<div class="alert alert-success">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$addsubscribersuccess}
    </ul>
</div>
{/if}

{if $addsubscribererror}
<br />
<div class="alert alert-danger">
	<p>{$LANG.clientareaerrors}</p>
    <ul>
        {$addsubscribererror}
    </ul>
</div>
{/if}

{if $delsubscribersuccess}
<br />
<div class="alert alert-success">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$delsubscribersuccess}
    </ul>
</div>
{/if}

{if $delsubscribererror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$delsubscribererror}
    </ul>
</div>
{/if}

{if $addmoderatorsuccess}
<br />
<div class="alert alert-success">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$addmoderatorsuccess}
    </ul>
</div>
{/if}

{if $addmoderatorerror}
<br />
<div class="alert alert-danger">
	<p>{$LANG.clientareaerrors}</p>
    <ul>
        {$addmoderatorerror}
    </ul>
</div>
{/if}

{if $delmoderatorsuccess}
<br />
<div class="alert alert-success">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$delmoderatorsuccess}
    </ul>
</div>
{/if}

<script language="javascript" type="text/javascript">
	{literal}
		jQuery(document).ready(function() {
			jQuery("#select-all-email").click(function() {
				var checkBoxes = jQuery("input[name=multiaction\\[\\]]");
				checkBoxes.prop("checked", !checkBoxes.prop("checked"));
			});                 
		});	
	{/literal}
</script>
<div class="tab-content margin-bottom">
	<div class="tab-pane fade in active" id="tabAddmem">
		<form method="post" action="emailmanagement.php?action=managelist#tabAddmem">
			<input type="hidden" name="domainid" value="{$domainid}"/>
			<input type="hidden" name="domain" value="{$domain}"/>
			<input type="hidden" name="addsubscriber" value="true"/>
			<input type="hidden" name="listaddress" value="{$listaddress}"/>
			<input type="hidden" name="istab" value="tabaddmembers"/>
			<table class="table table-bordered table-hover">
				<tr>
					<td colspan="2">
						<h3>{$LANG.rcmail_addmembers} {$listaddress}</h3>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Emails</strong>
					</td>
					 <td>
						{$LANG.rcmail_membersdesc} <br />
						<textarea class="form-control" name="subscribers" cols="50" rows="5">{if $addsubscribererror}{$smarty.post.subscribers}{/if}</textarea>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Note</strong>
					</td>
					<td>
						{$LANG.rcmail_maxmembersaddatonce}<br />
						{$LANG.rcmail_maxmembersdesc}
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<p align="center"><input type="submit" value="Add Member" class="btn btn-success"/></p>
					</td>
				</tr>
			</table>
		</form>
	</div>
	
	<div class="tab-pane fade" id="tabDelmem">
		<div class="input-group">
			<form method="post" action="emailmanagement.php?action=managelist#tabDelmem">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="listaddress" value="{$listaddress}"/>
				<input type="hidden" name="istab" value="tabremmembers"/>
				<span class="input-group-btn">
					<input type="text" name="q" value="{if $q}{$q}{else}{$LANG.rcmail_searchentercriteria}{/if}" class="form-control input-sm" onfocus="if(this.value=='{$LANG.rcmail_searchentercriteria}')this.value=''" />
					<button type="submit" class="btn btn-primary btn-sm">{$LANG.searchfilter}</button>
				</span>
			</form>
		</div>
		
		<br />
		<p>{$numproducts} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</p>
		<table class="table table-bordered table-hover">
			<tr>
				<td {if $orderby eq "subscriber"} class="headerSort{$sort}"{/if}><a href="emailmanagement.php?action=managelist{if $q}&q={$q}{/if}&listaddress={$listaddress}&orderby=subscriber&domainid={$domainid}&domain={$domain}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Members&nbsp;{$LANG.rcmail_singlewordof} {$listaddress}</a></td>
				<td><a href="#" onclick="return false">Actions</a></td>
				<td><input id="select-all-email" type="checkbox" /></td>
			</tr>
			{foreach key=num item=service from=$searchdataKey}
			<tr>
				<td>
					{$service.subscriber}
				</td>
				<td>
					<form method="post" action="emailmanagement.php?action=managelist#tabDelmem" onclick="return confirmMemberDelete();">
						<input type="hidden" name="deletesubscriber" value="true"/>
						<input type="hidden" name="subscribers" value="{$service.subscriber}"/>
						<input type="hidden" name="domainid" value="{$domainid}"/>
						<input type="hidden" name="domain" value="{$domain}"/>
						<input type="hidden" name="listaddress" value="{$listaddress}"/>
						<input type="hidden" name="page" value="{$pagenumber}"/>
						<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
						<input type="hidden" name="q" value="{$q}"/>
						<input type="hidden" name="istab" value="tabremmembers"/>
						<input type="submit" value="Delete" class="btn btn-danger btn-sm"/>
					</form>
				</td>
				<td><input name="multiaction[]" type="checkbox" value="{$service.subscriber}" class="checkbox"/></td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="3">
					{$LANG.norecordsfound}			
				</td>
			</tr>
			{/foreach}
			<tr>
				<td colspan="3">
					{if $searchdataKey}
					<div style="float:right;">
						<form id="multiaction" method="post" action="emailmanagement.php?action=managelist#tabDelmem">
							<input type="hidden" name="domain" value="{$domain}"/>
							<input type="hidden" name="domainid" value="{$domainid}"/>
							<input type="hidden" name="listaddress" value="{$listaddress}"/>
							<input type="hidden" name="page" value="{$pagenumber}"/>
							<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
							<input type="hidden" name="q" value="{$q}"/>
							<input type="hidden" name="istab" value="tabremmembers"/>
							<input type="submit" name="membersdelete" value="Delete Selected" class="btn btn-danger btn-sm" onclick="return confirmMemberDelete();" />
						</form>
					</div>
					{/if}
					{if $q neq ""}
					<div style="float:left;">
						<form method="post" action="emailmanagement.php?action=managelist#tabDelmem">
							<input type="hidden" name="q" value=""/> 
							<input type="hidden" name="domainid" value="{$domainid}"/>
							<input type="hidden" name="domain" value="{$domain}"/>
							<input type="hidden" name="listaddress" value="{$listaddress}"/>
							<p align="center"><input type="submit" value="Clear Search" class="btn btn-sm"/></p>
						</form>
					</div>
					{/if}			
				</td>
			</tr>
		</table>
		<br />
		<div class="pull-right">
			<form action="emailmanagement.php#tabDelmem">
				<input type="hidden" name="action" value="managelist" />
				<select class="form-control" name="itemlimit" id="itemlimit" onchange="this.form.submit();">
					<option>{$LANG.resultsperpage}</option>
					<option value="10"{if $itemlimit==10} selected{/if}>10</option>
					<option value="25"{if $itemlimit==25} selected{/if}>25</option>
					<option value="50"{if $itemlimit==50} selected{/if}>50</option>
					<option value="100"{if $itemlimit==100} selected{/if}>100</option>
					<option value="all"{if $itemlimit > 100} selected{/if}>{$LANG.clientareaunlimited}</option>
				</select>
				<input type="hidden" name="domainid" value="{$domainid}" />
				<input type="hidden" name="domain" value="{$domain}" />
				<input type="hidden" name="listaddress" value="{$listaddress}" />
				<input type="hidden" name="istab" value="tabremmembers"/>
			</form>
		</div>
		
		<div class="pull-left">
			<ul class="pagination" style="margin-top:0;">
				<li class="prev{if !$prevpage} disabled{/if}"><a href="{if $prevpage}emailmanagement.php?action=managelist{if $q}&q={$q}{/if}&amp;page={$prevpage}&domainid={$domainid}&domain={$domain}&listaddress={$listaddress}&itemlimit={$itemlimit}&istab=tabremmembers#tabDelmem{else}javascript:return false;{/if}">&larr; {$LANG.previouspage}</a></li>
				<li class="next{if !$nextpage || $nextpage==$pageend} disabled{/if}"><a href="{if $nextpage != $pageend}emailmanagement.php?action=managelist{if $q}&q={$q}{/if}&amp;page={$nextpage}&domainid={$domainid}&domain={$domain}&listaddress={$listaddress}&itemlimit={$itemlimit}&istab=tabremmembers#tabDelmem{else}javascript:return false;{/if}">{$LANG.nextpage} &rarr;</a></li>
			</ul>
		</div>		
	</div>
	
	<div class="tab-pane fade" id="tabManagemod">
		<table class="table table-bordered table-hover">
			<tr>
				<td colspan="2">
					<h3>{$LANG.rcmail_addmoderators} {$listaddress}</h3>
				</td>
			</tr>
			<tr>
				<td>
					{if $moderatorslist.1}
					{foreach key=num item=moderator from=$moderatorslist}
					<form method="post" action="emailmanagement.php?action=managelist#tabManagemod">
						<input type="hidden" name="domainid" value="{$domainid}"/>
						<input type="hidden" name="domain" value="{$domain}"/>
						<input type="hidden" name="deletemoderator" value="true"/>
						<input type="hidden" name="moderator" value="{$moderator}"/>
						<input type="hidden" name="listaddress" value="{$listaddress}"/>
						<input type="hidden" name="istab" value="tabmoderators"/>
						<div>
							<p>
								<strong>{$moderator}</strong>&nbsp;&nbsp;
								<input type="submit" value="Remove" onclick="return confirmModeratorDelete();" class="btn btn-danger btn-sm"/>
							</p>
						</div>
					</form>
					{/foreach}
					{else}
					{$moderatorslist.0}
					{/if}
				</td>
			</tr>
		</table>
		
		<br />
		
		<form method="post" action="emailmanagement.php?action=managelist#tabManagemod">
			<input type="hidden" name="domainid" value="{$domainid}"/>
			<input type="hidden" name="domain" value="{$domain}"/>
			<input type="hidden" name="addmoderator" value="true"/>
			<input type="hidden" name="listaddress" value="{$listaddress}"/>
			<input type="hidden" name="istab" value="tabmoderators"/>
			<table class="table table-bordered table-hover">
				<tr>
					<td width="200">
						Moderators Emails
					</td>
					<td>
						{$LANG.rcmail_moderatorsdesc}<br/>
						<textarea class="form-control" name="moderators" cols="50" rows="5">{if $addmoderatorerror}{$smarty.post.moderators}{/if}</textarea>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Note</strong>
					</td>
					<td>
					{$LANG.rcmail_undeleteablemoddesc}
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<p align="center"><input type="submit" value="Add Moderator" class="btn btn-success"/></p>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<script language="javascript" type="text/javascript">
	{literal}
		jQuery('#multiaction').on('submit',function(e){
			$form=jQuery(this);
			jQuery('input[type=checkbox]:checked').each(function(index,elem){
				var val=jQuery(elem).val(),name=jQuery(elem).attr('name');
				var hiddenInput=jQuery('<input type="hidden" value="'+val+'" name="'+name+'"/>');
				$form.append(hiddenInput);
			});
		});
	{/literal}
</script>
</div>

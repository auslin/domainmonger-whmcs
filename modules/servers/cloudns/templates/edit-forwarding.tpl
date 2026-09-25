<style type="text/css">
{literal}



a.btn.cloudns-switch-style-button,
button.btn.cloudns-switch-style-button,
input.btn.cloudns-switch-style-button,
.cloudns-switch-style-button,
.cloudns-switch-style-button:visited {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #ff6b1a !important;
	border-radius: 5px !important;
	background: #ff6b1a !important;
	background-color: #ff6b1a !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 14px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-align: center !important;
	text-decoration: none !important;
	text-transform: none !important;
	text-shadow: none !important;
	box-shadow: none !important;
	white-space: nowrap !important;
	box-sizing: border-box !important;
	vertical-align: middle !important;
	appearance: none !important;
	-webkit-appearance: none !important;
}

a.btn.cloudns-switch-style-button:hover,
a.btn.cloudns-switch-style-button:focus,
button.btn.cloudns-switch-style-button:hover,
button.btn.cloudns-switch-style-button:focus,
input.btn.cloudns-switch-style-button:hover,
input.btn.cloudns-switch-style-button:focus,
.cloudns-switch-style-button:hover,
.cloudns-switch-style-button:focus {
	background: #f05f12 !important;
	background-color: #f05f12 !important;
	background-image: none !important;
	border-color: #f05f12 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	text-shadow: none !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22) !important;
	outline: none !important;
}

a.btn.cloudns-switch-style-button:active,
button.btn.cloudns-switch-style-button:active,
input.btn.cloudns-switch-style-button:active,
.cloudns-switch-style-button:active {
	background: #dd520c !important;
	background-color: #dd520c !important;
	background-image: none !important;
	border-color: #dd520c !important;
	color: #ffffff !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.28) !important;
}

a.btn.cloudns-switch-style-button[disabled],
button.btn.cloudns-switch-style-button[disabled],
input.btn.cloudns-switch-style-button[disabled],
.cloudns-switch-style-button[disabled],
.cloudns-switch-style-button.disabled,
.cloudns-switch-style-button:disabled {
	background: #ff6b1a !important;
	background-color: #ff6b1a !important;
	background-image: none !important;
	border-color: #ff6b1a !important;
	color: #ffffff !important;
	opacity: 1 !important;
	cursor: not-allowed !important;
}

.btn-block.cloudns-switch-style-button {
	display: flex !important;
	width: 100% !important;
}

.cloudns-mailforward-catchall-notice {
	margin: 0 0 14px 0 !important;
	padding: 10px 12px !important;
	border: 1px solid #faebcc !important;
	border-left: 4px solid #f0ad4e !important;
	border-radius: 4px !important;
	background: #fcf8e3 !important;
	color: #8a6d3b !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
}

.cloudns-mailforward-catchall-notice strong {
	display: inline-block !important;
	min-width: 18px !important;
	padding: 1px 5px !important;
	border: 1px solid #e6d38a !important;
	border-radius: 4px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	color: #6f541e !important;
	font-family: Menlo, Monaco, Consolas, "Courier New", monospace !important;
	font-size: 12px !important;
	font-weight: 700 !important;
	line-height: 1.2 !important;
	text-align: center !important;
}

{/literal}
</style>
{assign var="path" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
	{assign var="path" value="./"}
{/if}
{include file="$path/header-settings.tpl"}

<form action="clientarea.php?action=productdetails&id={$serviceid}&customAction=edit-forward&zone={$zone}" method="post" id="recordsForm" class="recordsForm cloudns-forward-form cloudns-body-panel cloudns-form-panel cloudns-forward-form-panel">
	<div class="alert alert-warning cloudns-mailforward-catchall-notice">Enter <strong>*</strong> to create a catch-all email forward.</div>
	<div class="dTitle"><br /><br />Email:</div>
	<div class="clear"></div>
	<span class="spanHost"><input type="text" id="addRecordHost" name="editEmailForward" class="form-control" value="{$forward.source}" autocapitalize="off" spellcheck="false" />@{$zone}</span><br class="dTitle">
	<input type="hidden" name="forward_id" value="{$forward.id}">

	<br class="clear" />
	<div class="recordContainter">
		<div class="inputTitle">Points to:</div>
		<input type="email" id="addRecordRecord" name="editEmailForwardTo" value="{$forward.destination}" class="pointsTo form-control" autocapitalize="off" spellcheck="false" /> <br /><br />
	</div>

	<input type="submit" name="do_save" value="Save" class="btn cloudns-switch-style-button cloudns-btn-primary" />
</form>

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

{/literal}
</style>

<div class="newZoneContainer cloudns-add-zone-type-panel">
	<form action="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-zone" method="post">
		<h4>Master Zone</h4>
		<input type="hidden" name="newZoneOptions" value="1" />
		<div class="cloudns-auto-ns-note">The required DomainMonger nameservers will be added automatically.</div>
		
		<span class="pull-left" >Domain name: &nbsp; </span>
		<input class="pull-left" type="text" name="zone" id="zone" />
		<input type="hidden" name="zoneType" value="masterZoneType" />
		<input type="submit" name="" value="Create" class="btn cloudns-switch-style-button cloudns-btn-primary" />
	</form>
</div>
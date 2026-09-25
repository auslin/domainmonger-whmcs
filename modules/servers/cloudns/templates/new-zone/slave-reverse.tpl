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
<style type="text/css">
{if $version gte '6'}	
{literal}	

.notification {

	word-break: break-all;
}	
	
@media only screen and (max-width: 660px) {
	
	.dDot {
		display: none;
	}
	
	.mBr {
		display: block;
	}	
	
	form.recordsForm input.form-control,
	form.recordsForm select {
		width: 100%;
	}
}
	
{/literal}	
{/if}
</style>

<div class="newZoneContainer cloudns-add-zone-type-panel">
	<form action="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-zone" method="post" class="recordsForm">
		<h4>Slave Reverse Zone</h4>
		<table style="width: 100%;" class="newSlaveZoneAdd">
			<tr><td colspan="3">Reverse Zone Name: <br /> <input type="text" class="pull-left form-control slaveZone" maxlength="63" name="zone" /><span class="pull-left dDot">&nbsp;.&nbsp;</span>
			<br class="mBr clear">
			<br class="mBr">
				<select name="zoneSufix" id="zoneType" class="pull-left form-control">
					<option value="in-addr.arpa">in-addr.arpa</option>
					<option value="ip6.arpa">ip6.arpa</option>
				</select>
			</td></tr>
			<tr><td colspan="3" style="padding-top: 15px;">Master server IP:<br /> <input type="text" class="slaveZone form-control pull-left" id="slaveMasterIp" name="slaveMasterIp" />
			<br class="mButton clear">
			<br class="mButton">		
			<input type="hidden" name="zoneType" value="slaveReverseZoneType" /><input type="submit" value="Create" class="btn cloudns-switch-style-button cloudns-btn-primary" /></td></tr>

			<tr><td colspan="3">
			<br><p>* Manage the records from your master server</p><br />
			</td></tr>
		</table>
	</form>	

	<div class="notification">
		<p>The IPv4 zone needs to be in the format 1.0.0.127 for IP 127.0.0.1 and from the drop-down menu needs to be chosen in-addr.arpa.</p><br />

		<p>The IPv6 zone needs to be in the format 1.2.3.4.5.6.7.8.9.0.1.2.3.4.5.6.7.8.9.0.1.2.3.4.5.6.7.8.9.0.1.2 for IPv6 2109:8765:4321:0987:6543:2109:8765:4321 and from the drop-down menu needs to be chosen ip6.arpa.</p>
	</div>
</div>
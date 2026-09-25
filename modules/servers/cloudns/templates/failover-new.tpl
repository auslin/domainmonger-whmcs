<style type="text/css">
{literal}
	.w-50{
		width: 50%;
	}
	
	.mTitle {
		display: none;
	}
	
	.mt-8 {
		margin-top: 8px;
	}
	
	.inputLabel {
		width: auto;
		min-width: 40%;
	}
	
	.fo-domain {
		max-width: 100%;
	}
	
	.fo-port {
		max-width: 60px;
	}
	
	.fo-path {
		max-width: 20%;
	}
	
	.flex {
		display: flex;	
		flex: 1 0 auto;
		align-items:center;
		white-space: nowrap;
		flex-wrap: wrap;
		height: auto;
	}
	
	.flex input, .flex select {
		flex: 1 0 235px;
	}
	
	.mobileInfo {
		position: relative;
	}

	.mobileInfo .title {
		position: absolute;
		top: 20px;
		background: black;
		color: white;
		padding: 4px;
		left: 0;
		white-space: nowrap;
	}
	
	.popover {
		max-width: 100%;
	}
        
        .mr-50 {
                margin-right: 50px;
        }
        
        .w-50 {
                width: 50px;
        }
        
        .flex-element {
                flex: 1 0 235px;
        }
	
	@media only screen and (max-width: 650px) {
		.dTitle {
			display: none;
		}
		
		.mTitle {
			display: block;
		}
		
		.fo-domain, 
		.fo-port,
		.fo-path {
			max-width: none;
			width: 100%;
		}
	}



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

/* DM button/link taxonomy pass
   Primary = orange action, Secondary = neutral outline, Danger = destructive. */
a.btn.cloudns-btn-primary,
button.btn.cloudns-btn-primary,
input.btn.cloudns-btn-primary,
.btn.cloudns-btn-primary,
a.btn.cloudns-switch-style-button.cloudns-btn-primary,
button.btn.cloudns-switch-style-button.cloudns-btn-primary,
input.btn.cloudns-switch-style-button.cloudns-btn-primary {
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
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
	cursor: pointer !important;
	box-sizing: border-box !important;
}

a.btn.cloudns-btn-primary:hover,
a.btn.cloudns-btn-primary:focus,
button.btn.cloudns-btn-primary:hover,
button.btn.cloudns-btn-primary:focus,
input.btn.cloudns-btn-primary:hover,
input.btn.cloudns-btn-primary:focus,
.btn.cloudns-btn-primary:hover,
.btn.cloudns-btn-primary:focus {
	border-color: #f05f12 !important;
	background: #f05f12 !important;
	background-color: #f05f12 !important;
	background-image: none !important;
	color: #ffffff !important;
	text-decoration: none !important;
	box-shadow: none !important;
	outline: none !important;
}

a.btn.cloudns-btn-primary:active,
button.btn.cloudns-btn-primary:active,
input.btn.cloudns-btn-primary:active,
.btn.cloudns-btn-primary:active {
	border-color: #dd520c !important;
	background: #dd520c !important;
	background-color: #dd520c !important;
	color: #ffffff !important;
}

a.btn.cloudns-btn-secondary,
button.btn.cloudns-btn-secondary,
input.btn.cloudns-btn-secondary,
.btn.cloudns-btn-secondary,
a.btn.cloudns-switch-style-button.cloudns-btn-secondary,
button.btn.cloudns-switch-style-button.cloudns-btn-secondary,
input.btn.cloudns-switch-style-button.cloudns-btn-secondary {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #d75b0b !important;
	border-radius: 5px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #d75b0b !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
	cursor: pointer !important;
	box-sizing: border-box !important;
}

a.btn.cloudns-btn-secondary:hover,
a.btn.cloudns-btn-secondary:focus,
button.btn.cloudns-btn-secondary:hover,
button.btn.cloudns-btn-secondary:focus,
input.btn.cloudns-btn-secondary:hover,
input.btn.cloudns-btn-secondary:focus,
.btn.cloudns-btn-secondary:hover,
.btn.cloudns-btn-secondary:focus {
	border-color: #f7941d !important;
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	background-image: none !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	box-shadow: none !important;
	outline: none !important;
}

a.btn.cloudns-btn-danger,
button.btn.cloudns-btn-danger,
input.btn.cloudns-btn-danger,
.btn.cloudns-btn-danger,
a.btn.btn-danger.cloudns-btn-danger,
button.btn.btn-danger.cloudns-btn-danger,
input.btn.btn-danger.cloudns-btn-danger,
a.btn.cloudns-switch-style-button.cloudns-btn-danger,
button.btn.cloudns-switch-style-button.cloudns-btn-danger,
input.btn.cloudns-switch-style-button.cloudns-btn-danger {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #b94a48 !important;
	border-radius: 5px !important;
	background: #b94a48 !important;
	background-color: #b94a48 !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
	cursor: pointer !important;
	box-sizing: border-box !important;
}

a.btn.cloudns-btn-danger:hover,
a.btn.cloudns-btn-danger:focus,
button.btn.cloudns-btn-danger:hover,
button.btn.cloudns-btn-danger:focus,
input.btn.cloudns-btn-danger:hover,
input.btn.cloudns-btn-danger:focus,
.btn.cloudns-btn-danger:hover,
.btn.cloudns-btn-danger:focus {
	border-color: #a94442 !important;
	background: #a94442 !important;
	background-color: #a94442 !important;
	background-image: none !important;
	color: #ffffff !important;
	text-decoration: none !important;
	box-shadow: none !important;
	outline: none !important;
}

a.btn.cloudns-btn-primary[disabled],
button.btn.cloudns-btn-primary[disabled],
input.btn.cloudns-btn-primary[disabled],
a.btn.cloudns-btn-secondary[disabled],
button.btn.cloudns-btn-secondary[disabled],
input.btn.cloudns-btn-secondary[disabled],
a.btn.cloudns-btn-danger[disabled],
button.btn.cloudns-btn-danger[disabled],
input.btn.cloudns-btn-danger[disabled],
.btn.cloudns-btn-primary.disabled,
.btn.cloudns-btn-secondary.disabled,
.btn.cloudns-btn-danger.disabled,
.btn.cloudns-btn-primary:disabled,
.btn.cloudns-btn-secondary:disabled,
.btn.cloudns-btn-danger:disabled {
	opacity: 0.55 !important;
	cursor: not-allowed !important;
	box-shadow: none !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-zones-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon) {
	color: #d75b0b !important;
	text-decoration: underline !important;
	text-underline-offset: 2px !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-zones-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-zones-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):focus {
	color: #a94708 !important;
	text-decoration: underline !important;
}

.cloudns-action-icon-danger,
.cloudns-action-icon.cloudns-action-icon-danger {
	color: #b94a48 !important;
}

.cloudns-action-icon-danger:hover,
.cloudns-action-icon-danger:focus,
.cloudns-action-icon.cloudns-action-icon-danger:hover,
.cloudns-action-icon.cloudns-action-icon-danger:focus {
	background: #fff1f1 !important;
	border-color: #f0c8c8 !important;
	color: #a94442 !important;
	text-decoration: none !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a,
#cloudnsMobileSettingsMenu a.cloudns-danger-link {
	color: #9b2f2f !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:focus,
#cloudnsMobileSettingsMenu a.cloudns-danger-link:hover,
#cloudnsMobileSettingsMenu a.cloudns-danger-link:focus {
	background: #fff1f1 !important;
	color: #9b2f2f !important;
}

</style>

<script>
{literal}
	$(document).ready (function() {
		failoverChangeType();
		zone_failoverChangeDownEvent();
                failoverChangeNotifications();
                toggleProtocolOptions();

		var element = '.mTooltip';
		$(element).popover({
		});

		$('body').on('click', function (e) {
				
			$(element).each(function(index, elm) {
				hidePopover(elm, e);
			}); 
		});

		var hidePopover = function(element, e){
			if (!$(element).is(e.target) && $(element).has(e.target).length === 0 && $('.popover').has(e.target).length === 0){
				$(element).popover('hide');
			}
		};
	});
	
	function failoverChangeType() {
		var foType = parseInt($('#fo_check_type').val());
		$('.monitoringType').css('display', 'none');
		$('.monitoringType' + foType).css('display', '');

		var foHttpPort = parseInt($('#fo_http_port').val());
        }
        
        function failoverChangeNotifications () {
                var fo_notification = $('#fo_notification_type').val();
                var placeholder = '';
                
                $('.notificationType').css('display', 'none');
		$('.notificationType' + fo_notification).css('display', '');
                
                if (fo_notification == 1) {
                    placeholder = 'your_email@example.com';
                } else if (fo_notification == 2) {
                    placeholder = 'http://example.com?content=up';
                } else if (fo_notification == 3) {
                    placeholder = 'http://example.com?content=down';
                }
                
                $('#fo_notification_value').attr('placeholder', placeholder);
        }
        
        function toggleCustomStringOptions () {
                if ($('#web_custom_string_yes').is(':checked')) {
                        $('.customStringOptions').css('display', 'block');
                } else {
                        $('.customStringOptions').css('display', 'none');
                }
        }
        
        function toggleProtocolOptions () {
                if ($('#web_protocol_https').is(':checked')) {
                        $('.protocol-type').html('https');
                } else if ($('#web_protocol_http').is(':checked')){
                        $('.protocol-type').html('http');
                }
        }
        
	function zone_failoverChangeDownEvent() {
	var downEventHandler = $('#fo_down_event_handler').val();
	$('.monitoringDownEvent').css('display', 'none');
	$('.monitoringDownEvent' + downEventHandler).css('display', '');

	// down event handler - monitoring
	if (downEventHandler == 0) {
		$('#fo_up_handler_active').attr('disabled', 'disabled');
	} else {
		$('#fo_up_handler_active').removeAttr('disabled');
	}
}
{/literal}
</script>
{assign var="path" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
	{assign var="path" value="./"}
{/if}

{include file="$path/header-settings.tpl"}

<form method="post" class="from-inline" action="clientarea.php?action=productdetails&id={$serviceid}&customAction=failover-activate&zone={$zone}&dns_record_id={$record.id}">
	
	<div class="pull-left inputTitle fleft">Settings for <strong>{$fullHost}</strong> with current <strong>{$record.type}</strong> record to IP <strong>{$record.record}</strong></div>
	<br />
	<br />
	<br class="clear">
	<div class="flex">
		<label class="pull-left inputLabel fleft">Main IP:</label>
		<input id="fo_main_ip" name="fo_main_ip" type="text" value="{$record.record|@htmlspecialchars}" class="input-text form-control" />
		<span class="info dTitle">
			<img src="./assets/img/help.gif" class="showTitle" title="This is the main IP address which will be monitored and the failover will manage" alt="[?]">
		</span>
		<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="This is the main IP address which will be monitored and the failover will manage" alt="[?]">
			<img src="./assets/img/help.gif">
		</span>
	</div>
	<br class="clear">
	<div class="flex">
		<label class="pull-left inputLabel fleft">Monitoring type:</label>
		<select id="fo_check_type" name="fo_check_type" class="pull-left form-control" onChange="failoverChangeType();">
		{foreach $checkTypes as $id=>$name}
			<option value="{$id}">{$name}</option>
		{/foreach}
		</select>
		<span class="info dTitle">
			<img src="./assets/img/help.gif" class="showTitle" title="Choose the check which will be monitored to the main and backup IPs" alt="[?]" />
		</span>
		<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="Choose the check which will be monitored to the main and backup IPs" alt="[?]">
			<img src="./assets/img/help.gif">
		</span>
	</div>
	<br class="clear">
        <div class="flex monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_WEB} failover-notifications">
		<label class="pull-left inputLabel fleft">Protocol:</label>
                <div class="flex">
                    <label class="w-50" onclick="toggleProtocolOptions();"><input class="inputRadio pull-left" type="radio" name="web_protocol" id="web_protocol_https" value="https" checked="checked"> HTTPS</label>
                    <label class="flex-element" onclick="toggleProtocolOptions();"><input class="inputRadio pull-left" type="radio" name="web_protocol" id="web_protocol_http" value="http"> HTTP</label>
                </div>
                <span class="info dTitle">
			<img src="./assets/img/help.gif" class="showTitle" title="Choose your desired protocol" alt="[?]" />
		</span>
                <span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="Choose your desired protocol" alt="[?]">
			<img src="./assets/img/help.gif">
		</span>
                <br class="clear">
	</div>
        <br class="clear monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_WEB}">
        <div class="flex monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_WEB}">
		<label class="pull-left inputLabel fleft">Custom string:</label>
                <div class="flex">
                    <label class="w-50" onclick="toggleCustomStringOptions();"><input class="inputRadio pull-left" type="radio" name="web_custom_string" id="web_custom_string_yes" value="1"> Yes</label>
                    <label class="flex-element" onclick="toggleCustomStringOptions();"><input class="inputRadio pull-left" type="radio" name="web_custom_string" id="web_custom_string_no" value="0" checked="checked"> No</label>
                </div>
                <span class="info dTitle">
			<img src="./assets/img/help.gif" class="showTitle" title="If you want to check a custom string on the web page, mark YES" alt="[?]" />
		</span>
                <span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="If you want to check a custom string on the web page, mark YES" alt="[?]">
			<img src="./assets/img/help.gif">
		</span>
                <br class="clear">
	</div>
        <br class="clear monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_WEB}">
        <div class="flex monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_PING}">
                <label class="pull-left inputLabel fleft">Threshold:</label>
                <select id="fo_ping_threshold" name="fo_ping_threshold" class="pull-left form-control">
                    {foreach Cloudns_Failover::MONITORING_PING_THRESHOLD as $threshold}
			<option value="{$threshold}">{$threshold}%</option>
                    {/foreach}
                </select>
                <span class="info dTitle">
			<img src="./assets/img/help.gif" class="showTitle" title="Choose your preferred threshold for packet loss." alt="[?]" />
		</span>
                <span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="Choose your preferred threshold for packet loss." alt="[?]">
			<img src="./assets/img/help.gif">
		</span>
	</div>
        <br class="clear monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_PING}">
	<div class="flex">
		<label class="pull-left inputLabel fleft">Monitoring region:</label>
		<select id="fo_monitoring_region" name="fo_monitoring_region" class="pull-left form-control">
			<option value="global">Global</option>
			<option value="eur">Europe</option>
			<option value="nam">North America</option>
		</select>
		<span class="info dTitle">
			<img src="./assets/img/help.gif" class="showTitle" title="The record will be monitored only from this area" alt="[?]" />
		</span>
		<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="The record will be monitored only from this area" alt="[?]">
			<img src="./assets/img/help.gif">
		</span>
	</div>
	<br class="clear">
	
	<div class="monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_WEB}">
		<label class="pull-left inputLabel fleft dTitle">URL to check:</label>
		<label class="pull-left inputLabel fleft mTitle">Domain:</label>
		<br class="clear">
		<div class="flex">
			<span class="protocol-type">http</span><span class="monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_HTTPS} monitoringType{Cloudns_Failover::CHECK_TYPE_HTTPS_CUSTOM}">s</span>://&nbsp;
			<input id="fo_http_host" name="fo_http_host" class="form-control fo-domain" value="{$fullHost|@htmlspecialchars}" type="text" placeholder="FQDN"><span class="dTitle">&nbsp;:&nbsp;</span>
			<label class="pull-left inputLabel fleft mTitle"><br>Port:</label>
			<input id="fo_http_port" name="fo_http_port" class="form-control fo-port" type="text" value="80" placeholder="port"><span class="dTitle">&nbsp;/&nbsp;</span>
			<label class="pull-left inputLabel fleft mTitle"><br>Path:</label>
			<input id="fo_http_path" name="fo_http_path" class="form-control fo-path" type="text" placeholder="Path">
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="The FQDN will be monitored on the main and backup IPs" alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="The FQDN will be monitored on the main and backup IPs" alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="monitoringType customStringOptions" style="display:none">
		<div class="flex">
			<label class="pull-left inputLabel fleft">String to match:</label>
			<input id="fo_http_content" name="fo_http_content" type="text" class="input-text form-control" placeholder="OK">
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="The content returned by the checked URL should be equal to this string." alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="The content returned by the checked URL should be equal to this string." alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_TCP_SOCKET} monitoringType{Cloudns_Failover::CHECK_TYPE_UDP_SOCKET}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Port:</label>
			<input id="fo_port" name="fo_port" type="text" class="input-text form-control" placeholder="Port number">
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="The port number to which the TCP or UDP check will be made." alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="The port number to which the TCP or UDP check will be made." alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_DNS}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Host to query:</label>
			<input id="fo_dns_host" name="fo_dns_host" type="text" class="input-text form-control" value="{$fullHost|@htmlspecialchars}" placeholder="FQDN">
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="The FQDN for which the DNS check will be made." alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="The FQDN for which the DNS check will be made." alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_DNS}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Query type:</label>
			<select id="fo_dns_type" name="fo_dns_type" class="pull-left form-control">
				<option value="A" {if $record.type == 'A'} selected="selected" {/if}>A</option>
				<option value="AAAA" {if $record.type == 'AAAA'} selected="selected" {/if}>AAAA</option>
			</select>
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="DNS query type for the DNS check." alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="DNS query type for the DNS check." alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="monitoringType monitoringType{Cloudns_Failover::CHECK_TYPE_DNS}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Required response:</label>
			<input id="fo_dns_response" name="fo_dns_response" type="text" class="input-text form-control" placeholder="Response to the query" value="{$record.record|@htmlspecialchars}">
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="Expected response for the DNS check." alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="Expected response for the DNS check." alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="flex">
		<label class="pull-left inputLabel fleft">If the main IP is down:</label>
		<select id="fo_down_event_handler" name="fo_down_event_handler" class="pull-left form-control" onchange="zone_failoverChangeDownEvent()">
			<option value={Cloudns_Failover::DOWN_EVENT_HANDLER_MONITORING}>Monitoring only, e-mail notification</option>
			<option value={Cloudns_Failover::DOWN_EVENT_HANDLER_PASSIVE}>Deactivate the DNS record</option>
			<option value={Cloudns_Failover::DOWN_EVENT_HANDLER_ACTIVE}>Replace with working backup IP</option>
		</select>
	</div>
	<br class="clear">
	<div class="monitoringDownEvent monitoringDownEvent{Cloudns_Failover::DOWN_EVENT_HANDLER_ACTIVE}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Backup IP 1:</label>
			<input id="fo_backup_ip_1" name="fo_backup_ip_1" type="text" class="input-text form-control" placeholder="Required">
			<span class="info dTitle">
				<img src="./assets/img/help.gif" class="showTitle" title="IP to be changed to if the main IP is down" alt="[?]" />
			</span>
			<span class="info mTitle mobileInfo mTooltip" id="mTooltip" rel="popover" data-placement="bottom" data-content="IP to be changed to if the main IP is down" alt="[?]">
				<img src="./assets/img/help.gif">
			</span>
		</div>
		<br class="clear">
	</div>
	<div class="monitoringDownEvent monitoringDownEvent{Cloudns_Failover::DOWN_EVENT_HANDLER_ACTIVE}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Backup IP 2:</label>
			<input id="fo_backup_ip_2" name="fo_backup_ip_2" type="text" class="input-text form-control" placeholder="Optional">
		</div>
			<br class="clear">
	</div>
	<div class="monitoringDownEvent monitoringDownEvent{Cloudns_Failover::DOWN_EVENT_HANDLER_ACTIVE}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Backup IP 3:</label>
			<input id="fo_backup_ip_3" name="fo_backup_ip_3" type="text" class="input-text form-control" placeholder="Optional">
		</div>
		<br class="clear">
	</div>
	<div class="monitoringDownEvent monitoringDownEvent{Cloudns_Failover::DOWN_EVENT_HANDLER_ACTIVE}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Backup IP 4:</label>
			<input id="fo_backup_ip_4" name="fo_backup_ip_4" type="text" class="input-text form-control" placeholder="Optional">
		</div>
		<br class="clear">
	</div>
	<div class="monitoringDownEvent monitoringDownEvent{Cloudns_Failover::DOWN_EVENT_HANDLER_ACTIVE}">
		<div class="flex">
			<label class="pull-left inputLabel fleft">Backup IP 5:</label>
			<input id="fo_backup_ip_5" name="fo_backup_ip_5" type="text" class="input-text form-control" placeholder="Optional">
		</div>
		<br class="clear">
	</div>
	<div class="flex">
		<label class="pull-left inputLabel fleft">If the main IP is up:</label>
		<select id="fo_up_event_handler" name="fo_up_event_handler" class="pull-left form-control">
			<option value={Cloudns_Failover::BACK_UP_EVENT_HANDLER_MONITORING}>Monitoring only, e-mail notification</option>
			<option value={Cloudns_Failover::BACK_UP_EVENT_HANDLER_MANUAL}>Do not monitor it, if it is back up</option>
			<option id="fo_up_handler_active" value={Cloudns_Failover::BACK_UP_EVENT_HANDLER_AUTOMATIC}>Activate the main IP for the DNS record</option>
		</select>
	</div>
        <br class="clear">
        <div class="flex">
		<label class="pull-left inputLabel fleft">Notification Type:</label>
		<select id="fo_notification_type" name="fo_notification_type" class="pull-left form-control" onChange="failoverChangeNotifications();">
			<option value={Cloudns_Failover::NOTIFICATION_TYPE_EMAIL}>E-mail</option>
			<option value={Cloudns_Failover::NOTIFICATION_TYPE_WEBHOOK_UP}>Webhook - UP event</option>
			<option value={Cloudns_Failover::NOTIFICATION_TYPE_WEBHOOK_DOWN}>Webhook - DOWN event</option>
		</select>
	</div>
	<br class="clear">
        <div class="flex">
		<label class="pull-left notificationType notificationType{Cloudns_Failover::NOTIFICATION_TYPE_EMAIL} inputLabel fleft">E-mail:</label>
                <label class="pull-left notificationType notificationType{Cloudns_Failover::NOTIFICATION_TYPE_WEBHOOK_UP} notificationType{Cloudns_Failover::NOTIFICATION_TYPE_WEBHOOK_DOWN} inputLabel fleft">URL:</label>
		<input id="fo_notification_value" name="fo_notification_value" type="text" class="input-text form-control" />
	</div>
	<br class="clear">
	<input type="submit" name="activate_fo" value="Activate" class="btn cloudns-switch-style-button cloudns-btn-primary" />
</form>
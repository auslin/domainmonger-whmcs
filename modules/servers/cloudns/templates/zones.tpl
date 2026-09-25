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
<!-- DomainMonger Patch 64 ClouDNS global late visual override loader -->
<script type="text/javascript">
(function () {
    var cssHref = '{$WEB_ROOT}/modules/servers/cloudns/templates/cloudns-v65-late-override.css?v=1309';
    var cssId = 'dm-cloudns-v65-late-override';
    function loadCloudnsLateOverride() {
        if (document.getElementById(cssId)) {
            return;
        }
        var link = document.createElement('link');
        link.id = cssId;
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = cssHref;
        (document.body || document.getElementsByTagName('body')[0] || document.head).appendChild(link);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadCloudnsLateOverride);
    } else {
        loadCloudnsLateOverride();
    }
})();
</script>


<style type="text/css">
{literal}
.notification {
background-color: #dbe3ff;
border-color: #a2b4ee;
color: #585b66;
display:block;
font-style:normal;
padding: 10px 10px 10px 36px;
line-height: 1.5em;
}
.table tr#zones td ul.breadcrumb {
padding: 0;
margin: 0;
background: none;
}
.table tr#zones td:first-child {
width: 69%;
overflow: hidden;
vertical-align: middle;
}
.backToZones {
text-align: right;
list-style-type: none;
}

.cloudns-add-button,
.cloudns-add-button:visited {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 70px;
	
	padding: 0 14px;
	border: 1px solid #ff6b1a;
	border-radius: 5px;
	background: #ff6b1a !important;
	color: #ffffff !important;
	font-size: 14px;
	font-weight: 700;
	line-height: 1;
	text-decoration: none !important;
	box-shadow: none;
}

.cloudns-add-button:hover,
.cloudns-add-button:focus {
	background: #f05f12 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	border-color: #f05f12;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22);
	outline: none;
}

.cloudns-add-button:active {
	background: #dd520c !important;
	color: #ffffff !important;
	border-color: #dd520c;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.28);
}

.cloudns-action-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 22px;
	height: 22px;
	margin-left: 3px;
	border: 1px solid #d4d4d4;
	border-radius: 3px;
	background: #fff;
	color: #555 !important;
	text-decoration: none !important;
	font-size: 13px;
	vertical-align: middle;
}

.cloudns-action-icon:hover,
.cloudns-action-icon:focus {
	border-color: #f7941d;
	color: #f7941d !important;
	text-decoration: none !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18);
}

.cloudns-pencil {
	font-size: 15px;
}

.clear {
clear: both;
}
#zones-list.table.table-hover td.text-right {
text-align: right;
}
#zones-list {
width: 100%;
}

/* DM DNS Zones white background pass */
.cloudns-zones-panel {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	padding: 14px !important;
	margin: 0 0 14px 0 !important;
	box-sizing: border-box !important;
	overflow: hidden !important;
}

.cloudns-zones-heading-row {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin: 0 0 14px 0;
}

.cloudns-zones-heading-row .cloudns-module-title {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
	color: #333333 !important;
	font-size: 20px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

.cloudns-zones-heading-row .backToZones {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
	text-align: right;
}

.cloudns-zones-heading-row .backToZones li {
	margin: 0 !important;
	padding: 0 !important;
}

.cloudns-zones-panel .notification {
	background: #dce6ff !important;
	border: 1px solid #9fb7ff !important;
	border-radius: 6px !important;
	padding: 12px 14px !important;
	margin: 0 0 14px 0 !important;
	color: #30446c !important;
	line-height: 1.45 !important;
}

.cloudns-zones-panel #zones-list {
	margin-bottom: 0 !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-heading-row {
		align-items: stretch;
		flex-direction: column;
	}

	.cloudns-zones-heading-row .backToZones,
	.cloudns-zones-heading-row .backToZones .btn {
		width: 100%;
	}
}

/* DM deep UI consistency pass: zones list */
#zones-list {
	border: 1px solid #dddddd;
	border-radius: 8px;
	background: #ffffff;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
	overflow: hidden;
}

#zones-list > tbody > tr > td,
#zones-list > tr > td {
	padding: 12px 10px;
	border-top: 1px solid #eeeeee;
	color: #333333;
	font-size: 13px;
}

#zones-list tr:first-child td {
	border-top: 0;
}

#zones-list a {
	color: #ef9846;
	font-weight: 600;
}

#zones-list a:hover,
#zones-list a:focus {
	color: #d8873f;
}

.zones-options .btn-danger,
.zones-options .btn-danger:visited {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	min-width: 72px !important;
	padding: 0 14px !important;
	border: 1px solid #b94a48 !important;
	border-radius: 5px !important;
	background: #b94a48 !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 14px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	text-shadow: none !important;
	box-shadow: none !important;
}

.zones-options .btn-danger:hover,
.zones-options .btn-danger:focus {
	background: #a94442 !important;
	border-color: #a94442 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	box-shadow: 0 0 0 2px rgba(185, 74, 72, 0.18) !important;
	outline: none !important;
}


/* DM final visual consistency pass: DNS Zones standalone page */
.cloudns-zones-panel {
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
}

.cloudns-zones-panel #zones-list td,
.cloudns-zones-panel #zones-list th {
	vertical-align: middle !important;
}

.cloudns-zones-panel #zones-list td {
	line-height: 1.45 !important;
	padding: 10px !important;
}

.cloudns-zones-panel #zones-list .zones-options {
	display: inline-flex;
	align-items: center;
	justify-content: flex-end;
	gap: 6px;
	flex-wrap: wrap;
}

.cloudns-zones-panel a,
.cloudns-zones-panel a:visited {
	transition: color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease !important;
}

.cloudns-zones-panel #zones-list a:not(.btn):hover,
.cloudns-zones-panel #zones-list a:not(.btn):focus {
	color: #d8873f !important;
	text-decoration: underline !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-panel {
		padding: 10px !important;
	}
}

{/literal}
{if $version gte '6'}
{literal}
.vDots:after {
	content: '\2807';
	font-size: inherit;
}

#mobile-zones-options {
	display: none;
}

.mr-10 {
	margin-right: 10px;
}

@media only screen and (max-width: 1200px) {
	
	.zones-options {
		display: none;
	}
	
	#mobile-zones-options {
		display: block;
	}
}

@media only screen and (max-width: 870px) {
	
	section#header .logo-text {
		font-size: 2.3em;
	}
}

@media only screen and (max-width: 650px) {
	
	#mobile-zones-options a:focus, a:hover {
		text-decoration: none;
	}
	
	ol, ul {
		margin-top: 5px;
	}
}
{/literal}
{/if}
{if $version gte '7.7'}
{literal}
.zones-options {
	min-width: 260px;
}

{/literal}
{/if}

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


/* DM page shell consistency pass
   Standard page structure: page reference row, menu, then white content panel. */
.cloudns-module-header {
	clear: both !important;
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	gap: 16px !important;
	margin: 0 0 10px 0 !important;
	padding: 0 !important;
	box-sizing: border-box !important;
}

.cloudns-module-title-block {
	display: flex !important;
	align-items: center !important;
	gap: 10px !important;
	min-height: 34px !important;
	margin: 0 !important;
	padding: 0 !important;
}

.cloudns-module-title {
	margin: 0 !important;
	color: #333333 !important;
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

.cloudns-header-tools {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 10px !important;
	margin: 0 !important;
	padding: 0 !important;
}

ul#cloudnsSettingsMenu {
	margin: 0 0 10px 0 !important;
}

#cloudnsMobileSettingsMenu {
	margin: 0 0 10px 0 !important;
}

#cloudnsSettingsMenu + .clear,
#cloudnsMobileSettingsMenu + .clear {
	height: 0 !important;
	margin: 0 !important;
	padding: 0 !important;
	line-height: 0 !important;
}

#cloudnsSettingsMenu + .clear + br,
#cloudnsMobileSettingsMenu + .clear + br {
	display: none !important;
}

.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-mailforward-mx-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
.cloudns-form-panel,
.cloudns-record-form-panel,
.cloudns-forward-form-panel,
.newZoneContainer,
form#recordsForm.recordsForm:not(.cloudns-record-filter) {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	color: #333333 !important;
	box-sizing: border-box !important;
	margin-top: 0 !important;
	margin-bottom: 14px !important;
	padding: 14px !important;
}

.cloudns-body-panel:first-child,
.cloudns-zones-panel:first-child,
.newZoneContainer:first-child {
	margin-top: 0 !important;
}

.cloudns-body-panel > :first-child,
.cloudns-zones-panel > :first-child,
.cloudns-import-panel > :first-child,
.cloudns-soa-panel > :first-child,
.cloudns-statistics-panel > :first-child,
.cloudns-dnssec-panel > :first-child,
.cloudns-mailforward-panel > :first-child,
.cloudns-export-panel > :first-child,
.cloudns-free-ssl-panel > :first-child,
.cloudns-zone-transfers-panel > :first-child,
.cloudns-parked-panel > :first-child,
.cloudns-updated-panel > :first-child,
.cloudns-form-panel > :first-child,
.newZoneContainer > :first-child {
	margin-top: 0 !important;
}

.cloudns-body-panel > :last-child,
.cloudns-zones-panel > :last-child,
.cloudns-import-panel > :last-child,
.cloudns-soa-panel > :last-child,
.cloudns-statistics-panel > :last-child,
.cloudns-dnssec-panel > :last-child,
.cloudns-mailforward-panel > :last-child,
.cloudns-export-panel > :last-child,
.cloudns-free-ssl-panel > :last-child,
.cloudns-zone-transfers-panel > :last-child,
.cloudns-parked-panel > :last-child,
.cloudns-updated-panel > :last-child,
.cloudns-form-panel > :last-child,
.newZoneContainer > :last-child {
	margin-bottom: 0 !important;
}

.cloudns-zones-heading-row,
.cloudns-record-toolbar,
.cloudns-mailforward-toolbar,
.cloudns-section-tools,
.cloudns-export-actions,
.cloudns-free-ssl-actions,
.cloudns-zone-transfer-add,
.cloudns-updated-actions,
.cloudns-bulk-actions-row,
.cloudns-mailforward-actions-row,
.newZoneButtonsContainer {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	box-sizing: border-box !important;
}

.cloudns-zones-heading-row,
.cloudns-record-toolbar,
.cloudns-mailforward-toolbar,
.cloudns-section-tools {
	justify-content: space-between !important;
	margin: 0 0 12px 0 !important;
}

.cloudns-statistics-links {
	margin: 0 0 12px 0 !important;
}

.notification,
.cloudns-response,
.cloudns-zone-transfer-response,
.cloudns-free-ssl-response,
.cloudns-export-notice,
.cloudns-export-error {
	margin-top: 0 !important;
	margin-bottom: 14px !important;
}

@media only screen and (max-width: 870px) {
	.cloudns-module-header {
		align-items: flex-start !important;
		flex-direction: column !important;
	}
	.cloudns-header-tools,
	.cloudns-global-domain-switcher {
		width: 100% !important;
		justify-content: flex-start !important;
	}
}

@media only screen and (max-width: 650px) {
	.cloudns-body-panel,
	.cloudns-zones-panel,
	.cloudns-import-panel,
	.cloudns-soa-panel,
	.cloudns-statistics-panel,
	.cloudns-dnssec-panel,
	.cloudns-mailforward-panel,
	.cloudns-mailforward-mx-panel,
	.cloudns-export-panel,
	.cloudns-free-ssl-panel,
	.cloudns-zone-transfers-panel,
	.cloudns-parked-panel,
	.cloudns-updated-panel,
	.cloudns-form-panel,
	.newZoneContainer,
	form#recordsForm.recordsForm:not(.cloudns-record-filter) {
		padding: 10px !important;
	}
	.cloudns-zones-heading-row,
	.cloudns-record-toolbar,
	.cloudns-mailforward-toolbar,
	.cloudns-section-tools,
	.cloudns-export-actions,
	.cloudns-free-ssl-actions,
	.cloudns-zone-transfer-add,
	.cloudns-updated-actions,
	.cloudns-bulk-actions-row,
	.cloudns-mailforward-actions-row,
	.newZoneButtonsContainer {
		align-items: stretch !important;
		flex-direction: column !important;
	}
}



/* DM color/accent consistency pass
   Orange is the module action/link/focus accent.
   Blue is reserved for informational notices.
   Red is reserved for destructive/error states.
   Zones List domain links keep the approved orange text with a slight darken-on-hover. */
.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-mailforward-mx-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
.cloudns-form-panel,
.cloudns-record-form-panel,
.cloudns-forward-form-panel,
.newZoneContainer {
	color: #333333 !important;
}

.cloudns-body-panel .table > thead > tr > th,
.cloudns-body-panel .table > tbody > tr > th,
.cloudns-zones-panel .table > thead > tr > th,
.cloudns-zones-panel .table > tbody > tr > th,
.cloudns-import-panel .table > thead > tr > th,
.cloudns-import-panel .table > tbody > tr > th,
.cloudns-soa-panel .table > thead > tr > th,
.cloudns-soa-panel .table > tbody > tr > th,
.cloudns-statistics-panel .table > thead > tr > th,
.cloudns-statistics-panel .table > tbody > tr > th,
.cloudns-dnssec-panel .table > thead > tr > th,
.cloudns-dnssec-panel .table > tbody > tr > th,
.cloudns-mailforward-panel .table > thead > tr > th,
.cloudns-mailforward-panel .table > tbody > tr > th,
.cloudns-export-panel .table > thead > tr > th,
.cloudns-export-panel .table > tbody > tr > th,
.cloudns-free-ssl-panel .table > thead > tr > th,
.cloudns-free-ssl-panel .table > tbody > tr > th,
.cloudns-zone-transfers-panel .table > thead > tr > th,
.cloudns-zone-transfers-panel .table > tbody > tr > th,
.cloudns-parked-panel .table > thead > tr > th,
.cloudns-parked-panel .table > tbody > tr > th,
.cloudns-updated-panel .table > thead > tr > th,
.cloudns-updated-panel .table > tbody > tr > th,
#zones-list th {
	background: #f7f7f7 !important;
	background-color: #f7f7f7 !important;
	background-image: none !important;
	border-color: #dddddd !important;
	color: #333333 !important;
}

.cloudns-body-panel .table > tbody > tr > td,
.cloudns-zones-panel .table > tbody > tr > td,
.cloudns-import-panel .table > tbody > tr > td,
.cloudns-soa-panel .table > tbody > tr > td,
.cloudns-statistics-panel .table > tbody > tr > td,
.cloudns-dnssec-panel .table > tbody > tr > td,
.cloudns-mailforward-panel .table > tbody > tr > td,
.cloudns-export-panel .table > tbody > tr > td,
.cloudns-free-ssl-panel .table > tbody > tr > td,
.cloudns-zone-transfers-panel .table > tbody > tr > td,
.cloudns-parked-panel .table > tbody > tr > td,
.cloudns-updated-panel .table > tbody > tr > td,
#zones-list td {
	background-image: none !important;
	border-color: #eeeeee !important;
	color: #333333 !important;
}

.cloudns-body-panel .table-hover > tbody > tr:hover > td,
.cloudns-body-panel .table-hover > tbody > tr:hover > th,
.cloudns-zones-panel .table-hover > tbody > tr:hover > td,
.cloudns-zones-panel .table-hover > tbody > tr:hover > th,
.cloudns-statistics-panel .table-hover > tbody > tr:hover > td,
.cloudns-statistics-panel .table-hover > tbody > tr:hover > th,
.cloudns-mailforward-panel .table-hover > tbody > tr:hover > td,
.cloudns-mailforward-panel .table-hover > tbody > tr:hover > th,
.cloudns-zone-transfers-panel .table-hover > tbody > tr:hover > td,
.cloudns-zone-transfers-panel .table-hover > tbody > tr:hover > th,
.cloudns-free-ssl-panel .table-hover > tbody > tr:hover > td,
.cloudns-free-ssl-panel .table-hover > tbody > tr:hover > th,
.cloudns-parked-panel .table-hover > tbody > tr:hover > td,
.cloudns-parked-panel .table-hover > tbody > tr:hover > th,
#zones-list.table-hover > tbody > tr:hover > td,
#zones-list.table-hover > tbody > tr:hover > th {
	background-color: #fff8ec !important;
	color: #333333 !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon),
.newZoneContainer a:not(.btn):not(.cloudns-action-icon) {
	color: #d75b0b !important;
	text-decoration: underline !important;
	text-underline-offset: 2px !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon):focus,
.newZoneContainer a:not(.btn):not(.cloudns-action-icon):hover,
.newZoneContainer a:not(.btn):not(.cloudns-action-icon):focus {
	color: #a94708 !important;
	text-decoration: underline !important;
}

/* Explicitly include the standalone DNS Zones / Zones List page. */
.cloudns-zones-panel #zones-list a:not(.btn):not(.cloudns-action-icon),
#zones-list a:not(.btn):not(.cloudns-action-icon) {
	color: #ef9846 !important;
	text-decoration: none !important;
	font-weight: 600 !important;
}

.cloudns-zones-panel #zones-list a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-zones-panel #zones-list a:not(.btn):not(.cloudns-action-icon):focus,
#zones-list a:not(.btn):not(.cloudns-action-icon):hover,
#zones-list a:not(.btn):not(.cloudns-action-icon):focus {
	color: #d8873f !important;
	text-decoration: underline !important;
	text-underline-offset: 2px !important;
}

.cloudns-body-panel input.form-control:focus,
.cloudns-body-panel select.form-control:focus,
.cloudns-body-panel textarea.form-control:focus,
.cloudns-zones-panel input.form-control:focus,
.cloudns-zones-panel select.form-control:focus,
.cloudns-zones-panel textarea.form-control:focus,
.cloudns-import-panel input.form-control:focus,
.cloudns-import-panel select.form-control:focus,
.cloudns-import-panel textarea.form-control:focus,
.cloudns-soa-panel input.form-control:focus,
.cloudns-soa-panel select.form-control:focus,
.cloudns-soa-panel textarea.form-control:focus,
.cloudns-statistics-panel input.form-control:focus,
.cloudns-statistics-panel select.form-control:focus,
.cloudns-statistics-panel textarea.form-control:focus,
.cloudns-dnssec-panel input.form-control:focus,
.cloudns-dnssec-panel select.form-control:focus,
.cloudns-dnssec-panel textarea.form-control:focus,
.cloudns-mailforward-panel input.form-control:focus,
.cloudns-mailforward-panel select.form-control:focus,
.cloudns-mailforward-panel textarea.form-control:focus,
.cloudns-export-panel input.form-control:focus,
.cloudns-export-panel select.form-control:focus,
.cloudns-export-panel textarea.form-control:focus,
.cloudns-free-ssl-panel input.form-control:focus,
.cloudns-free-ssl-panel select.form-control:focus,
.cloudns-free-ssl-panel textarea.form-control:focus,
.cloudns-zone-transfers-panel input.form-control:focus,
.cloudns-zone-transfers-panel select.form-control:focus,
.cloudns-zone-transfers-panel textarea.form-control:focus,
.cloudns-parked-panel input.form-control:focus,
.cloudns-parked-panel select.form-control:focus,
.cloudns-parked-panel textarea.form-control:focus,
.newZoneContainer input.form-control:focus,
.newZoneContainer select.form-control:focus,
.newZoneContainer textarea.form-control:focus {
	border-color: #f7941d !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18) !important;
	outline: none !important;
}

.notification,
.cloudns-response:not(.cloudns-response-error),
.cloudns-mailforward-response:not(.cloudns-mailforward-response-error),
.cloudns-free-ssl-response:not(.cloudns-free-ssl-response-error),
.cloudns-zone-transfer-response:not(.cloudns-zone-transfer-response-error),
.cloudns-zone-transfer-notice,
.cloudns-parked-response:not(.cloudns-parked-response-error),
.cloudns-export-notice,
.cloudns-updated-empty {
	background: #f5f8ff !important;
	background-color: #f5f8ff !important;
	background-image: none !important;
	border: 1px solid #b8c7ff !important;
	border-radius: 6px !important;
	color: #30446c !important;
}

.cloudns-response-error,
.cloudns-mailforward-response-error,
.cloudns-free-ssl-response-error,
.cloudns-zone-transfer-response-error,
.cloudns-parked-response-error,
.cloudns-export-error {
	background: #fff1f1 !important;
	background-color: #fff1f1 !important;
	background-image: none !important;
	border: 1px solid #f0c8c8 !important;
	border-radius: 6px !important;
	color: #8f3634 !important;
}

.cloudns-page-status,
.cloudns-updated-summary,
.cloudns-updated-server-meta {
	color: #333333 !important;
}



/* DM typography / forms / tables pass
   Standardize readable text, labels, fields, table density, and helper text.
   Includes standalone DNS Zones / Zones List page. */
.cloudns-module-header,
.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-mailforward-mx-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
.cloudns-form-panel,
.cloudns-record-form-panel,
.cloudns-forward-form-panel,
.newZoneContainer {
	font-size: 13px !important;
	line-height: 1.45 !important;
	color: #333333 !important;
}

.cloudns-module-title {
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
	letter-spacing: 0 !important;
}

.cloudns-body-panel h3,
.cloudns-zones-panel h3,
.cloudns-import-panel h3,
.cloudns-soa-panel h3,
.cloudns-statistics-panel h3,
.cloudns-dnssec-panel h3,
.cloudns-mailforward-panel h3,
.cloudns-export-panel h3,
.cloudns-free-ssl-panel h3,
.cloudns-zone-transfers-panel h3,
.cloudns-parked-panel h3,
.cloudns-updated-panel h3,
.newZoneContainer h3 {
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.3 !important;
	margin: 0 0 12px 0 !important;
	color: #333333 !important;
}

.cloudns-body-panel h4,
.cloudns-zones-panel h4,
.cloudns-import-panel h4,
.cloudns-soa-panel h4,
.cloudns-statistics-panel h4,
.cloudns-dnssec-panel h4,
.cloudns-mailforward-panel h4,
.cloudns-export-panel h4,
.cloudns-free-ssl-panel h4,
.cloudns-zone-transfers-panel h4,
.cloudns-parked-panel h4,
.cloudns-updated-panel h4,
.newZoneContainer h4 {
	font-size: 16px !important;
	font-weight: 700 !important;
	line-height: 1.3 !important;
	margin: 0 0 12px 0 !important;
	color: #333333 !important;
}

.cloudns-body-panel label,
.cloudns-zones-panel label,
.cloudns-import-panel label,
.cloudns-soa-panel label,
.cloudns-statistics-panel label,
.cloudns-dnssec-panel label,
.cloudns-mailforward-panel label,
.cloudns-export-panel label,
.cloudns-free-ssl-panel label,
.cloudns-zone-transfers-panel label,
.cloudns-parked-panel label,
.cloudns-updated-panel label,
.newZoneContainer label,
#recordsForm label,
.recordsForm label {
	display: inline-block;
	max-width: 100%;
	margin-bottom: 6px !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 600 !important;
	line-height: 1.35 !important;
}

.cloudns-body-panel .small,
.cloudns-body-panel small,
.cloudns-zones-panel .small,
.cloudns-zones-panel small,
.cloudns-import-panel .small,
.cloudns-import-panel small,
.cloudns-soa-panel .small,
.cloudns-soa-panel small,
.cloudns-statistics-panel .small,
.cloudns-statistics-panel small,
.cloudns-dnssec-panel .small,
.cloudns-dnssec-panel small,
.cloudns-mailforward-panel .small,
.cloudns-mailforward-panel small,
.cloudns-export-panel .small,
.cloudns-export-panel small,
.cloudns-free-ssl-panel .small,
.cloudns-free-ssl-panel small,
.cloudns-zone-transfers-panel .small,
.cloudns-zone-transfers-panel small,
.cloudns-parked-panel .small,
.cloudns-parked-panel small,
.cloudns-updated-panel .small,
.cloudns-updated-panel small,
.newZoneContainer .small,
.newZoneContainer small {
	color: #666666 !important;
	font-size: 12px !important;
	line-height: 1.4 !important;
}

.cloudns-body-panel input.form-control,
.cloudns-body-panel select.form-control,
.cloudns-zones-panel input.form-control,
.cloudns-zones-panel select.form-control,
.cloudns-import-panel input.form-control,
.cloudns-import-panel select.form-control,
.cloudns-soa-panel input.form-control,
.cloudns-soa-panel select.form-control,
.cloudns-statistics-panel input.form-control,
.cloudns-statistics-panel select.form-control,
.cloudns-dnssec-panel input.form-control,
.cloudns-dnssec-panel select.form-control,
.cloudns-mailforward-panel input.form-control,
.cloudns-mailforward-panel select.form-control,
.cloudns-export-panel input.form-control,
.cloudns-export-panel select.form-control,
.cloudns-free-ssl-panel input.form-control,
.cloudns-free-ssl-panel select.form-control,
.cloudns-zone-transfers-panel input.form-control,
.cloudns-zone-transfers-panel select.form-control,
.cloudns-parked-panel input.form-control,
.cloudns-parked-panel select.form-control,
.cloudns-updated-panel input.form-control,
.cloudns-updated-panel select.form-control,
.newZoneContainer input.form-control,
.newZoneContainer select.form-control,
#recordsForm input.form-control,
#recordsForm select.form-control,
.recordsForm input.form-control,
.recordsForm select.form-control {
	min-height: 34px !important;
	height: 34px !important;
	padding: 6px 10px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.35 !important;
	box-sizing: border-box !important;
	box-shadow: none !important;
}

.cloudns-body-panel textarea.form-control,
.cloudns-zones-panel textarea.form-control,
.cloudns-import-panel textarea.form-control,
.cloudns-soa-panel textarea.form-control,
.cloudns-statistics-panel textarea.form-control,
.cloudns-dnssec-panel textarea.form-control,
.cloudns-mailforward-panel textarea.form-control,
.cloudns-export-panel textarea.form-control,
.cloudns-free-ssl-panel textarea.form-control,
.cloudns-zone-transfers-panel textarea.form-control,
.cloudns-parked-panel textarea.form-control,
.cloudns-updated-panel textarea.form-control,
.newZoneContainer textarea.form-control,
#recordsForm textarea.form-control,
.recordsForm textarea.form-control {
	min-height: 120px !important;
	padding: 8px 10px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	box-sizing: border-box !important;
	box-shadow: none !important;
}

.cloudns-import-panel textarea.form-control,
.importForm textarea {
	min-height: 320px !important;
}

.cloudns-body-panel input[type="checkbox"],
.cloudns-zones-panel input[type="checkbox"],
.cloudns-import-panel input[type="checkbox"],
.cloudns-soa-panel input[type="checkbox"],
.cloudns-statistics-panel input[type="checkbox"],
.cloudns-dnssec-panel input[type="checkbox"],
.cloudns-mailforward-panel input[type="checkbox"],
.cloudns-export-panel input[type="checkbox"],
.cloudns-free-ssl-panel input[type="checkbox"],
.cloudns-zone-transfers-panel input[type="checkbox"],
.cloudns-parked-panel input[type="checkbox"],
.cloudns-updated-panel input[type="checkbox"],
.newZoneContainer input[type="checkbox"],
.cloudns-body-panel input[type="radio"],
.cloudns-zones-panel input[type="radio"],
.cloudns-import-panel input[type="radio"],
.cloudns-soa-panel input[type="radio"],
.cloudns-statistics-panel input[type="radio"],
.cloudns-dnssec-panel input[type="radio"],
.cloudns-mailforward-panel input[type="radio"],
.cloudns-export-panel input[type="radio"],
.cloudns-free-ssl-panel input[type="radio"],
.cloudns-zone-transfers-panel input[type="radio"],
.cloudns-parked-panel input[type="radio"],
.cloudns-updated-panel input[type="radio"],
.newZoneContainer input[type="radio"] {
	margin: 2px 6px 0 0 !important;
	vertical-align: middle !important;
}

.cloudns-body-panel table,
.cloudns-zones-panel table,
.cloudns-import-panel table,
.cloudns-soa-panel table,
.cloudns-statistics-panel table,
.cloudns-dnssec-panel table,
.cloudns-mailforward-panel table,
.cloudns-export-panel table,
.cloudns-free-ssl-panel table,
.cloudns-zone-transfers-panel table,
.cloudns-parked-panel table,
.cloudns-updated-panel table,
.newZoneContainer table,
#zones-list {
	width: 100% !important;
	margin: 0 !important;
	border-collapse: collapse !important;
	font-size: 13px !important;
	line-height: 1.4 !important;
}

.cloudns-body-panel .table > thead > tr > th,
.cloudns-body-panel .table > tbody > tr > th,
.cloudns-zones-panel .table > thead > tr > th,
.cloudns-zones-panel .table > tbody > tr > th,
.cloudns-import-panel .table > thead > tr > th,
.cloudns-import-panel .table > tbody > tr > th,
.cloudns-soa-panel .table > thead > tr > th,
.cloudns-soa-panel .table > tbody > tr > th,
.cloudns-statistics-panel .table > thead > tr > th,
.cloudns-statistics-panel .table > tbody > tr > th,
.cloudns-dnssec-panel .table > thead > tr > th,
.cloudns-dnssec-panel .table > tbody > tr > th,
.cloudns-mailforward-panel .table > thead > tr > th,
.cloudns-mailforward-panel .table > tbody > tr > th,
.cloudns-export-panel .table > thead > tr > th,
.cloudns-export-panel .table > tbody > tr > th,
.cloudns-free-ssl-panel .table > thead > tr > th,
.cloudns-free-ssl-panel .table > tbody > tr > th,
.cloudns-zone-transfers-panel .table > thead > tr > th,
.cloudns-zone-transfers-panel .table > tbody > tr > th,
.cloudns-parked-panel .table > thead > tr > th,
.cloudns-parked-panel .table > tbody > tr > th,
.cloudns-updated-panel .table > thead > tr > th,
.cloudns-updated-panel .table > tbody > tr > th,
#zones-list th {
	padding: 9px 10px !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	text-align: left !important;
	vertical-align: middle !important;
	white-space: nowrap !important;
}

.cloudns-body-panel .table > tbody > tr > td,
.cloudns-zones-panel .table > tbody > tr > td,
.cloudns-import-panel .table > tbody > tr > td,
.cloudns-soa-panel .table > tbody > tr > td,
.cloudns-statistics-panel .table > tbody > tr > td,
.cloudns-dnssec-panel .table > tbody > tr > td,
.cloudns-mailforward-panel .table > tbody > tr > td,
.cloudns-export-panel .table > tbody > tr > td,
.cloudns-free-ssl-panel .table > tbody > tr > td,
.cloudns-zone-transfers-panel .table > tbody > tr > td,
.cloudns-parked-panel .table > tbody > tr > td,
.cloudns-updated-panel .table > tbody > tr > td,
#zones-list td {
	padding: 9px 10px !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	vertical-align: middle !important;
}

.cloudns-body-panel .text-right,
.cloudns-zones-panel .text-right,
.cloudns-mailforward-panel .text-right,
.cloudns-records-panel .text-right,
#zones-list .text-right {
	text-align: right !important;
}

.cloudns-action-col,
.action-col,
.cloudns-sortable .action-col,
#zones-list .zones-options {
	white-space: nowrap !important;
}

.cloudns-action-icon {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 26px !important;
	height: 26px !important;
	min-width: 26px !important;
	margin: 0 1px !important;
	border-radius: 4px !important;
	font-size: 14px !important;
	line-height: 1 !important;
	text-align: center !important;
	vertical-align: middle !important;
}

.cloudns-response,
.cloudns-mailforward-response,
.cloudns-free-ssl-response,
.cloudns-zone-transfer-response,
.cloudns-zone-transfer-notice,
.cloudns-parked-response,
.cloudns-export-error,
.cloudns-updated-empty,
.notification {
	font-size: 13px !important;
	line-height: 1.45 !important;
	padding: 10px 12px !important;
}

.cloudns-record-toolbar,
.cloudns-bulk-actions-row,
.cloudns-mailforward-actions-row,
.cloudns-export-actions,
.cloudns-free-ssl-actions,
.cloudns-zone-transfer-add,
.newZoneButtonsContainer {
	font-size: 13px !important;
	line-height: 1.35 !important;
}

.cloudns-record-toolbar label,
.cloudns-bulk-actions-row label,
.cloudns-export-actions label,
.cloudns-free-ssl-actions label,
.cloudns-zone-transfer-add label,
.newZoneButtonsContainer label {
	margin-bottom: 0 !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-body-panel .table > thead > tr > th,
	.cloudns-body-panel .table > tbody > tr > th,
	.cloudns-zones-panel .table > thead > tr > th,
	.cloudns-zones-panel .table > tbody > tr > th,
	.cloudns-mailforward-panel .table > thead > tr > th,
	.cloudns-mailforward-panel .table > tbody > tr > th,
	#zones-list th,
	.cloudns-body-panel .table > tbody > tr > td,
	.cloudns-zones-panel .table > tbody > tr > td,
	.cloudns-mailforward-panel .table > tbody > tr > td,
	#zones-list td {
		padding: 8px 8px !important;
		font-size: 12px !important;
	}

	.cloudns-module-title {
		font-size: 17px !important;
	}
}



/* DM Zones List consistency pass
   Align standalone Zones List page with the rest of the module tables/buttons. */
.cloudns-zones-heading-row .cloudns-module-title {
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

#zones-list {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	overflow: hidden !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	box-shadow: none !important;
}

#zones-list thead th {
	background: #f7f7f7 !important;
	background-color: #f7f7f7 !important;
	background-image: none !important;
	border-bottom: 1px solid #dddddd !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	padding: 9px 10px !important;
	vertical-align: middle !important;
}

#zones-list tbody td {
	border-top: 1px solid #eeeeee !important;
	padding: 9px 10px !important;
	vertical-align: middle !important;
}

#zones-list tbody tr:first-child td {
	border-top: 0 !important;
}

#zones-list .zones-actions-header,
#zones-list td.text-right {
	text-align: right !important;
}

.cloudns-zones-panel #zones-list .zones-options {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 8px !important;
	flex-wrap: nowrap !important;
	white-space: nowrap !important;
}

.cloudns-zones-panel #zones-list .zones-options .btn {
	margin: 0 !important;
	vertical-align: middle !important;
}

.cloudns-zones-heading-row .backToZones {
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
}

.cloudns-zones-heading-row .backToZones li {
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
}

.cloudns-zones-heading-row .cloudns-add-button {
	min-width: 72px !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-panel #zones-list .zones-options {
		display: none !important;
	}

	#zones-list .zones-actions-header {
		width: 58px !important;
	}
}



/* DM Zones counter
   Match the Records count indicator style for package zone usage. */
.cloudns-zones-title-group {
	display: inline-flex !important;
	align-items: center !important;
	gap: 10px !important;
	flex-wrap: wrap !important;
	min-width: 0 !important;
}

.cloudns-zones-count-indicator {
	display: inline-flex !important;
	align-items: center !important;
	height: var(--cloudns-toolbar-height, 34px) !important;
	padding: 0 10px !important;
	border: 1px solid #dddddd !important;
	border-radius: 4px !important;
	background: #fafafa !important;
	color: #444444 !important;
	font-size: 12px !important;
	font-weight: 600 !important;
	line-height: 1 !important;
	white-space: nowrap !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-title-group {
		width: 100% !important;
		justify-content: space-between !important;
	}
}



/* DM Zones counter/action alignment
   Place Zones usage next to +Add and align header/right actions with Manage/Delete. */
.cloudns-zones-heading-actions {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 8px !important;
	margin-left: auto !important;
	padding-right: 10px !important;
	white-space: nowrap !important;
}

.cloudns-zones-heading-actions .backToZones {
	display: inline-flex !important;
	align-items: center !important;
	margin: 0 !important;
	padding: 0 !important;
	width: auto !important;
}

.cloudns-zones-heading-actions .backToZones li {
	display: inline-flex !important;
	align-items: center !important;
	margin: 0 !important;
	padding: 0 !important;
}

.cloudns-zones-heading-actions .cloudns-add-button {
	margin: 0 !important;
}

#zones-list .zones-actions-header,
#zones-list .zones-actions-cell {
	width: 168px !important;
	min-width: 168px !important;
	max-width: 168px !important;
	text-align: right !important;
	white-space: nowrap !important;
	padding-right: 10px !important;
}

#zones-list .zones-actions-header {
	vertical-align: middle !important;
}

#zones-list .zones-actions-cell .zones-options {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 8px !important;
	width: 100% !important;
	white-space: nowrap !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-heading-actions {
		width: 100% !important;
		justify-content: space-between !important;
		padding-right: 0 !important;
		white-space: normal !important;
	}

	.cloudns-zones-heading-actions .backToZones,
	.cloudns-zones-heading-actions .backToZones .btn {
		width: auto !important;
	}

	#zones-list .zones-actions-header,
	#zones-list .zones-actions-cell {
		width: 58px !important;
		min-width: 58px !important;
		max-width: 58px !important;
	}
}



/* DM page-by-page fix pass 1: align Zones List top actions with row actions. */
.cloudns-zones-panel {
	--cloudns-zones-actions-width: 190px;
}

.cloudns-zones-heading-row {
	display: grid !important;
	grid-template-columns: minmax(0, 1fr) var(--cloudns-zones-actions-width) !important;
	align-items: center !important;
	column-gap: 12px !important;
}

.cloudns-zones-heading-actions {
	width: var(--cloudns-zones-actions-width) !important;
	justify-content: flex-end !important;
	padding-right: 10px !important;
	box-sizing: border-box !important;
}

#zones-list .zones-actions-header,
#zones-list .zones-actions-cell {
	width: var(--cloudns-zones-actions-width) !important;
	min-width: var(--cloudns-zones-actions-width) !important;
	max-width: var(--cloudns-zones-actions-width) !important;
	text-align: right !important;
	padding-right: 10px !important;
	box-sizing: border-box !important;
}

#zones-list .zones-actions-header {
	text-align: right !important;
}

#zones-list .zones-actions-cell .zones-options {
	justify-content: flex-end !important;
	width: 100% !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-heading-row {
		display: flex !important;
		flex-direction: column !important;
		align-items: stretch !important;
	}

	.cloudns-zones-heading-actions {
		width: 100% !important;
		padding-right: 0 !important;
	}

	#zones-list .zones-actions-header,
	#zones-list .zones-actions-cell {
		width: 58px !important;
		min-width: 58px !important;
		max-width: 58px !important;
	}
}



/* DM page-by-page fix pass 2: exact Zones action-column alignment. */
.cloudns-zones-panel {
	--cloudns-zones-actions-width: 130px;
	--cloudns-zones-actions-padding-right: 10px;
}

.cloudns-zones-heading-row {
	display: grid !important;
	grid-template-columns: minmax(0, 1fr) var(--cloudns-zones-actions-width) !important;
	align-items: center !important;
	column-gap: 12px !important;
}

.cloudns-zones-heading-actions {
	width: var(--cloudns-zones-actions-width) !important;
	min-width: var(--cloudns-zones-actions-width) !important;
	max-width: var(--cloudns-zones-actions-width) !important;
	justify-content: flex-end !important;
	padding-right: var(--cloudns-zones-actions-padding-right) !important;
	box-sizing: border-box !important;
	margin-left: 0 !important;
}

#zones-list .zones-actions-header,
#zones-list .zones-actions-cell {
	width: var(--cloudns-zones-actions-width) !important;
	min-width: var(--cloudns-zones-actions-width) !important;
	max-width: var(--cloudns-zones-actions-width) !important;
	text-align: right !important;
	padding-right: var(--cloudns-zones-actions-padding-right) !important;
	box-sizing: border-box !important;
}

#zones-list .zones-actions-header {
	text-align: right !important;
}

#zones-list .zones-actions-cell .zones-options {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 8px !important;
	width: auto !important;
	margin-left: auto !important;
	white-space: nowrap !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-heading-row {
		display: flex !important;
		flex-direction: column !important;
		align-items: stretch !important;
	}

	.cloudns-zones-heading-actions {
		width: 100% !important;
		min-width: 0 !important;
		max-width: none !important;
		padding-right: 0 !important;
		justify-content: space-between !important;
	}

	#zones-list .zones-actions-header,
	#zones-list .zones-actions-cell {
		width: 58px !important;
		min-width: 58px !important;
		max-width: 58px !important;
	}
}



/* DM page-by-page fix pass 3: align Actions text with Delete edge. */
.cloudns-zones-panel {
	--cloudns-zones-actions-width: 150px;
	--cloudns-zones-actions-padding-right: 10px;
	--cloudns-zones-delete-width: 58px;
}

.cloudns-zones-heading-row {
	display: grid !important;
	grid-template-columns: minmax(0, 1fr) auto !important;
	align-items: center !important;
	column-gap: 12px !important;
}

.cloudns-zones-heading-actions {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 8px !important;
	padding-right: var(--cloudns-zones-actions-padding-right) !important;
	box-sizing: border-box !important;
	margin-left: auto !important;
}

#zones-list .zones-actions-header,
#zones-list .zones-actions-cell {
	width: var(--cloudns-zones-actions-width) !important;
	min-width: var(--cloudns-zones-actions-width) !important;
	max-width: var(--cloudns-zones-actions-width) !important;
	text-align: right !important;
	padding-right: var(--cloudns-zones-actions-padding-right) !important;
	box-sizing: border-box !important;
	white-space: nowrap !important;
}

#zones-list .zones-actions-header .cloudns-zones-actions-label {
	display: inline-flex !important;
	justify-content: center !important;
	align-items: center !important;
	width: var(--cloudns-zones-delete-width) !important;
	margin-left: auto !important;
	text-align: center !important;
}

#zones-list .zones-actions-cell .zones-options {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 8px !important;
	width: auto !important;
	margin-left: auto !important;
	white-space: nowrap !important;
}

#zones-list .zones-actions-cell .zones-options .cloudns-btn-danger {
	min-width: var(--cloudns-zones-delete-width) !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-zones-heading-row {
		display: flex !important;
		flex-direction: column !important;
		align-items: stretch !important;
	}

	.cloudns-zones-heading-actions {
		width: 100% !important;
		padding-right: 0 !important;
		justify-content: space-between !important;
	}

	#zones-list .zones-actions-header,
	#zones-list .zones-actions-cell {
		width: 58px !important;
		min-width: 58px !important;
		max-width: 58px !important;
	}
}



/* DM zone limit behavior
   Replace +Add with disabled Limit Reached when package zone limit is reached. */
.cloudns-zones-heading-actions .cloudns-zone-limit-reached,
.cloudns-zones-heading-actions .cloudns-zone-limit-reached:hover,
.cloudns-zones-heading-actions .cloudns-zone-limit-reached:focus,
.cloudns-zones-heading-actions .cloudns-zone-limit-reached:active {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	height: 34px !important;
	min-height: 34px !important;
	padding: 0 12px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background: #eeeeee !important;
	background-color: #eeeeee !important;
	background-image: none !important;
	color: #777777 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	text-shadow: none !important;
	box-shadow: none !important;
	cursor: not-allowed !important;
	opacity: 1 !important;
	pointer-events: none !important;
	white-space: nowrap !important;
}

.cloudns-zones-heading-actions .cloudns-zone-limit-reached::before {
	content: "";
}

</style>

<div class="cloudns-body-panel cloudns-zones-panel">
	<div class="cloudns-zones-heading-row">
		<div class="cloudns-zones-title-group">
			<h4 class="cloudns-module-title">Zones List</h4>
		</div>
		<div class="cloudns-zones-heading-actions">
			<div class="cloudns-zones-count-indicator" aria-label="Zones count">Zones: {if isset($zonesCount)}{$zonesCount}{else}0{/if}{if isset($zonesLimit) && $zonesLimit != '' && $zonesLimit != '-1'}/{$zonesLimit}{/if}</div>
			{if $registeredDomains != 'on'}
				<ul class="backToZones">
					<li>
						{if isset($zonesLimit) && $zonesLimit != '' && $zonesLimit != '-1' && isset($zonesCount) && $zonesCount ge $zonesLimit}
							<span class="btn cloudns-add-button cloudns-zone-limit-reached" title="Zone limit reached for this package" aria-disabled="true">Limit Reached</span>
						{else}
							<a class="btn cloudns-add-button cloudns-switch-style-button cloudns-btn-primary" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone">+Add</a>
						{/if}
					</li>
				</ul>
			{/if}
		</div>
	</div>

{if is_array($response) && $response.status eq 'error'}
	<div class="notification">{$response.description}</div>
{/if}
{if !empty($zones) && !isset($zones.status)}
	<table class="table-hover table" id="zones-list">
		<thead>
			<tr>
				<th>Domain</th>
				<th class="text-right zones-actions-header"><span class="cloudns-zones-actions-label">Actions</span></th>
			</tr>
		</thead>
		<tbody>
	{foreach from=$zones item=zone}
		<tr id="zones">
			<td>
				<a title="DNS records of {$zone.name}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone.name}">{$zone.ascii}</a>
			</td>
			<td class="text-right zones-actions-cell">
				<div class="zones-options">
					<a class="btn cloudns-switch-style-button cloudns-btn-secondary" title="DNS records of {$zone.name}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone.name}">Manage</a>
{if $registeredDomains != 'on'}
					<a class="btn btn-danger btn-input-padded-responsive cloudns-btn-danger" title="Delete the DNS zone of {$zone.name}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-zone&zone={$zone.name}" onclick="return confirm('Are you sure you want to delete {$zone.name} and all its records?');">Delete</a>{/if}
				</div>
				<div id="mobile-zones-options" class="dropdown">
					<a href="#" class="dropdown-toggle vDots" data-toggle="dropdown"></a>
					<ul class="dropdown-menu pull-right">
						<li><a title="DNS records of {$zone.name}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone.name}"><span class="cloudns-action-icon cloudns-pencil mr-10">&#9998;</span>Manage</a></li>
						<li>
						{if $registeredDomains != 'on'}
							<a title="Delete the DNS zone of {$zone.name}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-zone&zone={$zone.name}" onclick="return confirm('Are you sure you want to delete {$zone.name} and all its records?');"><span class="cloudns-action-icon cloudns-action-icon-danger mr-10">&#10005;</span>Delete</a>{/if}
						</li>
					</ul>
				</div>
			</td>
		</tr>
	{/foreach}
		</tbody>
	</table>
{/if}
</div>

<?php

/**
 * ClouDNS DNS Manager v1.8
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function cloudns_MetaData() {
    return array(
        'DisplayName' => 'ClouDNS DNS Manager',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
    );
}

require_once dirname(__FILE__) . "/cloudns_core/core.php";

function cloudns_getModuleConfigValue($key, $default = null) {
	$configFile = dirname(__FILE__) . "/cloudns_config.php";
	if (!file_exists($configFile)) {
		return $default;
	}

	$config = include $configFile;
	if (!is_array($config) || !array_key_exists($key, $config)) {
		return $default;
	}

	return $config[$key];
}

function cloudns_configBool($key, $default = false) {
	$value = cloudns_getModuleConfigValue($key, $default);

	if (is_bool($value)) {
		return $value;
	}

	if (is_numeric($value)) {
		return ((int)$value) === 1;
	}

	return in_array(strtolower(trim((string)$value)), array('1', 'true', 'yes', 'on'), true);
}

function cloudns_ConfigOptions($params) {
	$cloudns = Cloudns_Core::inst($params);
	$servers = $cloudns->Servers->getAvailableServers();
	$serverString = $databaseTable = '';
	$tblparams = array(
		'id' => $_POST['id'],
		);
	$product = $cloudns->Database->select('tblproducts', $tblparams);
	if ($product[0]['registeredDomains'] != 'on') {
		$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
		$databaseTable = 'mod_cloudns_zones';
	} else {
		$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
		$databaseTable = 'tbldomains';
	}
	$foDescription = '';

	if ($foColumn == 1) {
		$foDescription = array(
			'Type' => 'text',
			'Size' => '25',
			'Default' => '1',
			'Description' => '<br />How many DNS Failover checks are supported by this plan/product.',
		);
	} else {
		$foDescription = array(
			'Description' => '<div style="color: red;">No "fo_checks" column! The "fo_checks" column needs to be added to "'.$databaseTable.'" table in your database, in order for this option to be available.</div>',
		);
	}

	foreach ($servers as $name) {
		$serverString .= $name . "<br />";
	}
	return array(
		'Zones' => array(
			'Type' => 'text',
			'Size' => '25',
			'Default' => '0',
			'Description' => '<br />How many zones are supported by this plan/product.',
		),
		'Servers' => array(
			'Type' => 'textarea',
			'Rows' => 10,
			'Cols' => 50,
			'Description' => "<br />List of DNS server for the customers to use. One server per row. Server names only. <br />Here is an example, with the default ones:<br /><br />" . $serverString . "<br /> If the list is empty and you didn't specify global servers the default servers of your ClouDNS account will be shown",
			'Default' => '',
		),
		'Registered domains' => array(
			'Type' => 'yesno',
			'Description' => "Check this box if you only want to use this product for the registered domains of the customer. The above zones limit will be ignored and the new limit will be the number of registered domains the customer has. No zones can be added or deleted. Zones for the Registered domains of the customer will be added automatically.",
		),
		'Template zone' => array(
			'Type' => 'text',
			'Size' => '25',
			'Default' => '',
			'Description' => '<br />Zone to be used as records template for new zones.',
		),
		'DNS Failover checks' => $foDescription,
		'Records' => array(
			'Type' => 'text',
			'Size' => '25',
			'Default' => '-1',
			'Description' => '<br />How many records are supported for a DNS zone. Use -1 for unlimited'
		),
		'Forwards' => array(
			'Type' => 'text',
			'Size' => '25',
			'Default' => '0',
			'Description' => '<br />How many email forwards are supported by this plan/product. Use -1 for unlimited.'
		),
	);
}

function cloudns_adminAttachNoticeKey($serviceId) {
	return 'cloudns_admin_attach_existing_zone_' . (int) $serviceId;
}

function cloudns_adminSetAttachNotice($serviceId, $status, $message) {
	if (!isset($_SESSION) || !is_array($_SESSION)) {
		return;
	}

	$_SESSION[cloudns_adminAttachNoticeKey($serviceId)] = array(
		'status' => $status,
		'message' => $message,
	);
}

function cloudns_adminGetAttachNotice($serviceId) {
	if (!isset($_SESSION) || !is_array($_SESSION)) {
		return array();
	}

	$key = cloudns_adminAttachNoticeKey($serviceId);
	if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
		return array();
	}

	$notice = $_SESSION[$key];
	unset($_SESSION[$key]);

	return $notice;
}

function cloudns_adminNormalizeZoneName($zone, $cloudns) {
	$zone = strtolower(rtrim(trim((string) $zone), '.'));
	if ($zone === '') {
		return '';
	}

	$zone = strtolower((string) $cloudns->Helper->getAsciiName($zone));
	return rtrim(trim($zone), '.');
}

function cloudns_adminAttachNoticeHtml(array $notice) {
	if (empty($notice['message'])) {
		return '';
	}

	$status = isset($notice['status']) ? (string) $notice['status'] : 'info';
	$allowedStatuses = array('success', 'warning', 'danger', 'info');
	if (!in_array($status, $allowedStatuses, true)) {
		$status = 'info';
	}

	return '<div class="alert alert-' . $status . '" style="margin:0;">'
		. htmlspecialchars((string) $notice['message'], ENT_QUOTES, 'UTF-8')
		. '</div>';
}

function cloudns_AdminServicesTabFields($params) {
	$cloudns = Cloudns_Core::inst($params);
	$data = $cloudns->Database->select('mod_cloudns_zones', array('serviceid' => $params['serviceid']));
	$notice = cloudns_adminGetAttachNotice($params['serviceid']);
	$fieldsarray = array();

	if (!empty($notice)) {
		$fieldsarray['DNSPlus Zone Attachment'] = cloudns_adminAttachNoticeHtml($notice);
	}

	if (empty($data)) {
		$fieldsarray['Connected DNS Zones'] = 'There are no DNS zones attached to this service.';
	} else {
		$zoneItems = array();
		foreach ($data as $zone) {
			$zoneName = isset($zone['name']) ? (string) $zone['name'] : '';
			if ($zoneName === '') {
				continue;
			}
			$zoneItems[] = '<li>' . htmlspecialchars($zoneName, ENT_QUOTES, 'UTF-8') . '</li>';
		}

		$fieldsarray['Connected DNS Zones'] = empty($zoneItems)
			? 'There are no DNS zones attached to this service.'
			: '<ul style="margin:0;padding-left:20px;">' . implode('', $zoneItems) . '</ul>';
	}

	$fieldsarray['Attach or Create DNSPlus Zone'] =
		'<input type="text" name="modulefields[2]" value="" size="45" maxlength="255" '
		. 'placeholder="example.com" autocomplete="off" />'
		. '<div style="margin-top:8px;">'
		. '<label style="font-weight:normal;margin-bottom:0;">'
		. '<input type="checkbox" name="modulefields[3]" value="create" /> '
		. 'Create the zone in DNSPlus if it does not already exist'
		. '</label>'
		. '</div>'
		. '<div class="help-block" style="margin-bottom:0;">'
		. 'Existing zones are attached without changing their DNS records. '
		. 'When creation is selected, a new master zone is created using this product\'s normal '
		. 'template or DNSPlus nameserver settings and service limits.'
		. '</div>';

	return $fieldsarray;
}

function cloudns_AdminServicesTabFieldsSave($params) {
	if (!isset($_POST['modulefields'][2])) {
		return;
	}

	$submittedZone = trim((string) $_POST['modulefields'][2]);
	if ($submittedZone === '') {
		return;
	}

	$createIfMissing = isset($_POST['modulefields'][3])
		&& in_array(
			strtolower(trim((string) $_POST['modulefields'][3])),
			array('1', 'on', 'yes', 'true', 'create'),
			true
		);

	$cloudns = Cloudns_Core::inst($params);
	$zone = cloudns_adminNormalizeZoneName($submittedZone, $cloudns);

	if (
		$zone === ''
		|| strlen($zone) > 255
		|| strpos($zone, '.') === false
		|| preg_match('/[\s\/?#\\\\]/', $zone)
		|| strpos($zone, '://') !== false
	) {
		cloudns_adminSetAttachNotice(
			$params['serviceid'],
			'danger',
			'Enter a valid DNS zone name only, such as example.com.'
		);
		return;
	}

	try {
		$existingMappings = $cloudns->Controller->getZoneByName($zone);
		if (!empty($existingMappings)) {
			$existingServiceId = isset($existingMappings[0]['serviceid'])
				? (int) $existingMappings[0]['serviceid']
				: 0;

			if ($existingServiceId === (int) $params['serviceid']) {
				cloudns_adminSetAttachNotice(
					$params['serviceid'],
					'info',
					$zone . ' is already attached to this service.'
				);
				return;
			}

			cloudns_adminSetAttachNotice(
				$params['serviceid'],
				'danger',
				$zone . ' is already attached to WHMCS service #' . $existingServiceId . '.'
			);
			return;
		}

		$zoneInfo = $cloudns->Zones->getZoneInfo($zone);
		if (!empty($zoneInfo)) {
			$inserted = $cloudns->Database->insert('mod_cloudns_zones', array(
				'serviceid' => (int) $params['serviceid'],
				'name' => $zone,
			));

			if (!$inserted) {
				cloudns_adminSetAttachNotice(
					$params['serviceid'],
					'danger',
					'The DNSPlus zone was found, but the WHMCS service mapping could not be saved.'
				);
				return;
			}

			$statusText = isset($zoneInfo['status']) && (int) $zoneInfo['status'] === 0
				? ' The DNSPlus zone is currently inactive.'
				: '';

			cloudns_adminSetAttachNotice(
				$params['serviceid'],
				'success',
				$zone . ' was attached to this WHMCS service.' . $statusText
			);
			return;
		}

		if (!$createIfMissing) {
			cloudns_adminSetAttachNotice(
				$params['serviceid'],
				'danger',
				$zone . ' does not currently exist in DNSPlus. Check the create option to create and attach it.'
			);
			return;
		}

		$zoneOption = $cloudns->Controller->defaultZone() ? 3 : 1;
		$servers = $cloudns->Servers->getAvailableServers();
		$zoneExists = array(
			'status' => 'error',
			'description' => 'There is no such DNS zone with the DNS servers',
			'zoneInfo' => array('name' => ''),
		);
		$createResponse = $cloudns->Controller->addNewZone(
			$zone,
			'masterZoneType',
			$zoneOption,
			is_array($servers) ? $servers : array(),
			'',
			$zoneExists
		);

		if (!isset($createResponse['status']) || $createResponse['status'] !== 'success') {
			$description = isset($createResponse['description'])
				? trim((string) $createResponse['description'])
				: 'DNSPlus did not return a successful response.';
			cloudns_adminSetAttachNotice(
				$params['serviceid'],
				'danger',
				'The DNSPlus zone could not be created: ' . $description
			);
			return;
		}

		cloudns_adminSetAttachNotice(
			$params['serviceid'],
			'success',
			$zone . ' was created in DNSPlus and attached to this WHMCS service.'
		);
	} catch (\Throwable $e) {
		logModuleCall(
			'cloudns',
			'AdminServicesTabFieldsSave-AttachOrCreateZone',
			array('serviceid' => (int) $params['serviceid'], 'zone' => $zone),
			$e->getMessage(),
			$e->getTraceAsString()
		);

		cloudns_adminSetAttachNotice(
			$params['serviceid'],
			'danger',
			'The DNSPlus zone could not be attached or created: ' . $e->getMessage()
		);
	}
}


function cloudns_CreateAccount ($params) {
	$cloudns = Cloudns_Core::inst($params);
	
	try {
		
	} catch (Exception $e) {
		// Record the error in WHMCS's module log.
		logModuleCall(
			'cloudns',
			__FUNCTION__,
			$params,
			$e->getMessage(),
			$e->getTraceAsString()
		);
		return $e->getMessage();
	}
	
	return 'success';
}


function cloudns_SuspendAccount ($params) {
	$cloudns = Cloudns_Core::inst($params);
	
	try {
		// make the zones inactive
		$result = $cloudns->Actions->suspendZones();
	} catch (Exception $e) {
		// Record the error in WHMCS's module log.
		logModuleCall(
			'cloudns',
			__FUNCTION__,
			$params,
			$e->getMessage(),
			$e->getTraceAsString()
		);
		return $e->getMessage();
	}
	
	return $result;
}


function cloudns_UnsuspendAccount ($params) {
	$cloudns = Cloudns_Core::inst($params);
	try {
		// make the zones active again
		$result = $cloudns->Actions->unSuspendZones();
	} catch (Exception $e) {
		// Record the error in WHMCS's module log.
		logModuleCall(
			'cloudns',
			__FUNCTION__,
			$params,
			$e->getMessage(),
			$e->getTraceAsString()
		);
		return $e->getMessage();
	}
	return $result;
}


function cloudns_TerminateAccount ($params) {
	$cloudns = Cloudns_Core::inst($params);
	try {
		// delete the zones
		$result = $cloudns->Actions->terminateAccount();
	} catch (Exception $e) {
		// Record the error in WHMCS's module log.
		logModuleCall(
			'cloudns',
			__FUNCTION__,
			$params,
			$e->getMessage(),
			$e->getTraceAsString()
		);
		return $e->getMessage();
	}
	return $result;
}
function cloudns_ChangePackage ($params) {
	$cloudns = Cloudns_Core::inst($params);
	
	try {
		
	} catch (Exception $e) {
		// Record the error in WHMCS's module log.
		logModuleCall(
			'cloudns',
			__FUNCTION__,
			$params,
			$e->getMessage(),
			$e->getTraceAsString()
		);
		return $e->getMessage();
	}
	
	return 'success';
}


function cloudns_ClientArea ($params) {
	$cloudns = Cloudns_Core::inst($params);
	$requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : 'zones';
	
	try {
		if (isset($params['status']) && $params['status'] != 'Active') {
			// returning template, which is forbiding us from
			// using not active Service
			return array(
				'tabOverviewReplacementTemplate' => 'templates/notActive.tpl',
			);
		}
		$zone = $cloudns->Helper->getRequest('zone');
		$response = $zoneInfo = $recordTypes = array();

		// DomainMonger Patch 500:
		// Allow service-list management links to request the DNS Records action directly.
		// If no zone is supplied, resolve to the first available zone instead of falling
		// through to the legacy/default Zones List behavior.
		if ($requestedAction === 'zone-settings' && trim((string)$zone) === '') {
			$domainSwitchZones = $cloudns->Controller->getDomainSwitchZones();
			if (is_array($domainSwitchZones) && !empty($domainSwitchZones) && isset($domainSwitchZones[0]['name']) && trim((string)$domainSwitchZones[0]['name']) !== '') {
				$firstZone = urlencode($domainSwitchZones[0]['name']);
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$firstZone}");
			}

			if ($params['configoption3'] != 'on') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=add-new-zone");
			}
		}
		
		if (!empty($zone)) {
			$zoneExists = $cloudns->Controller->zoneExists($zone);
			
			if (isset($zoneExists['status']) && ($requestedAction != 'add-existing-zone' && $requestedAction != 'add-zone')) {
				// the zone doesn't exist in ClouDNS
				if ($zoneExists['status'] == 'error' || $zoneExists['status'] == 'info') {
					if ($params['configoption3'] == 'on' && empty($cloudns->Controller->getRegisteredDomainByName($zone))) {
						$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
					}
						
						
					$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=add-existing-zone&zone={$zone}");
				// the zone doesn't exist here
				} elseif ($zoneExists['status'] == 'info' && $params['configoption3'] != 'on') {

					$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
				}
			}
		}
		
		$zoneInfo = isset($zoneExists['zoneInfo']) ? $zoneExists['zoneInfo'] : array('name'=>'');
		
		$zoneOwnership = $cloudns->Controller->getZoneByName($zoneInfo['name']);
		if (!empty($zone) && (isset($zoneOwnership[0]['serviceid']) && $zoneOwnership[0]['serviceid'] != $params['serviceid']) && $params['configoption3'] != 'on') {
			$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
		} elseif (!empty($zone) && (!isset($zoneOwnership[0]['serviceid']) || (isset($zoneOwnership[0]['serviceid']) && $zoneOwnership[0]['serviceid'] != $params['serviceid']))) {
			
			if ($params['configoption3'] == 'on') {
				$domainName = !empty($zoneInfo['name']) ? $zoneInfo['name'] : $zone;
				$domain = $cloudns->Controller->getRegisteredDomainByName($domainName);
				if (empty($domain) && ($requestedAction != 'add-existing-zone' && $requestedAction != 'add-zone')) {
					$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
				}
				$service = array();
				if (!empty($domain)) {
					$command = 'GetClientsProducts';
					$postData = array(
						'clientid' => $domain[0]['userid'],
						'serviceid' => $zoneOwnership[0]['serviceid'],
						'status' => true,
					);
					$adminUsername = $cloudns->Configuration->getAdminUser();
					$service = localAPI($command, $postData, $adminUsername);
				}
				
				if ((empty($service) || $service['totalresults'] == 0 || empty($service['products']))  && ($requestedAction != 'add-existing-zone' && $requestedAction != 'add-zone')) {
					$templateFile = 'templates/registered-domain-zone-not-own.tpl';
					
					// adding the below keys, because they are used everywhere and there is no need to add them in each action
					$version = explode('.', $params['whmcsVersion']);
					$templateVariables['serviceid'] = $params['serviceid'];
					$templateVariables['zone'] = $zone;
					$templateVariables['version']=$version[0];
					$templateVariables['theme'] = $params['clientareatemplate'];
					$templateVariables['registeredDomains'] = $params['configoption3'];

					// returning the error template
					return array(
						'tabOverviewReplacementTemplate' => $templateFile,
						'vars' => $templateVariables,
						'cloudAction' => $requestedAction,
					);
				}
			}
		}
		
		if ($requestedAction == 'bulk-add-zones') {
			$bulkZones = $cloudns->Helper->getPost('bulkZones', array());
			$result = $cloudns->Controller->addMasterZonesBulk($bulkZones);
			if (!isset($_SESSION['cloudnsBulkZoneResult']) || !is_array($_SESSION['cloudnsBulkZoneResult'])) {
				$_SESSION['cloudnsBulkZoneResult'] = array();
			}
			$_SESSION['cloudnsBulkZoneResult'][$params['serviceid']] = array(
				'action'=>'bulkMaster',
				'result'=>$result,
			);
			$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=add-new-zone-bulk-master");

		} elseif ($requestedAction == 'bulk-delete-zones') {
			$bulkZones = $cloudns->Helper->getPost('bulkDeleteZones', array());
			$result = $cloudns->Controller->deleteZonesBulk($bulkZones);
			if (!isset($_SESSION['cloudnsBulkZoneResult']) || !is_array($_SESSION['cloudnsBulkZoneResult'])) {
				$_SESSION['cloudnsBulkZoneResult'] = array();
			}
			$_SESSION['cloudnsBulkZoneResult'][$params['serviceid']] = array(
				'action'=>'bulkDelete',
				'result'=>$result,
			);
			$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=add-new-zone-bulk-delete");

		} elseif ($requestedAction == 'add-zone') {
			$zoneType = $cloudns->Helper->getPost('zoneType');
			
			// DomainMonger Patch 1590: master-zone nameservers are automatic.
			$servers = array();
			if ($cloudns->Controller->defaultZone()) {
				$option = 3;
			} else {
				$option = $cloudns->Helper->getPost('newZoneOptions', 1);
			}
			
				
			$sufix = $cloudns->Helper->getPost('zoneSufix');
			$masterIP = $cloudns->Helper->getPost('slaveMasterIp');
			
			if (!empty($sufix)) {
				$zone = $zone.'.'.$sufix;
			}
			$response = $cloudns->Controller->addNewZone($zone, $zoneType, $option, $servers, $masterIP, $zoneExists);
			if ($response['status'] == 'success') {
				if (isset($response['zoneType']) && $response['zoneType'] == 'parkedZoneType') {
					$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=parked-templates&zone={$response['zone']}");
				}
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$response['zone']}");
			} else {
				$version = explode('.', $params['whmcsVersion']);
				$zonesForBulk = $cloudns->Controller->getZones();
				if (!is_array($zonesForBulk) || isset($zonesForBulk['status'])) {
					$zonesForBulk = array();
				}
				$selectedZoneType = 'master';
				if ($zoneType == 'slaveZoneType') {
					$selectedZoneType = 'slave';
				} elseif ($zoneType == 'parkedZoneType') {
					$selectedZoneType = 'parked';
				} elseif ($zoneType == 'masterReverseZoneType') {
					$selectedZoneType = 'masterReverse';
				} elseif ($zoneType == 'slaveReverseZoneType') {
					$selectedZoneType = 'slaveReverse';
				}
				$templateVariables = array(
					'response'=>$response,
					'serversList'=>$cloudns->Servers->getAvailableServers(),
					'zonesForBulk'=>$zonesForBulk,
					'bulkZoneResult'=>array(),
					'selectedZoneType'=>$selectedZoneType,
					'version'=>$version[0],
					'theme'=>$params['clientareatemplate'],
					'templateZone'=>$cloudns->Controller->defaultZone(),
				);
			}
			
			$templateFile = 'templates/add-new-zone.tpl';
			
		} elseif ($requestedAction == 'add-forward') {
			$forward = $cloudns->Helper->getPost('addEmailForward');
			$to = $cloudns->Helper->getPost('addEmailForwardTo');
			$response = $cloudns->Controller->addNewForward($zoneInfo['name'], $forward, $to);
			if ($response['status'] == 'success') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=mail-forwarding&zone={$zoneInfo['name']}");
			}

			$version = explode('.', $params['whmcsVersion']);
			$templateVariables = array(
				'pagetitle' => 'Add Mail Forward - ' . $zoneInfo['name'],
				'response' => $response,
				'settings' => array(
					'source' => $forward,
					'destination' => $to,
				),
				'cloudAction' => 'add-new-forwarding',
				'version'=>$version[0],
				'theme' => $params['clientareatemplate'],
			);
			$templateFile = 'templates/add-new-forwarding.tpl';

		} elseif ($requestedAction == 'add-existing-zone') {
			if ($params['configoption3'] == 'on' && empty($cloudns->Controller->getRegisteredDomainByName($zone))) {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			
			if ($cloudns->Controller->defaultZone()) {
				$option = 3;
			} else {
				$option = $cloudns->Helper->getPost('newZoneOptions', 1);
			}
			$response = $cloudns->Controller->addNewExistingZone($zone, $option);
			if ($response['status'] == 'success') {
				if (isset($response['zoneType']) && $response['zoneType'] == 'parkedZoneType') {
					$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=parked-templates&zone={$response['zone']}");
				}
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$response['zone']}");
			} else {
				$version = explode('.', $params['whmcsVersion']);
				$templateVariables = array(
					'response' => $response,
					'cloudAction' => 'add-new-zone',
					'version'=>$version[0],
					'theme' => $params['clientareatemplate'],
				);
			}
			$templateFile = 'templates/add-new-zone.tpl';
			
		} elseif ($requestedAction == 'zone-settings') {
			if (isset($_POST['mass-edit-ttl']) && isset($_POST['records']) && is_array($_POST['records'])) {
				$templateVariables = $cloudns->Actions->massEditTtl($zoneInfo, $_POST['records'], $cloudns->Helper->getPost('massTtl'));
				$templateFile = 'templates/records.tpl';
			} elseif (isset($_POST['records']) && is_array($_POST['records'])) {
				foreach ($_POST['records'] as $recordId) {
					$cloudns->Controller->deleteRecord($zoneInfo['name'], $recordId);
				}
			}
			if (!isset($templateFile)) {
				// DomainMonger Patch 1821: the DNS Records filter is already a fixed
				// client-side list in records.tpl. Do not block the page on ClouDNS'
				// get-available-record-types endpoint just to validate that filter.
				// Add/Edit Record workflows still use the provider-backed type list.
				$recordTypes = array(
					'A', 'CNAME', 'MX', 'NS', 'SPF', 'SRV', 'TXT', 'WR', 'AAAA',
					'ALIAS', 'CAA', 'CERT', 'DNAME', 'DS', 'HINFO', 'HTTPS', 'LOC',
					'NAPTR', 'OPENPGPKEY', 'PTR', 'RP', 'SMIMEA', 'SSHFP', 'SVCB', 'TLSA'
				);
				$failoverChecks = $params['configoption5'];
			$productid = $params['pid'];
			$requestedType = 'all';
			$recordsLimit = $params['configoption6'];
			$error = $cloudns->Helper->getGet('error');
			
			// DomainMonger Patch 1550: always load the complete zone so the
			// DNS Records type filter can work immediately without a page reload.
			// Preserve a requested type only as the initial client-side filter.
			if (in_array($cloudns->Helper->getPost('recordsType'), $recordTypes, true)) {
				$requestedType = $cloudns->Helper->getPost('recordsType');
			} elseif (in_array($cloudns->Helper->getGet('type'), $recordTypes, true)) {
				$requestedType = $cloudns->Helper->getGet('type');
			}
			
			if ($zoneInfo['type'] == 'slave') {
				$templateFile = 'templates/slave/master-servers.tpl';
			} else {
				$templateFile = 'templates/records.tpl';
			}
			
			$templateVariables = $cloudns->Actions->getSettings($zoneInfo, 'all', $recordTypes, $failoverChecks, $productid);
			$templateVariables['defaultType'] = $requestedType;
			// DomainMonger Patch 1821: Patch 1550 already fetched the complete
			// record set, so derive the count locally instead of making the
			// redundant dns/get-records-count.json API request.
			$templateVariables['recordsCount'] = (isset($templateVariables['records']) && is_array($templateVariables['records']))
				? count($templateVariables['records'])
				: 0;
			$templateVariables['recordsLimit'] = $recordsLimit;
			$templateVariables['error'] = $error;
			
				if (isset($templateVariables['response']) && isset($templateVariables['response']['status']) && ($templateVariables['response']['status'] == 'error' || $templateVariables['response']['status'] == '0')) {
					$templateFile = 'templates/zone-error.tpl';
				}
			}
			
		} elseif ($requestedAction == 'delete-record') {
			$record_id = $cloudns->Helper->getGet('record');
			$templateVariables = $cloudns->Actions->deleteRecord($zoneInfo, $record_id);
			$templateFile = 'templates/records.tpl';
			
		} elseif ($requestedAction == 'delete-forward') {
			$forwardId = $cloudns->Helper->getGet('record');
			$templateVariables = $cloudns->Actions->deleteForward($zoneInfo, $forwardId);
			$templateFile = 'templates/mail-forwarding.tpl';

		} elseif ($requestedAction == 'delete-zone') {
			$templateVariables = $cloudns->Actions->deleteZone($zone);
			$templateFile = 'templates/zones.tpl';
			
		} elseif ($requestedAction == 'bind-settings') {
			$templateVariables = $cloudns->Actions->getBINDSettings($zoneInfo);
			$templateFile = 'templates/slave/slave-bind-settings.tpl';
			
		} elseif ($requestedAction == 'delete-master-servers') {
			$master = $cloudns->Helper->getGet('master_server_id');
			$templateVariables = $cloudns->Actions->deleteMasterServer($zone, $master);
			$templateFile = 'templates/slave/master-servers.tpl';
			
		} elseif ($requestedAction == 'add-master-servers') {
			$master = $cloudns->Helper->getPost('masterIP');
			$templateVariables = $cloudns->Actions->addMasterServer($zone, $master);
			$templateFile = 'templates/slave/master-servers.tpl';
		
		} elseif ($requestedAction == 'add-new-record') {
			$templateVariables = $cloudns->Actions->addNewRecord($zoneInfo, $cloudns->Helper->getRequest('type'), $cloudns->Helper->getRequest('source_record'));
			
			if ($templateVariables['status'] == 'error') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}&error=1");
			} else {
				$templateFile = 'templates/add-new-record.tpl';
			}
		} elseif ($requestedAction == 'add-record') {
			$recordType = $cloudns->Helper->getRequest('addRecordType');
			$settings = array();
			$settings['host'] = $cloudns->Helper->getPost('addRecordHost');
			$settings['record'] = html_entity_decode($cloudns->Helper->getPost('addRecordRecord'));
			$settings['ttl'] = $cloudns->Helper->getPost('addRecordTtl');
			$status = 1;
			
			if ($recordType == 'SRV') {
				$settings['priority'] = $cloudns->Helper->getPost('addRecordSRVPriority');
				$settings['weight'] = $cloudns->Helper->getPost('addRecordWeight');
				$settings['port'] = $cloudns->Helper->getPost('addRecordPort');
			} else if ($recordType == 'MX') {
				$settings['priority'] = $cloudns->Helper->getPost('addRecordMXPriority');
			} else if ($recordType == 'WR') {
				$settings['frame'] = $cloudns->Helper->getPost('addRecordWRFrame');
				$settings['frame-title'] = $cloudns->Helper->getPost('addRecordWRFrameTitle');
				$settings['frame-description'] = $cloudns->Helper->getPost('addRecordWRFrameDescription');
				$settings['frame-keywords'] = $cloudns->Helper->getPost('addRecordWRFrameKeywords');
				$settings['save-path'] = $cloudns->Helper->getPost('addRecordWRSavePath');
				$settings['mobile-meta'] = $cloudns->Helper->getPost('addRecordWRMobileMeta');
				$settings['redirect-type'] = $cloudns->Helper->getPost('wr_type');
			} else if ($recordType == 'RP') {
				$settings['mail'] = $cloudns->Helper->getPost('addRecordMail');
				$settings['txt'] = $cloudns->Helper->getPost('addRecordTxt');
			} else if ($recordType == 'SSHFP') {
				$settings['algorithm'] = $cloudns->Helper->getPost('algorithm');
				$settings['fptype'] = $cloudns->Helper->getPost('fp_type');
			} else if ($recordType == 'NAPTR') {
				$settings['order'] = $cloudns->Helper->getPost('addRecordOrder');
				$settings['pref'] = $cloudns->Helper->getPost('addRecordPref');
				$settings['flag'] = $cloudns->Helper->getPost('flag');
				$settings['params'] = $cloudns->Helper->getPost('addRecordParams');
				$settings['regexp'] = $cloudns->Helper->getPost('addRecordRegexp');
				$settings['replace'] = $cloudns->Helper->getPost('addRecordReplace');
			} else if ($recordType == 'CAA') {
				$settings['caa_flag'] = $cloudns->Helper->getPost('addRecordCAAflag');
				$settings['caa_type'] = $cloudns->Helper->getPost('addRecordCAAtype');
				$settings['caa_value'] = $cloudns->Helper->getPost('addRecordCAAvalue');
			} else if ($recordType == 'TLSA') {
				$settings['tlsa_usage'] = $cloudns->Helper->getPost('addRecordUsage');
				$settings['tlsa_selector'] = $cloudns->Helper->getPost('addRecordSelector');
				$settings['tlsa_matching_type'] = $cloudns->Helper->getPost('addRecordMatchingType');
			} else if ($recordType == 'DS') {
				$settings['key-tag'] = $cloudns->Helper->getPost('addRecordKeyTag');
				$settings['algorithm'] = $cloudns->Helper->getPost('addRecordDsAlgorithm');
				$settings['digest-type'] = $cloudns->Helper->getPost('addRecordDigestType');
			} else if ($recordType == 'CERT') {
				$settings['cert-type'] = $cloudns->Helper->getPost('addRecordCertType');
				$settings['cert-key-tag'] = $cloudns->Helper->getPost('addRecordCertKeyTag');
				$settings['cert-algorithm'] = $cloudns->Helper->getPost('addRecordCertAlgorithm');
			} else if ($recordType == 'HINFO') {
				$settings['cpu'] = $cloudns->Helper->getPost('addRecordCPU');
				$settings['os'] = $cloudns->Helper->getPost('addRecordOS');
			} else if ($recordType == 'LOC') {
				$settings['lat-deg'] = $cloudns->Helper->getPost('addRecordLatDeg');
				$settings['lat-min'] = $cloudns->Helper->getPost('addRecordLatMin');
				$settings['lat-sec'] = $cloudns->Helper->getPost('addRecordLatSec');
				$settings['lat-dir'] = $cloudns->Helper->getPost('addRecordLatDir');
				$settings['long-deg'] = $cloudns->Helper->getPost('addRecordLongDeg');
				$settings['long-min'] = $cloudns->Helper->getPost('addRecordLongMin');
				$settings['long-sec'] = $cloudns->Helper->getPost('addRecordLongSec');
				$settings['long-dir'] = $cloudns->Helper->getPost('addRecordLongDir');
				$settings['altitude'] = $cloudns->Helper->getPost('addRecordAltitude');
				$settings['size'] = $cloudns->Helper->getPost('addRecordSize');
				$settings['h-precision'] = $cloudns->Helper->getPost('addRecordHPrecision');
				$settings['v-precision'] = $cloudns->Helper->getPost('addRecordVPrecision');
			} else if ($recordType == 'SMIMEA') {
				$settings['smimea-usage'] = $cloudns->Helper->getPost('addRecordSmimeaUsage');
				$settings['smimea-selector'] = $cloudns->Helper->getPost('addRecordSmimeaSelector');
				$settings['smimea-matching-type'] = $cloudns->Helper->getPost('addRecordSmimeaMatchingType');
				} else if ($recordType == 'SVCB' || $recordType == 'HTTPS') {
					$settings['priority'] = $cloudns->Helper->getPost('addRecordPriority');
					$settings['parameters'] = html_entity_decode($cloudns->Helper->getPost('addRecordParameters'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
			$templateVariables = $cloudns->Actions->doAddNewRecord($zoneInfo, $recordType, $settings);
			$templateFile = 'templates/add-new-record.tpl';
			
		} elseif ($requestedAction == 'activate-dynamic-url') {
			$record_id = $cloudns->Helper->getGet('dns_record_id');
			$templateVariables = $cloudns->Actions->activateDynamicUrl($zoneInfo, $record_id);
			$templateFile = 'templates/records.tpl';
		} elseif ($requestedAction == 'create-monitoring-check') {
			$record_id = $cloudns->Helper->getGet('dns_record_id');
			$templateVariables = $cloudns->Actions->createMonitoringCheck($zoneInfo, $record_id);
			$templateFile = 'templates/records.tpl';
		} elseif ($requestedAction == 'get-failover-settings') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$templateVariables = $cloudns->Actions->getFailoverSettings($zoneInfo, $record_id);
				if (isset($templateVariables['failover']) && isset($templateVariables['failover']['status']) && $templateVariables['failover']['status'] != 'error') {
					$templateFile = 'templates/failover-view.tpl';
				} else {
					$templateFile = 'templates/failover-new.tpl';
				}
			}
			else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
		} elseif ($requestedAction == 'failover-activate') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$settings = array();
				$settings['check_type'] = $cloudns->Helper->getPost('fo_check_type');
				$settings['down_event_handler'] = $cloudns->Helper->getPost('fo_down_event_handler');
				$settings['up_event_handler'] = $cloudns->Helper->getPost('fo_up_event_handler');
				$settings['main_ip'] = $cloudns->Helper->getPost('fo_main_ip');
				$settings['backup_ip_1'] = $cloudns->Helper->getPost('fo_backup_ip_1');
				$settings['backup_ip_2'] = $cloudns->Helper->getPost('fo_backup_ip_2');
				$settings['backup_ip_3'] = $cloudns->Helper->getPost('fo_backup_ip_3');
				$settings['backup_ip_4'] = $cloudns->Helper->getPost('fo_backup_ip_4');
				$settings['backup_ip_5'] = $cloudns->Helper->getPost('fo_backup_ip_5');
				$settings['ping_threshold'] = $cloudns->Helper->getPost('fo_ping_threshold');
				$settings['monitoring_region'] = $cloudns->Helper->getPost('fo_monitoring_region');
				if ($settings['check_type'] === '18') {
					$settings['host'] = $cloudns->Helper->getPost('fo_http_host');
					$settings['port'] = $cloudns->Helper->getPost('fo_http_port');
				} else {
					$settings['host'] = $cloudns->Helper->getPost('fo_dns_host');
					$settings['port'] = $cloudns->Helper->getPost('fo_port');
				}
				$settings['path'] = $cloudns->Helper->getPost('fo_http_path');
				$settings['query_type'] = $cloudns->Helper->getPost('fo_dns_type');
				$settings['query_response'] = $cloudns->Helper->getPost('fo_dns_response');
				$settings['notification_type'] = $cloudns->Helper->getPost('fo_notification_type');
				$settings['notification_value'] = $cloudns->Helper->getPost('fo_notification_value');
				
				if ($settings['check_type'] === '18') {
					if ($cloudns->Helper->getPost('web_custom_string') == '1') {
						$settings['content'] = htmlspecialchars($cloudns->Helper->getPost('fo_http_content'));
					}
					$settings['http_protocol'] = $cloudns->Helper->getPost('web_protocol');
				}

				$templateVariables = $cloudns->Actions->activateFailover($zoneInfo, $record_id, $settings);
				$templateFile = 'templates/failover-new.tpl';
			}
			else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
		} elseif ($requestedAction == 'failover-deactivate') {
			$record_id = $cloudns->Helper->getGet('dns_record_id');
			
			$templateVariables = $cloudns->Actions->deactivateFailover($zoneInfo, $record_id);
			$templateFile = 'templates/records.tpl';
			
		} elseif ($requestedAction == 'failover-edit') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$settings = array();
				$settings['check_type'] = $cloudns->Helper->getPost('fo_check_type');
				$settings['down_event_handler'] = $cloudns->Helper->getPost('fo_down_event_handler');
				$settings['up_event_handler'] = $cloudns->Helper->getPost('fo_up_event_handler');
				$settings['main_ip'] = $cloudns->Helper->getPost('fo_main_ip');
				$settings['backup_ip_1'] = $cloudns->Helper->getPost('fo_backup_ip_1');
				$settings['backup_ip_2'] = $cloudns->Helper->getPost('fo_backup_ip_2');
				$settings['backup_ip_3'] = $cloudns->Helper->getPost('fo_backup_ip_3');
				$settings['backup_ip_4'] = $cloudns->Helper->getPost('fo_backup_ip_4');
				$settings['backup_ip_5'] = $cloudns->Helper->getPost('fo_backup_ip_5');
				$settings['ping_threshold'] = $cloudns->Helper->getPost('fo_ping_threshold');
				$settings['monitoring_region'] = $cloudns->Helper->getPost('fo_monitoring_region');
				if ($settings['check_type'] === '18') {
					$settings['host'] = $cloudns->Helper->getPost('fo_http_host');
					$settings['port'] = $cloudns->Helper->getPost('fo_http_port');
				} else {
					$settings['host'] = $cloudns->Helper->getPost('fo_dns_host');
					$settings['port'] = $cloudns->Helper->getPost('fo_port');
				}
				
				if ($settings['check_type'] === '18') {
					if ($cloudns->Helper->getPost('web_custom_string') == '1') {
						$settings['content'] = $cloudns->Helper->getPost('fo_http_content');
					}
					$settings['http_protocol'] = $cloudns->Helper->getPost('web_protocol');
				}
				
				$settings['path'] = $cloudns->Helper->getPost('fo_http_path');
				$settings['query_type'] = $cloudns->Helper->getPost('fo_dns_type');
				$settings['query_response'] = $cloudns->Helper->getPost('fo_dns_response');

				$templateVariables = $cloudns->Actions->editFailover($zoneInfo, $record_id, $settings);
				$templateFile = 'templates/failover-view.tpl';					
			} else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
		} elseif ($requestedAction == 'failover-action-log') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$page = abs($cloudns->Helper->getGet('page', 1));

				$templateVariables = $cloudns->Actions->failoverActionLog($zoneInfo, $record_id, $page);
				$templateFile = 'templates/failover-action-log.tpl';
			} else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
		} elseif ($requestedAction == 'failover-monitoring-log') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$page = abs($cloudns->Helper->getGet('page', 1));

				$templateVariables = $cloudns->Actions->failoverMonitoringLog($zoneInfo, $record_id, $page);
				$templateFile = 'templates/failover-monitoring-log.tpl';
			} else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			
		} elseif ($requestedAction == 'failover-monitoring-notifications') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$page = abs($cloudns->Helper->getGet('page', 1));

				$templateVariables = $cloudns->Actions->failoverMonitoringNotifications($zoneInfo, $record_id, $page);
				$templateFile = 'templates/failover-monitoring-notifications.tpl';
			} else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			
		} elseif ($requestedAction == 'failover-add-notification') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$page = abs($cloudns->Helper->getGet('page', 1));
				$settings = array();
				$settings['notification_type'] = $cloudns->Helper->getPost('fo_notification_type');
				$settings['notification_value'] = $cloudns->Helper->getPost('fo_notification_value');

				$templateVariables = $cloudns->Actions->addFailoverNotification($zoneInfo, $record_id, $settings, $page);
				$templateFile = 'templates/failover-monitoring-notifications.tpl';
			}
			else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
		} elseif ($requestedAction == 'failover-delete-notification') {
			$productid = $params['pid'];
			$freeProductId = $cloudns->Configuration->getFreeProductId();
			if ($productid != $freeProductId) {
				$foColumn = $cloudns->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
			} else {
				$foColumn = $cloudns->Database->columnCheck('tbldomains', 'fo_checks');
			}
			if ($foColumn == 1) {
				$record_id = $cloudns->Helper->getGet('dns_record_id');
				$page = abs($cloudns->Helper->getGet('page', 1));;
				$notification_id = $cloudns->Helper->getGet('notification_id');

				$templateVariables = $cloudns->Actions->deleteFailoverNotification($zoneInfo, $record_id, $notification_id, $page);
				$templateFile = 'templates/failover-monitoring-notifications.tpl';
			}
			else {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}

		} elseif ($requestedAction == 'zone-transfers') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$templateVariables = $cloudns->Actions->zoneTransfers($zoneInfo);
			$templateFile = 'templates/zone-transfers.tpl';

		} elseif ($requestedAction == 'zone-transfers-add') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$ip = $cloudns->Helper->getPost('ip', '');
			$templateVariables = $cloudns->Actions->zoneTransfersAdd($zoneInfo, $ip);
			$templateFile = 'templates/zone-transfers.tpl';

		} elseif ($requestedAction == 'zone-transfers-delete') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$id = $cloudns->Helper->getPost('id', '');
			$templateVariables = $cloudns->Actions->zoneTransfersDelete($zoneInfo, $id);
			$templateFile = 'templates/zone-transfers.tpl';

		} elseif ($requestedAction == 'parked-templates') {
			$templateVariables = $cloudns->Actions->parkedTemplates($zoneInfo);
			$templateFile = 'templates/parked-templates.tpl';

		} elseif ($requestedAction == 'parked-templates-apply') {
			$template = $cloudns->Helper->getPost('template', '');
			$title = $cloudns->Helper->getPost('title', '');
			$description = $cloudns->Helper->getPost('description', '');
			$keywords = $cloudns->Helper->getPost('keywords', '');
			$contactForm = $cloudns->Helper->getPost('contact_form', '0');
			$templateVariables = $cloudns->Actions->parkedTemplatesApply($zoneInfo, $template, $title, $description, $keywords, $contactForm);
			$templateFile = 'templates/parked-templates.tpl';

		} elseif ($requestedAction == 'export-zone-file') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$download = $cloudns->Helper->getGet('download', '0');
			$format = $cloudns->Helper->getRequest('format', 'bind');
			$templateVariables = $cloudns->Actions->exportZoneFile($zoneInfo, $format, $download == '1');
			$templateFile = 'templates/export-zone-file.tpl';

		} elseif ($requestedAction == 'copy-zone') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$templateVariables = $cloudns->Actions->copyZone($zoneInfo);
			$templateFile = 'templates/copy-zone.tpl';

		} elseif ($requestedAction == 'copy-zone-execute') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$destinationZone = $cloudns->Helper->getPost('destination_zone', '');
			$options = array(
				'create_if_missing' => $cloudns->Helper->getPost('create_if_missing', '') == '1',
				'mode' => $cloudns->Helper->getPost('copy_mode', 'replace_matching'),
				'follow_domain' => $cloudns->Helper->getPost('follow_domain', '') == '1',
				'copy_wr' => $cloudns->Helper->getPost('copy_wr', '') == '1',
				'copy_forwards' => $cloudns->Helper->getPost('copy_forwards', '') == '1',
				'copy_hsts' => $cloudns->Helper->getPost('copy_hsts', '') == '1',
			);
			$templateVariables = $cloudns->Actions->copyZoneExecute($zoneInfo, $destinationZone, $options);
			$templateFile = 'templates/copy-zone.tpl';

		} elseif ($requestedAction == 'free-ssl') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$templateVariables = $cloudns->Actions->freeSsl($zoneInfo);
			$templateFile = 'templates/free-ssl.tpl';

		} elseif ($requestedAction == 'freessl-activate') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$issuer = $cloudns->Helper->getPost('issuer', '1');
			$templateVariables = $cloudns->Actions->freeSslActivate($zoneInfo, $issuer);
			$templateFile = 'templates/free-ssl.tpl';

		} elseif ($requestedAction == 'freessl-deactivate') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$templateVariables = $cloudns->Actions->freeSslDeactivate($zoneInfo);
			$templateFile = 'templates/free-ssl.tpl';

		} elseif ($requestedAction == 'freessl-change-issuer') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$issuer = $cloudns->Helper->getPost('issuer', '1');
			$templateVariables = $cloudns->Actions->freeSslChangeIssuer($zoneInfo, $issuer);
			$templateFile = 'templates/free-ssl.tpl';

		} elseif ($requestedAction == 'freessl-hsts') {
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$enabled = $cloudns->Helper->getPost('hsts', '0') == '1';
			$templateVariables = $cloudns->Actions->freeSslSetHsts($zoneInfo, $enabled);
			$templateFile = 'templates/free-ssl.tpl';

		} elseif ($requestedAction == 'dnssec'){
			
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
			}
			
			$waitDSrecords = $cloudns->Helper->getGet('waitDS');
			$templateVariables = $cloudns->Actions->dnssec($zoneInfo, $waitDSrecords);
			if (isset($templateVariables['dnssec']) && isset($templateVariables['dnssec']['status']) && $templateVariables['dnssec']['status'] == '1') {
				$templateFile = 'templates/dnssec-settings.tpl';
			}
			elseif (isset($templateVariables['dnssec']) && isset($templateVariables['dnssec']['status']) && $templateVariables['dnssec']['status'] == 'error' && $waitDSrecords == 1) {
				$templateFile = 'templates/dnssec-waiting.tpl';
			}
			else {
				$templateFile = 'templates/dnssec-show.tpl';
			}
			
		} elseif ($requestedAction == 'dnssec-show'){
			
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
			}
			
			$templateVariables = $cloudns->Actions->dnssecShow($zoneInfo);
			$templateFile = 'templates/dnssec-show.tpl';
			
		} elseif ($requestedAction == 'dnssec-settings'){
			
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
			}
			
			$templateVariables = $cloudns->Actions->dnssecSettings($zoneInfo);
			$templateFile = 'templates/dnssec-settings.tpl';
			
		} elseif ($requestedAction == 'dnssec-activate'){
			
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
			}
			
			$waitDSrecords = $cloudns->Helper->getGet('waitds', 0);
			
			$templateVariables = $cloudns->Actions->dnssecActivate($zoneInfo);
			$templateFile = 'templates/dnssec-settings.tpl';
			
		} elseif ($requestedAction == 'dnssec-deactivate'){
			
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
			}
			
			$templateVariables = $cloudns->Actions->dnssecDeactivate($zoneInfo);
			$templateFile = 'templates/dnssec-show.tpl';
			
		} elseif ($requestedAction == 'dnssec-waiting'){
			
			if ($zoneInfo['type'] != 'master') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
			}
			
			$templateVariables = $cloudns->Actions->dnssecWaiting($zoneInfo);
			$templateFile = 'templates/dnssec-waiting.tpl';
			
		} elseif ($requestedAction == 'edit-record') {
			$record_id = $cloudns->Helper->getGet('dns_record_id');
			$templateVariables = $cloudns->Actions->editRecord($zoneInfo, $record_id);
			$templateFile = 'templates/edit-record.tpl';
			
		} elseif ($requestedAction == 'mail-forwarding') {
			if (isset($_POST['forwards']) && is_array($_POST['forwards'])) {
				foreach ($_POST['forwards'] as $forwardId) {
					$cloudns->Controller->deleteForward($zoneInfo['name'], $forwardId);
				}
			}

			$templateVariables = $cloudns->Actions->getForwards($zoneInfo);
			$templateFile = 'templates/mail-forwarding.tpl';

		} elseif ($requestedAction == 'mail-forwarding-add-mx') {
			$templateVariables = $cloudns->Actions->addMailForwardMxRecords($zoneInfo);
			$templateFile = 'templates/mail-forwarding.tpl';

		} elseif ($requestedAction == 'add-new-forwarding') {
			$templateVariables = array(
				'pagetitle' => 'Add Mail Forward - ' . $zoneInfo['name'],
				'zoneInfo' => $zoneInfo,
				'settings' => array(),
				'cloudAction' => 'add-new-forwarding',
				'version' => explode('.', $params['whmcsVersion'])[0],
				'theme' => $params['clientareatemplate'],
			);
			$templateFile = 'templates/add-new-forwarding.tpl';

		} elseif ($requestedAction == 'do-edit-forward') {
			$forwardId = $cloudns->Helper->getGet('forward_id');
			$templateVariables = $cloudns->Actions->getForward($zoneInfo, $forwardId);
			$templateFile = 'templates/edit-forwarding.tpl';

		} elseif ($requestedAction == 'edit-forward') {
			$forwardId = $cloudns->Helper->getPost('forward_id');
			$from = $cloudns->Helper->getPost('editEmailForward');
			$to = $cloudns->Helper->getPost('editEmailForwardTo');
			$templateVariables = $cloudns->Actions->doEditForward($zoneInfo, $forwardId, $from, $to);
			$templateFile = 'templates/edit-forwarding.tpl';

		} elseif ($requestedAction == 'do-edit-record') {
			$record_id = $cloudns->Helper->getGet('dns_record_id');
			$recordType = $cloudns->Helper->getPost('recordType');
			
			$settings = array();
			$settings['host'] = $cloudns->Helper->getPost('editRecordHost');
			$settings['record'] = html_entity_decode($cloudns->Helper->getPost('editRecordRecord'));
			$settings['ttl'] = $cloudns->Helper->getPost('editRecordTtl');
			$status = 1;
			
			if ($recordType == 'SRV') {
				$settings['priority'] = $cloudns->Helper->getPost('editRecordPriority');
				$settings['weight'] = $cloudns->Helper->getPost('editRecordWeight');
				$settings['port'] = $cloudns->Helper->getPost('editRecordPort');
			} else if ($recordType == 'MX') {
				$settings['priority'] = $cloudns->Helper->getPost('editRecordPriority');
			} else if ($recordType == 'WR') {
				$settings['frame'] = $cloudns->Helper->getPost('editRecordWRFrame');
				$settings['frame-title'] = $cloudns->Helper->getPost('editRecordWRFrameTitle');
				$settings['frame-description'] = $cloudns->Helper->getPost('editRecordWRFrameDescription');
				$settings['frame-keywords'] = $cloudns->Helper->getPost('editRecordWRFrameKeywords');
				$settings['save-path'] = $cloudns->Helper->getPost('editRecordWRSavePath');
				$settings['mobile-meta'] = $cloudns->Helper->getPost('editRecordWRMobileMeta');
				$settings['redirect-type'] = $cloudns->Helper->getPost('wr_type');
			} else if ($recordType == 'RP') {
				$settings['mail'] = $cloudns->Helper->getPost('editRecordMail');
				$settings['txt'] = $cloudns->Helper->getPost('editRecordTxt');
			} else if ($recordType == 'SSHFP') {
				$settings['algorithm'] = $cloudns->Helper->getPost('algorithm');
				$settings['fptype'] = $cloudns->Helper->getPost('fp_type');
			} else if ($recordType == 'NAPTR') {
				$settings['order'] = $cloudns->Helper->getPost('editRecordOrder');
				$settings['pref'] = $cloudns->Helper->getPost('editRecordPref');
				$settings['flag'] = $cloudns->Helper->getPost('flag');
				$settings['params'] = $cloudns->Helper->getPost('editRecordParams');
				$settings['regexp'] = $cloudns->Helper->getPost('editRecordRegexp');
				$settings['replace'] = $cloudns->Helper->getPost('editRecordReplace');
			} else if ($recordType == 'CAA') {
				$settings['caa_flag'] = $cloudns->Helper->getPost('editRecordCAAflag');
				$settings['caa_type'] = $cloudns->Helper->getPost('editRecordCAAtype');
				$settings['caa_value'] = $cloudns->Helper->getPost('editRecordCAAvalue');
			} else if ($recordType == 'TLSA') {
				$settings['tlsa_usage'] = $cloudns->Helper->getPost('editRecordUsage');
				$settings['tlsa_selector'] = $cloudns->Helper->getPost('editRecordSelector');
				$settings['tlsa_matching_type'] = $cloudns->Helper->getPost('editRecordMatchingType');
			} else if ($recordType == 'DS') {
				$settings['key-tag'] = $cloudns->Helper->getPost('editRecordKeyTag');
				$settings['algorithm'] = $cloudns->Helper->getPost('editRecordDsAlgorithm');
				$settings['digest-type'] = $cloudns->Helper->getPost('editRecordDigestType');
			} else if ($recordType == 'CERT') {
				$settings['cert-type'] = $cloudns->Helper->getPost('editRecordCertType');
				$settings['cert-key-tag'] = $cloudns->Helper->getPost('editRecordCertKeyTag');
				$settings['cert-algorithm'] = $cloudns->Helper->getPost('editRecordCertAlgorithm');
			} else if ($recordType == 'HINFO') {
				$settings['cpu'] = $cloudns->Helper->getPost('editRecordCPU');
				$settings['os'] = $cloudns->Helper->getPost('editRecordOS');
			} else if ($recordType == 'LOC') {
				$settings['lat-deg'] = $cloudns->Helper->getPost('editRecordLatDeg');
				$settings['lat-min'] = $cloudns->Helper->getPost('editRecordLatMin');
				$settings['lat-sec'] = $cloudns->Helper->getPost('editRecordLatSec');
				$settings['lat-dir'] = $cloudns->Helper->getPost('editRecordLatDir');
				$settings['long-deg'] = $cloudns->Helper->getPost('editRecordLongDeg');
				$settings['long-min'] = $cloudns->Helper->getPost('editRecordLongMin');
				$settings['long-sec'] = $cloudns->Helper->getPost('editRecordLongSec');
				$settings['long-dir'] = $cloudns->Helper->getPost('editRecordLongDir');
				$settings['altitude'] = $cloudns->Helper->getPost('editRecordAltitude');
				$settings['size'] = $cloudns->Helper->getPost('editRecordSize');
				$settings['h-precision'] = $cloudns->Helper->getPost('editRecordHPrecision');
				$settings['v-precision'] = $cloudns->Helper->getPost('editRecordVPrecision');
			} else if ($recordType == 'SMIMEA') {
				$settings['smimea-usage'] = $cloudns->Helper->getPost('editRecordSmimeaUsage');
				$settings['smimea-selector'] = $cloudns->Helper->getPost('editRecordSmimeaSelector');
				$settings['smimea-matching-type'] = $cloudns->Helper->getPost('editRecordSmimeaMatchingType');
				} else if ($recordType == 'SVCB' || $recordType == 'HTTPS') {
					$settings['priority'] = $cloudns->Helper->getPost('editRecordPriority');
					$settings['parameters'] = html_entity_decode($cloudns->Helper->getPost('editRecordParameters'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
			$templateVariables = $cloudns->Actions->doEditRecord($zoneInfo, $record_id, $settings, $status, $recordType);

			$templateFile = 'templates/edit-record.tpl';
	
		} elseif (in_array($requestedAction, array(
			'add-new-zone',
			'add-new-zone-master',
			'add-new-zone-bulk-master',
			'add-new-zone-bulk-delete',
			'add-new-zone-slave',
			'add-new-zone-parked',
			'add-new-zone-master-reverse',
			'add-new-zone-slave-reverse',
		), true)) {
			if ($params['configoption3'] == 'on') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}");
			}
			$version = explode('.', $params['whmcsVersion']);
			$zonesForBulk = $cloudns->Controller->getZones();
			if (!is_array($zonesForBulk) || isset($zonesForBulk['status'])) {
				$zonesForBulk = array();
			}
			$bulkZoneResult = array();
			if (isset($_SESSION['cloudnsBulkZoneResult'][$params['serviceid']])) {
				$bulkZoneResult = $_SESSION['cloudnsBulkZoneResult'][$params['serviceid']];
				unset($_SESSION['cloudnsBulkZoneResult'][$params['serviceid']]);
			}

			// DomainMonger Patch 1592: each zone tool has its own customAction.
			// WHMCS product-details routing did not consistently retain the added
			// zoneTool query parameter, causing bulk pages to fall back to master.
			$zoneToolActions = array(
				'add-new-zone'=>'master',
				'add-new-zone-master'=>'master',
				'add-new-zone-bulk-master'=>'bulkMaster',
				'add-new-zone-bulk-delete'=>'bulkDelete',
				'add-new-zone-slave'=>'slave',
				'add-new-zone-parked'=>'parked',
				'add-new-zone-master-reverse'=>'masterReverse',
				'add-new-zone-slave-reverse'=>'slaveReverse',
			);
			$selectedZoneType = isset($zoneToolActions[$requestedAction]) ? $zoneToolActions[$requestedAction] : 'master';
			$templateVariables = array(
				'serversList'=>$cloudns->Servers->getAvailableServers(),
				'zonesForBulk'=>$zonesForBulk,
				'bulkZoneResult'=>$bulkZoneResult,
				'selectedZoneType'=>$selectedZoneType,
				'cloudAction'=>'add-new-zone',
				'version'=>$version[0],
				'theme'=>$params['clientareatemplate'],
				'templateZone'=>$cloudns->Controller->defaultZone(),
			);
			
			$templateFile = 'templates/add-new-zone.tpl';
			
		} elseif ($requestedAction == 'soa-settings') {
			$templateVariables = $cloudns->Actions->getSOASettings($zoneInfo);
			$templateFile = 'templates/soa.tpl';
			
		} elseif ($requestedAction == 'edit-soa-settings') {
			$soa = array();
			$soa['primaryNS'] = $cloudns->Helper->getPost('primaryNS');
			$soa['adminMail'] = $cloudns->Helper->getPost('adminMail');
			$soa['refresh'] = $cloudns->Helper->getPost('refresh');
			$soa['retry'] = $cloudns->Helper->getPost('retry');
			$soa['expire'] = $cloudns->Helper->getPost('expire');
			$soa['defaultTTL'] = $cloudns->Helper->getPost('defaultTTL');
			$soaAction = 'do_save';
			if (isset($_POST['do_reset'])) {
				$soaAction = 'do_reset';
			}
			$templateVariables = $cloudns->Actions->editSOA($zoneInfo, $soa, $soaAction);
			$templateFile = 'templates/soa.tpl';
			
		} elseif ($requestedAction == 'update-status') {
			$templateVariables = $cloudns->Actions->getUpdateStatus($zoneInfo);
			$templateFile = 'templates/update-status.tpl';
			
		} elseif ($requestedAction == 'update') {
			$templateFile = 'templates/update-status.tpl';
			$templateVariables = $cloudns->Actions->updateZone($zoneInfo);
			
		} elseif ($requestedAction == 'import') {
			$templateFile = 'templates/import.tpl';
			$version = explode('.', $params['whmcsVersion']);
			$response = '';
			$templateVariables = array(
				'pagetitle' => 'Import Zone File - ' . $zone,
				'response' => $response,
				'cloudAction' => 'import',
				'version'=>$version[0],
				'theme' => $params['clientareatemplate'],
			);
			
		} elseif ($requestedAction == 'import-records') {
			$fileType = $cloudns->Helper->getPost('type');
			if (!in_array($fileType, array('bind', 'tinydns'))) {
				$templateVariables = array(
					'pagetitle' => 'Import Zone File - ' . $zone,
					'response' => 'Invalid DNS zone file type',
					'cloudAction' => 'import',
				);
			} else {
				$delete = $cloudns->Helper->getPost('delete');
				if (!in_array($delete, array(0, 1))) {
					$templateVariables = array(
						'pagetitle' => 'Import Zone File - ' . $zone,
						'response' => 'Invalid delete parameter',
						'cloudAction' => 'import',
					);
				}
				$format = $cloudns->Helper->getPost('type');
				$records = html_entity_decode($cloudns->Helper->getPost('recordsList'));
				
				$templateVariables = $cloudns->Actions->importRecords($zone, $records, $delete, $format);
			}
			$templateFile = 'templates/import.tpl';
			
		} elseif ($requestedAction == 'statistics') {
			$templateVariables = $cloudns->Actions->getStats($zoneInfo, $cloudns->Helper->getGet('date', ''));
			$templateFile = 'templates/statistics.tpl';
			
		} else {
			$_SESSION['zoneInfo'] = array();

			// DomainMonger Patch 486:
			// The DNS Records page is now the primary zone-management page.
			// Redirect the legacy/default Zones List view to the first available zone
			// instead of rendering the standalone Zones List screen.
			$domainSwitchZones = $cloudns->Controller->getDomainSwitchZones();
			if (is_array($domainSwitchZones) && !empty($domainSwitchZones) && isset($domainSwitchZones[0]['name']) && trim((string)$domainSwitchZones[0]['name']) !== '') {
				$firstZone = urlencode($domainSwitchZones[0]['name']);
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=zone-settings&zone={$firstZone}");
			}

			if ($params['configoption3'] != 'on') {
				$cloudns->Helper->redirect("clientarea.php?action=productdetails&id={$params['serviceid']}&customAction=add-new-zone");
			}

			$templateFile = 'templates/zones.tpl';
			$templateVariables = $cloudns->Actions->getZones();
		}
		
		// adding the below keys, because they are used everywhere and there is no need to add them in each action
		$version = explode('.', $params['whmcsVersion']);
		$templateVariables['serviceid'] = $params['serviceid'];
		$templateVariables['zone'] = $zone;
		$templateVariables['version']=$version[0];
		$templateVariables['theme'] = $params['clientareatemplate'];
		$templateVariables['registeredDomains'] = $params['configoption3'];
		$templateVariables['monitoringIconsEnabled'] = cloudns_configBool('show_monitoring_icons', false) ? 1 : 0;
		if (!isset($templateVariables['isParkedZone'])) {
			$templateVariables['isParkedZone'] = 0;
			if (!empty($zoneInfo) && isset($zoneInfo['name']) && $zoneInfo['name'] != '') {
				$templateVariables['isParkedZone'] = $cloudns->Actions->isParkedZoneForMenu($zoneInfo) ? 1 : 0;
			}
		}

		$domainSwitchActions = array(
			'zone-settings',
			'mail-forwarding',
			'statistics',
			'update-status',
			'dnssec',
			'soa-settings',
			'import',
			'zone-transfers',
			'free-ssl',
			'copy-zone',
			'export-zone-file',
			'parked-templates',
		);
		$templateVariables['domainSwitchZones'] = $cloudns->Controller->getDomainSwitchZones();
		$templateVariables['zonesCount'] = is_array($templateVariables['domainSwitchZones']) ? count($templateVariables['domainSwitchZones']) : 0;
		$templateVariables['zonesLimit'] = isset($params['configoption1']) ? $params['configoption1'] : '';
		if ($params['configoption3'] == 'on') {
			$templateVariables['zonesLimit'] = $templateVariables['zonesCount'];
		}
		$templateVariables['domainSwitchAction'] = in_array($requestedAction, $domainSwitchActions, true) ? $requestedAction : 'zone-settings';
		$templateVariables['domainSwitchDate'] = $cloudns->Helper->getGet('date', '');
				
		// returning the template
		return array(
			'tabOverviewReplacementTemplate' => $templateFile,
			'vars' => $templateVariables,
			'cloudAction' => $requestedAction,
		);
		
	} catch (Exception $e) {
		// Record the error in WHMCS's module log.
		logModuleCall(
			'cloudns',
			__FUNCTION__,
			$params,
			$e->getMessage(),
			$e->getTraceAsString()
		);
		$version = explode('.', $params['whmcsVersion']);
		// In an error condition, display an error page.
		return array(
			'tabOverviewReplacementTemplate' => 'zone-error.tpl',
			'templateVariables' => array(
				'zone' => $zone,
				'response' => array('status'=>'error', 'description'=>$e->getMessage()),
				'usefulErrorHelper' => $e->getMessage(),
				'version'=>$version[0],
				'theme' => $params['clientareatemplate'],
				'registeredDomains' => $params['registeredDomains'],
				'cloudAction' => 'error',
			),
		);
	}
}


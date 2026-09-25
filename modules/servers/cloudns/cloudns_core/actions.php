<?php

class Cloudns_Actions {
	/**
	 * @var core 
	 */
	protected $core;
	protected $params;

	public function __construct($params) {
		$this->core = Cloudns_Core::inst($params);
		$this->params = $params;
	}

	/**
	 * Gets the zones from the module's table
	 * @return array
	 */
	public function getZones() {

		if ($this->params['registeredDomains'] == 'on') {

			$zones = $this->core->Controller->getRegisteredDomains();
			foreach ($zones as &$zone) {
				$zone['ascii'] = $this->core->Helper->getUnicodeName($zone['name']);
			}

			$response = $zones;
		} else {
			$response = $this->core->Controller->getZones();
		}
		$zonesCount = 0;
		if (is_array($response) && !isset($response['status'])) {
			$zonesCount = count($response);
		}

		$zonesLimit = isset($this->params['zonesLimit']) ? $this->params['zonesLimit'] : '';
		if ($this->params['registeredDomains'] == 'on') {
			$zonesLimit = $zonesCount;
		}

		return array(
			'response' => $response,
			'zones' => $response,
			'zonesCount' => $zonesCount,
			'zonesLimit' => $zonesLimit,
			'cloudAction' => 'zones',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Gets the info needed to display the Add new record page
	 * @param array $zoneInfo
	 * @param string $recordType
	 * @return array
	 */
	public function addNewRecord($zoneInfo, $recordType, $sourceRecordId = null) {
		
		// check records limit before creation
		$recordsCount = $this->core->Controller->getRecordsCount($zoneInfo['name']);

		if ($this->params['recordsLimit'] != '' && $this->params['recordsLimit'] != -1 && $recordsCount >= $this->params['recordsLimit']) {
			return array('status' => 'error');
		}
		
		if ($zoneInfo['zone'] == 'ipv4' || $zoneInfo['zone'] == 'ipv6') {
			$zone_type = 'reverse';
		} else {
			$zone_type = $zoneInfo['zone'];
		}

		$recordTypes = $this->core->Controller->getRecordTypes($zone_type);
		$ttl = $this->core->Controller->getAvailableTTL();
		$type = 'A';
		$settings = array();
		if (!empty($sourceRecordId)) {
			$sourceRecord = $this->core->Controller->getRecordById($zoneInfo['name'], $sourceRecordId);
			if (!empty($sourceRecord) && isset($sourceRecord['type']) && in_array($sourceRecord['type'], $recordTypes)) {
				$type = $sourceRecord['type'];
				$settings = $this->recordToAddSettings($sourceRecord);
			}
		} elseif (in_array($recordType, $recordTypes)) {
			$type = $recordType;
		}

		$shortName = $this->core->Helper->shortenLongName($zoneInfo['name'], 34);

		return array(
			'pagetitle' => 'Add DNS Record - ' . $zoneInfo['name'],
			'recordTypes' => $recordTypes,
			'defaultType' => $type,
			'shortName' => $shortName,
			'ttls' => $ttl,
			'settings' => $settings,
			'cloudAction' => 'add-new-record',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Adds new record
	 * @param array $zoneInfo
	 * @param string $recordType The type of the new record
	 * @param array $settings The record's settings submited by the customer
	 * @return array
	 */
	// todo
	public function doAddNewRecord($zoneInfo, $recordType, $settings) {
		$zone = $zoneInfo['name'];
		
		$recordTypes = $this->core->Controller->getRecordTypes($zoneInfo['zone']);
		$ttl = $this->core->Controller->getAvailableTTL();

		$response = $this->core->Records->recordAdd($zone, $recordType, $settings);

		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zone}");
		}
		$shortName = $this->core->Helper->shortenLongName($zone, 34);

		return array(
			'pagetitle' => 'Add DNS Record - ' . $zone,
			'response' => $response,
			'recordTypes' => $recordTypes,
			'defaultType' => $recordType,
			'settings' => $settings,
			'zone' => $zone,
			'ttls' => $ttl,
			'shortName' => $shortName,
			'cloudAction' => 'add-record',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Gets the information needed to display the Edit record page
	 * @param array $zoneInfo
	 * @param int $record_id ID of the record in the ClouDNS system
	 * @return array
	 */
	/**
	 * Gets all mail forwards for a zone.
	 *
	 * @param array $zoneInfo
	 * @param array $response
	 * @return array
	 */
	public function getForwards($zoneInfo, $response = array()) {
		$forwards = $this->core->Controller->getForwards($zoneInfo['name']);
		$mailForwardMxStatus = $this->getMailForwardMxStatus($zoneInfo['name']);

		return array(
			'pagetitle' => 'Mail Forwards - ' . $zoneInfo['name'],
			'zone' => $zoneInfo['name'],
			'zoneInfo' => $zoneInfo,
			'records' => $forwards,
			'forwardsCount' => count($forwards),
			'forwardsLimit' => $this->params['forwardsLimit'],
			'mailForwardMxStatus' => $mailForwardMxStatus,
			'cloudAction' => 'mail-forwarding',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
			'response' => $response,
		);
	}

	public function addMailForwardMxRecords($zoneInfo) {
		$zone = $zoneInfo['name'];
		$mxStatus = $this->getMailForwardMxStatus($zone);

		if (isset($mxStatus['error']) && $mxStatus['error'] != '') {
			return $this->getForwards($zoneInfo, array(
				'status' => 'error',
				'description' => $mxStatus['error'],
			));
		}

		if (empty($mxStatus['missing'])) {
			return $this->getForwards($zoneInfo, array(
				'status' => 'info',
				'description' => 'The required ClouDNS mail forwarding MX records are already active.',
			));
		}

		$created = array();
		$errors = array();

		foreach ($mxStatus['missing'] as $mxRecord) {
			$settings = array(
				'host' => '',
				'record' => $mxRecord,
				'ttl' => $this->getMailForwardMxTtl(),
				'priority' => '100',
			);

			$response = $this->core->Records->recordAdd($zone, 'MX', $settings);

			if (isset($response['status']) && $response['status'] == 'success') {
				$created[] = $mxRecord;
			} else {
				$description = isset($response['description']) ? $response['description'] : (isset($response['statusDescription']) ? $response['statusDescription'] : 'Unknown API error');
				$errors[] = $mxRecord . ': ' . $description;
			}
		}

		if (!empty($errors)) {
			return $this->getForwards($zoneInfo, array(
				'status' => 'error',
				'description' => 'Could not add all required Mail Forward MX records. ' . implode(' ', $errors),
			));
		}

		return $this->getForwards($zoneInfo, array(
			'status' => 'success',
			'description' => 'Added Mail Forward MX records: ' . implode(', ', $created),
		));
	}

	protected function getRequiredMailForwardMxRecords() {
		return array(
			'mailforward31.cloudns.net',
			'mailforward32.cloudns.net',
		);
	}

	protected function getMailForwardMxTtl() {
		$availableTtls = $this->core->Records->getAvailableTTL();
		if (is_array($availableTtls)) {
			foreach ($availableTtls as $ttl) {
				if ((string)$ttl === '3600') {
					return '3600';
				}
			}
			foreach ($availableTtls as $ttl) {
				if (is_scalar($ttl) && preg_match('/^[0-9]+$/', (string)$ttl)) {
					return (string)$ttl;
				}
			}
		}
		return '3600';
	}

	protected function getMailForwardMxStatus($zone) {
		$required = $this->getRequiredMailForwardMxRecords();
		$active = array();
		foreach ($required as $mxRecord) {
			$active[$mxRecord] = false;
		}

		$records = $this->core->Records->getRecords($zone);
		if (!is_array($records)) {
			return array(
				'required' => $required,
				'active' => array(),
				'missing' => $required,
				'allActive' => false,
				'error' => 'Could not check Mail Forward MX records.',
			);
		}

		if (isset($records['status']) && $records['status'] == 'error') {
			return array(
				'required' => $required,
				'active' => array(),
				'missing' => $required,
				'allActive' => false,
				'error' => isset($records['description']) ? $records['description'] : 'Could not check Mail Forward MX records.',
			);
		}

		$normalizedRequired = array();
		foreach ($required as $mxRecord) {
			$normalizedRequired[$this->normalizeMailForwardMxValue($mxRecord)] = $mxRecord;
		}

		foreach ($records as $record) {
			if (!is_array($record)) {
				continue;
			}

			$type = isset($record['type']) ? $record['type'] : (isset($record['record-type']) ? $record['record-type'] : '');
			if (strtoupper((string)$type) !== 'MX') {
				continue;
			}

			if (!$this->isMailForwardMxRecordActive($record)) {
				continue;
			}

			$host = isset($record['host']) ? $record['host'] : '';
			if (!$this->isRootMailForwardMxHost($host, $zone)) {
				continue;
			}

			$value = '';
			foreach (array('record', 'value', 'points_to', 'target') as $key) {
				if (isset($record[$key])) {
					$value = $record[$key];
					break;
				}
			}

			$normalizedValue = $this->normalizeMailForwardMxValue($value);
			if (isset($normalizedRequired[$normalizedValue])) {
				$active[$normalizedRequired[$normalizedValue]] = true;
			}
		}

		$activeRecords = array();
		$missingRecords = array();
		foreach ($active as $mxRecord => $isActive) {
			if ($isActive) {
				$activeRecords[] = $mxRecord;
			} else {
				$missingRecords[] = $mxRecord;
			}
		}

		return array(
			'required' => $required,
			'active' => $activeRecords,
			'missing' => $missingRecords,
			'allActive' => empty($missingRecords),
			'error' => '',
		);
	}

	protected function isMailForwardMxRecordActive($record) {
		if (!isset($record['status'])) {
			return true;
		}

		$status = strtolower(trim((string)$record['status']));
		return in_array($status, array('1', 'active', 'yes', 'true'), true);
	}

	protected function isRootMailForwardMxHost($host, $zone) {
		$host = $this->normalizeMailForwardMxValue($host);
		$zone = $this->normalizeMailForwardMxValue($zone);

		return $host === '' || $host === '@' || $host === $zone;
	}

	protected function normalizeMailForwardMxValue($value) {
		return strtolower(rtrim(trim((string)$value), '.'));
	}

	/**
	 * Gets one mail forward for editing.
	 *
	 * @param array $zoneInfo
	 * @param int|string $forwardId
	 * @param array $response
	 * @return array
	 */
	public function getForward($zoneInfo, $forwardId, $response = array()) {
		$forward = $this->core->Controller->getForwardById($zoneInfo['name'], $forwardId);

		if (empty($forward)) {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=mail-forwarding&zone={$zoneInfo['name']}");
		}

		return array(
			'pagetitle' => 'Edit Mail Forward - ' . $zoneInfo['name'],
			'zoneInfo' => $zoneInfo,
			'forward' => $forward,
			'cloudAction' => 'edit-forwarding',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
			'response' => $response,
		);
	}

	/**
	 * Saves an edited mail forward.
	 *
	 * @param array $zoneInfo
	 * @param int|string $forwardId
	 * @param string $source
	 * @param string $destination
	 * @return array
	 */
	public function doEditForward($zoneInfo, $forwardId, $source, $destination) {
		$response = $this->core->Forwards->forwardEdit($zoneInfo['name'], $forwardId, $source, $destination);

		if (isset($response['status']) && $response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=mail-forwarding&zone={$zoneInfo['name']}");
		}

		$forward = array(
			'id' => $forwardId,
			'source' => $source,
			'destination' => $destination,
		);

		return array(
			'pagetitle' => 'Edit Mail Forward - ' . $zoneInfo['name'],
			'response' => $response,
			'forward' => $forward,
			'zone' => $zoneInfo['name'],
			'cloudAction' => 'edit-forwarding',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Maps a fetched DNS record into the Add New Record form field names.
	 *
	 * @param array $record
	 * @return array
	 */
	private function recordToAddSettings($record) {
		$settings = array(
			'host' => isset($record['host']) ? $record['host'] : '',
			'record' => isset($record['record']) ? $record['record'] : '',
			'ttl' => isset($record['ttl']) ? $record['ttl'] : 3600,
		);

		$type = isset($record['type']) ? $record['type'] : '';
		if ($type == 'MX') {
			$settings['addRecordMXPriority'] = isset($record['priority']) ? trim($record['priority']) : 10;
		} elseif ($type == 'SRV') {
			$settings['addRecordSRVPriority'] = isset($record['priority']) ? trim($record['priority']) : 0;
			$settings['addRecordWeight'] = isset($record['weight']) ? $record['weight'] : 0;
			$settings['addRecordPort'] = isset($record['port']) ? $record['port'] : 0;
		} elseif ($type == 'WR') {
			$settings['addRecordWRFrame'] = isset($record['frame']) ? $record['frame'] : 0;
			$settings['addRecordWRFrameTitle'] = isset($record['frame_title']) ? $record['frame_title'] : '';
			$settings['addRecordWRFrameDescription'] = isset($record['frame_description']) ? $record['frame_description'] : '';
			$settings['addRecordWRFrameKeywords'] = isset($record['frame_keywords']) ? $record['frame_keywords'] : '';
			$settings['addRecordWRSavePath'] = isset($record['save_path']) ? $record['save_path'] : 0;
			$settings['addRecordWRMobileMeta'] = isset($record['mobile_meta']) ? $record['mobile_meta'] : 0;
			$settings['addRecordWRType'] = isset($record['redirect_type']) ? $record['redirect_type'] : 301;
		} elseif ($type == 'RP') {
			$settings['mail'] = isset($record['mail']) ? $record['mail'] : '';
			$settings['txt'] = isset($record['txt']) ? $record['txt'] : '';
		} elseif ($type == 'SSHFP') {
			$settings['algorithm'] = isset($record['algorithm']) ? $record['algorithm'] : 1;
			$settings['fptype'] = isset($record['fptype']) ? $record['fptype'] : (isset($record['fp_type']) ? $record['fp_type'] : 1);
		} elseif ($type == 'NAPTR') {
			$settings['addRecordOrder'] = isset($record['order']) ? $record['order'] : 0;
			$settings['addRecordPref'] = isset($record['pref']) ? $record['pref'] : 0;
			$settings['flag'] = isset($record['flag']) ? $record['flag'] : '';
			$settings['addRecordParams'] = isset($record['params']) ? $record['params'] : '';
			$settings['addRecordRegexp'] = isset($record['regexp']) ? $record['regexp'] : '';
			$settings['addRecordReplace'] = isset($record['replace']) ? $record['replace'] : '';
		} elseif ($type == 'CAA') {
			$settings['caa_flag'] = isset($record['caa_flag']) ? $record['caa_flag'] : 0;
			$settings['caa_type'] = isset($record['caa_type']) ? $record['caa_type'] : 'issue';
			$settings['caa_value'] = isset($record['caa_value']) ? $record['caa_value'] : '';
		} elseif ($type == 'TLSA') {
			$settings['usage'] = isset($record['tlsa_usage']) ? $record['tlsa_usage'] : 0;
			$settings['selector'] = isset($record['tlsa_selector']) ? $record['tlsa_selector'] : 0;
			$settings['matchingtype'] = isset($record['tlsa_matching_type']) ? $record['tlsa_matching_type'] : 0;
		} elseif ($type == 'DS') {
			$settings['key-tag'] = isset($record['key_tag']) ? $record['key_tag'] : '';
			$settings['algorithm'] = isset($record['algorithm']) ? $record['algorithm'] : '';
			$settings['digest-type'] = isset($record['digest_type']) ? $record['digest_type'] : '';
		} elseif ($type == 'CERT') {
			$settings['cert-type'] = isset($record['cert_type']) ? $record['cert_type'] : '';
			$settings['key-tag'] = isset($record['key_tag']) ? $record['key_tag'] : '';
			$settings['algorithm'] = isset($record['algorithm']) ? $record['algorithm'] : '';
		} elseif ($type == 'HINFO') {
			$settings['cpu'] = isset($record['cpu']) ? $record['cpu'] : '';
			$settings['os'] = isset($record['os']) ? $record['os'] : '';
		} elseif ($type == 'LOC') {
			foreach (array('lat-deg'=>'lat_deg','lat-min'=>'lat_min','lat-sec'=>'lat_sec','lat-dir'=>'lat_dir','long-deg'=>'long_deg','long-min'=>'long_min','long-sec'=>'long_sec','long-dir'=>'long_dir','altitude'=>'altitude','size'=>'size','h-precision'=>'h_precision','v-precision'=>'v_precision') as $formKey => $recordKey) {
				$settings[$formKey] = isset($record[$recordKey]) ? $record[$recordKey] : '';
			}
		} elseif ($type == 'SMIMEA') {
			$settings['smimea-usage'] = isset($record['smimea_usage']) ? $record['smimea_usage'] : 0;
			$settings['smimea-selector'] = isset($record['smimea_selector']) ? $record['smimea_selector'] : 0;
			$settings['smimea-matching-type'] = isset($record['smimea_matching_type']) ? $record['smimea_matching_type'] : 0;
		} elseif ($type == 'SVCB' || $type == 'HTTPS') {
			$settings['addRecordPriority'] = isset($record['priority']) ? $record['priority'] : 0;
			$settings['addRecordParameters'] = isset($record['parameters']) ? $record['parameters'] : '';
		}

		return $settings;
	}

	/**
	 * Maps a fetched DNS record into the API field names needed to modify only the TTL.
	 *
	 * @param array $record
	 * @param int|string $ttl
	 * @return array
	 */
	private function recordToEditSettings($record, $ttl) {
		$settings = array(
			'host' => isset($record['host']) ? $record['host'] : '',
			'record' => isset($record['record']) ? $record['record'] : '',
			'ttl' => $ttl,
		);

		foreach (array('priority','weight','port','mail','txt','algorithm','fptype','order','pref','flag','params','regexp','replace','caa_flag','caa_type','caa_value','tlsa_usage','tlsa_selector','tlsa_matching_type','cpu','os','altitude','size','parameters') as $key) {
			if (isset($record[$key])) {
				$settings[$key] = $record[$key];
			}
		}

		foreach (array('key-tag'=>'key_tag','digest-type'=>'digest_type','cert-type'=>'cert_type','cert-key-tag'=>'key_tag','cert-algorithm'=>'algorithm','lat-deg'=>'lat_deg','lat-min'=>'lat_min','lat-sec'=>'lat_sec','lat-dir'=>'lat_dir','long-deg'=>'long_deg','long-min'=>'long_min','long-sec'=>'long_sec','long-dir'=>'long_dir','h-precision'=>'h_precision','v-precision'=>'v_precision','smimea-usage'=>'smimea_usage','smimea-selector'=>'smimea_selector','smimea-matching-type'=>'smimea_matching_type') as $apiKey => $recordKey) {
			if (isset($record[$recordKey])) {
				$settings[$apiKey] = $record[$recordKey];
			}
		}

		if (isset($record['fp_type']) && !isset($settings['fptype'])) {
			$settings['fptype'] = $record['fp_type'];
		}

		return $settings;
	}

	/**
	 * Enables/returns Dynamic URL for A/AAAA records and returns the updated records table variables.
	 *
	 * @param array $zoneInfo
	 * @param int|string $record_id
	 * @return array
	 */
	public function activateDynamicUrl($zoneInfo, $record_id) {
		$record = $this->core->Controller->getRecordById($zoneInfo['name'], $record_id);
		if (empty($record) || !in_array($record['type'], array('A', 'AAAA'))) {
			$response = array('status' => 'error', 'description' => 'Dynamic URL is available only for A and AAAA records.');
		} else {
			$response = $this->core->Records->getDynamicUrl($zoneInfo['name'], $record_id);
			if (isset($response['dynamic-url'])) {
				$response['description'] = 'Dynamic URL: ' . $response['dynamic-url'];
			} elseif (isset($response['url'])) {
				$response['description'] = 'Dynamic URL: ' . $response['url'];
			} elseif (!isset($response['description']) && !isset($response['statusDescription'])) {
				$response['description'] = 'Dynamic URL request completed.';
			}
		}

		$recordTypes = $this->core->Controller->getRecordTypes($zoneInfo['zone']);
		return $this->getRecords($zoneInfo, 'all', $recordTypes, $this->params['failoverChecks'], $this->params['productid'], $response);
	}

	/**
	 * Creates a Monitoring check based on the selected DNS record.
	 *
	 * @param array $zoneInfo
	 * @param int|string $record_id
	 * @return array
	 */
	public function createMonitoringCheck($zoneInfo, $record_id) {
		$record = $this->core->Controller->getRecordById($zoneInfo['name'], $record_id);
		$settings = $this->buildMonitoringSettings($zoneInfo, $record);
		if (empty($settings)) {
			$response = array('status' => 'error', 'description' => 'A monitoring check could not be created for this record type.');
		} else {
			$response = $this->core->Records->createMonitoringCheck($settings);
			if (!isset($response['description']) && isset($response['id'])) {
				$response['description'] = 'Monitoring check created. ID: ' . $response['id'];
			} elseif (!isset($response['description']) && !isset($response['statusDescription'])) {
				$response['description'] = 'Monitoring check request completed.';
			}
		}

		$recordTypes = $this->core->Controller->getRecordTypes($zoneInfo['zone']);
		return $this->getRecords($zoneInfo, 'all', $recordTypes, $this->params['failoverChecks'], $this->params['productid'], $response);
	}

	/**
	 * Mass updates TTL for selected records.
	 *
	 * @param array $zoneInfo
	 * @param array $recordIds
	 * @param int|string $ttl
	 * @return array
	 */
	public function massEditTtl($zoneInfo, $recordIds, $ttl) {
		$updated = 0;
		$failed = 0;
		if (!is_array($recordIds)) {
			$recordIds = array();
		}

		foreach ($recordIds as $record_id) {
			$record = $this->core->Controller->getRecordById($zoneInfo['name'], $record_id);
			if (empty($record)) {
				$failed++;
				continue;
			}
			$settings = $this->recordToEditSettings($record, $ttl);
			$response = $this->core->Records->recordEdit($zoneInfo['name'], $record_id, $settings);
			if (isset($response['status']) && $response['status'] == 'success') {
				$updated++;
			} else {
				$failed++;
			}
		}

		$response = array('status' => ($failed > 0 ? 'error' : 'success'), 'description' => 'TTL updated on ' . $updated . ' record(s)' . ($failed > 0 ? '; ' . $failed . ' failed.' : '.'));
		$recordTypes = $this->core->Controller->getRecordTypes($zoneInfo['zone']);
		return $this->getRecords($zoneInfo, 'all', $recordTypes, $this->params['failoverChecks'], $this->params['productid'], $response);
	}

	/**
	 * Builds default Monitoring API parameters from a DNS record.
	 *
	 * @param array $zoneInfo
	 * @param array $record
	 * @return array
	 */
	private function buildMonitoringSettings($zoneInfo, $record) {
		if (empty($record) || empty($record['type'])) {
			return array();
		}

		$type = $record['type'];
		$host = isset($record['host']) ? $record['host'] : '';
		$fqdn = $host . (mb_strlen($host) > 0 ? '.' : '') . $zoneInfo['name'];
		$name = preg_replace('/[^a-zA-Z0-9 .-]/', '-', $fqdn . ' ' . $type);
		$name = mb_substr($name, 0, 32, 'UTF-8');
		$recordValue = isset($record['record']) ? trim($record['record']) : '';

		if (in_array($type, array('A', 'AAAA')) && !empty($recordValue)) {
			return array(
				'name' => $name,
				'check_type' => 17,
				'ip' => $recordValue,
				'check_period' => 600,
				'monitoring_region' => 'global',
			);
		}

		if (in_array($type, array('A','AAAA','CNAME','NS','MX','TXT','SPF','SRV','CAA','DS','TLSA','HTTPS','SVCB')) && !empty($fqdn)) {
			$queryResponse = $recordValue;
			if ($type == 'MX' && isset($record['priority'])) {
				$queryResponse = trim($record['priority'] . ' ' . $recordValue);
			} elseif ($type == 'SRV') {
				$queryResponse = trim((isset($record['priority']) ? $record['priority'] : '0') . ' ' . (isset($record['weight']) ? $record['weight'] : '0') . ' ' . (isset($record['port']) ? $record['port'] : '0') . ' ' . $recordValue);
			} elseif ($type == 'CAA') {
				$queryResponse = trim((isset($record['caa_flag']) ? $record['caa_flag'] : '0') . ' ' . (isset($record['caa_type']) ? $record['caa_type'] : 'issue') . ' ' . (isset($record['caa_value']) ? $record['caa_value'] : ''));
			}

			return array(
				'name' => $name,
				'check_type' => 10,
				'host' => $fqdn,
				'query_type' => $type,
				'query_response' => $queryResponse,
				'check_period' => 600,
				'monitoring_region' => 'global',
			);
		}

		return array();
	}

	public function editRecord($zoneInfo, $record_id) {
		$record = $this->core->Controller->getRecordById($zoneInfo['name'], $record_id);

		if (empty($record)) {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
		}

		$record['full_host'] = $this->core->Helper->getUnicodeName($record['host'] . (mb_strlen($record['host']) > 0 ? '.' : '') . $zoneInfo['name']);
		$shortName = $this->core->Helper->shortenLongName($zoneInfo['name'], 34);
		$uniHost = $this->core->Helper->getUnicodeName($record['host']);

		$ttl = $this->core->Controller->getAvailableTTL();

		return array(
			'pagetitle' => 'Edit DNS Record - ' . $zoneInfo['name'],
			'shortName' => $shortName,
			'record' => $record,
			'uniHost' => $uniHost,
			'zone' => $zoneInfo['name'],
			'ttls' => $ttl,
			'cloudAction' => 'edit-record',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * 
	 * @param array $zoneInfo
	 * @param int $record_id
	 * @param array $settings
	 * @param int $status
	 * @param string $recordType
	 * @return array
	 */
	public function doEditRecord($zoneInfo, $record_id, $settings, $status, $recordType) {
		$record = $this->core->Controller->getRecordById($zoneInfo['name'], $record_id);
		$uniHost = $this->core->Helper->getUnicodeName($record['host']);
		$shortName = $this->core->Helper->shortenLongName($zoneInfo['name'], 34);

		$response = $this->core->Records->recordEdit($zoneInfo['name'], $record_id, $settings, $status);

		// merging the arrays so the new values can be used to fill
		// the form, if there is an error with the record saving 
		$settings['fp_type'] = $this->core->Helper->getPost('fp_type');
		$record = array_merge($record, $settings);
		$ttl = $this->core->Controller->getAvailableTTL();
		
		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
		}
		
		return array(
			'pagetitle' => 'Edit DNS Record - ' . $zoneInfo['name'],
			'response' => $response,
			'shortName' => $shortName,
			'record' => $record,
			'uniHost' => $uniHost,
			'settings' => $settings,
			'zone' => $zoneInfo['name'],
			'ttls' => $ttl,
			'cloudAction' => 'do-edit-record',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Gets the SOA settings
	 * @param array $zoneInfo
	 * @return array
	 */
	public function getSOASettings($zoneInfo) {
		if (!empty($zoneInfo)) {
			$soa = $this->core->SOA->getSOA($zoneInfo['name']);

			if (empty($soa)) {
				$response = array('status' => 'error', 'description' => 'It looks like there is no such zone');
			}

			$unicodePrymaryNS = $this->core->Helper->getUnicodeName($soa['primaryNS']);

			return array(
				'pagetitle' => 'SOA - ' . $zoneInfo['name'],
				'response' => $response,
				'soa' => $soa,
				'primaryNS' => $unicodePrymaryNS,
				'cloudAction' => 'soa-settings',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		} else {
			return array('response' => 'An error occured');
		}
	}

	/**
	 * 
	 * @param array $zoneInfo
	 * @param array $soa
	 * @param string $soaAction
	 * @return array
	 */
	public function editSOA($zoneInfo, $soa, $soaAction) {
		$zone = $zoneInfo['name'];

		if ($soaAction == 'do_save') {
			$response = $this->core->SOA->editSOA($zone, $soa['primaryNS'], $soa['adminMail'], $soa['refresh'], $soa['retry'], $soa['expire'], $soa['defaultTTL']);
		} elseif ($soaAction == 'do_reset') {
			$response = $this->core->SOA->resetSOA($zone);
		} else {
			$response = array('status' => 'error', 'description' => 'An error occurred while saving the changes!');
		}

		if (isset($response['status']) && $response['status'] == 'error') {
			$soaSettings = $this->core->SOA->getSOA($zone);
			$soa = array_merge($soaSettings, $soa);
		} else {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=soa-settings&zone={$zone}");
		}

		return array(
			'pagetitle' => 'SOA - ' . $zone,
			'response' => $response,
			'zoneInfo' => $zoneInfo,
			'soa' => $soa,
			'cloudAction' => 'edit-soa-settings',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Calls a method depending on the zone type (slave or master)
	 * @param array 
	 * $zoneInfo
	 * @param string $type Record type to return only these records for master zones
	 * @return array
	 */
	public function getSettings($zoneInfo, $type, $recordTypes, $failoverChecks, $productid) {
		if (!empty($zoneInfo)) {
			
			if (isset($zoneInfo['status']) && $zoneInfo['status'] == '0') {
				return array('response' => array('status' => $zoneInfo['status'], 'description' => 'The DNS zone ('.$zoneInfo['name'].') is currently unavailable, please contact Technical Support for assistance.'));
			}
			
			if ($zoneInfo['type'] == 'master') {
				return $this->getRecords($zoneInfo, $type, $recordTypes, $failoverChecks, $productid);
			} else {
				return $this->getMasterServers($zoneInfo);
			}
		} else {
			return array('response' => 'There is no such DNS zone');
		}
	}

	/**
	 * 
	 * @param array $zoneInfo
	 * @param string $type
	 * @param array
	 * @return array
	 */
	public function getRecords($zoneInfo, $type, $recordTypes, $failoverChecks, $productid, $response = array()) {
		$records = $this->core->Controller->getRecords($zoneInfo['name'], $type);
		$freeProductId = $this->core->Configuration->getFreeProductId();
		if ($productid != $freeProductId) {
			$foColumn = $this->core->Database->columnCheck('mod_cloudns_zones', 'fo_checks');
		} else {
			$foColumn = $this->core->Database->columnCheck('tbldomains', 'fo_checks');
		}

		foreach ($records as &$row) {
			$row['full_host'] = $this->core->Helper->getUnicodeName($row['host'] . (mb_strlen($row['host']) > 0 ? '.' : '') . $zoneInfo['name']);
			$row['shortHost'] = $this->core->Helper->shortenLongName($row['host'] . (mb_strlen($row['host']) > 0 ? '.' : '') . $zoneInfo['name'], 24);
						
			if ($row['type'] == 'MX') {
				$row['priority'] = $row['priority'] . ' ';
			}
			if ($row['type'] == 'SRV') {
				$row['priority'] = $row['priority'] . ' ' . $row['weight'] . ' ' . $row['port'] . ' ';
			}
			if (in_array($row['type'], array('SSHFP', 'TXT', 'SPF', 'MX', 'SRV'))) {
				$row['shortRecord'] = $this->core->Helper->shortenLongString($row['priority'] . $row['record'], 24);
			} elseif ($row['type'] == 'RP') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['mail'], 24);
			} elseif ($row['type'] == 'NAPTR') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['order'] . ' ' . $row['pref'] . ' "' . $row['flag'] . '" "' . $row['params'] . '" ' . (!empty($row['regexp']) ? '"' . $row['regexp'] . '"' : '""') . ' ' . (!empty($row['replace']) ? $row['replace'] : '.'), 30);
			} elseif ($row['type'] == 'CAA') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['caa_flag'] . ' ' . $row['caa_type'] . ' "' . $row['caa_value'] . '"', 30);
			} elseif ($row['type'] == 'TLSA') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['tlsa_usage'].' '.$row['tlsa_selector'].' '.$row['tlsa_matching_type']. ' '.$row['record'], 30);
			} elseif ($row['type'] == 'DS') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['key_tag'].' '.$row['algorithm'].' '.$row['digest_type']. ' '.$row['record'], 30);
			} elseif ($row['type'] == 'CERT') {
				$row['shortRecord'] = $this->core->Helper->shortenLongString($row['cert_type'].' '.$row['key_tag'].' '.$row['algorithm']. ' '.$row['record'], 24);
			} elseif ($row['type'] == 'HINFO') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['cpu'].' '.$row['os'], 30);
			} elseif ($row['type'] == 'LOC') {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['lat_deg'].' '.$row['lat_min'].' '.$row['lat_sec'].' '.strtoupper($row['lat_dir']).' '.$row['long_deg'].' '.$row['long_min'].' '.$row['long_sec'].' '.strtoupper($row['long_dir']).' '.$row['altitude'].' '.$row['size'].' '.$row['h_precision'].' '.$row['v_precision'], 30);
			} elseif ($row['type'] == 'SMIMEA') {
				$row['shortRecord'] = $this->core->Helper->shortenLongString($row['smimea_usage'].' '.$row['smimea_selector'].' '.$row['smimea_matching_type']. ' '.$row['record'], 30);
			} elseif ($row['type'] == 'HTTPS' || $row['type'] == 'SVCB') {
				$row['shortRecord'] = $this->core->Helper->shortenLongString($row['priority'].' '.$row['record'].' '.$row['parameters'], 30);
			} else {
				$row['shortRecord'] = $this->core->Helper->shortenLongName($row['record'], 24);
			}
			
			$row['ttl_seconds'] = $this->core->Helper->convertSeconds($row['ttl']);
		}

		$ttl = $this->core->Controller->getAvailableTTL();

		return array(
			'pagetitle' => 'DNS Records - ' . $zoneInfo['name'],
			'zoneInfo' => $zoneInfo,
			'recordTypes' => $recordTypes,
			'defaultType' => $type,
			'foColumn' => $foColumn,
			'records' => $records,
			'cloudAction' => 'zone-settings',
			'failover' => $failover,
			'failoverChecks' => $failoverChecks,
			'ttls' => $ttl,
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
			'response' => $response,
		);
	}

	/**
	 * Deletes a record
	 * @param array $zoneInfo
	 * @param int $record_id
	 * @return type
	 */
	/**
	 * Deletes a mail forward.
	 *
	 * @param array $zoneInfo
	 * @param int|string $forwardId
	 * @return array
	 */
	public function deleteForward($zoneInfo, $forwardId) {
		$response = $this->core->Controller->deleteForward($zoneInfo['name'], $forwardId);
		if (isset($response['status']) && $response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=mail-forwarding&zone={$zoneInfo['name']}");
		}

		return $this->getForwards($zoneInfo, $response);
	}

	public function deleteRecord($zoneInfo, $record_id) {
		$response = $this->core->Controller->deleteRecord($zoneInfo['name'], $record_id);
		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
		}
		
		$recordTypes = $this->core->Controller->getRecordTypes($zoneInfo['zone']);
		
		return $this->getRecords($zoneInfo, 'all', $recordTypes, $this->params['failoverChecks'], $this->params['productid'], $response);
	}

	/**
	 * Deletes a zone
	 * @param array $zoneInfo
	 * @return array
	 */
	public function deleteZone($zone) {
		$response = $this->core->Controller->deleteZone($zone);

		if ($response['status'] == 'success') {
			// DomainMonger Patch 486:
			// After deleting a zone, stay in the DNS Records workflow.
			// Send the user to the next available zone, or to Add Zone when no zones remain.
			$domainSwitchZones = $this->core->Controller->getDomainSwitchZones();
			if (is_array($domainSwitchZones) && !empty($domainSwitchZones) && isset($domainSwitchZones[0]['name']) && trim((string)$domainSwitchZones[0]['name']) !== '') {
				$firstZone = urlencode($domainSwitchZones[0]['name']);
				$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$firstZone}");
			}

			if ($this->params['registeredDomains'] != 'on') {
				$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=add-new-zone");
			}

			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}");
		}
		$toReturn = $this->getZones();
		$toReturn['response'] = $response['description'];

		return $toReturn;
	}

	/**
	 * Gets the slave servers for slave zones
	 * @param array $zoneInfo
	 * @return array
	 */
	public function getBINDSettings($zoneInfo) {
		$ipv4 = '';
		$ipv6 = '';
		foreach ($this->core->Servers->getMasterServers() as $server) {
			$ipv4 .= "		{$server['ip4']};\n";
			if (!is_null($server['ip6'])) {
				$ipv6 .= "		{$server['ip6']};\n";
			}
		}

		return array(
			'pagetitle' => 'BIND Settings - ' . $zoneInfo['name'],
			'zoneInfo' => $zoneInfo,
			'ipv4' => $ipv4,
			'ipv6' => $ipv6,
			'cloudAction' => 'bind-settings',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Gets the master servers of a slave zone, added by the client
	 * @param array $zoneInfo
	 * @return array
	 */
	public function getMasterServers($zoneInfo) {
		$masterServers = $this->core->Slave->getMasterServers($zoneInfo['name']);
		$response = '';
		return array(
			'pagetitle' => 'Master Servers - ' . $zoneInfo['name'],
			'zoneInfo' => $zoneInfo,
			'response' => $response,
			'masterServers' => $masterServers,
			'cloudAction' => 'zone-settings',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Deletes a master server
	 * @param string $zone
	 * @param string $master
	 * @return array
	 */
	public function deleteMasterServer($zone, $master) {
		$response = $this->core->Slave->deleteMasterServer($zone, $master);
		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zone}");
		}
		$masterServers = $this->core->Slave->getMasterServers($zone);
		return array(
			'pagetitle' => 'Master Servers - ' . $zone,
			'response' => $response,
			'masterServers' => $masterServers,
			'cloudAction' => 'delete-master-server',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * 
	 * @param string $zone
	 * @param string $master
	 * @return array
	 */
	public function addMasterServer($zone, $master) {
		$response = $this->core->Slave->addMasterServer($zone, $master);
		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zone}");
		}
		$masterServers = $this->core->Slave->getMasterServers($zone);

		return array(
			'pagetitle' => 'Master Servers - ' . $zone,
			'response' => $response,
			'masterServers' => $masterServers,
			'cloudAction' => 'add-master-server',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Gets the update status of a zone
	 * @param array $zoneInfo
	 * @return array
	 */
	public function getUpdateStatus($zoneInfo) {
		$updated = $this->core->Zones->getUpdateStatus($zoneInfo['name']);
		$updateReport = $this->core->Zones->getUpdateStatusReport($zoneInfo['name']);

		return array(
			'pagetitle' => 'Status - ' . $zoneInfo['name'],
			'response' => '',
			'updated' => $updated,
			'updateReport' => $updateReport,
			'zoneInfo' => $zoneInfo,
			'cloudAction' => 'update-status',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Updates the zone at the DNS servers
	 * @param array $zoneInfo
	 * @return array
	 */
	public function updateZone($zoneInfo) {
		$response = $this->core->Zones->updateZone($zoneInfo['name']);
		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=update-status&zone={$zoneInfo['name']}");
		} else {
			$updated = $this->core->Zones->getUpdateStatus($zoneInfo['name']);
			$updateReport = $this->core->Zones->getUpdateStatusReport($zoneInfo['name']);

			return array(
				'pagetitle' => 'Status - ' . $zoneInfo['name'],
				'response' => $response,
				'updated' => $updated,
				'updateReport' => $updateReport,
				'zoneInfo' => $zoneInfo,
				'cloudAction' => 'update',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		}
	}

	/**
	 * Imports records from a text area in BIND or TinyDNS format
	 * @param string $zone
	 * @param string $records A list with all the records submited by the client
	 * @param int $delete 1 or 0
	 * @param string $format BIND or TinyDNS
	 * @return array
	 */
	public function importRecords($zone, $records, $delete, $format) {
		$response = $this->core->Records->import($zone, $records, $format, $delete);

		if ($response['status'] == 'success') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zone}");
		}
		return array(
			'pagetitle' => 'Import Zone File - ' . $zone,
			'response' => $response,
			'cloudAction' => 'import',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	/**
	 * Returns the data needed to display the statistics
	 * @param array $zoneInfo
	 * @param string $date
	 * @return array
	 */
	public function getStats($zoneInfo, $date) {
		$zone = $zoneInfo['name'];
		$stats = $this->core->Statistics->getZoneStats($zone, $date);

		$requests = array_sum($stats);
		$statsTable = '';
		foreach ($stats as $key => $value) {
			$showLink = true;

			if ($date == 'last-30-days') {
				$unixtime = strtotime($key);
				$timeHTML = date('l, F d, Y', $unixtime);
				$timeLink = date('Ymd', $unixtime);
				$hover = date('Y-m-d', $unixtime);

				if (strtotime($hover) < strtotime('-1 month')) {
					$showLink = false;
				}
			} else if (strlen($date) == 8) {
				$currhour = $key;
				$nexthour = $currhour + 1;

				if ($nexthour < 10) {
					$nexthour = "0{$nexthour}";
				}

				$timeLink = 0;

				$timeHTML = "{$currhour}:00 - {$nexthour}:00 , " . date('F d , Y', strtotime($date));
				$hover = date('Y-m-d', strtotime($date)) . " $currhour:00 - $nexthour:00";
				$showLink = false;
			} elseif (strlen($date) == 6) {
				$timeHTML = date('l, F d, Y', strtotime($date . $key));
				$timeLink = $date . $key;
				$hover = date('Y-m-d', strtotime($date . $key));
				if (strtotime($hover) < strtotime('-1 month')) {
					$showLink = false;
				}
				$noLinkDate = date('Ymd', strtotime('-1 month'));
			} elseif (strlen($date) == 4) {
				$timeHTML = date('F, Y', strtotime($date . $key . '01'));
				$timeLink = $date . $key;
				$hover = date('Y-m', strtotime($date . $key . '01'));
			} else {
				$hover = $timeHTML = $timeLink = $key;
			}

			// html
			if ($requests == 0) {
				$percent = 0;
			} else {
				$percent = ($value / $requests) * 100;
			}

			$tdWidth = strlen($date) * 42;
			if (strlen($date) == 12) {
				$tdWidth = 270;
			} elseif (strlen($date) == 0) {
				$tdWidth = 100;
			}

			$sortTime = is_numeric($timeLink) ? $timeLink : preg_replace('/[^0-9]/', '', (string) $hover);
			$statsTable .= '<tr ' . (($showLink == true || strlen($date) < 6) ? 'class="pointer" onClick="javascript: location.href=\'clientarea.php?action=productdetails&id=' . $this->params['serviceid'] . '&customAction=statistics&zone=' . $zone . '&date=' . $timeLink . '\'"' : '') . '>
				<td class="text-right" data-order="' . $sortTime . '" style="width:' . $tdWidth . 'px;">' . $timeHTML . '</td>
				<td data-order="' . (int) $value . '">
					<div style="height:10px; width:' . round((450 * $percent) / 100) . 'px; background-color:#ffa900; margin:3px 5px 0 5px;" class="pull-left">&nbsp</div> ' . number_format($percent, 2) . '% (' . number_format($value) . ' Requests)
				</td>
			</tr>';
		}

		$statLinks = '';
		if (strlen($date) == 8) {
			$statLinks = ' » <a href="clientarea.php?action=productdetails&id=' . $this->params['serviceid'] . '&customAction=statistics&zone=' . $zone . '&date=' . substr($date, 0, 4) . '">' . substr($date, 0, 4) . '</a> » <a href="clientarea.php?action=productdetails&id=' . $this->params['serviceid'] . '&customAction=statistics&zone=' . $zone . '&date=' . substr($date, 0, 6) . '">' . date('F', strtotime($date)) . '</a> » ' . date('l d', strtotime(substr($date, 0, 8)));
		} elseif (strlen($date) == 6) {
			$statLinks = ' » <a href="clientarea.php?action=productdetails&id=' . $this->params['serviceid'] . '&customAction=statistics&zone=' . $zone . '&date=' . substr($date, 0, 4) . '">' . substr($date, 0, 4) . '</a> » ' . date('F', strtotime($date . '01'));
		} elseif (strlen($date) == 4) {
			$statLinks = ' » ' . substr($date, 0, 4);
		}

		$response = '';
		return array(
			'pagetitle' => 'Statistics - ' . $zone,
			'response' => $response,
			'statsTable' => $statsTable,
			'statLinks' => $statLinks,
			'requests' => $requests,
			'date' => $date,
			'zoneInfo' => $zoneInfo,
			'cloudAction' => 'statistics',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function suspendZones() {
		$zones = $this->core->Controller->getZones();

		if (!isset($zones['status'])) {
			$message = array();
			foreach ($zones as $zone) {
				$response = $this->core->Zones->suspend($zone['name']);
				if (isset($response['status']) && $response['status'] == 'error') {
					$message[] = $response['description'];
				}
			}

			if (!empty($message)) {
				return implode(', ', $message);
			}
		}

		return 'success';
	}

	public function unSuspendZones() {
		$zones = $this->core->Controller->getZones();

		if (!isset($zones['status'])) {
			$message = array();
			foreach ($zones as $zone) {
				$response = $this->core->Zones->unSuspend($zone['name']);
				if (isset($response['status']) && $response['status'] == 'error') {
					$message[] = $response['description'];
				}
			}
			if (!empty($message)) {
				return implode(', ', $message);
			}
		}

		return 'success';
	}

	public function terminateAccount() {
		$zones = $this->core->Controller->getZones();
		if (!isset($zones['status'])) {
			$message = array();
			foreach ($zones as $zone) {
				$this->core->Controller->deleteZone($zone['name']);
			}
		}

		return 'success';
	}

	public function getFailoverSettings($zoneInfo, $record_id) {
		$zone = $zoneInfo['name'];
		$response = $this->core->Failover->failoverSettings($zone, $record_id);

		if ($response['status'] == '-1') {
			return $this->failoverView($zoneInfo, $record_id);
		} else {
			return $this->failoverSettings($zoneInfo, $record_id);
		}
	}

	public function failoverSettings($zoneInfo, $record_id) {
		$zone = $zoneInfo['name'];

		$record = $this->core->Controller->getRecordById($zone, $record_id);
		$failover = $this->core->Failover->failoverSettings($zone, $record_id);
		$checkTypes = $this->core->Failover->getCheckTypes();

		$record['full_host'] = $this->core->Helper->getUnicodeName($record['host'] . (mb_strlen($record['host']) > 0 ? '.' : '') . $zoneInfo['name']);
		$shortName = $this->core->Helper->shortenLongName($zone, 34);
		$uniHost = $this->core->Helper->getUnicodeName($record['host']);

		return array(
			'pagetitle' => 'DNS Failover & Monitoring',
			'shortName' => $shortName,
			'record' => $record,
			'failover' => $failover,
			'fullHost' => $record['full_host'],
			'uniHost' => $uniHost,
			'zone' => $zone,
			'settings' => array(),
			'checkTypes' => $checkTypes,
			'cloudAction' => 'failover-new',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function activateFailover($zoneInfo, $record_id, $settings) {
		$zone = $zoneInfo['name'];
		
		$checkTypes = $this->core->Failover->getCheckTypes();
		$record = $this->core->Controller->getRecordById($zone, $record_id);
		$record['full_host'] = $this->core->Helper->getUnicodeName($record['host'] . (mb_strlen($record['host']) > 0 ? '.' : '') . $zoneInfo['name']);
		$shortName = $this->core->Helper->shortenLongName($zone, 34);
		$uniHost = $this->core->Helper->getUnicodeName($record['host']);
		$response['status'] = 'success';
		
		if ($settings['notification_type'] == Cloudns_Failover::NOTIFICATION_TYPE_EMAIL) {
			$notification_value = $this->core->Helper->getUnicodeName($settings['notification_value']);
			if (!$this->core->Valid->mail($notification_value)) {
				$response['status'] = 'error';
				$response['description'] = 'Invalid monitoring notification mail';
			}
		} else {
			$notification_value = $this->core->Helper->convertWRtoAscii($settings['notification_value']);
			$url = parse_url($notification_value);
			$valid = false;
			
			if (isset($url['host'])) {
				$valid = $this->core->Valid->host($url['host']);
			}
			
			if (!isset($url['host']) || (isset($url['host']) && !$valid['status'])) {
				$response['status'] = 'error';
				$response['description'] = 'Invalid monitoring notification URL';
			}
			
			if (!isset($url['scheme']) || (isset($url['scheme']) && !in_array($url['scheme'], array('http', 'https', 'ftp')))) {
				$response['status'] = 'error';
				$response['description'] = 'Invalid monitoring notification URL';
			}
		}
		
		if ($response['status'] != "success") {
			return array(
				'pagetitle' => 'DNS Failover & Monitoring',
				'shortName' => $shortName,
				'record' => $record,
				'response' => $response,
				'fullHost' => $record['full_host'],
				'uniHost' => $uniHost,
				'zone' => $zone,
				'settings' => array(),
				'checkTypes' => $checkTypes,
				'cloudAction' => 'failover-new',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		}
		
		if ($settings['notification_type']  == Cloudns_Failover::NOTIFICATION_TYPE_WEBHOOK_UP || $settings['notification_type'] == Cloudns_Failover::NOTIFICATION_TYPE_WEBHOOK_DOWN) {
			
			$response = $this->core->Failover->failoverActivate($zone, $record_id, $settings);

			//add the webhook notification
			if ($response['status'] == "success") {
				$webhookResponse = $this->core->Failover->createNotification($zone, $record_id, $settings['notification_type'], $settings['notification_value']);
			}
		} else {
			$settings['notification_mail'] = $settings['notification_value'];
			$response = $this->core->Failover->failoverActivate($zone, $record_id, $settings);
		}
		
		if ($response['status'] == "success") {

			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");

			return array(
				'pagetitle' => 'DNS Failover & Monitoring',
				'shortName' => $shortName,
				'response' => $response,
				'record' => $record,
				'fullHost' => $record['full_host'],
				'uniHost' => $uniHost,
				'settings' => $settings,
				'zone' => $zone,
				'cloudAction' => 'failover-activate',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		} else {
			return array(
				'pagetitle' => 'DNS Failover & Monitoring',
				'shortName' => $shortName,
				'record' => $record,
				'response' => $response,
				'fullHost' => $record['full_host'],
				'uniHost' => $uniHost,
				'zone' => $zone,
				'settings' => array(),
				'checkTypes' => $checkTypes,
				'cloudAction' => 'failover-new',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		}
	}

	public function deactivateFailover($zoneInfo, $record_id) {
		$zone = $zoneInfo['name'];

		$response = $this->core->Failover->failoverDeactivate($zone, $record_id, $settings);
		if ($response['status'] == "success") {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
		}
		return $this->getRecords($zoneInfo, 'all', $response);
	}

	public function editFailover($zoneInfo, $record_id, $settings) {
		$zone = $zoneInfo['name'];

		$record = $this->core->Controller->getRecordById($zone, $record_id);
		$checkTypes = $this->core->Failover->getCheckTypes();
		$response = $this->core->Failover->failoverEdit($zone, $record_id, $settings);
		
		$record['full_host'] = $this->core->Helper->getUnicodeName($record['host'] . (mb_strlen($record['host']) > 0 ? '.' : '') . $zoneInfo['name']);
		$shortName = $this->core->Helper->shortenLongName($zone, 34);
		$uniHost = $this->core->Helper->getUnicodeName($record['host']);
		
		if ($response['status'] == "success") {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=zone-settings&zone={$zoneInfo['name']}");
		
			return array(
				'pagetitle' => 'DNS Failover & Monitoring',
				'shortName' => $shortName,
				'response' => $response,
				'record' => $record,
				'fullHost' => $record['full_host'],
				'uniHost' => $uniHost,
				'settings' => $settings,
				'zone' => $zone,
				'checkTypes' => $checkTypes,
				'cloudAction' => 'failover-edit',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		} else {
			$failover = $this->core->Failover->failoverSettings($zone, $record_id);
			
			return array(
				'pagetitle' => 'DNS Failover & Monitoring',
				'shortName' => $shortName,
				'response' => $response,
				'failover' => $failover,
				'record' => $record,
				'fullHost' => $record['full_host'],
				'uniHost' => $uniHost,
				'zone' => $zone,
				'settings' => $settings,
				'checkTypes' => $checkTypes,
				'cloudAction' => 'failover-view',
				'version' => $this->params['shortVersion'],
				'theme' => $this->params['theme'],
			);
		}
	}

	public function failoverView($zoneInfo, $record_id) {
		$zone = $zoneInfo['name'];
		$record = $this->core->Controller->getRecordById($zone, $record_id);
		$failover = $this->core->Failover->failoverSettings($zone, $record_id);
		$checkTypes = $this->core->Failover->getCheckTypes();

		$record['full_host'] = $this->core->Helper->getUnicodeName($record['host'] . (mb_strlen($record['host']) > 0 ? '.' : '') . $zoneInfo['name']);
		$shortName = $this->core->Helper->shortenLongName($zone, 34);
		$uniHost = $this->core->Helper->getUnicodeName($record['host']);

		return array(
			'pagetitle' => 'DNS Failover & Monitoring',
			'shortName' => $shortName,
			'record' => $record,
			'failover' => $failover,
			'fullHost' => $record['full_host'],
			'uniHost' => $uniHost,
			'zone' => $zone,
			'settings' => array(),
			'checkTypes' => $checkTypes,
			'cloudAction' => 'failover-view',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function failoverActionLog($zoneInfo, $record_id, $page) {
		$zone = $zoneInfo['name'];
		$record = $this->core->Controller->getRecordById($zone, $record_id);
		$rows_per_page = 10;
		$pages = $this->core->Failover->getActionHistoryPages($zone, $record_id, $rows_per_page);

		$pagination = $this->core->helper->showPaging($page, $pages, "clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=failover-action-log&zone={$zone}&dns_record_id={$record_id}&page=PAGE_NUM", null, true);

		$actionLog = $this->core->Failover->getActionHistory($zone, $record_id, $page, $rows_per_page);
		$actionLogTable = '';

		foreach ($actionLog as $row) {
			$date = date('Y-m-d H:i:s', $row['time']);
			if ($row['action'] == Cloudns_Failover::ACTION_RECORD_ACTIVATED) {
				$row['action'] = 'ACTIVATED';
			} elseif ($row['action'] == Cloudns_Failover::ACTION_RECORD_DEACTIVATED) {
				$row['action'] = 'DEACTIVATED';
			} elseif ($row['action'] == Cloudns_Failover::ACTION_RECORD_REPLACED) {
				$row['action'] = 'CHANGED';
			} else {
				$row['action'] = 'UNKNOWN';
			}
			$action = $row['action'];
			$ip = $row['ip'];
			$actionLogTable .= '<tr><td>' . $date . '</td><td class="bold' . (($action == 'CHANGED') ? ' blue"' : (($action == 'ACTIVATED') ? ' green"' : (($action == 'DEACTIVATED') ? ' red"' : (($action == 'UNKNOWN') ? ' grey"' : '')))) . '>' . $action . '</td><td>' . $ip . '</td></tr>';
		}

		$actionLogTable .= '<tr><td class="dataTables_length" colspan="3">' . $pagination . '</td></tr>';

		return array(
			'record' => $record,
			'pagetitle' => 'DNS Failover & Monitoring',
			'actionLogTable' => $actionLogTable,
			'actionLog' => $actionLog,
			'cloudAction' => 'failover-action-log',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function failoverMonitoringLog($zoneInfo, $record_id, $page) {
		$zone = $zoneInfo['name'];
		$record = $this->core->Controller->getRecordById($zone, $record_id);
		$rows_per_page = 10;
		$pages = $this->core->Failover->getCheckHistoryPages($zone, $record_id, $rows_per_page);

		$pagination = $this->core->helper->showPaging($page, $pages, "clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=failover-monitoring-log&zone={$zone}&dns_record_id={$record_id}&page=PAGE_NUM", null, true);

		$monitoringLog = $this->core->Failover->getCheckHistory($zone, $record_id, $page, $rows_per_page);
		$monitoringLogTable = '';
		foreach ($monitoringLog as $row) {
			$date = date('Y-m-d H:i:s', $row['time']);
			if ($row['status'] == Cloudns_Failover::CHECK_STATUS_UP) {
				$row['status'] = 'UP';
			} elseif ($row['status'] == Cloudns_Failover::CHECK_STATUS_DOWN) {
				$row['status'] = 'DOWN';
			} else {
				$row['status'] = 'UNKNOWN';
			}
			$status = $row['status'];
			$ip = $row['ip'];
			$location = $row['checker_location'];
			$monitoringLogTable .= '<tr><td>' . $date . '</td><td class="bold' . ($status == 'UP' ? ' green' : (($status == 'DOWN' ? ' red' : ''))) . '">' . $status . '</td><td>' . $ip . '</td><td>' . $location . '</td></tr>';
		}
		$monitoringLogTable .= '<tr><td class="dataTables_length" colspan="4">' . $pagination . '</td></tr>';

		return array(
			'record' => $record,
			'pagetitle' => 'DNS Failover & Monitoring',
			'cloudAction' => 'failover-monitoring-notifications',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
			'monitoringLog' => $monitoringLog,
			'monitoringLogTable' => $monitoringLogTable,
		);
	}
	
	public function addFailoverNotification($zoneInfo, $record_id, $settings, $page) {
		$zone = $zoneInfo['name'];

		$response = $this->core->Failover->createNotification($zone, $record_id, $settings['notification_type'], $settings['notification_value']);
		
		if ($response['status'] == 'success') {
			$response['status'] = 'info';
		}
		
		$rows_per_page = 10;
		$pages = $this->core->Failover->getNotificationPages($zone, $record_id, $rows_per_page);

		$pagination = $this->core->helper->showPaging($page, $pages, "clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=failover-monitoring-notifications&zone={$zone}&dns_record_id={$record_id}&page=PAGE_NUM", null, true);

		$record = $this->core->Failover->failoverSettings($zone, $record_id);
		$notifications = $this->core->Failover->listNotifications($zone, $record_id, $page, $rows_per_page);

		$notificationsTable = '';
		foreach ($notifications as $row) {
			$status = $row['status'];
			$ip = $row['ip'];
			$location = $row['checker_location'];
			$notificationsTable .= '<tr><td>' . $row['type'] . '</td><td>' . $row['value'] . '</td><td><a href="clientarea.php?action=productdetails&id=' . $this->params['serviceid'] . '&customAction=failover-delete-notification&zone=' . $zone . '&dns_record_id=' . $record_id . '&notification_id=' . $row['notification_id'] . '&page=' . $page . '" title="Delete this record" onclick="return confirm(\'Are you sure you want to delete this record?\');">Delete</a></td>';
		}
		$notificationsTable .= '<tr><td class="dataTables_length" colspan="3">' . $pagination . '</td></tr>';

		return array(
		    'record' => $record,
		    'record_id' => $record_id,
		    'pagetitle' => 'DNS Failover & Monitoring',
		    'cloudAction' => 'failover-monitoring-notifications',
		    'response' => $response,
		    'notifications' => $notifications,
		    'notificationsTable' => $notificationsTable,
		    'version' => $this->params['shortVersion'],
		    'theme' => $this->params['theme'],
		);
	}

	public function deleteFailoverNotification($zoneInfo, $record_id, $notification_id, $page) {
		$zone = $zoneInfo['name'];
		$rows_per_page = 10;
		$notifications = $this->core->Failover->listNotifications($zone, $record_id, 1, $rows_per_page);
		
		if (count($notifications) <= 1) {
			$response = array();
			$response['status'] = 'error';
			$response['description'] = 'You need to have at least 1 active failover notification';
		} else {
			$response = $this->core->Failover->deleteNotification($zone, $record_id, $notification_id);
		}
		
		if ($response['status'] == 'success') {
			$response['status'] = 'info';
		} 
		$pages = $this->core->Failover->getNotificationPages($zone, $record_id, $rows_per_page);
		$pagination = $this->core->helper->showPaging($page, $pages, "clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=failover-monitoring-notifications&zone={$zone}&dns_record_id={$record_id}&page=PAGE_NUM", null, true);

		$record = $this->core->Failover->failoverSettings($zone, $record_id);
		$notifications = $this->core->Failover->listNotifications($zone, $record_id, $page, $rows_per_page);
			
		$notificationsTable = '';
		foreach ($notifications as $row) {
			$status = $row['status'];
			$ip = $row['ip'];
			$location = $row['checker_location'];
			$notificationsTable .= '<tr><td>' . $row['type'] . '</td><td>' . $row['value'] . '</td><td><a href="clientarea.php?action=productdetails&id='.$this->params['serviceid'].'&customAction=failover-delete-notification&zone='.$zone.'&dns_record_id='.$record_id.'&notification_id='.$row['notification_id'].'&page='.$page.'" title="Delete this record" onclick="return confirm(\'Are you sure you want to delete this record?\');">Delete</a></td>';
		}
		$notificationsTable .= '<tr><td class="dataTables_length" colspan="3">' . $pagination . '</td></tr>';

		return array(
			'record' => $record,
			'record_id' => $record_id,
			'pagetitle' => 'DNS Failover & Monitoring',
			'cloudAction' => 'failover-monitoring-notifications',
			'response' => $response,
			'notifications' => $notifications,
			'notificationsTable' => $notificationsTable,
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}
	
	public function failoverMonitoringNotifications($zoneInfo, $record_id, $page) {
		$zone = $zoneInfo['name'];
		$rows_per_page = 10;
		$pages = $this->core->Failover->getNotificationPages($zone, $record_id, $rows_per_page);
		
		$pagination = $this->core->helper->showPaging($page, $pages, "clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=failover-monitoring-notifications&zone={$zone}&dns_record_id={$record_id}&page=PAGE_NUM", null, true);
		
		$record = $this->core->Failover->failoverSettings($zone, $record_id);
		
		$notifications = $this->core->Failover->listNotifications($zone, $record_id, $page, $rows_per_page);
		$notificationsTable = '';
		foreach ($notifications as $row) {
			$status = $row['status'];
			$ip = $row['ip'];
			$location = $row['checker_location'];
			$notificationsTable .= '<tr><td>' . $row['type'] . '</td><td>' . $row['value'] . '</td><td><a href="clientarea.php?action=productdetails&id='.$this->params['serviceid'].'&customAction=failover-delete-notification&zone='.$zone.'&dns_record_id='.$record_id.'&notification_id='.$row['notification_id'].'&page='.$page.'" title="Delete this record" onclick="return confirm(\'Are you sure you want to delete this record?\');">Delete</a></td>';
		}
		$notificationsTable .= '<tr><td class="dataTables_length" colspan="3">' . $pagination . '</td></tr>';
		
		return array(
			'record' => $record,
			'record_id' => $record_id,
			'pagetitle' => 'DNS Failover & Monitoring',
			'cloudAction' => 'failover-monitoring-notifications',
			'notifications' => $notifications,
			'notificationsTable' => $notificationsTable,
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}
	

	public function zoneTransfers($zoneInfo, $response = '') {
		$zone = $zoneInfo['name'];
		$rawTransfers = $this->core->Zonetransfers->listAllowedIps($zone);
		$listResponse = '';
		if (isset($rawTransfers['status']) && $rawTransfers['status'] == 'error') {
			$listResponse = $rawTransfers;
		}

		return array(
			'pagetitle' => 'Zone Transfers - ' . $zone,
			'zone' => $zone,
			'zoneInfo' => $zoneInfo,
			'zoneTransfers' => $this->normalizeZoneTransfers($rawTransfers),
			'zoneTransfersRaw' => $rawTransfers,
			'listResponse' => $listResponse,
			'response' => $response,
			'cloudAction' => 'zone-transfers',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function zoneTransfersAdd($zoneInfo, $ip) {
		$ip = trim((string)$ip);
		if ($ip === '') {
			$response = array('status' => 'error', 'description' => 'Please enter an IP address or subnet.');
		} else {
			$response = $this->core->Zonetransfers->allowIp($zoneInfo['name'], $ip);
		}
		return $this->zoneTransfers($zoneInfo, $response);
	}

	public function zoneTransfersDelete($zoneInfo, $id) {
		$id = trim((string)$id);
		if ($id === '') {
			$response = array('status' => 'error', 'description' => 'Missing allowed IP ID.');
		} else {
			$response = $this->core->Zonetransfers->deleteAllowedIp($zoneInfo['name'], $id);
		}
		return $this->zoneTransfers($zoneInfo, $response);
	}

	protected function normalizeZoneTransfers($rawTransfers) {
		$items = array();
		if (!is_array($rawTransfers)) {
			return $items;
		}
		if (isset($rawTransfers['status']) && $rawTransfers['status'] == 'error') {
			return $items;
		}

		foreach ($rawTransfers as $key => $value) {
			if (in_array($key, array('status', 'description', 'statusDescription'), true)) {
				continue;
			}

			$id = $key;
			$ip = '';
			if (is_array($value)) {
				if (isset($value['id'])) {
					$id = $value['id'];
				} elseif (isset($value['record_id'])) {
					$id = $value['record_id'];
				}

				if (isset($value['ip'])) {
					$ip = $value['ip'];
				} elseif (isset($value['ip_address'])) {
					$ip = $value['ip_address'];
				} elseif (isset($value['address'])) {
					$ip = $value['address'];
				} elseif (isset($value[0])) {
					$ip = $value[0];
				}
			} else {
				$ip = $value;
			}

			$ip = trim((string)$ip);
			$id = trim((string)$id);
			if ($ip === '') {
				continue;
			}
			$items[] = array(
				'id' => $id,
				'ip' => $ip,
			);
		}
		return $items;
	}

	public function freeSsl($zoneInfo, $response = '') {
		$zone = $zoneInfo['name'];
		$freeSsl = $this->core->Freessl->getInfo($zone);

		return array(
			'pagetitle' => 'SSL - ' . $zone,
			'zone' => $zone,
			'zoneInfo' => $zoneInfo,
			'freeSsl' => $freeSsl,
			'hstsState' => $this->core->Freessl->getHstsState($freeSsl),
			'issuers' => $this->core->Freessl->getIssuers(),
			'response' => $response,
			'cloudAction' => 'free-ssl',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function freeSslActivate($zoneInfo, $issuer) {
		$zone = $zoneInfo['name'];
		$response = $this->core->Freessl->activate($zone, $issuer);
		return $this->freeSsl($zoneInfo, $response);
	}

	public function freeSslDeactivate($zoneInfo) {
		$zone = $zoneInfo['name'];
		$response = $this->core->Freessl->deactivate($zone);
		return $this->freeSsl($zoneInfo, $response);
	}

	public function freeSslChangeIssuer($zoneInfo, $issuer) {
		$zone = $zoneInfo['name'];
		$response = $this->core->Freessl->changeIssuer($zone, $issuer);
		return $this->freeSsl($zoneInfo, $response);
	}

	public function freeSslSetHsts($zoneInfo, $enabled) {
		$zone = $zoneInfo['name'];
		$response = $this->core->Freessl->setHsts($zone, $enabled ? 1 : 0);
		return $this->freeSsl($zoneInfo, $response);
	}

	public function copyZone($zoneInfo, $response = '', $form = array()) {
		$zone = isset($zoneInfo['name']) ? (string)$zoneInfo['name'] : '';
		return array(
			'pagetitle' => 'Copy Zone - ' . $zone,
			'zone' => $zone,
			'zoneInfo' => $zoneInfo,
			// Keep Copy Zone feedback page-specific. Using the module-wide
			// `response` variable causes WHMCS/shared DNSPlus renderers to
			// display the same notice a second time.
			'copyResponse' => $response,
			'copyForm' => is_array($form) ? $form : array(),
			'cloudAction' => 'copy-zone',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function copyZoneExecute($zoneInfo, $destinationZone, array $options) {
		$sourceZone = isset($zoneInfo['name']) ? (string)$zoneInfo['name'] : '';
		$form = array(
			'destination' => (string)$destinationZone,
			'create_if_missing' => !empty($options['create_if_missing']) ? '1' : '0',
			'mode' => isset($options['mode']) ? (string)$options['mode'] : 'replace_matching',
			'follow_domain' => !empty($options['follow_domain']) ? '1' : '0',
			'copy_wr' => !empty($options['copy_wr']) ? '1' : '0',
			'copy_forwards' => !empty($options['copy_forwards']) ? '1' : '0',
			'copy_hsts' => !empty($options['copy_hsts']) ? '1' : '0',
		);

		$prepared = $this->core->Copyzone->prepareDestination($destinationZone, !empty($options['create_if_missing']));
		if (!isset($prepared['status']) || $prepared['status'] !== 'success') {
			return $this->copyZone($zoneInfo, $prepared, $form);
		}

		$result = $this->core->Copyzone->copy($sourceZone, $destinationZone, $options);
		if (isset($prepared['created']) && $prepared['created']) {
			if (!isset($result['status']) || $result['status'] !== 'success') {
				$rollback = $this->core->Copyzone->rollbackCreatedDestination($destinationZone);
				$rollbackOk = is_array($rollback) && isset($rollback['status']) && $rollback['status'] === 'success';
				$baseMessage = is_array($result) && isset($result['description']) ? (string)$result['description'] : 'DNSPlus did not confirm the zone copy.';
				$result = array(
					'status' => 'error',
					'description' => $baseMessage . ($rollbackOk
						? ' The newly created destination zone was rolled back.'
						: ' The newly created destination zone could not be rolled back automatically: ' . (is_array($rollback) && isset($rollback['description']) ? (string)$rollback['description'] : 'unknown rollback error.')),
				);
			} else {
				$result['destination_created'] = true;
			}
		}
		return $this->copyZone($zoneInfo, $result, $form);
	}


	public function isParkedZoneForMenu($zoneInfo) {
		if (!is_array($zoneInfo) || !isset($zoneInfo['name']) || trim((string)$zoneInfo['name']) === '') {
			return false;
		}

		$zoneName = strtolower(rtrim(trim((string)$zoneInfo['name']), '.'));
		if ($zoneName === '') {
			return false;
		}

		if (isset($zoneInfo['type']) && $zoneInfo['type'] == 'parked') {
			if (!isset($_SESSION['cloudnsParkedMenuStatus']) || !is_array($_SESSION['cloudnsParkedMenuStatus'])) {
				$_SESSION['cloudnsParkedMenuStatus'] = array();
			}
			$_SESSION['cloudnsParkedMenuStatus'][$zoneName] = array('value' => 1, 'time' => time());
			return true;
		}

		// DomainMonger Patch 1821: Parked Templates visibility previously made
		// dns/get-parked-settings.json block every normal zone-page render.
		// Cache the result per zone for the same 20-minute window used by zone info.
		if (isset($_SESSION['cloudnsParkedMenuStatus'][$zoneName])
			&& is_array($_SESSION['cloudnsParkedMenuStatus'][$zoneName])
			&& isset($_SESSION['cloudnsParkedMenuStatus'][$zoneName]['value'])
			&& isset($_SESSION['cloudnsParkedMenuStatus'][$zoneName]['time'])
			&& ($_SESSION['cloudnsParkedMenuStatus'][$zoneName]['time'] + 1200) >= time()) {
			return (bool)$_SESSION['cloudnsParkedMenuStatus'][$zoneName]['value'];
		}

		$settingsRaw = $this->core->Parkedtemplates->getSettings($zoneInfo['name']);
		$isParked = $this->isParkedSettingsResponseSuccessful($settingsRaw);
		if (!isset($_SESSION['cloudnsParkedMenuStatus']) || !is_array($_SESSION['cloudnsParkedMenuStatus'])) {
			$_SESSION['cloudnsParkedMenuStatus'] = array();
		}
		$_SESSION['cloudnsParkedMenuStatus'][$zoneName] = array(
			'value' => $isParked ? 1 : 0,
			'time' => time(),
		);

		return $isParked;
	}

	public function parkedTemplates($zoneInfo, $response = '') {
		$zone = $zoneInfo['name'];
		$settingsRaw = $this->core->Parkedtemplates->getSettings($zone);
		$isParkedZone = $this->isParkedSettingsResponseSuccessful($settingsRaw);

		$templatesRaw = array();
		if ($isParkedZone) {
			$templatesRaw = $this->core->Parkedtemplates->getTemplates();
		} elseif ($response === '') {
			$response = '';
		}

		return array(
			'pagetitle' => 'Parked Templates',
			'zone' => $zone,
			'zoneInfo' => $zoneInfo,
			'isParkedZone' => $isParkedZone,
			'parkedSettingsMissing' => $this->isParkedSettingsMissingResponse($settingsRaw),
			'parkedTemplates' => $isParkedZone ? $this->normalizeParkedTemplates($templatesRaw) : array(),
			'parkedTemplatesRaw' => $templatesRaw,
			'parkedSettings' => $isParkedZone ? $this->normalizeParkedSettings($settingsRaw) : array(),
			'parkedSettingsRaw' => $settingsRaw,
			'response' => $response,
			'cloudAction' => 'parked-templates',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function parkedTemplatesApply($zoneInfo, $template, $title = '', $description = '', $keywords = '', $contactForm = '0') {
		$settingsRaw = $this->core->Parkedtemplates->getSettings($zoneInfo['name']);
		if (!$this->isParkedSettingsResponseSuccessful($settingsRaw)) {
			return $this->parkedTemplates($zoneInfo, '');
		}

		$template = trim((string)$template);
		if ($template === '' || !preg_match('/^[0-9]+$/', $template)) {
			$response = array('status' => 'error', 'description' => 'Please choose a parked template to apply.');
		} else {
			$response = $this->core->Parkedtemplates->applySettings($zoneInfo['name'], $template, $title, $description, $keywords, $contactForm);
		}

		return $this->parkedTemplates($zoneInfo, $response);
	}

	protected function isParkedSettingsResponseSuccessful($settingsRaw) {
		if (!is_array($settingsRaw)) {
			return false;
		}

		$description = '';
		if (isset($settingsRaw['description'])) {
			$description .= ' ' . (string)$settingsRaw['description'];
		}
		if (isset($settingsRaw['statusDescription'])) {
			$description .= ' ' . (string)$settingsRaw['statusDescription'];
		}

		if (stripos($description, 'not a parked zone') !== false) {
			return false;
		}

		/*
		 * ClouDNS can return:
		 *
		 *   status = Failed
		 *   statusDescription = There are no settings for example.com
		 *
		 * for a valid newly-created parked zone before the first template has been
		 * applied. Treat that as a parked zone with empty current settings.
		 */
		if (stripos($description, 'there are no settings for') !== false) {
			return true;
		}

		if (isset($settingsRaw['status'])) {
			$status = strtolower((string)$settingsRaw['status']);
			if ($status === 'error' || $status === 'failed') {
				return false;
			}
		}

		return true;
	}

	protected function isParkedSettingsMissingResponse($settingsRaw) {
		if (!is_array($settingsRaw)) {
			return false;
		}

		$description = '';
		if (isset($settingsRaw['description'])) {
			$description .= ' ' . (string)$settingsRaw['description'];
		}
		if (isset($settingsRaw['statusDescription'])) {
			$description .= ' ' . (string)$settingsRaw['statusDescription'];
		}

		return (stripos($description, 'there are no settings for') !== false);
	}

	protected function normalizeParkedTemplates($templatesRaw) {
		$items = array();
		if (!is_array($templatesRaw)) {
			return $items;
		}
		if (isset($templatesRaw['status']) && $templatesRaw['status'] == 'error') {
			return $items;
		}

		foreach ($templatesRaw as $key => $value) {
			if (in_array($key, array('status', 'description', 'statusDescription'), true)) {
				continue;
			}

			$item = array(
				'id' => '',
				'name' => '',
				'description' => '',
				'preview' => '',
			);

			if (is_array($value)) {
				$item['id'] = isset($value['id']) ? $value['id'] : $key;
				$item['name'] = isset($value['name']) ? $value['name'] : (isset($value['title']) ? $value['title'] : $key);
				$item['description'] = isset($value['description']) ? $value['description'] : '';
				if (isset($value['preview'])) {
					$item['preview'] = $value['preview'];
				} elseif (isset($value['preview_url'])) {
					$item['preview'] = $value['preview_url'];
				} elseif (isset($value['url'])) {
					$item['preview'] = $value['url'];
				}
			} else {
				$item['id'] = $key;
				$item['name'] = $value;
			}

			$item['id'] = trim((string)$item['id']);
			$item['name'] = trim((string)$item['name']);
			if ($item['name'] === '') {
				$item['name'] = $item['id'];
			}
			if ($item['name'] === '') {
				continue;
			}
			$items[] = $item;
		}

		return $items;
	}

	protected function normalizeParkedSettings($settingsRaw) {
		$settings = array(
			'template' => '',
			'title' => '',
			'description' => '',
			'keywords' => '',
			'contact_form' => '',
		);

		if (!is_array($settingsRaw)) {
			return $settings;
		}
		if (isset($settingsRaw['status']) && ($settingsRaw['status'] == 'error' || $settingsRaw['status'] == 'Failed')) {
			return $settings;
		}

		foreach ($settingsRaw as $key => $value) {
			$keyLower = strtolower(str_replace('-', '_', (string)$key));
			if (is_array($value)) {
				continue;
			}
			if ($keyLower === 'template' || $keyLower === 'template_id') {
				$settings['template'] = (string)$value;
			} elseif ($keyLower === 'title') {
				$settings['title'] = (string)$value;
			} elseif ($keyLower === 'description') {
				$settings['description'] = (string)$value;
			} elseif ($keyLower === 'keywords') {
				$settings['keywords'] = (string)$value;
			} elseif ($keyLower === 'contact_form' || $keyLower === 'contactform') {
				$settings['contact_form'] = (string)$value;
			}
		}

		return $settings;
	}

	public function exportZoneFile($zoneInfo, $format = 'bind', $forceDownload = false) {
		$zone = $zoneInfo['name'];
		$format = $this->core->Zoneexport->normalizeFormat($format);
		$export = $this->core->Zoneexport->export($zone, $format);

		if ($forceDownload && isset($export['content']) && $export['content'] !== '') {
			$extension = $this->core->Zoneexport->getFileExtension($format);
			header('Content-Type: ' . $this->core->Zoneexport->getContentType($format));
			header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', $zone) . '.' . $extension . '"');
			echo $export['content'];
			exit;
		}

		return array(
			'pagetitle' => 'Export Zone File - ' . $zone,
			'zone' => $zone,
			'zoneInfo' => $zoneInfo,
			'exportZoneFile' => isset($export['content']) ? $export['content'] : '',
			'exportResponse' => $export,
			'exportFormat' => $format,
			'exportFormats' => $this->core->Zoneexport->getFormats(),
			'cloudAction' => 'export-zone-file',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}


	public function dnssec($zoneInfo, $waitDSrecords) {
		$zone = $zoneInfo['name'];
		$response = $this->core->dnssec->getDSrecords($zone);
		
		if ($response['status'] == '1') {
			return $this->dnssecSettings($zoneInfo);
		} elseif ($response['status'] == 'error' && $waitDSrecords == 1) {
			return $this->dnssecWaiting($zoneInfo);
		} else {
			return $this->dnssecShow($zoneInfo);
		}
	}

	public function dnssecShow($zoneInfo) {
		$zone = $zoneInfo['name'];
	
		return array(
			'pagetitle' => 'DNSSEC - '.$zone,
			'zone' => $zone,
			'cloudAction' => 'dnssec-show',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}
	
	public function dnssecSettings($zoneInfo) {
		$zone = $zoneInfo['name'];
		$dnssec = $this->core->dnssec->getDSrecords($zone);
		
		return array(
			'pagetitle' => 'DNSSEC - '.$zone,
			'zone' => $zone,
			'dnssec' => $dnssec,
			'cloudAction' => 'dnssec-settings',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}
	
	public function dnssecActivate($zoneInfo) {
		$zone = $zoneInfo['name'];
		$response = $this->core->dnssec->activateDnssec($zone);
		$getDSrecords = $this->core->dnssec->getDSrecords($zone);
		
		if ($response['status'] == 'success' && $getDSrecords['status'] == '1') {
			$waitDSrecords = 1;
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=dnssec-settings&zone={$zoneInfo['name']}&waitDS={$waitDSrecords}");
		}
		
		elseif ($response['status'] == 'success' && $getDSrecords['status'] == 'error') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=dnssec-waiting&zone={$zoneInfo['name']}");
		}

		return array(
			'zone' => $zone,
			'response' => $response,
			'cloudAction' => 'dnssec-activate',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}

	public function dnssecDeactivate($zoneInfo) {
		$zone = $zoneInfo['name'];

		$response = $this->core->dnssec->deactivateDnssec($zone);
		if ($response['status'] == "success") {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=dnssec-show&zone={$zoneInfo['name']}");
		}
		
		return $this->dnssecShow($zoneInfo);
	}
	
	public function dnssecWaiting ($zoneInfo) {
		$zone = $zoneInfo['name'];
		
		$response = $this->core->dnssec->getDSrecords($zone);
		
		if ($response['status'] == '1') {
			$this->core->Helper->redirect("clientarea.php?action=productdetails&id={$this->params['serviceid']}&customAction=dnssec-settings&zone={$zoneInfo['name']}");
		}
		
		return array(
			'pagetitle' => 'DNSSEC - '.$zone,
			'zone' => $zone,
			'response' => $response,
			'cloudAction' => 'dnssec-waiting',
			'version' => $this->params['shortVersion'],
			'theme' => $this->params['theme'],
		);
	}
}

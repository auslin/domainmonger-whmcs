<?php

class Cloudns_Freessl {

	protected $core;
	protected $params;

	public function __construct($params) {
		$this->core = Cloudns_Core::inst($params);
		$this->params = $params;
	}

	public function getInfo($zone) {
		$request = array('domain-name' => $zone);
		return $this->core->Api->call('dns/freessl-get.json', $request);
	}

	public function activate($zone, $issuer) {
		$request = array(
			'domain-name' => $zone,
			'issuer' => $this->normalizeIssuer($issuer),
		);
		return $this->core->Api->call('dns/freessl-activate.json', $request);
	}

	public function deactivate($zone) {
		$request = array('domain-name' => $zone);
		return $this->core->Api->call('dns/freessl-deactivate.json', $request);
	}

	public function changeIssuer($zone, $issuer) {
		$request = array(
			'domain-name' => $zone,
			'issuer' => $this->normalizeIssuer($issuer),
		);
		return $this->core->Api->call('dns/freessl-change-issuer.json', $request);
	}

	public function setHsts($zone, $enabled) {
		$request = array(
			'domain-name' => $zone,
			'hsts' => $enabled ? 1 : 0,
		);
		return $this->core->Api->call('dns/freessl-set-hsts.json', $request);
	}


	public function getHstsState($info) {
		if (!is_array($info)) {
			return 'unknown';
		}
		if (isset($info['status']) && in_array(strtolower((string)$info['status']), array('error', 'failed'), true)) {
			return 'unavailable';
		}
		foreach (array('hsts', 'HSTS', 'hsts_status', 'hsts-status') as $key) {
			if (!array_key_exists($key, $info)) {
				continue;
			}
			$value = strtolower(trim((string)$info[$key]));
			if (in_array($value, array('1', 'true', 'yes', 'on', 'active', 'enabled'), true)) {
				return 'active';
			}
			if (in_array($value, array('0', 'false', 'no', 'off', 'inactive', 'disabled'), true)) {
				return 'inactive';
			}
		}
		return 'unknown';
	}

	public function normalizeIssuer($issuer) {
		$issuer = (string)$issuer;
		if (!in_array($issuer, array('1', '2'), true)) {
			return '1';
		}
		return $issuer;
	}

	public function getIssuers() {
		return array(
			'1' => 'ZeroSSL',
			'2' => "Let's Encrypt",
		);
	}
}

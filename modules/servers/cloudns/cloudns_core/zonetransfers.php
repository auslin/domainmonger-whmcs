<?php

class Cloudns_Zonetransfers {

	protected $core;
	protected $params;

	public function __construct($params) {
		$this->core = Cloudns_Core::inst($params);
		$this->params = $params;
	}

	public function listAllowedIps($zone) {
		$request = array('domain-name' => $zone);
		return $this->core->Api->call('dns/axfr-list.json', $request);
	}

	public function allowIp($zone, $ip) {
		$request = array(
			'domain-name' => $zone,
			'ip' => trim((string)$ip),
		);
		return $this->core->Api->call('dns/axfr-add.json', $request);
	}

	public function deleteAllowedIp($zone, $id) {
		$request = array(
			'domain-name' => $zone,
			'id' => trim((string)$id),
		);
		return $this->core->Api->call('dns/axfr-remove.json', $request);
	}
}

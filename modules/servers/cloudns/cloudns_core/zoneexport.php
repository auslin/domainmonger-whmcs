<?php

class Cloudns_Zoneexport {

	protected $core;
	protected $params;

	public function __construct($params) {
		$this->core = Cloudns_Core::inst($params);
		$this->params = $params;
	}

	public function export($zone, $format) {
		$format = $this->normalizeFormat($format);
		if ($format === 'csv') {
			return $this->exportCsv($zone);
		}
		if ($format === 'json') {
			return $this->exportJson($zone);
		}
		return $this->exportBind($zone);
	}

	public function normalizeFormat($format) {
		$format = strtolower(trim((string)$format));
		if (!in_array($format, array('bind', 'csv', 'json'), true)) {
			return 'bind';
		}
		return $format;
	}

	public function getFormats() {
		return array(
			'bind' => 'BIND',
			'csv' => 'CSV',
			'json' => 'JSON',
		);
	}

	public function getFileExtension($format) {
		$format = $this->normalizeFormat($format);
		if ($format === 'csv') {
			return 'csv';
		}
		if ($format === 'json') {
			return 'json';
		}
		return 'zone';
	}

	public function getContentType($format) {
		$format = $this->normalizeFormat($format);
		if ($format === 'csv') {
			return 'text/csv; charset=utf-8';
		}
		if ($format === 'json') {
			return 'application/json; charset=utf-8';
		}
		return 'text/plain; charset=utf-8';
	}

	public function exportBind($zone) {
		$request = array('domain-name' => $zone);
		$response = $this->callRaw('dns/records-export.json', $request);

		$decoded = json_decode($response, true);
		if (is_array($decoded)) {
			$status = isset($decoded['status']) ? $decoded['status'] : '';
			if ($status == 'Failed' || strtolower((string)$status) == 'error') {
				return array(
					'status' => 'error',
					'description' => isset($decoded['statusDescription']) ? $decoded['statusDescription'] : (isset($decoded['description']) ? $decoded['description'] : 'Export failed.'),
					'format' => 'bind',
					'content' => '',
					'raw' => $decoded,
				);
			}

			$content = $this->extractBindContentFromResponse($decoded);
			if ($content !== '') {
				return array(
					'status' => 'success',
					'description' => isset($decoded['statusDescription']) ? $decoded['statusDescription'] : (isset($decoded['description']) ? $decoded['description'] : ''),
					'format' => 'bind',
					'content' => $content,
					'raw' => $decoded,
				);
			}

			$generated = $this->generateBindFromRecords($zone);
			if (isset($generated['status']) && $generated['status'] == 'error') {
				return $generated;
			}

			return array(
				'status' => 'success',
				'description' => isset($decoded['statusDescription']) ? $decoded['statusDescription'] : (isset($decoded['description']) ? $decoded['description'] : 'Export completed.'),
				'format' => 'bind',
				'content' => $generated['content'],
				'raw' => $decoded,
			);
		}

		$response = (string)$response;
		if (trim($response) === '') {
			$generated = $this->generateBindFromRecords($zone);
			if (isset($generated['status']) && $generated['status'] == 'error') {
				return $generated;
			}
			return array(
				'status' => 'success',
				'description' => 'Export completed.',
				'format' => 'bind',
				'content' => $generated['content'],
				'raw' => $response,
			);
		}

		return array(
			'status' => 'success',
			'description' => '',
			'format' => 'bind',
			'content' => $response,
			'raw' => $response,
		);
	}

	public function exportCsv($zone) {
		$records = $this->core->Records->getRecords($zone);
		if (isset($records['status']) && $records['status'] == 'error') {
			return array(
				'status' => 'error',
				'description' => isset($records['description']) ? $records['description'] : 'Could not retrieve records for CSV export.',
				'format' => 'csv',
				'content' => '',
				'raw' => $records,
			);
		}

		$rows = $this->normalizeRecords($records);
		$handle = fopen('php://temp', 'r+');
		fputcsv($handle, array('id', 'type', 'host', 'record', 'ttl', 'priority', 'weight', 'port'));
		foreach ($rows as $row) {
			fputcsv($handle, array(
				$row['id'],
				$row['type'],
				$row['host'],
				$row['record'],
				$row['ttl'],
				$row['priority'],
				$row['weight'],
				$row['port'],
			));
		}
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);

		return array(
			'status' => 'success',
			'description' => '',
			'format' => 'csv',
			'content' => $content,
			'raw' => $records,
		);
	}

	public function exportJson($zone) {
		$records = $this->core->Records->getRecords($zone);
		if (isset($records['status']) && $records['status'] == 'error') {
			return array(
				'status' => 'error',
				'description' => isset($records['description']) ? $records['description'] : 'Could not retrieve records for JSON export.',
				'format' => 'json',
				'content' => '',
				'raw' => $records,
			);
		}

		return array(
			'status' => 'success',
			'description' => '',
			'format' => 'json',
			'content' => json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			'raw' => $records,
		);
	}


	protected function extractBindContentFromResponse($decoded) {
		$preferredKeys = array('content', 'zone_file', 'zoneFile', 'bind', 'data', 'result', 'response');
		foreach ($preferredKeys as $key) {
			if (isset($decoded[$key]) && is_string($decoded[$key]) && trim($decoded[$key]) !== '') {
				return (string)$decoded[$key];
			}
		}

		foreach ($decoded as $value) {
			if (is_array($value)) {
				$content = $this->extractBindContentFromResponse($value);
				if ($content !== '') {
					return $content;
				}
			}
		}

		return '';
	}

	protected function generateBindFromRecords($zone) {
		$records = $this->core->Records->getRecords($zone);
		if (isset($records['status']) && $records['status'] == 'error') {
			return array(
				'status' => 'error',
				'description' => isset($records['description']) ? $records['description'] : 'Could not retrieve records for BIND export.',
				'format' => 'bind',
				'content' => '',
				'raw' => $records,
			);
		}

		$rows = $this->normalizeRecords($records);
		$zone = rtrim((string)$zone, '.');
		$lines = array();
		$lines[] = '; BIND zone export generated from ClouDNS records';
		$lines[] = '$ORIGIN ' . $zone . '.';
		$lines[] = '$TTL 3600';

		foreach ($rows as $row) {
			$type = strtoupper(trim((string)$row['type']));
			if ($type === '') {
				continue;
			}

			$host = $this->formatBindHost($row['host'], $zone);
			$ttl = $this->formatBindTtl($row['ttl']);
			$value = $this->formatBindValue($type, $row);
			if ($value === '') {
				continue;
			}

			$lines[] = trim(sprintf('%-24s %-8s IN %-6s %s', $host, $ttl, $type, $value));
		}

		return array(
			'status' => 'success',
			'description' => '',
			'format' => 'bind',
			'content' => implode("\n", $lines) . "\n",
			'raw' => $records,
		);
	}

	protected function formatBindHost($host, $zone) {
		$host = trim((string)$host);
		if ($host === '' || $host === '@' || $host === $zone || $host === $zone . '.') {
			return '@';
		}
		if (substr($host, -1) === '.') {
			return $host;
		}
		$suffix = '.' . $zone;
		if (substr($host, -strlen($suffix)) === $suffix) {
			return substr($host, 0, -strlen($suffix));
		}
		return $host;
	}

	protected function formatBindTtl($ttl) {
		$ttl = trim((string)$ttl);
		if ($ttl === '' || !preg_match('/^[0-9]+$/', $ttl)) {
			return '3600';
		}
		return $ttl;
	}

	protected function formatBindValue($type, $row) {
		$value = trim((string)$row['record']);
		if ($value === '') {
			return '';
		}

		if ($type === 'MX') {
			$priority = trim((string)$row['priority']);
			return ($priority !== '' ? $priority . ' ' : '') . $this->formatBindTarget($value);
		}

		if ($type === 'SRV') {
			$priority = trim((string)$row['priority']);
			$weight = trim((string)$row['weight']);
			$port = trim((string)$row['port']);
			return trim(($priority !== '' ? $priority : '0') . ' ' . ($weight !== '' ? $weight : '0') . ' ' . ($port !== '' ? $port : '0') . ' ' . $this->formatBindTarget($value));
		}

		if ($type === 'TXT' || $type === 'SPF') {
			return '"' . addcslashes($value, "\\\"") . '"';
		}

		if (in_array($type, array('CNAME', 'NS', 'PTR'), true)) {
			return $this->formatBindTarget($value);
		}

		return $value;
	}

	protected function formatBindTarget($value) {
		$value = trim((string)$value);
		if ($value === '' || $value === '@' || preg_match('/^[0-9a-f:.]+$/i', $value)) {
			return $value;
		}
		return (substr($value, -1) === '.') ? $value : $value . '.';
	}

	protected function normalizeRecords($records) {
		$rows = array();
		if (!is_array($records)) {
			return $rows;
		}

		foreach ($records as $key => $record) {
			if (in_array($key, array('status', 'description', 'statusDescription'), true)) {
				continue;
			}
			if (!is_array($record)) {
				continue;
			}

			$rows[] = array(
				'id' => isset($record['id']) ? $record['id'] : $key,
				'type' => isset($record['type']) ? $record['type'] : '',
				'host' => isset($record['host']) ? $record['host'] : '',
				'record' => isset($record['record']) ? $record['record'] : '',
				'ttl' => isset($record['ttl']) ? $record['ttl'] : (isset($record['ttl_seconds']) ? $record['ttl_seconds'] : ''),
				'priority' => isset($record['priority']) ? $record['priority'] : '',
				'weight' => isset($record['weight']) ? $record['weight'] : '',
				'port' => isset($record['port']) ? $record['port'] : '',
			);
		}

		return $rows;
	}

	protected function callRaw($url, $data) {
		$auth_id = $this->core->Configuration->getApiUser();
		$auth_password = $this->core->Configuration->getApiPassword();
		$authData = array('auth-id' => $auth_id , 'auth-password' => $auth_password);
		$request = http_build_query(array_merge($authData, $data));
		$action = $url;
		$url = 'https://api.cloudns.net/' . $url;

		$init = curl_init();
		curl_setopt($init, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($init, CURLOPT_URL, $url);
		curl_setopt($init, CURLOPT_POST, true);
		curl_setopt($init, CURLOPT_POSTFIELDS, $request);
		curl_setopt($init, CURLOPT_USERAGENT, 'cloudns-whmcs/v'.$this->params['version'].'-'.$this->params['moduleVersion']);

		$content = curl_exec($init);
		$curlError = curl_error($init);
		curl_close($init);

		logModuleCall('cloudns', $action, $request, $content);

		if ($content === false || $curlError !== '') {
			return json_encode(array(
				'status' => 'Failed',
				'statusDescription' => 'Export request failed: ' . $curlError,
			));
		}

		return $content;
	}
}

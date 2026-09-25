<?php

class Cloudns_Copyzone {
    protected $core;
    protected $params;

    public function __construct($params) {
        $this->core = Cloudns_Core::inst($params);
        $this->params = $params;
    }

    public function normalizeZone($zone) {
        $zone = strtolower(rtrim(trim((string)$zone), '.'));
        if ($zone === '' || strlen($zone) > 255 || strpos($zone, '.') === false || preg_match('/[\s\/?#\\\\]/', $zone) || strpos($zone, '://') !== false) {
            return '';
        }
        return $zone;
    }


    public function prepareDestination($destinationZone, $createIfMissing = false) {
        $destinationZone = $this->normalizeZone($destinationZone);
        if ($destinationZone === '') {
            return $this->error('Enter a valid destination DNS zone name.');
        }

        $mapping = $this->core->Controller->getZoneByName($destinationZone);
        if (!empty($mapping)) {
            $serviceId = isset($mapping[0]['serviceid']) ? (int)$mapping[0]['serviceid'] : 0;
            if ($serviceId !== (int)$this->params['serviceid']) {
                return $this->error($destinationZone . ' is already attached to WHMCS service #' . $serviceId . '.');
            }
        }

        // Use the raw API response here instead of Zones->getZoneInfo(). That helper
        // intentionally collapses every provider error into an empty array, which is
        // unsafe for provisioning because an authentication/network/provider error
        // must never be interpreted as "zone missing".
        $zoneInfo = $this->core->Api->call('dns/get-zone-info.json', array('domain-name' => $destinationZone));
        if ($this->success($zoneInfo)) {
            if (empty($mapping)) {
                return $this->error($destinationZone . ' already exists in DNSPlus but is not attached to this WHMCS service. It was not claimed automatically.');
            }
            if (isset($zoneInfo['type']) && (string)$zoneInfo['type'] !== 'master') {
                return $this->error($destinationZone . ' exists, but it is not a master DNS zone.');
            }
            return array('status' => 'success', 'zone' => $destinationZone, 'created' => false);
        }

        $lookupMessage = $this->message($zoneInfo);
        if (stripos($lookupMessage, 'Missing domain-name') === false) {
            return $this->error('DNSPlus could not verify whether ' . $destinationZone . ' already exists: ' . $lookupMessage);
        }

        if (!$createIfMissing) {
            return $this->error($destinationZone . ' does not exist in DNSPlus. Enable Create destination zone to create and attach it.');
        }

        // A zone can be deleted directly in DNSPlus while its local WHMCS mapping
        // remains behind. If that stale row belongs to this exact service, remove
        // only the stale local mapping before recreating the remote zone. Never
        // remove or claim a mapping owned by another service.
        if (!empty($mapping)) {
            $removed = $this->core->Database->delete('mod_cloudns_zones', array(
                'serviceid' => (int)$this->params['serviceid'],
                'name' => $destinationZone,
            ));
            if (empty($removed)) {
                return $this->error('DNSPlus found a stale local zone mapping that could not be cleared safely.');
            }
            $mapping = array();
        }

        $servers = $this->core->Servers->getAvailableServers();
        $zoneExists = array(
            'status' => 'error',
            'description' => 'There is no such DNS zone with the DNS servers',
            'zoneInfo' => array('name' => ''),
        );
        // For a copy operation, intentionally create a clean nameserver-only master zone.
        // Do not apply the product template first and then copy another zone over it.
        $create = $this->core->Controller->addNewZone(
            $destinationZone,
            'masterZoneType',
            1,
            is_array($servers) ? $servers : array(),
            '',
            $zoneExists
        );
        if (!$this->success($create)) {
            return $this->error('The destination zone could not be created: ' . $this->message($create));
        }

        return array('status' => 'success', 'zone' => $destinationZone, 'created' => true);
    }

    public function rollbackCreatedDestination($destinationZone) {
        $destinationZone = $this->normalizeZone($destinationZone);
        if ($destinationZone === '') {
            return $this->error('The newly created destination zone could not be identified for rollback.');
        }

        $mapping = $this->core->Controller->getZoneByName($destinationZone);
        if (empty($mapping)) {
            return array('status' => 'success', 'description' => 'The destination zone is no longer attached to this service.');
        }

        $serviceId = isset($mapping[0]['serviceid']) ? (int)$mapping[0]['serviceid'] : 0;
        if ($serviceId !== (int)$this->params['serviceid']) {
            return $this->error('Rollback was refused because the destination zone is no longer attached to this service.');
        }

        return $this->core->Controller->deleteZone($destinationZone);
    }

    public function copy($sourceZone, $destinationZone, array $options = array()) {
        $sourceZone = $this->normalizeZone($sourceZone);
        $destinationZone = $this->normalizeZone($destinationZone);
        $mode = isset($options['mode']) ? (string)$options['mode'] : 'replace_matching';
        $followDomain = !empty($options['follow_domain']);
        $copyForwards = !array_key_exists('copy_forwards', $options) || !empty($options['copy_forwards']);
        $copyWr = !array_key_exists('copy_wr', $options) || !empty($options['copy_wr']);
        $copyHsts = !empty($options['copy_hsts']);

        if ($sourceZone === '' || $destinationZone === '') {
            return $this->error('Enter valid source and destination DNS zone names.');
        }
        if ($sourceZone === $destinationZone) {
            return $this->error('The source and destination DNS zones must be different.');
        }
        if (!in_array($mode, array('replace_matching', 'add_alongside', 'replace_entire'), true)) {
            return $this->error('Select a valid copy mode.');
        }

        $sourceInfo = $this->core->Zones->getZoneInfo($sourceZone);
        if (empty($sourceInfo) || (isset($sourceInfo['status']) && $sourceInfo['status'] === 'error')) {
            return $this->error('The source DNS zone was not found in DNSPlus.');
        }
        if (isset($sourceInfo['type']) && (string)$sourceInfo['type'] !== 'master') {
            return $this->error('Copy Zone is available only for master DNS zones.');
        }

        $destinationInfo = $this->core->Zones->getZoneInfo($destinationZone);
        if (empty($destinationInfo) || (isset($destinationInfo['status']) && $destinationInfo['status'] === 'error')) {
            return $this->error('The destination DNS zone was not found in DNSPlus.');
        }
        if (isset($destinationInfo['type']) && (string)$destinationInfo['type'] !== 'master') {
            return $this->error('The destination must be a master DNS zone.');
        }

        $sourceRecords = $this->recordsList($this->core->Records->getRecords($sourceZone));
        $destinationRecords = $this->recordsList($this->core->Records->getRecords($destinationZone));
        $copySourceRecords = array_values(array_filter($sourceRecords, function ($record) use ($copyWr) {
            return $copyWr || strtoupper($this->recordType($record)) !== 'WR';
        }));
        $sourceForwards = $copyForwards ? $this->forwardsList($this->core->Forwards->getForwards($sourceZone)) : array();
        // Replace Entire Zone means the destination zone contents are replaced,
        // including Mail Forwards. Load destination forwards even when Copy Mail
        // Forwards is unchecked so they can still be cleared. The checkbox controls
        // only whether source forwards are copied back into the emptied destination.
        $destinationForwards = ($copyForwards || $mode === 'replace_entire')
            ? $this->forwardsList($this->core->Forwards->getForwards($destinationZone))
            : array();
        $destinationWrCounts = $this->wrSignatureCounts($destinationRecords);

        if ($mode === 'add_alongside') {
            $conflict = $this->findAddAlongsideCnameConflict($copySourceRecords, $destinationRecords);
            if ($conflict !== '') {
                return $this->error($conflict . ' Use Replace Matching Records for this destination.');
            }
        }

        $recordLimit = isset($this->params['recordsLimit']) ? (int)$this->params['recordsLimit'] : -1;
        if ($recordLimit >= 0) {
            $expectedRecords = count($copySourceRecords);
            if ($mode === 'add_alongside') {
                $expectedRecords += count($destinationRecords);
            } elseif ($mode === 'replace_matching') {
                $matchingIds = array();
                $sourceKeys = array();
                $sourceHosts = array();
                $sourceCnameHosts = array();
                foreach ($copySourceRecords as $record) {
                    $type = strtoupper($this->recordType($record));
                    $host = strtolower($this->recordHost($record));
                    if ($type === '') {
                        continue;
                    }
                    $sourceKeys[$type . '|' . $host] = true;
                    $sourceHosts[$host] = true;
                    if ($type === 'CNAME') {
                        $sourceCnameHosts[$host] = true;
                    }
                }
                foreach ($destinationRecords as $record) {
                    $type = strtoupper($this->recordType($record));
                    $host = strtolower($this->recordHost($record));
                    $id = $this->recordId($record);
                    if ($id !== '' && (
                        isset($sourceKeys[$type . '|' . $host])
                        || isset($sourceCnameHosts[$host])
                        || ($type === 'CNAME' && isset($sourceHosts[$host]))
                    )) {
                        $matchingIds[$id] = true;
                    }
                }
                $expectedRecords += max(0, count($destinationRecords) - count($matchingIds));
            }
            if ($expectedRecords > $recordLimit) {
                return $this->error('This copy would exceed this DNSPlus product limit of ' . $recordLimit . ' DNS records per zone.');
            }
        }

        $forwardLimit = isset($this->params['forwardsLimit']) ? (int)$this->params['forwardsLimit'] : -1;
        if ($copyForwards && $forwardLimit >= 0) {
            $expectedForwards = count($sourceForwards);
            if ($mode === 'add_alongside') {
                $expectedForwards += count($destinationForwards);
            } elseif ($mode === 'replace_matching') {
                $sourceBoxes = array();
                foreach ($sourceForwards as $forward) {
                    $parts = $this->forwardMailboxParts($forward, $sourceZone);
                    $key = $this->forwardOwnerKey($parts['host'], $parts['box']);
                    if ($key !== '|') {
                        $sourceBoxes[$key] = true;
                    }
                }
                $kept = 0;
                foreach ($destinationForwards as $forward) {
                    $parts = $this->forwardMailboxParts($forward, $destinationZone);
                    $key = $this->forwardOwnerKey($parts['host'], $parts['box']);
                    if (!isset($sourceBoxes[$key])) {
                        $kept++;
                    }
                }
                $expectedForwards += $kept;
            }
            if ($expectedForwards > $forwardLimit) {
                return $this->error('This copy would exceed this DNSPlus product limit of ' . $forwardLimit . ' Mail Forwards per zone.');
            }
        }

        $summary = array(
            'status' => 'success',
            'description' => '',
            'source' => $sourceZone,
            'destination' => $destinationZone,
            'mode' => $mode,
            'records_deleted' => 0,
            'wr_added' => 0,
            'forwards_deleted' => 0,
            'forwards_added' => 0,
            'hsts' => 'not-requested',
        );

        $forwardDeleteIds = array();
        if ($mode === 'replace_entire') {
            // DNS records are cleared atomically by copy-records below. Replace Entire
            // Zone also clears all destination Mail Forwards regardless of whether source
            // forwards are being copied. Queue deletion until after the DNS copy succeeds
            // so a provider copy failure does not unnecessarily remove forwarding data.
            foreach ($destinationForwards as $forward) {
                $id = $this->forwardId($forward);
                if ($id !== '') {
                    $forwardDeleteIds[$id] = $id;
                }
            }
        } elseif ($mode === 'replace_matching') {
            $sourceKeys = array();
            $sourceHosts = array();
            $sourceCnameHosts = array();
            foreach ($copySourceRecords as $record) {
                $type = strtoupper($this->recordType($record));
                $host = strtolower($this->recordHost($record));
                if ($type === '') {
                    continue;
                }
                $sourceKeys[$type . '|' . $host] = true;
                $sourceHosts[$host] = true;
                if ($type === 'CNAME') {
                    $sourceCnameHosts[$host] = true;
                }
            }
            foreach ($destinationRecords as $record) {
                $type = strtoupper($this->recordType($record));
                $host = strtolower($this->recordHost($record));
                $id = $this->recordId($record);
                if ($type === '' || $id === '') {
                    continue;
                }
                $conflict = isset($sourceKeys[$type . '|' . $host]);
                // CNAME cannot coexist with other record types at the same owner name.
                if (isset($sourceCnameHosts[$host]) || ($type === 'CNAME' && isset($sourceHosts[$host]))) {
                    $conflict = true;
                }
                if (!$conflict) {
                    continue;
                }
                $delete = $this->core->Records->delete($destinationZone, $id);
                if (!$this->success($delete)) {
                    return $this->error('Could not remove a matching destination DNS record: ' . $this->message($delete));
                }
                $summary['records_deleted']++;
            }

            if ($copyForwards) {
                $boxes = array();
                foreach ($sourceForwards as $forward) {
                    $parts = $this->forwardMailboxParts($forward, $sourceZone);
                    $key = $this->forwardOwnerKey($parts['host'], $parts['box']);
                    if ($key !== '|') {
                        $boxes[$key] = true;
                    }
                }
                foreach ($destinationForwards as $forward) {
                    $parts = $this->forwardMailboxParts($forward, $destinationZone);
                    $key = $this->forwardOwnerKey($parts['host'], $parts['box']);
                    $id = $this->forwardId($forward);
                    if ($id !== '' && isset($boxes[$key])) {
                        $forwardDeleteIds[$id] = $id;
                    }
                }
            }
        }

        $request = array(
            'domain-name' => $destinationZone,
            'from-domain' => $sourceZone,
            'delete-current-records' => $mode === 'replace_entire' ? '1' : '0',
            'follow-domain' => $followDomain ? '1' : '0',
        );
        $copy = $this->core->Api->call('dns/copy-records.json', $request);
        if (!$this->success($copy) && !$this->isWrOnlyCopyWarning($copy)) {
            return $this->error('DNSPlus could not copy the DNS records: ' . $this->message($copy));
        }

        if (!empty($forwardDeleteIds)) {
            foreach ($forwardDeleteIds as $forwardId) {
                $delete = $this->core->Forwards->delete($destinationZone, $forwardId);
                if (!$this->success($delete)) {
                    return $this->error('DNS records copied, but destination Mail Forwards could not be prepared: ' . $this->message($delete));
                }
                $summary['forwards_deleted']++;
            }
        }

        // DNSPlus provider-side copy can report WR records as skipped. Treat an
        // WR-only warning as a partial success, then handle WR records explicitly here.
        // If WR copying is enabled, verify and add any missing redirects. If it is
        // disabled, remove only WR records newly introduced by the provider copy.
        $afterRecords = $this->recordsList($this->core->Records->getRecords($destinationZone));
        if ($copyWr) {
            $wrCounts = $this->wrSignatureCounts($afterRecords);
            foreach ($sourceRecords as $record) {
                if (strtoupper($this->recordType($record)) !== 'WR') {
                    continue;
                }
                $settings = $this->wrSettings($record, $sourceZone, $destinationZone, $followDomain);
                $signature = $this->wrSettingsSignature($settings);
                if (isset($wrCounts[$signature]) && $wrCounts[$signature] > 0) {
                    $wrCounts[$signature]--;
                    continue;
                }
                $add = $this->core->Records->recordAdd($destinationZone, 'WR', $settings, 1);
                if (!$this->success($add)) {
                    return $this->error('Standard records copied, but a Web Redirect could not be copied: ' . $this->message($add));
                }
                $summary['wr_added']++;
            }
        } else {
            $allowedCounts = $mode === 'replace_entire' ? array() : $destinationWrCounts;
            $seenCounts = array();
            foreach ($afterRecords as $record) {
                if (strtoupper($this->recordType($record)) !== 'WR') {
                    continue;
                }
                $signature = $this->wrSignature($record);
                $seenCounts[$signature] = isset($seenCounts[$signature]) ? $seenCounts[$signature] + 1 : 1;
                $allowed = isset($allowedCounts[$signature]) ? $allowedCounts[$signature] : 0;
                if ($seenCounts[$signature] <= $allowed) {
                    continue;
                }
                $id = $this->recordId($record);
                if ($id === '') {
                    return $this->error('DNS records copied, but DNSPlus could not identify an unexpected Web Redirect to remove.');
                }
                $delete = $this->core->Records->delete($destinationZone, $id);
                if (!$this->success($delete)) {
                    return $this->error('DNS records copied, but an unexpected Web Redirect could not be removed: ' . $this->message($delete));
                }
            }
        }

        if ($copyForwards) {
            $afterForwards = $this->forwardsList($this->core->Forwards->getForwards($destinationZone));
            $forwardSignatures = array();
            foreach ($afterForwards as $forward) {
                $parts = $this->forwardMailboxParts($forward, $destinationZone);
                $signature = $this->forwardSignature($parts['host'], $parts['box'], $this->forwardDestination($forward));
                $forwardSignatures[$signature] = true;
            }
            foreach ($sourceForwards as $forward) {
                $parts = $this->forwardMailboxParts($forward, $sourceZone);
                $host = $parts['host'];
                $box = $parts['box'];
                $destination = $this->forwardDestination($forward);
                if ($box === '' || $destination === '') {
                    continue;
                }
                if ($followDomain) {
                    $destination = $this->followEmailDomain($destination, $sourceZone, $destinationZone);
                }
                $signature = $this->forwardSignature($host, $box, $destination);
                if (isset($forwardSignatures[$signature])) {
                    continue;
                }
                $add = $this->core->Api->call('dns/add-mail-forward.json', array(
                    'domain-name' => $destinationZone,
                    'box' => $box,
                    'host' => $host,
                    'destination' => $destination,
                ));
                if (!$this->success($add)) {
                    return $this->error('DNS records copied, but a Mail Forward could not be copied: ' . $this->message($add));
                }
                $forwardSignatures[$signature] = true;
                $summary['forwards_added']++;
            }
        }

        if ($copyHsts) {
            $sourceSsl = $this->core->Freessl->getInfo($sourceZone);
            $destinationSsl = $this->core->Freessl->getInfo($destinationZone);
            $sourceHsts = $this->extractHsts($sourceSsl);
            if ($sourceHsts === null) {
                $summary['hsts'] = 'source-unavailable';
            } elseif (!$this->success($destinationSsl)) {
                $summary['hsts'] = 'destination-freessl-inactive';
            } else {
                $hstsResponse = $this->core->Freessl->setHsts($destinationZone, $sourceHsts ? 1 : 0);
                if (!$this->success($hstsResponse)) {
                    return $this->error('Zone data copied, but the HSTS setting could not be copied: ' . $this->message($hstsResponse));
                }
                $summary['hsts'] = $sourceHsts ? 'active' : 'inactive';
            }
        }

        $summary['description'] = 'Zone copy completed.';
        return $summary;
    }

    protected function recordsList($records) {
        if (!is_array($records) || isset($records['status'])) {
            return array();
        }
        $out = array();
        foreach ($records as $key => $record) {
            if (!is_array($record)) {
                continue;
            }
            if (!isset($record['id']) && !isset($record['record-id']) && (is_int($key) || ctype_digit((string)$key))) {
                $record['id'] = (string)$key;
            }
            $out[] = $record;
        }
        return $out;
    }

    protected function forwardsList($forwards) {
        if (!is_array($forwards) || isset($forwards['status'])) {
            return array();
        }
        $out = array();
        foreach ($forwards as $key => $forward) {
            if (!is_array($forward)) {
                continue;
            }
            if (!isset($forward['id']) && !isset($forward['mail-forward-id']) && (is_int($key) || ctype_digit((string)$key))) {
                $forward['id'] = (string)$key;
            }
            $out[] = $forward;
        }
        return $out;
    }

    protected function recordId(array $record) {
        foreach (array('id', 'record-id', 'record_id') as $key) {
            if (isset($record[$key]) && trim((string)$record[$key]) !== '') {
                return trim((string)$record[$key]);
            }
        }
        return '';
    }

    protected function recordType(array $record) {
        return isset($record['type']) ? (string)$record['type'] : (isset($record['record-type']) ? (string)$record['record-type'] : '');
    }

    protected function recordHost(array $record) {
        return isset($record['host']) ? rtrim(trim((string)$record['host']), '.') : '';
    }

    protected function findAddAlongsideCnameConflict(array $sourceRecords, array $destinationRecords) {
        $sourceHosts = array();
        $sourceCnameHosts = array();
        foreach ($sourceRecords as $record) {
            $host = strtolower($this->recordHost($record));
            $type = strtoupper($this->recordType($record));
            if ($type === '') {
                continue;
            }
            $sourceHosts[$host] = true;
            if ($type === 'CNAME') {
                $sourceCnameHosts[$host] = true;
            }
        }
        foreach ($destinationRecords as $record) {
            $host = strtolower($this->recordHost($record));
            $type = strtoupper($this->recordType($record));
            if ($type === '') {
                continue;
            }
            if (isset($sourceCnameHosts[$host]) || ($type === 'CNAME' && isset($sourceHosts[$host]))) {
                $label = $host === '' ? '@' : $host;
                return 'Add Alongside cannot copy records at ' . $label . ' because a CNAME would conflict with existing DNS data.';
            }
        }
        return '';
    }

    protected function wrSignatureCounts(array $records) {
        $counts = array();
        foreach ($records as $record) {
            if (strtoupper($this->recordType($record)) !== 'WR') {
                continue;
            }
            $signature = $this->wrSignature($record);
            $counts[$signature] = isset($counts[$signature]) ? $counts[$signature] + 1 : 1;
        }
        return $counts;
    }

    protected function forwardId(array $forward) {
        foreach (array('id', 'mail-forward-id', 'mail_forward_id') as $key) {
            if (isset($forward[$key]) && trim((string)$forward[$key]) !== '') {
                return trim((string)$forward[$key]);
            }
        }
        return '';
    }

    protected function forwardHost(array $forward, $zone = '') {
        $parts = $this->forwardMailboxParts($forward, $zone);
        return $parts['host'];
    }

    protected function forwardBox(array $forward, $zone = '') {
        $parts = $this->forwardMailboxParts($forward, $zone);
        return $parts['box'];
    }

    protected function forwardMailboxParts(array $forward, $zone = '') {
        $zone = strtolower(rtrim(trim((string)$zone), '.'));
        $host = '';
        foreach (array('host', 'subdomain') as $key) {
            if (isset($forward[$key])) {
                $host = strtolower(rtrim(trim((string)$forward[$key]), '.'));
                break;
            }
        }

        $box = '';
        $source = '';
        if (isset($forward['box']) && trim((string)$forward['box']) !== '') {
            $source = trim((string)$forward['box']);
        } else {
            foreach (array('source', 'mailbox', 'from') as $key) {
                if (isset($forward[$key]) && trim((string)$forward[$key]) !== '') {
                    $source = trim((string)$forward[$key]);
                    break;
                }
            }
        }

        // List Mail Forwards commonly returns the source as a full address such as
        // test@example.com (or @example.com for catch-all), while Add Mail Forward
        // requires the mailbox local part and host as separate parameters. Normalize
        // that representation before comparing or recreating the forward.
        if ($source !== '') {
            $at = strrpos($source, '@');
            if ($at !== false) {
                $local = substr($source, 0, $at);
                $mailDomain = strtolower(rtrim(trim(substr($source, $at + 1)), '.'));
                $box = $local === '' ? '*' : $local;

                if ($host === '' && $zone !== '' && $mailDomain !== '') {
                    if ($mailDomain === $zone) {
                        $host = '';
                    } else {
                        $suffix = '.' . $zone;
                        if (strlen($mailDomain) > strlen($suffix) && substr($mailDomain, -strlen($suffix)) === $suffix) {
                            $host = substr($mailDomain, 0, -strlen($suffix));
                        }
                    }
                }
            } else {
                $box = $source;
            }
        }

        if ($box === '' && isset($forward['box'])) {
            $box = trim((string)$forward['box']);
        }
        if ($box === '') {
            return array('box' => '', 'host' => $host);
        }
        if ($box === '@') {
            $box = '*';
        }

        return array('box' => $box, 'host' => $host);
    }

    protected function forwardDestination(array $forward) {
        foreach (array('destination', 'to', 'forward-to') as $key) {
            if (isset($forward[$key])) {
                return trim((string)$forward[$key]);
            }
        }
        return '';
    }

    protected function wrSettings(array $record, $sourceZone, $destinationZone, $followDomain) {
        $aliases = array(
            'host' => array('host'),
            'record' => array('record'),
            'ttl' => array('ttl'),
            'frame' => array('frame'),
            'frame-title' => array('frame-title', 'frame_title'),
            'frame-description' => array('frame-description', 'frame_description'),
            'frame-keywords' => array('frame-keywords', 'frame_keywords'),
            'save-path' => array('save-path', 'save_path'),
            'mobile-meta' => array('mobile-meta', 'mobile_meta'),
            'redirect-type' => array('redirect-type', 'redirect_type', 'wr_type'),
        );
        $settings = array();
        foreach ($aliases as $target => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $record)) {
                    $settings[$target] = $record[$key];
                    break;
                }
            }
        }
        if (!isset($settings['host'])) {
            $settings['host'] = '';
        }
        if (!isset($settings['record'])) {
            $settings['record'] = '';
        }
        if (!isset($settings['ttl']) || trim((string)$settings['ttl']) === '') {
            $settings['ttl'] = '3600';
        }
        if ($followDomain && isset($settings['record'])) {
            $settings['record'] = str_ireplace($sourceZone, $destinationZone, (string)$settings['record']);
        }
        return $settings;
    }

    protected function wrSignature(array $record) {
        return $this->wrSettingsSignature($this->wrSettings($record, '', '', false));
    }

    protected function wrSettingsSignature(array $settings) {
        $parts = array();
        foreach (array('host', 'record', 'ttl', 'frame', 'frame-title', 'frame-description', 'frame-keywords', 'save-path', 'mobile-meta', 'redirect-type') as $key) {
            $parts[] = strtolower(trim((string)(isset($settings[$key]) ? $settings[$key] : '')));
        }
        return implode('|', $parts);
    }

    protected function followEmailDomain($email, $sourceZone, $destinationZone) {
        $email = trim((string)$email);
        $suffix = '@' . strtolower($sourceZone);
        if (strlen($email) > strlen($suffix) && strtolower(substr($email, -strlen($suffix))) === $suffix) {
            return substr($email, 0, -strlen($suffix)) . '@' . $destinationZone;
        }
        return $email;
    }

    protected function forwardOwnerKey($host, $box) {
        return strtolower(rtrim(trim((string)$host), '.')) . '|' . strtolower(trim((string)$box));
    }

    protected function forwardSignature($host, $box, $destination) {
        return $this->forwardOwnerKey($host, $box) . '|' . strtolower(trim((string)$destination));
    }

    protected function extractHsts($response) {
        if (!is_array($response)) {
            return null;
        }
        foreach (array('hsts', 'HSTS', 'hsts_status', 'hsts-status') as $key) {
            if (!array_key_exists($key, $response)) {
                continue;
            }
            $value = strtolower(trim((string)$response[$key]));
            if (in_array($value, array('1', 'true', 'yes', 'on', 'active', 'enabled'), true)) {
                return true;
            }
            if (in_array($value, array('0', 'false', 'no', 'off', 'inactive', 'disabled'), true)) {
                return false;
            }
        }
        return null;
    }

    protected function isWrOnlyCopyWarning($response) {
        $message = $this->message($response);
        if (stripos($message, 'Following records are not copied:') === false || stripos($message, ' WR ') === false) {
            return false;
        }

        // The provider may concatenate skipped records without separators. Refuse
        // to downgrade the failure if the warning names any supported DNS type
        // other than WR. WR is copied separately below.
        $types = array(
            'A', 'AAAA', 'MX', 'CNAME', 'TXT', 'SPF', 'NS', 'SRV', 'RP', 'SSHFP',
            'ALIAS', 'CAA', 'TLSA', 'CERT', 'DS', 'PTR', 'NAPTR', 'HINFO', 'LOC',
            'DNAME', 'SMIMEA', 'OPENPGPKEY'
        );
        foreach ($types as $type) {
            if (preg_match('/(?:^|\s)' . preg_quote($type, '/') . '(?:\s|$)/i', $message)) {
                return false;
            }
        }
        return true;
    }

    protected function success($response) {
        if (!is_array($response)) {
            return false;
        }
        if (!isset($response['status'])) {
            return true;
        }
        $status = strtolower(trim((string)$response['status']));
        return in_array($status, array('success', '1', 'ok'), true);
    }

    protected function message($response) {
        if (!is_array($response)) {
            return 'DNSPlus returned an invalid response.';
        }
        foreach (array('description', 'statusDescription', 'message') as $key) {
            if (isset($response[$key]) && trim((string)$response[$key]) !== '') {
                return str_ireplace('ClouDNS', 'DNSPlus', trim((string)$response[$key]));
            }
        }
        return 'DNSPlus did not confirm the operation.';
    }

    protected function error($message) {
        return array('status' => 'error', 'description' => (string)$message);
    }
}

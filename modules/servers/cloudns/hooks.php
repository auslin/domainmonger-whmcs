<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!function_exists('domainmonger_cloudns_current_service_is_cloudns')) {
	function domainmonger_cloudns_current_service_is_cloudns($serviceId)
	{
		$serviceId = (int) $serviceId;
		$userId = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : 0;

		if ($serviceId < 1 || $userId < 1) {
			return false;
		}

		static $cache = array();
		$cacheKey = $userId . ':' . $serviceId;

		if (array_key_exists($cacheKey, $cache)) {
			return $cache[$cacheKey];
		}

		try {
			$service = Capsule::table('tblhosting')
				->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
				->where('tblhosting.id', $serviceId)
				->where('tblhosting.userid', $userId)
				->select('tblproducts.servertype')
				->first();

			$cache[$cacheKey] = $service && strtolower((string) $service->servertype) === 'cloudns';
		} catch (\Throwable $e) {
			$cache[$cacheKey] = false;
		}

		return $cache[$cacheKey];
	}
}

add_hook('ClientAreaPrimarySidebar', 1, function ($menu) {
	// Check if the user is logged in
	if (empty($_SESSION['uid'])) {
	    return; // Exit if the user is not logged in
	}

	$serviceId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

	$isCloudnsServicePage = domainmonger_cloudns_current_service_is_cloudns($serviceId);

	// The ClouDNS module sidebar only contained Overview > Zones List, which is redundant.
	// Remove it for ClouDNS service pages so the module content can use the wider layout.
	if ($isCloudnsServicePage && !is_null($menu->getChild('Service Details Overview'))) {
		$menu->removeChild('Service Details Overview');
	}

	// Move the standard WHMCS cancellation link out of the left Actions sidebar for ClouDNS.
	// It is added to the ClouDNS Advanced dropdown in header-settings.tpl instead.
	if ($isCloudnsServicePage && !is_null($menu->getChild('Service Details Actions'))) {
		$actionsMenu = $menu->getChild('Service Details Actions');

		foreach ($actionsMenu->getChildren() as $childName => $child) {
			$label = method_exists($child, 'getLabel') ? (string) $child->getLabel() : '';
			$uri = method_exists($child, 'getUri') ? (string) $child->getUri() : '';

			$normalizedName = preg_replace('/[^a-z]/', '', strtolower((string) $childName));
			$normalizedLabel = preg_replace('/[^a-z]/', '', strtolower(strip_tags(html_entity_decode($label, ENT_QUOTES, 'UTF-8'))));

			if (
				in_array($normalizedName, array('requestcancellation', 'requestcancelation'), true)
				|| in_array($normalizedLabel, array('requestcancellation', 'requestcancelation'), true)
				|| strpos($uri, 'action=cancel') !== false
			) {
				$actionsMenu->removeChild($childName);
			}
		}
	}
	// Domain Details already has a native DNS Management item.
	// Do not add a second ClouDNS DNS Management sidebar link here;
	// the DNS product remains available from Services and the ClouDNS product page.
});

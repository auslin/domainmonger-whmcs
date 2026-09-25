<?php
	
if (!defined("WHMCS"))
die("This file cannot be accessed directly");	

/*
	--------------
	
		This hook was developed by "Brian!" from the WHMCS community. On behalf of everyone at Zomex we would like to thank Brian for not only this script but also his time and dedication given to the WHMCS software. Reference: https://forum.whmcs.com/showthread.php?130402-If-statement-link-to-the-WHMCS-marketplace-connect-pages&p=521186
		
		This hook generates links for the WHMCS marketplace connect pages if enabled. These links are used in the WHMCS template menus.
		
	--------------	
*/

use Illuminate\Database\Capsule\Manager as Capsule;

if(Capsule::schema()->hasTable('tblmarketconnect_services')){	
function zomex_marketconnect_hook($vars)
{
    global $CONFIG;    
    $friendlyurl = $CONFIG['RouteUriPathMode'];
    
    if ($friendlyurl == 'acceptpathinfo') {
        $urlpath = 'index.php/store/';
    }
    elseif ($friendlyurl == 'rewrite') {
        $urlpath = 'store/';
    }
    elseif ($friendlyurl == 'basic') {
        $urlpath = 'index.php?rp=/store/';
    }
    
    $marketconnect = Capsule::table('tblmarketconnect_services')->where('status', '1')->get();
    
    if (count($marketconnect)) {

        foreach ($marketconnect as $service) {
            if ($service->name == 'spamexperts') {
	            $spamexperts = $urlpath.'email-services';
            }
            elseif ($service->name == 'symantec') {
                $symantec = $urlpath.'ssl-certificates';
            }    
            if ($service->name == 'weebly') {
	            $weebly = $urlpath.'website-builder';
            }
            
            if ($service->name == 'sitelock') {
	            $sitelock = $urlpath.'sitelock';
            }  
            
            if ($service->name == 'codeguard') {
	            $codeguard = $urlpath.'codeguard';
            } 
            
            if ($service->name == 'sitelockvpn') {
	            $vpn = $urlpath.'vpn';
            } 
            
            if ($service->name == 'marketgoo') {
	            $marketgoo = $urlpath.'marketgoo';
            }             
                                               
        }
        return array("spamexperts" => $spamexperts, "symantec" => $symantec, "weebly" => $weebly, "sitelock" => $sitelock, "codeguard" => $codeguard, "vpn" => $vpn, "marketgoo" => $marketgoo);
    }
	
}

	add_hook("ClientAreaPage", 1, "zomex_marketconnect_hook");
}	


/*
	--------------
	
		This hook was developed by Peter from myworks.design
		
		This hook generates the products/services links from the cart.
		
	--------------	
*/

add_hook('ClientAreaPage', 1, function($vars) {
	$zomex_pg_list = array();
    $p_group_data = Capsule::table('tblproductgroups')
	->where('hidden', '=', 0)
	->orderBy('order', 'asc')
	->get();
	
	$language = $vars['language'];	
	$pg_ids = array();
	if(is_array($p_group_data) && count($p_group_data)){
		foreach($p_group_data as $pgd){
			$tmp_arr = array();
			$tmp_arr['gid'] = $pgd->id;
			$pg_ids[] = $pgd->id;
			$tmp_arr['name'] = $pgd->name;
			$tmp_arr['headline'] = $pgd->headline;
			//$tmp_arr['tagline'] = $pgd->tagline;
			$zomex_pg_list[] = $tmp_arr;
		}		
		
		if(count($pg_ids)){
			$dt_list = array();
			$dyn_trns_data = Capsule::table('tbldynamic_translations')
			->where('language', '=', $language)
			->whereIn('related_type', array('product_group.{id}.name','product_group.{id}.headline','product_group.{id}.tagline'))
			->whereIn('related_id', $pg_ids)			
			->get();			
			
			if(is_array($dyn_trns_data) && count($dyn_trns_data)){
				foreach($dyn_trns_data as $dtd){
					$dti_key = str_replace('product_group.{id}.','',$dtd->related_type);
					$dt_list[$dtd->related_id][$dti_key] = $dtd->translation;
				}
			}			
			
			if(count($dt_list)){
				$zomex_pg_list_new = array();
				foreach($zomex_pg_list as $pgl){
					$tmp_arr = array();
					$tmp_arr['gid'] = $pgl['gid'];						
					$tmp_arr['name'] = (isset($dt_list[$pgl['gid']]['name']))?$dt_list[$pgl['gid']]['name']:$pgl['name'];
					$tmp_arr['headline'] = (isset($dt_list[$pgl['gid']]['headline']))?$dt_list[$pgl['gid']]['headline']:$pgl['headline'];
					//$tmp_arr['tagline'] = (isset($dt_list[$pgl['gid']]['tagline']))?$dt_list[$pgl['gid']]['tagline']:$pgl['tagline'];
					$zomex_pg_list_new[] = $tmp_arr;
				}
				$zomex_pg_list = $zomex_pg_list_new;
			}
			
		}
	}
	//echo '<pre>'; print_r($zomex_pg_list);echo '</pre>';
	if(!isset($vars['zomex_product_group_list'])){
		$vars['zomex_product_group_list'] = $zomex_pg_list;
	}
	return $vars;
});
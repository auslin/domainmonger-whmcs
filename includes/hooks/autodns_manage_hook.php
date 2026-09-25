<?php
function autodns_manage_hook()
{
      update_query("tbldomains", array("dnsmanagement"=>"on"), array("dnsmanagement"=>""));
}
add_hook('DailyCronJob', 0, 'autodns_manage_hook');
?>
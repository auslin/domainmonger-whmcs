<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function defaultnameservers_hook_updatens($vars) {
global $params;

$domain = $vars['domain'];
$sql = "SELECT registrar from tbldomains WHERE domain='" . $domain . "'";
$rs = full_query( $sql );
$row = mysql_fetch_object( $rs );
$registrar = $row->registrar;
$sql ="SELECT * FROM mod_defaultnameservers WHERE registrar='" . $registrar . "';";
$rs = full_query( $sql );
$row = mysql_fetch_object( $rs );

if( $params['ns1'] == $params['original']['ns1'] ){
    $params['ns1'] = $row->ns1;
    $params['ns2'] = $row->ns2;
    $params['ns3'] = $row->ns3;
    $params['ns4'] = $row->ns4;
    $params['ns5'] = $row->ns5;
    }
}

//add_hook("PreDomainRegister",1,"defaultnameservers_hook_updatens");

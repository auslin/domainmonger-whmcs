<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function defaultnameservers_config() {
    $configarray = array(
    "name" => "Default nameservers",
    "description" => "Addon to setup custom default nameservers",
    "version" => "1.0",
    "author" => "whmcsmod.com",
    "language" => "english",
    "fields" => array(
    ));
    return $configarray;
}

function defaultnameservers_activate() {

   $sql = "CREATE TABLE mod_defaultnameservers( id serial, registrar varchar(255), ns1 varchar(255), ns2 varchar(255), ns3 varchar(255), ns4 varchar(255), ns5 varchar(255));";
   full_query( $sql );
}

function defaultnameservers_deactivate() {

}

function defaultnameservers_upgrade($vars) {

}

function defaultnameservers_output($vars) {

    if( isset( $_GET['savens'] ) ){
        $sql = "DELETE FROM  mod_defaultnameservers WHERE registrar='" . $_GET['registrar'] ."'";
        full_query( $sql );
        $sql = "insert into mod_defaultnameservers(registrar,ns1,ns2,ns3,ns4,ns5) values('" . $_GET['registrar'] . "','" . $_GET['ns1'] . "','" . $_GET['ns2'] . "','" . $_GET['ns3'] . "','" . $_GET['ns4'] . "','" . $_GET['ns5'] . "')";
        full_query( $sql );
    }

    $html = '';
    $modulelink = $vars['modulelink'];
    $sql = "SELECT * FROM tblregistrars GROUP BY registrar";
    $rs = full_query( $sql );
    while( $row = mysql_fetch_object( $rs ) ){
        $sql = "SELECT * from  mod_defaultnameservers WHERE registrar='" . $row->registrar ."'";
        $data =  mysql_fetch_object( full_query( $sql ) );
        $html .= '
                 <form action="addonmodules.php" method="GET"> 
		     <fieldset>
                         <legend><strong>' . $row->registrar . '</strong></legend>
	                 <span>Default nameserver 1: </span><input type="text" size="100" name="ns1" value="' . $data->ns1 . '"></br>
                         <span>Default nameserver 2: </span><input type="text" size="100" name="ns2" value="' . $data->ns2 . '"></br>
                         <span>Default nameserver 3: </span><input type="text" size="100" name="ns3" value="' . $data->ns3 . '"></br>
                         <span>Default nameserver 4: </span><input type="text" size="100" name="ns4" value="' . $data->ns4 . '"></br>
                         <span>Default nameserver 5: </span><input type="text" size="100" name="ns5" value="' . $data->ns5 . '"></br>
                         <input type="hidden" name="module" value="defaultnameservers">
                         <input type="hidden" name="registrar" value="' . $row->registrar . '">
                         <input name="savens" type="submit" class="btn btn-success" value="Save">
                    </fieldset>
		</form>

        ';
    }
    echo $html;


}


<?php
/*
*******************************
* Official Module             *
* Last Updated: 26th Jan 2009 *
* Created by: Matt            *
*******************************

*** EU VAT Number Validation Module ***

This module will check the VAT Number a user supplies in the signup form is
valid and automatically set the user as tax exempt if a valid VAT Number is
supplied and the user lives within the EU but outside the home country

** Installation **

1. Upload to /includes/hooks/
2. Enter the name for your VAT Number Custom Field on line 24
3. Enter your companies home country code on line 25
4. Uncomment lines 27 & 28

*/

$VAT_CUSTOM_FIELD_NAME = "VAT Number";
$VAT_HOME_COUNTRY = "GB";

#add_hook("ClientDetailsValidation",0,"checkVATNumberIsValid","");
#add_hook("ClientAdd",0,"setTaxExemptForVAT","");
#add_hook("ClientEdit",0,"setTaxExemptForVAT","");

# Do Not Edit Below This Line

function checkVATNumberIsValid($vars) {

    global $VAT_CUSTOM_FIELD_NAME,$errormessage;

    $result = select_query("tblcustomfields","id",array("type"=>"client","fieldname"=>$VAT_CUSTOM_FIELD_NAME));
    $data = mysql_fetch_array($result);
    $VAT_CUSTOM_FIELD_ID = $data["id"];

    $vatnumber = $_POST["customfield"][$VAT_CUSTOM_FIELD_ID];

    if ($vatnumber) {

        $vatnumber = strtoupper($vatnumber);
        $vatnumber = ereg_replace("[^A-Z0-9]", "", $vatnumber);

        $vat_prefix = substr($vatnumber, 0, 2);
        $vat_num = substr($vatnumber, 2);

        $url = 'http://isvat.appspot.com/'.rawurlencode($vat_prefix).'/'.rawurlencode($vat_num).'/';
        $result = file_get_contents($url);
        if ($result!="true") {
            $errormessage .= "<li>The supplied VAT Number is not valid";
        }

    }

}

function setTaxExemptForVAT($vars) {

    global $VAT_CUSTOM_FIELD_NAME,$VAT_HOME_COUNTRY;

    $result = select_query("tblcustomfields","id",array("type"=>"client","fieldname"=>$VAT_CUSTOM_FIELD_NAME));
    $data = mysql_fetch_array($result);
    $VAT_CUSTOM_FIELD_ID = $data["id"];

    $result = select_query("tblcustomfieldsvalues","value",array("fieldid"=>$VAT_CUSTOM_FIELD_ID,"relid"=>$vars["userid"]));
    $data = mysql_fetch_array($result);
    $VAT_CUSTOM_FIELD_VALUE = $data["value"];

    $european_union_countries = array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK');

    if ((in_array($vars["country"],$european_union_countries))AND($vars["country"]!=$VAT_HOME_COUNTRY)AND($VAT_CUSTOM_FIELD_VALUE)) {
        update_query("tblclients",array("taxexempt"=>"on"),array("id"=>$vars["userid"]));
    }

}

?>
<?php
require("../dbconnect.php");
require("../includes/functions.php");
$adminuser='';
$adminuser=$_GET['aa'];
$adminuser=base64_decode($adminuser);
//Set Vars
$domain=$_REQUEST['domain'];
$domain=trim($domain);
$command = 'domainwhois';
$values = array( 'domain' => $domain);


// Call API
$results = localAPI($command,$values,$adminuser);
//if ($results['result']!="success") echo "An Error Occurred: ".$results['result'];


if ($results["result"]=="success") {

   
 $ds_status=$results["status"];
 $xml= new SimpleXMLElement('<hostco></hostco>');
$xml->domain['version']= '1.0';
$name= $xml->domain->addChild('name',$domain);
$status= $xml->domain->addChild('status',$ds_status);

echo $xml->asXML(); 
} else {
  # An error occured
  echo "The following error occured: ".$results["message"];
}
?>
<?php
/*
*************************************************************************
*                                                                       *
* WHMCSslider                                    *
* Copyright (c) Hostco Inc All Rights Reserved,                         *
* Release Date: 3rd June 2011                                         *
* Version 1.3                                                           *
*                                                                       *
*************************************************************************
*                                                                       *
* This software is furnished under a license and may be used and copied *
* only  in  accordance  with  the  terms  of such  license and with the *
* inclusion of the above copyright notice.  This software  or any other *
* copies thereof may not be provided or otherwise made available to any *
* other person.  No title to and  ownership of the  software is  hereby *
* transferred.                                                          *
*                                                                       *
* Please see the EULA file for the full End User License Agreement.     *
*                                                                       *
*************************************************************************
*/

@error_reporting(0);
@ini_set("register_globals","off");

$owndir = "username/";
$tempsdir = $owndir."templates/";
define("ROOTDIR",dirname(__FILE__)."/../");
define("CLIENTAREA",true);
define("FORCESSL",true);

require(ROOTDIR."dbconnect.php");
require(ROOTDIR."includes/functions.php");
require(ROOTDIR."includes/clientfunctions.php");
require(ROOTDIR."includes/clientareafunctions.php");
require(ROOTDIR."includes/orderfunctions.php");
require(ROOTDIR."includes/invoicefunctions.php");
require(ROOTDIR."includes/gatewayfunctions.php");
require(ROOTDIR."includes/configoptionsfunctions.php");
require(ROOTDIR."includes/customfieldfunctions.php");
require(ROOTDIR."includes/domainfunctions.php");
require(ROOTDIR."includes/whoisfunctions.php");
require(ROOTDIR."includes/countries.php");




$pagetitle =  "WHMCS username";
$breadcrumbnav = "<a href=\"index.php\">".$_LANG['globalsystemname']."</a> > <a href=\"".$_SERVER['PHP_SELF']."\">".$pagetitle."</a>";

initialiseClientArea($pagetitle,'',$breadcrumbnav);
function column_CADD($column,$table,$column_type)
{
$sql="SELECT * FROM ".$table;
$result=mysql_query($sql);	
$row = mysql_fetch_array($result);

if (array_key_exists($column, $row)) {
  //column exists
}
else{

$sql='ALTER TABLE '.$table.' ADD '.$column.' '.$column_type.'';
$result=mysql_query($sql);
}	
}
column_CADD("username","tblclients","TEXT");

if(isset($_POST['submit']))
{
$uid=$_SESSION['uid'];
$wuser=$_POST['wuser'];
$_SESSION['usernameerror']='';
$sql='SELECT id FROM tblclients WHERE username = "'.$wuser.'"';
$result=mysql_query($sql);

while($row = mysql_fetch_array($result))
{ $rarray[]=$row['id']; }

if(count($rarray)==0 || $rarray==null ){
$_SESSION['usernameerror']='';
$wuser=trim($wuser);
$sql2='UPDATE tblclients SET username = "'.$wuser.'" WHERE id='.$uid;

$result2=mysql_query($sql2);	
}
else{
$error_msg="USERNAME ".$wuser."  is already taken!!";
$_SESSION['usernameerror']=$error_msg;
}
}
if($_SESSION['uid']!=null){

$uid=$_SESSION['uid'];
$sql="SELECT username FROM tblclients WHERE id=".$uid." ORDER by id DESC LIMIT 1";
$result=mysql_query($sql);	
$row = mysql_fetch_array($result);
$_SESSION['wuser']=$row['username']; 
}
echo processSingleTemplate($tempsdir."username.tpl",$templatevars);



?>
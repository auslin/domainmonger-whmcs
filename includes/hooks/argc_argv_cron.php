<?php
function check_register_argc_argv($vars) {
	$responsedata = array("argc"=>$_SERVER["argc"],"argv"=>$_SERVER["argv"]);
	logModuleCall("DailyCron","php argc/argv","na",$responsedata);
}
add_hook("DailyCronJob",1,"check_register_argc_argv");
?>
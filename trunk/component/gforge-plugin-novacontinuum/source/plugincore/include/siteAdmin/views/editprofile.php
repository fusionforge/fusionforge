<?php

if(isset($instanceid)&&isset($profileid)){
	$instance = $serviceManager->getContinuumInstance($instanceid);
	
	$profileToEdit = $serviceManager->getProfileForInstance($instance,$profileid);
}
require_once(dirname(__FILE__).'/addprofile.php');
?>
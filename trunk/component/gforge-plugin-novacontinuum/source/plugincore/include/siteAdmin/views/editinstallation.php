<?php

if(isset($instanceid)&&isset($installationid)){
	$instance = $serviceManager->getContinuumInstance($instanceid);
	
	$installationToEdit = $serviceManager->getInstallationForInstance($instance,$installationid);
}
require_once(dirname(__FILE__).'/addinstallation.php');
?>
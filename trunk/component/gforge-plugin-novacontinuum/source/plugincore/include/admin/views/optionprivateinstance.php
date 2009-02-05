<?php

$instance = $serviceManager->getPrivateInstanceForProjects($group_id);
if(isset($instance)){
	$instanceid = $instance->id; 
	require_once(dirname(__FILE__).'/../../siteAdmin/views/optioninstance.php');
}
?>
<?php

if(isset($instanceid)&&isset($scheduleid)){
	$instance = $serviceManager->getContinuumInstance($instanceid);
	
	$scheduleToEdit = $serviceManager->getScheduleForInstance($instance,$scheduleid);
}
require_once(dirname(__FILE__).'/addschedule.php');
?>
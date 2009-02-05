<?php

if(isset($notifierid)){

	$selectedInstance = $serviceManager->getInstanceForProjects($group_id);

	if(isset($projectid)){	
		$notifierToEdit = $serviceManager->getNotifier($selectedInstance->instance,$projectid,$notifierid);
	}else{
		$notifierToEdit = $serviceManager->getGroupNotifier($selectedInstance->instance,$selectedInstance->continuumProjectGroupId,$notifierid);
	}
}
require_once(dirname(__FILE__).'/addnotifier.php');
?>
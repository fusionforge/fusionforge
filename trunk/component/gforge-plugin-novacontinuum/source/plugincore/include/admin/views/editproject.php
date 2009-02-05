<?php

if(isset($projectid)){
	$projectToEdit = $serviceManager->getProject($projectid);
}
require_once(dirname(__FILE__).'/addproject.php');
?>
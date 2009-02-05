<?php

if(isset($instanceid)){
	$instanceToEdit = $serviceManager->getContinuumInstance($instanceid);
}
require_once(dirname(__FILE__).'/addinstance.php');
?>
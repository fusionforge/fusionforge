<?php

if(isset($instanceid)){
	$instanceToEdit = $serviceManager->getHttpProxy($instanceid);
}
require_once(dirname(__FILE__).'/addproxy.php');
?>
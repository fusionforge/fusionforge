<?php

if(isset($builddefid)&&isset($group_id)){
	$buildDefToEdit = $serviceManager->getbuilddef($builddefid,$group_id);
}
require_once(dirname(__FILE__).'/addbuilddef.php');
?>
<?php 


include dirname(__FILE__).'/commonsactions.php';

if ((isset ($view) == false))
{
	$view = "default";
}

switch ($view)
	{
		case "default" :
			break;
		case "showbuildresults":
		case "showbuildresult":
			if(!$serviceManager->hasRoleForGroup($group_id,'show_build_result')){
				$view = "default";
			}
			break;
		case "showprojectdetails":
			if(!$serviceManager->hasRoleForGroup($group_id,'show_project_detail')){
				$view = "default";
			}
			break;
		case "deletesite":
			if(!$serviceManager->hasRoleForGroup($group_id,'write_maven_site')){
				$view = "default";
			}
			break;
		default :
			$view = "default";
	}
$from = 'user';
include dirname(__FILE__).'/views/'.$view.'.php';
?>
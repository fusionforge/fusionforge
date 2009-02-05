<?php 

if ((isset ($action) == true))
{
	switch ($action)
	{
		case "buildproject":
			if($serviceManager->hasRoleForGroup($group_id,'run_project')){
				if(isset($projectid)&&isset($group_id)){
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($selectedInstance)){
						$pingRet = $selectedInstance->instance->ping();
						if($pingRet===true){
							$continuumProjects = $serviceManager->getAllContinuumProjects($projectid);
							foreach ($continuumProjects as $key=>$value) {
	      				$selectedInstance->instance->addProjectToBuildQueue($value);
	      			}
							
						}
					}
				}
			}
			break;
		case "buildsubproject":
			if($serviceManager->hasRoleForGroup($group_id,'run_continuum_project')){
				if(isset($projectid)&&isset($group_id)){
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($selectedInstance)){
						$pingRet = $selectedInstance->instance->ping();
						if($pingRet===true){
	      				$selectedInstance->instance->addProjectToBuildQueue($projectid);
						}
					}
				}
			}
			break;
		case "buildwithbuilddef":
			if($serviceManager->hasRoleForGroup($group_id,'run_build_def')){
				if(isset($builddefid)&&isset($group_id)){
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($selectedInstance)){
						$pingRet = $selectedInstance->instance->ping();
						if($pingRet===true){
	      			$projects = $serviceManager->getProjects($group_id);
							foreach ($projects as $project) {
								foreach ($project->continuumProjects as $value) {
	       					$selectedInstance->instance->addProjectToBuildQueueWithBuildDef($value,$builddefid);
	       				}
	      			}
						}
					}
				}
			}
			break;
		case "deletesite":
			if($serviceManager->hasRoleForGroup($group_id,'write_maven_site')){
				if(isset($siteid)&&isset($group_id)){
					$serviceManager->deleteSite($siteid,$group_id);
				}
			}
			break;
	}
}

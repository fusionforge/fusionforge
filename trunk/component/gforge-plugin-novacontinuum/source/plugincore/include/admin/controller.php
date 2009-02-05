<?php 

if ((isset ($action) == true))
{
	switch ($action)
	{
		case "updateRoles" :
			if($serviceManager->hasRoleForGroup($group_id,'manage_role')){
				$roles = array();
				if(isset($manage_private_instance)){
					$roles['manage_private_instance'] = array();
					foreach ($manage_private_instance as $key=>$value) {
	     			$roles['manage_private_instance'][$key] = true;
	     		}
				}
				if(isset($select_instance)){
					$roles['select_instance'] = array();
					foreach ($select_instance as $key=>$value) {
	     			$roles['select_instance'][$key] = true;
	     		}
				}
				if(isset($manage_build_def)){
					$roles['manage_build_def'] = array();
					foreach ($manage_build_def as $key=>$value) {
	     			$roles['manage_build_def'][$key] = true;
	     		}
				}
				if(isset($run_build_def)){
					$roles['run_build_def'] = array();
					foreach ($run_build_def as $key=>$value) {
	     			$roles['run_build_def'][$key] = true;
	     		}
				}
				if(isset($read_maven_site)){
					$roles['read_maven_site'] = array();
					foreach ($read_maven_site as $key=>$value) {
	     			$roles['read_maven_site'][$key] = true;
	     		}
				}
				if(isset($write_maven_site)){
					$roles['write_maven_site'] = array();
					foreach ($write_maven_site as $key=>$value) {
	     			$roles['write_maven_site'][$key] = true;
	     		}
				}
				if(isset($manage_project)){
					$roles['manage_project'] = array();
					foreach ($manage_project as $key=>$value) {
	     			$roles['manage_project'][$key] = true;
	     		}
				}
				if(isset($release_project)){
					$roles['release_project'] = array();
					foreach ($release_project as $key=>$value) {
	     			$roles['release_project'][$key] = true;
	     		}
				}
				if(isset($run_project)){
					$roles['run_project'] = array();
					foreach ($run_project as $key=>$value) {
	     			$roles['run_project'][$key] = true;
	     		}
				}
				if(isset($run_continuum_project)){
					$roles['run_continuum_project'] = array();
					foreach ($run_continuum_project as $key=>$value) {
	     			$roles['run_continuum_project'][$key] = true;
	     		}
				}
				if(isset($show_build_result)){
					$roles['show_build_result'] = array();
					foreach ($show_build_result as $key=>$value) {
	     			$roles['show_build_result'][$key] = true;
	     		}
				}
				if(isset($show_project_detail)){
					$roles['show_project_detail'] = array();
					foreach ($show_project_detail as $key=>$value) {
	     			$roles['show_project_detail'][$key] = true;
	     		}
				}
				if(isset($view_access)){
					$roles['view_access'] = array();
					foreach ($view_access as $key=>$value) {
	     			$roles['view_access'][$key] = true;
	     		}
				}
				if(isset($all)){
					foreach ($all as $key=>$value) {
	     			$roles['manage_private_instance'][$key] = true;
	     			$roles['select_instance'][$key] = true;
	     			$roles['manage_build_def'][$key] = true;
	     			$roles['run_build_def'][$key] = true;
	     			$roles['read_maven_site'][$key] = true;
	     			$roles['write_maven_site'][$key] = true;
	     			$roles['release_project'][$key] = true;
	     			$roles['manage_project'][$key] = true;
	     			$roles['run_project'][$key] = true;
	     			$roles['run_continuum_project'][$key] = true;
	     			$roles['show_build_result'][$key] = true;
	     			$roles['show_project_detail'][$key] = true;
	     			$roles['view_access'][$key] = true;
	     		}
				}
				if(isset($none)){
					foreach ($none as $key=>$value) {
	     			$roles['manage_private_instance'][$key] = false;
	     			$roles['select_instance'][$key] = false;
	     			$roles['manage_build_def'][$key] = false;
	     			$roles['run_build_def'][$key] = false;
	     			$roles['read_maven_site'][$key] = false;
	     			$roles['write_maven_site'][$key] = false;
	     			$roles['release_project'][$key] = false;
	     			$roles['manage_project'][$key] = false;
	     			$roles['run_project'][$key] = false;
	     			$roles['run_continuum_project'][$key] = false;
	     			$roles['show_build_result'][$key] = false;
	     			$roles['show_project_detail'][$key] = false;
	     			$roles['view_access'][$key] = false;
	     		}
				}
				if(isset($manage_role)){
					$roles['manage_role'] = array();
					foreach ($manage_role as $key=>$value) {
	     			$roles['manage_role'][$key] = true;
	     		}
				}
				
				$serviceManager->deleteRoles($group_id);
				
				foreach ( $roles as $rolename=>$roleids) {
				
					foreach ( $roleids as $roleid=>$value) {
						if($value == true){
		   				$serviceManager->addRoleForGroup($group_id,$roleid,$rolename);
		   			}
		   		}
		   	}
   		}
			break;
		case "selectInstance" :
			if($serviceManager->hasRoleForGroup($group_id,'select_instance')){
				if(isset($instance)){
					if($instance==-1){
						$serviceManager->removeInstanceForProject($group_id);
					}else{
						$serviceManager->defineInstanceForProject($group_id,$instance);
					}
				}
			}
			break;
		case "addprivateinstance" :
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($url)){
					require_once dirname(__FILE__).'/../dto/ContinuumInstance.php';
					require_once dirname(__FILE__).'/../dto/HttpProxy.php';
					$instance= new ContinuumInstance($group->getPublicName(),$url,$user,$password,1,true);
					if($proxy>0){
						$instance->httpProxy=new HttpProxy();
						$instance->httpProxy->id=$proxy;
					}
					$instance->groupId=$group_id;
					$serviceManager->addContinuumInstance($instance);
					
				}
			}
			break;
		case "deleteinstance":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				$serviceManager->deletePrivateInstance($group_id);
			}
			break;
		case "editprivateinstance":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($url)){
					$instance = $serviceManager->getPrivateInstanceForProjects($group_id);
					$instance->url=$url;
					$instance->user=$user;
					$instance->password=$password;
					if($proxy>0){
						$instance->httpProxy=new HttpProxy();
						$instance->httpProxy->id=$proxy;
					}else{
						$instance->httpProxy=null;
					}
					$serviceManager->updateContinuumInstance($instance);
				}
			}
			break;
		case "addproject":
			if($serviceManager->hasRoleForGroup($group_id,'manage_project')){
				if(isset($name)&&isset($url)){
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($selectedInstance)){
						$pingRet = $selectedInstance->instance->ping();
						if($pingRet===true){
							$serviceManager->addMavenTwoProject($name,$url,$username,$password,$selectedInstance,$group_id);
						}
					}
				}
			}
			break;
		case "editproject":
			if($serviceManager->hasRoleForGroup($group_id,'manage_project')){
				if(isset($projectid)&&isset($group_id)&&isset($name)&&isset($url)){
				
					$projectToEdit = $serviceManager->getProject($projectid);
					
					
					if($url!=$projectToEdit->url||$username!=$projectToEdit->userName||$password!=$projectToEdit->pwd){
						
						$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
						if(isset($selectedInstance)){
							$pingRet = $selectedInstance->instance->ping();
							if($pingRet===true){
								$continuumProjects = $serviceManager->getAllContinuumProjects($projectid);
								foreach ($continuumProjects as $key=>$value) {
		      				$selectedInstance->instance->removeProject($value);
		      			}
								
								$serviceManager->deleteProject($projectid);
								$serviceManager->addMavenTwoProject($name,$url,$username,$password,$selectedInstance,$group_id);
							}
						}
					}else{
						if($name!=$projectToEdit->name){
							$projectToEdit->name = $name;
							$serviceManager->updateProject($projectToEdit);
						}
					}
				}
			}
			break;
		case "deleteproject":
			if($serviceManager->hasRoleForGroup($group_id,'manage_project')){
				if(isset($projectid)&&isset($group_id)){
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($selectedInstance)){
						$pingRet = $selectedInstance->instance->ping();
						if($pingRet===true){
							$continuumProjects = $serviceManager->getAllContinuumProjects($projectid);
							foreach ($continuumProjects as $key=>$value) {
	      				$selectedInstance->instance->removeProject($value);
	      			}
							
							$serviceManager->deleteProject($projectid);
						}
					}
				}
			}
			break;
		case "setdefaultbuilddef":
			if($serviceManager->hasRoleForGroup($group_id,'manage_build_def')){
				if(isset($builddefid)&&isset($group_id)){
					$serviceManager->setdefaultbuilddef($builddefid,$group_id);
				}
			}
			break;
		case "unsetdefaultbuilddef":
			if($serviceManager->hasRoleForGroup($group_id,'manage_build_def')){
				if(isset($builddefid)&&isset($group_id)){
					$serviceManager->unsetdefaultbuilddef($builddefid,$group_id);
				}
			}
			break;
		case "deletebuilddef":
			if($serviceManager->hasRoleForGroup($group_id,'manage_build_def')){
				if(isset($builddefid)&&isset($group_id)){
					$serviceManager->deletebuilddef($builddefid,$group_id);
				}
			}
			break;
		case "addbuilddef":
			if($serviceManager->hasRoleForGroup($group_id,'manage_build_def')){
				if(!isset($profile)){
					$profile = null;
				}
				if(isset($buildFresh)){
					$buildFresh=true;
				}else{
					$buildFresh=false;
				}
				if(isset($alwaysBuild)){
					$alwaysBuild=true;
				}else{
					$alwaysBuild=false;
				}
				if(!isset($buildFile)){
					$buildFile = null;
				}
				if(isset($goals)&&isset($arguments)&&isset($schedule)&&isset($group_id)){
					$serviceManager->addbuilddef($group_id,$goals,$arguments,$schedule,$profile,$buildFresh,$alwaysBuild,$buildFile);
				}
			}
			break;
		case "editbuilddef":
			if($serviceManager->hasRoleForGroup($group_id,'manage_build_def')){
				if(!isset($profile)){
					$profile = null;
				}
				if(isset($buildFresh)){
					$buildFresh=true;
				}else{
					$buildFresh=false;
				}
				if(isset($alwaysBuild)){
					$alwaysBuild=true;
				}else{
					$alwaysBuild=false;
				}
				if(!isset($buildFile)){
					$buildFile = null;
				}
				if(isset($goals)&&isset($arguments)&&isset($schedule)&&isset($group_id)&&isset($builddefid)){
					$serviceManager->editbuilddef($group_id,$builddefid,$goals,$arguments,$schedule,$profile,$buildFresh,$alwaysBuild,$buildFile);
				}
			}
			break;
		case "disableschedule":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($scheduleid)&&isset($instanceid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->disableSchedule($instance,$scheduleid);
				}
			}
			break;
		case "enableschedule":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($scheduleid)&&isset($instanceid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->enableSchedule($instance,$scheduleid);
				}
			}
			break;
		case "deleteschedule":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($scheduleid)&&isset($instanceid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->deleteSchedule($instance,$scheduleid);
				}
			}
			break;
		case "addschedule":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($name)&&isset($instanceid)){
				
					$schedule = new Schedule();
					if($cronExpressionYear==''){
						$cronExp = array($cronExpressionSeconde,$cronExpressionMinute,$cronExpressionHour,$cronExpressionDayOfMonth,$cronExpressionMonth,$cronExpressionDayOfWeek);
					}else{
						$cronExp = array($cronExpressionSeconde,$cronExpressionMinute,$cronExpressionHour,$cronExpressionDayOfMonth,$cronExpressionMonth,$cronExpressionDayOfWeek,$cronExpressionYear);
					}
					$schedule->setCronExp($cronExp);
					$schedule->maxJobExecutionTime=$maxJobExecutionTime;
					$schedule->delay=$delay;
					$schedule->description=$description;
					$schedule->name=$name;
					if(isset($active)){
						$schedule->active=true;
					}else{
						$schedule->active=false;
					}
					
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->addSchedule($instance,$schedule);
				}
			}
			break;
		case "editschedule":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($name)&&isset($instanceid)&&isset($scheduleid)){
				
					$schedule = new Schedule();
					$schedule->id=$scheduleid;
					if($cronExpressionYear==''){
						$cronExp = array($cronExpressionSeconde,$cronExpressionMinute,$cronExpressionHour,$cronExpressionDayOfMonth,$cronExpressionMonth,$cronExpressionDayOfWeek);
					}else{
						$cronExp = array($cronExpressionSeconde,$cronExpressionMinute,$cronExpressionHour,$cronExpressionDayOfMonth,$cronExpressionMonth,$cronExpressionDayOfWeek,$cronExpressionYear);
					}
					$schedule->setCronExp($cronExp);
					$schedule->maxJobExecutionTime=$maxJobExecutionTime;
					$schedule->delay=$delay;
					$schedule->description=$description;
					$schedule->name=$name;
					if(isset($active)){
						$schedule->active=true;
					}else{
						$schedule->active=false;
					}
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->updateSchedule($instance,$schedule);
				}
			}
			break;
		case "deletenotifier":
			if($serviceManager->hasRoleForGroup($group_id,'manage_project')){
				if(isset($notifierid)){
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($projectid)){	
						$serviceManager->deleteNotifier($selectedInstance->instance,$projectid,$notifierid);
					}else{
						$serviceManager->deleteGroupNotifier($selectedInstance->instance,$selectedInstance->continuumProjectGroupId,$notifierid);
					}
				}
			}
			break;
		case "addnotifier":
			if($serviceManager->hasRoleForGroup($group_id,'manage_project')){
				if(isset($address)){
				
					$notifier = new ProjectNotifier();
					$notifier->address=$address;
					if(isset($sendOnSuccess)){
						$notifier->sendOnSuccess=true;
					}else{
						$notifier->sendOnSuccess=false;
					}
					if(isset($sendOnFailure)){
						$notifier->sendOnFailure=true;
					}else{
						$notifier->sendOnFailure=false;
					}
					if(isset($sendOnError)){
						$notifier->sendOnError=true;
					}else{
						$notifier->sendOnError=false;
					}
					if(isset($sendOnWarning)){
						$notifier->sendOnWarning=true;
					}else{
						$notifier->sendOnWarning=false;
					}
					if(isset($enabled)){
						$notifier->enabled=true;
					}else{
						$notifier->enabled=false;
					}
					$notifier->type = 'mail';
					$notifier->from = 2;
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($projectid)){	
						$serviceManager->addNotifier($selectedInstance->instance,$projectid,$notifier);
					}else{
						$serviceManager->addGroupNotifier($selectedInstance->instance,$selectedInstance->continuumProjectGroupId,$notifier);
					}
				}
			}
			break;
		case "editnotifier":
			if($serviceManager->hasRoleForGroup($group_id,'manage_project')){
				if(isset($address)&&isset($notifierid)){
				
					$notifier = new ProjectNotifier();
					$notifier->id=$notifierid;
					$notifier->address=$address;
					if(isset($sendOnSuccess)){
						$notifier->sendOnSuccess=true;
					}else{
						$notifier->sendOnSuccess=false;
					}
					if(isset($sendOnFailure)){
						$notifier->sendOnFailure=true;
					}else{
						$notifier->sendOnFailure=false;
					}
					if(isset($sendOnError)){
						$notifier->sendOnError=true;
					}else{
						$notifier->sendOnError=false;
					}
					if(isset($sendOnWarning)){
						$notifier->sendOnWarning=true;
					}else{
						$notifier->sendOnWarning=false;
					}
					if(isset($enabled)){
						$notifier->enabled=true;
					}else{
						$notifier->enabled=false;
					}
					$notifier->type = 'mail';
					$notifier->from = 2;
					$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
					if(isset($projectid)){	
						$serviceManager->updateNotifier($selectedInstance->instance,$projectid,$notifier);
					}else{
						$serviceManager->updateGroupNotifier($selectedInstance->instance,$selectedInstance->continuumProjectGroupId,$notifier);
					}
				}
			}
			break;
		case "deleteinstallation":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($installationid)&&isset($instanceid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->deleteInstallation($instance,$installationid);
				}
			}
			break;
		case "addinstallation":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($name)&&isset($type)&&isset($instanceid)){
				
					$installation = new Installation();
					$installation->name=$name;
					$installation->varName=$varName;
					$installation->type=$type;
					if($type =='jdk'){
						$installation->varName='JAVA_HOME';
					}else if($type =='maven2'){
						$installation->varName='M2_HOME';
					}
					
					$installation->varValue=$varValue;
					
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->addInstallation($instance,$installation);
				}
			}
			break;
		case "editinstallation":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($name)&&isset($type)&&isset($instanceid)&&isset($installationid)){
				
					$installation = new Installation();
					$installation->installationId=$installationid;
					$installation->name=$name;
					$installation->varName=$varName;
					$installation->type=$type;
					if($type =='jdk'){
						$installation->varName='JAVA_HOME';
					}else if($type =='maven2'){
						$installation->varName='M2_HOME';
					}
					
					$installation->varValue=$varValue;
					
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->updateInstallation($instance,$installation);
				}
			}
			break;
		case "deleteprofile":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($profileid)&&isset($instanceid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$serviceManager->deleteProfile($instance,$profileid);
				}
			}
			break;
		case "addprofile":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($name)&&isset($instanceid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$profile = new Profile();
					$profile->name=$name;
					
					if($jdk == -1){
						$profile->jdk = null;
					}else{
						$profile->jdk = $serviceManager->getInstallationForInstance($instance,$jdk);
					}
					if($builder == -1){
						$profile->builder = null;
					}else{
						$profile->builder = $serviceManager->getInstallationForInstance($instance,$builder);
					}
					$profile->environmentVariables = array();
					if (isset($varenvs)){
						foreach ($varenvs as $key=>$varenv) {
      				$profile->environmentVariables[] = $serviceManager->getInstallationForInstance($instance,$varenv);
      			}
					}
					$serviceManager->addProfile($instance,$profile);
				}
			}
			break;
		case "editprofile":
			if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				if(isset($name)&&isset($instanceid)&&isset($profileid)){
					$instance = $serviceManager->getContinuumInstance($instanceid);
					$profile = new Profile();
					$profile->id=$profileid;
					$profile->name=$name;
					if($jdk == -1){
						$profile->jdk = null;
					}else{
						$profile->jdk = $serviceManager->getInstallationForInstance($instance,$jdk);
					}
					if($builder == -1){
						$profile->builder = null;
					}else{
						$profile->builder = $serviceManager->getInstallationForInstance($instance,$builder);
					}
					$profile->environmentVariables = array();
					if (isset($varenvs)){
						foreach ($varenvs as $key=>$varenv) {
      				$profile->environmentVariables[] = $serviceManager->getInstallationForInstance($instance,$varenv);
      			}
					}
					$serviceManager->updateProfile($instance,$profile);
				}
			}
			break;
			
		case "releaseproject":
		  if($serviceManager->hasRoleForGroup($group_id,'release_project')){
		    if(isset($projectid)&& isset($newversion)&&isset($group_id)){
		      if(isset($addtag)){
  					$addtag=true;
  				}else{
  					$addtag=false;
  				}
		      $serviceManager->releaseProject($projectid,$newversion,$addtag,$group_id);
		    }
		  }
			break;
	}
}
include dirname(__FILE__).'/../user/commonsactions.php';

if ((isset ($view) == false))
{
	$view = "default";
}

switch ($view)
	{
		case "default" :
			break;
		case "addprivateinstance":
		case "editprivateinstance":
		case "deleteprivateinstance":
		case "optionprivateinstance":
		case "addschedule":
		case "editschedule":
		case "deleteschedule":
		case "addinstallation":
		case "editinstallation":
		case "deleteinstallation":
		case "addprofile":
		case "editprofile":
		case "deleteprofile":
			if(!$serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
				$view = "default";
			}
			break;
		case "releaseproject":
		case "releaseproject_access_control":
			if(!$serviceManager->hasRoleForGroup($group_id,'release_project')){
				$view = "default";
				break;
			}
			if(!isset($projectid)){
        $view = "default";
				break;
      }
      $errorManager =& ErrorManager::getInstance();
			$userForge=session_get_user();
			
      if(!isset($username) || $username != $userForge->getUnixName()){
			  if(isset($username)){
			     $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "prepare_username_not_valid"));
			  }
			  $view = "releaseproject_access_control";
        break;
      }
      
			if(!isset($password) || md5($password) != $userForge->getMD5Passwd()){
			  if($password != ''){
			     $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "prepare_password_not_valid"));
			  }else{
           $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "prepare_password_empty"));
        }
        
        $view = "releaseproject_access_control";
        break;
      }
      $projectRelease = $serviceManager->prepareRelease($username,$password,$projectid);
      if($projectRelease == null){
        $view = "default";
				break;
      }
			break;
		case "addproject":
		case "editproject":
		case "deleteproject":
		case "addnotifier":
		case "editnotifier":
		case "deletenotifier":
			if(!$serviceManager->hasRoleForGroup($group_id,'manage_project')){
				$view = "default";
			}
			break;
		case "addbuilddef":
		case "editbuilddef":
		case "deletebuilddef":
			if(!$serviceManager->hasRoleForGroup($group_id,'manage_build_def')){
				$view = "default";
			}
			break;
		case "showbuildresults":
		case "showbuildresult":
			if(!$serviceManager->hasRoleForGroup($group_id,'show_build_result')){
				$view = "default";
			}else{
				$view ='../../user/views/'.$view;
			}
			break;
		case "showprojectdetails":
			if(!$serviceManager->hasRoleForGroup($group_id,'show_project_detail')){
				$view = "default";
			}else{
				$view ='../../user/views/'.$view;
			}
			break;
		case "deletesite":
			if(!$serviceManager->hasRoleForGroup($group_id,'write_maven_site')){
				$view = "default";
			}else{
				$view ='../../user/views/'.$view;
			}
			break;
		default :
			$view = "default";
	}
$from = 'admin';
include dirname(__FILE__).'/views/'.$view.'.php';
?>
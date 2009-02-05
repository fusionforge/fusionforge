<?php 
if ((isset ($action) == true))
{
	switch ($action)
	{
		case "allowPrivateInstance" :
			$configuration = $serviceManager->getConfiguration();
			$configuration->values['allowPrivateInstance'] = '1';
			$serviceManager->updateConfiguration($configuration);
			break;
		case "disallowPrivateInstance" :
			$configuration = $serviceManager->getConfiguration();
			$configuration->values['allowPrivateInstance'] = '0';
			$serviceManager->updateConfiguration($configuration);
			break;
		case "addinstance" :
			if(isset($name)&&isset($url)){
				if(isset($isEnabled)){
					$isEnabled = 1;
				}else{
					$isEnabled = 0;
				}
				require_once dirname(__FILE__).'/../dto/ContinuumInstance.php';
				require_once dirname(__FILE__).'/../dto/HttpProxy.php';
				$instance= new ContinuumInstance($name,$url,$user,$password,isset($maxUse)?$maxUse:0,$isEnabled);
				if($proxy>0){
					$instance->httpProxy=new HttpProxy();
					$instance->httpProxy->id=$proxy;
				}
				$serviceManager->addContinuumInstance($instance);
				
			}
			break;
		case "editinstance" :
			if(isset($name)&&isset($url)&&isset($instanceid)){
				if(isset($isEnabled)){
					$isEnabled = 1;
				}else{
					$isEnabled = 0;
				}
				require_once dirname(__FILE__).'/../dto/ContinuumInstance.php';
				require_once dirname(__FILE__).'/../dto/HttpProxy.php';
				$instance= new ContinuumInstance($name,$url,$user,$password,isset($maxUse)?$maxUse:0,$isEnabled);
				$instance->id=$instanceid;
				$instance->groupId=$instanceGroupId;
				if($proxy>0){
					$instance->httpProxy=new HttpProxy();
					$instance->httpProxy->id=$proxy;
				}
				$serviceManager->updateContinuumInstance($instance);
			}
			break;
		case "deleteinstance" :
			if(isset($instanceid)){
				$serviceManager->deleteContinuumInstance($instanceid);
			}
			break;
		case "disableinstance" :
			if(isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$instance->isEnabled=0;
				$serviceManager->updateContinuumInstance($instance);
			}
			break;
		case "updateconfiguration" :
			break;
		case "enableinstance" :
			if(isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$instance->isEnabled=1;
				$serviceManager->updateContinuumInstance($instance);
			}
			break;
		case "addproxy" :
			if(isset($name)&&isset($host)&&isset($port)){
				require_once dirname(__FILE__).'/../dto/HttpProxy.php';
				$instance= new HttpProxy($name, $host, $port, $userName, $password);
				$serviceManager->addHttpProxy($instance);
			}
			break;
		case "editproxy" :
			if(isset($name)&&isset($host)&&isset($port)&&isset($instanceid)){
				require_once dirname(__FILE__).'/../dto/HttpProxy.php';
				$instance= new HttpProxy($name, $host, $port, $userName, $password);
				$instance->id=$instanceid;
				$serviceManager->updateHttpProxy($instance);
			}
			break;
		case "deleteproxy" :
			if(isset($instanceid)){
				$serviceManager->deleteHttpProxy($instanceid);
			}
			break;
		case "disableschedule":
			if(isset($scheduleid)&&isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$serviceManager->disableSchedule($instance,$scheduleid);
			}
			break;
		case "enableschedule":
			if(isset($scheduleid)&&isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$serviceManager->enableSchedule($instance,$scheduleid);
			}
			break;
		case "deleteschedule":
			if(isset($scheduleid)&&isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$serviceManager->deleteSchedule($instance,$scheduleid);
			}
			break;
		case "addschedule":
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
			break;
		case "editschedule":
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
			break;
		case "deleteinstallation":
			if(isset($installationid)&&isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$serviceManager->deleteInstallation($instance,$installationid);
			}
			break;
		case "addinstallation":
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
			break;
		case "editinstallation":
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
			break;
		case "deleteprofile":
			if(isset($profileid)&&isset($instanceid)){
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$serviceManager->deleteProfile($instance,$profileid);
			}
			break;
		case "addprofile":
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
				$instance = $serviceManager->getContinuumInstance($instanceid);
				$serviceManager->addProfile($instance,$profile);
			}
			break;
		case "editprofile":
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
			break;
	}
}


if ((isset ($view) == false))
{
	$view = "default";
}
switch ($view)
	{
		case "default" :
		case "addproxy" :
		case "editproxy" :
		case "deleteproxy" :
		case "addinstance" :
		case "editinstance" :
		case "deleteinstance" :
		case "optioninstance" :
		case "addschedule":
		case "editschedule":
		case "deleteschedule":
		case "addinstallation":
		case "editinstallation":
		case "deleteinstallation":
		case "addprofile":
		case "editprofile":
		case "deleteprofile":
			break;
		default :
			$view = "default";
	}
$from = 'siteAdmin';
include dirname(__FILE__).'/views/'.$view.'.php';
?>
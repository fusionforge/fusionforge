<?php
require_once('plugins/report/include/graph/KiviatGraph.php');

require('common/include/constants.php');
require('local.inc');
require_once('common/include/database.php');

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}

include ('plugins/report/config.php');
require_once('common/novaforge/log.php');

require_once('plugins/report/include/dao/CheckStyleCheckerDAO.php');
require_once('plugins/report/include/dao/MavenInfoDAO.php');
require_once('plugins/report/include/dao/JavancssDAO.php');

$dao =& CheckStyleCheckerDAO::getInstance();
$mavenDao =& MavenInfoDAO::getInstance();

if(isset($allmodules) && $allmodules == 'false'){
	$allmodules = false;
}else{
	$allmodules = true;
}

if(isset($mvngrpid)&&isset($mvnartid)){
	$allmodules = false;
}else{
	$allmodules = true;
}


if($allmodules){
	$versions = $mavenDao->getVersions($group_id);
	if(!isset($mvnversion)){
		$mvnversion = $dao->getLastVersion($group_id);
	}
}else{
	$foundVersion = false;
	if(isset($mvnversion)){
		$versions = $mavenDao->getVersionsForModule($group_id,$mvngrpid,$mvnartid);
		foreach ($versions as $version) {
			if($version == $mvnversion){
				$foundVersion = true;
			}
		}
	}
	if(!$foundVersion){
		$mvnversion = $dao->getLastVersionForModule($group_id,$mvngrpid,$mvnartid);
	}
}

$modules = $mavenDao->getModules($group_id);

$jncssDAO =& JavancssDAO::getInstance();

$jncssResume = null;
$objectives = null;
if($allmodules){
	$modules = $mavenDao->getModules($group_id);
	$jncssResume = new JavaNCSSResumeDTO();
	$jncssResume->setGroupId($group_id);
	$jncssResume->setMavenGroupId("");
	$jncssResume->setMavenArtefactId("");
	$jncssResume->setMavenVersion($mvnversion);
	$jncssResume->setNbFunction(0);
	$jncssResume->setNbClass(0);
	$jncssResume->setNbPackage(0);
	if(!empty($modules)){
		foreach ($modules as $mod) {
			$jncssResumeMOD = $jncssDAO->getResume($group_id,$mod->getMavenGroupId(),$mod->getMavenArtefactId(),$mvnversion);
			if( $jncssResumeMOD != null ){
				$jncssResume->setNbFunction($jncssResume->getNbFunction()+$jncssResumeMOD->getNbFunction());
				$jncssResume->setNbClass($jncssResume->getNbClass()+$jncssResumeMOD->getNbClass());
				$jncssResume->setNbPackage($jncssResume->getNbPackage()+$jncssResumeMOD->getNbPackage());
			}
		}
	}

	$objectives = $dao->getObjectivesForVersion($group_id,$mvnversion);
}else{
	$jncssResume = $jncssDAO->getResume($group_id,$mvngrpid,$mvnartid,$mvnversion);
	$objectives = $dao->getObjectivesForModule($group_id,$mvngrpid,$mvnartid,$mvnversion);
}

$titles = array();
$real = array();
$rejected = array();
$acceptableWithReserve = array();
$accepted = array();

foreach ($objectives as $obj) {
	$titles [] = utf8_decode($obj->getName());
	$real [] = round($obj->getRate($jncssResume),2);
	$rejected [] = 2.0;
	$acceptableWithReserve [] = 3.0;
	$accepted [] = 4.0;
}

$graph = new KiviatGraph($titles);


$graph->addRadarPlot($accepted, 'lightgreen@0.5', 'green', 'Accepted');
$graph->addRadarPlot($acceptableWithReserve, 'lightyellow@0.5', 'yellow', 'Acceptable with reserve');
$graph->addRadarPlot($rejected, 'lightred@0.5', 'red', 'Rejected');
$graph->addRadarPlot($real, 'lightblue@0.5', 'blue', 'Real');


$graph->printImage();

?>
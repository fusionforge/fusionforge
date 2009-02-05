<?php

require_once('../../env.inc.php');
require_once($gfwww.'include/pre.php');
include ('plugins/report/config.php');
require_once('common/novaforge/log.php');
require_once('plugins/report/include/dao/CheckStyleCheckerDAO.php');
require_once('plugins/report/include/dao/MavenInfoDAO.php');
require_once('plugins/report/include/dao/JavancssDAO.php');
if (!$group_id) {
	exit_no_group();
}

$group = &group_get_object ($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

site_project_header (array ('title'=>'report', 'group'=>$group_id, 'toptab'=>'report'));

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

?>
<p><strong> <?php
$selectedStyle = 'style="color:#633; text-decoration:underline;" ';

if(isset($mvnversion)){
	$text_version = '&mvnversion='.$mvnversion;
}
$menu_text = dgettext ('gforge-plugin-report', 'all_modules');
$menu_links = '/plugins/report/index.php?allmodules=true&group_id='.$group_id.$text_version;

if($allmodules){
	$style = $selectedStyle;
}else{
	$style = '';
}

echo '<a '.$style.'href="'.$menu_links.'">'.$menu_text.'</a>';

$modules = $mavenDao->getModules($group_id);
if(!empty($modules)){
	echo ' | ';


	$lastModule = array_pop($modules);
	foreach ($modules as $mod) {
		printMenuModule($mod);
		echo ' | ';
	}
	printMenuModule($lastModule);
}
?> </strong></p>
<p><strong> <?php


if(!empty($versions)){

	$lastVersion = array_pop($versions);
	$menu_text = array ();
	$menu_links = array ();
	foreach ($versions as $version) {
		printMenuVersion($version);
		echo ' | ';
	}
	printMenuVersion($lastVersion);
}

function printMenuModule($mod){
	global $mvngrpid,$mvnartid,$mvnversion,$selectedStyle,$group_id;
	$style = '';
	if(isset($mvngrpid)&&isset($mvnartid)){
		if($mod->getMavenGroupId() == $mvngrpid && $mod->getMavenArtefactId() == $mvnartid){
			$style = $selectedStyle;
		}
	}
	if(isset($mvnversion)){
		$test_version = '&mvnversion='.$mvnversion;
	}
	$menu_text = $mod->getMavenArtefactId();
	$menu_links = '/plugins/report/index.php?allmodules=false&mvngrpid='.$mod->getMavenGroupId().'&mvnartid='.$mod->getMavenArtefactId().$test_version.'&group_id='.$group_id;
	echo '<a '.$style.'href="'.$menu_links.'">'.$menu_text.'</a>';
}

function printMenuVersion($version){
	global $mvngrpid,$mvnartid,$mvnversion,$selectedStyle,$group_id,$allmodules;
	$style = '';
	if(isset($mvnversion)){
		if($version == $mvnversion){
			$style = $selectedStyle;
		}
	}
	if(isset($mvngrpid)&&isset($mvnartid)){
		$test_mvn = '&mvngrpid='.$mvngrpid.'&mvnartid='.$mvnartid;
	}
	$menu_text = $version;
	$menu_links = '/plugins/report/index.php?allmodules='.$allmodules.$test_mvn.'&mvnversion='.$version.'&group_id='.$group_id;
	echo '<a '.$style.'href="'.$menu_links.'">'.$menu_text.'</a>';
}
?> </strong></p>
<?php

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



echo $HTML->boxTop (dgettext ('gforge-plugin-report', 'quality_objectives_synthesis'));

if(isset($mvngrpid)&&isset($mvnartid)){
	$test_mvn = '&mvngrpid='.$mvngrpid.'&mvnartid='.$mvnartid;
}
$url = 'kiviat.php?allmodules='.$allmodules.$test_mvn.'&mvnversion='.$mvnversion.'&group_id='.$group_id;
?>
<br />
<img src="<?php echo $url;?>" alt="Kiviat" border='none'/>
<br />
<br />
<table width="auto" align="left" border="1" cellspacing="0"
	cellpadding="7" style="border-collapse: collapse">
	<tr>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_quality_objective');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_quality_objective_rate');?></th>
	</tr>
	<?php
	foreach ($objectives as $obj) {
		?>
	<tr>
		<td><?php echo $obj->getName();?></td>
		<td><?php echo round($obj->getRate($jncssResume),1);?></td>
	</tr>
	<?php
	}
	?>
</table>
	<?php
	echo $HTML->boxMiddle (dgettext ('gforge-plugin-report', 'table_metriques_title'));
	?>
<br />
<table width="auto" align="left" border="1" cellspacing="0"
	cellpadding="7" style="border-collapse: collapse">
	<tr>
		<td><?php echo dgettext ('gforge-plugin-report', 'table_metriques_number_of_package');?></td>
		<td><?php if( $jncssResume != null ){ echo $jncssResume->getNbPackage(); }?></td>
	</tr>
	<tr>
		<td><?php echo dgettext ('gforge-plugin-report', 'table_metriques_number_of_class');?></td>
		<td><?php if( $jncssResume != null ){ echo $jncssResume->getNbClass(); }?></td>
	</tr>
	<tr>
		<td><?php echo dgettext ('gforge-plugin-report', 'table_metriques_number_of_methods');?></td>
		<td><?php if( $jncssResume != null ){ echo $jncssResume->getNbFunction(); }?></td>
	</tr>
</table>
	<?php
	foreach ($objectives as $obj) {

		echo $HTML->boxMiddle ($obj->getName());

		?>
		
<br />
<table width="auto" align="left" border="1" cellspacing="0"
	cellpadding="7" style="border-collapse: collapse">
	<tr>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_criteria');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_rate');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_coef');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_rates_1');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_rates_2');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_rates_3');?></th>
		<th><?php echo dgettext ('gforge-plugin-report', 'table_objective_rates_4');?></th>
	</tr>
	<?php
	foreach ($obj->getRules() as $rule) {
		?>
	<tr>
		<td><?php echo $rule->getName();?></td>
		<td><?php echo round($rule->getRate($jncssResume),1);?></td>
		<td><?php echo $rule->getCoef();?></td>
		<td><?php echo $rule->getRate1($jncssResume);?></td>
		<td><?php echo $rule->getRate2($jncssResume);?></td>
		<td><?php echo $rule->getRate3($jncssResume);?></td>
		<td><?php echo $rule->getRate4($jncssResume);?></td>
	</tr>
	<?php
	}

	?>
</table>
	<?php
	}
	echo $HTML->boxBottom ();

	site_project_footer (array ());
	?>

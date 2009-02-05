<?php
echo "<h2>" . dgettext ("gforge-plugin-novacontinuum", "title_admin") . "</h2>";

if($from == 'admin'){
	$urlPrefix = 'admin/';
}else{
	$urlPrefix = '';
}

$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');

$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);

if(isset($projectid)){

	$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
	if(isset($selectedInstance)){
		$pingRet = $selectedInstance->instance->ping();
		if($pingRet===true){
			$continuumProject = $serviceManager->getContinuumProject($projectid,$selectedInstance);
			$buildResults = $serviceManager->getBuildResultsForProject($projectid,$selectedInstance);
	
			echo $HTML->boxTop ( sprintf ( dgettext ("gforge-plugin-novacontinuum", "build_results"), $continuumProject->name));


?>

				<center>
          <table border="0" cellspacing="2" cellpadding="3" width="50%">
            <tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_name");?></label></th>
    					<td><?php echo $continuumProject->name;?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_group_id");?></label></th>
    					<td><?php echo $continuumProject->groupId;?></td>
						</tr>
            <tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_artifact_id");?></label></th>
    					<td><?php echo $continuumProject->artifactId;?></td>
						</tr>
            <tr>
    					<th width="35%" align="right"><label for="project_version" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_version");?></label></th>
    					<td><?php echo $continuumProject->version;?></td>
						</tr>
            <tr>
    					<th width="35%" align="right"><label for="project_projectGroup_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_projectgroup_name");?></label></th>
    					<td><?php echo $continuumProject->projectGroup->name;?></td>
						</tr>
            <tr>
    					<th width="35%" align="right"><label for="lastBuildDateTime" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_last_build");?></label></th>
    					<td><?php 
    					$buildResult = $serviceManager->getBuildResult($projectid,$continuumProject->latestBuildId,$selectedInstance);
							$time = $buildResult->endTime / 1000;
							echo date("D M j G:i:s T Y",$time);
							
							?></td>
						</tr>
          </table>
				</center>
			<center>
      	<table border="0" cellspacing="2" cellpadding="3" width="100%">
      	<tr  align="center">
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_number");?></th>
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_starttime");?></th>
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_endtime");?></th>
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_time");?></th>
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_state");?></th>
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_def_desc");?></th>
      		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "build_results_head_result");?></th>
      	</tr>
      	
      	 	
		<?php
			
			foreach ($buildResults as $key=>$value) {
				
				?>
				<tr align="center">
					<td><?php echo $value->buildNumber;?></td>
					<td><?php 
							$time = $value->startTime / 1000;
							echo date("D M j G:i:s T Y",$time);?></td>
					<td><?php 
							$time = $value->endTime / 1000;
							echo date("D M j G:i:s T Y",$time);?></td>
					<td><?php
							$diffTime = intval(($value->endTime - $value->startTime) / 1000);
							echo $diffTime;?></td>
					<td><a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=showbuildresult&buildresultid=<?php echo $value->id;?>&projectid=<?php echo $projectid;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "show_build_result_tooltip")?>"><img src="imgs/<?php echo $value->getStateImage();?>" alt="state" border="none"/></a></td>
					<td><?php echo $value->buildDefinition->goals.' '.$value->buildDefinition->arguments.' ('.$value->buildDefinition->schedule->name.')';?></td>
					<td></td>
   			</tr>
   			<?php
   		}
   		
   		?>
   			</table>
			</center>
   		<?php
		}
	}
}
echo $HTML->boxBottom ();
?>
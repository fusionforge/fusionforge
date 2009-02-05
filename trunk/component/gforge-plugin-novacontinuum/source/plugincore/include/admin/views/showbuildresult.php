<?php
echo "<h2>" . dgettext ("gforge-plugin-novacontinuum", "title_admin") . "</h2>";

$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=showbuildresults&projectid='.$projectid.'&group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);

if(isset($projectid)&&isset($buildresultid)){

	$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
	if(isset($selectedInstance)){
		$pingRet = $selectedInstance->instance->ping();
		if($pingRet===true){
			$continuumProject = $serviceManager->getContinuumProject($projectid,$selectedInstance);
			$buildResult = $serviceManager->getBuildResult($projectid,$buildresultid,$selectedInstance);
			$buildOutput = $serviceManager->getBuildOutput($projectid,$buildresultid,$selectedInstance);
		  
		  
			$time = $buildResult->startTime / 1000;
			$startStr = date("D M j G:i:s T Y",$time);
			echo $HTML->boxTop ( sprintf ( dgettext ("gforge-plugin-novacontinuum", "build_result" ) , $continuumProject->name, $startStr));

?>

				<center>
          <table border="0" cellspacing="2" cellpadding="3" width="50%">
            <tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_start_time");?></label></th>
    					<td><?php 
							$time = $buildResult->startTime / 1000;
							echo date("D M j G:i:s T Y",$time);?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_end_time");?></label></th>
    					<td><?php 
							$time = $buildResult->endTime / 1000;
							echo date("D M j G:i:s T Y",$time);?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_time");?></label></th>
    					<td><?php
							$diffTime = intval(($buildResult->endTime - $buildResult->startTime) / 1000);
							echo $diffTime;?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_trigger");?></label></th>
    					<td><?php 
								if($buildResult->trigger==1){
									echo dgettext ("gforge-plugin-novacontinuum", "build_result_trigger_forced");
								}else{
									echo dgettext ("gforge-plugin-novacontinuum", "build_result_trigger_planned");
								}?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_state");?></label></th>
    					<td><img src="imgs/<?php echo $buildResult->getStateImage();?>" alt="state" border="none"/></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_build_number");?></label></th>
    					<td><?php echo $buildResult->buildNumber;?></td>
						</tr>
          </table>
				</center>
				
   		<?php
   		
   		
   		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "build_result_used_def"));
   		?>

				<center>
          <table border="0" cellspacing="2" cellpadding="3" width="50%">
            <tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_build_def_goals");?></label></th>
    					<td><?php echo $buildResult->buildDefinition->goals;?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_build_def_arguments");?></label></th>
    					<td><?php echo $buildResult->buildDefinition->arguments;?></td>
						</tr>
						<tr>
    					<th width="35%" align="right"><label for="project_name" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "build_result_build_def_schedule");?></label></th>
    					<td><?php echo $buildResult->buildDefinition->schedule->name;?></td>
						</tr>
          </table>
				</center>
				
   		<?php
   		
   		if($buildResult->error!=''){
			 	echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "build_result_error"));
			 	$order   = array("\r\n", "\n", "\r");
				$replace = '<br />';
			 	$message = str_replace($order,$replace,$buildResult->error);
			 	echo $message;
			}else{
			 	echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "build_result_success"));
			 	
			 	$order   = array("\r\n", "\n", "\r");
				$replace = '<br />';
			 	$message = str_replace($order,$replace,$buildOutput);
			 	echo $message;
			}
		}
	}
}
echo $HTML->boxBottom ();
?>
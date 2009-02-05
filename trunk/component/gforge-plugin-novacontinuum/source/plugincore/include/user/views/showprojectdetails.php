<?php
echo "<h2>" . dgettext ("gforge-plugin-novacontinuum", "title_admin") . "</h2>";

$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
if($from == 'admin'){
	$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
}else{
	$menu_links [] = '/plugins/novacontinuum/index.php?group_id='.$group_id;
}
echo $HTML->subMenu ($menu_text, $menu_links);

if(isset($projectid)){

	$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
	if(isset($selectedInstance)){
		$pingRet = $selectedInstance->instance->ping();
		if($pingRet===true){
			$continuumProject = $serviceManager->getContinuumProject($projectid,$selectedInstance);
	
			echo $HTML->boxTop ( sprintf (dgettext ("gforge-plugin-novacontinuum", "project_details"), $continuumProject->name));


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
    					<th width="35%" align="right"><label for="project_scmUrl" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_scm_url");?></label></th>
    					<td><?php echo $continuumProject->scmUrl;?></td>
						</tr>
            <tr>
    					<th width="35%" align="right"><label for="project_scmTag" class="label"><?php echo dgettext ("gforge-plugin-novacontinuum", "project_details_scm_tag");?></label></th>
    					<td><?php echo $continuumProject->scmTag;?></td>
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
<?php
		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "project_details_developpers"));
		?>
		<ul width="90%">
		<?php
		
		foreach ($continuumProject->developers as $key=>$value) {
		?>
		
			<li style="display: block;width:30%;float: left;margin-left: 15px;padding: 5px;"><ul><li><b><?php echo $value->name;?></b><br /><?php echo $value->email;?></li></ul></li>
				
		<?php
		}
		?>
		</ul>
		<?php
		
		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers"));
		$roleManageProject = $serviceManager->hasRoleForGroup($group_id,'manage_project');
		if($roleManageProject){
			$menu_text = array ();
			$menu_links = array ();
			$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_notifier');
			$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=addnotifier&group_id='.$group_id.'&projectid='.$projectid;
			echo $HTML->subMenu ($menu_text, $menu_links);
		}
		
		$classifiedNotifiers = array();
		foreach ($continuumProject->notifiers as $key=>$value) {
			if(!isset($classifiedNotifiers[$value->type])){
				$classifiedNotifiers[$value->type] = array();
			}
			$classifiedNotifiers[$value->type][] = $value;
		}
		
		foreach ($classifiedNotifiers as $keyNoti=>$newArray) {
		?>
			<h4><?php echo sprintf ( dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_by") ,$keyNoti);?></h4>
			<ul width="90%">
			<?php
			
			foreach ($newArray as $key=>$value) {
				$options = array();
				if($value->sendOnSuccess==1){
					$options[]=dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_success");
				}
				if($value->sendOnFailure==1){
					$options[]=dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_failure");
				}
				if($value->sendOnWarning==1){
					$options[]=dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_warning");
				}
				if($value->sendOnError==1){
					$options[]=dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_error");
				}
				
				$notiString = "";
				if(count($options)==1){
					$notiString=$options[0];
				}else if(count($options)==2){
					$notiString=$options[0].dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_separator").$options[1];
				}else if(count($options)>2){
					
					for ($i=0;$i<count($options)-1 ;$i++ ) {
     				$notiString=$notiString.$options[$i].dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_separator");
     			}
					$notiString=$notiString.$options[count($options)-1];
				}
				
				if($value->from == 1){
					$fromStr = ' ('.dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_from_project").')';
				}else{
					$fromStr = ' ('.dgettext ("gforge-plugin-novacontinuum", "project_details_notifiers_from_user").')';
				} 
			?>
				
				<li style="display: block;width:30%;float: left;margin-left: 15px;padding: 5px;"><ul><li><b><?php echo $value->address.$fromStr;?></b>
					<?php
						if($roleManageProject){
							if($value->from == 2){
							?>
							<a href="/plugins/novacontinuum/admin/index.php?view=editnotifier&group_id=<?php echo $group_id;?>&projectid=<?php echo $projectid;?>&notifierid=<?php echo $value->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_notifier_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
							<a href="/plugins/novacontinuum/admin/index.php?view=deletenotifier&group_id=<?php echo $group_id;?>&projectid=<?php echo $projectid;?>&notifierid=<?php echo $value->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_notifier_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
							<?php
							}
						}
					
					?>
							
					<br />
					<?php echo $notiString;?>
					</li></ul></li>
					
			<?php
			
			}
			?>
			</ul>
		<?php
		}
		?>
		<?php
		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "project_details_dependencies"));
		?>
		<ul width="90%">
		<?php
		
		foreach ($continuumProject->dependencies as $key=>$value) {
		?>
		
			<li style="display: block;width:45%;float: left;margin-left: 15px;padding: 5px;"><ul><li>
				&lt;dependency&gt;<br />
      	<span style="padding-left:10px;">&lt;groupId&gt;<b><?php echo $value->groupId;?></b>&lt;/groupId&gt;<br /></span>
      	<span style="padding-left:10px;">&lt;artifactId&gt;<b><?php echo $value->artifactId;?></b>&lt;/artifactId&gt;<br /></span>
      	<span style="padding-left:10px;">&lt;version&gt;<b><?php echo $value->version;?></b>&lt;/version&gt;<br /></span>
      	&lt;/dependency&gt;
      	</li></ul></li>
		<?php
		}
		?>
		</ul>
		<?php
		}
	}
}
echo $HTML->boxBottom ();
?>
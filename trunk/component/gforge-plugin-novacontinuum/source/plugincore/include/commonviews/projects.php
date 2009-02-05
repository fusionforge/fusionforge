<?php

if($from == 'admin'){
	$urlPrefix = 'admin/';
}else{
	$urlPrefix = '';
}

if(isset($selectedInstance)){
	$pingRet = $selectedInstance->instance->ping();
	if($pingRet===true){
		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "group_admin_build_def_management"));
		$menu_text = array ();
		$menu_links = array ();
		$roleManageBuildDef = $serviceManager->hasRoleForGroup($group_id,'manage_build_def');
		if($roleManageBuildDef){
			$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_build_definition');
			$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=addbuilddef&group_id='.$group_id;
			echo $HTML->subMenu ($menu_text, $menu_links);
		}
		?>
			<ul>
				<?php
					$buildDefs = $serviceManager->getBuildDefinitionsForProjectGroup($selectedInstance);
					$nbDef = 0;
					foreach ($buildDefs as $key=>$buildDef) {
						if($buildDef->defaultForProject==1){
							$nbDef++;
						}
					}
					foreach ($buildDefs as $key=>$buildDef) {
	    			?>
	    			<li>
	    			<?php
	    				if($roleManageBuildDef){
						?>
							<a href="/plugins/novacontinuum/admin/index.php?view=editbuilddef&builddefid=<?php echo $buildDef->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_build_def_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
							<?php 
							if(($nbDef<=1) && ($buildDef->defaultForProject==1 )){
							?>
							<img src="imgs/connect.png" alt="SetDef" border='none'/>
							<?php
							} else {
								if($buildDef->defaultForProject==1){
								?>
								<a href="/plugins/novacontinuum/admin/index.php?action=unsetdefaultbuilddef&builddefid=<?php echo $buildDef->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "unset_defaut_build_def_tooltip")?>"><img src="imgs/connect.png" alt="UnSetDef" border='none'/></a>
								<?php
								}else{
							?>
							<a href="/plugins/novacontinuum/admin/index.php?action=setdefaultbuilddef&builddefid=<?php echo $buildDef->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "set_defaut_build_def_tooltip")?>"><img src="imgs/disconnect.png" alt="SetDef" border='none'/></a>
							<a href="/plugins/novacontinuum/admin/index.php?view=deletebuilddef&builddefid=<?php echo $buildDef->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_build_def_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
							<?php
								}
							}
							}
							?>
							
						<?php
	    				if($serviceManager->hasRoleForGroup($group_id,'run_build_def')){
						?>
							<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?action=buildwithbuilddef&builddefid=<?php echo $buildDef->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "run_build_def_tooltip")?>"><img src="imgs/buildnow.gif" alt="Build" border="none"/></a>
						<?php
							}
						?>
							<?php echo $buildDef->goals;?> 
							<?php echo $buildDef->arguments;?>
							<?php echo '('.$buildDef->schedule->name.')'; ?>
							</li>
	    			<?php
	    		}
				?>
			</ul>
		
		<?php
		
		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "group_admin_project_management"));
		$menu_text = array ();
		$menu_links = array ();
		$roleManageProject = $serviceManager->hasRoleForGroup($group_id,'manage_project');
		if($roleManageProject){
			$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_project');
			$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=addproject&group_id='.$group_id;
			echo $HTML->subMenu ($menu_text, $menu_links);
		}
		$projects = $serviceManager->getProjects($group_id);
		
		?>
			<ul>
			<?php
			foreach ($projects as $keyProject=>$project) {
				
	  		?>
				<li><h4><?php echo $project->name;?>
						<?php
							if($roleManageProject){
						?>
						<a href="/plugins/novacontinuum/admin/index.php?view=editproject&projectid=<?php echo $project->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_project_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
						<a href="/plugins/novacontinuum/admin/index.php?view=deleteproject&projectid=<?php echo $project->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_project_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
						<?php
			    		}
						?>
						<?php
							if($serviceManager->hasRoleForGroup($group_id,'run_project')){
						?>
						<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?action=buildproject&projectid=<?php echo $project->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "build_project_tooltip")?>"><img src="imgs/buildnow.gif" alt="Build" border="none"/></a>
						<?php
			    		}
						?>
						<?php
							if($serviceManager->hasRoleForGroup($group_id,'release_project')){
						?>
						<a href="/plugins/novacontinuum/admin/index.php?view=releaseproject&projectid=<?php echo $project->id;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "release_project_tooltip")?>"><img src="imgs/releaseproject.gif" alt="Release" border='none'/></a>
						<?php
			    		}
						?>
						</h4>
					<ul>	
						<?php
							foreach ($project->continuumProjects as $key=>$value) {
								$continuumProject = $serviceManager->getContinuumProject($value,$selectedInstance);
	  				?>
							<li><img src="imgs/<?php echo $continuumProject->getStateImage();?>" alt="state"/> 
							<?php
							if($serviceManager->hasRoleForGroup($group_id,'show_build_result')){
							?>
							<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=showbuildresults&projectid=<?php echo $value;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "show_build_results_tooltip")?>"><img src="imgs/buildhistory.gif" alt="Show" border="none"/></a>
							<?php
				    		}
							?>
							<?php
							if($serviceManager->hasRoleForGroup($group_id,'run_continuum_project')){
							?>
							<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?action=buildsubproject&projectid=<?php echo $value;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "build_sub_project_tooltip")?>"><img src="imgs/buildnow.gif" alt="Build" border="none"/></a>
							<?php
				    		}
							?>
							<?php
							if($serviceManager->hasRoleForGroup($group_id,'show_project_detail')){
							?>
							<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=showprojectdetails&projectid=<?php echo $value;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "show_project_details_tooltip")?>"><img src="imgs/workingcopy.gif" alt="ShowDetails" border="none"/></a>
							<?php
				    		}
							?>
							<?php echo $continuumProject->name.' ('.$continuumProject->groupId.'.'.$continuumProject->artifactId.'-'.$continuumProject->version.')';?></li>	
						<?php
	  					}
	  				?>					
					</ul>	
				</li>
				<?php
	  	}
	  	?>
			</ul>
		<?php
		echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "group_admin_notifiers_management"));
		$menu_text = array ();
		$menu_links = array ();
		if($roleManageProject){
			$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_notifier');
			$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=addnotifier&group_id='.$group_id;
			echo $HTML->subMenu ($menu_text, $menu_links);
		}
		?>
		<?php
			$projectGroupDetails = $serviceManager->getProjectGroupDetails($selectedInstance);
			
			$classifiedNotifiers = array();
			foreach ($projectGroupDetails->notifiers as $key=>$value) {
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
							<a href="/plugins/novacontinuum/admin/index.php?view=editnotifier&group_id=<?php echo $group_id;?>&notifierid=<?php echo $value->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_notifier_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
							<a href="/plugins/novacontinuum/admin/index.php?view=deletenotifier&group_id=<?php echo $group_id;?>&notifierid=<?php echo $value->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_notifier_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
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
			
		<br />
		<br />
		<br />
		<?php
	}
}

?>
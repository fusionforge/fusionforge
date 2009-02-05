
<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php
if($from == 'admin'){
	$urlPrefix = 'admin/';
	$groupIdAdding = '&group_id='.$group_id;
	$groupIdInit = '?group_id='.$group_id;
}else{
	$urlPrefix = 'siteAdmin/';
	$groupIdAdding = '';
	$groupIdInit = '';
}

$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php'.$groupIdInit;

echo $HTML->subMenu ($menu_text, $menu_links);

$instance = $serviceManager->getContinuumInstance($instanceid);
?>

<?php 

	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "manage_schedule_instance"));
	
	
	$menu_text = array ();
	$menu_links = array ();
	$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_schedule_instance');
	$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view=addschedule&instanceid='.$instanceid.$groupIdAdding;
	echo $HTML->subMenu ($menu_text, $menu_links);
?>
<p />
<center>
	<table border="0" cellspacing="0" cellpadding="7" style="border-collapse: collapse; border-bottom:thin solid black;">
		<tr style="border:thin solid black;">
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_schedule_instance_col_name')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_schedule_instance_col_desc')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_schedule_instance_col_waittime')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_schedule_instance_col_cron')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_schedule_instance_col_maxtime')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_schedule_instance_col_activated')?></th>
    	<th style="border-left:thin solid black;border-right:thin solid black;"></th>
    </tr>
      	  	  	  	  	
<?php 
	$schedules = $serviceManager->getSchedulesForInstance($instance);
	
	foreach ($schedules as $key=>$schedule) {
 		?>
 		<tr>
			<td style="border-left:thin solid black;"><?php echo $schedule->name;?></td>
			<td style="border-left:thin solid black;"><?php echo $schedule->description;?></td>
			<td style="border-left:thin solid black;" width="40px" align="right"><?php echo $schedule->delay;?></td>
			<td style="border-left:thin solid black;" align="right"><?php echo $schedule->cronExpression;?></td>
			<td style="border-left:thin solid black;" align="right"><?php echo $schedule->maxJobExecutionTime;?></td> 
			<td style="border-left:thin solid black;" width="20px" align="center">
			<?php 
			if($schedule->active == 1){ ?>
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=<?php echo $view;?>&action=disableschedule&scheduleid=<?php echo $schedule->id;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "disable_schedule_tooltip")?>"><img src="imgs/lock_open.png" alt="Disable" border='none'/></a>
			<?php } else {?>
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=<?php echo $view;?>&action=enableschedule&scheduleid=<?php echo $schedule->id;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "enable_schedule_tooltip")?>"><img src="imgs/lock.png" alt="Enable" border='none'/></a>
			<?php }?>
			</td>
			<td style="border-left:thin solid black;border-right:thin solid black;" width="40px" align="center">
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=editschedule&scheduleid=<?php echo $schedule->id;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_schedule_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=deleteschedule&scheduleid=<?php echo $schedule->id;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_schedule_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
			</td>
		</tr> 	
 		<?php
 	}
?>
	</table>
</center>

<?php 

	echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "manage_installation"));
	
	
	$menu_text = array ();
	$menu_links = array ();
	$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_installation_tool');
	$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view=addinstallation&newTool=true&instanceid='.$instanceid.$groupIdAdding;
	$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_installation_var');
	$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view=addinstallation&instanceid='.$instanceid.$groupIdAdding;
	
	echo $HTML->subMenu ($menu_text, $menu_links);
?>
<p />
<center>
	<table border="0" cellspacing="0" cellpadding="7" style="border-collapse: collapse; border-bottom:thin solid black;">
		<tr style="border:thin solid black;">
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_installation_col_name')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_installation_col_var_name')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_installation_col_type')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_installation_col_var_value')?></th>
    	<th style="border-left:thin solid black;border-right:thin solid black;"></th>
    </tr>
      	  	  	  	  	
<?php 
	$installations = $serviceManager->getInstallationsForInstance($instance);
	
	foreach ($installations as $key=>$installation) {
 		?>
 		<tr>
			<td style="border-left:thin solid black;"><?php echo $installation->name;?></td>
			<td style="border-left:thin solid black;"><?php echo $installation->varName;?></td>
			<td style="border-left:thin solid black;" width="40px"><?php echo $installation->type;?></td>
			<td style="border-left:thin solid black;"><?php echo $installation->varValue;?></td>
			<td style="border-left:thin solid black;border-right:thin solid black;" width="40px" align="center">
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=editinstallation&installationid=<?php echo $installation->installationId;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_installation_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=deleteinstallation&installationid=<?php echo $installation->installationId;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_installation_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
			</td>
		</tr> 	
 		<?php
 	}
?>
	</table>
</center>

<?php 

	echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "manage_profiles"));
	
	
	$menu_text = array ();
	$menu_links = array ();
	$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_profile');
	$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view=addprofile&instanceid='.$instanceid.$groupIdAdding;
	
	echo $HTML->subMenu ($menu_text, $menu_links);
?>
<p />
<center>
	<table border="0" cellspacing="0" cellpadding="7" style="border-collapse: collapse; border-bottom:thin solid black;">
		<tr style="border:thin solid black;">
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_profile_col_name')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_profile_col_jdk')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_profile_col_builder')?></th>
    	<th style="border-left:thin solid black;"><?php echo dgettext ('gforge-plugin-novacontinuum', 'manage_profile_col_env_var')?></th>
    	<th style="border-left:thin solid black;border-right:thin solid black;"></th>
    </tr>
      	  	  	  	  	
<?php 
	$profiles = $serviceManager->getProfilesForInstance($instance);
	
	foreach ($profiles as $key=>$profile) {
 		?>
 		<tr>
			<td style="border-left:thin solid black;"><?php echo $profile->name;?></td>
			<td style="border-left:thin solid black;"><?php if(isset($profile->jdk)){echo $profile->jdk->name;}?></td>
			<td style="border-left:thin solid black;" width="40px"><?php if(isset($profile->builder)){echo $profile->builder->name;}?></td>
			<td style="border-left:thin solid black;"><ul>
			<?php
				foreach ($profile->environmentVariables as $key=>$value) {
    			?>
    				<li><?php echo $value->name;?></li>
    			<?php
    		}
			?>
			</ul></td>
			<td style="border-left:thin solid black;border-right:thin solid black;" width="40px" align="center">
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=editprofile&profileid=<?php echo $profile->id;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_profile_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
				<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=deleteprofile&profileid=<?php echo $profile->id;?>&instanceid=<?php echo $instanceid;?><?php echo $groupIdAdding;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_profile_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
			</td>
		</tr> 	
 		<?php
 	}
?>
	</table>
</center>
<?php
echo $HTML->boxBottom ();
?>
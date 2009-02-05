<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php

if(isset($projectid)){
	$viewUrl = 'showprojectdetails';
}else{
	$viewUrl = 'default';
}
if(isset($projectid)){
	$projectidAdding = '&projectid='.$projectid;
}else{
	$projectidAdding = '';
}
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?view='.$viewUrl.'&group_id='.$group_id.$projectidAdding;
	
echo $HTML->subMenu ($menu_text, $menu_links);
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "delete_notifier"));
?><p />
<?php
if(isset($notifierid)){

	$selectedInstance = $serviceManager->getInstanceForProjects($group_id);

	if(isset($projectid)){	
		$notifierToEdit = $serviceManager->getNotifier($selectedInstance->instance,$projectid,$notifierid);
	}else{
		$notifierToEdit = $serviceManager->getGroupNotifier($selectedInstance->instance,$selectedInstance->continuumProjectGroupId,$notifierid);
	}

	
	echo sprintf ( dgettext ('gforge-plugin-novacontinuum', 'confirm_delete_notifier') , $notifierToEdit->address);
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="deletenotifier">
	<input type="hidden" name="view" value="<?php echo $viewUrl;?>">
	<input type="hidden" name="notifierid" value="<?php echo $notifierid;?>">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<?php
		if(isset($projectid)){?>
	<input type="hidden" name="projectid" value="<?php echo $projectid;?>">
	<?php
		}
	?>
	<input type="submit" name="deleteNotifier" value="<? echo dgettext ("gforge-plugin-novacontinuum", "confirm_delete"); ?>" />
</form>

<?php	
}
echo $HTML->boxBottom ();
?>
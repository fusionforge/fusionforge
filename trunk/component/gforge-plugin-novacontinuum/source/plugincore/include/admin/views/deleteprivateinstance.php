<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "delete_private_instance"));
?><p />
<?php

	$instanceToEdit = $serviceManager->getPrivateInstanceForProjects($group_id);
	if(isset($instanceToEdit)){
	
	echo sprintf ( dgettext ('gforge-plugin-novacontinuum', 'confirm_delete_private_instance') , $instanceToEdit->name);
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="deleteinstance">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<input type="submit" name="deleteInstance" value="<? echo dgettext ("gforge-plugin-novacontinuum", "confirm_delete"); ?>" />
</form>

<?php	
}
echo $HTML->boxBottom ();
?>

<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "delete_build_def"));
?><p />
<?php

	$buildDefToEdit = $serviceManager->getbuilddef($builddefid,$group_id);
	if(isset($buildDefToEdit)){
	
	echo sprintf ( dgettext ('gforge-plugin-novacontinuum', 'confirm_delete_build_def' ) , $buildDefToEdit->goals.' '.$buildDefToEdit->arguments.' ('.$buildDefToEdit->schedule->name.')');
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="deletebuilddef">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<input type="hidden" name="builddefid" value="<?php echo $builddefid;?>">
	<input type="submit" name="deletebuilddef" value="<? echo dgettext ("gforge-plugin-novacontinuum", "confirm_delete"); ?>" />
</form>

<?php	
}
echo $HTML->boxBottom ();
?>

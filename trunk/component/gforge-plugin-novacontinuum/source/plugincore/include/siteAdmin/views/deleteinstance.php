<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/siteAdmin/index.php';
	
echo $HTML->subMenu ($menu_text, $menu_links);
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "delete_instance"));
?><p />
<?php
if(isset($instanceid)){
	$instanceToEdit = $serviceManager->getContinuumInstance($instanceid);
	
	
	echo sprintf ( dgettext ('gforge-plugin-novacontinuum', 'confirm_delete_instance') , $instanceToEdit->name);
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="deleteinstance">
	<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<input type="submit" name="deleteInstance" value="<? echo dgettext ("gforge-plugin-novacontinuum", "confirm_delete"); ?>" />
</form>

<?php	
}
echo $HTML->boxBottom ();
?>
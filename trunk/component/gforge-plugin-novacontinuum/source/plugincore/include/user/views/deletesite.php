<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_admin"); ?><h2>

<?php
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
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "delete_site_maven"));
?><p />
<?php

	if(isset($siteid)){
	
	echo sprintf ( dgettext ('gforge-plugin-novacontinuum', 'confirm_delete_site_maven') , $serviceManager->formatSiteDate($siteid));
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="deletesite">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<input type="hidden" name="siteid" value="<?php echo $siteid;?>">
	<input type="submit" name="deletebuilddef" value="<? echo dgettext ("gforge-plugin-novacontinuum", "confirm_delete"); ?>" />
</form>

<?php	
}
echo $HTML->boxBottom ();
?>

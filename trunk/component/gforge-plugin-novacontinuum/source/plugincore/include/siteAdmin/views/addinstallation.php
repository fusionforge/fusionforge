<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php
if($from == 'admin'){
	$urlPrefix = 'admin/';
	$groupIdAdding = '&group_id='.$group_id;
	$viewUrl = 'optionprivateinstance';
}else{
	$urlPrefix = 'siteAdmin/';
	$groupIdAdding = '';
	$viewUrl = 'optioninstance';
}

if((isset($installationToEdit))&&(isset($installationToEdit->type))&&($installationToEdit->type=='jdk')){
	$tool = true;
}else if((isset($installationToEdit))&&(isset($installationToEdit->type))&&($installationToEdit->type=='maven2')){
	$tool = true;
}else if(isset($newTool)){
	$tool = true;
}else {
	$tool = false;
}
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view='.$viewUrl.'&instanceid='.$instanceid.$groupIdAdding;
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 
if($view=='addinstallation'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_installation"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_installation"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="view" value="<?php echo $viewUrl;?>">
	<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<?php
		if(isset($installationToEdit)){?>
	<input type="hidden" name="installationid" value="<?php echo $installationToEdit->installationId;?>">
	<?php
		}
	?>
	<?php
		if($from == 'admin'){?>
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">	
	<?php
		}
	?>
	
	<table>
		<tr valign="top">
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_installation_name");?></td>
			<td><input size="40" maxlength="128" type="text" name="name" <?php if(isset($installationToEdit)){echo 'value="'.$installationToEdit->name.'"';}?>/></td>
		</tr>
		<?php
			if($tool == false){
		?>
		<tr valign="top">
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_installation_var_name");?><input type="hidden" name="type" value="envvar"></td>
			<td><input size="40" maxlength="128" type="text" name="varName" <?php if(isset($installationToEdit)){echo 'value="'.$installationToEdit->varName.'"';}?>/></td>
		</tr>
		
		<?php
			} else{
		?>
		<tr>
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_installation_type");?></td>
			<td>
				<select name="type">
					<option value="jdk"<?php if((isset($installationToEdit))&&(isset($installationToEdit->type))&&($installationToEdit->type=='jdk')){echo ' selected="selected" ';}?>>Jdk</option>
					<option value="maven2"<?php if((isset($installationToEdit))&&(isset($installationToEdit->type))&&($installationToEdit->type=='maven2')){echo ' selected="selected" ';}?>>Maven2</option>
				</select>
			</td>
		</tr>
		<?php
			}
		?>
		<tr valign="top">
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_installation_var_value");?></td>
			<td><input size="40" type="text" name="varValue" <?php if(isset($installationToEdit)){echo 'value="'.$installationToEdit->varValue.'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td colspan="2"></td>
			<td><input type="submit" name="addInstallation" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addinstallation'?"submit_add_installation":"submit_edit_installation")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
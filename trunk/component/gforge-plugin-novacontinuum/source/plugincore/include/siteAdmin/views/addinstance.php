
<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');

if($view=='addprivateinstance' ||$view=='editprivateinstance'){
$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
}else{
$menu_links [] = '/plugins/novacontinuum/siteAdmin/index.php';
}
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 
if($view=='addinstance' ||$view=='addprivateinstance'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_instance"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_instance"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<?php if(isset($instanceid)){?>
		<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<?php }?>
	<?php if($view=='addprivateinstance'||$view=='editprivateinstance'){?>
		<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<?php }?>
	<?php if(isset($instanceToEdit)){?>
		<input type="hidden" name="instanceGroupId" value="<?php echo $instanceToEdit->groupId;?>">
	<?php }?>
	<table>
		<?php if($view!='addprivateinstance' && $view!='editprivateinstance'){?>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_name");?></td>
			<td><input size="40" maxlength="128" type="text" name="name" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->name.'"';}?>/></td>
		</tr>
		<?php }?>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_url");?></td>
			<td><input size="40" type="text" name="url" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->url.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_user");?></td>
			<td><input size="40" maxlength="128" type="text" name="user" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->user.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_password");?></td>
			<td><input size="40" maxlength="128" type="password" name="password" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->password.'"';}?>/></td>
		</tr>
		<?php if($view!='addprivateinstance' && $view!='editprivateinstance'){?>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_maxUse");?></td>
			<td><input size="40" maxlength="128" type="text" name="maxUse" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->maxUse.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_isEnabled");?></td>
			<td><input type="checkbox" name="isEnabled" <?php if(isset($instanceToEdit)&&$instanceToEdit->isEnabled){echo 'checked="true"';}?>/></td>
		</tr>
		<?php }?>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_proxy");?></td>
			<td>
				<select name="proxy">
					<option value="-1"><? echo dgettext ("gforge-plugin-novacontinuum", "form_add_instance_proxy_none");?></option>
				<?php
					$instances = $serviceManager->getAllHttpProxies();
					
					foreach ($instances as $instance) {
				?><option value="<?php echo $instance->id;?>"<?php if((isset($instanceToEdit))&&(isset($instanceToEdit->httpProxy))&&($instanceToEdit->httpProxy->id==$instance->id)){echo ' selected="selected" ';}?>><?php echo $instance->name;?></option>
				<?php
					}
				?>
				</select>
			
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="addInstance" value="<?php echo dgettext ("gforge-plugin-novacontinuum", ($view=='addinstance'||$view=='addprivateinstance'?"submit_add_instance":"submit_edit_instance")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
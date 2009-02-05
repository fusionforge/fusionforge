
<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/siteAdmin/index.php';
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 
if($view=='addproxy'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_proxy"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_proxy"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<?php if(isset($instanceid)){?>
		<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<?php }?>
	
	<table>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_proxy_name");?></td>
			<td><input size="40" maxlength="128" type="text" name="name" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->name.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_proxy_host");?></td>
			<td><input size="40" type="text" name="host" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->host.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_proxy_port");?></td>
			<td><input size="40" type="text" name="port" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->port.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_proxy_user");?></td>
			<td><input size="40" maxlength="128" type="text" name="userName" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->userName.'"';}?>/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_proxy_password");?></td>
			<td><input size="40" maxlength="128" type="password" name="password" <?php if(isset($instanceToEdit)){echo 'value="'.$instanceToEdit->password.'"';}?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="addInstance" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addproxy'?"submit_add_proxy":"submit_edit_proxy")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
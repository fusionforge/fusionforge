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
?>

<?php 
if($view=='addschedule'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_notifier"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_notifier"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="view" value="<?php echo $viewUrl;?>">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<?php
		if(isset($projectid)){?>
	<input type="hidden" name="projectid" value="<?php echo $projectid;?>">
	<?php
		}
	?>
	<?php
		if(isset($notifierToEdit)){?>
	<input type="hidden" name="notifierid" value="<?php echo $notifierToEdit->id;?>">
	<?php
		}
	?>
	
	<table>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_notifier_address");?></td>
			<td><input size="40" maxlength="128" type="text" name="address" <?php if(isset($notifierToEdit)){echo 'value="'.$notifierToEdit->address.'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="sendOnSuccess" <?php if(isset($notifierToEdit)&& $notifierToEdit->sendOnSuccess==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_notifier_sendOnSuccess");?></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="sendOnFailure" <?php if(isset($notifierToEdit)&& $notifierToEdit->sendOnFailure==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_notifier_sendOnFailure");?></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="sendOnError" <?php if(isset($notifierToEdit)&& $notifierToEdit->sendOnError==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_notifier_sendOnError");?></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="sendOnWarning" <?php if(isset($notifierToEdit)&& $notifierToEdit->sendOnWarning==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_notifier_sendOnWarning");?></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="enabled" <?php if(isset($notifierToEdit)&& $notifierToEdit->enabled==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_notifier_enabled");?></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="submit" name="addNotifier" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addnotifier'?"submit_add_notifier":"submit_edit_notifier")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>

<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 
if($view=='addproject'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_build_definition"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_build_definition"));
} 
?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<?php if(isset($builddefid)){?>
		<input type="hidden" name="builddefid" value="<?php echo $builddefid;?>">
	<?php }?>
	
	<table>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_goals");?></td>
			<td><input size="40" maxlength="128" type="text" name="goals" value="<?php if(isset($buildDefToEdit)){echo $buildDefToEdit->goals;}?>"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_arguments");?></td>
			<td><input size="40" type="text" name="arguments" value="<?php if(isset($buildDefToEdit)){echo $buildDefToEdit->arguments;}?>"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_build_file");?></td>
			<td><input size="40" type="text" name="buildFile" value="<?php if(isset($buildDefToEdit)){echo $buildDefToEdit->buildFile;}else { echo 'pom.xml';}?>"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_schedule");?></td>
			<td>
				<select name="schedule">
				<?php
					$schedules = $serviceManager->getSchedules( $group_id );
					
					foreach ($schedules as $schedule) {
				?><option value="<?php echo $schedule->id;?>"<?php if((isset($buildDefToEdit))&&(isset($buildDefToEdit->schedule))&&($buildDefToEdit->schedule->id==$schedule->id)){echo ' selected="selected" ';}?>><?php echo $schedule->name;?></option>
				<?php
					}
				?>
				</select>
			
			</td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_profile");?></td>
			<td>
				<select name="profile">
					<option value="-1"<?php if((!isset($buildDefToEdit))||(!isset($buildDefToEdit->profile))){echo ' selected="selected" ';}?>></option>
				<?php
					$profiles = $serviceManager->getProfiles($group_id);
					
					foreach ($profiles as $profile) {
				?><option value="<?php echo $profile->id;?>"<?php if((isset($buildDefToEdit))&&(isset($buildDefToEdit->profile))&&($buildDefToEdit->profile->id==$profile->id)){echo ' selected="selected" ';}?>><?php echo $profile->name;?></option>
				<?php
					}
				?>
				</select>
			
			</td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="buildFresh" <?php if(isset($buildDefToEdit)&& $buildDefToEdit->buildFresh==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_build_fresh");?></td>
		</tr>
		<tr valign="top">
			<td></td>
			<td><input type="checkbox" name="alwaysBuild" <?php if(isset($buildDefToEdit)&& $buildDefToEdit->alwaysBuild==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_build_def_always_build");?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="addBuildDef" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addbuilddef'?"submit_add_build_def":"submit_edit_build_def")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
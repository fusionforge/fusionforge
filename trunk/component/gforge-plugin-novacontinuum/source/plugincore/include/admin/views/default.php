<?php
echo "<h2>" . dgettext ("gforge-plugin-novacontinuum", "title_admin") . "</h2>";

$errorManager =& ErrorManager::getInstance();
$errors = $errorManager->getErrors();
foreach ($errors as $error) {
echo "<span style='color:red; font-style:italic; font-size: larger; font-weight:bolder'>".$error."</span><br />";
}
echo "<br />";
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "group_admin_instance_management"));

$configuration = $serviceManager->getConfiguration();
$privateInstance = $serviceManager->getPrivateInstanceForProjects($group_id);
	
if($serviceManager->hasRoleForGroup($group_id,'manage_private_instance')){
	if($configuration->values['allowPrivateInstance'] == '1' || isset($privateInstance)){
	$menu_text = array ();
	$menu_links = array ();
	if(isset($privateInstance)){
		$pingRetPrivate = $privateInstance->ping(); 
		if($pingRetPrivate===true){
			$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'option_private_instance');
			$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=optionprivateinstance&group_id='.$group_id;
		}
		$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'edit_private_instance');
		$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=editprivateinstance&group_id='.$group_id;
		
		$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'delete_private_instance');
		$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=deleteprivateinstance&group_id='.$group_id;
		
	}else{
		$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_private_instance');
		$menu_links [] = '/plugins/novacontinuum/admin/index.php?view=addprivateinstance&group_id='.$group_id;
	}
	$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'download_private_instance');
	$menu_links [] = '#';	
	echo $HTML->subMenu ($menu_text, $menu_links);
	}
}
$selectedInstance = $serviceManager->getInstanceForProjects($group_id);

?>
<p />
<form name="frmInstances" action="<?php echo $PHP_SELF; ?>" method="post">
<?php
if($serviceManager->hasRoleForGroup($group_id,'select_instance')){
?>

		<input type="hidden" name="action" value="selectInstance">
		<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
		<select name="instance" onChange="frmInstances.submit();">
			<option value="-1"></option>
		<?php
		if(isset($privateInstance)){
			$pingRetPrivate = $privateInstance->ping(); 
			if(($pingRetPrivate===true) ||(isset($selectedInstance)&&$selectedInstance->instance->id==$privateInstance->id)){ ?>
				<option value="<?php echo $privateInstance->id;?>" <?php if(isset($selectedInstance)&&$selectedInstance->instance->id==$privateInstance->id){echo ' selected="selected" ';}?>>[<?php echo $privateInstance->name;?>]</option>
		<?php
			}
		}
		$instances = $serviceManager->getAllUsableContinuumInstances($group_id);
		
		foreach ($instances as $instance) {
		?>
			<option value="<?php echo $instance->id;?>" <?php if(isset($selectedInstance)&&$selectedInstance->instance->id==$instance->id){echo ' selected="selected" ';}?>><?php echo $instance->name;?></option>		
		<?php
		}?>
		</select>
		

<?php
			}else{
				if(isset($selectedInstance)){
?>
					<h3 style="display:inline;"><?php echo $selectedInstance->instance->name;?></h3>
<?php
				}
			}
?>
	<?php
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping(); 
			if($pingRet===true){ ?>
				<img src="imgs/icon_success_sml.gif" alt="status" border='none'/>
		<?php }else{?>
				<a href="javascript:alert('<?php echo $pingRet;?>');"><img src="imgs/icon_error_sml.gif" alt="status" border='none'/></a>
		<?php 
			}
			
			if(isset($privateInstance)&&$privateInstance->id==$selectedInstance->instance->id){?>
			<img src="imgs/rosette.png" alt="label" border='none'/>
			<?php
			}
		} 
		?>
	</form>
<br />
<br />


<?php
	require_once (dirname(__FILE__).'/../../commonviews/mavensites.php');
	require_once (dirname(__FILE__).'/../../commonviews/projects.php');
?>
<p />

<?php

if ($serviceManager->hasRoleForGroup($group_id,'manage_role'))
{
echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "group_admin_role_management"));

$selectedRoles = $serviceManager->getRoles($group_id);
?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="action" value="updateRoles">
<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
<table border="0" cellspacing="0" cellpadding="7" style="border-collapse: collapse">
	<tr style="border:thin solid black;">
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_role");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_manage_roles");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_all");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_none");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_manage_private_instance");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_select_instance");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_manage_build_def");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_run_build_def");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_read_maven_site");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_write_maven_site");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_release_project");?></th>
		<th style="border-left:thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_manage_project");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_run_project");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_run_continuum_project");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_show_build_result");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_show_project_detail");?></th>
		<th><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_col_view");?></th>
	</tr>
	<?php
		
		$arrayRoles = $serviceManager->getGForgeRoles($group_id);
		
		foreach ( $arrayRoles as $key=>$value) {?>
  		<tr style="border-left:thin solid black;border-right:thin solid black;">
  			<td><?php echo $value;?></td>
  			<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_role[<?php echo $key;?>]" <?php if(isset($selectedRoles['manage_role'][$key])){echo 'checked="true"';}?>/></td>
  			<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="all[<?php echo $key;?>]"/></td>
  			<td align="center"><input type="checkbox" name="none[<?php echo $key;?>]" /></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_private_instance[<?php echo $key;?>]" <?php if(isset($selectedRoles['manage_private_instance'][$key])){echo 'checked="true"';}?>/></td>
  			<td align="center"><input type="checkbox" name="select_instance[<?php echo $key;?>]" <?php if(isset($selectedRoles['select_instance'][$key])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_build_def[<?php echo $key;?>]" <?php if(isset($selectedRoles['manage_build_def'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="run_build_def[<?php echo $key;?>]" <?php if(isset($selectedRoles['run_build_def'][$key])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="read_maven_site[<?php echo $key;?>]" <?php if(isset($selectedRoles['read_maven_site'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="write_maven_site[<?php echo $key;?>]" <?php if(isset($selectedRoles['write_maven_site'][$key])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="release_project[<?php echo $key;?>]" <?php if(isset($selectedRoles['release_project'][$key])){echo 'checked="true"';}?>/></td>
        <td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_project[<?php echo $key;?>]" <?php if(isset($selectedRoles['manage_project'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="run_project[<?php echo $key;?>]" <?php if(isset($selectedRoles['run_project'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="run_continuum_project[<?php echo $key;?>]" <?php if(isset($selectedRoles['run_continuum_project'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="show_build_result[<?php echo $key;?>]" <?php if(isset($selectedRoles['show_build_result'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="show_project_detail[<?php echo $key;?>]" <?php if(isset($selectedRoles['show_project_detail'][$key])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="view_access[<?php echo $key;?>]" <?php if(isset($selectedRoles['view_access'][$key])){echo 'checked="true"';}?>/></td>	
  		</tr>
  	<?php
 		}
	?>
			<tr style="border-left:thin solid black;border-right:thin solid black;">
				<td><?php echo dgettext ("gforge-plugin-novacontinuum", "group_admin_lbl_guest");?></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_role[-1]" <?php if(isset($selectedRoles['manage_role'][-1])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="all[-1]"/></td>
				<td align="center"><input type="checkbox" name="none[-1]" /></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_private_instance[-1]" <?php if(isset($selectedRoles['manage_private_instance'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="select_instance[-1]" <?php if(isset($selectedRoles['select_instance'][-1])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_build_def[-1]" <?php if(isset($selectedRoles['manage_build_def'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="run_build_def[-1]" <?php if(isset($selectedRoles['run_build_def'][-1])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="read_maven_site[-1]" <?php if(isset($selectedRoles['read_maven_site'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="write_maven_site[-1]" <?php if(isset($selectedRoles['write_maven_site'][-1])){echo 'checked="true"';}?>/></td>
				<td style="border-left:thin solid black;" align="center"><input type="checkbox" name="release_project[-1]" <?php if(isset($selectedRoles['release_project'][-1])){echo 'checked="true"';}?>/></td>
        <td style="border-left:thin solid black;" align="center"><input type="checkbox" name="manage_project[-1]" <?php if(isset($selectedRoles['manage_project'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="run_project[-1]" <?php if(isset($selectedRoles['run_project'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="run_continuum_project[-1]" <?php if(isset($selectedRoles['run_continuum_project'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="show_build_result[-1]" <?php if(isset($selectedRoles['show_build_result'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="show_project_detail[-1]" <?php if(isset($selectedRoles['show_project_detail'][-1])){echo 'checked="true"';}?>/></td>
				<td align="center"><input type="checkbox" name="view_access[-1]" <?php if(isset($selectedRoles['view_access'][-1])){echo 'checked="true"';}?>/></td>	
			</tr>
			<tr>
				<td style="border-top:thin solid black;" colspan="16" align="left"><input type="submit" name="updateRoles" value="<?php echo dgettext ("gforge-plugin-novacontinuum", "submit_update_roles"); ?>" /></td>
			</tr>
</table>
</form>
<?php
}

echo $HTML->boxBottom ();
?>


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
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_project"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_project"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<?php if(isset($projectid)){?>
		<input type="hidden" name="projectid" value="<?php echo $projectid;?>">
	<?php }?>
	
	<table>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_project_name");?></td>
			<td><input size="40" maxlength="128" type="text" name="name" value="<?php if(isset($projectToEdit)){echo $projectToEdit->name;}else{echo $group->getPublicName();}?>"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_project_url");?></td>
			<td><input size="40" type="text" name="url" value="<?php if(isset($projectToEdit)){echo $projectToEdit->url;}else{echo 'http://'.$group->getSCMBox().'/svn/'.$group->getUnixName().'/pom.xml';}?>"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_project_user");?></td>
			<td><?php
				$userForge=session_get_user();
			?><input size="40" maxlength="128" type="text" name="username" value="<?php if(isset($projectToEdit)){echo $projectToEdit->userName;}else{ echo $userForge->getUnixName();}?>"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_project_password");?></td>
			<td><input size="40" maxlength="128" type="password" name="password" <?php if(isset($projectToEdit)){echo 'value="'.$projectToEdit->pwd.'"';}?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="addProject" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addproject'?"submit_add_project":"submit_edit_project")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
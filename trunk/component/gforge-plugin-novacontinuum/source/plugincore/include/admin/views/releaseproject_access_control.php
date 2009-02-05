
<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 


	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "release_project_access_control"));
	$errorManager =& ErrorManager::getInstance();
  $errors = $errorManager->getErrors();
  foreach ($errors as $error) {
  echo "<span style='color:red; font-style:italic; font-size: larger; font-weight:bolder'>". $error."</span><br />";
  }
  echo "<br />";
	
?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="view" value="releaseproject">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<input type="hidden" name="projectid" value="<?php echo $projectid;?>">
	
	
	<table>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_scmuser");?></td>
			<td><?php
				$userForge=session_get_user();
			?><input size="40" maxlength="128" type="text" name="username" value="<?php echo $userForge->getUnixName();?>" readonly="readonly"/></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_scmpassword");?></td>
			<td><input size="40" maxlength="128" type="password" name="password" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="addProject" value="<? echo dgettext ("gforge-plugin-novacontinuum", "submit_release_project_access_control"); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
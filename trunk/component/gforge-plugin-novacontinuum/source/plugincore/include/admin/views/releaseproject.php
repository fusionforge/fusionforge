
<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_admin"); ?><h2>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_admin');
$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 


	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "release_project"));
	
	
	
	$errorManager =& ErrorManager::getInstance();
  $errors = $errorManager->getErrors();
  foreach ($errors as $error) {
  echo "<span style='color:red; font-style:italic; font-size: larger; font-weight:bolder'>". $error."</span><br />";
  }
  echo "<br />";
  
?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">
	<input type="hidden" name="projectid" value="<?php echo $projectid;?>">
	<table>
	  <tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_tag_url");?></td>
			<td><?php echo $serviceManager->getTagURL($projectid);?></td>
		</tr>
		<tr valign="top">
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_add_tag_to_project_list");?></td>
			<td><input type="checkbox" name="addtag" /></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_tag");?></td>
			<td><?php echo $serviceManager->getReleaseVersion($projectRelease->version);?></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_new_version");?></td>
			<td><input size="40" type="text" name="newversion" value="<?php echo $serviceManager->getNewVersion($projectRelease->version);?>"/></td>
		</tr>
		<tr>
		  <td colspan="2" style="border-top : thin black solid;"></td>
		</tr>
	  <?php printModuleInfo($projectRelease);?>
		
		<tr>
			<td></td>
			<td><input type="submit" name="addProject" value="<? echo dgettext ("gforge-plugin-novacontinuum", "submit_release_project"); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();


function printModuleInfo($moduleRelease){
  global $Language;
?>
    <tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_group_id");?></td>
			<td><?php echo $moduleRelease->groupId;?></td>
		</tr>
    <tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_artifact_id");?></td>
			<td><?php echo $moduleRelease->artifactId;?></td>
		</tr>
		<tr>
			<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_version");?></td>
			<td><?php echo $moduleRelease->version;?></td>
		</tr>
		<tr>
		  <td colspan="2" style="border-top : thin black solid;"></td>
		</tr>
		<?php
    if(count($moduleRelease->modules)>0){
    ?>
		<tr>
		  <td valign = "top"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_release_project_modules");?></td>
      <td><table><?php
      foreach ($moduleRelease->modules as $module) {
      	printModuleInfo($module);
      }
      ?></table></td>
    </tr>
<?php
    }
}
?>
<?php
echo "<h2>" . dgettext ("gforge-plugin-novacontinuum", "title") . "</h2>";

$selectedRoles = $serviceManager->hasRoleForGroup($group_id,'manage_private_instance');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'select_instance');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'manage_build_def');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'run_build_def');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'manage_project');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'run_project');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'run_continuum_project');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'show_build_result');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'show_project_detail');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'manage_role');

if($selectedRoles){
	$menu_text = array ();
	$menu_links = array ();
	$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'admin_link');
	$menu_links [] = '/plugins/novacontinuum/admin/index.php?group_id='.$group_id;
	echo $HTML->subMenu ($menu_text, $menu_links);
	
}
$selectedInstance = $serviceManager->getInstanceForProjects($group_id);

if($serviceManager->hasRoleForGroup($group_id,'view_access')){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "selected_instance"));
	
	if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping(); 
			if($pingRet===true){
				echo dgettext ("gforge-plugin-novacontinuum", "reachable_instance");
			}else{
				echo dgettext ("gforge-plugin-novacontinuum", "not_reachable_instance");
			}
	}else{
		echo dgettext ("gforge-plugin-novacontinuum", "not_selected_instance");
	}
	?>

	<?php
		require_once (dirname(__FILE__).'/../../commonviews/mavensites.php');
		require_once (dirname(__FILE__).'/../../commonviews/projects.php');
	?>
	<?php
	echo $HTML->boxBottom ();
}
?>
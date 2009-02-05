<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
?>
<?php
/**
 * GForge Plugin novacontinuum Class
 *
 * This file is part of gforge-plugin-novacontinuum
 *
 */

require_once ("common/include/Error.class.php");
require_once ("common/include/Plugin.class.php");
require_once ("common/novaforge/log.php");

class NovaContinuumPlugin extends Plugin
{

   function NovaContinuumPlugin ()
   {
      $this->Plugin ();
      $this->name = "novacontinuum";
      $this->hooks [] = "groupmenu";
      $this->hooks [] = "groupisactivecheckbox";
      $this->hooks [] = "groupisactivecheckboxpost";
      $this->hooks [] = "project_admin_plugins";
		  $this->hooks [] = "site_admin_option_hook";
		  $this->hooks [] = "mypage";
   }

   function activatePlugin (&$Group)
   {
      $retour = true;
      
      return $retour;
   }

   function desactivatePlugin (&$Group)
   {
   }

   /**
    * The function to be called for a Hook
    *
    * @param    String  $hookname  The name of the hookname that has been happened
    * @param    String  $params    The params of the Hook
    *
    */
   function CallHook ($hookname, $params)
   {
   	global
			$Language,
			$HTML,
			$G_SESSION;

		switch ($hookname)
		{
      case "groupmenu" :
				$group = &group_get_object ($params ["group"]);
				if ((isset ($group) !== false)
				    &&  (is_object ($group) == true)
				    &&  ($group->isError () == false)
				    &&  ($group->isProject () == true))
				{
					if ($group->usesPlugin ($this->name) == true)
					{
						$params ['DIRS'] [] = "/plugins/novacontinuum/?group_id=" . $group->getID ();
						$params ['TITLES'] [] = dgettext ("gforge-plugin-novacontinuum", "tab_title");
					}
					if ($params ["toptab"] == $this->name)
					{
						$params ["selected"] = count ($params ["TITLES"]) - 1;
					}
				}
				break;
			case "groupisactivecheckbox" :
				$group = &group_get_object ($params ["group"]);
				echo "<tr><td><input type=\"checkbox\" name=\"use_novacontinuum\" value=\"1\"";
				if ($group->usesPlugin ($this->name) == true)
				{
					echo " checked";
				}
				echo "></td><td><strong>". dgettext ("gforge-plugin-novacontinuum", "use_novacontinuum") ."</strong></td></tr>\n";
				break;
			case "groupisactivecheckboxpost" :
				$group = &group_get_object ($params ["group"]);
				if (getIntFromRequest ("use_novacontinuum") == 1)
				{
					$group->setPluginUse ($this->name, true);
				}
				else
				{
					$group->setPluginUse ($this->name, false);
				}
				break;
			case "project_admin_plugins" :
				$group = &group_get_object ($params ["group_id"]);
				if ((isset ($group) !== false)
				    &&  (is_object ($group) == true)
				    &&  ($group->isError () == false)
				    &&  ($group->isProject () == true)
				    &&  ($group->usesPlugin ($this->name) == true))
				{
					echo "<a href=\"/plugins/novacontinuum/admin/index.php?group_id=" . $group->getID () . "\">" . dgettext ("gforge-plugin-novacontinuum", "title_admin") . "</a><br/>";
				}
				break;
			case "site_admin_option_hook" :
				echo "<li><a href=\"/plugins/novacontinuum/siteAdmin/index.php\">" . dgettext ("gforge-plugin-novacontinuum", "title_site_admin") . "</a><br/></li>";
				break;
			case "mypage" :
				require_once(dirname(__FILE__).'/services/ServicesManager.php');
				$serviceManager =& ServicesManager::getInstance();
				
				echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "your_continuum_projects"), false, false);
				$groups = $serviceManager->getUserProject();
				if(count($groups)>0){
						echo '<ul>';
						foreach ($groups as $group_id=>$group) {
								echo '<li>'.htmlspecialchars($group);
								$selectedInstance = $serviceManager->getInstanceForProjects($group_id);
								if(isset($selectedInstance)){
									
									$pingRet = $selectedInstance->instance->ping();
									if($pingRet===true){
										$projects = $serviceManager->getProjects($group_id);
										if(count($projects)>0){
											echo '<ul>';
											foreach ($projects as $keyProject=>$project) {
												echo '<li>'.$project->name;
												if(count($project->continuumProjects)>0){
													echo '<ul>';
													foreach ($project->continuumProjects as $key=>$value) {
														$continuumProject = $serviceManager->getContinuumProject($value,$selectedInstance);
														echo '<li><img src="/plugins/novacontinuum/imgs/'.$continuumProject->getStateImage().'" alt="state"/> ';
														echo '<a href="/plugins/novacontinuum/?group_id='.$group_id.'">'.$continuumProject->name.'</a>';
														echo '</li>'; 
													}
													echo '</ul>';
												}
												echo '</li>';
											}
											echo '</ul>';
										}else{
											echo "<br /><strong>" . dgettext ("gforge-plugin-novacontinuum", "no_project_assigned") . "</strong>";
										}		
									}else{
										echo "<br /><strong>" . dgettext ("gforge-plugin-novacontinuum", "not_reachable_instance") . "</strong>";
									}
								}else{
									echo "<br /><strong>" . dgettext ("gforge-plugin-novacontinuum", "no_selected_instance") . "</strong>";
								}
								echo '</li>';
						}
						echo '</ul>';
				}else{
					echo "<br /><strong>" . dgettext ("gforge-plugin-novacontinuum", "no_project_assigned") . "</strong>";
				}
			break;
			}
      
   }

}

?>

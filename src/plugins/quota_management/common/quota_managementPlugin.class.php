<?php

/**
 * quota_managementPlugin Class
 *
 * Copyright 2010, Fusionforge Team
 * Copyright 2011, Franck Villaume - Capgemini
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class quota_managementPlugin extends Plugin {
	function quota_managementPlugin () {
		$this->Plugin() ;
		$this->name = "quota_management" ;
		$this->text = "quota_management!" ; // To show in the tabs, use...
		$this->hooks[] = "user_personal_links";//to make a link to the user's personal part of the plugin
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "userisactivecheckbox" ; // The "use ..." checkbox in user account
		$this->hooks[] = "userisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "site_admin_option_hook"; // to show in admin
		$this->hooks[] = "quota_label_project_admin"; // to show in admin project
		$this->hooks[] = "quota_link_project_admin"; // to show in admin project
	}

	function CallHook ($hookname, &$params) {
		global $use_quota_managementplugin, $G_SESSION, $HTML;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("quota_management")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/quota_management/index.php' . $param ));
			}
		} elseif ($hookname == "groupmenu") {
			$group_id = $params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ($project->usesPlugin($this->name)) {
				$params['TITLES'][] = $this->text;
				$params['DIRS'][] = util_make_url ('/plugins/quota_management/index.php?type=group&id=' . $group_id . "&pluginname=" . $this->name) ; // we indicate the part we're calling is the project one
				$params['ADMIN'][] = '';
			} else {
			//	$params['TITLES'][]=$this->text." is [Off]";
			}
			(($params['toptab'] == $this->name) ? $params['selected'] = (count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_quota_managementplugin = getStringFromRequest('use_quota_managementplugin');
			if ($use_quota_managementplugin == 1) {
				$group->setPluginUse($this->name);
			} else {
				$group->setPluginUse($this->name, false);
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
			$user = $params['user'];
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_quota_managementplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $user->usesPlugin($this->name)) {
				echo "CHECKED";
 			}
			echo ">    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "userisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
			$user = $params['user'];
			$use_quota_managementplugin = getStringFromRequest('use_quota_managementplugin');
			if ($use_quota_managementplugin == 1) {
				$user->setPluginUse($this->name);
			} else {
				$user->setPluginUse($this->name, false);
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_quota_managementplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ($user->usesPlugin($this->name)) {
				echo "CHECKED";
			}
			echo ">    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "user_personal_links") {
			// this displays the link in the user's profile page to it's personal quota_management (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/quota_management/index.php?id=$userid&type=user&pluginname=".$this->name,
						     _('View Personal quota_management')
					) ;
				echo '</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  quota_management administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin($this->name)) {
				echo util_make_link('/plugins/quota_management/index.php?id='.$group->getID().'&type=admin&pluginname='.$this->name,
						     _('View the quota_management Administration')
					) ;
				echo '<br />';
			}
		} elseif ($hookname == "site_admin_option_hook") {
			// www/admin/index.php line 167
			// ...
			?>
			<li><?php echo util_make_link("/plugins/quota_management/quota.php",
						       _('Ressources usage and quota')
				); ?></li>
			<?php
		} elseif ($hookname == "quota_label_project_admin") {
			// www/project/admin/project_admin_utils.php line 80
			$labels[] = _('Quota');
		} elseif ($hookname == "quota_link_project_admin") {
			// www/project/admin/project_admin_utils.php line 99
			$group_id=$params['group'];
			$links[] = '/plugins/quota_management/quota.php?group_id='.$group_id;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

<?php

/**
 * ContribTrackerPlugin Class
 *
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
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
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

class ContribTrackerPlugin extends Plugin {
	function ContribTrackerPlugin () {
		$this->Plugin() ;
		$this->name = "contribtracker" ;
		$this->text = "Contribution Tracker" ; // To show in the tabs, use...
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page for group
	}

	function CallHook ($hookname, $params) {
		if ($hookname == "groupmenu") {
			$group_id=$params['group'];
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
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][] = _('Contribution tracker') ;
				$params['DIRS'][]='/plugins/contribtracker/index.php?type=group&id=' . $group_id . "&pluginname=" . $this->name; // we indicate the part we're calling is the project one
			}	
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_contribtrackerplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo "><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_contribtrackerplugin = getStringFromRequest('use_contribtrackerplugin');
			if ( $use_contribtrackerplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  ContribTracker administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo util_make_link ("/plugins/projects_hierarchy/index.php?id=".$group->getID().'&type=admin&pluginname='.$this->name,
						     _('View the ContribTracker Administration')) ;
				echo '</p>';
			}
		}												    
		elseif ($hookname == "blahblahblah") {
			// ...
		} 
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

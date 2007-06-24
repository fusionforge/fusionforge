<?php

/**
 * CVSSyncMailPlugin Class
 *
 *
 * This file is part of GForge.
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
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

class CVSSyncMailPlugin extends Plugin {

	function CVSSyncMailPlugin () {
		$this->Plugin() ;
		$this->name = "cvssyncmail" ;
		$this->text = "CVS->Syncmail" ;
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
	}

	function CallHook ($hookname, $params) {
		global $use_cvssyncmailplugin,$G_SESSION,$HTML;
		$group_id=$params['group'];
		if ($hookname == "groupisactivecheckbox") {
			$group = &group_get_object($group_id);
			if ($group->usesPlugin('scmcvs')) {
				//Check if the group is active
				// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
				echo "<tr>";
				echo "<td>";
				echo ' <input type="CHECKBOX" name="use_cvssyncmailplugin" value="1" ';
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
			}
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group = &group_get_object($group_id);
			$use_cvssyncmailplugin = getStringFromRequest('use_cvssyncmailplugin');
			if ( $use_cvssyncmailplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} 
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

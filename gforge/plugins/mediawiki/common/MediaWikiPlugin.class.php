<?php

/**
 * MediaWikiPlugin Class
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

class MediaWikiPlugin extends Plugin {
	function MediaWikiPlugin () {
		$this->Plugin() ;
		$this->name = "mediawiki" ;
		$this->text = "Mediawiki" ; // To show in the tabs, use...
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "project_public_area";
		$this->hooks[] = "role_get";
		$this->hooks[] = "role_normalize";
		$this->hooks[] = "role_translate_strings";
	}

	function CallHook ($hookname, $params) {
		global $use_mediawikiplugin,$G_SESSION,$HTML;

		if (isset($params['group_id'])) {
			$group_id=$params['group_id'];
		} elseif (isset($params['group'])) {
			$group_id=$params['group'];
		} else {
			$group_id=null;
		}
		if ($hookname == "groupmenu") {
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
				$params['TITLES'][]=$this->text;
				if ($GLOBALS['sys_use_mwframe']){
					$params['DIRS'][]=util_make_url ('/plugins/mediawiki/frame.php?group_id=' . $project->getID()) ; 
				} else {
					$params['DIRS'][]=util_make_url('/plugins/mediawiki/wiki/'.$project->getUnixName().'/index.php');
				}
			}
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_mediawikiplugin" value="1" ';
			// checked or unchecked?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "checked";
			}
			echo " /><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group = &group_get_object($group_id);
			$use_mediawikiplugin = getStringFromRequest('use_mediawikiplugin');
			if ( $use_mediawikiplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "project_public_area") {
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
				print '<a href='. util_make_url ('/plugins/mediawiki/wiki/'.$project->getUnixName().'/index.php').'>';
				print html_abs_image(util_make_url ('/plugins/mediawiki/wiki/'.$project->getUnixName().'/skins/fusionforge/wiki.png'),'20','20',array('alt'=>'Mediawiki'));
				print ' Mediawiki';
				print '</a>';
			}
		} elseif ($hookname == "role_get") {
			$role =& $params['role'] ;
			
			$edit = new PluginSpecificRoleSetting ($role,
							       'plugin_mediawiki_edit') ;
			$edit->SetAllowedValues (array ('0', '1', '2')) ;
			$edit->SetDefaultValues (array ('Admin' => '2',
							'Senior Developer' => '2',
							'Junior Developer' => '1',
							'Doc Writer' => '2',
							'Support Tech' => '0')) ;
		} elseif ($hookname == "role_normalize") {
			$role =& $params['role'] ;
			$new_sa =& $params['new_sa'] ;

			$role->normalizeDataForSection ($new_sa, 'plugin_mediawiki_edit') ;
		} elseif ($hookname == "role_translate_strings") {
			$edit = new PluginSpecificRoleSetting ($role,
							       'plugin_mediawiki_edit') ;
			$edit->setDescription (_('Mediawiki write access')) ;
			$edit->setValueDescriptions (array ('0' => _('No editing'),
							    '1' => _('Edit existing pages only'), 
							    '2' => _('Edit and create pages'))) ;
		}
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

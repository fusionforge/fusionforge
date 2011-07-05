<?php
/**
 * quota_managementPlugin Class
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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
		$this->name = "quota_management";
		$this->text = "Quota Management"; // To show in the tabs, use...
		$this->_addHook('groupisactivecheckbox'); // The "use ..." checkbox in editgroupinfo
		$this->_addHook('groupisactivecheckboxpost'); //
		$this->_addHook('userisactivecheckbox'); // The "use ..." checkbox in user account
		$this->_addHook('userisactivecheckboxpost'); //
		$this->_addHook('project_admin_plugins'); // to show up in the admin page fro group
		$this->_addHook('site_admin_option_hook'); // to show in admin
		$this->_addHook('groupadminmenu');
	}

	function CallHook($hookname, &$params) {
		global $use_quota_managementplugin, $G_SESSION, $HTML;
		$returned = false;
		switch ($hookname) {
			case "project_admin_plugins": {
				// this displays the link in the project admin options page to it's  quota_management administration
				$group_id = $params['group_id'];
				$group = &group_get_object($group_id);
				if ( $group->usesPlugin($this->name)) {
					echo util_make_link('/plugins/quota_management/index.php?id='.$group->getID().'&type=admin&pluginname='.$this->name,
							_('View the quota_management Administration')
						) ;
					echo '<br />';
				}
				$returned = true;
				break;
			}
			case "site_admin_option_hook": {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
				$returned = true;
				break;
			}
			case "groupadminmenu": {
				$params['labels'][] = _ ('Quota');
				$group_id = $params['group'];
				$params['links'][] = '/plugins/quota_management/index.php?id='.$group_id.'&type=admin&pluginname='.$this->name;
				$params['attr_r'][] = array('class' => 'tabtitle', 'title' => _('View the quota_management Administration'));
				$returned = true;
				break;
			}
		}
		return $returned;
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/quota.php', _('Ressources usage and quota'));
	}

	function convert_bytes_to_mega($mega) {
		$b = round($mega / (1024*1024), 2);
		return $b;
	}

	function add_numbers_separator($val, $sep=' ') {
		$size = "$val";
		$size = strrev($size);
		$size = wordwrap($size, 3, $sep, 1);
		$size = strrev($size);
		return $size;
	}

	function get_dir_size($dir) {
		$size = "";
		$cmd = "/usr/bin/du -bs $dir";
		$res = shell_exec ($cmd);
		$a = explode("\t", $res);
		if (isset($a[1])) $size = $a[0];
		return "$size";
	}

	function quota_management_Project_Header($params) {
		global $id;
		$params['toptab'] = 'quota_management';
		$params['group'] = $id;
		/*
		Show horizontal links
		*/
		site_project_header($params);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

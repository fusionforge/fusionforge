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
	function __construct() {
		parent::__construct();
		$this->name = "quota_management";
		$this->text = _("Quota Management"); // To show in the tabs, use...
		$this->pkg_desc =
_("This is a Quota Management plugin within FusionForge. Provide an easy way
to monitor disk and database usage per user, project.");
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
				echo util_make_link('/plugins/quota_management/index.php?group_id='.$params['group_id'].'&type=projectadmin',
						_('View the Quota Management Administration'));
				echo '<br />';
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
				$params['links'][] = '/plugins/quota_management/index.php?group_id='.$params['group'].'&type=projectadmin';
				$params['attr_r'][] = array('title' => _('View the Quota Management Administration'));
				$returned = true;
				break;
			}
		}
		return $returned;
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/index.php?type=globaladmin', _('Ressources usage and quota'));
	}

	function get_dir_size($dir) {
		$size = "";
		$cmd = "/usr/bin/du -bs $dir";
		$res = shell_exec ($cmd);
		$a = explode("\t", $res);
		if (isset($a[1])) $size = $a[0];
		return "$size";
	}

	function getHeader($type, $group_id = 0) {
		switch ($type) {
			case 'globaladmin': {
				global $gfwww;
				require_once $gfwww.'admin/admin_utils.php';
				site_admin_header(array('title'=>_('Quota and Usage Admin')));
				break;
			}
			case 'projectadmin': {
				global $gfwww;
				require_once $gfwww.'project/admin/project_admin_utils.php';
				project_admin_header(array('title' => sprintf(_('Quota Management for %s'), group_getname($group_id)), 'group' => $group_id));
				break;
			}
		}
	}

	function getDocumentsSizeQuery() {
		return db_query_params('SELECT doc_data.group_id, SUM(doc_data_version.filesize) as size, SUM(octet_length(doc_data_version.data_words)) as size1
					FROM doc_data, doc_data_version WHERE doc_data.docid = doc_data_version.docid GROUP BY doc_data.group_id',
			array());
	}

	function getDocumentsSizeForProject($group_id) {
		return db_query_params('SELECT doc_data.group_id, SUM(doc_data_version.filesize) as size, SUM(octet_length(doc_data_version.data_words)) as size1, count(doc_data_version.serial_id) as nb
					FROM doc_data, doc_data_version WHERE doc_data.docid = doc_data_version.docid AND doc_data.group_id = $1 GROUP BY doc_data.group_id',
			array($group_id));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

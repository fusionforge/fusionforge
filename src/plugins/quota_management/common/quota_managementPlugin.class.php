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

	var $data_array;

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
		$returned = false;
		switch ($hookname) {
			case "project_admin_plugins": {
				// this displays the link in the project admin options page to it's  quota_management administration
				echo html_e('p', array(), util_make_link('/plugins/'.$this->name.'/?type=projectadmin&group_id='.$params['group_id'],
						_('Quota Management Administration')));
				$returned = true;
				break;
			}
			case "site_admin_option_hook": {
				echo html_e('li', array(), $this->getAdminOptionLink());
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
		$size = 0;
		$cmd = "/usr/bin/du -bs $dir";
		$res = shell_exec($cmd);
		$a = explode("\t", $res);
		if (isset($a[1])) $size = $a[0];
		return (int)$size;
	}

	function setDirSize($group_id, $dirtype, $dirsize) {
		switch ($dirtype) {
			case 'home':
				$res = db_query_params('UPDATE plugin_quota_management SET home_usage = $1 WHERE group_id = $2', array($dirsize, $group_id));
				break;
			case 'ftp':
				$res = db_query_params('UPDATE plugin_quota_management SET ftp_usage = $1 WHERE group_id = $2', array($dirsize, $group_id));
				break;
		}
	}

	function getDataArray($group_id) {
		$res = db_query_params('SELECT * from plugin_quota_management WHERE group_id = $1', array($group_id));
		$this->data_array = db_fetch_array($res);
	}

	function getFTPSize($group_id) {
		if (!isset($this->data_array['ftp_usage'])) {
			$this->getDataArray($group_id);
		}
		return $this->data_array['ftp_usage'];
	}

	function getHomeSize($group_id) {
		if (!isset($this->data_array['home_usage'])) {
			$this->getDataArray($group_id);
		}
		return $this->data_array['home_usage'];
	}

	function getQuota($group_id, $quota_type) {
		if (!isset($this->data_array[$quota_type])) {
			$this->getDataArray($group_id);
		}
		return $this->data_array[$quota_type];
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
					FROM doc_data, doc_data_version
					WHERE doc_data.docid = doc_data_version.docid
					GROUP BY doc_data.group_id
					ORDER BY doc_data.group_id',
			array());
	}

	function getDocumentsSizeForProject($group_id) {
		return db_query_params('SELECT SUM(doc_data_version.filesize) as size, SUM(octet_length(doc_data_version.data_words)) as size1, count(doc_data_version.serial_id) as nb
					FROM doc_data, doc_data_version WHERE doc_data.docid = doc_data_version.docid AND doc_data.group_id = $1',
			array($group_id));
	}

	function getNewsSizeQuery() {
		return db_query_params('SELECT group_id, SUM(octet_length(summary) + octet_length(details)) as size
					FROM news_bytes
					GROUP BY group_id
					ORDER BY group_id',
			array());
	}

	function getNewsSizeForProject($group_id) {
		return db_query_params('SELECT SUM(octet_length(summary) + octet_length(details)) as size, count(*) as nb
					FROM news_bytes
					WHERE group_id = $1',
			array($group_id));
	}

	function getForumSizeQuery() {
		return db_query_params('SELECT forum_group_list.group_id as group_id, SUM(octet_length(subject)+octet_length(body)) as size
					FROM forum INNER JOIN forum_group_list ON forum.group_forum_id = forum_group_list.group_forum_id
					GROUP BY group_id
					ORDER BY group_id',
			array());
	}

	function getForumSizeForProject($group_id) {
		return db_query_params('SELECT SUM(octet_length(subject)+octet_length(body)) as size, count(*) as nb
					FROM forum INNER JOIN forum_group_list ON forum.group_forum_id = forum_group_list.group_forum_id
					WHERE group_id = $1',
			array($group_id));
	}

	function getTrackersSizeQuery() {
		return db_query_params('SELECT artifact_group_list.group_id, SUM(octet_length(artifact.summary)+octet_length(artifact.details)+octet_length(artifact_message.body)+artifact_file.filesize) as size
					FROM artifact, artifact_group_list, artifact_message, artifact_file
					WHERE artifact.group_artifact_id = artifact_group_list.group_artifact_id
					AND artifact.artifact_id = artifact_message.artifact_id
					AND artifact.artifact_id = artifact_file.artifact_id
					GROUP BY artifact_group_list.group_id
					ORDER BY artifact_group_list.group_id',
			array());
	}

	function getTrackerSizeForProject($group_id) {
		return db_query_params('SELECT SUM(octet_length(artifact.summary)+octet_length(artifact.details)+octet_length(artifact_message.body)+artifact_file.filesize) as size, count(artifact_group_list.group_artifact_id) as nb
					FROM artifact, artifact_group_list, artifact_message, artifact_file
					WHERE artifact.group_artifact_id = artifact_group_list.group_artifact_id
					AND artifact.artifact_id = artifact_message.artifact_id
					AND artifact.artifact_id = artifact_file.artifact_id
					AND group_id = $1',
			array($group_id));
	}

	function getFRSSizeQuery() {
		return db_query_params('SELECT frs_package.group_id, SUM(octet_length(frs_package.name)+octet_length(frs_release.name)+octet_length(frs_release.notes)+octet_length(frs_release.changes)+frs_file.file_size) as size
					FROM frs_package, frs_release, frs_file
					WHERE frs_package.package_id = frs_release.package_id
					AND frs_release.release_id = frs_file.release_id
					GROUP BY frs_package.group_id
					ORDER BY frs_package.group_id',
			array());
	}

	function getFRSSizeForProject($group_id) {
		return db_query_params('SELECT SUM(octet_length(frs_package.name)+octet_length(frs_release.name)+octet_length(frs_release.notes)+octet_length(frs_release.changes)+frs_file.file_size) as size, count(frs_package.package_id) as nb
					FROM frs_package, frs_release, frs_file
					WHERE frs_package.package_id = frs_release.package_id
					AND frs_release.release_id = frs_file.release_id
					AND group_id = $1',
			array($group_id));
	}

	function getPMSizeQuery() {
		return db_query_params('SELECT project_group_list.group_id, SUM(octet_length(project_group_list.description)+octet_length(project_task.summary)+octet_length(project_task.details)+octet_length(project_messages.body)) as size
					FROM project_group_list, project_task, project_messages
					WHERE project_group_list.group_project_id = project_task.group_project_id
					AND project_task.project_task_id = project_messages.project_task_id
					GROUP BY project_group_list.group_id
					ORDER BY project_group_list.group_id',
			array());
	}

	function getPMSizeForProject($group_id) {
		return db_query_params('SELECT SUM(octet_length(project_group_list.description)+octet_length(project_task.summary)+octet_length(project_task.details)+octet_length(project_messages.body)) as size, count(project_group_list.group_project_id) as nb
					FROM project_group_list, project_task, project_messages
					WHERE project_group_list.group_project_id = project_task.group_project_id
					AND project_task.project_task_id = project_messages.project_task_id
					AND group_id = $1',
			array($group_id));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

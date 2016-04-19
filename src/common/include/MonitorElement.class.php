<?php
/**
 * FusionForge MonitorElement Object
 *
 * Copyright 2014, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class MonitorElement extends FFError {

	var $_clearMonitorQuery = null;
	var $_clearMonitorForUserIdQuery = null;
	var $_disableMonitoringByUserIdQuery = null;
	var $_disableMonitoringForGroupIdByUserIdQuery = null;
	var $_enableMonitoringByUserIdQuery = null;
	var $_getAllEmailsInArrayQuery = null;
	var $_getMonitorCounterIntegerQuery = null;
	var $_getMonitorUsersIdsInArrayQuery = null;
	var $_getMonitoredByUserIdInArrayQuery = null;
	var $_getMonitoredDistinctGroupIdsByUserIdInArrayQuery = null;
	var $_getMonitoredIdsByGroupIdByUserIdInArrayQuery = null;
	var $_isMonitoredByAnyQuery = null;
	var $_isMonitoredByUserIdQuery = null;

	function __construct($what) {
		switch ($what) {
			case 'docdata': {
				$this->_clearMonitorQuery = 'delete from docdata_monitored_docman where doc_id = $1';
				$this->_clearMonitorForUserIdQuery = 'delete from docdata_monitored_docman where user_id = $1';
				$this->_disableMonitoringByUserIdQuery = 'delete from docdata_monitored_docman where doc_id = $1 and user_id = $2';
				$this->_disableMonitoringForGroupIdByUserIdQuery = 'delete from docdata_monitored_docman where exists (select docdata_monitored_docman.doc_id from docdata_monitored_docman, doc_data where docdata_monitored_docman.doc_id = doc_data.docid and doc_data.group_id = $1 and docdata_monitored_docman.user_id = $2)';
				$this->_enableMonitoringByUserIdQuery = 'insert into docdata_monitored_docman (doc_id, user_id) values ($1, $2)';
				$this->_getAllEmailsInArrayQuery = 'select users.email from users, docdata_monitored_docman where users.user_id = docdata_monitored_docman.user_id and docdata_monitored_docman.doc_id = $1 and users.status = $2';
				$this->_getMonitorCounterIntegerQuery = 'select count(docgroup_monitored_docman.user_id) as count from docdata_monitored_docman, users where users.user_id = docdata_monitored_docman.user_id and doc_id = $1 and users.status = $2';
				$this->_getMonitorUsersIdsInArrayQuery = 'select docdata_monitored_docman.user_id from docdata_monitored_docman, users where users.user_id = docdata_monitored_docman.user_id and doc_id = $1 and users.status = $2';;
				$this->_getMonitoredByUserIdInArrayQuery = 'select doc_id from docdata_monitored_docman where user_id = $1';
				$this->_getMonitoredDistinctGroupIdsByUserIdInArrayQuery = 'select distinct doc_data.group_id from groups, doc_data, docdata_monitored_docman where docdata_monitored_docman.doc_id = doc_data.docid and groups.group_id = doc_data.group_id and docdata_monitored_docman.user_id = $1 and groups.status = $2';
				$this->_getMonitoredIdsByGroupIdByUserIdInArrayQuery = 'select doc_data.docid from doc_data, docdata_monitored_docman where doc_data.docid = docdata_monitored_docman.doc_id and doc_data.group_id = $1 and docdata_monitored_docman.user_id = $2';
				$this->_isMonitoredByAnyQuery = 'select doc_id, docdata_monitored_docman.user_id from docdata_monitored_docman, users where users.user_id = docdata_monitored_docman.user_id and doc_id = $1 and users.status = $2';
				$this->_isMonitoredByUserIdQuery = 'select doc_id from docdata_monitored_docman where doc_id = $1 and user_id = $2';
				break;
			}
			case 'docgroup': {
				$this->_clearMonitorQuery = 'delete from docgroup_monitored_docman where docgroup_id = $1';
				$this->_clearMonitorForUserIdQuery = 'delete from docgroup_monitored_docman where user_id = $1';
				$this->_disableMonitoringByUserIdQuery = 'delete from docgroup_monitored_docman where docgroup_id = $1 and user_id = $2';
				$this->_disableMonitoringForGroupIdByUserIdQuery = null; // not used by docgroup.
				$this->_enableMonitoringByUserIdQuery = 'insert into docgroup_monitored_docman (docgroup_id, user_id) values ($1, $2)';
				$this->_getAllEmailsInArrayQuery = 'select users.email from users, docgroup_monitored_docman where users.user_id = docgroup_monitored_docman.user_id and docgroup_monitored_docman.docgroup_id = $1 and users.status = $2';
				$this->_getMonitorCounterIntegerQuery = 'select count(docgroup_monitored_docman.user_id) as count from docgroup_monitored_docman, users where users.user_id = docgroup_monitored_docman.user_id and docgroup_id = $1 and users.status = $2';
				$this->_getMonitorUsersIdsInArrayQuery = 'select docgroup_monitored_docman.user_id from docgroup_monitored_docman, users where users.user_id = docgroup_monitored_docman.user_id and docgroup_id = $1 and users.status = $2';
				$this->_getMonitoredByUserIdInArrayQuery = 'select docgroup_id from docgroup_monitored_docman where user_id = $1';
				$this->_getMonitoredDistinctGroupIdsByUserIdInArrayQuery = 'select distinct doc_groups.group_id from groups, doc_groups, docgroup_monitored_docman where docgroup_monitored_docman.docgroup_id = doc_groups.doc_group and groups.group_id = doc_groups.group_id and docgroup_monitored_docman.user_id = $1';
				$this->_getMonitoredIdsByGroupIdByUserIdInArrayQuery = 'select doc_groups.doc_group from doc_groups, docgroup_monitored_docman where doc_groups.doc_group = docgroup_monitored_docman.docgroup_id and doc_groups.group_id = $1 and docgroup_monitored_docman.user_id = $2';
				$this->_isMonitoredByAnyQuery = 'select docgroup_id, docgroup_monitored_docman.user_id from docgroup_monitored_docman, users where users.user_id = docgroup_monitored_docman.user_id and docgroup_id = $1 and users.status = $2';
				$this->_isMonitoredByUserIdQuery = 'select docgroup_id from docgroup_monitored_docman where docgroup_id = $1 and user_id = $2';
				break;
			}
			case 'forum': {
				$this->_clearMonitorQuery = 'delete from forum_monitored_forums where forum_id = $1';
				$this->_clearMonitorForUserIdQuery = 'delete from forum_monitored_forums where user_id = $1';
				$this->_disableMonitoringByUserIdQuery = 'delete from forum_monitored_forums where forum_id = $1 and user_id = $2';
				$this->_disableMonitoringForGroupIdByUserIdQuery = 'delete from forum_monitored_forums where exists (select forum_monitored_forums.forum_id from forum_monitored_forums, forum_group_list where forum_monitored_forums.forum_id = forum_group_list.group_forum_id and forum_group_list.group_id = $1 and forum_monitored_forums.user_id = $2)';
				$this->_enableMonitoringByUserIdQuery = 'insert into forum_monitored_forums (forum_id, user_id) values ($1, $2)';
				$this->_getAllEmailsInArrayQuery = 'select users.email from users, forum_monitored_forums where users.user_id = forum_monitored_forums.user_id and forum_monitored_forums.forum_id = $1 and users.status = $2';
				$this->_getMonitorCounterIntegerQuery = 'select count(forum_monitored_forums.user_id) as count from forum_monitored_forums, users where users.user_id = forum_monitored_forums.user_id and forum_monitored_forums.forum_id = $1 and users.status = $2';
				$this->_getMonitorUsersIdsInArrayQuery = 'select forum_monitored_forums.user_id from forum_monitored_forums, users where users.user_id = forum_monitored_forums.user_id and forum_id = $1 and users.status = $2';
				$this->_getMonitoredByUserIdInArrayQuery = 'select forum_id from forum_monitored_forums where user_id = $1';
				$this->_getMonitoredDistinctGroupIdsByUserIdInArrayQuery = 'select distinct forum_group_list.group_id from groups, forum_group_list, forum_monitored_forums where groups.group_id = forum_group_list.group_id and forum_group_list.group_forum_id = forum_monitored_forums.forum_id and forum_monitored_forums.user_id = $1 and groups.status = $2';
				$this->_getMonitoredIdsByGroupIdByUserIdInArrayQuery = 'select forum_group_list.group_forum_id from groups, forum_group_list,forum_monitored_forums where groups.group_id = forum_group_list.group_id and forum_group_list.group_forum_id = forum_monitored_forums.forum_id and groups.group_id = $1 and forum_monitored_forums.user_id= $2';
				$this->_isMonitoredByAnyQuery = 'select forum_id, forum_monitored_forums.user_id from forum_monitored_forums, users where users.user_id = forum_monitored_forums.user_id and forum_monitored_forums.forum_id = $1 and users.status = $2';
				$this->_isMonitoredByUserIdQuery = 'select forum_id from forum_monitored_forums where forum_id = $1 and user_id = $2';
				break;
			}
			case 'artifact_type': {
				$this->_clearMonitorQuery = 'delete from artifact_type_monitor where group_artifact_id = $1';
				$this->_clearMonitorForUserIdQuery = 'delete from artifact_type_monitor where user_id = $1';
				$this->_enableMonitoringByUserIdQuery = 'insert into artifact_type_monitor (group_artifact_id, user_id) values ($1, $2)';
				$this->_disableMonitoringByUserIdQuery = 'delete from artifact_type_monitor where group_artifact_id = $1 and user_id = $2';
				$this->_getMonitorUsersIdsInArrayQuery = 'select artifact_type_monitor.user_id from artifact_type_monitor, users where users.user_id = artifact_type_monitor.user_id and artifact_type_monitor.group_artifact_id = $1 and users.status = $2';
				$this->_isMonitoredByUserIdQuery = 'select group_artifact_id from artifact_type_monitor where group_artifact_id = $1 and user_id = $2';
				break;
			}
			case 'artifact': {
				$this->_clearMonitorQuery = 'delete from artifact_monitor where artifact_id = $1';
				$this->_clearMonitorForUserIdQuery = 'delete from artifact_monitor where user_id = $1';
				$this->_enableMonitoringByUserIdQuery = 'insert into artifact_monitor (artifact_id, user_id) values ($1, $2)';
				$this->_disableMonitoringByUserIdQuery = 'delete from artifact_monitor where artifact_id = $1 and user_id = $2';
				$this->_getMonitorUsersIdsInArrayQuery = 'select artifact_monitor.user_id from artifact_monitor, users where users.user_id = artifact_monitor.user_id and artifact_monitor.artifact_id = $1 and users.status = $2';
				$this->_isMonitoredByUserIdQuery = 'select artifact_id from artifact_monitor where artifact_id = $1 and user_id = $2';
				break;
			}
			case 'frspackage': {
				$this->_clearMonitorQuery = 'delete from filemodule_monitor where filemodule_id = $1';
				$this->_clearMonitorForUserIdQuery = 'delete from filemodule_monitor where user_id = $1';
				$this->_disableMonitoringByUserIdQuery = 'delete from filemodule_monitor where filemodule_id = $1 and user_id = $2';
				$this->_disableMonitoringForGroupIdByUserIdQuery = 'delete from filemodule_monitor where exists (select filemodule_monitor.filemodule_id from filemodule_monitor, frs_package where filemodule_monitor.filemodule_id = frs_package.package_id and frs_package.group_id = $1 and filemodule_monitor.user_id = $2)';
				$this->_enableMonitoringByUserIdQuery = 'insert into filemodule_monitor (filemodule_id, user_id) values ($1, $2)';
				$this->_getAllEmailsInArrayQuery = 'select users.email from users, filemodule_monitor where users.user_id = filemodule_monitor.user_id and filemodule_monitor.filemodule_id = $1 and users.status = $2';
				$this->_getMonitorCounterIntegerQuery = 'select count(filemodule_monitor.user_id) as count from filemodule_monitor, users where users.user_id = filemodule_monitor.user_id and filemodule_monitor.filemodule_id = $1 and users.status = $2';
				$this->_getMonitorUsersIdsInArrayQuery = 'select filemodule_monitor.user_id from filemodule_monitor, users where users.user_id = filemodule_monitor.user_id and filemodule_monitor.filemodule_id = $1 and users.status = $2';
				$this->_getMonitoredByUserIdInArrayQuery = 'select filemodule_id from filemodule_monitor where user_id = $1';
				$this->_getMonitoredDistinctGroupIdsByUserIdInArrayQuery = 'select distinct frs_package.group_id from groups, frs_package, filemodule_monitor where filemodule_monitor.filemodule_id = frs_package.package_id and groups.group_id = frs_package.group_id and filemodule_monitor.user_id = $1 and groups.status = $2';
				$this->_getMonitoredIdsByGroupIdByUserIdInArrayQuery = 'select filemodule_monitor.filemodule_id from groups,filemodule_monitor,frs_package where groups.group_id = frs_package.group_id and frs_package.package_id = filemodule_monitor.filemodule_id and groups.group_id=$1 and filemodule_monitor.user_id=$2';
				$this->_isMonitoredByAnyQuery = 'select filemodule_id, filemodule_monitor.user_id from filemodule_monitor, users where users.user_id = filemodule_monitor.user_id and filemodule_monitor.filemodule_id = $1 and users.status = $2';
				$this->_isMonitoredByUserIdQuery = 'select filemodule_id from filemodule_monitor where filemodule_id = $1 and user_id = $2';
				break;
			}
			case 'frsrelease': {
				break;
			}
			default: {
				return false;
			}
		}
		return true;
	}

	function clearMonitor($which = 0) {
		if ($which && isset($this->_clearMonitorQuery)) {
			$result = db_query_params($this->_clearMonitorQuery, array($which));
			if (!$result) {
				$this->setError(_('Unable to clear monitoring')._(': ').db_error());
				return false;
			}
			return true;
		}
		$this->setError('clearMonitor:: '._('Missing parameters values.'));
		return false;
	}

	function clearMonitorForUserId($who = 0) {
		if ($who && isset($this->_clearMonitorForUserIdQuery)) {
			$result = db_query_params($this->_clearMonitorForUserIdQuery, array($who));
			if (!$result) {
				$this->setError(_('Unable to clear monitoring for user')._(': ').db_error());
				return false;
			}
			return true;
		}
		$this->setError('clearMonitor:: '._('Missing parameters values.'));
		return false;
	}

	function disableMonitoringByUserId($which = 0, $who = 0) {
		if ($which && $who && isset($this->_disableMonitoringByUserIdQuery)) {
			if ($this->isMonitoredByUserId($which, $who)) {
				$result = db_query_params($this->_disableMonitoringByUserIdQuery, array($which, $who));
				if (!$result) {
					$this->setError(_('Unable to remove monitor from db')._(': ').db_error());
					return false;
				}
			}
			return true;
		}
		$this->setError('disableMonitoringByUserId:: '._('Missing parameters values.'));
		return false;
	}

	function disableMonitoringForGroupIdByUserId($where = 0, $who = 0) {
		if ($where && $who && isset($this->_disableMonitoringForGroupIdByUserIdQuery)) {
			$result = db_query_params($this->_disableMonitoringForGroupIdByUserIdQuery, array($where, $who));
			if (!$result) {
				$this->setError(_('Unable to remove monitor from db')._(': ').db_error());
				return false;
			}
			return true;
		}
		$this->setError('disableMonitoringForGroupIdByUserId:: '._('Missing parameters values.'));
		return false;
	}

	function enableMonitoringByUserId($which, $who) {
		if ($which && $who && isset($this->_enableMonitoringByUserIdQuery)) {
			if (!$this->isMonitoredByUserId($which, $who)) {
				$result = db_query_params($this->_enableMonitoringByUserIdQuery, array($which, $who));
				if (!$result) {
					$this->setError(_('Unable to add monitor')._(': ').db_error());
					return false;
				}
			}
			return true;
		}
		$this->setError('enableMonitoringByUserId:: '._('Missing parameters values.'));
		return true;
	}

	function getAllEmailsInArray($which = 0) {
		if ($which && isset($this->_getAllEmailsInArrayQuery)) {
			$result = db_query_params($this->_getAllEmailsInArrayQuery, array($which, 'A'));
			if ($result || db_numrows($result) >= 0) {
				return util_result_column_to_array($result);
			}
			$this->setError(_('Unable to get emails from db')._(': ').db_error());
			return false;
		}
		$this->setError('getAllEmailsInArray:: '._('Missing parameters values.'));
		return false;
	}

	function getAllEmailsInCommatSeparated($which = 0) {
		if ($which) {
			$getAllEmailsInCommatSeparatedArray = $this->getAllEmailsInArray($which);
			if ($getAllEmailsInCommatSeparatedArray && is_array($getAllEmailsInCommatSeparatedArray)) {
				$getAllEmailsInCommatSeparatedString = '';
				$comma = '';
				for ($i = 0; $i < count($getAllEmailsInCommatSeparatedArray); $i++) {
					if ( $i > 0 )
						$comma = ',';

					$getAllEmailsInCommatSeparatedString .= $comma.$getAllEmailsInCommatSeparatedArray[$i];
				}
				return $getAllEmailsInCommatSeparatedString;
			}
			return false;
		}
		$this->setError('getAllEmailsInCommatSeparated:: '._('Missing parameters values.'));
		return false;
	}

	function getMonitorUsersIdsInArray($which = 0) {
		if ($which && isset($this->_getMonitorUsersIdsInArrayQuery)) {
			$result = db_query_params($this->_getMonitorUsersIdsInArrayQuery, array($which, 'A'));
			if ($result || db_numrows($result) >= 0) {
				return util_result_column_to_array($result);
			} else {
				$this->setError(_('Unable to get ids from db')._(': ').db_error());
				return false;
			}
		}
		$this->setError('getMonitorUsersIdsInArray:: '._('Missing parameters values.'));
		return false;
	}

	function getMonitorCounterInteger($which = 0) {
		if ($which && isset($this->_getMonitorCounterIntegerQuery)) {
			$result = db_query_params($this->_getMonitorCounterIntegerQuery, array($which, 'A'));
			if ($result) {
				return db_numrows($result);
			} else {
				$this->setError(_('Unable to get counter from db')._(': ').db_error());
				return false;
			}
		}
		$this->setError('getMonitorCounterInteger:: '._('Missing parameters values.'));
		return false;
	}

	function getMonitedByUserIdInArray($who = 0) {
		if ($who && isset($this->_getMonitoredByUserIdInArrayQuery)) {
			$result = db_query_params($this->_getMonitoredByUserIdInArrayQuery, array($who));
			if ($result || db_numrows($result) >= 0) {
				return util_result_column_to_array($result);
			} else {
				$this->setError(_('Unable to get ids from db')._(': ').db_error());
				return false;
			}
		}
		$this->setError('getMonitedByUserId:: '._('Missing parameters values.'));
		return false;
	}

	function getMonitoredDistinctGroupIdsByUserIdInArray($who = 0) {
		if ($who && isset($this->_getMonitoredDistinctGroupIdsByUserIdInArrayQuery)) {
			$result = db_query_params($this->_getMonitoredDistinctGroupIdsByUserIdInArrayQuery, array($who, 'A'));
			if ($result || db_numrows($result) >= 0) {
				return util_result_column_to_array($result);
			} else {
				$this->setError(_('Unable to get ids from db')._(': ').db_error());
				return false;
			}
		}
		$this->setError('getMonitoredDistinctGroupIdsByUserIdInArray:: '._('Missing parameters values.'));
		return false;
	}

	function getMonitoredIdsByGroupIdByUserIdInArray($where = 0, $who = 0) {
		if ($who && isset($this->_getMonitoredIdsByGroupIdByUserIdInArrayQuery)) {
			$result = db_query_params($this->_getMonitoredIdsByGroupIdByUserIdInArrayQuery, array($where, $who));
			if ($result || db_numrows($result) >= 0) {
				return util_result_column_to_array($result);
			} else {
				$this->setError(_('Unable to get ids from db')._(': ').db_error());
				return false;
			}
		}
		$this->setError('getMonitoredIdsByGroupIdByUserIdInArray:: '._('Missing parameters values.'));
		return false;
	}

	function isMonitoredByUserId($which = 0, $who = 0) {
		if ($which && $who && isset($this->_isMonitoredByUserIdQuery)) {
			$result = db_query_params($this->_isMonitoredByUserIdQuery, array($which, $who));
			if ($result && db_numrows($result) == 1) {
				return true;
			}
			return false;
		}
		$this->setError('isMonitoredByUserId:: '._('Missing parameters values.'));
		return false;
	}

	function isMonitoredByAny($which = 0) {
		if ($which && isset($this->_isMonitoredByAnyQuery)) {
			$result = db_query_params($this->_isMonitoredByAnyQuery, array($which, 'A'));
			if ($result && db_numrows($result) >= 1) {
				return true;
			}
			return false;
		}
		$this->setError('isMonitoredByAny:: '._('Missing parameters values.'));
		return false;
	}
}

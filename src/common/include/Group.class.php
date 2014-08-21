<?php
/**
 * FusionForge groups
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2009-2013, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2010-2012, Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013, Franck Villaume - TrivialDev
 * Copyright 2013, French Ministry of National Education
 * http://fusionforge.org
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

require_once $gfcommon.'tracker/ArtifactTypes.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'pm/ProjectGroup.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'include/Role.class.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'mail/MailingList.class.php';
require_once $gfcommon.'mail/MailingListFactory.class.php';
require_once $gfcommon.'survey/SurveyFactory.class.php';
require_once $gfcommon.'survey/SurveyQuestionFactory.class.php';
require_once $gfcommon.'include/gettext.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$GROUP_OBJ=array();

/**
 * group_get_object() - Get the group object.
 *
 * group_get_object() is useful so you can pool group objects/save database queries
 * You should always use this instead of instantiating the object directly.
 *
 * You can now optionally pass in a db result handle. If you do, it re-uses that query
 * to instantiate the objects.
 *
 * IMPORTANT! That db result must contain all fields
 * from groups table or you will have problems
 *
 * @param	int		$group_id	Required
 * @param	int|bool	$res		Result set handle ("SELECT * FROM groups WHERE group_id=xx")
 * @return	Group|bool	A group object or false on failure
 */
function &group_get_object($group_id, $res = false) {
	//create a common set of group objects
	//saves a little wear on the database

	//automatically checks group_type and
	//returns appropriate object

	global $GROUP_OBJ;
	if (!isset($GROUP_OBJ["_".$group_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM groups WHERE group_id=$1', array($group_id));
		}
		if (!$res || db_numrows($res) < 1) {
			$GROUP_OBJ["_".$group_id."_"]=false;
		} else {
			/*
				check group type and set up object
			*/
			if (db_result($res,0,'type_id') == 1) {
				//project
				$GROUP_OBJ["_".$group_id."_"] = new Group($group_id, $res);
			} else {
				//invalid
				$GROUP_OBJ["_".$group_id."_"] = false;
			}
		}
	}
	return $GROUP_OBJ["_".$group_id."_"];
}

function &group_get_objects($id_arr) {
	global $GROUP_OBJ;

	// Note: if we don't do this, the result may be corrupted
	$fetch = array();
	$return = array();

	foreach ($id_arr as $id) {
		//
		// See if this ID already has been fetched in the cache
		//
		if (!isset($GROUP_OBJ["_".$id."_"])) {
			$fetch[] = $id;
		}
	}
	if (count($fetch) > 0) {
		$res=db_query_params('SELECT * FROM groups WHERE group_id = ANY ($1)',
					array(db_int_array_to_any_clause($fetch)));
		while ($arr = db_fetch_array($res)) {
			$GROUP_OBJ["_".$arr['group_id']."_"] = new Group($arr['group_id'],$arr);
		}
	}
	foreach ($id_arr as $id) {
		$return[] =& $GROUP_OBJ["_".$id."_"];
	}
	return $return;
}

function &group_get_active_projects() {
	$res = db_query_params('SELECT group_id FROM groups WHERE status=$1',
				array('A'));
	return group_get_objects(util_result_column_to_array($res,0));
}

function &group_get_all_projects() {
	$res = db_query_params ('SELECT group_id FROM groups',
				array());
	return group_get_objects(util_result_column_to_array($res,0));
}

function &group_get_template_projects() {
	$res = db_query_params('SELECT group_id FROM groups WHERE is_template=1 AND status != $1',
				array('D'));
	return group_get_objects(util_result_column_to_array($res,0));
}

function &group_get_object_by_name($groupname) {
	$res = db_query_params('SELECT * FROM groups WHERE unix_group_name=$1', array($groupname));
	return group_get_object(db_result($res, 0, 'group_id'), $res);
}

function &group_get_objects_by_name($groupname_arr) {
	$res = db_query_params('SELECT group_id FROM groups WHERE unix_group_name = ANY ($1)',
				array(db_string_array_to_any_clause($groupname_arr)));
	$arr =& util_result_column_to_array($res,0);
	return group_get_objects($arr);
}

function group_get_object_by_publicname($groupname) {
	$res = db_query_params('SELECT * FROM groups WHERE lower(group_name) LIKE $1',
				array(htmlspecialchars(html_entity_decode(strtolower($groupname)))));
	return group_get_object(db_result($res, 0, 'group_id'), $res);
}

/**
 * get_public_active_projects_asc() - Get a list of rows for public active projects (initially in trove/full_list)
 *
 * @param	int	$max_query_limit Optional Maximum number of rows to limit query length
 * @return	array	List of public active projects
 */
function get_public_active_projects_asc($max_query_limit = -1) {

	$res_grp = db_query_params ('
			SELECT group_id, group_name, unix_group_name, short_description, register_time
			FROM groups
			WHERE status = $1 AND type_id=1 AND is_template=0 AND register_time > 0
			ORDER BY group_name ASC
			',
			array('A'),
			$max_query_limit);
	$projects = array();
	while ($row_grp = db_fetch_array($res_grp)) {
		if (!forge_check_perm ('project_read', $row_grp['group_id'])) {
			continue;
		}
		$projects[] = $row_grp;
	}
	return $projects;
}


class Group extends Error {
	/**
	 * Associative array of data from db.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * array of User objects.
	 *
	 * @var	array	$membersArr.
	 */
	var $membersArr;

	/**
	 * Whether the use is an admin/super user of this project.
	 *
	 * @var	bool	$is_admin.
	 */
	var $is_admin;

	/**
	 * Artifact types result handle.
	 *
	 * @var	int	$types_res.
	 */
	var $types_res;

	/**
	 * Associative array of data for plugins.
	 *
	 * @var	array	$plugins_data.
	 */
	var $plugins_data;


	/**
	 * Associative array of data for the group menu.
	 *
	 * @var	array	$menu_data.
	 */
	var $menu_data;

	/**
	 * Group - Group object constructor - use group_get_object() to instantiate.
	 *
	 * @param	int|bool	$id	Required - Id of the group you want to instantiate.
	 * @param	int|bool	$res	Database result from select query OR associative array of all columns.
	 */
	function __construct($id = false, $res = false) {
		$this->Error();
		if (!$id) {
			//setting up an empty object
			//probably going to call create()
			return;
		}
		if (!$res) {
			if (!$this->fetchData($id)) {
				return;
			}
		} else {
			//
			//	Assoc array was passed in
			//
			if (is_array($res)) {
				$this->data_array =& $res;
			} else {
				if (db_numrows($res) < 1) {
					//function in class we extended
					$this->setError(_('Group Not Found'));
					$this->data_array=array();
					return;
				} else {
					//set up an associative array for use by other functions
					$this->data_array = db_fetch_array_by_row($res, 0);
				}
			}
		}

	}

	/**
	 * fetchData - May need to refresh database fields if an update occurred.
	 *
	 * @param	int	$group_id The group_id.
	 * @return	boolean	success or not
	 */
	function fetchData($group_id) {
		$res = db_query_params ('SELECT * FROM groups WHERE group_id=$1',
					array($group_id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(sprintf('fetchData():: %s', db_error()));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		return true;
	}

	/**
	 * create - Create new group.
	 *
	 * This method should be called on empty Group object.
	 * It will add an entry for a pending group/project (status 'P')
	 *
	 * @param	object	$user			The User object.
	 * @param	string	$group_name		The full name of the user.
	 * @param	string	$unix_name		The Unix name of the user.
	 * @param	string	$description		The new group description.
	 * @param	string	$purpose		The purpose of the group.
	 * @param	string	$unix_box
	 * @param	string	$scm_box
	 * @param	bool	$is_public
	 * @param	bool	$send_mail		Whether to send an email or not
	 * @param	int	$built_from_template	The id of the project this new project is based on
	 * @return	boolean	success or not
	 */
	function create(&$user, $group_name, $unix_name, $description, $purpose, $unix_box = 'shell1',
			$scm_box = 'cvs1', $is_public = true, $send_mail = true, $built_from_template = 0) {
		// $user is ignored - anyone can create pending group

		global $SYS;
		if ($this->getID()!=0) {
			$this->setError(_('Group object already exists.'));
			return false;
		} elseif (!$this->validateGroupName($group_name)) {
			return false;
		} elseif (!account_groupnamevalid($unix_name)) {
			$this->setError(_('Invalid Unix Name.'));
			return false;
		} elseif (!$SYS->sysUseUnixName($unix_name)) {
			$this->setError(_('Unix name already taken.'));
			return false;
		} elseif (db_numrows(db_query_params('SELECT group_id FROM groups WHERE unix_group_name=$1',
							array($unix_name))) > 0) {
			$this->setError(_('Unix name already taken.'));
			return false;
		} elseif (strlen($purpose)<10) {
			$this->setError(_('Please describe your Registration Project Purpose and Summarization in a more comprehensive manner.'));
			return false;
		} elseif (strlen($purpose)>1500) {
			$this->setError(_('The Registration Project Purpose and Summarization text is too long. Please make it smaller than 1500 characters.'));
			return false;
		} elseif (strlen($description)<10) {
			$this->setError(_('Describe in a more comprehensive manner your project.'));
			return false;
		} else {

			// Check if sys_use_project_vhost for homepage
			if (forge_get_config('use_project_vhost')) {
				$homepage = $unix_name.".".forge_get_config('web_host');
			} else {
				$homepage = forge_get_config('web_host')."/www/".$unix_name."/";
			}

			db_begin();

			$res = db_query_params('
				INSERT INTO groups(
					group_name,
					unix_group_name,
					short_description,
					http_domain,
					homepage,
					status,
					unix_box,
					scm_box,
					register_purpose,
					register_time,
					rand_hash,
					built_from_template
				)
				VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)',
						array(htmlspecialchars($group_name),
							$unix_name,
							htmlspecialchars($description),
							$homepage,
							$homepage,
							'P',
							$unix_box,
							$scm_box,
							htmlspecialchars($purpose),
							time(),
							md5(util_randbytes()),
							$built_from_template));
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError(sprintf(_('Error: Cannot create group: %s'),db_error()));
				db_rollback();
				return false;
			}

			$id = db_insertid($res, 'groups', 'group_id');
			if (!$id) {
				$this->setError(sprintf(_('Error: Cannot get group id: %s'),db_error()));
				db_rollback();
				return false;
			}

			if (!$this->fetchData($id)) {
				db_rollback();
				return false;
			}

			$gjr = new GroupJoinRequest($this);
			$gjr->create($user->getID(),
					'Fake GroupJoinRequest to store the creator of a project',
					false);

			$hook_params = array();
			$hook_params['group'] = $this;
			$hook_params['group_id'] = $this->getID();
			$hook_params['group_name'] = $group_name;
			$hook_params['unix_group_name'] = $unix_name;
			plugin_hook("group_create", $hook_params);

			db_commit();
			if ($send_mail) {
				$this->sendNewProjectNotificationEmail();
			}
			return true;
		}
	}


	/**
	 * updateAdmin - Update core properties of group object.
	 *
	 * This function require site admin privilege.
	 *
	 * @param	object	$user		User requesting operation (for access control).
	 * @param	int	$type_id	Group type (1-project, 2-foundry).
	 * @param	string	$unix_box	Machine on which group's home directory located.
	 * @param	string	$http_domain	Domain which serves group's WWW.
	 * @return	bool	status.
	 * @access	public
	 */
	function updateAdmin(&$user, $type_id, $unix_box, $http_domain) {
		$perm =& $this->getPermission();

		if (!$perm || !is_object($perm)) {
			$this->setError(_('Could not get permission.'));
			return false;
		}

		if (!$perm->isSuperUser()) {
			$this->setError(_('Permission denied.'));
			return false;
		}

		db_begin();

		$res = db_query_params('
			UPDATE groups
			SET type_id=$1, unix_box=$2, http_domain=$3
			WHERE group_id=$4',
					array($type_id,
						$unix_box,
						$http_domain,
						$this->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error: Cannot change group properties: %s'),db_error());
			db_rollback();
			return false;
		}

		// Log the audit trail
		if ($type_id != $this->data_array['type_id']) {
			$this->addHistory('type_id', $this->data_array['type_id']);
		}
		if ($unix_box != $this->data_array['unix_box']) {
			$this->addHistory('unix_box', $this->data_array['unix_box']);
		}
		if ($http_domain != $this->data_array['http_domain']) {
			$this->addHistory('http_domain', $this->data_array['http_domain']);
		}

		if (!$this->fetchData($this->getID())) {
			db_rollback();
			return false;
		}
		db_commit();
		return true;
	}

	/**
	 * update - Update number of common properties.
	 *
	 * Unlike updateAdmin(), this function accessible to project admin.
	 *
	 * @param	object	$user			User requesting operation (for access control).
	 * @param	string	$group_name
	 * @param	string	$homepage
	 * @param	string	$short_description
	 * @param	bool	$use_mail
	 * @param	bool	$use_survey
	 * @param	bool	$use_forum
	 * @param	bool	$use_pm
	 * @param	bool	$use_pm_depend_box
	 * @param	bool	$use_scm
	 * @param	bool	$use_news
	 * @param	bool	$use_docman
	 * @param	string	$new_doc_address
	 * @param	bool	$send_all_docs
	 * @param	int	$logo_image_id
	 * @param	bool	$use_ftp
	 * @param	bool	$use_tracker
	 * @param	bool	$use_frs
	 * @param	bool	$use_stats
	 * @param	string	$tags
	 * @param	bool	$use_activity
	 * @param	bool	$is_public		group is publicly accessible
	 * @return	int    status.
	 * @access    public
	 */
	function update(&$user, $group_name, $homepage, $short_description, $use_mail, $use_survey, $use_forum,
		$use_pm, $use_pm_depend_box, $use_scm, $use_news, $use_docman,
		$new_doc_address, $send_all_docs, $logo_image_id,
		$use_ftp, $use_tracker, $use_frs, $use_stats, $tags, $use_activity, $is_public) {

		$perm =& $this->getPermission();

		if (!$perm || !is_object($perm)) {
			$this->setError(_('Could not get permission.'));
			return false;
		}

		if (!$perm->isAdmin()) {
			$this->setError(_('Permission denied.'));
			return false;
		}

		// Validate some values
		if ($this->getPublicName() != htmlspecialchars($group_name)) {
			if (!$this->validateGroupName($group_name)) {
				return false;
			}
		}

		if ($new_doc_address) {
			$invalid_mails = validate_emails($new_doc_address);
			if (count($invalid_mails) > 0) {
				$this->setError(sprintf(ngettext('New Doc Address Appeared Invalid: %s', 'New Doc Addresses Appeared Invalid: %s', count($invalid_mails)),implode(',',$invalid_mails)));
				return false;
			}
		}

		// in the database, these all default to '1',
		// so we have to explicitly set 0
		if (!$use_mail) {
			$use_mail = 0;
		}
		if (!$use_survey) {
			$use_survey = 0;
		}
		if (!$use_forum) {
			$use_forum = 0;
		}
		if (!$use_pm) {
			$use_pm = 0;
		}
		if (!$use_pm_depend_box) {
			$use_pm_depend_box = 0;
		}
		if (!$use_scm) {
			$use_scm = 0;
		}
		if (!$use_news) {
			$use_news = 0;
		}
		if (!$use_docman) {
			$use_docman = 0;
		}
		if (!$use_ftp) {
			$use_ftp = 0;
		}
		if (!$use_tracker) {
			$use_tracker = 0;
		}
		if (!$use_frs) {
			$use_frs = 0;
		}
		if (!$use_stats) {
			$use_stats = 0;
		}
		if (!$use_activity) {
			$use_activity = 0;
		}
		if (!$send_all_docs) {
			$send_all_docs = 0;
		}

		$homepage = ltrim($homepage);
		if (!$homepage) {
			$homepage = util_make_url('/projects/' . $this->getUnixName() . '/');
		}

		if (strlen(htmlspecialchars($short_description))<10) {
			$this->setError(_('Describe in a more comprehensive manner your project.'));
			return false;
		}

		db_begin();

		//XXX not yet actived logo_image_id='$logo_image_id',
		$res = db_query_params('UPDATE groups
			SET group_name=$1,
				homepage=$2,
				short_description=$3,
				use_mail=$4,
				use_survey=$5,
				use_forum=$6,
				use_pm=$7,
				use_pm_depend_box=$8,
				use_scm=$9,
				use_news=$10,
				new_doc_address=$11,
				send_all_docs=$12,
				use_ftp=$13,
				use_tracker=$14,
				use_frs=$15,
				use_stats=$16,
				use_activity=$17
			WHERE group_id=$18',
					array(htmlspecialchars($group_name),
						$homepage,
						htmlspecialchars($short_description),
						$use_mail,
						$use_survey,
						$use_forum,
						$use_pm,
						$use_pm_depend_box,
						$use_scm,
						$use_news,
						$new_doc_address,
						$send_all_docs,
						$use_ftp,
						$use_tracker,
						$use_frs,
						$use_stats,
						$use_activity,
						$this->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(sprintf(_('Error updating project information: %s'), db_error()));
			db_rollback();
			return false;
		}

		if (!$this->setUseDocman($use_docman)) {
			$this->setError(sprintf(_('Error updating project information: use_docman %s'), db_error()));
			db_rollback();
			return false;
		}

		if ($this->setTags($tags) === false) {
			db_rollback();
			return false;
		}

		// Log the audit trail
		$this->addHistory('Changed Public Info', '');

		if (!$this->fetchData($this->getID())) {
			db_rollback();
			return false;
		}

		$hook_params = array();
		$hook_params['group'] = $this;
		$hook_params['group_id'] = $this->getID();
		$hook_params['group_homepage'] = $homepage;
		$hook_params['group_name'] = htmlspecialchars($group_name);
		$hook_params['group_description'] = htmlspecialchars($short_description);
		$hook_params['group_ispublic'] = $is_public;
		if (!plugin_hook("group_update", $hook_params)) {
			if (!$this->isError()) {
				$this->setError(_('Error updating project information in plugin_hook group_update'));
			}
			db_rollback();
			return false;
		}

		db_commit();
		return true;
	}

	/**
	 * getID - Simply return the group_id for this object.
	 *
	 * @return int group_id.
	 */
	function getID() {
		return $this->data_array['group_id'];
	}

	/**
	 * getType() - Foundry, project, etc.
	 *
	 * @return	int	The type flag from the database.
	 */
	function getType() {
		return $this->data_array['type_id'];
	}


	/**
	 * getStatus - the status code.
	 *
	 * Statuses	char	include I,H,A,D,P.
	 *   A: Active
	 *   H: Hold
	 *   P: Pending
	 *   I: Incomplete
	 *   D: Deleted
	 */
	function getStatus() {
		return $this->data_array['status'];
	}

	/**
	 * setStatus - set the status code.
	 *
	 * Statuses include I,H,A,D,P.
	 *   A: Active
	 *   H: Hold
	 *   P: Pending
	 *   I: Incomplete
	 *   D: Deleted
	 *
	 * @param	object	$user	User requesting operation (for access control).
	 * @param	string	$status	Status value.
	 * @return	boolean	success.
	 * @access	public
	 */
	function setStatus(&$user, $status) {
		global $SYS;

		if (!forge_check_global_perm_for_user($user, 'approve_projects')) {
			$this->setPermissionDeniedError();
			return false;
		}

		//	Projects in 'A' status can only go to 'H' or 'D'
		//	Projects in 'D' status can only go to 'A'
		//	Projects in 'P' status can only go to 'A' OR 'D'
		//	Projects in 'I' status can only go to 'P'
		//	Projects in 'H' status can only go to 'A' OR 'D'
		$allowed_status_changes = array(
			'AH'=>1,'AD'=>1,'DA'=>1,'PA'=>1,'PD'=>1,
			'IP'=>1,'HA'=>1,'HD'=>1
		);

		// Check that status transition is valid
		if ($this->getStatus() != $status
			&& !array_key_exists($this->getStatus(). $status, $allowed_status_changes)) {
			$this->setError(_('Invalid Status Change From: ').$this->getStatus(). _(' To: '.$status));
			return false;
		}

		db_begin();

		$res = db_query_params('UPDATE groups
			SET status=$1
			WHERE group_id=$2', array($status, $this->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(sprintf(_('Error: Cannot change group status: %s'),db_error()));
			db_rollback();
			return false;
		}

		if ($status=='A') {
			// Activate system group, if not yet
			if (!$SYS->sysCheckGroup($this->getID())) {
				if (!$SYS->sysCreateGroup($this->getID())) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			}
			if (!$this->activateUsers()) {
				db_rollback();
				return false;
			}

		/* Otherwise, the group is not active, and make sure that
		   System group is not active either */
		} elseif ($SYS->sysCheckGroup($this->getID())) {
			if (!$SYS->sysRemoveGroup($this->getID())) {
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
		}

		$hook_params = array();
		$hook_params['group'] = $this;
		$hook_params['group_id'] = $this->getID();
		$hook_params['status'] = $status;
		plugin_hook("group_setstatus", $hook_params);

		db_commit();

		// Log the audit trail
		if ($status != $this->getStatus()) {
			$this->addHistory(_('Status'), $this->getStatus());
		}

		$this->data_array['status'] = $status;
		return true;
	}

	/**
	 * isProject - Simple boolean test to see if it's a project or not.
	 *
	 * @return	boolean	is_project.
	 */
	function isProject() {
		if ($this->getType()==1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * isPublic - Wrapper around RBAC to check if a project is anonymously readable
	 *
	 * @return	boolean	is_public.
	 */
	function isPublic() {
		$ra = RoleAnonymous::getInstance();
		return $ra->hasPermission('project_read', $this->getID());
	}

	/**
	 * isActive - Database field status of 'A' returns true.
	 *
	 * @return	boolean	is_active.
	 */
	function isActive() {
		if ($this->getStatus()=='A') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * isTemplate - Simply returns the is_template flag from the database.
	 *
	 * @return	boolean	is_template.
	 */
	function isTemplate() {
		return $this->data_array['is_template'];
	}

	/**
	 * setAsTemplate - Set the template status of a project
	 *
	 * @param	boolean	$booleanparam	is_template.
	 * @return	bool
	 */
	function setAsTemplate($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET is_template=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['is_template']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * getTemplateProject - Return the project template this project is built from
	 *
	 * @return	object	The template project
	 */
	function getTemplateProject() {
		return group_get_object($this->data_array['built_from_template']);
	}

	/**
	 *  getUnixName - the unix_name
	 *
	 * @return	string	unix_name.
	 */
	function getUnixName() {
		return strtolower($this->data_array['unix_group_name']);
	}

	/**
	 * getPublicName - the full-length public name.
	 *
	 * @return	string	The group_name.
	 */
	function getPublicName() {
		return $this->data_array['group_name'];
	}

	/**
	 * getRegisterPurpose - the text description of the purpose of this project.
	 *
	 * @return	string	The description.
	 */
	function getRegisterPurpose() {
		return $this->data_array['register_purpose'];
	}

	/**
	 * getDescription - the text description of this project.
	 *
	 * @return	string	The description.
	 */
	function getDescription() {
		return $this->data_array['short_description'];
	}

	/**
	 * getStartDate - the unix time this project was registered.
	 *
	 * @return	int	(unix time) of registration.
	 */
	function getStartDate() {
		return $this->data_array['register_time'];
	}

	/**
	 * getLogoImageID - the id of the logo in the database for this project.
	 *
	 * @return	int	The ID of logo image in db_images table (or 100 if none).
	 */
	function getLogoImageID() {
		return $this->data_array['logo_image_id'];
	}

	/**
	 * getUnixBox - the hostname of the unix box where this project is located.
	 *
	 * @return	string	The name of the unix machine for the group.
	 */
	function getUnixBox() {
		return $this->data_array['unix_box'];
	}

	/**
	 * getSCMBox - the hostname of the scm box where this project is located.
	 *
	 * @return	string	The name of the unix machine for the group.
	 */
	function getSCMBox() {
		return $this->data_array['scm_box'];
	}
	/**
	 * setSCMBox - the hostname of the scm box where this project is located.
	 *
	 * @param	string	$scm_box	The name of the new SCM_BOX
	 * @return	bool
	 */
	function setSCMBox($scm_box) {

		if ($scm_box == $this->data_array['scm_box']) {
			return true;
		}
		if ($scm_box) {
			db_begin();
			$res = db_query_params('UPDATE groups SET scm_box=$1 WHERE group_id=$2', array($scm_box, $this->getID()));
			if ($res) {
				$this->addHistory('scm_box', $this->data_array['scm_box']);
				$this->data_array['scm_box'] = $scm_box;
				db_commit();
				return true;
			} else {
				db_rollback();
				$this->setError(_("Could not insert SCM_BOX to database"));
				return false;
			}
		} else {
			$this->setError(_("SCM Box cannot be empty"));
			return false;
		}
	}

	/**
	 * getDomain - the hostname.domain where their web page is located.
	 *
	 * @return	string	The name of the group [web] domain.
	 */
	function getDomain() {
		return $this->data_array['http_domain'];
	}

	/**
	 * getRegistrationPurpose - the text description of the purpose of this project.
	 *
	 * @return	string	The application for project hosting.
	 */
	function getRegistrationPurpose() {
		return $this->data_array['register_purpose'];
	}

	/**
	 * getAdmins - Get array of Admin user objects.
	 *
	 * @return	array	Array of User objects.
	 */
	function &getAdmins() {
		$roles = RBACEngine::getInstance()->getRolesByAllowedAction ('project_admin', $this->getID());

		$user_ids = array();

		foreach ($roles as $role) {
			if (! ($role instanceof RoleExplicit)) {
				continue;
			}
			if ($role->getHomeProject() == NULL
				|| $role->getHomeProject()->getID() != $this->getID()) {
				continue;
			}

			foreach ($role->getUsers() as $u) {
				$user_ids[] = $u->getID();
			}
		}
		$active_ids = array();
		$ids = array_unique ($user_ids);
		foreach ($ids as $id) {
			$u = user_get_object ($id);
			if ($u->isActive()) {
				$active_ids[] = $u;
			}
		}
		return $active_ids;
	}

	/*
		Common Group preferences for tools
	*/

	/**
	 * enableAnonSCM - whether or not this group has opted to enable Anonymous SCM.
	 *
	 * @return	boolean	enable_scm.
	 */
	function enableAnonSCM() {
		$r = RoleAnonymous::getInstance();
		return $r->hasPermission('scm', $this->getID(), 'read');
	}

	function SetUsesAnonSCM($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$r = RoleAnonymous::getInstance();
		$r->setSetting('scm', $this->getID(), $booleanparam);
		db_commit();
	}

	/**
	 * enablePserver - whether or not this group has opted to enable Pserver.
	 *
	 * @return	boolean	enable_pserver.
	 */
	function enablePserver() {
		if ($this->usesSCM()) {
			return $this->data_array['enable_pserver'];
		} else {
			return false;
		}
	}

	function SetUsesPserver($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET enable_pserver=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['enable_pserver'] = $booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * usesSCM - whether or not this group has opted to use SCM.
	 *
	 * @return	boolean	uses_scm.
	 */
	function usesSCM() {
		if (forge_get_config('use_scm')) {
			return $this->data_array['use_scm'];
		} else {
			return false;
		}
	}

	/**
	 * setUseSCM - Set the SCM usage
	 *
	 * @param	boolean	$booleanparam	enabled/disabled
	 * @return	bool
	 */
	function setUseSCM($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET use_scm=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['use_scm']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * usesMail - whether or not this group has opted to use mailing lists.
	 *
	 * @return	boolean	uses_mail.
	 */
	function usesMail() {
		if (forge_get_config('use_mail')) {
			return $this->data_array['use_mail'];
		} else {
			return false;
		}

		$hook_params = array();
		$hook_params['group'] = $this;
		$hook_params['group_id'] = $this->getID();
		$hook_params['group_homepage'] = $this->getHomePage();
		$hook_params['group_name'] = $this->getPublicName();
		$hook_params['group_description'] = $this->getDescription();
		plugin_hook ("group_update", $hook_params);
	}

	/**
	 * setUseMail - Set the mailing-list usage
	 *
	 * @param	boolean	$booleanparam	enabled/disabled
	 * @return	bool
	 */
	function setUseMail($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET use_mail=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['use_mail']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * usesNews - whether or not this group has opted to use news.
	 *
	 * @return	boolean	uses_news.
	 */
	function usesNews() {
		if (forge_get_config('use_news')) {
			return $this->data_array['use_news'];
		} else {
			return false;
		}
	}

	/**
	 * usesActivity - whether or not this group has opted to display Project Activities.
	 *
	 * @return	boolean	uses_activities.
	 */
	function usesActivity() {
		if (forge_get_config('use_activity')) {
			return $this->data_array['use_activity'];
		} else {
			return false;
		}
	}

	/**
	 * usesForum - whether or not this group has opted to use discussion forums.
	 *
	 * @return	boolean	uses_forum.
	 */
	function usesForum() {
		if (forge_get_config('use_forum')) {
			return $this->data_array['use_forum'];
		} else {
			return false;
		}
	}

	/**
	 * setUseForum - Set the forum usage
	 *
	 * @param	boolean	$booleanparam	enabled/disabled
	 * @return	bool
	 */
	function setUseForum($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET use_forum=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['use_forum']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * usesStats - whether or not this group has opted to use stats.
	 *
	 * @return	boolean	uses_stats.
	 */
	function usesStats() {
		return $this->data_array['use_stats'];
	}

	/**
	 * usesFRS - whether or not this group has opted to use file release system.
	 *
	 * @return	boolean	uses_frs.
	 */
	function usesFRS() {
		if (forge_get_config('use_frs')) {
			return $this->data_array['use_frs'];
		} else {
			return false;
		}
	}

	/**
	 * setUseFRS - Set the FRS usage
	 *
	 * @param	boolean	$booleanparam	enabled/disabled
	 * @return	bool
	 */
	function setUseFRS($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET use_frs=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['use_frs']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * usesTracker - whether or not this group has opted to use tracker.
	 *
	 * @return	boolean	uses_tracker.
	 */
	function usesTracker() {
		if (forge_get_config('use_tracker')) {
			return $this->data_array['use_tracker'];
		} else {
			return false;
		}
	}

	/**
	 * setUseTracker - Set the tracker usage
	 *
	 * @param	boolean	$booleanparam	enabled/disabled
	 * @return	bool
	 */
	function setUseTracker($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params ('UPDATE groups SET use_tracker=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['use_tracker']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 *  useCreateOnline - whether or not this group has opted to use create online documents option.
	 *
	 * @return	boolean	use_docman_create_online.
	 */
	function useCreateOnline() {
		if (forge_get_config('use_docman')) {
			return $this->data_array['use_docman_create_online'];
		} else {
			return false;
		}
	}

	/**
	 * usesDocman - whether or not this group has opted to use docman.
	 *
	 * @return	boolean	use_docman.
	 */
	function usesDocman() {
		if (forge_get_config('use_docman')) {
			return $this->data_array['use_docman'];
		} else {
			return false;
		}
	}

	/**
	 *	setUseDocman - Set the docman usage
	 *
	 *	@param	boolean	$booleanparam	enabled/disabled
	 * @return bool
	 */
	function setUseDocman($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET use_docman = $1 WHERE group_id = $2',
					array($booleanparam, $this->getID()));
		if ($res) {
			// check if / doc_group exists, if not create it
			$trashdir = db_query_params('select groupname from doc_groups where groupname = $1 and group_id = $2',
							array('.trash', $this->getID()));
			if ($trashdir && db_numrows($trashdir) == 0) {
				$resinsert = db_query_params('insert into doc_groups (groupname, group_id, stateid) values ($1, $2, $3)',
						array('.trash', $this->getID(), '2'));
				if (!$resinsert) {
					db_rollback();
					return false;
				}
			}
			$this->data_array['use_docman'] = $booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 *  useDocmanSearch - whether or not this group has opted to use docman search engine.
	 *
	 * @return	boolean	use_docman_search.
	 */
	function useDocmanSearch() {
		if (forge_get_config('use_docman')) {
			return $this->data_array['use_docman_search'];
		} else {
			return false;
		}
	}

	/**
	 * useWebdav - whether or not this group has opted to use webdav interface.
	 *
	 * @return	boolean	use_docman_search.
	 */
	function useWebdav() {
		if (forge_get_config('use_webdav')) {
			return $this->data_array['use_webdav'];
		} else {
			return false;
		}
	}

	/**
	 * usesFTP - whether or not this group has opted to use FTP.
	 *
	 * @return	boolean	uses_ftp.
	 */
	function usesFTP() {
		if (forge_get_config('use_ftp')) {
			return $this->data_array['use_ftp'];
		} else {
			return false;
		}
	}

	/**
	 * usesSurvey - whether or not this group has opted to use surveys.
	 *
	 * @return	boolean	uses_survey.
	 */
	function usesSurvey() {
		if (forge_get_config('use_survey')) {
			return $this->data_array['use_survey'];
		} else {
			return false;
		}
	}

	/**
	 * usesPM - whether or not this group has opted to Project Manager.
	 *
	 * @return	boolean	uses_projman.
	 */
	function usesPM() {
		if (forge_get_config('use_pm')) {
			return $this->data_array['use_pm'];
		} else {
			return false;
		}
	}

	/**
	 *	setUsePM - Set the PM usage
	 *
	 *	@param	boolean	$booleanparam	enabled/disabled
	 * @return bool
	 */
	function setUsePM($booleanparam) {
		db_begin();
		$booleanparam = $booleanparam ? 1 : 0;
		$res = db_query_params('UPDATE groups SET use_pm=$1 WHERE group_id=$2',
					array($booleanparam, $this->getID()));
		if ($res) {
			$this->data_array['use_pm']=$booleanparam;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 *  getPlugins -  get a list of all available group plugins
	 *
	 * @return	array	array containing plugin_id => plugin_name
	 */
	function getPlugins() {
		if (!isset($this->plugins_data)) {
			$this->plugins_data = array();
			$res = db_query_params('SELECT group_plugin.plugin_id, plugins.plugin_name
						FROM group_plugin, plugins
						WHERE group_plugin.group_id=$1
						AND group_plugin.plugin_id=plugins.plugin_id', array($this->getID()));
			$rows = db_numrows($res);

			for ($i=0; $i<$rows; $i++) {
				$plugin_id = db_result($res, $i, 'plugin_id');
				$this->plugins_data[$plugin_id] = db_result($res, $i, 'plugin_name');
			}
		}
		return $this->plugins_data;
	}

	/**
	 * usesPlugin - returns true if the group uses a particular plugin
	 *
	 * @param	string	$pluginname name of the plugin
	 * @return	boolean	whether plugin is being used or not
	 */
	function usesPlugin($pluginname) {
		$plugins_data = $this->getPlugins();
		foreach ($plugins_data as $p_id => $p_name) {
			if ($p_name == $pluginname) {
				return true;
			}
		}
		return false;
	}

	/**
	 * added for Codendi compatibility
	 * usesServices - returns true if the group uses a particular plugin or feature
	 *
	 * @param	string	$feature    name of the plugin
	 * @return	boolean	whether plugin is being used or not
	 */
	function usesService($feature) {
		$plugins_data = $this->getPlugins();
		$pm = plugin_manager_get_object();
		foreach ($plugins_data as $p_id => $p_name) {
			if ($p_name == $feature) {
				return true;
			}
			if ($pm->getPluginByName($p_name)->provide($feature)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * setPluginUse - enables/disables plugins for the group
	 *
	 * @param	string	$pluginname	name of the plugin
	 * @param	boolean	$val		the new state
	 * @return	string	database result
	 */
	function setPluginUse($pluginname, $val=true) {
		if ($val == $this->usesPlugin($pluginname)) {
			// State is already good, returning
			return true;
		}
		$res = db_query_params('SELECT plugin_id FROM plugins WHERE plugin_name=$1',
					array($pluginname));
		$rows = db_numrows($res);
		if ($rows == 0) {
			// Error: no plugin by that name
			return false;
		}
		$plugin_id = db_result($res,0,'plugin_id');
		// Invalidate cache
		unset($this->plugins_data);
		if ($val) {
			$res = db_query_params('INSERT INTO group_plugin (group_id, plugin_id) VALUES ($1, $2)',
						array($this->getID(),
							$plugin_id));
		} else {
			$res = db_query_params('DELETE FROM group_plugin WHERE group_id=$1 AND plugin_id=$2',
						array($this->getID(),
							$plugin_id));
		}
		$this->normalizeAllRoles();
		return $res;
	}

	/**
	 * getDocEmailAddress - get email address(es) to send doc notifications to.
	 *
	 * @return	string	email address.
	 */
	function getDocEmailAddress() {
		return $this->data_array['new_doc_address'];
	}

	/**
	 * DocEmailAll - whether or not this group has opted to use receive notices on all doc updates.
	 *
	 * @return	boolean	email_on_all_doc_updates.
	 */
	function docEmailAll() {
		return $this->data_array['send_all_docs'];
	}

	/**
	 * getHomePage - The URL for this project's home page.
	 *
	 * @return	string	homepage URL.
	 */
	function getHomePage() {
		if (!preg_match("/^[a-zA-Z][a-zA-Z0-9+.-]*:/",
			$this->data_array['homepage'])) {
			$this->data_array['homepage'] = util_url_prefix() .
				$this->data_array['homepage'];
		}
		return $this->data_array['homepage'];
	}

	/**
	 * getTags - Tags of this project.
	 *
	 * @return	string	List of tags. Comma separated
	 */
	function getTags() {
		$sql = 'SELECT name FROM project_tags WHERE group_id = $1';
		$res = db_query_params($sql, array($this->getID()));
		return join(', ', util_result_column_to_array($res));
	}

	/**
	 * setTags - Set tags of this project.
	 *
	 * @param	string	$tags
	 * @return	string	database result.
	 */
	function setTags($tags) {
		db_begin();
		$sql = 'DELETE FROM project_tags WHERE group_id=$1';
		$res = db_query_params($sql, array($this->getID()));
		if (!$res) {
			$this->setError('Deleting old tags: '.db_error());
			db_rollback();
			return false;
		}
		$inserted = array();
		$tags_array = preg_split('/[;,]/', $tags);
		foreach ($tags_array as $tag) {
			$tag = preg_replace('/[\t\r\n]/', ' ', $tag);
			// Allowed caracteres: [A-Z][a-z][0-9] -_&'#+.
			if (preg_match('/[^[:alnum:]| |\-|_|\&|\'|#|\+|\.]/', $tag)) {
				$this->setError(_('Bad tag name, you only can use the following characters: [A-Z][a-z][0-9]-_&\'#+. and space'));
				db_rollback();
				return false;
			}
			$tag = trim($tag);
			if ($tag == '' || array_search($tag, $inserted) !== false) continue;
			$sql = 'INSERT INTO project_tags (group_id,name) VALUES ($1, $2)';
			$res = db_query_params($sql, array($this->getID(), $tag));
			if (!$res) {
				$this->setError(_('Setting tags:') . ' ' .
					db_error());
				db_rollback();
				return false;
			}
			$inserted[] = $tag;
		}
		db_commit();
		return true;
	}

	/**
	 * getPermission - Return a Permission for this Group
	 *
	 * @return	object	The Permission.
	 */
	function &getPermission() {
		return permission_get_object($this);
	}

	function delete($sure, $really_sure, $really_really_sure) {
		if (!$sure || !$really_sure || !$really_really_sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if ($this->getID() == forge_get_config('news_group') ||
			$this->getID() == 1 ||
			$this->getID() == forge_get_config('stats_group') ||
			$this->getID() == forge_get_config('peer_rating_group')) {
			$this->setError(_('Cannot Delete System Group'));
			return false;
		}
		$perm = $this->getPermission();
		if (!$perm || !is_object($perm)) {
			$this->setPermissionDeniedError();
			return false;
		} elseif ($perm->isError()) {
			$this->setPermissionDeniedError();
			return false;
		} elseif (!$perm->isSuperUser()) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();
		//
		//	Remove all the members
		//
		$members = $this->getMembers();
		foreach ($members as $i) {
			if(!$this->removeUser($i->getID())) {
				$this->setError(_('Could not properly remove member:').' '.$i->getID());
				return false;
			}
		}

		// unlink roles from this project
		foreach ($this->getRoles() as $r) {
			if ($r->getHomeProject() == NULL
				|| $r->getHomeProject()->getID() != $this->getID()) {
				$r->unlinkProject($this);
			}
		}

		//
		//	Delete Trackers
		//
		if ($this->usesTracker()) {
			$atf = new ArtifactTypeFactory($this);
			$at_arr = $atf->getArtifactTypes();
			foreach ($at_arr as $i) {
				if (!is_object($i)) {
					continue;
				}
				if (!$i->delete(1,1)) {
					$this->setError(_('Could not properly delete the tracker:').' '.$i->getErrorMessage());
					return false;
				}
			}
		}
		//
		//	Delete Forums
		//

		if ($this->usesForum()) {
			$ff = new ForumFactory($this);
			$f_arr = $ff->getForums();
			foreach ($f_arr as $i) {
				if (!is_object($i)) {
					continue;
				}
				if(!$i->delete(1,1)) {
					$this->setError(_('Could not properly delete the forum:').' '.$i->getErrorMessage());
					return false;
				}
			}
		}
		//
		//	Delete Subprojects
		//
		if ($this->usesPM()) {
			$pgf = new ProjectGroupFactory($this);
			$pg_arr = $pgf->getProjectGroups();
			foreach ($pg_arr as $i) {
				if (!is_object($i)) {
					continue;
				}
				if (!$i->delete(1,1)) {
					$this->setError(_('Could not properly delete the ProjectGroup:').' '.$i->getErrorMessage());
					return false;
				}
			}
		}
		//
		//	Delete FRS Packages
		//
		$res = db_query_params('SELECT * FROM frs_package WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error FRS Packages: ').db_error());
			db_rollback();
			return false;
		}

		while ($arr = db_fetch_array($res)) {
			$frsp=new FRSPackage($this, $arr['package_id'], $arr);
			if (!$frsp->delete(1, 1)) {
				$this->setError(_('Could not properly delete the FRSPackage:').' '.$frsp->getErrorMessage());
				return false;
			}
		}
		//
		//	Delete news
		//
		$news_group=group_get_object(forge_get_config('news_group'));
		$res = db_query_params('SELECT forum_id FROM news_bytes WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting News: ').db_error());
			db_rollback();
			return false;
		}

		for ($i=0; $i<db_numrows($res); $i++) {
			$Forum = new Forum($news_group,db_result($res,$i,'forum_id'));
			if (!$Forum->delete(1,1)) {
				$this->setError(_("Could Not Delete News Forum: %d"),$Forum->getID());
				return false;
			}
		}
		$res = db_query_params('DELETE FROM news_bytes WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting News: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete docs
		//
		$res = db_query_params('DELETE FROM doc_data WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Documents: ').db_error());
			db_rollback();
			return false;
		}

		$res = db_query_params('DELETE FROM doc_groups WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Documents: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete Tags
		//
		$res=db_query_params('DELETE FROM project_tags WHERE group_id=$1', array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Tags: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete group history
		//
		$res = db_query_params('DELETE FROM group_history WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Project History: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete group plugins
		//
		$res = db_query_params('DELETE FROM group_plugin WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Project Plugins: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete group cvs stats
		//
		$res = db_query_params ('DELETE FROM stats_cvs_group WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting SCM Statistics: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete Surveys
		//
		if ($this->usesSurvey()) {
			$sf = new SurveyFactory($this);
			$s_arr =& $sf->getSurveys();
			foreach ($s_arr as $i) {
				if (!is_object($i)) {
					continue;
				}
				if (!$i->delete()) {
					$this->setError(_('Could not properly delete the survey'));
					db_rollback();
					return false;
				}
			}
		//
		//	Delete SurveyQuestions
		//
			$sqf = new SurveyQuestionFactory($this);
			$sq_arr = $sqf->getSurveyQuestions();
			if (is_array($sq_arr)) {
				foreach ($sq_arr as $i) {
					if (!is_object($i)) {
						continue;
					}
					if (!$i->delete()) {
						$this->setError(_('Could not properly delete the survey questions'));
						db_rollback();
						return false;
					}
				}
			}
		}
		//
		//	Delete Mailing List Factory
		//
		if ($this->usesMail()) {
			$mlf = new MailingListFactory($this);
			$ml_arr = $mlf->getMailingLists();
			foreach ($ml_arr as $i) {
				if (!is_object($i)) {
					continue;
				}
				if (!$i->delete(1,1)) {
					$this->setError(_('Could not properly delete the mailing list'));
					db_rollback();
					return false;
				}
			}
		}
		//
		//	Delete trove
		//
		$res = db_query_params('DELETE FROM trove_group_link WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Trove: ').db_error());
			db_rollback();
			return false;
		}

		$res = db_query_params('DELETE FROM trove_agg WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Trove: ').db_error());
			db_rollback();
			return false;
		}

		//
		//	Delete counters
		//
		$res = db_query_params('DELETE FROM project_sums_agg WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Counters: ').db_error());
			db_rollback();
			return false;
		}

		$res = db_query_params('INSERT INTO deleted_groups (unix_group_name, delete_date, isdeleted) VALUES ($1, $2, $3)',
					array($this->getUnixName(),
						time(),
						0));
		if (!$res) {
			$this->setError(_('Error Deleting Project:').' '.db_error());
			db_rollback();
			return false;
		}

		// Remove users & delete roles from this project
		$members = $this->getMembers();
		foreach ($members as $userObject) {
			$this->removeUser($userObject->getID());
		}
		$localRolesId = $this->getRolesId(false);
		foreach ($localRolesId as $localRoleId) {
			$roleObject = new Role($this, $localRoleId);
			$roleObject->delete();
		}
		// Delete entry in groups.
		$res = db_query_params('DELETE FROM groups WHERE group_id=$1',
					array($this->getID()));
		if (!$res) {
			$this->setError(_('Error Deleting Project:').' '.db_error());
			db_rollback();
			return false;
		}

		db_commit();

		$hook_params = array();
		$hook_params['group'] = $this;
		$hook_params['group_id'] = $this->getID();
		plugin_hook("group_delete", $hook_params);

		if (forge_get_config('upload_dir') != '' && $this->getUnixName()) {
			exec('/bin/rm -rf '.forge_get_config('upload_dir').'/'.$this->getUnixName().'/');
		}
		if (forge_get_config('ftp_upload_dir') != '' && $this->getUnixName()) {
			exec('/bin/rm -rf '.forge_get_config('ftp_upload_dir').'/'.$this->getUnixName().'/');
		}
		//
		//	Delete reporting
		//
		db_query_params('DELETE FROM rep_group_act_monthly WHERE group_id=$1',
		array($this->getID()));
		//echo 'rep_group_act_monthly'.db_error();
		db_query_params('DELETE FROM rep_group_act_weekly WHERE group_id=$1',
		array($this->getID()));
		//echo 'rep_group_act_weekly'.db_error();
		db_query_params('DELETE FROM rep_group_act_daily WHERE group_id=$1',
		array($this->getID()));
		//echo 'rep_group_act_daily'.db_error();
		unset($this->data_array);
		return true;
	}

	/*
		Basic functions to add/remove users to/from a group
		and update their permissions
	*/

	/**
	 * addUser - controls adding a user to a group.
	 *
	 * @param	string	$user_identifier	Unix name of the user to add OR integer user_id.
	 * @param	int	$role_id		The role_id this user should have.
	 * @return	boolean	success.
	 * @access	public
	 */
	function addUser($user_identifier, $role_id) {
		global $SYS;
		/*
			Admins can add users to groups
		*/

		if (!forge_check_perm ('project_admin', $this->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();

		/*
			get user id for this user's unix_name
		*/
		if (is_int ($user_identifier)) { // user_id or user_name
			$res_newuser = db_query_params ('SELECT * FROM users WHERE user_id=$1', array($user_identifier));
		} else {
			$res_newuser = db_query_params ('SELECT * FROM users WHERE user_name=$1', array($user_identifier));
		}
		if (db_numrows($res_newuser) > 0) {
			//
			//	make sure user is active
			//
			if (db_result($res_newuser,0,'status') != 'A') {
				$this->setError(_('User is not active. Only active users can be added.'));
				db_rollback();
				return false;
			}

			//
			//	user was found - set new user_id var
			//
			$user_id = db_result($res_newuser,0,'user_id');

			$role = new Role($this, $role_id);
			if (!$role || !is_object($role)) {
				$this->setError(_('Error Getting Role Object'));
				db_rollback();
				return false;
			} elseif ($role->isError()) {
				$this->setError('addUser::roleget::'.$role->getErrorMessage());
				db_rollback();
				return false;
			}

			$role->addUser(user_get_object($user_id));
			if (!$SYS->sysCheckCreateGroup($this->getID())){
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
			if (!$SYS->sysCheckCreateUser($user_id)) {
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
			if (!$SYS->sysGroupCheckUser($this->getID(),$user_id)) {
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
		} else {
			//
			//	user doesn't exist
			//
			$this->setError(_('That user does not exist.'));
			db_rollback();
			return false;
		}

		$hook_params['group'] = $this;
		$hook_params['group_id'] = $this->getID();
		$hook_params['user'] = user_get_object($user_id);
		$hook_params['user_id'] = $user_id;
		plugin_hook ("group_adduser", $hook_params);

		//
		//	audit trail
		//
		$this->addHistory(_('Added User'),$user_identifier);
		db_commit();
		return true;
	}

	/**
	 * removeUser - controls removing a user from a group.
	 *
	 * Users can remove themselves.
	 *
	 * @param	int	$user_id	The ID of the user to remove.
	 * @return	boolean	success.
	 */
	function removeUser($user_id) {
		global $SYS;

		if ($user_id != user_getid()
			&& !forge_check_perm('project_admin', $this->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();

		$user = user_get_object($user_id);
		$roles = RBACEngine::getInstance()->getAvailableRolesForUser($user);
		$found_role = NULL;
		foreach ($roles as $role) {
			if ($role->getHomeProject() && $role->getHomeProject()->getID() == $this->getID()) {
				$found_role = $role;
				break;
			}
		}
		if ($found_role == NULL) {
			$this->setError(sprintf(_('Error: User not removed: %s')));
			db_rollback();
			return false;
		}
		$found_role->removeUser($user);
		if (!$SYS->sysGroupCheckUser($this->getID(), $user_id)) {
			$this->setError($SYS->getErrorMessage());
			db_rollback();
			return false;
		}

		//
		//	reassign open artifacts to id=100
		//
		$res = db_query_params('UPDATE artifact SET assigned_to=100
				WHERE group_artifact_id
				IN (SELECT group_artifact_id
				FROM artifact_group_list
				WHERE group_id=$1 AND status_id=1 AND assigned_to=$2)',
						array($this->getID(),
							$user_id));
		if (!$res) {
			$this->setError(_('Error: artifact:').' '.db_error());
			db_rollback();
			return false;
		}

		//
		//	reassign open tasks to id=100
		//	first have to purge any assignments that would cause
		//	conflict with existing assignment to 100
		//
		$res = db_query_params('DELETE FROM project_assigned_to
					WHERE project_task_id IN (SELECT pt.project_task_id
					FROM project_task pt, project_group_list pgl, project_assigned_to pat
					WHERE pt.group_project_id = pgl.group_project_id
					AND pat.project_task_id=pt.project_task_id
					AND pt.status_id=1 AND pgl.group_id=$1
					AND pat.assigned_to_id=$2)
					AND assigned_to_id=100',
						array($this->getID(),
							$user_id));
		if (!$res) {
			$this->setError(sprintf(_('Error: project_assigned_to %d: %s'), 1, db_error()));
			db_rollback();
			return false;
		}
		$res = db_query_params('UPDATE project_assigned_to SET assigned_to_id=100
					WHERE project_task_id IN (SELECT pt.project_task_id
					FROM project_task pt, project_group_list pgl
					WHERE pt.group_project_id = pgl.group_project_id
					AND pt.status_id=1 AND pgl.group_id=$1)
					AND assigned_to_id=$2',
						array($this->getID(),
							$user_id));
		if (!$res) {
			$this->setError(sprintf(_('Error: project_assigned_to %d: %s'), 2, db_error()));
			db_rollback();
			return false;
		}

		//
		//	Remove user from system
		//
		if (!$SYS->sysGroupRemoveUser($this->getID(), $user_id)) {
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
		}

		$hook_params['group'] = $this;
		$hook_params['group_id'] = $this->getID();
		$hook_params['user'] = user_get_object($user_id);
		$hook_params['user_id'] = $user_id;
		plugin_hook ("group_removeuser", $hook_params);

		//audit trail
		$this->addHistory(_('Removed User'),$user_id);

		db_commit();
		return true;
	}

	/**
	 * updateUser - controls updating a user's role in this group.
	 *
	 * @param	int	$user_id	The ID of the user.
	 * @param	int	$role_id	The role_id to set this user to.
	 * @return	boolean	success.
	 */
	function updateUser($user_id, $role_id) {

		if (!forge_check_perm ('project_admin', $this->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		$newrole = RBACEngine::getInstance()->getRoleById ($role_id);
		if (!$newrole || !is_object($newrole)) {
			$this->setError(_('Could Not Get Role'));
			return false;
		} elseif ($newrole->isError()) {
			$this->setError(sprintf(_('Role: %s'),$role->getErrorMessage()));
			return false;
		} elseif ($newrole->getHomeProject() == NULL
			  || $newrole->getHomeProject()->getID() != $this->getID()) {
			$this->setError(_('Wrong destination role'));
			return false;
		}
		$user = user_get_object ($user_id);
		$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($user);
		$found_role = NULL;
		foreach ($roles as $role) {
			if ($role->getHomeProject() && $role->getHomeProject()->getID() == $this->getID()) {
				$found_role = $role;
				break;
			}
		}
		if ($found_role == NULL) {
			$this->setError(sprintf(_('Error: User not removed: %s')));
			db_rollback();
			return false;
		}
		$found_role->removeUser ($user);
		$newrole->addUser ($user);

		$this->addHistory(_('Updated User'),$user_id);
		return true;
	}

	/**
	 * addHistory - Makes an audit trail entry for this project.
	 *
	 * @param	string	$field_name	The name of the field.
	 * @param	string	$old_value	The Old Value for this $field_name.
	 * @return 	resource		database result handle.
	 * @access public
	 */
	function addHistory($field_name, $old_value) {
		return db_query_params ('INSERT INTO group_history(group_id,field_name,old_value,mod_by,adddate)
			VALUES ($1,$2,$3,$4,$5)',
					array($this->getID(),
						$field_name,
						$old_value,
						user_getid(),
						time()));
	}

	/**
	 * activateUsers - Make sure that group members have unix accounts.
	 *
	 * Setup unix accounts for group members. Can be called even
	 * if members are already active.
	 *
	 * @access private
	 */
	function activateUsers() {
		/*
			Activate member(s) of the project
		*/

		global $SYS;

		$members = $this->getUsers (true);

		foreach ($members as $member) {
			$user_id = $member->getID();

			if (!$SYS->sysCheckCreateGroup($this->getID())){
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
			if (!$SYS->sysCheckCreateUser($user_id)) {
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
			if (!$SYS->sysGroupCheckUser($this->getID(),$user_id)) {
				$this->setError($SYS->getErrorMessage());
				db_rollback();
				return false;
			}
		}

		return true;
	}

	/**
	 * getMembers - returns array of User objects for this project
	 *
	 * @return array of User objects for this group.
	 */
	function getMembers() {
		return $this->getUsers (true);
	}

	/**
	 * replaceTemplateStrings - fill-in some blanks with project name
	 *
	 * @param	string	$string Template string
	 * @return	string	String after replacements
	 */
	function replaceTemplateStrings($string) {
		$string = str_replace ('UNIXNAME', $this->getUnixName(), $string);
		$string = str_replace ('PUBLICNAME', $this->getPublicName(), $string);
		$string = str_replace ('DESCRIPTION', $this->getDescription(), $string);
		return $string;
	}

	/**
	 * approve - Approve pending project.
	 *
	 * @param	object  $user	The User object who is doing the updating.
	 * @return	bool
	 * @access	public
	 */
	function approve(&$user) {
		global $gfcommon,$gfwww;
		require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

		if ($this->getStatus()=='A') {
			$this->setError(_("Group already active"));
			return false;
		}

		db_begin();

		// Step 1: Activate group and create LDAP entries
		if (!$this->setStatus($user, 'A')) {
			db_rollback();
			return false;
		}

		// Switch to system language for item creation
		setup_gettext_from_sys_lang();

		// Create default roles
		$idadmin_group = NULL;
		foreach (get_group_join_requests ($this) as $gjr) {
			$idadmin_group = $gjr->getUserID();
			break;
		}
		if ($idadmin_group == NULL) {
			$idadmin_group = $user->getID();
		}

		$template = $this->getTemplateProject();
		$id_mappings = array();
		$seen_admin_role = false;
		if ($template) {
			// Copy roles from template project
			foreach($template->getRoles() as $oldrole) {
				if ($oldrole->getHomeProject() != NULL) {
					$role = new Role($this);
					$data = array();
					// Need to use a different role name so that the permissions aren't set from the hardcoded defaults
					$role->create('TEMPORARY ROLE NAME', $data, true);
					$role->setName($oldrole->getName());
					if ($oldrole->getSetting ('project_admin', $template->getID())) {
						$seen_admin_role = true;
					}
				} else {
					$role = $oldrole;
					$role->linkProject($this);
				}
				$id_mappings['role'][$oldrole->getID()] = $role->getID();
				// Reuse the project_admin permission
				$role->setSetting ('project_admin', $this->getID(), $oldrole->getSetting ('project_admin', $template->getID()));
			}
		}

		if (!$seen_admin_role) {
			$role = new Role($this);
			$adminperms = array('project_admin' => array ($this->getID() => 1));
			$role_id = $role->create ('Admin', $adminperms, true);
		}

		$roles = $this->getRoles();
		foreach ($roles as $r) {
			if ($r->getHomeProject() == NULL) {
				continue;
			}
			if ($r->getSetting ('project_admin', $this->getID())) {
				$r->addUser(user_get_object ($idadmin_group));
			}
		}

		// Temporarily switch to the submitter's identity
		$saved_session = session_get_user();
		session_set_internal($idadmin_group);

		if ($template) {
			if (forge_get_config('use_tracker')) {
				$this->setUseTracker ($template->usesTracker());
				if ($template->usesTracker()) {
					$oldatf = new ArtifactTypeFactory($template);
					foreach ($oldatf->getArtifactTypes() as $o) {
						$t = new ArtifactType ($this);
						$t->create ($this->replaceTemplateStrings($o->getName()),$this->replaceTemplateStrings($o->getDescription()),$o->emailAll(),$o->getEmailAddress(),$o->getDuePeriod()/86400,0,$o->getSubmitInstructions(),$o->getBrowseInstructions());
						$id_mappings['tracker'][$o->getID()] = $t->getID();
						$t->cloneFieldsFrom ($o->getID());
					}
				}
			}

			if (forge_get_config('use_pm')) {
				$this->setUsePM ($template->usesPM());
				if ($template->usesPM()) {
					$oldpgf = new ProjectGroupFactory($template);
					foreach ($oldpgf->getProjectGroups() as $o) {
						$pg = new ProjectGroup($this);
						$pg->create($this->replaceTemplateStrings($o->getName()),$this->replaceTemplateStrings($o->getDescription()),$o->getSendAllPostsTo());
						$id_mappings['pm'][$o->getID()] = $pg->getID();
					}
				}
			}

			if (forge_get_config('use_forum')) {
				$this->setUseForum($template->usesForum());
				if ($template->usesForum()) {
					$oldff = new ForumFactory($template);
					foreach ($oldff->getForums() as $o) {
						$f = new Forum($this);
						$f->create($this->replaceTemplateStrings($o->getName()),$this->replaceTemplateStrings($o->getDescription()),$o->getSendAllPostsTo(),1);
						$id_mappings['forum'][$o->getID()] = $f->getID();
					}
				}
			}

			if (forge_get_config('use_docman')) {
				$this->setUseDocman($template->usesDocman());
				if ($template->usesDocman()) {
					$olddgf = new DocumentGroupFactory($template);
					// First pass: create all docgroups
					$id_mappings['docman_docgroup'][0] = 0;
					foreach ($olddgf->getDocumentGroups() as $o) {
						$ndgf = new DocumentGroup($this);
						// .trash is a reserved directory
						if ($o->getName() != '.trash' && $o->getParentID() == 0) {
							$ndgf->create($this->replaceTemplateStrings($o->getName()));
							$id_mappings['docman_docgroup'][$o->getID()] = $ndgf->getID();
						}
					}
					// Second pass: restore hierarchy links
					foreach ($olddgf->getDocumentGroups() as $o) {
						$ndgf = new DocumentGroup($this);
						if ($o->getName() != '.trash' && $o->getParentID() == 0) {
							$ndgf->fetchData($id_mappings['docman_docgroup'][$o->getID()]);
							$ndgf->update($ndgf->getName(), $id_mappings['docman_docgroup'][$o->getParentID()]);
						}
					}
				}
			}

			if (forge_get_config('use_frs')) {
				$this->setUseFRS ($template->usesFRS());
				if ($template->usesFRS()) {
					foreach (get_frs_packages($template) as $o) {
						$newp = new FRSPackage($this);
						$nname = $this->replaceTemplateStrings($o->getName());
						$newp->create ($nname, $o->isPublic());
					}
				}
			}

			if (forge_get_config('use_mail')) {
				$this->setUseMail($template->usesMail());
				if ($template->usesMail()) {
					$oldmlf = new MailingListFactory($template);
					foreach ($oldmlf->getMailingLists() as $o) {
						$ml = new MailingList($this);
						$nname = preg_replace ('/^'.$template->getUnixName().'-/','',$o->getName());

						$ndescription = $this->replaceTemplateStrings($o->getDescription());
						$ml->create($nname, $ndescription, $o->isPublic());
					}
				}
			}

			if (0) {
				/* use SCM plugin from template group */
				$this->setUseSCM($template->usesSCM());

				foreach ($template->getPlugins() as
					$plugin_id => $plugin_name) {
					$this->setPluginUse($plugin_name);
				}
			} else {
				/* use SCM choice from registration page */

				foreach ($template->getPlugins() as
					$plugin_id => $plugin_name) {
					if (substr($plugin_name, 3) == 'scm' &&
						$plugin_name != 'scmhook') {
						/* skip copying scm plugins */
						continue;
					}
					/* enable other plugins though */
					$this->setPluginUse($plugin_name);
				}
			}

			foreach ($template->getRoles() as $oldrole) {
				$newrole = RBACEngine::getInstance()->getRoleById ($id_mappings['role'][$oldrole->getID()]);
				if ($oldrole->getHomeProject() != NULL
					&& $oldrole->getHomeProject()->getID() == $template->getID()) {
					$newrole->setPublic ($oldrole->isPublic());
				}
				$oldsettings = $oldrole->getSettingsForProject ($template);

				$sections = array('project_read', 'project_admin', 'frs', 'scm', 'docman', 'tracker_admin', 'new_tracker', 'forum_admin', 'new_forum', 'pm_admin', 'new_pm');
				foreach ($sections as $section) {
					$newrole->setSetting ($section, $this->getID(), $oldsettings[$section][$template->getID()]);
				}

				$sections = array('tracker', 'pm', 'forum');
				foreach ($sections as $section) {
					if (isset ($oldsettings[$section])) {
						foreach ($oldsettings[$section] as $k => $v) {
							// Only copy perms for tools that have been copied
							if (isset ($id_mappings[$section][$k])) {
								$newrole->setSetting ($section,
											$id_mappings[$section][$k],
											$v);
							}
						}
					}
				}
			}

			$lm = new WidgetLayoutManager();
			$lm->createDefaultLayoutForProject ($this->getID(), $template->getID());

			$params = array();
			$params['template'] = $template;
			$params['project'] = $this;
			$params['id_mappings'] = $id_mappings;
			plugin_hook_by_reference ('clone_project_from_template', $params);
		} else {
			// Disable everything
			db_query_params ('UPDATE groups SET use_mail=0, use_survey=0, use_forum=0, use_pm=0, use_pm_depend_box=0, use_scm=0, use_news=0, use_docman=0, use_ftp=0, use_tracker=0, use_frs=0, use_stats=0 WHERE group_id=$1',
				array($this->getID()));
		}

		$this->normalizeAllRoles();
		// empty members cache because the group creator is not yet in cache.
		unset($this->membersArr);
		$this->activateUsers();

		// Delete fake join request
		foreach (get_group_join_requests ($this) as $gjr) {
			$gjr->delete(true);
		}

		// Switch back to user preference
		session_set_internal($saved_session->getID());
		setup_gettext_from_context();

		db_commit();

		$this->sendApprovalEmail();
		$this->addHistory(_('Approved'), 'x');

		//
		//	Plugin can make approve operation there
		//
		$params = array();
		$params['group'] = $this;
		$params['group_id'] = $this->getID();
		plugin_hook('group_approved', $params);

		return true;
	}

	/**
	 * sendApprovalEmail - Send new project email.
	 *
	 * @return	boolean	success.
	 * @access	public
	 */
	function sendApprovalEmail() {
		$admins = RBACEngine::getInstance()->getUsersByAllowedAction ('project_admin', $this->getID());

		if (count($admins) < 1) {
			$this->setError(_("Group does not have any administrators."));
			return false;
		}

		// send one email per admin
		foreach ($admins as $admin) {
			setup_gettext_for_user ($admin);

			$message=sprintf(_('Your project registration for %4$s has been approved.

Project Full Name:  %1$s
Project Unix Name:  %2$s

Your DNS will take up to a day to become active on our site.
Your web site is accessible through your shell account. Please read
site documentation (see link below) about intended usage, available
services, and directory layout of the account.

If you visit your
own project page in %4$s while logged in, you will find
additional menu functions to your left labeled \'Project Admin\'.

We highly suggest that you now visit %4$s and create a public
description for your project. This can be done by visiting your project
page while logged in, and selecting \'Project Admin\' from the menus
on the left (or by visiting %3$s
after login).

Your project will also not appear in the Trove Software Map (primary
list of projects hosted on %4$s which offers great flexibility in
browsing and search) until you categorize it in the project administration
screens. So that people can find your project, you should do this now.
Visit your project while logged in, and select \'Project Admin\' from the
menus on the left.

Enjoy the system, and please tell others about %4$s. Let us know
if there is anything we can do to help you.

-- the %4$s crew'),
							htmlspecialchars_decode($this->getPublicName()),
							$this->getUnixName(),
							util_make_url ('/project/admin/?group_id='.$this->getID()),
							forge_get_config ('forge_name'));

			util_send_message($admin->getEmail(), sprintf(_('%s Project Approved'), forge_get_config ('forge_name')), $message);

			setup_gettext_from_context();
		}

		return true;
	}

	/**
	 * sendRejectionEmail - Send project rejection email.
	 *
	 * This function sends out a rejection message to a user who
	 * registered a project.
	 *
	 * @param	int	$response_id		The id of the response to use.
	 * @param	string	$message		The rejection message.
	 * @return	bool	completion status.
	 * @access	public
	 */
	function sendRejectionEmail($response_id, $message="zxcv") {
		$submitters = array();
		foreach (get_group_join_requests ($this) as $gjr) {
			$submitters[] = user_get_object($gjr->getUserID());
		}

		if (count ($submitters) < 1) {
			$this->setError(_("Group does not have any administrators."));
			return false;
		}

		foreach ($submitters as $admin) {
			setup_gettext_for_user($admin);

			$response = sprintf(_('Your project registration for %s has been denied.'), forge_get_config('forge_name')) . "\n\n"
					. _('Project Full Name')._(': '). $this->getPublicName() . "\n"
					. _('Project Unix Name')._(': '). $this->getUnixName() . "\n\n"
					. _('Reasons for negative decision')._(': ') . "\n\n";

			// Check to see if they want to send a custom rejection response
			if ($response_id == 0) {
				$response .= $message;
			} else {
				$response .= db_result(
					db_query_params('SELECT response_text FROM canned_responses WHERE response_id=$1', array($response_id)),
					0,
					"response_text");
			}

			util_send_message($admin->getEmail(), sprintf(_('%s Project Denied'), forge_get_config ('forge_name')), $response);
			setup_gettext_from_context();
		}

		return true;
	}

	/**
	 * sendNewProjectNotificationEmail - Send new project notification email.
	 *
	 * This function sends out a notification email to the
	 * SourceForge admin user when a new project is
	 * submitted.
	 *
	 * @return	boolean	success.
	 * @access	public
	 */
	function sendNewProjectNotificationEmail() {
		// Get the user who wants to register the project
		$submitters = array();
		foreach (get_group_join_requests ($this) as $gjr) {
			$submitters[] = user_get_object($gjr->getUserID());
		}
		if (count ($submitters) < 1) {
			$this->setError(_("Could not find user who has submitted the project."));
			return false;
		}

		$admins = RBACEngine::getInstance()->getUsersByAllowedAction ('approve_projects', -1);

		if (count($admins) < 1) {
			$this->setError(_("There is no administrator to send the mail to."));
			return false;
		}

		foreach ($admins as $admin) {
			$admin_email = $admin->getEmail();
			setup_gettext_for_user ($admin);

			$message = sprintf(_('New %s Project Submitted'), forge_get_config ('forge_name')) . "\n\n"
					. _('Project Full Name')._(': ').htmlspecialchars_decode($this->getPublicName()) . "\n"
					. _('Submitted Description')._(': ').htmlspecialchars_decode($this->getRegistrationPurpose()) . "\n";

			foreach ($submitters as $submitter) {
				$message .= _('Submitter')._(': ').$submitter->getRealName().' ('.$submitter->getUnixName().')' . "\n\n";
			}

			$message .= "\n"
					. _('Please visit the following URL to approve or reject this project')._(': '). "\n"
					. util_make_url('/admin/approve-pending.php');
			util_send_message($admin_email, sprintf(_('New %s Project Submitted'), forge_get_config('forge_name')), $message);
			setup_gettext_from_context();
		}

		$email = $submitter->getEmail();
		setup_gettext_for_user ($submitter);

		$message = sprintf(_('New %s Project Submitted'), forge_get_config ('forge_name')) . "\n\n"
				. _('Project Full Name')._(': ') . $this->getPublicName() . "\n"
				. _('Submitted Description')._(': ') . util_unconvert_htmlspecialchars($this->getRegistrationPurpose()) . "\n\n"
				. sprintf(_('The %s admin team will now examine your project submission. You will be notified of their decision.'),
						  forge_get_config ('web_host'));

		util_send_message($email, sprintf(_('New %s Project Submitted'), forge_get_config ('forge_name')), $message);
		setup_gettext_from_context();

		return true;
	}

	/**
	 * validateGroupName - Validate the group name
	 *
	 * @param	string	Group name.
	 *
	 * @return	boolean	an error false and set an error is the group name is invalid otherwise return true
	 */
	function validateGroupName($group_name) {
		if (strlen($group_name)<3) {
			$this->setError(_('Group name is too short'));
			return false;
		} elseif (strlen(htmlspecialchars($group_name))>40) {
			$this->setError(_('Group name is too long'));
			return false;
		} elseif (group_get_object_by_publicname($group_name)) {
			$this->setError(_('Group name already taken'));
			return false;
		}
		return true;
	}

	/**
	 * getRolesId - Get Ids of the roles of the group.
	 *
	 * @param	bool	all role ids or local role ids only. Default is all role ids
	 * @return	array	Role ids of this group.
	 */
	function getRolesId($global = true) {
		$role_ids = array();

		$res = db_query_params('SELECT role_id FROM pfo_role WHERE home_group_id=$1',
					array($this->getID()));
		while ($arr = db_fetch_array($res)) {
			$role_ids[] = $arr['role_id'];
		}
		if ($global) {
			$res = db_query_params('SELECT role_id FROM role_project_refs WHERE group_id=$1',
						array($this->getID()));
			while ($arr = db_fetch_array($res)) {
				$role_ids[] = $arr['role_id'];
			}
		}

		return array_unique($role_ids);
	}

	/**
	 * getRoles - Get the roles of the group.
	 *
	 * @return	array	Roles of this group.
	 */
	function getRoles() {
		$result = array();

		$roles = $this->getRolesId();
		$engine = RBACEngine::getInstance();
		foreach ($roles as $role_id) {
			$result[] = $engine->getRoleById ($role_id);
		}

		return $result;
	}

	function normalizeAllRoles() {
		$roles = $this->getRoles();

		foreach ($roles as $r) {
			$r->normalizeData();
		}
	}

	/**
	 * getUnixStatus - Status of activation of unix account.
	 *
	 * @return string	Values: (N)one, (A)ctive, (S)uspended or (D)eleted
	 */
	function getUnixStatus() {
		return $this->data_array['unix_status'];
	}

	/**
	 * setUnixStatus - Sets status of activation of unix account.
	 *
	 * @param	string	$status The unix status.
	 *				N	no_unix_account
	 *				A	active
	 *				S	suspended
	 *				D	deleted
	 *
	 * @return	boolean success.
	 */
	function setUnixStatus($status) {
		global $SYS;
		db_begin();
		$res = db_query_params ('UPDATE groups SET unix_status=$1 WHERE group_id=$2',
					array($status,
						$this->getID()));

		if (!$res) {
			$this->setError(sprintf(_('Error: Cannot Update Group Unix Status: %s'),db_error()));
			db_rollback();
			return false;
		} else {
			if ($status == 'A') {
				if (!$SYS->sysCheckCreateGroup($this->getID())) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			} else {
				if ($SYS->sysCheckGroup($this->getID())) {
					if (!$SYS->sysRemoveGroup($this->getID())) {
						$this->setError($SYS->getErrorMessage());
						db_rollback();
						return false;
					}
				}
			}

			$this->data_array['unix_status']=$status;
			db_commit();
			return true;
		}
	}

	/**
	 * getUsers - Get the users of a group
	 *
	 * @param	bool	$onlylocal
	 * @return	array	user's objects.
	 */
	function getUsers($onlylocal = true) {
		if (!isset($this->membersArr)) {
			$this->membersArr = array();

			$ids = array();
			foreach ($this->getRoles() as $role) {
				if ($onlylocal
					&& ($role->getHomeProject() == NULL || $role->getHomeProject()->getID() != $this->getID())) {
					continue;
				}
				foreach ($role->getUsers() as $user) {
					$ids[] = $user->getID();
				}
			}
			$ids = array_unique ($ids);
			foreach ($ids as $id) {
				$u = user_get_object ($id);
				if ($u->isActive()) {
					$this->membersArr[] = $u;
				}
			}
		}
		return $this->membersArr;
	}

	function setDocmanCreateOnlineStatus($status) {
		db_begin();
		/* if we activate search engine, we probably want to reindex */
		$res = db_query_params('UPDATE groups SET use_docman_create_online=$1 WHERE group_id=$2',
					array($status, $this->getID()));

		if (!$res) {
			$this->setError(sprintf(_('Error: Cannot Update Group DocmanCreateOnline Status: %s'),db_error()));
			db_rollback();
			return false;
		} else {
			$this->data_array['use_docman_create_online']=$status;
			db_commit();
			return true;
		}
	}

	function setDocmanWebdav($status) {
		db_begin();
		/* if we activate search engine, we probably want to reindex */
		$res = db_query_params('UPDATE groups SET use_webdav=$1 WHERE group_id=$2',
					array($status,
						   $this->getID()));

		if (!$res) {
			$this->setError(sprintf(_('Error: Cannot Update Group UseWebdab Status: %s'),db_error()));
			db_rollback();
			return false;
		} else {
			$this->data_array['use_webdav']=$status;
			db_commit();
			return true;
		}
	}

	function setDocmanSearchStatus($status) {
		db_begin();
		/* if we activate search engine, we probably want to reindex */
		$res = db_query_params('UPDATE groups SET use_docman_search=$1, force_docman_reindex=$1 WHERE group_id=$2',
					array($status,
						$this->getID()));

		if (!$res) {
			$this->setError(sprintf(_('Error: Cannot Update Group UseDocmanSearch Status: %s'),db_error()));
			db_rollback();
			return false;
		} else {
			$this->data_array['use_docman_search']=$status;
			db_commit();
			return true;
		}
	}

	function setDocmanForceReindexSearch($status) {
		db_begin();
		/* if we activate search engine, we probably want to reindex */
		$res = db_query_params('UPDATE groups SET force_docman_reindex=$1 WHERE group_id=$2',
					array($status,
						$this->getID()));

		if (!$res) {
			$this->setError(sprintf(_('Error: Cannot Update Group force_docman_reindex %s'),db_error()));
			db_rollback();
			return false;
		} else {
			$this->data_array['force_docman_reindex']=$status;
			db_commit();
			return true;
		}
	}
}

/**
 * group_getname() - get the group name
 *
 * @param	int	 $group_id	The group ID
 * @return	string
 * @deprecated
 *
 */
function group_getname ($group_id = 0) {
	$grp = group_get_object($group_id);
	if ($grp) {
		return $grp->getPublicName();
	} else {
		return 'Invalid';
	}
}

/**
 * group_getunixname() - get the unixname for a group
 *
 * @param	int	 $group_id	The group ID
 * @return	string
 * @deprecated
 *
 */
function group_getunixname ($group_id) {
	$grp = group_get_object($group_id);
	if ($grp) {
		return $grp->getUnixName();
	} else {
		return 'Invalid';
	}
}

/**
 * group_get_result() - Get the group object result ID.
 *
 * @param	int	 $group_id	The group ID
 * @return	int
 * @deprecated
 *
 */
function &group_get_result($group_id=0) {
	$grp = group_get_object($group_id);
	if ($grp) {
		return $grp->getData();
	} else {
		return 0;
	}
}

function getAllProjectTags($onlyvisible = true) {
	$res = db_query_params('SELECT project_tags.name, groups.group_id FROM groups, project_tags WHERE groups.group_id = project_tags.group_id AND groups.status = $1 ORDER BY project_tags.name, groups.group_id',
				array('A'));

	if (!$res || db_numrows($res) == 0) {
		return false;
	}

	$result = array();

	while ($arr = db_fetch_array($res)) {
		$tag = $arr[0];
		$group_id = $arr[1];
		if (!isset($result[$tag])) {
			$result[$tag] = array();
		}

		if (!$onlyvisible || forge_check_perm('project_read', $group_id)) {
			$p = group_get_object($group_id);
			$result[$tag][] = array('unix_group_name' => $p->getUnixName(),
						'group_id' => $group_id);
		}
	}

	return $result;
}

/**
 * Utility class to compare project based in various criteria (names, unixnames, id, ...)
 *
 */
class ProjectComparator {
	var $criterion = 'name';

	function Compare ($a, $b) {
		switch ($this->criterion) {
		case 'name':
		default:
			$namecmp = strcoll ($a->getPublicName(), $b->getPublicName());
			if ($namecmp != 0) {
				return $namecmp;
			}
			/* If several projects share a same public name */
			return strcoll ($a->getUnixName(), $b->getUnixName());
			break;
		case 'unixname':
			return strcmp ($a->getUnixName(), $b->getUnixName());
			break;
		case 'id':
			$aid = $a->getID();
			$bid = $b->getID();
			if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
			break;
		}
	}
}

function sortProjectList (&$list, $criterion='name') {
	$cmp = new ProjectComparator();
	$cmp->criterion = $criterion;

	return usort ($list, array($cmp, 'Compare'));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/**
 * FusionForge project manager
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';

	/**
	*	Fetches a ProjectGroup object from the database
	*
	* @param group_project_id	the projectgroup id  
	*	@param data	whether or not the db result handle is passed in
	*	@return	the ProjectGroup object
	*/
	function &projectgroup_get_object($group_project_id,$data=false) {
		global $PROJECTGROUP_OBJ;
		if (!isset($PROJECTGROUP_OBJ["_".$group_project_id."_"])) {
			if ($data) {
				//the db result handle was passed in
			} else {
				$res = db_query_params ('SELECT * FROM project_group_list_vw WHERE group_project_id=$1',
							array ($group_project_id)) ;
				if (db_numrows($res) <1 ) {
					$PROJECTGROUP_OBJ["_".$group_project_id."_"]=false;
					return false;
				}
				$data =& db_fetch_array($res);
			}
			$Group =& group_get_object($data["group_id"]);
			$PROJECTGROUP_OBJ["_".$group_project_id."_"]= new ProjectGroup($Group,$group_project_id,$data);
		}
		return $PROJECTGROUP_OBJ["_".$group_project_id."_"];
	}


class ProjectGroup extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;
	var $statuses;
	var $categories;
	var $technicians;
	var $current_user_perm;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this forum is associated.
	 *  @param  int	 The group_project_id.
	 *  @param  array	The associative array of data.
	 *	@return	boolean	success.
	 */
	function ProjectGroup(&$Group, $group_project_id=false, $arr=false) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError('ProjectGroup:: No Valid Group Object');
			return false;
		}
		if ($Group->isError()) {
			$this->setError('ProjectGroup:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($group_project_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($group_project_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Group_id in db result does not match Group Object');
					return false;
				}
			}
			//
			//  Make sure they can even access this object
			//
			if (!forge_check_perm ('pm', $this->getID(), 'read')) {
				$this->setPermissionDeniedError();
				$this->data_array = null;
				return false;
			}
		}
		return true;
	}

	/**
	 *	create - create a new ProjectGroup in the database.
	 *
	 *	@param	string	The project name.
	 *	@param	string	The project description.
	 *	@param	int	Whether it is (1) public or (0) private .
	 *	@param	string	The email address to send new notifications to.
	 *	@return boolean success.
	 */
	function create($project_name,$description,$is_public=1,$send_all_posts_to='') {
		if (strlen($project_name) < 3) {
			$this->setError(_('Title Must Be At Least 5 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Document Description Must Be At Least 10 Characters'));
			return false;
		}
		if ($send_all_posts_to) {
			$invalid_mails = validate_emails($send_all_posts_to);
			if (count($invalid_mails) > 0) {
				$this->setInvalidEmailError();
				return false;
			}
		}
		
		if (!forge_check_perm ('pm_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();
		$result = db_query_params ('INSERT INTO project_group_list (group_id,project_name,is_public,description,send_all_posts_to) VALUES ($1,$2,$3,$4,$5)',
					   array ($this->Group->getId(),
						  htmlspecialchars($project_name),
						  $is_public,
						  htmlspecialchars($description),
						  $send_all_posts_to)) ;
		if (!$result) {
			db_rollback();
			$this->setError('Error Adding ProjectGroup: '.db_error());
			return false;
		}
		$this->group_project_id=db_insertid($result,'project_group_list','group_project_id');
		$this->fetchData($this->group_project_id);

		$roles_group = $this->Group->getRolesId();
		for ($i=0; $i<sizeof($roles_group); $i++) {
			// set the permission for the role's group 
			$role = new Role($this);
			$role_name = db_query_params ('SELECT role_name from role where role_id = $1', array($roles_group[$i]));
			$role_name = db_fetch_array($role_name);
			$role_setting_res = db_query_params ('INSERT INTO role_setting (role_id,section_name,ref_id,value) VALUES ($1,$2,$3,$4)',
						array ($roles_group[$i],
						       'pm',
						       $this->group_project_id,
						       $role->defaults[$role_name['role_name']]['pm'])) ;
			if (!$role_setting_res) {
				db_rollback();
				$this->setError('Error: Role setting for tasks id ' . $this->group_forum_id . ' for groud id ' . $this->Group->getID() . ' ' .db_error());
				return false;
			}
		}

		db_commit();

		$this->Group->normalizeAllRoles () ;

		return true;
	}

	/**
	 *  fetchData - re-fetch the data for this ProjectGroup from the database.
	 *
	 *  @param  int	 The project group ID.
	 *  @return	boolean	success.
	 */
	function fetchData($group_project_id) {
		$res = db_query_params ('SELECT * FROM project_group_list_vw WHERE group_project_id=$1 AND group_id=$2',
					array ($group_project_id,
					       $this->Group->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ProjectGroup:: Invalid group_project_id');
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group object this ProjectGroup is associated with.
	 *
	 *	@return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - get this GroupProjectID.
	 *
	 *	@return	int	The group_project_id #.
	 */
	function getID() {
		return $this->data_array['group_project_id'];
	}

	/**
	 *	getOpenCount - get the count of open tracker items in this tracker type.
	 *
	 *	@return   int The count.
	 */
	function getOpenCount() {
		return $this->data_array['open_count'];
	}

	/**
	 *	getTotalCount - get the total number of tracker items in this tracker type.
	 *
	 *	@return   int The total count.
	 */
	function getTotalCount() {
		return $this->data_array['count'];
	}

	/**
	 *	isPublic - Is this projectGroup open to the general public.
	 *
	 *	@return boolean	allow.
	 */
	function isPublic() {
		return $this->data_array['is_public'];
	}

	/**
	 *	getName - get the name of this projectGroup.
	 *
	 *	@return string	The name of this projectGroup.
	 */
	function getName() {
		return $this->data_array['project_name'];
	}

	/**
	 *	getSendAllPostsTo - an optional email address to send all task updates to.
	 *
	 *	@return string	The email address.
	 */
	function getSendAllPostsTo() {
		return $this->data_array['send_all_posts_to'];
	}

	/**
	 *	getDescription - the description of this ProjectGroup.
	 *
	 *	@return string	The description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 * getStatuses - Return result set of statuses.
	 *
	 * @returns Database result set.
	 */
	function getStatuses () {
		if (!$this->statuses) {
			$this->statuses = db_query_params ('SELECT * FROM project_status',
							   array());
		}
		return $this->statuses;
	}

	/**
	 * getCategories - Return result set of categories.
	 *
	 * @returns Database result set.
	 */
	function getCategories () {
		if (!$this->categories) {
			$this->categories = db_query_params ('SELECT category_id,category_name FROM project_category WHERE group_project_id=$1',
							     array ($this->getID()));
		}
		return $this->categories;
	}

	/**
	 *  getCategoryObjects - Array of ProjectCategory objects set up for this artifact type.
	 *
	 *  @return array   Of ProjectCategory objects.
	 */
	function &getCategoryObjects() {
		$res = $this->getCategories();
		$cats = array();
		while ($arr = db_fetch_array($res)) {
			$cats[] = new ProjectCategory($this,$arr);
		}
		return $cats;
	}

	/**
	 * getTechnicians - Return a result set of pm technicians in this group.
	 *
	 * @returns Datbase result set.
	 */
	function getTechnicians () {
		if (!$this->technicians) {
			$this->technicians = db_query_params ('SELECT users.user_id, users.realname 
				FROM users, role_setting, user_group
				WHERE users.user_id=user_group.user_id
                                AND role_setting.role_id=user_group.role_id
                                AND role_setting.ref_id=$1
				AND role_setting.value::integer IN (1,2) 
                                AND role_setting.section_name=$2
				ORDER BY users.realname',
							      array ($this->getID(),
								     'pm')) ;
		}
		return $this->technicians;
	}

	/**
	 *  getTechnicianObjects - Array of User objects set up for this artifact type.
	 *
	 *  @return array   Of User objects.
	 */
	function &getTechnicianObjects() {
		$res = $this->getTechnicians();
		$arr =& util_result_column_to_array($res,0);
		return user_get_objects($arr);
	}

	/**
	 *	update - update a ProjectGroup in the database.
	 *
	 *	@param	string	The project name.
	 *	@param	string	The project description.
	 *	@param	string	The email address to send new notifications to.
	 *	@return boolean success.
	 */
	function update($project_name,$description,$send_all_posts_to='') {
		if (strlen($project_name) < 3) {
			$this->setError(_('Title Must Be At Least 5 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Document Description Must Be At Least 10 Characters'));
			return false;
		}

		if ($send_all_posts_to) {
			$invalid_mails = validate_emails($send_all_posts_to);
			if (count($invalid_mails) > 0) {
				$this->setInvalidEmailError();
				return false;
			}
		}

		$res = db_query_params ('DELETE FROM role_setting WHERE section_name=$1 AND ref_id=$2',
				 array ('pm',
				 $this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

		if (!$this->userIsAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}

		$res = db_query_params ('UPDATE project_group_list SET project_name=$1,
			description=$2,	send_all_posts_to=$3
			WHERE group_id=$4 AND group_project_id=$5',
					array (htmlspecialchars($project_name),
					       htmlspecialchars($description),
					       $send_all_posts_to,
					       $this->Group->getID(),
					       $this->getID())) ;

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('Error On Update: '.db_error().$sql);
			return false;
		}
		return true;
	}

	/**
	 *	delete - delete this subproject and all its related data.
	 *
	 *	@param  bool	I'm Sure.
	 *	@param  bool	I'm REALLY sure.
	 *	@return   bool true/false;
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError();
			return false;
		}
		if (!$this->userIsAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();

                $res = db_query_params ('DELETE FROM project_assigned_to
			WHERE EXISTS (SELECT project_task_id FROM project_task
			WHERE group_project_id=$1
			AND project_task.project_task_id=project_assigned_to.project_task_id)',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

		$res = db_query_params ('DELETE FROM project_dependencies
			WHERE EXISTS (SELECT project_task_id FROM project_task
			WHERE group_project_id=$1
			AND project_task.project_task_id=project_dependencies.project_task_id)',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

		$res = db_query_params ('DELETE FROM project_history
			WHERE EXISTS (SELECT project_task_id FROM project_task
			WHERE group_project_id=$1
			AND project_task.project_task_id=project_history.project_task_id)',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

                $res = db_query_params ('DELETE FROM project_messages
			WHERE EXISTS (SELECT project_task_id FROM project_task
			WHERE group_project_id=$1
			AND project_task.project_task_id=project_messages.project_task_id)',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

                $res = db_query_params ('DELETE FROM project_task_artifact
			WHERE EXISTS (SELECT project_task_id FROM project_task
			WHERE group_project_id=$1
			AND project_task.project_task_id=project_task_artifact.project_task_id)',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

                $res = db_query_params ('DELETE FROM rep_time_tracking
			WHERE EXISTS (SELECT project_task_id FROM project_task
			WHERE group_project_id=$1
			AND project_task.project_task_id=rep_time_tracking.project_task_id)',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

                $res = db_query_params ('DELETE FROM project_task
			WHERE group_project_id=$1',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

		$res = db_query_params ('DELETE FROM project_category WHERE group_project_id=$1',
					array ($this->getID())) ;

		if (!$res)
		{
			$this->setError('DATABASE '.db_error());
			return false;
		}

		$res = db_query_params ('DELETE FROM project_group_list WHERE group_project_id=$1',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

		$res = db_query_params ('DELETE FROM project_counts_agg WHERE group_project_id=$1',
					array ($this->getID())) ;

                if (!$res)
                {
                        $this->setError('DATABASE '.db_error());
                        return false;
                }

		db_commit();

		$this->Group->normalizeAllRoles () ;

		return true;
	}

	/*

		USER PERMISSION FUNCTIONS

	*/

	/**
	 *  userIsAdmin - see if the logged-in user's perms are >= 2 or Group PMAdmin.
	 *
	 *  @return boolean user_is_admin.
	 */
	function userIsAdmin() {
		return forge_check_perm ('pm', $this->getID(), 'manager') ;
	}

	/**
	 *  userIsTechnician - see if the logged-in user's perms are >= 1 or Group PMAdmin.
	 *
	 *  @return boolean user_is_technician.
	 */
	function userIsTechnician() {
		return forge_check_perm ('pm', $this->getID(), 'tech') ;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

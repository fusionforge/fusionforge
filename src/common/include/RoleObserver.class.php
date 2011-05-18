<?php
/**
 * FusionForge observer role
 *
 * Copyright 2004, GForge, LLC
 * Copyright 2009, Roland Mas
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

require_once $gfcommon.'include/rbac_texts.php' ;

class RoleObserver extends Error {

	var $setting_array;
	var $role_vals;
	var $Group;
	var $role_values=array(
	'projectpublic'=>array('0','1'),
	'scmpublic'=>array('0','1'),
	'forumpublic'=>array('0','1'),
	'forumanon'=>array('0','1'),
	'trackerpublic'=>array('0','1'),
	'trackeranon'=>array('0','1'),
	'pmpublic'=>array('0','1'),
	'frspackage'=>array('0','1'));

	/**
	 *  Role($group,$id) - CONSTRUCTOR.
	 *
	 *  @param  object	 The Group object.
	 *  @param  int	 The role_id.
	 */

	function RoleObserver ($Group) {
		$this->Error();
		if (!$Group || !is_object($Group) || $Group->isError()) {
			$this->setError('Role::'.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;
		return $this->fetchData();
	}

    /**
     *  getID - get the ID of this role.
     *
     *  @return string The ID of the observer.
     */
	function getID() {
		return 'observer';
	}

    /**
     *  getName - get the name of this role.
     *
     *  @return string  The name of this role.
     */
	function getName() {
		return 'Observer';
	}

	/**
	 *  fetchData - May need to refresh database fields.
	 *
	 *  If an update occurred and you need to access the updated info.
	 *
	 *  @return boolean success;
	 */
	function fetchData() {
		$this->setting_array=array();
		//
		//	Forum is_public/allow_anon
		//
		$res = db_query_params ('SELECT group_forum_id,is_public,allow_anonymous FROM forum_group_list WHERE group_id=$1',
					array ($this->Group->getID())) ;
		while ($arr = db_fetch_array($res)) {
			$this->setting_array['forumpublic'][$arr['group_forum_id']] = $arr['is_public'];
			$this->setting_array['forumanon'][$arr['group_forum_id']] = $arr['allow_anonymous'];
		}

		//
		//	Tasks is_public/allow_anon
		//
		$res = db_query_params ('SELECT group_project_id,is_public FROM project_group_list WHERE group_id=$1',
					array ($this->Group->getID())) ;
		while ($arr = db_fetch_array($res)) {
			$this->setting_array['pmpublic'][$arr['group_project_id']] = $arr['is_public'];
		}

		//
		//	Tracker is_public/allow_anon
		//
		$res = db_query_params ('SELECT group_artifact_id,is_public,allow_anon FROM artifact_group_list WHERE group_id=$1',
					array ($this->Group->getID())) ;
		while ($arr = db_fetch_array($res)) {
			$this->setting_array['trackerpublic'][$arr['group_artifact_id']] = $arr['is_public'];
			$this->setting_array['trackeranon'][$arr['group_artifact_id']] = $arr['allow_anon'];
		}

		//
		//	FRS packages can be public/private now
		//
		$res = db_query_params ('SELECT package_id,is_public FROM frs_package WHERE group_id=$1',
					array ($this->Group->getID())) ;
		while ($arr = db_fetch_array($res)) {
			$this->setting_array['frspackage'][$arr['package_id']] = $arr['is_public'];
		}

		//
		//	AnonSCM
		//
		$this->Group->fetchData( $this->Group->getID() );
		$this->setting_array['scmpublic'][0]=$this->Group->enableAnonSCM();
		$this->setting_array['projectpublic'][0]=$this->Group->isPublic();
//echo '<html><body><pre>'.print_r($this->setting_array).'</pre>';
//exit;
		return true;
	}

	/**
	 *  &getRoleVals - get all the values and language text strings for this section.
	 *
	 *  @return array	Assoc array of values for this section.
	 */
	function &getRoleVals($section) {
		global $role_vals, $rbac_permission_names;
		setup_rbac_strings () ;

		//
		//	Optimization - save array so it is only built once per page view
		//
		if (!isset($role_vals[$section])) {

			for ($i=0; $i<count($this->role_values[$section]); $i++) {
				//
				//	Build an associative array of these key values + localized description
				//
				$role_vals[$section][$this->role_values[$section][$i]]=$rbac_permission_names["$section".$this->role_values[$section][$i]];
			}
		}
		return $role_vals[$section];
	}

    /**
     *  getVal - get a value out of the array of settings for this role.
     *
     *  @param  string  The name of the role.
     *  @param  integer The ref_id (ex: group_artifact_id, group_forum_id) for this item.
     *  @return integer The value of this item.
     */
	function getVal($section,$ref_id) {
		global $role_default_array;
		if (!$ref_id) {
			$ref_id=0;
		}
		if (!isset($this->setting_array) && !isset($this->data_array)) {
			$this->setting_array=$role_default_array;
		}
		return $this->setting_array[$section][$ref_id];
	}

    /**
     *  update - update a new in the database.
     *
     *  @param  array   A multi-dimensional array of data in this format: $data['section_name']['
     *  @return boolean True on success or false on failure.
     */
	function update($data) {
		if (!forge_check_perm ('project_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();

////$data['section_name']['ref_id']=$val
		$arr1 = array_keys($data);
		for ($i=0; $i<count($arr1); $i++) {	
			$arr2 = array_keys($data[$arr1[$i]]);
			for ($j=0; $j<count($arr2); $j++) {
				$usection_name=$arr1[$i];
				$uref_id=$arr2[$j];
				$uvalue=$data[$usection_name][$uref_id];
				if (!$uref_id) {
					$uref_id=0;
				}
				if (!$uvalue) {
					$uvalue=0;
				}
				//
				//	See if this setting changed. If so, then update it
				//
				if ($this->getVal($usection_name,$uref_id) != $uvalue) {
					if ($usection_name == 'scmpublic' || 
						$usection_name == 'projectpublic') {
						if (!$data['scmpublic'][0]) {
							$data['scmpublic'][0]=0;
						}
						if (!$data['projectpublic'][0]) {
							$data['projectpublic'][0]=0;
							// Groups cannot be private and have public SCM
							// so we should always ensure that the scm is 
							// private if we change a group to private.
							$data['scmpublic'][0]=0;
						}
						$res = db_query_params ('UPDATE groups SET enable_anonscm=$1, is_public=$2 WHERE group_id=$3',
									array ($data['scmpublic'][0],
									       $data['projectpublic'][0],
									       $this->Group->getID())) ;
							if (!$res) {
								$this->setError('update::group::'.db_error());
								db_rollback();
								return false;
							}

					//
					//	Forum
					//
					} elseif ($usection_name == 'forumpublic' || $usection_name == 'forumanon') {
						//
						//	prevent double-updating each forum
						//
						if ($updated['forum'][$uref_id]) {
							continue;
						}
						$res = db_query_params ('UPDATE forum_group_list SET is_public=$1, allow_anonymous=$2 WHERE group_forum_id=$3 AND group_id=$4',
									array ($data['forumpublic'][$uref_id],
									       $data['forumanon'][$uref_id],
									       $uref_id,
									       $this->Group->getID())) ;
						$updated['forum'][$uref_id]=1;
						if (!$res) {
							$this->setError('update::forum::'.db_error());
							db_rollback();
							return false;
						}
					} elseif ($usection_name == 'pmpublic') {
						$res = db_query_params ('UPDATE project_group_list SET is_public=$1 WHERE group_project_id=$2 AND group_id=$3',
									array ($uvalue,
									       $uref_id,
									       $this->Group->getID())) ;
						if (!$res) {
							$this->setError('update::pm::'.db_error());
							db_rollback();
							return false;
						}

					} elseif ($usection_name == 'frspackage') {
						$res = db_query_params ('UPDATE frs_package SET is_public=$1 WHERE package_id=$2 AND group_id=$3',
									array ($uvalue,
									       $uref_id,
									       $this->Group->getID())) ;
						if (!$res) {
							$this->setError('update::frspackage::'.db_error());
							db_rollback();
							return false;
						}

					} elseif ($usection_name == 'trackerpublic' || $usection_name == 'trackeranon') {
						//
						//	prevent double-updating each forum
						//
						if ($updated['tracker'][$uref_id]) {
							continue;
						}
						$res = db_query_params ('UPDATE artifact_group_list SET is_public=$1, allow_anon=$2 WHERE group_artifact_id=$3 AND group_id=$4',
									array ($data['trackerpublic'][$uref_id],
									       $data['trackeranon'][$uref_id],
									       $uref_id,
									       $this->Group->getID())) ;
						$updated['tracker'][$uref_id]=1;
						if (!$res) {
							$this->setError('update::tracker::'.db_error());
							db_rollback();
							return false;
						}
					}
				}
			}
		}

		db_commit();
		$this->fetchData();
		return true;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

<?php
/**
 * FusionForge file release system
 *
 * Copyright 2002, Tim Perdue/GForge, LLC
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
require_once $gfcommon.'frs/FRSRelease.class.php';

function &get_frs_packages($Group) {
	$res=db_query("SELECT * FROM frs_package WHERE group_id='".$Group->getID()."'");
	if (db_numrows($res) < 1) {
		return false;
	}
	$ps = array();
	while($arr = db_fetch_array($res)) {
		$ps[]=new FRSPackage($Group,$arr['package_id'],$arr);
	}
	return $ps;
}

/**
 * Gets a FRSPackage object from the given package id
 * 
 * @param package_id	the package id
 * @param data	the DB handle if passed in (optional)
 * @return	the FRSPackage object	
 */
function &frspackage_get_object($package_id, $data=false) {
	global $FRSPACKAGE_OBJ;
	if (!isset($FRSPACKAGE_OBJ['_'.$package_id.'_'])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res=db_query("SELECT * FROM frs_package
				WHERE package_id='$package_id'");
			if (db_numrows($res)<1) {
				$FRSPACKAGE_OBJ['_'.$package_id.'_']=false;
				return false;
			}
			$data =& db_fetch_array($res);			
		}
		$Group =& group_get_object($data['group_id']);
		$FRSPACKAGE_OBJ['_'.$package_id.'_']= new FRSPackage($Group,$data['package_id'],$data);
	}
	return $FRSPACKAGE_OBJ['_'.$package_id.'_'];
}

class FRSPackage extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;
	var $package_releases;

	/**
	 * The Group object.
	 *
	 * @var  object  $Group.
	 */
	var $Group; //group object

	/**
	 *  Constructor.
	 *
	 *  @param  object  The Group object to which this FRSPackage is associated.
	 *  @param  int  The package_id.
	 *  @param  array   The associative array of data.
	 *	@return	boolean	success.
	 */
	function FRSPackage(&$Group, $package_id=false, $arr=false) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError('FRSPackage:: No Valid Group Object');
			return false;
		}
		if ($Group->isError()) {
			$this->setError('FRSPackage:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($package_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($package_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Group_id in db result does not match Group Object');
					$this->data_array=null;
					return false;
				}
//
//	Add an is_public check here
//
			}
		}
		return true;
	}

	/**
	 *	create - create a new FRSPackage in the database.
	 *
	 *	@param	string	The name of this package.
	 *	@param	boolean	Whether it's public or not. 1=public 0=private.
	 *	@return	boolean success.
	 */
	function create($name,$is_public=1) {
		global $sys_apache_user,$sys_apache_group;
		if (strlen($name) < 3) {
			$this->setError(_('FRSPackage Name Must Be At Least 3 Characters'));
			return false;
		}
		if (!util_is_valid_filename($name)) {
			$this->setError(_('FRSPackage::Update: Package Name can only be alphanumeric'));
		}
		$perm =& $this->Group->getPermission( session_get_user() );

		if (!$perm || !is_object($perm) || !$perm->isReleaseTechnician()) {
			$this->setPermissionDeniedError();
			return false;
		}

		$res=db_query("SELECT * FROM frs_package WHERE group_id='".$this->Group->getID()."'
			AND name='".htmlspecialchars($name)."'");
		if (db_numrows($res)) {
			$this->setError('FRSPackage::create() Error Adding Package: Name Already Exists');
			return false;
		}

		$sql="INSERT INTO frs_package(group_id,name,status_id,is_public)
			VALUES ('".$this->Group->getId()."','".htmlspecialchars($name)."','1','$is_public')";

		db_begin();
		$result=db_query($sql);
		if (!$result) {
			db_rollback();
			$this->setError('FRSPackage::create() Error Adding Package: '.db_error());
			return false;
		}
		$this->package_id=db_insertid($result,'frs_package','package_id');
		if (!$this->fetchData($this->package_id)) {
			db_rollback();
			return false;
		} else {

			//make groupdir if it doesn't exist
			$groupdir = $GLOBALS['sys_upload_dir'].'/'.$this->Group->getUnixName();
			if (!is_dir($groupdir)) {
				@mkdir($groupdir);
			}

			$newdirlocation = $GLOBALS['sys_upload_dir'].'/'.$this->Group->getUnixName().'/'.$this->getFileName();
			exec("/bin/mkdir $newdirlocation",$out);
			// this 2 should normally silently fail (because it�s called with the apache user) but if it�s root calling the create() method, then the owner and group for the directory should be changed
			@chown($newdirlocation,$sys_apache_user);
			@chgrp($newdirlocation,$sys_apache_group);
			db_commit();
			return true;
		}
	}

	/**
	 *  fetchData - re-fetch the data for this Package from the database.
	 *
	 *  @param  int  The package_id.
	 *  @return boolean	success.
	 */
	function fetchData($package_id) {
		$res=db_query("SELECT * FROM frs_package
			WHERE package_id='$package_id'
			AND group_id='". $this->Group->getID() ."'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError('FRSPackage::fetchData()  Invalid package_id'.db_error());
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *  getGroup - get the Group object this FRSPackage is associated with.
	 *
	 *  @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *  getID - get this package_id.
	 *
	 *  @return	int	The id of this package.
	 */
	function getID() {
		return $this->data_array['package_id'];
	}

	/**
	 *  getName - get the name of this package.
	 *
	 *  @return string  The name of this package.
	 */
	function getName() {
		return $this->data_array['name'];
	}

	/**
	 *  getFileName - get the filename of this package.
	 *
	 *  @return string  The name of this package.
	 */
	function getFileName() {
		return eregi_replace("[^-A-Z0-9_\.]",'',$this->data_array['name']);
	}

	/**
	 *  getStatus - get the status of this package.
	 *
	 *  @return int	The status.
	 */
	function getStatus() {
		return $this->data_array['status_id'];
	}

	/**
	 *	isPublic - whether non-group-members can view.
	 *
	 *	@return boolean   is_public.
	 */
	function isPublic() {
		return $this->data_array['is_public'];
	}

	/**
	 *  setMonitor - Add the current user to the list of people monitoring this package.
	 *
	 *  @return	boolean	success.
	 */
	function setMonitor() {
		if (!session_loggedin()) {
			$this->setError(_('You can only monitor if you are logged in'));
			return false;
		}
		$sql="SELECT * FROM filemodule_monitor
			WHERE user_id='".user_getid()."'
			AND filemodule_id='".$this->getID()."';";
		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO filemodule_monitor (filemodule_id,user_id)
				VALUES ('".$this->getID()."','".user_getid()."')";

			$result = db_query($sql);

			if (!$result) {
				$this->setError('Unable to add monitor: '.db_error());
				return false;
			}

		}
		return true;
	}

	/**
	 *  stopMonitor - Remove the current user from the list of people monitoring this package.
	 *
	 *  @return	boolean	success.
	 */
	function stopMonitor() {
		if (!session_loggedin()) {
			$this->setError(_('You can only monitor if you are logged in'));
			return false;
		}
		$sql="DELETE FROM filemodule_monitor
			WHERE user_id='".user_getid()."'
			AND filemodule_id='".$this->getID()."';";
		return db_query($sql);
	}

	/**
	 *	getMonitorCount - Get the count of people monitoring this package
	 *
	 *	@return int the count
	 */
	function getMonitorCount() {
		$sql = "select count(*) as count from filemodule_monitor where filemodule_id = ".$this->getID();
		$res = db_result(db_query($sql), 0, 0);
		if ($res < 0) {
			$this->setError('FRSPackage::getMonitorCount() Error On querying monitor count: '.db_error());
			return false;
		}
		return $res;
	}	

	/**
	 *  isMonitoring - Is the current user in the list of people monitoring this package.
	 *
	 *  @return	boolean	is_monitoring.
	 */
	function isMonitoring() {
		if (!session_loggedin()) {
			return false;
		}
		$sql="SELECT * FROM filemodule_monitor
			WHERE user_id='".user_getid()."'
			AND filemodule_id='".$this->getID()."';";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 *  getMonitorIDs - Return an array of user_id's of the list of people monitoring this package.
	 *
	 *  @return	array	The array of user_id's.
	 */
	function &getMonitorIDs() {
		$res=db_query("SELECT user_id
			FROM filemodule_monitor
			WHERE filemodule_id='".$this->getID()."'");
		return util_result_column_to_array($res);
	}

	/**
	 *	update - update an FRSPackage in the database.
	 *
	 *	@param	string	The name of this package.
	 *	@param	int	The status_id of this package from frs_status table.
	 *	@return	boolean success.
	 */
	function update($name,$status) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSPackage Name Must Be At Least 3 Characters'));
			return false;
		}

		$perm =& $this->Group->getPermission( session_get_user() );

		if (!$perm || !is_object($perm) || !$perm->isReleaseTechnician()) {
			$this->setPermissionDeniedError();
			return false;
		}		
		if($this->getName()!=htmlspecialchars($name)) {
			$res=db_query("SELECT * FROM frs_package WHERE group_id='".$this->Group->getID()."'
			AND name='".htmlspecialchars($name)."'");
			if (db_numrows($res)) {
				$this->setError('FRSPackage::update() Error Updating Package: Name Already Exists');
				return false;
			}
		}
		db_begin();
		$res=db_query("UPDATE frs_package SET
			name='".htmlspecialchars($name)."',
			status_id='$status'
			WHERE group_id='".$this->Group->getID()."'
			AND package_id='".$this->getID()."'");
		if (!$res || db_affected_rows($res) < 1) {
			db_rollback();
			$this->setError('FRSPackage::update() Error On Update: '.db_error());
			return false;
		}

		$olddirname = $this->getFileName();
		if(!$this->fetchData($this->getID())){
			db_rollback();
			$this->setError('FRSPackage::update() Error Updating Package: Couldn�t fetch data');
			return false;
		}
		$newdirname = $this->getFileName();
		$olddirlocation = $GLOBALS['sys_upload_dir'].'/'.$this->Group->getUnixName().'/'.$olddirname;
		$newdirlocation = $GLOBALS['sys_upload_dir'].'/'.$this->Group->getUnixName().'/'.$newdirname;
		
		if(($olddirname!=$newdirname)){
			if(is_dir($newdirlocation)){
				db_rollback();
				$this->setError('FRSPackage::update() Error Updating Package: Directory Already Exists');
				return false;	
			} else {
				if(!@rename($olddirlocation,$newdirlocation)) {
					db_rollback();
					$this->setError('FRSPackage::update() Error Updating Package: Couldn�t rename dir');
					return false;
				}
			}
		}	
		db_commit();
		return true;
	}

	/**
	 *	getReleases - gets Release objects for all the releases in this package.
	 *
	 *  return  array   Array of FRSRelease Objects.
	 */
	function &getReleases() {
		if (!is_array($this->package_releases) || count($this->package_releases) < 1) {
			$this->package_releases=array();
			$res=db_query("SELECT * FROM frs_release WHERE package_id='".$this->getID()."'");
			while ($arr = db_fetch_array($res)) {
				$this->package_releases[]=new FRSRelease($this,$arr['release_id'],$arr);
			}
		}
		return $this->package_releases;
	}

	/**
	 *  delete - delete this package and all its related data.
	 *
	 *  @param  bool	I'm Sure.
	 *  @param  bool	I'm REALLY sure.
	 *  @return   bool true/false;
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError();
			return false;
		}
		$perm =& $this->Group->getPermission( session_get_user() );

		if (!$perm || !is_object($perm) || !$perm->isReleaseTechnician()) {
			$this->setPermissionDeniedError();
			return false;
		}
		$r =& $this->getReleases();
		for ($i=0; $i<count($r); $i++) {
			if (!is_object($r[$i]) || $r[$i]->isError() || !$r[$i]->delete($sure, $really_sure)) {
				$this->setError('Release Error: '.$r[$i]->getName().':'.$r[$i]->getErrorMessage());
				return false;
			}
		}
		$dir=$GLOBALS['sys_upload_dir'].'/'.
			$this->Group->getUnixName() . '/' .
			$this->getFileName().'/';

		// double-check we're not trying to remove root dir
		if (util_is_root_dir($dir)) {
			$this->setError('Package::delete error: trying to delete root dir');
			return false;
		}
		exec('rm -rf '.$dir);

		db_query("DELETE FROM frs_package WHERE package_id='".$this->getID()."'
			AND group_id='".$this->Group->getID()."'");
		return true;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

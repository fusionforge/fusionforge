<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 *
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */

require_once 'common/dao/include/DataAccessObject.class.php';

/**
 *  Data Access Object for mailing lists
 */
class MailmanListDao extends DataAccessObject {

	public function __construct($da) {
		parent::__construct($da);
	}


	/**
	 * Search active (=not deteted) mailing lists
	 * return all active lists
	 * @return DataAccessResult
	 */
	function & searchAllActiveML() {
		$sql = "SELECT *
			FROM mail_group_list
			WHERE is_public IN (0,1)";
		return $this->retrieve($sql,array());
	}

	/**
	 * Searches by group_list_id
	 * @return DataAccessResult
	 */
	function & searchByGroupListId($group_list_id) {
		$group_list_id = $this->da->quoteSmart($group_list_id);
		$sql = "SELECT * FROM mail_group_list
			WHERE group_list_id = $1";
		return $this->retrieve($sql,array($group_list_id));
	}
	/**
	 * Searches by list_name
	 * @return DataAccessResult
	 */
	function & searchByName($realListName) {
		$realListName = $this->da->quoteSmart($realListName);
		$sql = 'SELECT 1 FROM mail_group_list WHERE lower(list_name)=$1';
		return $this->retrieve($sql,array($realListName));
	}
	/**
	 * Searches by group_id
	 * @return DataAccessResult
	 */
	function & searchByGroupId($group_id) {
		$group_id = $this->da->quoteSmart($group_id);
		$sql = "SELECT * FROM mail_group_list
			WHERE group_id = $1 ORDER BY group_list_id";
		return $this->retrieve($sql,array($group_id));
	}
	/**
	 * Searches data with group_list_id and group_id
	 * @return DataAccessResult
	 */
	function & searchListFromGroup($group_list_id,$group_id) {
		$group_id = $this->da->quoteSmart($group_id);
		$group_list_id = $this->da->quoteSmart($group_list_id);
		$sql = "SELECT * FROM mail_group_list
			WHERE group_id = $1 AND group_list_id=$2";
		return $this->retrieve($sql,array($group_id,$group_list_id));
	}

	function & insertNewList($group_id, $realListName,$isPublic,$listPassword,$creator_id,$requested,$description) {
		$group_id = $this->da->quoteSmart($group_id);
		$realListName = $this->da->quoteSmart($realListName);
		$isPublic = $this->da->quoteSmart($isPublic);
		$creator_id = $this->da->quoteSmart($creator_id);
		$requested = $this->da->quoteSmart($requested);
		$listPassword = $this->da->quoteSmart($listPassword);
		$description = $this->da->quoteSmart($description);
		$sql = "INSERT INTO mail_group_list (group_id, list_name, is_public, password, list_admin, status, description) VALUES ($1,$2,$3,$4,$5,$6,$7);";
		return db_insertid($this->update($sql,array($group_id, $realListName,$isPublic,$listPassword,$creator_id,$requested,$description)),'mail_group_list','group_list_id');
	}
	function  deleteList($group_id, $list_id) {
		return $this->updateList($list_id,$group_id,"deleted",9,1);
	}

	function  updateList($group_list_id,$group_id, $description, $isPublic,$status) {
		$group_id = $this->da->quoteSmart($group_id);
		$group_list_id = $this->da->quoteSmart($group_list_id);
		$isPublic = $this->da->quoteSmart($isPublic);
		$status = $this->da->quoteSmart($status);
		$description = $this->da->quoteSmart($description);
		$sql = "UPDATE mail_group_list SET is_public=$1, description=$2," .
			"status=$3 WHERE group_list_id=$4 AND group_id=$5;";
		return $this->update($sql,array($isPublic,$description,$status,$group_list_id,$group_id));

	}

	function  newSubscriber($usermail, $username, $userpasswd, $listname) {
		$usermail = $this->da->quoteSmart($usermail);
		$username = $this->da->quoteSmart($username);
		$userpasswd = $this->da->quoteSmart($userpasswd);
		$listname = $this->da->quoteSmart($listname);
		$sql="INSERT INTO plugin_mailman (address,password,name,listname) VALUES ($1,$2,$3,$4);";
		return $this->update($sql,array($usermail,$userpasswd,$username,$listname));
	}

	function  deleteSubscriber($usermail, $listname) {
		$usermail = $this->da->quoteSmart($usermail);
		$listname = $this->da->quoteSmart($listname);
		$sql="DELETE FROM  plugin_mailman WHERE listname=$1 AND address=$2;";
		return $this->update($sql,array($listname,$usermail));
	}
	function & userIsMonitoring($usermail,$listname) {
		$usermail = $this->da->quoteSmart($usermail);
		$listname = $this->da->quoteSmart($listname);
		$sql="SELECT count(*) AS count FROM plugin_mailman WHERE address=$1 AND listname=$2;";
		return $this->retrieve($sql,array($usermail,$listname));
	}

	function & listsMonitoredByUser($usermail) {
		$usermail = $this->da->quoteSmart($usermail);
		$sql="SELECT groups.group_name,groups.group_id,mail_group_list.group_list_id,mail_group_list.list_name ".
		     "FROM groups,mail_group_list,plugin_mailman ".
		     "WHERE groups.group_id=mail_group_list.group_id AND groups.status ='A' ".
		     "AND mail_group_list.list_name=plugin_mailman.listname ".
		     "AND plugin_mailman.address=$1 ORDER BY group_name DESC";

		return $this->retrieve($sql,array($usermail));
	}

	function & compareInfos($mail) {
		$mail = $this->da->quoteSmart($mail);
		$sql="SELECT password, name FROM  plugin_mailman WHERE address=$1;";
		return $this->retrieve($sql,array($mail));

	}
	function updateInfos($mail,$passwd,$name) {
		$mail = $this->da->quoteSmart($mail);
		$passwd = $this->da->quoteSmart($passwd);
		$name = $this->da->quoteSmart($name);
		$sql="UPDATE plugin_mailman SET password=$1, name=$2 WHERE address=$3;";
		return $this->update($sql,array($passwd,$name,$mail));

	}
}
?>

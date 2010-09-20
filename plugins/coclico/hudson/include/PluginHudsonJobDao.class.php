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
 */
require_once('common/dao/include/DataAccessObject.class.php');

/**
 *  Data Access Object for PluginHudsonJob 
 */
class PluginHudsonJobDao extends DataAccessObject {
	/**
	 * Constructs the PluginHudsonJobDao
	 * @param $da instance of the DataAccess class
	 */
	function PluginHudsonJobDao( $da ) {
		DataAccessObject::DataAccessObject($da);
	}

	/**
	 * Gets all jobs in the db
	 * @return DataAccessResult
	 */
	function & searchAll() {
		$sql = "SELECT * FROM plugin_hudson_job";
		return $this->retrieve($sql);
	}

	/**
	 * Searches PluginHudsonJob by Codendi group ID 
	 * @return DataAccessResult
	 */
	function & searchByGroupID($group_id) {
		$sql = "SELECT *  
			FROM plugin_hudson_job
			WHERE group_id = $1";
		$group_id = $this->da->quoteSmart($group_id);
		return $this->retrieve($sql,array($group_id));
	}

	/**
	 * Searches PluginHudsonJob by job ID 
	 * @return DataAccessResult
	 */
	function & searchByJobID($job_id) {
		$sql = "SELECT *  
			FROM plugin_hudson_job
			WHERE job_id = $1";
		$job_id = $this->da->quoteSmart($job_id);
		return $this->retrieve($sql,array($job_id));
	}

	/**
	 * Searches PluginHudsonJob by job name 
	 * @return DataAccessResult
	 */
	function & searchByJobName($job_name) {
		$sql = "SELECT *  
			FROM plugin_hudson_job
			WHERE name = $1";
		$job_name = $this->da->quoteSmart($job_name);
		return $this->retrieve($sql,array($job_name));
	}

	/**
	 * Searches PluginHudsonJob by user ID
	 * means "all the jobs of all projects the user is member of" 
	 * @return DataAccessResult
	 */
	function & searchByUserID($user_id) {
		$sql = "SELECT j.*  
			FROM plugin_hudson_job j, users u, user_group ug
			WHERE ug.group_id = j.group_id AND
			u.user_id = ug.user_id AND 
			u.user_id = $1";
		$user_id = $this->da->quoteSmart($user_id);
		return $this->retrieve($sql,array($user_id));
	}

	/**
	 * create a row in the table plugin_hudson_job 
	 * @return true if there is no error
	 */
	function createHudsonJob($group_id, $hudson_job_url, $job_name, $use_svn_trigger = false, $use_cvs_trigger = false, $token = null) {
		$sql = "INSERT INTO plugin_hudson_job (group_id, job_url, name, use_svn_trigger, use_cvs_trigger, token) VALUES ($1, $2, $3, $4, $5, $6)";
		$group_id = $this->da->quoteSmart($group_id);
		$hudson_job_url = $this->da->quoteSmart($hudson_job_url);
		$job_name = $this->da->quoteSmart($job_name);
		$use_svn_trigger = ($use_svn_trigger?1:0);
		$use_cvs_trigger = ($use_cvs_trigger?1:0);
		$token = (($token !== null)?$this->da->quoteSmart($token):$this->da->quoteSmart(''));
		$ok = $this->update($sql,array($group_id, $hudson_job_url, $job_name , $use_svn_trigger, $use_cvs_trigger,$token));
		return $ok;
	}

	function updateHudsonJob($job_id, $hudson_job_url, $job_name, $use_svn_trigger = false, $use_cvs_trigger = false, $token = null) {
		$sql = "UPDATE plugin_hudson_job SET job_url = $1, name = $2, use_svn_trigger = $3, use_cvs_trigger = $4, token = $5 WHERE job_id = $6";
		$hudson_job_url = $this->da->quoteSmart($hudson_job_url);
		$job_name = $this->da->quoteSmart($job_name);
		$use_svn_trigger = ($use_svn_trigger?1:0);
		$use_cvs_trigger = ($use_cvs_trigger?1:0);
		$token = (($token !== null)?$this->da->quoteSmart($token):$this->da->quoteSmart(''));
		$job_id=$this->da->quoteSmart($job_id);
		$ok = $this->update($sql,array($hudson_job_url, $job_name , $use_svn_trigger, $use_cvs_trigger,$token, $job_id));
		return $ok;
	}

	function deleteHudsonJob($job_id) {
		$sql = "DELETE FROM plugin_hudson_job WHERE job_id = $1";
		$job_id=$this->da->quoteSmart($job_id);
		$updated = $this->update($sql,array($job_id));
		return $updated;
	}

	function deleteHudsonJobsByGroupID($group_id) {
		$sql = "DELETE FROM plugin_hudson_job WHERE group_id = $1";
		$group_id = $this->da->quoteSmart($group_id);
		$updated = $this->update($sql,array($group_id));
		return $updated;
	}

}

?>

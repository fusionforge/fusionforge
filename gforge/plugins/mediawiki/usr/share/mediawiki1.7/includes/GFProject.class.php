<?php   
/**
 *	Group object
 *
 *	Sets up database results and preferences for a group and abstracts this info.
 *
 *	Foundry.class.php and Project.class.php call this.
 *
 *	Project.class.php contains all the deprecated API from the old group.php file
 *
 *	DEPENDS on user.php being present and setup properly
 *
 *	GENERALLY YOU SHOULD NEVER INSTANTIATE THIS OBJECT DIRECTLY
 *	USE group_get_object() to instantiate properly
 *
 * @version   $Id: Group.class.php 4707 2005-10-04 11:53:48Z danper $
 * @author Tim Perdue <tperdue@valinux.com>
 * @date 2000-08-28
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('www/include/BaseLanguage.class.php');

$GROUP_OBJ=array();

/**
 *  group_get_object() - Get the group object.
 *
 *  group_get_object() is useful so you can pool group objects/save database queries
 *  You should always use this instead of instantiating the object directly.
 *
 *  You can now optionally pass in a db result handle. If you do, it re-uses that query
 *  to instantiate the objects.
 *
 *  IMPORTANT! That db result must contain all fields
 *  from groups table or you will have problems
 *
 *  @param		int		Required
 *  @param		int		Result set handle ("SELECT * FROM groups WHERE group_id=xx")
 *  @return a group object or false on failure
 */
function &group_get_object($group_id,$res=false) {
	//create a common set of group objects
	//saves a little wear on the database

	//automatically checks group_type and 
	//returns appropriate object
	
	global $GROUP_OBJ;
	if (!isset($GROUP_OBJ["_".$group_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res=db_query("SELECT * FROM groups WHERE group_id='$group_id'");
		}
		if (!$res || db_numrows($res) < 1) {
			$GROUP_OBJ["_".$group_id."_"]=false;
		} else {
			/*
				check group type and set up object
			*/
			if (db_result($res,0,'type_id')==1) {
				//project
				$GROUP_OBJ["_".$group_id."_"]= new Group($group_id,$res);
			} else {
				//invalid
				$GROUP_OBJ["_".$group_id."_"]=false;
			}
		}
	}
	return $GROUP_OBJ["_".$group_id."_"];
}
function &group_get_object_by_name($groupname) {
	$res=db_query("SELECT * FROM groups WHERE unix_group_name='$groupname'");
	return group_get_object(db_result($res,0,'group_id'),$res);
}

class Group extends Error {
	/**
	 * Associative array of data from db.
	 * 
	 * @var array $data_array.
	 */
	var $data_array;

	/**
	 * array of User objects.
	 * 
	 * @var array $membersArr.
	 */
	var $membersArr;

	/**
	 * Permissions data row from db.
	 * 
	 * @var array $perm_data_array.
	 */
	var $perm_data_array;

	/**
	 * Whether the use is an admin/super user of this project.
	 *
	 * @var bool $is_admin.
	 */
	var $is_admin;

	/**
	 * Artifact types result handle.
	 * 
	 * @var int $types_res.
	 */
	var $types_res;

	/**
	 * Associative array of data for plugins.
	 * 
	 * @var array $plugins_array.
	 */
	var $plugins_array;

	/**
	 *	Group - Group object constructor - use group_get_object() to instantiate.
	 *
	 *	@param	int		Required - group_id of the group you want to instantiate.
	 *	@param	int		Database result from select query OR associative array of all columns.
	 */
	function Group($id=false, $res=false) {
		$this->Error();
		if (!$id) {
			//setting up an empty object
			//probably going to call create()
			return true;
		}
		if (!$res) {
			if (!$this->fetchData($id)) {
				return false;
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
					$this->setError('Group Not Found');
					$this->data_array=array();
					return false;
				} else {
					//set up an associative array for use by other functions
					db_reset_result($res);
					$this->data_array =& db_fetch_array($res);
				}
			}
		}
		
		$systemGroups = array(GROUP_IS_NEWS, GROUP_IS_STATS, GROUP_IS_PEER_RATINGS);
		if(!$this->isPublic() && !in_array($id, $systemGroups)) {
			$perm =& $this->getPermission(session_get_user());

			if (!$perm || !is_object($perm) || !$perm->isMember()) {
				$this->setError(_('Permission denied'), ERROR__PERMISSION_DENIED_ERROR);
				return false;
			}
		}
		return true;
	}

	/**
	 *	fetchData - May need to refresh database fields if an update occurred.
	 *
	 *	@param	int	The group_id.
	 */
	function fetchData($group_id) {
		$res = db_query("SELECT * FROM groups WHERE group_id='$group_id'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError('fetchData():: '.db_error());
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		return true;
	}

	/**
	 *  getPlugins -  get a list of all available group plugins
	 *
	 *  @return	array	array containing plugin_id => plugin_name
	 */
	function getPlugins() {
		if (!isset($this->plugins_data)) {
			$this->plugins_data = array () ;
			$sql="SELECT group_plugin.plugin_id, plugins.plugin_name
							  FROM group_plugin, plugins
				  WHERE group_plugin.group_id=".$this->getID()."
								AND group_plugin.plugin_id = plugins.plugin_id" ;
			$res=db_query($sql);
			$rows = db_numrows($res);

			for ($i=0; $i<$rows; $i++) {
				$plugin_id = db_result($res,$i,'plugin_id');
				$this->plugins_data[$plugin_id] = db_result($res,$i,'plugin_name');
			}
		}
		return $this->plugins_data ;
	}

	/**
	 *  usesPlugin - returns true if the group uses a particular plugin 
	 *
	 *  @param	string	name of the plugin
	 *  @return	boolean	whether plugin is being used or not
	 */
	function usesPlugin($pluginname) {
		$plugins_data = $this->getPlugins() ;
		foreach ($plugins_data as $p_id => $p_name) {
			if ($p_name == $pluginname) {
				return true ;
			}
		}
		return false ;
	}

	/**
	 *  setPluginUse - enables/disables plugins for the group
	 *
	 *  @param	string	name of the plugin
	 *  @param	boolean	the new state
	 *  @return	string	database result 
	 */
	function setPluginUse($pluginname, $val=true) {
		if ($val == $this->usesPlugin($pluginname)) {
			// State is already good, returning
			return true ;
		}
		$sql="SELECT plugin_id
			  FROM plugins
			  WHERE plugin_name = '" . $pluginname . "'" ;
		$res=db_query($sql);
		$rows = db_numrows($res);
		if ($rows == 0) {
			// Error: no plugin by that name
			return false ;
		}
		$plugin_id = db_result($res,0,'plugin_id');
		// Invalidate cache
		unset ($this->plugins_data) ;
		if ($val) {
			$sql="INSERT INTO group_plugin (group_id, plugin_id)
							  VALUES (". $this->getID() . ", ". $plugin_id .")" ;
			$res=db_query($sql);
			return $res ;
		} else {
			$sql="DELETE FROM group_plugin
				WHERE group_id = ". $this->getID() . "
				AND plugin_id = ". $plugin_id ;
			$res=db_query($sql);
			return $res ;
		}
	}

	/**
	 *	getPermission - Return a Permission for this Group and the specified User.
	 *
	 *	@param	object	The user you wish to get permission for (usually the logged in user).
	 *	@return	object	The Permission.
	 */
	function &getPermission(&$_user) {
		return permission_get_object($this, $_user);
	}

	/**
	 *	isPublic - Simply returns the is_public flag from the database.
	 *
	 *	@return	boolean	is_public.
	 */
	function isPublic() {
		return $this->data_array['is_public'];
	}

	/**
	 *	getID - Simply return the group_id for this object.
	 *
	 *	@return int group_id.
	 */
	function getID() {
		return $this->data_array['group_id'];
	}

	/**
	 *	usesSCM - whether or not this group has opted to use SCM.
	 *
	 *	@return	boolean	uses_scm.
	 */
	function usesSCM() {
		global $sys_use_scm;
		if ($sys_use_scm) {
			return $this->data_array['use_scm'];
		} else {
			return false;
		}
	}

	/**
	 *	usesMail - whether or not this group has opted to use mailing lists.
	 *
	 *	@return	boolean uses_mail.
	 */
	function usesMail() {
		global $sys_use_mail;
		if ($sys_use_mail) {
			return $this->data_array['use_mail'];
		} else {
			return false;
		}
	}

	/**
	 * 	usesNews - whether or not this group has opted to use news.
	 *
	 *	@return	boolean	uses_news.
	 */
	function usesNews() {
		global $sys_use_news;
		if ($sys_use_news) {
			return $this->data_array['use_news'];
		} else {
			return false;
		}
	}

	/**
	 *	usesForum - whether or not this group has opted to use discussion forums.
	 *
	 *  @return	boolean	uses_forum.
	 */
	function usesForum() {
		global $sys_use_forum;
		if ($sys_use_forum) {
			return $this->data_array['use_forum'];
		} else {
			return false;
		}
	}	   

	/**
	 *  usesStats - whether or not this group has opted to use stats.
	 *
	 *  @return	boolean	uses_stats.
	 */
	function usesStats() {
		return $this->data_array['use_stats'];
	}

	/**
	 *  usesFRS - whether or not this group has opted to use file release system.
	 *
	 *  @return	boolean	uses_frs.
	 */
	function usesFRS() {
		global $sys_use_frs;
		if ($sys_use_frs) {
			return $this->data_array['use_frs'];
		} else {
			return false;
		}
	}

	/**
	 *  usesTracker - whether or not this group has opted to use tracker.
	 *
	 *  @return	boolean	uses_tracker.
	 */
	function usesTracker() {
		global $sys_use_tracker;
		if ($sys_use_tracker) {
			return $this->data_array['use_tracker'];
		} else {
			return false;
		}
	}

	/**
	 *  usesDocman - whether or not this group has opted to use docman.
	 *
	 *  @return	boolean	uses_docman.
	 */
	function usesDocman() {
		global $sys_use_docman;
		if ($sys_use_docman) {
			return $this->data_array['use_docman'];
		} else {
			return false;
		}
	}

	/**
	 *  usesFTP - whether or not this group has opted to use FTP.
	 *
	 *  @return	boolean	uses_ftp.
	 */
	function usesFTP() {
		global $sys_use_ftp;
		if ($sys_use_ftp) {
			return $this->data_array['use_ftp'];
		} else {
			return false;
		}
	}

	/**
	 *  usesSurvey - whether or not this group has opted to use surveys.
	 *
	 *  @return	boolean	uses_survey.
	 */
	function usesSurvey() {
		global $sys_use_survey;
		if ($sys_use_survey) {
			return $this->data_array['use_survey'];
		} else {
			return false;
		}
	}	   

	/**
	 *  usesPM - whether or not this group has opted to Project Manager.
	 *
	 *  @return	boolean	uses_projman.
	 */
	function usesPM() {
		global $sys_use_pm;
		if ($sys_use_pm) {
			return $this->data_array['use_pm'];
		} else {
			return false;
		}
	}

	/**
	 *	isProject - Simple boolean test to see if it's a project or not.
	 *
	 *	@return	boolean is_project.
	 */
	function isProject() {
		if ($this->getType()==1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	getType() - Foundry, project, etc.
	 *
	 *	@return	int	The type flag from the database.
	 */
	function getType() {
		return $this->data_array['type_id'];
	}

	/**
	 *  getUnixName - the unix_name
	 *
	 *  @return	string	unix_name.
	 */
	function getUnixName() {
		return strtolower($this->data_array['unix_group_name']);
	}

	/**
	 *  getPublicName - the full-length public name.
	 *
	 *  @return	string	The group_name.
	 */
	function getPublicName() {
		return htmlspecialchars($this->data_array['group_name']);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

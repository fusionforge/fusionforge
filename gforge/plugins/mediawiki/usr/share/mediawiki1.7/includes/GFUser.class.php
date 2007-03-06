<?php
/**
 * User class
 *
 * Sets up database results and preferences for a user and abstracts this info
 *
 *  You can now optionally pass in a db result
 *  handle. If you do, it re-uses that query
 *  to instantiate the objects
 *
 *  IMPORTANT! That db result must contain all fields
 *  from users table or you will have problems
 *
 * GENERALLY YOU SHOULD NEVER INSTANTIATE THIS OBJECT DIRECTLY
 * USE user_get_object() to instantiate properly - this will pool the objects
 * and increase efficiency
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id: User.class 5278 2006-02-09 16:25:13Z danper $
 * @author Tim Perdue tperdue@valinux.com
 * @date 2000-10-11
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

$USER_OBJ=array();

/**
 * user_get_object() - Get User object by user ID.
 *  user_get_object is useful so you can pool user objects/save database queries
 *  You should always use this instead of instantiating the object directly 
 *
 *  @param		int		The ID of the user - required
 *  @param		int		The result set handle ("SELECT * FROM USERS WHERE user_id=xx")
 *  @return a user object or false on failure
 *
 */
function &user_get_object($user_id,$res=false) {
	//create a common set of group objects
	//saves a little wear on the database
	
	//automatically checks group_type and 
	//returns appropriate object
	
	global $USER_OBJ;
	if (!isset($USER_OBJ["_".$user_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res=db_query("SELECT * FROM users WHERE user_id='$user_id'");
		}
		if (!$res || db_numrows($res) < 1) {
			$USER_OBJ["_".$user_id."_"]=false;
		} else {
			$USER_OBJ["_".$user_id."_"]= new GFUser($user_id,$res);
		}
	}
	return $USER_OBJ["_".$user_id."_"];
}

function &user_get_objects($id_arr) {
	global $USER_OBJ;
	$fetch = array();
	$return = array();

	for ($i=0; $i<count($id_arr); $i++) {
		//
		//  See if this ID already has been fetched in the cache
		//
		if (!$id_arr[$i]) {
			continue;
		}
		if (!isset($USER_OBJ["_".$id_arr[$i]."_"])) {
			$fetch[]=$id_arr[$i];
		} else {
			$return[] =& $USER_OBJ["_".$id_arr[$i]."_"];
		}
	}
	if (count($fetch) > 0) {
		$sql="SELECT * FROM users WHERE user_id IN ('".implode($fetch,'\',\'') ."')";
		$res=db_query($sql);
		while ($arr =& db_fetch_array($res)) {
			$USER_OBJ["_".$arr['user_id']."_"] = new User($arr['user_id'],$arr);
			$return[] =& $USER_OBJ["_".$arr['user_id']."_"];
		}
	}
	return $return;
}

function &user_get_objects_by_name($username_arr) {
	$res=db_query("SELECT user_id FROM users WHERE user_name IN ('".implode($username_arr,'\',\'')."')");
	$arr =& util_result_column_to_array($res,0);
	return user_get_objects($arr);
}

class GFUser extends Error {
	/** 
	 * Associative array of data from db.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;
	
	/**
	 * Is this person a site super-admin?
	 *
	 * @var		bool	$is_super_user
	 */
	var $is_super_user;

	/**
	 * Is this person the logged in user?
	 *
	 * @var		bool	$is_logged_in
	 */
	var $is_logged_in;

	/**
	 * Array of preferences
	 *
	 * @var		array	$user_pref
	 */
	var $user_pref;

	var $theme;
	var $theme_id;

	/**
	 *	User($id,$res) - CONSTRUCTOR - GENERALLY DON'T USE THIS
	 *
	 *	instead use the user_get_object() function call
	 *
	 *	@param	int		The user_id
	 *	@param	int		The database result set OR array of data
	 */
	function GFUser($id=false,$res=false) {
		$this->Error();
		if (!$id) {
			//setting up an empty object
			//probably going to call create()
			return true;
		}
		if (!$res) {
			$this->fetchData($id);
		} else {
			if (is_array($res)) {
				$this->data_array =& $res;
			} elseif (db_numrows($res) < 1) {
				//function in class we extended
				$this->setError('User Not Found');
				$this->data_array=array();
				return false;
			} else {
				//set up an associative array for use by other functions
				db_reset_result($res);
				$this->data_array =& db_fetch_array($res);
			}
		}
		$this->is_super_user=false;
		$this->is_logged_in=false;
		return true;
	}
	
	/**
	 *	fetchData - May need to refresh database fields.
	 *
	 *	If an update occurred and you need to access the updated info.
	 *
	 *	@return boolean success;
	 */
	function fetchData($user_id) {
		$res=db_query("SELECT * FROM users WHERE user_id='$user_id'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError('User::fetchData()::'.db_error());
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		return true;
	}
	
	/**
	 *	getID - Simply return the user_id for this object.
	 *
	 *	@return	int	This user's user_id number.
	 */
	function getID() {
		return $this->data_array['user_id'];
	}

	/**
	 *	getUnixName - the user's unix_name.
	 *
	 *	@return	string	This user's unix/login name.
	 */
	function getUnixName() {
		return strtolower($this->data_array['user_name']);
	}

	/**
	 *	getEmail - the user's email address.
	 *
	 *	@return	string	This user's email address.
	 */
	function getEmail() {
		return $this->data_array['email'];
	}

	/**
	 *	getRealName - get the user's real name.
	 *
	 *	@return	string	This user's real name.
	 */
	function getRealName() {
		return $this->getFirstName(). ' ' .$this->getLastName();
	}

	/**
	 *	getFirstName - get the user's first name.
	 *
	 *	@return	string	This user's first name.
	 */
	function getFirstName() {
		return $this->data_array['firstname'];
	}

	/**
	 *	getLastName - get the user's last name.
	 *
	 *	@return	string	This user's last name.
	 */
	function getLastName() {
		return $this->data_array['lastname'];
	}

	/**
	 *	getAddDate - this user's unix time when account was opened.
	 *
	 *	@return	int	This user's unix time when account was opened.
	 */
	function getAddDate() {
		return $this->data_array['add_date'];
	}

	/**
	 *	getTimeZone - this user's timezone setting.
	 *
	 *	@return	string	This user's timezone setting.
	 */
	function getTimeZone() {
		return $this->data_array['timezone'];
	}

	/**
	 *	getCountryCode - this user's ccode setting.
	 *
	 *	@return	string	This user's ccode setting.
	 */
	function getCountryCode() {
		return $this->data_array['ccode'];
	}

	/**
	 *	getLanguage - this user's language_id from supported_languages table.
	 *
	 *	@return	int	This user's language_id.
	 */
	function getLanguage() {
		return $this->data_array['language'];
	}

	/**
	 *	getAddress - get this user's address.
	 *
	 *	@return text	This user's address.
	 */
	function getAddress() {
		return $this->data_array['address'];
	}

	/**
	 *	getAddress2 - get this user's address2.
	 *
	 *	@return text	This user's address2.
	 */
	function getAddress2() {
		return $this->data_array['address2'];
	}

	/**
	 *	getPhone - get this person's phone number.
	 *
	 *	@return text	This user's phone number.
	 */
	function getPhone() {
		return $this->data_array['phone'];
	}

	/**
	 *	getFax - get this person's fax number.
	 *
	 *	@return text	This user's fax.
	 */
	function getFax() {
		return $this->data_array['fax'];
	}

	/**
	 *	getTitle - get this person's title.
	 *
	 *	@return text	This user's title.
	 */
	function getTitle() {
		return $this->data_array['title'];
	}

	/**
	 *	setLoggedIn($val) - Really only used by session code.
	 *
	 * 	@param	boolean	The session value.
	 */
	function setLoggedIn($val=true) {
		$this->is_logged_in=$val;
		if ($val) {
			//if this is the logged in user, see if they are a super user
			$sql="SELECT count(*) FROM user_group WHERE user_id='". $this->getID() ."' AND group_id='1' AND admin_flags='A'";
			$result=db_query($sql);
			if (!$result) {
				$this->is_super_user=false;
				return;
			}
			$row_count = db_fetch_array($result);
			$this->is_super_user = ($row_count['count'] > 0);
		}
	}

	/**
	 *	isLoggedIn - only used by session code.
	 *
	 *	@return	boolean	is_logged_in.
	 */
	function isLoggedIn() {
		return $this->is_logged_in;
	}

	function setUpTheme() {
//
//	An optimization in session_getdata lets us pre-fetch this in most cases.....
//
		if (!$this->data_array['dirname']) {
			$res=db_query("SELECT dirname FROM themes WHERE theme_id='".$this->getThemeID()."'");
			$this->theme=db_result($res,0,'dirname');
		} else {
			$this->theme=$this->data_array['dirname'];
		}
		if (is_file($GLOBALS['sys_themeroot'].$this->theme.'/Theme.class')) {
			$GLOBALS['sys_theme']=$this->theme;
		} else {
			$this->theme=$GLOBALS['sys_theme'];
		}
		return $this->theme;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

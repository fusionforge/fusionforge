<?php
/**
 * FusionForge user management
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
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

require_once $gfwww.'include/vote_function.php';
$USER_OBJ=array();

/**
 * user_get_object_by_name() - Get User object by username.
 *  user_get_object is useful so you can pool user objects/save database queries
 *  You should always use this instead of instantiating the object directly 
 *
 *  @param		string	The unix username - required
 *  @param		int		The result set handle ("SELECT * FROM USERS WHERE user_id=xx")
 *  @return a user object or false on failure
 *
 */
function &user_get_object_by_name($user_name,$res=false) {
	$user_name = strtolower($user_name);
	if (!$res) {
		$res=db_query("SELECT * FROM users WHERE user_name='$user_name'");
	}
	return user_get_object(db_result($res,0,'user_id'),$res);
}

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
			$USER_OBJ["_".$arr['user_id']."_"] = new GFUser($arr['user_id'],$arr);
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
	 *	GFUser($id,$res) - CONSTRUCTOR - GENERALLY DON'T USE THIS
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
				$this->data_array = db_fetch_array($res);
			}
		}
		$this->is_super_user=false;
		$this->is_logged_in=false;
		return true;
	}
	
	/**
	 * create() - Create a new user.
	 *
	 * @param	string	The unix username.
	 * @param	string	The real firstname.
	 * @param	string	The real lastname.
	 * @param	string	The first password.
	 * @param	string	The confirmation password.
	 * @param	string	The users email address.
	 * @param	string	The users preferred default language.
	 * @param	string	The users preferred default timezone.
	 * @param	string	The users preference for receiving site updates by email.
	 * @param	string	The users preference for receiving community updates by email.
	 * @param	int		The ID of the language preference.
	 * @param	string	The users preferred timezone.
	 * @param	string	The users Jabber address.
	 * @param	int		The users Jabber preference.
	 * @param	int		The users theme_id.
	 * @param	string	The users unix_box.
	 * @param	string	The users address.
	 * @param	string	The users address part 2.
	 * @param	string	The users phone.
	 * @param	string	The users fax.
	 * @param	string	The users title.
	 * @param	char(2)	The users ISO country_code.
	 * @param	bool	Whether to send an email or not
	 * @returns The newly created user ID
	 *
	 */
	function create($unix_name,$firstname,$lastname,$password1,$password2,$email,
		$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,
		$unix_box='shell',$address='',$address2='',$phone='',$fax='',$title='',$ccode='US',$send_mail=true) {
		if (!$theme_id) {
			$this->setError(_('You must supply a theme'));
			return false;
		}
		if (!$unix_name) {
			$this->setError(_('You must supply a username'));
			return false;
		}
		if (!$firstname) {
			$this->setError(_('You must supply a first name'));
			return false;
		}
		if (!$lastname) {
			$this->setError(_('You must supply a last name'));
			return false;
		}
		if (!$password1) {
			$this->setError(_('You must supply a password'));
			return false;
		}
		if ($password1 != $password2) {
			$this->setError(_('Passwords do not match'));
			return false;
		}
		if (!account_pwvalid($password1)) {
			$this->setError(_('Invalid Password:'));
			return false;
		}
		$unix_name=strtolower($unix_name);
		if (!account_namevalid($unix_name)) {
			$this->setError(_('Invalid Unix Name.'));
			return false;
		}
		if (!validate_email($email)) {
			$this->setError(_('Invalid Email Address'));
			return false;
		}
		if ($jabber_address && !validate_email($jabber_address)) {
			$this->setError(_('Invalid Jabber Address'));
			return false;
		}
		if (!$jabber_only) {
			$jabber_only=0;
		} else {
			$jabber_only=1;
		}
		if (db_numrows(db_query("SELECT user_id FROM users WHERE user_name LIKE '$unix_name'")) > 0) {
			$this->setError(_('That username already exists.'));
			return false;
		}
		if ($GLOBALS['sys_require_unique_email']) {
			if (db_numrows(db_query("SELECT user_id FROM users WHERE email='$email'")) > 0) {
				$this->setError(_('User with this email already exists - use people search to recover your login.'));
				return false;
			}
		}
		// if we got this far, it must be good
		$confirm_hash = substr(md5($password1 . rand() . microtime()),0,16);
		db_begin();
		$sql="INSERT INTO users (user_name,user_pw,unix_pw,realname,firstname,lastname,email,add_date,
			status,confirm_hash,mail_siteupdates,mail_va,language,timezone,jabber_address,jabber_only,
			unix_box,address,address2,phone,fax,title,ccode,theme_id) 
			VALUES ('$unix_name',
			'". md5($password1) . "',
			'". account_genunixpw($password1) . "',
			'". htmlspecialchars($firstname.' '.$lastname). "',
			'". htmlspecialchars($firstname). "',
			'". htmlspecialchars($lastname). "',
			'$email',
			'" . time() . "',
			'P',
			'$confirm_hash',
			'". (($mail_site)?"1":"0") . "',
			'". (($mail_va)?"1":"0") . "',
			'$language_id',
			'$timezone',
			'$jabber_address',
			'$jabber_only',
			'$unix_box',
			'". htmlspecialchars($address) ."',
			'". htmlspecialchars($address2) ."',
			'". htmlspecialchars($phone) ."',
			'". htmlspecialchars($fax) ."',
			'". htmlspecialchars($title) ."',
			'$ccode',
			'$theme_id')";


		$result=db_query($sql);
	
		if (!$result) {
			$this->setError(_('Insert Failed') .db_error().$sql);
			db_rollback();
			return false;
		} else {

			$id = db_insertid($result,'users','user_id');
			if (!$id) {
				$this->setError('Could Not Get USERID: ' .db_error());
				db_rollback();
				return false;
			}
			// send mail
			if (!$this->fetchData($id)) {
				db_rollback();
				return false;
			}

			$hook_params = array ();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			$hook_params['user_name'] = $unix_name;
			$hook_params['user_password'] = $password1;
			plugin_hook ("user_create", $hook_params);
			
			if ($send_mail) {
				setup_gettext_from_lang_id($language_id);
				$this->sendRegistrationEmail();
				setup_gettext_from_browser() ;
			}

			db_commit();
			return $id;
		}
	}

	/**
	 *	sendRegistrationEmail() - Send email for registration verification
	 *
	 *	@return true or false
	 */
	function sendRegistrationEmail() {
		$message=stripcslashes(sprintf(_('Thank you for registering on the %3$s web site. You have
account with username %1$s created for you. In order
to complete your registration, visit the following url: 

<%2$s>

(If you don\'t see any URL above, it is likely due to a bug in your mail client.
Use one below, but make sure it is entered as the single line.)

%2$s

Enjoy the site.

-- the %3$s staff
'),
					       $this->getUnixName(),
					       util_make_url ('/account/verify.php?confirm_hash=_'.$this->getConfirmHash()),
					       $GLOBALS['sys_name']));
		util_send_message(
			$this->getEmail(),
			sprintf(_('%1$s Account Registration'), $GLOBALS['sys_name']),
			$message
		);
	}

	/**
	 *	delete() - remove the User from all his groups.
	 *
	 *	Remove the User from all his groups and set his status to D.
	 *
	 *  @param	boolean	Confirmation of deletion.
	 *	@return true or false
	 */
	function delete($sure) {
		if (!$sure) {
			return false;
		} else {
			$groups = &$this->getGroups();
			if (is_array($groups)) {
				foreach ($groups as $group) {
					$group->removeUser($this->getID());
				}
			}

			db_begin();
			$res = db_query("DELETE FROM artifact_monitor WHERE user_id='".$this->getID()."' ");
			if (!$res) {
				$this->setError('ERROR - Could Not Delete From artifact_monitor: '.db_error());
				db_rollback();
				return false;
			}
			$res = db_query("DELETE FROM artifact_type_monitor WHERE user_id='".$this->getID()."' ");
			if (!$res) {
				$this->setError('ERROR - Could Not Delete From artifact_type_monitor: '.db_error());
				db_rollback();
				return false;
			}
			$res = db_query("DELETE FROM forum_monitored_forums WHERE user_id='".$this->getID()."' ");
			if (!$res) {
				$this->setError('ERROR - Could Not Delete From forum_monitored_forums: '.db_error());
				db_rollback();
				return false;
			}				
			$res = db_query("DELETE FROM filemodule_monitor WHERE user_id='".$this->getID()."' ");
			if (!$res) {
				$this->setError('ERROR - Could Not Delete From filemodule_monitor: '.db_error());
				db_rollback();
				return false;
			}

			$hook_params = array ();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			plugin_hook ("user_delete", $hook_params);
			
			$this->setStatus('D');
			db_commit();
		}
		return true;
	}

	/**
	 *	update() - update *common* properties of GFUser object.
	 *
	 *	Use specific setter to change other properties.
	 *
	 *  @param	string	The users first name.
	 *  @param	string	The users last name.
	 *  @param	int		The ID of the users language preference.
	 *  @param	string	The useres timezone preference.
	 *  @param	string	The users preference for receiving site updates by email.
	 *  @param	string	The users preference for receiving community updates by email.
	 *	@param	string	The users preference for being participating in "peer ratings".
	 *	@param	string	The users Jabber account address.
	 *	@param	int	The users Jabber preference.
	 *	@param	int	The users theme_id preference.
	 *	@param	string	The users address.
	 *	@param	string	The users address2.
	 *	@param	string	The users phone.
	 *	@param	string	The users fax.
	 *	@param	string	The users title.
	 *	@param	string	The users ccode.
	 */
	function update($firstname,$lastname,$language_id,$timezone,$mail_site,$mail_va,$use_ratings,
		$jabber_address,$jabber_only,$theme_id,$address,$address2,$phone,$fax,$title,$ccode) {
		$mail_site = $mail_site ? 1 : 0;
		$mail_va   = $mail_va   ? 1 : 0;
		$block_ratings = $use_ratings ? 0 : 1;

		if ($jabber_address && !validate_email($jabber_address)) {
			$this->setError(_('Invalid Jabber Address'));
			return false;
		}
		if (!$jabber_only) {
			$jabber_only=0;
		} else {
			$jabber_only=1;
		}

		db_begin();

		$res = db_query("
			UPDATE users
			SET
			realname='".htmlspecialchars($firstname . ' ' .$lastname)."',
			firstname='".htmlspecialchars($firstname)."',
			lastname='".htmlspecialchars($lastname)."',
			language='$language_id',
			timezone='$timezone',
			mail_siteupdates=$mail_site,
			mail_va=$mail_va,
			block_ratings='$block_ratings',
			jabber_address='$jabber_address',
			jabber_only='$jabber_only',
			address='". htmlspecialchars($address) ."',
			address2='". htmlspecialchars($address2) ."',
			phone='". htmlspecialchars($phone) ."',
			fax='". htmlspecialchars($fax) ."',
			title='". htmlspecialchars($title) ."',
			ccode='$ccode',
			theme_id='$theme_id'
			WHERE user_id='".$this->getID()."'
		");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User Object: '.db_error());
			db_rollback();
			return false;
		} else {
			// If there's a transaction from using to not
			// using ratings, remove all rating made by the
			// user (ratings by others should not be removed,
			// as it opens possibility to abuse rate system)
			if (!$use_ratings && $this->usesRatings()) {
				vote_remove_all_ratings_by($this->getID());
			}
			if (!$this->fetchData($this->getID())) {
				db_rollback();
				return false;
			}
			
			$hook_params = array ();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			plugin_hook ("user_update", $hook_params);
			
			db_commit();
			return true;
		}
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
			$this->setError('GFUser::fetchData()::'.db_error());
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
	 *	getStatus - get the status of this user.
	 *
	 *	Statuses include (A)ctive, (P)ending, (S)uspended ,(D)eleted.
	 *
	 *	@return	char	This user's status flag.
	 */
	function getStatus() {
		return $this->data_array['status'];
	}

	/**
	 *	setStatus - set this user's status.
	 *
	 *	@param	string	Status - P, A, S, or D.
	 *	@return	boolean	success.
	 */
	function setStatus($status) {

		if ($status != 'P' && $status != 'A'
			&& $status != 'S' && $status != 'D') {
			$this->setError('ERROR: Invalid status value');
			return false;
		}

		db_begin();
		$res=db_query("UPDATE users 
			SET status='$status' 
			WHERE user_id='". $this->getID()."'");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User Status: '.db_error());
			db_rollback();
			return false;
		} else {
			$this->data_array['status']=$status;
			if ($status == 'D') {
				// Remove this user from all groups
				$res = db_query(" DELETE FROM user_group WHERE user_id='".$this->getID()."' ");
				if (!$res) {
					$this->setError('ERROR - Could Not Propogate Deleted Status: '.db_error());
					db_rollback();
					return false;
				}
			}
			$hook_params = array ();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			$hook_params['status'] = $status;
			plugin_hook ("user_setstatus", $hook_params);
			
			db_commit();
			
			//plugin webcalendar, create cal_user
			plugin_hook('add_cal_user',$this->getID());
			
			return true;
		}
	}

	/**
	 *	isActive - whether this user is confirmed and active.
	 *
	 *	Database field status of 'A' returns true.
	 *	@return	boolean is_active.
	 */
	function isActive() {
		if ($this->getStatus()=='A') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	getUnixStatus - Status of activation of unix account.
	 *
	 *	@return	char	(N)one, (A)ctive, (S)uspended or (D)eleted
	 */
	function getUnixStatus() {
		return $this->data_array['unix_status'];
	}

	/**
	 *	setUnixStatus - Sets status of activation of unix account.
	 *
	 *	@param	string	The unix status.
	 *	N	no_unix_account
	 *	A	active
	 *	S	suspended
	 *	D	deleted
	 *
	 *	@return	boolean success.
	 */
	function setUnixStatus($status) {
		global $SYS;
		db_begin();
		$res=db_query("
			UPDATE users 
			SET unix_status='$status' 
			WHERE user_id='". $this->getID()."'
		");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User Unix Status: '.db_error());
			db_rollback();
			return false;
		} else {
			if ($status == 'A') {
				if (!$SYS->sysCheckCreateUser($this->getID())) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			} else {
				if ($SYS->sysCheckUser($this->getID())) {
					if (!$SYS->sysRemoveUser($this->getID())) {
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
	 *	getUnixName - the user's unix_name.
	 *
	 *	@return	string	This user's unix/login name.
	 */
	function getUnixName() {
		return strtolower($this->data_array['user_name']);
	}

	/**
	 *	getUnixPasswd - get the user's password.
	 *
	 * 	@return	string	This user's unix crypted passwd.
	 */
	function getUnixPasswd() {
		return $this->data_array['unix_pw'];
	}

	/**
	 *	getUnixBox - the hostname of the unix box this user has an account on.
	 *
	 * 	@return	string	This user's shell login machine.
	 */
	function getUnixBox() {
		return $this->data_array['unix_box'];
	}

	/**
	 *	getMD5Passwd - the password.
	 *
	 *	@return	string	This user's MD5-crypted passwd.
	 */
	function getMD5Passwd() {
		return $this->data_array['user_pw'];
	}

	/**
	 *	getConfirmHash - the confirm hash in the db.
	 *
	 *	@return	string	This user's confirmation hash.
	 */
	function getConfirmHash() {
		return $this->data_array['confirm_hash'];
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
	 *	getNewEmail - while changing an email address, it is stored here until confirmation.
	 *
	 *	getNewEmail is a private operation for email change.
	 *
	 *	@return	string	This user's new (not yet confirmed) email address.
	 *	@private
	 */
	function getNewEmail() {
		return $this->data_array['email_new'];
	}

	/**
	 *	setEmail - set a new email address, which must be confirmed.
	 *
	 *  @param	string	The email address.
	 *	@return boolean success.
	 */
	function setEmail($email) {
		if (!$email || !validate_email($email)) {
			$this->setError('ERROR: Invalid Email');
			return false;
		}
		db_begin();
		$res=db_query("
			UPDATE users 
			SET email='$email' 
			WHERE user_id='". $this->getID()."'
		");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User Email: '.db_error());
			db_rollback();
			return false;
		} else {
			$hook_params = array ();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			$hook_params['user_email'] = $email;
			plugin_hook ("user_setemail", $hook_params);
			
			$this->data_array['email'] = $email;
			db_commit();
			return true;
		}
	}

	/**
	 *	setNewEmailAndHash - setNewEmailAndHash is a private operation for email change.
	 *
	 *  @param	string	The email address.
	 *  @param	string	The email hash.
	 *	@return boolean success.
	 */
	function setNewEmailAndHash($email, $hash='') {

		if (!$hash) {
			$hash = substr(md5(strval(time()) . strval(mt_rand())), 0, 16);
		}

		if (!$email || !validate_email($email)) {
			$this->setError('ERROR - Invalid Email');
			return false;
		}

		$res=db_query("
			UPDATE users
			SET confirm_hash='$hash',
			email_new='$email'
			WHERE user_id='".$this->getID()."'
		");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User Email And Hash: '.db_error());
			return false;
		} else {
			$this->data_array['email_new']	= $email;
			$this->data_array['confirm_hash'] = $hash;
			return true;
		}
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
	 *	getShell - this user's preferred shell.
	 *
	 *	@return	string	This user's preferred shell.
	 */
	function getShell() {
		return $this->data_array['shell'];
	}

	/**
	 *	setShell - sets user's preferred shell.
	 *
	 *  @param	string	The users preferred shell.
	 *	@return boolean success.
	 */
	function setShell($shell) {
		global $SYS;
		$shells = file('/etc/shells');
		$shells[count($shells)] = "/bin/cvssh";
		$out_shells = array();
		foreach ($shells as $s) {
			if (substr($s, 0, 1) == '#') {
				continue;
			}
			$out_shells[] = chop($s);
		}
		if (!in_array($shell, $out_shells)) {
			$this->setError('ERROR: Invalid Shell');
			return false;
		}

		db_begin();
		$res=db_query("
			UPDATE users 
			SET shell='$shell' 
			WHERE user_id='". $this->getID()."'
		");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User Unix Shell: '.db_error());
			db_rollback();
			return false;
		} else {
			// Now change LDAP attribute, but only if corresponding
			// entry exists (i.e. if user have shell access)
			if ($SYS->sysCheckUser($this->getID()))
			{
				if (!$SYS->sysUserSetAttribute($this->getID(),"loginShell",$shell)) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			}
			$this->data_array['shell']=$shell;
		}
		db_commit();
		return true;
	}

	/**
	 *	getUnixUID() - Get the unix UID of the user
	 *
	 *	@return	int	This user's UID.
	 */
	function getUnixUID() {
		return $this->data_array['unix_uid'];
	}

	/**
	 *	getUnixGID() - Get the unix GID of the user
	 *
	 *	@return	int	This user's GID.
	 */
	function getUnixGID() {
		return $this->data_array['unix_gid'];
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
	 *	getJabberAddress - this user's optional jabber address.
	 *
	 *	@return	string	This user's jabber address.
	 */
	function getJabberAddress() {
		return $this->data_array['jabber_address'];
	}

	/**
	 *	getJabberOnly - whether this person wants updates sent ONLY to jabber.
	 *
	 *	@return boolean	This user's jabber preference.
	 */
	function getJabberOnly() {
		return $this->data_array['jabber_only'];
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
	 *	getGroups - get an array of groups this user is a member of.
	 *
	 *	@return array	Array of groups.
	 */
	function &getGroups() {
		$sql="SELECT group_id
			FROM user_group
			WHERE user_id='". $this->getID() ."'";
		$res=db_query($sql);
		$arr =& util_result_column_to_array($res,0);	
		return group_get_objects($arr);
	}

	/**
	 *	getAuthorizedKeys - the SSH authorized keys set by the user.
	 *
	 *	@return	string	This user's SSH authorized (public) keys.
	 */
	function getAuthorizedKeys() {
		return ereg_replace("###", "\n", $this->data_array['authorized_keys']);
	}

	/**
	 *	setAuthorizedKeys - set the SSH authorized keys for the user.
	 *
	 *  @param	string	The users public keys.
	 *	@return boolean success.
	 */
	function setAuthorizedKeys($keys) {
		$keys = trim($keys);
		$keys = ereg_replace("\r\n", "\n", $keys); // Convert to Unix EOL
		$keys = ereg_replace("\n+", "\n", $keys); // Remove empty lines
		$keys = ereg_replace("\n", "###", $keys); // Convert EOL to marker

		$res=db_query("
			UPDATE users 
			SET authorized_keys='$keys'
			WHERE user_id='".$this->getID()."'
		");

		if (!$res) {
			$this->setError('ERROR - Could Not Update User SSH Keys');
			return false;
		} else {
			$this->data_array['authorized_keys'] = $keys;
			return true;
		}
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
			$sql="SELECT count(*) AS count FROM user_group WHERE user_id='". $this->getID() ."' AND group_id='1' AND admin_flags='A'";
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

	/**
	 *	deletePreference - delete a preference for this user.
	 *
	 *	@param	string	The unique field name for this preference.
	 *	@return	boolean	success.
	 */
	function deletePreference($preference_name) {
		$preference_name=strtolower(trim($preference_name));
		unset($this->user_pref["$preference_name"]);
		$res= db_query("DELETE FROM user_preferences 
			WHERE user_id='". $this->getID() ."'
			AND preference_name='$preference_name'");
		return $res;
	}

	/**
	 *	setPreference - set a new preference for this user.
	 *
	 *	@param	string	The unique field name for this preference.
	 *	@param	string	The value you are setting this preference to.
	 *	@return	boolean	success.
	 */
	function setPreference($preference_name,$value) {
		$preference_name=strtolower(trim($preference_name));
		//delete pref if not value passed in
		unset($this->user_pref);
		if (!isset($value)) {
			$result=db_query("DELETE FROM user_preferences WHERE 
				user_id='". $this->getID() ."' AND preference_name='$preference_name'");
		} else {
			$result=db_query("UPDATE user_preferences SET preference_value='$value',set_date='". time() ."' ".
				"WHERE user_id='". $this->getID() ."' ".
				"AND preference_name='$preference_name'");
			if (db_affected_rows($result) < 1) {
				//echo db_error();
				$result=db_query("INSERT INTO user_preferences (user_id,preference_name,preference_value,set_date) ".
					"VALUES ('". $this->getID() ."','$preference_name','$value','". time() ."')");
				return $result;
			}
		}
	}

	/**
	 *	getPreference - get a specific preference.
	 *
	 *	@param	string	The unique field name for this preference.
	 *	@return the preference string or false on failure.
	 */
	function getPreference($preference_name) {
		$preference_name=strtolower(trim($preference_name));
		/*
			First check to see if we have already fetched the preferences
		*/
		if (isset($this->user_pref)) {
			//echo "\n\nPrefs were fetched already";
			if (isset($this->user_pref["$preference_name"])) {
				//we have fetched prefs - return part of array
				return $this->user_pref["$preference_name"];
			} else {
				//we have fetched prefs, but this pref hasn't been set
				return false;
			}
		} else {
			//we haven't returned prefs - go to the db
			$result=db_query("SELECT preference_name,preference_value FROM user_preferences ".
				"WHERE user_id='". $this->getID() ."'");
			if (db_numrows($result) < 1) {
				//echo "\n\nNo Prefs Found";
				return false;
			} else {
				$pref=array();
				//iterate and put the results into an array
				for ($i=0; $i<db_numrows($result); $i++) {
					$pref["".db_result($result,$i,'preference_name').""]=db_result($result,$i,'preference_value');
				}
				$this->user_pref = $pref;

				if (array_key_exists($preference_name,$this->user_pref)) {
					//we have fetched prefs - return part of array
					return $this->user_pref["$preference_name"];
				} else {
					//we have fetched prefs, but this pref hasn't been set
					return false;
				}
			}
		}
	}

	/**
	 *	setPasswd - Changes user's password.
	 *
	 *	@param	string	The plaintext password.
	 *	@return boolean success.
	 */
	function setPasswd($passwd) {
		global $SYS;
		if (!account_pwvalid($passwd)) {
			$this->setError('Error: '.$GLOBALS['register_error']);
			return false;
		}

		db_begin();
		$unix_pw = account_genunixpw($passwd);

		$res=db_query("
			UPDATE users
			SET user_pw='" . md5($passwd) . "',
			unix_pw='$unix_pw'
			WHERE user_id='".$this->getID()."'
		");

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('ERROR - Could Not Change User Password: '.db_error());
			db_rollback();
			return false;
		} else {
			// Now change LDAP password, but only if corresponding
			// entry exists (i.e. if user have shell access)
			if ($SYS->sysCheckUser($this->getID())) {
				if (!$SYS->sysUserSetAttribute($this->getID(),"userPassword",'{crypt}'.$unix_pw)) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			}
		}
		$hook_params = array ();
		$hook_params['user'] = $this;
		$hook_params['user_id'] = $this->getID();
		$hook_params['user_password'] = $passwd;
		plugin_hook ("user_setpasswd", $hook_params);
		db_commit();
		return true;
	}

	/**
	 *	usesRatings - whether user participates in rating system.
	 *
	 *	@return boolean success.
	 */
	function usesRatings() {
		return !$this->data_array['block_ratings'];
	}

	/**
	 *  getPlugins -  get a list of all available user plugins
	 *
	 *  @return array array containing plugin_id => plugin_name
	 */
	function getPlugins() {
		if (!isset($this->plugins_data)) {
			$this->plugins_data = array () ;
			$sql="SELECT user_plugin.plugin_id, plugins.plugin_name
				FROM user_plugin, plugins
				WHERE user_plugin.user_id=".$this->getID()."
					AND user_plugin.plugin_id = plugins.plugin_id" ;
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
	 *  usesPlugin - returns true if the user uses a particular plugin 
	 *
	 *  @param	string	name of the plugin
	 *  @return	boolean	whether plugin is being used or not
	 */
	function usesPlugin($pluginname) {
		$plugins_data = $this->getPlugins() ;
		foreach ($plugins_data as $p_name) {
			if ($p_name == $pluginname) {
				return true ;
			}
		}
		return false ;
	}

	/**
	 *  setPluginUse - enables/disables plugins for the user
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
			$sql="INSERT INTO user_plugin (user_id, plugin_id)
				VALUES (". $this->getID() . ", ". $plugin_id .")" ;
			$res=db_query($sql);
			return $res ;
		} else {
			$sql="DELETE FROM user_plugin
				WHERE user_id = ". $this->getID() . "
				AND plugin_id = ". $plugin_id ;
			$res=db_query($sql);
			return $res ;
		}
	}

	/**
	 *	getMailingsPrefs - Get activity status for one of the site mailings.
	 *
	 *	@param	string	The id of mailing ('mail_va' for community mailings, 'mail_siteupdates' for site mailings)
	 *	@return	boolean success.
	 */
	function getMailingsPrefs($mailing_id) {
		if ($mailing_id=='va') {
			return $this->data_array['mail_va'];
		} else if ($mailing_id=='site') {
			return $this->data_array['mail_siteupdates'];
		} else {
			return 0;
		}
	}

	/**
	 *	unsubscribeFromMailings - Disable email notifications for user.
	 *
	 *	@param	boolean	If false, disable general site mailings, else - all.
	 *	@return	boolean	success.
	 */
	function unsubscribeFromMailings($all=false) {
		$res1 = $res2 = $res3 = true;
		$res1 = db_query("
			UPDATE users
			SET mail_siteupdates=0,
				mail_va=0
			WHERE user_id='".$this->getID()."'
		");
		if ($all) {
			$res2 = db_query("
				DELETE FROM forum_monitored_forums
				WHERE user_id='".$this->getID()."'
			");
			$res3 = db_query("
				DELETE FROM filemodule_monitor
				WHERE user_id='".$this->getID()."'
			");
		}

		return $res1 && $res2 && $res3;
	}

	/**
	 *	getThemeID - get the theme_id for this user.
	 *
	 *	@return	int	The theme_id.
	 */
	function getThemeID() {
		return $this->data_array['theme_id'];
	}

	/**
	 *	getThemeID - get the theme_id for this user from the theme_prefs table.
	 *
	 *	@return	int	The theme_id.
	 */
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
		if (is_file($GLOBALS['sys_themeroot'].$this->theme.'/Theme.class.php')) {
			$GLOBALS['sys_theme']=$this->theme;
		} else {
			$this->theme=$GLOBALS['sys_theme'];
		}
		return $this->theme;
	}
}

/*




		EVERYTHING BELOW HERE IS DEPRECATED


		DO NOT USE FOR ANY NEW CODE



*/



/**
 * user_ismember() - DEPRECATED; DO NOT USE!
 *
 * @param		int		The Group ID
 * @param		int		The Type
 * @deprecated
 *
 */
function user_ismember($group_id,$type=0) {
	if (!session_loggedin()) {
		return false;
	}

	$project =& group_get_object($group_id);

	if (!$project || !is_object($project)) {
			return false;
	}

	$perm =& $project->getPermission( session_get_user() );
	if (!$perm || !is_object($perm) || !$perm->isMember()) {
		return false;
	}

	$type=strtoupper($type);
	
	switch ($type) {
		case 'P2' : {
			//pm admin
			return $perm->isPMAdmin();
			break; 
		}
		case 'F2' : {
			//forum admin
			return $perm->isForumAdmin();
			break; 
		}
		case '0' : {
			//just in this group
			return $perm->isMember();
			break;
		}
		case 'A' : {
			//admin for this group
			return $perm->isAdmin();
			break;
		}
		case 'D1' : {
			//document editor
			return $perm->isDocEditor();
			break;
		}
		default : {
			//fubar request
			return false;
		}
	}
	return false;
}

/**
 * user_getname() - DEPRECATED; DO NOT USE!
 *
 * @param		int		The User ID
 * @deprecated
 *
 */
function user_getname($user_id = false) {
	// use current user if one is not passed in
	if (!$user_id) {
		if (session_loggedin()) {
			$user=&user_get_object(user_getid());
			if ($user) {
				return $user->getUnixName();
			} else {
				return 'Error getting user';
			}
		} else {
			return 'No User Id';
		}
	} else {
		$user=&user_get_object($user_id);
		if ($user) {
			return $user->getUnixName();
		} else {
			return 'Invalid User';
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

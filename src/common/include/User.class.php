<?php
/**
 * FusionForge user management
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2009-2010, Roland Mas
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

$USER_OBJ = array();

/**
 * user_get_object_by_name() - Get User object by username.
 * user_get_object is useful so you can pool user objects/save database queries
 * You should always use this instead of instantiating the object directly
 *
 * @param string       $user_name The unix username - required
 * @param bool|int     $res       The result set handle ("SELECT * FROM USERS WHERE user_id=xx")
 * @return GFUser User object or false on failure
 */
function &user_get_object_by_name($user_name, $res = false) {
	$user_name = strtolower($user_name);
	if (!$res) {
		$res = db_query_params('SELECT * FROM users WHERE user_name=$1',
					array($user_name));
	}
	return user_get_object(db_result($res, 0, 'user_id'), $res);
}

/**
 * user_get_object_by_email() - Get User object by email address
 * Only works if sys_require_unique_email is true
 *
 * @param string    $email The unix username - required
 * @param bool|int  $res   The result set handle ("SELECT * FROM USERS WHERE user_id=xx")
 * @return GFUser User object or false on failure
 */
function user_get_object_by_email($email, $res = false) {
	if (!validate_email($email)
	    || !forge_get_config('require_unique_email')) {
		return false;
	}
	if (!$res) {
		$res = db_query_params('SELECT * FROM users WHERE email=$1',
			array($email));
	}
	return user_get_object(db_result($res, 0, 'user_id'), $res);
}

/**
 * user_get_object_by_name_or_email() - Get User object by username or email.
 * user_get_object is useful so you can pool user objects/save database queries
 * You should always use this instead of instantiating the object directly
 *
 * @param	string	The unix username - required
 * @param	int	The result set handle ("SELECT * FROM USERS WHERE user_id=xx")
 * @return	a user object or false on failure
 */
function &user_get_object_by_name_or_email($user_name, $res = false) {
	$user = user_get_object_by_name($user_name, $res);
	if (is_object($user)) {
		return $user ;
	}

	$user = user_get_object_by_email($user_name, $res) ;
	if (is_object($user)) {
		return $user ;
	}

	return false;
}

/**
 * user_get_object() - Get User object by user ID.
 * user_get_object is useful so you can pool user objects/save database queries
 * You should always use this instead of instantiating the object directly
 *
 * @param int      $user_id The ID of the user - required
 * @param int|bool $res     The result set handle ("SELECT * FROM USERS WHERE user_id=xx")
 * @return GFUser a user object or false on failure
 */
function &user_get_object($user_id, $res = false) {
	//create a common set of group objects
	//saves a little wear on the database

	//automatically checks group_type and
	//returns appropriate object

	global $USER_OBJ;
	if (!isset($USER_OBJ["_".$user_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM users WHERE user_id=$1',
						array($user_id));
		}
		if (!$res || db_numrows($res) < 1) {
			$USER_OBJ["_".$user_id."_"] = false;
		} else {
			$USER_OBJ["_".$user_id."_"] = new GFUser($user_id, $res);
		}
	}
	return $USER_OBJ["_".$user_id."_"];
}

/**
 * @param $id_arr
 * @return GFUser[]
 */
function &user_get_objects($id_arr) {
	global $USER_OBJ;
	$fetch = array();
	$return = array();

	foreach ($id_arr as $id) {
		//
		//  See if this ID already has been fetched in the cache
		//
		if (!isset($USER_OBJ["_".$id."_"])) {
			$fetch[] = $id;
		}
	}
	if (count($fetch) > 0) {
		$res = db_query_params('SELECT * FROM users WHERE user_id = ANY ($1)',
			array(db_int_array_to_any_clause($fetch)));
		while ($arr = db_fetch_array($res)) {
			$USER_OBJ["_".$arr['user_id']."_"] = new GFUser($arr['user_id'], $arr);
		}
	}
	foreach ($id_arr as $id) {
		$return[] =& $USER_OBJ["_".$id."_"];
	}
	return $return;
}

/**
 * @param string $username_arr
 * @return GFUser[]
 */
function &user_get_objects_by_name($username_arr) {
	$res = db_query_params('SELECT user_id FROM users WHERE lower(user_name) = ANY ($1)',
				array(db_string_array_to_any_clause ($username_arr)));
	$arr =& util_result_column_to_array($res, 0);
	return user_get_objects($arr);
}

/**
 * @param string $email_arr
 * @return GFUser[]
 */
function &user_get_objects_by_email($email_arr) {
	$res = db_query_params('SELECT user_id FROM users WHERE lower(email) = ANY ($1)',
		array(db_string_array_to_any_clause($email_arr)));
	$arr =& util_result_column_to_array($res, 0);
	return user_get_objects($arr);
}

/**
 * user_get_active_users - Return the list of active users.
 *
 * @return GFUser[]
 */
function &user_get_active_users() {
	$res = db_query_params('SELECT user_id FROM users WHERE status=$1',
		array('A'));
	return user_get_objects(util_result_column_to_array($res, 0));
}

function &user_get_all_users() {
	$res=db_query_params ('SELECT user_id FROM users',
			      array()) ;
	return user_get_objects (util_result_column_to_array($res,0)) ;
}

class GFUser extends Error {
	/**
	 * Associative array of data from db.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * Is this person a site super-admin?
	 *
	 * @var	bool	$is_super_user
	 */
	var $is_super_user;

	/**
	 * Is this person the logged in user?
	 *
	 * @var	bool	$is_logged_in
	 */
	var $is_logged_in;

	/**
	 * Array of preferences
	 *
	 * @var	array	$user_pref
	 */
	var $user_pref;

	var $theme;
	var $theme_id;

	/**
	 * CONSTRUCTOR - GENERALLY DON'T USE THIS
	 * instead use the user_get_object() function call
	 *
	 * @param bool|int $id  The user_id
	 * @param bool|int $res The database result set OR array of data
	 */
	function __construct($id = false, $res = false) {
		$this->Error();
		if (!$id) {
			//setting up an empty object
			//probably going to call create()
			return;
		}
		if (!$res) {
			$this->fetchData($id);
		} else {
			if (is_array($res)) {
				$this->data_array =& $res;
			} elseif (db_numrows($res) < 1) {
				//function in class we extended
				$this->setError(_('User Not Found'));
				$this->data_array = array();
				return;
			} else {
				//set up an associative array for use by other functions
				$this->data_array = db_fetch_array_by_row($res, 0);
				if (($this->getUnixStatus() == 'A') && (forge_get_config('use_shell'))) {
					$this->data_array['authorized_keys'] = array();
					$res = db_query_params('select * from sshkeys where userid = $1 and deleted = 0', array($this->getID()));
					while ($arr = db_fetch_array($res)) {
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['upload'] = $arr['upload'];
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['name'] = $arr['name'];
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['fingerprint'] = $arr['fingerprint'];
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['algorithm'] = $arr['algorithm'];
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['deploy'] = $arr['deploy'];
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['key'] = $arr['sshkey'];
						$this->data_array['authorized_keys'][$arr['id_sshkeys']]['keyid'] = $arr['id_sshkeys'];
					}
				}
			}
		}
		$this->is_super_user = false;
		$this->is_logged_in = false;
	}

	/**
	 * create() - Create a new user.
	 *
	 * @param	string		$unix_name		The unix username.
	 * @param	string		$firstname		The real firstname.
	 * @param	string		$lastname		The real lastname.
	 * @param	string		$password1		The first password.
	 * @param	string		$password2		The confirmation password.
	 * @param	string		$email			The users email address.
	 * @param	string		$mail_site		The users preferred default language.
	 * @param	string		$mail_va		The users preferred default timezone.
	 * @param	int			$language_id	The ID of the language preference.
	 * @param	string		$timezone		The users preferred default timezone.
	 * @param	string		$dummy1			ignored	(no longer used)
	 * @param	int			$dummy2			ignored	(no longer used)
	 * @param	int			$theme_id		The users theme_id.
	 * @param	string    	$unix_box		The users unix_box.
	 * @param	string    	$address		The users address.
	 * @param	string    	$address2		The users address part 2.
	 * @param	string    	$phone			The users phone.
	 * @param	string    	$fax			The users fax.
	 * @param	string		$title			The users title.
	 * @param	string		$ccode			The users ISO country_code.
	 * @param	bool		$send_mail		Whether to send an email or not
	 * @param	bool|int	$tooltips		The users preference for tooltips
     * @return	bool|int	The newly created user ID
	 *
	 */
	function create($unix_name, $firstname, $lastname, $password1, $password2, $email,
					$mail_site, $mail_va, $language_id, $timezone,
					$dummy1, $dummy2, $theme_id, $unix_box = 'shell',
					$address = '', $address2 = '', $phone = '', $fax = '', $title = '',
					$ccode = 'US', $send_mail = true, $tooltips = true) {
		global $SYS;
		if (!$theme_id) {
			$this->setError(_('You must supply a theme'));
			return false;
		}
		if (!forge_get_config('require_unique_email')) {
			if (!$unix_name) {
				$this->setError(_('You must supply a username'));
				return false;
			}
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
			$this->setError(_('Invalid Password'));
			return false;
		}
		//testing if there is at least one capital letter in the unix name
		if  (preg_match('/[A-Z]/', $unix_name)) {
			$this->setError(_('Invalid Unix Name (must not contain uppercase characters)'));
			return false;
		}
		$unix_name = strtolower($unix_name);
		if (!account_namevalid($unix_name)) {
			$this->setError(_('Invalid Unix Name.'));
			return false;
		}
		if (!$SYS->sysUseUnixName($unix_name)) {
			$this->setError(_('Unix name already taken.'));
			return false;
		}
		if (!validate_email($email)) {
			$this->setError(_('Invalid Email Address')._(': ').$email);
			return false;
		}
		if ($unix_name && db_numrows(db_query_params('SELECT user_id FROM users WHERE user_name LIKE $1',
							     array($unix_name))) > 0) {
			$this->setError(_('That username already exists.'));
			return false;
		}
		if (forge_get_config('require_unique_email')) {
			if (user_get_object_by_email($email)) {
				$this->setError(_('User with this email already exists - use people search to recover your login.'));
				return false;
			}
		}
		if (forge_get_config('require_unique_email') && !$unix_name) {
			// Let's generate a loginname for the user
			// ...based on the email address:
			$email_array = explode('@', $email, 2);
			$email_u = $email_array [0];
			$l = preg_replace('/[^a-z0-9]/', '', $email_u);
			$l = substr($l, 0, 15);
			// Is the user part of the email address okay?
			if (account_namevalid($l)
				&& db_numrows(db_query_params('SELECT user_id FROM users WHERE user_name = $1',
							  array($l))) == 0) {
				$unix_name = $l;
			} else {
				// No? What if we add a number at the end?
				$i = 0;
				while ($i < 1000) {
					$c = substr($l, 0, 15 - strlen("$i"))."$i";
					if (account_namevalid($c)
						&& db_numrows(db_query_params('SELECT user_id FROM users WHERE user_name = $1',
									  array($c))) == 0) {
						$unix_name = $c;
						break;
					}
					$i++;
				}
			}
			// If we're really unlucky, then let's go brute-force
			while (!$unix_name) {
				$c = substr(md5($email.util_randbytes()), 0, 15);
				if (account_namevalid($c)
					&& db_numrows(db_query_params('SELECT user_id FROM users WHERE user_name = $1',
								  array($c))) == 0) {
					$unix_name = $c;
				}
			}
		}
		$unix_name = strtolower($unix_name);
		if (!account_namevalid($unix_name)) {
			$this->setError(_('Invalid Unix Name.'));
			return false;
		}
		$shell = account_get_user_default_shell();
		// if we got this far, it must be good
		$confirm_hash = substr(md5($password1.util_randbytes().microtime()), 0, 16);
		db_begin();
		$result = db_query_params('INSERT INTO users (user_name,user_pw,unix_pw,realname,firstname,lastname,email,add_date,status,confirm_hash,mail_siteupdates,mail_va,language,timezone,unix_box,address,address2,phone,fax,title,ccode,theme_id,tooltips,shell)
							VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24)',
			array($unix_name,
				md5($password1),
				account_genunixpw($password1),
				htmlspecialchars($firstname.' '.$lastname),
				htmlspecialchars($firstname),
				htmlspecialchars($lastname),
				$email,
				time(),
				'P',
				$confirm_hash,
				(($mail_site)? "1" : "0"),
				(($mail_va)? "1" : "0"),
				$language_id,
				$timezone,
				$unix_box,
				htmlspecialchars($address),
				htmlspecialchars($address2),
				htmlspecialchars($phone),
				htmlspecialchars($fax),
				htmlspecialchars($title),
				$ccode,
				$theme_id,
				$tooltips,
				$shell));
		if (!$result) {
			$this->setError(_('Insert Error')._(': ').db_error());
			db_rollback();
			return false;
		} else {

			$id = db_insertid($result, 'users', 'user_id');
			if (!$id) {
				$this->setError(_('Could Not Get User Id: ') .db_error());
				db_rollback();
				return false;
			}
			// send mail
			if (!$this->fetchData($id)) {
				db_rollback();
				return false;
			}

			$hook_params = array();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			$hook_params['user_name'] = $unix_name;
			$hook_params['user_password'] = $password1;
			plugin_hook("user_create", $hook_params);

			if ($send_mail) {
				setup_gettext_from_lang_id($language_id);
				$this->sendRegistrationEmail();
				setup_gettext_from_context();
			}

			db_commit();
			return $id;
		}
	}

	/**
	 * sendRegistrationEmail() - Send email for registration verification
	 *
	 * @return bool    success or not
	 */
	function sendRegistrationEmail() {
		$message = stripcslashes(sprintf(_('Thank you for registering on the %3$s web site. You have
account with username %1$s created for you. In order
to complete your registration, visit the following url:

<%2$s>

You have 1 week to confirm your account. After this time, your account will be deleted.

(If you don\'t see any URL above, it is likely due to a bug in your mail client.
Use one below, but make sure it is entered as the single line.)

%2$s'),
			$this->getUnixName(),
			util_make_url('/account/verify.php?confirm_hash=_'.$this->getConfirmHash()),
			forge_get_config('forge_name')));
		$message .= "\n\n";
		$message .= _('Enjoy the site.');
		$message .= "\n\n";
		$message .= sprintf(_('-- the %s staff'), forge_get_config('forge_name'));
		$message .= "\n";
		util_send_message(
			$this->getEmail(),
			sprintf(_('%s Account Registration'), forge_get_config('forge_name')),
			$message
		);
	}

	/**
	 * delete() - remove the User from all his groups.
	 *
	 * Remove the User from all his groups and set his status to D.
	 *
	 * @param    boolean    $sure Confirmation of deletion.
	 * @return    boolean    success or not
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
			$res = db_query_params('DELETE FROM artifact_monitor WHERE user_id=$1',
						array($this->getID()));
			if (!$res) {
				$this->setError(_('Could Not Delete From artifact_monitor:') . ' '.db_error());
				db_rollback();
				return false;
			}
			$res = db_query_params('DELETE FROM artifact_type_monitor WHERE user_id=$1',
						array($this->getID()));
			if (!$res) {
				$this->setError(_('Could Not Delete From artifact_type_monitor:') . ' ' .db_error());
				db_rollback();
				return false;
			}
			$res = db_query_params('DELETE FROM forum_monitored_forums WHERE user_id=$1',
						array($this->getID()));
			if (!$res) {
				$this->setError(_('Could Not Delete From forum_monitored_forums:') . ' '.db_error());
				db_rollback();
				return false;
			}
			$res = db_query_params('DELETE FROM filemodule_monitor WHERE user_id=$1',
						array($this->getID()));
			if (!$res) {
				$this->setError(_('Could Not Delete From filemodule_monitor:') . ' '.db_error());
				db_rollback();
				return false;
			}

			$hook_params = array();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			plugin_hook("user_delete", $hook_params);

			$this->setStatus('D');
			$this->setUnixStatus('D');
			db_commit();
		}
		return true;
	}

	/**
	 * update() - update *common* properties of GFUser object.
	 *
	 * Use specific setter to change other properties.
	 *
	 * @param	string	$firstname		The users first name.
	 * @param	string	$lastname		The users last name.
	 * @param	int		$language_id	The ID of the users language preference.
	 * @param	string	$timezone		The users timezone preference.
	 * @param	string	$mail_site		The users preference for receiving site updates by email.
	 * @param	string	$mail_va		The users preference for receiving community updates by email.
	 * @param	string	$use_ratings	The users preference for being participating in "peer ratings".
	 * @param	string	$dummy1			ignored	(no longer used)
	 * @param	int		$dummy2			ignored	(no longer used)
	 * @param	int		$theme_id		The users theme_id preference.
	 * @param	string	$address		The users address.
	 * @param	string	$address2		The users address2.
	 * @param	string	$phone			The users phone.
	 * @param	string	$fax			The users fax.
	 * @param	string	$title			The users title.
	 * @param	string	$ccode			The users ccode.
	 * @param	int		$tooltips		The users preference for tooltips.
	 * @param	string	$email			The users email.
     * @return bool
	 */
	function update($firstname, $lastname, $language_id, $timezone, $mail_site, $mail_va, $use_ratings,
					$dummy1, $dummy2, $theme_id, $address, $address2, $phone, $fax, $title,
					$ccode, $tooltips, $email = '') {
		$mail_site = $mail_site ? 1 : 0;
		$mail_va = $mail_va ? 1 : 0;
		$block_ratings = $use_ratings ? 0 : 1;

		db_begin();

		$res = db_query_params('
				UPDATE users
				SET
				realname=$1,
				firstname=$2,
				lastname=$3,
				language=$4,
				timezone=$5,
				mail_siteupdates=$6,
				mail_va=$7,
				block_ratings=$8,
				address=$9,
				address2=$10,
				phone=$11,
				fax=$12,
				title=$13,
				ccode=$14,
				theme_id=$15,
				tooltips=$16
				WHERE user_id=$17',
			array(
				htmlspecialchars($firstname . ' ' .$lastname),
				htmlspecialchars($firstname),
				htmlspecialchars($lastname),
				$language_id,
				$timezone,
				$mail_site,
				$mail_va,
				$block_ratings,
				htmlspecialchars($address),
				htmlspecialchars($address2),
				htmlspecialchars($phone),
				htmlspecialchars($fax),
				htmlspecialchars($title),
				$ccode,
				$theme_id,
				$tooltips,
				$this->getID()));

		if (!$res) {
			$this->setError(_('Error: Cannot Update User Object:').' '.db_error());
			db_rollback();
			return false;
		}

		if ($email && $email != $this->getEmail()
			&& !$this->setEmail($email)) {
			return false;
		}

		// If there's a transaction from using to not
		// using ratings, remove all rating made by the
		// user (ratings by others should not be removed,
		// as it opens possibility to abuse rate system)
		if (!$use_ratings && $this->usesRatings()) {
			db_query_params('DELETE FROM user_ratings WHERE rated_by=$1',
					array($this->getID()));
		}
		if (!$this->fetchData($this->getID())) {
			db_rollback();
			return false;
		}

		$hook_params = array();
		$hook_params['user'] = $this;
		$hook_params['user_id'] = $this->getID();
		plugin_hook("user_update", $hook_params);

		db_commit();
		return true;
	}

	/**
	 * fetchData - May need to refresh database fields.
	 *
	 * If an update occurred and you need to access the updated info.
	 *
	 * @param    int    $user_id the User ID data to be fetched
	 * @return    boolean    success;
	 */
	function fetchData($user_id) {
		$res = db_query_params('SELECT * FROM users WHERE user_id=$1',
					array($user_id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('GFUser::fetchData():: '.db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		if (($this->getUnixStatus() == 'A') && (forge_get_config('use_shell'))) {
			$this->data_array['authorized_keys'] = array();
			$res = db_query_params('select * from sshkeys where userid = $1 and deleted = 0', array($this->getID()));
			while ($arr = db_fetch_array($res)) {
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['upload'] = $arr['upload'];
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['name'] = $arr['name'];
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['fingerprint'] = $arr['fingerprint'];
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['algorithm'] = $arr['algorithm'];
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['deploy'] = $arr['deploy'];
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['key'] = $arr['sshkey'];
				$this->data_array['authorized_keys'][$arr['id_sshkeys']]['keyid'] = $arr['id_sshkeys'];
			}
		}
		return true;
	}

	/**
	 * getID - Simply return the user_id for this object.
	 *
	 * @return    int    This user's user_id number.
	 */
	function getID() {
		return $this->data_array['user_id'];
	}

	/**
	 * getStatus - get the status of this user.
	 *
	 * Statuses include (A)ctive, (P)ending, (S)uspended ,(D)eleted.
	 *
	 * @return    string    This user's status flag.
	 */
	function getStatus() {
		return $this->data_array['status'];
	}

	/**
	 * setStatus - set this user's status.
	 *
	 * @param    string    $status Status - P, A, S, or D.
	 * @return    boolean    success.
	 */
	function setStatus($status) {

		if ($status != 'P' && $status != 'A'
			&& $status != 'S' && $status != 'D') {
			$this->setError(_('Error: Invalid status value'));
			return false;
		}
		if ($this->getStatus() != 'P' && $status == 'P') {
			$this->setError(_('Error: You cannot set pending status if user is suspend or active'));
			return false;
		}

		db_begin();
		$res = db_query_params('UPDATE users SET status=$1 WHERE user_id=$2',
					array($status, $this->getID()));

		if (!$res) {
			$this->setError(_('Error: Cannot Update User Status:').' '.db_error());
			db_rollback();
			return false;
		} else {
			$this->data_array['status'] = $status;
			if ($status == 'D') {
				$projects = $this->getGroups() ;
				foreach ($projects as $p) {
					$p->removeUser ($this->getID()) ;
				}
			}

			$hook_params = array();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			$hook_params['status'] = $status;
			plugin_hook("user_setstatus", $hook_params);

			db_commit();

			return true;
		}
	}

	/**
	 * isActive - whether this user is confirmed and active.
	 *
	 * Database field status of 'A' returns true.
	 *
	 * @return    boolean is_active.
	 */
	function isActive() {
		if ($this->getStatus() == 'A') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * getUnixStatus - Status of activation of unix account.
	 *
	 * @return string (N)one, (A)ctive, (S)uspended or (D)eleted
	 */
	function getUnixStatus() {
		return $this->data_array['unix_status'];
	}

	/**
	 * setUnixStatus - Sets status of activation of unix account.
	 *
	 * @param    string    $status The unix status.
	 *                             N    no_unix_account
	 *                             A    active
	 *                             S    suspended
	 *                             D    deleted
	 *
	 * @return	boolean success.
	 */
	function setUnixStatus($status) {
		global $SYS;
		db_begin();
		$res = db_query_params('UPDATE users SET unix_status=$1 WHERE user_id=$2',
					array($status, $this->getID()));

		if (!$res) {
			$this->setError('Error: Cannot Update User Unix Status: '.db_error());
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

			$this->data_array['unix_status'] = $status;
			db_commit();
			return true;
		}
	}

	/**
	 * getUnixName - the user's unix_name.
	 *
	 * @return    string    This user's unix/login name.
	 */
	function getUnixName() {
		return strtolower($this->data_array['user_name']);
	}

	/**
	 * getUnixPasswd - get the user's password.
	 *
	 * @return    string    This user's unix crypted passwd.
	 */
	function getUnixPasswd() {
		return $this->data_array['unix_pw'];
	}

	/**
	 * getUnixBox - the hostname of the unix box this user has an account on.
	 *
	 * @return    string    This user's shell login machine.
	 */
	function getUnixBox() {
		return $this->data_array['unix_box'];
	}

	/**
	 * getMD5Passwd - the password.
	 *
	 * @return    string    This user's MD5-crypted passwd.
	 */
	function getMD5Passwd() {
		return $this->data_array['user_pw'];
	}

	//Added to be compatible with codendi getUserPw function
	function getUserPw() {
		return $this->data_array['user_pw'];
	}

	/**
	 * getConfirmHash - the confirm hash in the db.
	 *
	 * @return    string    This user's confirmation hash.
	 */
	function getConfirmHash() {
		return $this->data_array['confirm_hash'];
	}

	/**
	 * getEmail - the user's email address.
	 *
	 * @return    string    This user's email address.
	 */
	function getEmail() {
		return str_replace("\n", "", $this->data_array['email']);
	}

	/**
	 * getSha1Email - a SHA1 encoded hash of the email URI (including mailto: prefix)
	 *
	 * @return string The SHA1 encoded value for the email
	 */
	function getSha1Email() {
		return sha1('mailto:'.$this->getEmail());
	}

	/**
	 * getNewEmail - while changing an email address, it is stored here until confirmation.
	 *
	 * getNewEmail is a private operation for email change.
	 *
	 * @return    string    This user's new (not yet confirmed) email address.
	 * @private
	 */
	function getNewEmail() {
		return $this->data_array['email_new'];
	}

	/**
	 * setEmail - set a new email address, which must be confirmed.
	 *
	 * @param    string    $email The email address.
	 * @return    boolean    success.
	 */
	function setEmail($email) {

		if (!strcasecmp($this->getEmail(), $email)) {
			return true;
		}

		if (!$email || !validate_email($email)) {
			$this->setError(_('Invalid Email Address'));
			return false;
		}

		if (forge_get_config('require_unique_email')) {
			if (db_numrows(db_query_params('SELECT user_id FROM users WHERE user_id!=$1 AND (lower(email) LIKE $2 OR lower(email_new) LIKE $2)',
								array($this->getID(),
									strtolower($email)))) > 0) {
				$this->setError(_('User with this email already exists.'));
				return false;
			}
		}

		db_begin();
		$res = db_query_params('UPDATE users SET email=$1 WHERE user_id=$2',
					array($email, $this->getID()));

		if (!$res) {
			$this->setError('Error: Cannot Update User Email: '.db_error());
			db_rollback();
			return false;
		} else {
			$hook_params = array();
			$hook_params['user'] = $this;
			$hook_params['user_id'] = $this->getID();
			$hook_params['user_email'] = $email;
			plugin_hook("user_setemail", $hook_params);

			if (!$this->fetchData($this->getId())) {
				db_rollback();
				return false;
			}

			db_commit();
			return true;
		}
	}

	/**
	 * setNewEmailAndHash - setNewEmailAndHash is a private operation for email change.
	 *
	 * @param    string    $email The email address.
	 * @param    string    $hash  The email hash.
	 * @return    boolean    success.
	 */
	function setNewEmailAndHash($email, $hash = '') {

		if (!$hash) {
			$hash = substr(md5(strval(time()).strval(util_randbytes())), 0, 16);
		}

		if (!$email || !validate_email($email)) {
			$this->setError(_('Invalid Email Address'));
			return false;
		}

		if (forge_get_config('require_unique_email')) {
			if (db_numrows(db_query_params('SELECT user_id FROM users WHERE user_id!=$1 AND (lower(email) LIKE $2 OR lower(email_new) LIKE $2)',
								array($this->getID(),
									strtolower($email)))) > 0) {
				$this->setError(_('User with this email already exists.'));
				return false;
			}
		}
		$res = db_query_params('UPDATE users SET confirm_hash=$1, email_new=$2 WHERE user_id=$3',
					array($hash, $email, $this->getID()));
		if (!$res) {
			$this->setError('Error: Cannot Update User Email And Hash: '.db_error());
			return false;
		} else {
			$this->data_array['email_new'] = $email;
			$this->data_array['confirm_hash'] = $hash;
			return true;
		}
	}

	/**
	 * getRealName - get the user's real name.
	 *
	 * @return    string    This user's real name.
	 */
	function getRealName() {
		return $this->data_array['realname'];
	}

	/**
	 *    setRealName - set the user's real name.
	 *
	 * @param string $realname
	 * @return    string    boolean.
	 */
	function setRealName($realname) {
		$res = db_query_params('UPDATE users SET realname=$1 WHERE user_id=$2',
			array($realname, $this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('Error: Cannot Update real name of user : '.db_error());
			return false;
		}
		$this->data_array['realname'] = $realname;
		return true;
	}

	/**
	 * getFirstName - get the user's first name.
	 *
	 * @return    string    This user's first name.
	 */
	function getFirstName() {
		return $this->data_array['firstname'];
	}

	/**
	 * getLastName - get the user's last name.
	 *
	 * @return    string    This user's last name.
	 */
	function getLastName() {
		return $this->data_array['lastname'];
	}

	/**
	 * getAddDate - this user's unix time when account was opened.
	 *
	 * @return    int    This user's unix time when account was opened.
	 */
	function getAddDate() {
		return $this->data_array['add_date'];
	}

	/**
	 * getTimeZone - this user's timezone setting.
	 *
	 * @return    string    This user's timezone setting.
	 */
	function getTimeZone() {
		return $this->data_array['timezone'];
	}

	/**
	 * getCountryCode - this user's ccode setting.
	 *
	 * @return    string    This user's ccode setting.
	 */
	function getCountryCode() {
		return $this->data_array['ccode'];
	}

	/**
	 * getShell - this user's preferred shell.
	 *
	 * @return    string    This user's preferred shell.
	 */
	function getShell() {
		return $this->data_array['shell'];
	}

	/**
	 * setShell - sets user's preferred shell.
	 *
	 * @param    string    $shell The users preferred shell.
	 * @return    boolean    success.
	 */
	function setShell($shell) {
		global $SYS;

		$shells = account_getavailableshells();

		if (!in_array($shell, $shells)) {
			$this->setError(sprintf(_('Error: Invalid Shell %s'), $shell));
			return false;
		}

		db_begin();
		$res = db_query_params('UPDATE users SET shell=$1 WHERE user_id=$2',
					array($shell, $this->getID()));
		if (!$res) {
			$this->setError(_('Error: Cannot Update User Unix Shell:').' '.db_error());
			db_rollback();
			return false;
		} else {
			// Now change LDAP attribute, but only if corresponding
			// entry exists (i.e. if user have shell access)
			if ($SYS->sysCheckUser($this->getID())) {
				if (!$SYS->sysUserSetAttribute($this->getID(), "loginShell", $shell)) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			}
			$this->data_array['shell'] = $shell;
		}
		db_commit();
		return true;
	}

	/**
	 * getUnixUID() - Get the unix UID of the user
	 *
	 * @return    int    This user's UID.
	 */
	function getUnixUID() {
		return $this->data_array['unix_uid'];
	}

	/**
	 * getUnixGID() - Get the unix GID of the user
	 *
	 * @return    int    This user's GID.
	 */
	function getUnixGID() {
		return $this->data_array['unix_gid'];
	}

	/**
	 * getLanguage - this user's language_id from supported_languages table.
	 *
	 * @return    int    This user's language_id.
	 */
	function getLanguage() {
		return $this->data_array['language'];
	}

	/**
	 * getAddress - get this user's address.
	 *
	 * @return    text    This user's address.
	 */
	function getAddress() {
		return $this->data_array['address'];
	}

	/**
	 * getAddress2 - get this user's address2.
	 *
	 * @return    text    This user's address2.
	 */
	function getAddress2() {
		return $this->data_array['address2'];
	}

	/**
	 * getPhone - get this person's phone number.
	 *
	 * @return    text    This user's phone number.
	 */
	function getPhone() {
		return $this->data_array['phone'];
	}

	/**
	 * getFax - get this person's fax number.
	 *
	 * @return text    This user's fax.
	 */
	function getFax() {
		return $this->data_array['fax'];
	}

	/**
	 * getTitle - get this person's title.
	 *
	 * @return text    This user's title.
	 */
	function getTitle() {
		return $this->data_array['title'];
	}

	/**
	 * getGroups - get an array of groups this user is a member of.
	 *
	 * @return array	Array of groups.
	 */
	function &getGroups($onlylocal = true) {
		$ids = array();
		foreach ($this->getRoles() as $r) {
			if ($onlylocal) {
				if ($r instanceof RoleExplicit
				    && $r->getHomeProject() != NULL) {
					$ids[] = $r->getHomeProject()->getID();
				}
			} else {
				foreach ($r->getLinkedProjects() as $p) {
					$ids[] = $p->getID();
				}
			}
		}
		return group_get_objects(array_values(array_unique($ids))) ;
	}

	/**
	 * getAuthorizedKeys - the SSH authorized keys set by the user.
	 *
	 * @return	string	This user's SSH authorized (public) keys.
	 */
	function getAuthorizedKeys() {
		return $this->data_array['authorized_keys'];
	}

	/**
	 *	addAuthorizedKey - add the SSH authorized key for the user.
	 *
	 * @param    string    $keys The users public keys.
	 * @return    boolean    success.
	 */
	function addAuthorizedKey($key) {
		$key = trim($key);
		$key = preg_replace("/\r\n/", "\n", $key); // Convert to Unix EOL
		$key = preg_replace("/\n+/", "\n", $key); // Remove empty lines
		$tempfile = tempnam("/tmp", "authkey");
		$ft = fopen($tempfile, 'w');
		fwrite($ft, $key);
		fclose($ft);
		$returnExec = array();
		exec("/usr/bin/ssh-keygen -lf ".$tempfile, $returnExec);
		unlink($tempfile);
		$returnExecExploded = explode(' ', $returnExec[0]);
		$fingerprint = $returnExecExploded[1];
		$now = time();
		$explodedKey = explode(' ', $key);
		$existingKeys = $this->getAuthorizedKeys();
		foreach ($existingKeys as $existingKey) {
			if ($existingKey['fingerprint'] == $fingerprint) {
				$this->setError(_('SSH Key already in use'));
				return false;
			}
		}
		$res = db_query_params('insert into sshkeys (userid, fingerprint, upload, sshkey, name, algorithm)
							values ($1, $2, $3, $4, $5, $6)',
					array($this->getID(), $fingerprint, $now, $key, $explodedKey[2], $explodedKey[0]));
		if (!$res) {
			$this->setError(_('Error - Could Not Add User SSH Key:').db_error());
			return false;
		} else {
			$keyid = db_insertid($res, 'sshkeys', 'id_sshkeys');
			$this->data_array['authorized_keys'][$keyid]['fingerprint'] = $fingerprint;
			$this->data_array['authorized_keys'][$keyid]['upload'] = $now;
			$this->data_array['authorized_keys'][$keyid]['sshkey'] = $key;
			$this->data_array['authorized_keys'][$keyid]['deploy'] = 0;
			$this->data_array['authorized_keys'][$keyid]['keyid'] = $keyid;
			return true;
		}
	}

	function deleteAuthorizedKey($keyid) {
		$res = db_query_params('update sshkeys set deleted = 1 where id_sshkeys =$1 and userid = $2',
					array($keyid, $this->getID()));
		if (!$res) {
			$this->setError(_('Error: Cannot Update User SSH Keys'));
			return false;
		} else {
			unset($this->data_array['authorized_keys'][$keyid]);
			return true;
		}
	}

	/**
	 * setLoggedIn($val) - Really only used by session code.
	 *
	 * @param    boolean    $val The session value.
	 */
	function setLoggedIn($val = true) {
		$this->is_logged_in = $val;

		if ($val) {
			$this->is_super_user = forge_check_global_perm_for_user($this, 'forge_admin') ;
		}
	}

	/**
	 * isLoggedIn - only used by session code.
	 *
	 * @return    boolean    is_logged_in.
	 */
	function isLoggedIn() {
		return $this->is_logged_in;
	}

	/**
	 * deletePreference - delete a preference for this user.
	 *
	 * @param    string    $preference_name The unique field name for this preference.
	 * @return    boolean    success.
	 */
	function deletePreference($preference_name) {
		$preference_name = strtolower(trim($preference_name));
		unset($this->user_pref["$preference_name"]);
		$res = db_query_params('DELETE FROM user_preferences WHERE user_id=$1 AND preference_name=$2',
					array($this->getID(), $preference_name));
		return ((!$res || db_affected_rows($res) < 1) ? false : true);
	}

	/**
	 * setPreference - set a new preference for this user.
	 *
	 * @param    string    $preference_name The unique field name for this preference.
	 * @param    string    $value           The value you are setting this preference to.
	 * @return    boolean    success.
	 */
	function setPreference($preference_name, $value) {
		$preference_name = strtolower(trim($preference_name));
		//delete pref if not value passed in
		unset($this->user_pref);
		if (!isset($value)) {
			$result = db_query_params('DELETE FROM user_preferences WHERE user_id=$1 AND preference_name=$2',
						   array($this->getID(),
							  $preference_name));
		} else {
			$result = db_query_params('UPDATE user_preferences SET preference_value=$1,set_date=$2	WHERE user_id=$3 AND preference_name=$4',
						   array($value,
							  time(),
							  $this->getID(),
							  $preference_name));
			if (db_affected_rows($result) < 1) {
				//echo db_error();
				$result = db_query_params('INSERT INTO user_preferences (user_id,preference_name,preference_value,set_date) VALUES ($1,$2,$3,$4)',
							   array($this->getID(),
								  $preference_name,
								  $value,
								  time()));
			}
		}
		return ((!$result || db_affected_rows($result) < 1) ? false : true);
	}

	/**
	 * getPreference - get a specific preference.
	 *
	 * @param    string        $preference_name The unique field name for this preference.
	 * @return    string|bool    the preference string or false on failure.
	 */
	function getPreference($preference_name) {
		$preference_name = strtolower(trim($preference_name));
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
			$result = db_query_params('SELECT preference_name,preference_value FROM user_preferences WHERE user_id=$1',
						   array($this->getID()));
			if (db_numrows($result) < 1) {
				//echo "\n\nNo Prefs Found";
				return false;
			} else {
				$pref = array();
				//iterate and put the results into an array
				for ($i = 0; $i < db_numrows($result); $i++) {
					$pref["".db_result($result, $i, 'preference_name').""] = db_result($result, $i, 'preference_value');
				}
				$this->user_pref = $pref;

				if (array_key_exists($preference_name, $this->user_pref)) {
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
	 * setPasswd - Changes user's password.
	 *
	 * @param    string    $passwd The plaintext password.
	 * @return    boolean    success.
	 */
	function setPasswd($passwd) {
		global $SYS;
		if (!account_pwvalid($passwd)) {
			$this->setError(_('Error')._(': ').$GLOBALS['register_error']);
			return false;
		}

		db_begin();
		$md5_pw = md5($passwd);
		$unix_pw = account_genunixpw($passwd);

		$res = db_query_params('UPDATE users SET user_pw=$1, unix_pw=$2 WHERE user_id=$3',
					array($md5_pw,
					       $unix_pw,
					       $this->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error: Cannot Change User Password:').' '.db_error());
			db_rollback();
			return false;
		} else {
			// Now change LDAP password, but only if corresponding
			// entry exists (i.e. if user have shell access)
			if ($SYS->sysCheckUser($this->getID())) {
				if (!$SYS->sysUserSetAttribute($this->getID(), "userPassword", '{crypt}'.$unix_pw)) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			}
		}
		$hook_params = array();
		$hook_params['user'] = $this;
		$hook_params['user_id'] = $this->getID();
		$hook_params['user_password'] = $passwd;
		plugin_hook("user_setpasswd", $hook_params);
		db_commit();
		return true;
	}

	/**
	 * setMD5Passwd - Changes user's MD5 password.
	 *
	 * @param    string    $md5 The MD5-hashed password.
	 * @return    boolean    success.
	 */
	function setMD5Passwd($md5) {
		db_begin();
		if ($md5) {
			$res = db_query_params('UPDATE users SET user_pw=$1 WHERE user_id=$2',
				array($md5, $this->getID()));

			if (!$res || db_affected_rows($res) < 1) {
				$this->setError(_('Error: Cannot Change User Password:').' '.db_error());
				db_rollback();
				return false;
			}
		}
		db_commit();
		return true;
	}

	/**
	 * setUnixPasswd - Changes user's Unix-hashed password.
	 *
	 * @param    string    $unix The Unix-hashed password.
	 * @return    boolean    success.
	 */
	function setUnixPasswd($unix) {
		global $SYS;

		db_begin();
		if ($unix) {
			$res = db_query_params('UPDATE users SET unix_pw=$1 WHERE user_id=$2',
				array($unix, $this->getID()));

			if (!$res || db_affected_rows($res) < 1) {
				$this->setError(_('Error: Cannot Change User Password:').' '.db_error());
				db_rollback();
				return false;
			}

			// Now change system password, but only if corresponding
			// entry exists (i.e. if user have shell access)
			if ($SYS->sysCheckUser($this->getID())) {
				if (!$SYS->sysUserSetAttribute($this->getID(), "userPassword", '{crypt}'.$unix)) {
					$this->setError($SYS->getErrorMessage());
					db_rollback();
					return false;
				}
			}
		}
		db_commit();
		return true;
	}

	/**
	 * usesRatings - whether user participates in rating system.
	 *
	 * @return    boolean    success.
	 */
	function usesRatings() {
		return !$this->data_array['block_ratings'];
	}

	/**
	 * usesTooltips - whether user enables or not tooltips.
	 *
	 * @return    boolean    success.
	 */
	function usesTooltips() {
		return $this->data_array['tooltips'];
	}

	/**
	 * getPlugins -  get a list of all available user plugins
	 *
	 * @return    array    array containing plugin_id => plugin_name
	 */
	function getPlugins() {
		if (!isset($this->plugins_data)) {
			$this->plugins_data = array();
			$res = db_query_params('SELECT user_plugin.plugin_id, plugins.plugin_name
						 FROM user_plugin, plugins
						 WHERE user_plugin.user_id=$1
						 AND user_plugin.plugin_id=plugins.plugin_id',
				array($this->getID()));
			$rows = db_numrows($res);

			for ($i = 0; $i < $rows; $i++) {
				$plugin_id = db_result($res, $i, 'plugin_id');
				$this->plugins_data[$plugin_id] = db_result($res, $i, 'plugin_name');
			}
		}
		return $this->plugins_data;
	}

	/**
	 * usesPlugin - returns true if the user uses a particular plugin
	 *
	 * @param    string    $pluginname name of the plugin
	 * @return    boolean    whether plugin is being used or not
	 */
	function usesPlugin($pluginname) {
		$plugins_data = $this->getPlugins();
		foreach ($plugins_data as $p_name) {
			if ($p_name == $pluginname) {
				return true;
			}
		}
		return false;
	}

	/**
	 * setPluginUse - enables/disables plugins for the user
	 *
	 * @param    string     $pluginname name of the plugin
	 * @param    boolean    $val        the new state
	 * @return    string    database result
	 */
	function setPluginUse($pluginname, $val = true) {
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
		$plugin_id = db_result($res, 0, 'plugin_id');
		// Invalidate cache
		unset ($this->plugins_data);
		if ($val) {
			return db_query_params('INSERT INTO user_plugin (user_id,plugin_id) VALUES ($1,$2)',
				array($this->getID(),
					$plugin_id));
		} else {
			return db_query_params('DELETE FROM user_plugin WHERE user_id=$1 AND plugin_id=$2',
				array($this->getID(),
					$plugin_id));
		}
	}

	/**
	 * getMailingsPrefs - Get activity status for one of the site mailings.
	 *
	 * @param    string    $mailing_id The id of mailing ('mail_va' for community mailings, 'mail_siteupdates' for site mailings)
	 * @return    boolean    success.
	 */
	function getMailingsPrefs($mailing_id) {
		if ($mailing_id == 'va') {
			return $this->data_array['mail_va'];
		} elseif ($mailing_id == 'site') {
			return $this->data_array['mail_siteupdates'];
		} else {
			return 0;
		}
	}

	/**
	 * unsubscribeFromMailings - Disable email notifications for user.
	 *
	 * @param    boolean    $all If false, disable general site mailings, else - all.
	 * @return    boolean    success.
	 */
	function unsubscribeFromMailings($all = false) {
		$res1 = $res2 = $res3 = true;
		$res1 = db_query_params('UPDATE users SET mail_siteupdates=0, mail_va=0 WHERE user_id=$1',
			array($this->getID()));
		if ($all) {
			$res2 = db_query_params('DELETE FROM forum_monitored_forums WHERE user_id=$1',
				array($this->getID()));
			$res3 = db_query_params('DELETE FROM filemodule_monitor WHERE user_id=$1',
				array($this->getID()));
		}

		return $res1 && $res2 && $res3;
	}

	/**
	 * getThemeID - get the theme_id for this user.
	 *
	 * @return    int    The theme_id.
	 */
	function getThemeID() {
		return $this->data_array['theme_id'];
	}

	/**
	 * setUpTheme - get the theme path
	 *
	 * @return    string    The theme path.
	 */
	function setUpTheme() {
//
//	An optimization in session_getdata lets us pre-fetch this in most cases.....
//
		if (!isset($this->data_array['dirname']) || !$this->data_array['dirname']) {
			$res = db_query_params('SELECT dirname FROM themes WHERE theme_id=$1',
				array($this->getThemeID()));
			$this->theme = db_result($res, 0, 'dirname');
		} else {
			$this->theme = $this->data_array['dirname'];
		}
		if (is_file(forge_get_config('themes_root').'/'.$this->theme.'/Theme.class.php')) {
			$GLOBALS['sys_theme'] = $this->theme;
		} else {
			$this->theme = forge_get_config('default_theme');
		}
		return $this->theme;
	}

	/**
	 * getRole() - Get user Role object.
	 *
	 * @param	object	group object
	 * @return	object	Role object
	 */
	function getRole(&$g) {
		if (is_int ($g) || is_string($g)) {
			$group_id = $g;
		} else {
			$group_id = $g->getID();
		}
		foreach ($this->getRoles () as $r) {
			if ($r instanceof RoleExplicit ) {
				$homeproj = $r->getHomeProject();
				if ($homeproj) {
					if ($homeproj->getID() == $group_id) {
						return $r;
					}
				}
			}
		}
		return false;
	}

	function getRoles() {
		return RBACEngine::getInstance()->getAvailableRolesForUser($this);
	}

	/* Codendi Glue */
	function isMember($g, $type = 0){
		if (is_int ($g) || is_string($g)) {
			$group = group_get_object($g) ;
			$group_id = $g ;
		} else {
			$group = $g ;
			$group_id = $group->getID() ;
		}

		switch ($type) {
		case 0:
		default:
			foreach ($this->getGroups() as $p) {
				if ($p->getID() == $group_id) {
					return true ;
				}
			}
			return false ;
			break;
		case 'P2':
			//pm admin
			return forge_check_perm_for_user($this, 'pm_admin', $group_id);
			break;
		case 'F2':
			//forum admin
			return forge_check_perm_for_user($this, 'forum_admin', $group_id);
			break;
		case 'A':
			//admin for this group
			return forge_check_perm_for_user($this, 'project_admin', $group_id);
			break;
		case 'D1':
			//document editor
			return forge_check_perm_for_user($this, 'docman', $group_id, 'admin');
			break;
		}
	}

	/**
	 * setAdminNotification - send an email to all admins (used in verify.php)
	 *
	 * @return	boolean	True
	 */
	function setAdminNotification() {
		$admins = RBACEngine::getInstance()->getUsersByAllowedAction('forge_admin', -1);
		foreach ($admins as $admin) {
			$admin_email = $admin->getEmail();
			setup_gettext_for_user($admin);
			$message = sprintf(_('New User %1$s registered and validated
Full Name:  %2$s
Email: %3$s
'),
					$this->getUnixName() , $this->getRealName(), $this->getEmail());
			util_send_message($admin_email, sprintf(_('New %1$s User'), forge_get_config ('forge_name')), $message);
			setup_gettext_from_context();
		}
		return true;
	}
}

/*

		EVERYTHING BELOW HERE IS DEPRECATED

		DO NOT USE FOR ANY NEW CODE

*/

/**
 * user_ismember() - DEPRECATED; DO NOT USE! (TODO: document what should be used instead)
 *  Replace user_ismember(1[, 'A']) with forge_check_global_perm('forge_admin')
 *  Replace user_ismember($group_id, 'A') with forge_check_perm('project_admin', $group_id)
 *  For now, keep user_ismember($group_id) alone
 *
 * @param        int        $group_id The Group ID
 * @param        int        $type     The Type
 * @return bool
 * @deprecated
 *
 */
function user_ismember($group_id, $type = 0) {
	if (!session_loggedin()) {
		return false;
	}

	return session_get_user()->isMember($group_id, $type) ;
}

/**
 * user_getname() - DEPRECATED; DO NOT USE! (TODO: document what should be used instead)
 *
 * @param        int        $user_id The User ID
 * @return string
 * @deprecated
 *
 */
function user_getname($user_id = false) {
	// use current user if one is not passed in
	if (!$user_id) {
		if (session_loggedin()) {
			$user = user_get_object(user_getid());
			if ($user) {
				return $user->getUnixName();
			} else {
				return 'Error getting user';
			}
		} else {
			return 'No User Id';
		}
	} else {
		$user = user_get_object($user_id);
		if ($user) {
			return $user->getUnixName();
		} else {
			return 'Invalid User';
		}
	}
}

class UserComparator {
	var $criterion = 'name';

	function Compare($a, $b) {
		switch ($this->criterion) {
			case 'name':
			default:
				$name_compare = strcoll($a->getRealName(), $b->getRealName());
				if ($name_compare != 0) {
					return $name_compare;
				}
				/* If several projects share a same real name */
				return strcoll($a->getUnixName(), $b->getUnixName());
				break;
			case 'unixname':
				return strcmp($a->getUnixName(), $b->getUnixName());
				break;
			case 'id':
				$aid = $a->getID();
				$bid = $b->getID();
				if ($a == $b) {
					return 0;
				}
				return ($a < $b)? -1 : 1;
				break;
		}
	}
}

function sortUserList(&$list, $criterion = 'name') {
	$cmp = new UserComparator ();
	$cmp->criterion = $criterion;

	return usort($list, array($cmp, 'Compare'));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

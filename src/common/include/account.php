<?php
/**
 * FusionForge account functions
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2010, Franck Villaume - Capgemini
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

/**
 * account_pwvalid() - Validates a password
 *
 * @param		string	The plaintext password string
 * @returns		true on success/false on failure
 *
 */
function account_pwvalid($pw) {
	if (strlen($pw) < 6) {
		$GLOBALS['register_error'] = _('Password must be at least 6 characters.');
		return 0;
	}
	return 1;
}

/**
 * account_namevalid() - Validates a login username
 *
 * @param		string	The username string
 * @returns		true on success/false on failure
 *
 */
function account_namevalid($name) {


	// no spaces
	if (strrpos($name,' ') > 0) {
		$GLOBALS['register_error'] = _('There cannot be any spaces in the login name.');
		return 0;
	}

	// min and max length
	if (strlen($name) < 3) {
		$GLOBALS['register_error'] = _('Name is too short. It must be at least 3 characters.');
		return 0;
	}
	if (strlen($name) > 15) {
		$GLOBALS['register_error'] = _('Name is too long. It must be less than 15 characters.');
		return 0;
	}

	if (!preg_match('/^[a-z][-a-z0-9_]+$/', $name)) {
		$GLOBALS['register_error'] = _('Illegal character in name.');
		return 0;
	}

	// illegal names
	$regExpReservedNames = "^(root|bin|daemon|adm|lp|sync|shutdown|halt|mail|news|"
		. "uucp|operator|games|mysql|httpd|nobody|dummy|www|cvs|shell|ftp|irc|"
		. "debian|ns|download)$";
	if( preg_match("/$regExpReservedNames/i", $name) ) {
		$GLOBALS['register_error'] = _('Name is reserved.');
		return 0;
	}
	if (forge_get_config('use_shell')) {
		if ( exec("getent passwd $name") != "" ){
			$GLOBALS['register_error'] = _('That username already exists.');
			return 0;
		}
		if ( exec("getent group $name") != "" ){
			$GLOBALS['register_error'] = _('That username already exists.');
			return 0;
		}
	}
	if (preg_match("/^(anoncvs_)/i",$name)) {
		$GLOBALS['register_error'] = _('Name is reserved for CVS.');
		return 0;
	}
		
	return 1;
}

/**
 * account_groupnamevalid() - Validates an account group name
 *
 * @param		string	The group name string
 * @returns		true on success/false on failure
 *
 */
function account_groupnamevalid($name) {
	if (!account_namevalid($name)) return 0;
	
	// illegal names
	$regExpReservedGroupNames = "^(www[0-9]?|cvs[0-9]?|shell[0-9]?|ftp[0-9]?|"
		. "irc[0-9]?|news[0-9]?|mail[0-9]?|ns[0-9]?|download[0-9]?|pub|users|"
		. "compile|lists|slayer|orbital|tokyojoe|webdev|projects|cvs|monitor|"
		. "mirrors?)$";
	if(preg_match("/$regExpReservedGroupNames/i",$name)) {
		$GLOBALS['register_error'] = _('Name is reserved for DNS purposes.');
		return 0;
	}

	if(preg_match("/_/",$name)) {
		$GLOBALS['register_error'] = _('Group name cannot contain underscore for DNS reasons.');
		return 0;
	}

	return 1;
}

/**
 * genchr() - Generate a random character
 * 
 * This is a local function used for account_salt()
 *
 * @return int $num A random character
 *
 */
function genchr(){
	do {	  
		$num = util_randnum(46, 122);
	} while ( ( $num > 57 && $num < 65 ) || ( $num > 90 && $num < 97 ) );	  
	$char = chr($num);	  
	return $char;	  
}	   

/**
 * account_gensalt() - A random salt generator
 *
 * @returns The random salt string
 *
 */
function account_gensalt(){

	// ncommander: modified for cipher selection
	// crypt() selects the cipher based on
	// the salt, so ...
	
	$a = genchr(); 
	$b = genchr();
	switch(forge_get_config('unix_cipher')) {
		case 'DES':
			$salt = "$a$b";
			break;
		default:
		case 'MD5':	
			$salt = "$1$" . "$a$b";
			break;
		case 'Blowfish':
			$i = 0;
			while (!$i = 16) {
			 	$salt .= rand(64,126);
			 	$i++;
			 }
			return "$2a$".$salt;
			break;
	}
	return $salt;	
}

/**
 * account_genunixpw() - Generate unix password
 *
 * @param		string	The plaintext password string
 * @return		The encrypted password
 *
 */
function account_genunixpw($plainpw) {
	// ncommander: Support clear password hashing
	// for usergroup_plain.php

	if (strcasecmp(forge_get_config('unix_cipher'), 'Plain') == 0) {
		return $plainpw;
	} else {
		return crypt($plainpw,account_gensalt());
	}
}

/**
 * account_shellselects() - Print out shell selects
 *
 * @param		string	The current shell
 *
 */
function account_shellselects($current) {
	$shells = file("/etc/shells");
	$shells[count($shells)] = "/bin/cvssh";

	for ($i = 0; $i < count($shells); $i++) {
		$this_shell = chop($shells[$i]);

		if ($current == $this_shell) {
			echo "<option selected=\"selected\" value=$this_shell>$this_shell</option>\n";
		} else {
			if (! preg_match("/^#/",$this_shell)){
				echo "<option value=\"$this_shell\">$this_shell</option>\n";
			}
		}
	}
}

/**
 *	account_user_homedir() - Returns full path of user home directory
 *
 *  @param		string	The username
 *	@return home directory path
 */
function account_user_homedir($user) {
	//return '/home/users/'.substr($user,0,1).'/'.substr($user,0,2).'/'.$user;
	return forge_get_config('homedir_prefix').'/'.$user;
}

/**
 *	account_group_homedir() - Returns full path of group home directory
 *
 *  @param		string	The group name
 *	@return home directory path
 */
function account_group_homedir($group) {
	//return '/home/groups/'.substr($group,0,1).'/'.substr($group,0,2).'/'.$group;
	return forge_get_config('groupdir_prefix').'/'.$group;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

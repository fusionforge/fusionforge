<?php
/**
 * FusionForge account functions
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
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

/**
 * account_pwvalid() - Validates a password
 *
 * @param	string	$pw	The plaintext password string
 * @return	bool	true on success/false on failure
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
 * @param	string	$name	The username string
 * @param	bool	$unix	Check for an unix username
 * @return	bool	true on success/false on failure
 *
 */
function account_namevalid($name, $unix=false) {

	if (!$unix) {
		// If accounts comes from ldap and no shell access, then disable controls.
		$pluginManager = plugin_manager_get_object();
		if (!forge_get_config('use_shell') && $pluginManager->PluginIsInstalled('ldapextauth')) {
			return true;
		}
	}

	// no spaces
	if (strrpos($name,' ') > 0) {
		$GLOBALS['register_error'] = _('There cannot be any spaces in the login name.');
		return false;
	}

	// min and max length
	if (strlen($name) < 3) {
		$GLOBALS['register_error'] = _('Name is too short. It must be at least 3 characters.');
		return false;
	}
	if (strlen($name) > 15) {
		$GLOBALS['register_error'] = _('Name is too long. It must be less than 15 characters.');
		return false;
	}

	if (!preg_match('/^[a-z0-9][-a-z0-9_\.]+\z/', $name)) {
		$GLOBALS['register_error'] = _('Illegal character in name.');
		return false;
	}

	// illegal names
	$regExpReservedNames = "^(root|bin|daemon|adm|lp|sync|shutdown|halt|mail|news|"
		. "uucp|operator|games|mysql|httpd|nobody|dummy|www|cvs|shell|ftp|irc|"
		. "debian|ns|download)$";
	if( preg_match("/$regExpReservedNames/i", $name) ) {
		$GLOBALS['register_error'] = _('Name is reserved.');
		return false;
	}
	if (forge_get_config('use_shell')) {
		if (exec("getent passwd $name") != "" ){
			$GLOBALS['register_error'] = _('That username already exists.');
			return false;
		}
		if (exec("getent group $name") != "" ){
			$GLOBALS['register_error'] = _('That username already exists.');
			return false;
		}
	}
	if (preg_match("/^(anoncvs_)/i",$name)) {
		$GLOBALS['register_error'] = _('Name is reserved for CVS.');
		return false;
	}

	return true;
}

/**
 * account_groupnamevalid() - Validates an account group name
 *
 * @param	string	$name	The group name string
 * @return	bool	true on success/false on failure
 *
 */
function account_groupnamevalid($name) {
	if (!account_namevalid($name, 1)) return 0;

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
 * genchr - Generate a random character
 *
 * This is a local function used for account_salt()
 *
 * @return	string	A random character
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
 * account_gensalt - A random salt generator
 *
 * @returns	string	The random salt string
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
 * account_genunixpw - Generate unix password
 *
 * @param	string	$plainpw	The plaintext password string
 * @return	string	The encrypted password
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
 * account_get_user_default_shell - return default user shell
 *
 * @return	string	the shell absolute path.
 */
function account_get_user_default_shell() {
	$user_default_shell = forge_get_config('user_default_shell');
	if (! isset($user_default_shell)) {
		// same as in DB schema before that config var was introduced
		$user_default_shell = '/bin/bash';
	}
	return $user_default_shell;
}

/**
 * account_getavailableshells - return available shells for the users
 *
 * @return	array	Available shells
 */
function account_getavailableshells($add_user_default_shell = TRUE) {
	// we'd better use the shells defined inside the 'chroot' in {core/chroot}/etc/shells it it exists
	$chroot = forge_get_config('chroot');
	$shells_file = $chroot.'/etc/shells';
	if(! file_exists($shells_file) ) {
		// otherwise, fallback to /etc/shells
		$shells_file = '/etc/shells';
	}
	$shells = file($shells_file);

	$out_shells = array();
	foreach ($shells as $s) {
		if (substr($s, 0, 1) == '#') {
			continue;
		}
		$out_shells[] = chop($s);
	}
	// in most cases, we do need to add the default shell in case it wouldn't be in the ../etc/shells already (no regression)
	if ($add_user_default_shell) {
		$user_default_shell = account_get_user_default_shell();
		if (! file_exists($user_default_shell) ) {
			// we'll always add cvssh if no other defaukt set ... TODO: explain why ?
			$user_default_shell = "/bin/cvssh";
		}
		if (!in_array($user_default_shell, $out_shells)) {
			$out_shells[count($out_shells)] = $user_default_shell;
		}
	}
	return $out_shells;
}

/**
 * account_shellselects - Print out shell selects
 *
 * @param	string	the current shell
 * @return	string	HTML code options for a select tag
 */
function account_shellselects($current) {
	$html = '';

	$shells = account_getavailableshells();

	$found = false;
	for ($i = 0; $i < count($shells); $i++) {
		$this_shell = $shells[$i];

		if ($current == $this_shell) {
			$found = true;
			$html .= "<option selected=\"selected\" value=\"$this_shell\">$this_shell</option>\n";
		} else {
			// the last one is supposed to be the default, so select it if not found current shell to observe default settings
			if ( ($i == (count($shells) - 1)) && (! $found)) {
				$html .= "<option selected=\"selected\" value=\"$this_shell\">$this_shell</option>\n";
			} else {
				$html .= "<option value=\"$this_shell\">$this_shell</option>\n";
			}
		}
	}
	if (!$found) {
		// add the current option but unselectable -> defaults to cvssh if no other option in {core/chroot}/etc/shells
		$html .= "<option value=\"$current\" disabled=\"disabled\">$current</option>\n";
	}
	echo $html;
}

/**
 * account_user_homedir - Returns full path of user home directory
 *
 * @param	string	The username
 * @return	string	home directory path
 */
function account_user_homedir($user) {
	//return '/home/users/'.substr($user,0,1).'/'.substr($user,0,2).'/'.$user;
	return forge_get_config('homedir_prefix').'/'.$user;
}

/**
 * account_group_homedir - Returns full path of group home directory
 *
 * @param	string	The group name
 * @return	string	home directory path
 */
function account_group_homedir($group) {
	//return '/home/groups/'.substr($group,0,1).'/'.substr($group,0,2).'/'.$group;
	return forge_get_config('groupdir_prefix').'/'.$group;
}

/**
 * checkKeys - Simple function that tries to check the validity of public ssh keys with a regexp.
 * Exits with an error message if an invalid key is found.
 *
 * @param	string	$keys	A string with a set of keys to check. Each key is delimited by a carriage return.
 */
function checkKeys($keys) {
	$key = strtok($keys, "\n");
	while ($key !== false) {
		$key = trim($key);
		if ((strlen($key) > 0) && ($key[0] != '#')) {
			/* The encoded key is made of 0-9, A-Z ,a-z, +, / (base 64) characters,
			 ends with zero or up to three '=' and the length must be >= 512 bits (157 base64 characters).
			 The whole key ends with an optional comment. */
			if ( preg_match("@^(((no-port-forwarding|no-X11-forwarding|no-agent-forwarding|no-pty|command=\"[^\"]+\"|from=\"?[A-Za-z0-9\.-]+\"?),?)*\s+)?ssh-(rsa|dss)\s+[A-Za-z0-9+/]{157,}={0,2}(\s+.*)?$@", $key) === 0 ) { // Warning: we must use === for the test
				$msg = sprintf(_('The following key has a wrong format: |%s|.  Please, correct it by going back to the previous page.'),
						htmlspecialchars($key));
				session_redirect('/account/?&error_msg='.urlencode($msg));
			}
		}
		$key = strtok("\n");
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: account.php,v 1.35 2000/11/03 02:17:32 tperdue Exp $
//
// adduser.php - All the forms and functions to manage unix users
//

/*

	Create a new user account

	returns user_id/false and $feedback


*/
function account_register_new($unix_name,$realname,$password1,$password2,$email,$language,$timezone,$mail_site,$mail_va,$language_id,$timezone) {
	global $feedback;

	if (db_numrows(db_query("SELECT user_id FROM users WHERE user_name LIKE '$unix_name'")) > 0) {
		$feedback .= "That username already exists.";
		return false;
	}       
	if (!$unix_name) {
		$feedback .= "You must supply a username.";
		return false;
	}       
	if (!$password1) {
		$feedback .= "You must supply a password.";
		return false;
	}       
	if ($password1 != $password2) {
		$feedback .= "Passwords do not match.";
		return false;
	}       
	if (!account_pwvalid($password1)) {
		$feedback .= ' Password must be at least 6 characters. ';
		return false;
	}       
	if (!account_namevalid($unix_name)) {
		$feedback .= ' Invalid Unix Name ';
		return false;
	}       
	if (!validate_email($email)) {
		$feedback .= ' Invalid Email Address ';
		return false;
	}
	// if we got this far, it must be good
	$confirm_hash = substr(md5($session_hash . $HTTP_POST_VARS['form_pw'] . time()),0,16);

	$result=db_query("INSERT INTO users (user_name,user_pw,unix_pw,realname,email,add_date,"
		. "shell,status,confirm_hash,mail_siteupdates,mail_va,language,timezone) "
		. "VALUES ('$unix_name',".
		"'". md5($password1) . "',".
		"'". account_genunixpw($password1) . "',".
		"'". "$realname',".
		"'$email',".
		"'" . time() . "',".
		"'/bin/cvssh',".
		"'P',".
		"'$confirm_hash',".
		"'". (($mail_site)?"1":"0") . "',".
		"'". (($mail_va)?"1":"0") . "',".
		"'$language_id',".
		"'$timezone')");
	$user_id=db_insertid($result,'users','user_id');

	if (!$result || !$user_id) {
		$feedback .= ' Insert Failed '.db_error();
		return false;
	} else {
	
		// send mail
		$message = "Thank you for registering on the SourceForge web site. In order\n"
			. "to complete your registration, visit the following url: \n\n"
			. "<https://". $GLOBALS['HTTP_HOST'] ."/account/verify.php?confirm_hash=$confirm_hash>\n\n"
			. "Enjoy the site.\n\n"
			. " -- the SourceForge staff\n";
			
		mail($email,"SourceForge Account Registration",$message,"From: noreply@".$GLOBALS['HTTP_HOST']);
		
		return $user_id;
	}       
}

function account_pwvalid($pw) {
	if (strlen($pw) < 6) {
		$GLOBALS['register_error'] = "Password must be at least 6 characters.";
		return 0;
	}
	return 1;
}

function account_namevalid($name) {
	// no spaces
	if (strrpos($name,' ') > 0) {
		$GLOBALS['register_error'] = "There cannot be any spaces in the login name.";	
		return 0;
	}

	// must have at least one character
	if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") == 0) {
		$GLOBALS['register_error'] = "There must be at least one character.";
		return 0;
	}

	// must contain all legal characters
	//if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#\$%^&*()-_\\/{}[]<>+=|;:?.,`~")
	if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_")
		!= strlen($name)) {
		$GLOBALS['register_error'] = "Illegal character in name.";
		return 0;
	}

	// min and max length
	if (strlen($name) < 3) {
		$GLOBALS['register_error'] = "Name is too short. It must be at least 3 characters.";
		return 0;
	}
	if (strlen($name) > 15) {
		$GLOBALS['register_error'] = "Name is too long. It must be less than 15 characters.";
		return 0;
	}

	// illegal names
	if (eregi("^((root)|(bin)|(daemon)|(adm)|(lp)|(sync)|(shutdown)|(halt)|(mail)|(news)"
		. "|(uucp)|(operator)|(games)|(mysql)|(httpd)|(nobody)|(dummy)"
		. "|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(ns)|(download))$",$name)) {
		$GLOBALS['register_error'] = "Name is reserved.";
		return 0;
	}
	if (eregi("^(anoncvs_)",$name)) {
		$GLOBALS['register_error'] = "Name is reserved for CVS.";
		return 0;
	}
		
	return 1;
}

function account_groupnamevalid($name) {
	if (!account_namevalid($name)) return 0;
	
	// illegal names
	if (eregi("^((www[0-9]?)|(cvs[0-9]?)|(shell[0-9]?)|(ftp[0-9]?)|(irc[0-9]?)|(news[0-9]?)"
		. "|(mail[0-9]?)|(ns[0-9]?)|(download[0-9]?)|(pub)|(users)|(compile)|(lists)"
		. "|(slayer)|(orbital)|(tokyojoe)|(webdev)|(projects)|(cvs)|(slayer)|(monitor)|(mirrors?))$",$name)) {
		$GLOBALS['register_error'] = "Name is reserved for DNS purposes.";
		return 0;
	}

	if (eregi("_",$name)) {
		$GLOBALS['register_error'] = "Group name cannot contain underscore for DNS reasons.";
		return 0;
	}

	return 1;
}

// The following is a random salt generator
function account_gensalt(){
	function rannum(){	     
		mt_srand((double)microtime()*1000000);		  
		$num = mt_rand(46,122);		  
		return $num;		  
	}	     
	function genchr(){
		do {	  
			$num = rannum();		  
		} while ( ( $num > 57 && $num < 65 ) || ( $num > 90 && $num < 97 ) );	  
		$char = chr($num);	  
		return $char;	  
	}	   

	$a = genchr(); 
	$b = genchr();
	$salt = "$1$" . "$a$b";
	return $salt;	
}

// generate unix pw
function account_genunixpw($plainpw) {
	return crypt($plainpw,account_gensalt());
}

// print out shell selects
function account_shellselects($current) {
	$shells = file("/etc/shells");
	$shells[count($shells)] = "/bin/cvssh";

	for ($i = 0; $i < count($shells); $i++) {
		$this_shell = chop($shells[$i]);

		if ($current == $this_shell) {
			echo "<option selected value=$this_shell>$this_shell</option>\n";
		} else {
			if (! ereg("^#",$this_shell)){
				echo "<option value=$this_shell>$this_shell</option>\n";
			}
		}
	}
}

?>

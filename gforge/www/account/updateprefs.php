<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: updateprefs.php,v 1.12 2000/12/07 21:58:27 pfalcon Exp $

require "pre.php";    
session_require(array('isloggedin'=>1));

db_query("UPDATE users SET "
	. "mail_siteupdates=" . ($form_mail_site?"1":"0") . ","
	. "mail_va=" . ($form_mail_va?"1":"0") . " WHERE "
	. "user_id=" . user_getid());

if ($form_remember_user) {
	$user=&user_get_object(user_getid());
        // set cookie, expire in one year 
	setcookie("sf_user_hash",user_getid().'_'.substr($user->getMD5Passwd(),0,16),time()+90*24*60*60,'/');
} else {
        // remove cookie
	setcookie("sf_user_hash",'',0,'/');
}

session_redirect("/account/");

?>

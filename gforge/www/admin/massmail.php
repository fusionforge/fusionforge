<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
session_require(array('group'=>'1','admin_flags'=>'A'));
$HTML->header(array('title'=>"Administrative Mass Mail Engine"));

// get numbers of users for each mailing
$res_count = db_query("SELECT count(*) AS count FROM users WHERE status='A' AND mail_va=1");
$row_count = db_fetch_array($res_count);
$count_comm = $row_count[count];
$res_count = db_query("SELECT count(*) AS count FROM users WHERE status='A' AND mail_siteupdates=1");
$row_count = db_fetch_array($res_count);
$count_sf = $row_count[count];
$res_count = db_query("SELECT count(*) AS count FROM users WHERE status='A'");
$row_count = db_fetch_array($res_count);
$count_all = $row_count[count];
$res_count = db_query("SELECT count(*) AS count FROM users,user_group WHERE "
	."users.user_id=user_group.user_id AND users.status='A' AND user_group.admin_flags='A'");
$row_count = db_fetch_array($res_count);
$count_admin = $row_count[count];
$res_count = db_query("SELECT count(*) AS count FROM users,user_group WHERE "
	."users.user_id=user_group.user_id AND users.status='A'");
$row_count = db_fetch_array($res_count);
$count_devel = $row_count[count];
$res_count = db_query("SELECT count(*) AS count FROM users,user_group WHERE "
	."users.user_id=user_group.user_id AND users.status='A' AND user_group.group_id=1");
$row_count = db_fetch_array($res_count);
$count_sfadmin = $row_count[count];

print '<P><B>Mail Engine for SourceForge Subscribers (MESS)</B>

<P>Be <FONT color=#FF0000><B>VERY</B></FONT> careful with this form,
because sutmitting it WILL send email to lots of users.

<FORM action="massmail_execute.php">
<INPUT type="radio" name="destination" value="comm">
Send only to users subscribed to "Additional Community Mailings" ('
.$count_comm
.')<BR><INPUT type="radio" name="destination" value="sf">
Send only to users that agreed to receive "Site Updates" ('
.$count_sf
.')<BR><INPUT type="radio" name="destination" value="devel">
Send only to project developers ('
.$count_devel
.')<BR><INPUT type="radio" name="destination" value="admin">
Send only to project administrators ('
.$count_admin
.')<BR><INPUT type="radio" name="destination" value="sfadmin">
Send only to SourceForge administrators (test) ('
.$count_sfadmin
.')<BR><INPUT type="radio" name="destination" value="all">
Send to all users, regardless of their preferences ('
.$count_all
.')
<P><B>Start With User ID:</B> (for use when the process quits)
<BR><INPUT type="text" name="first_user" value="0">
<P>
Subject:
<BR><INPUT type="text" name="mail_subject" value="SourceForge: ">

<P>Text of Message:
<PRE>
<BR><TEXTAREA name="mail_message" cols="70" rows="40" wrap="physical">

---------------------
This email was sent from '. $GLOBALS['sys_default_domain'] .'. To change your email receipt
preferences, please visit the site and edit your account via the
"Account Maintenance" link.

Direct any questions to admin@'. $GLOBALS['sys_default_domain'].', or reply to this email.
</TEXTAREA>
</PRE>
<P><INPUT type="submit" name="Submit" value="Submit">

</FORM>
';

$HTML->footer(array());

?>

<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require_once('pre.php');

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

print '<p><strong>Mail Engine for <?php echo $GLOBALS['sys_name']; ?> Subscribers</strong>

<p>Be <span style="color:red"><strong>VERY</strong></span> careful with this form,
because sutmitting it WILL send email to lots of users.

<form action="massmail_execute.php">
<input type="radio" name="destination" value="comm" />
Send only to users subscribed to "Additional Community Mailings" ('
.$count_comm
.')<br /><input type="radio" name="destination" value="sf" />
Send only to users that agreed to receive "Site Updates" ('
.$count_sf
.')<br /><input type="radio" name="destination" value="devel" />
Send only to project developers ('
.$count_devel
.')<br /><input type="radio" name="destination" value="admin" />
Send only to project administrators ('
.$count_admin
.')<br /><input type="radio" name="destination" value="sfadmin" />
Send only to <?php echo $GLOBALS['sys_name']; ?> administrators (test) ('
.$count_sfadmin
.')<br /><input type="radio" name="destination" value="all" />
Send to all users, regardless of their preferences ('
.$count_all
.')
<p><strong>Start With User ID:</strong> (for use when the process quits)
<br /><input type="text" name="first_user" value="0" /></p>
<p>
Subject:
<br /><input type="text" name="mail_subject" value="<?php echo $GLOBALS['sys_name']; ?>: " /></p>

<p>Text of Message:
<pre>
<br /><textarea name="mail_message" cols="70" rows="40" wrap="physical">

---------------------
This email was sent from '. $GLOBALS['sys_default_domain'] .'. To change your email receipt
preferences, please visit the site and edit your account via the
"Account Maintenance" link.

Please direct any questions to admin@'. $GLOBALS['sys_default_domain'].' .
</textarea>
</pre></p>
<p><input type="submit" name="Submit" value="Submit" /></p>

</form>
';

$HTML->footer(array());

?>

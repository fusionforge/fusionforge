<?php
/**
  *
  * Site Admin page for setting up massmailings.
  *
  * This is frontend of SF massmail facility, which allows to prepare
  * messages for delivery to target categories of site users. very
  * delivery is performed via cronjob.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

if ($submit) {

	if (!$mail_type) {
		exit_error(
			'Missing parameter',
			'You must select target audience for mailing'
		);
	}

	if (!trim($mail_message)) {
		exit_error(
			'Missing parameter',
			'You are trying to send empty message'
		);
	}

	if (trim($mail_subject) == '['.$GLOBALS['sys_name'].']') {
		exit_error(
			'Missing parameter',
			'You must give proper subject to the mailing'
		);
	}

	$res = db_query("
		INSERT INTO massmail_queue(type,subject,message,queued_date)
		VALUES ('$mail_type','$mail_subject','$mail_message',".time().")
	");

	if (!$res || db_affected_rows($res)<1) {
		exit_error(
			'Error Scheduling Mailing',
			'Could not schedule mailing, database error: '.db_error()
		);
	}

	site_admin_header(array('title'=>"Administrative Mass Mail Engine"));
	print "<p>Mailing successfully scheduled for delivery</p>";
	site_admin_footer(array());
	exit();
}

site_admin_header(array('title'=>"Administrative Mass Mail Engine"));

print '
<h4>
Mail Engine for '.$GLOBALS['sys_name'].' Subscribers
</h4>
';

print '
<p>
<a href="#active">Active deliveries</a>
</p>

<p>Be <span style="color:red"><strong>VERY</strong></span> careful with this form,
because submitting it WILL lead to sending email to lots of users.
</p>
';

print '
<form action="'.$PHP_SELF.'" method="post">'
.'<strong>Target Audience:</strong>'.utils_requiredField().'<br />'.html_build_select_box_from_arrays(
	array(0,'SITE','COMMUNTY','DVLPR','ADMIN','ALL','SFDVLPR'),
	array(
		'(select)',
		'Subscribers to "Site Updates"',
		'Subscribers to "Additional Community Mailings"',
		'All Project Developers',
		'All Project Admins',
		'All Users',
		$GLOBALS['sys_name'].' Developers (test)'
	),
	'mail_type',false,false
)
.'<br />';


print '

<p>
<strong>Subject:</strong>'.utils_requiredField().'
<br /><input type="text" name="mail_subject" size="50" value="['.$GLOBALS['sys_name'].'] " /></p>

<p><strong>Text of Message:</strong>'.utils_requiredField().' (will be appended with unsubscription
information, if applicable)
<pre><textarea name="mail_message" cols="70" rows="20" wrap="physical">
</textarea>
</pre></p>

<p><input type="submit" name="submit" value="Schedule for Mailing" /></p>

</form>
';


$res = db_query("
	SELECT *
	FROM massmail_queue
	WHERE finished_date=0
");

$title=array();
$title[]='&nbsp;';
$title[]='ID';
$title[]='Type';
$title[]='Subject';
$title[]='Date';
$title[]='Last user_id mailed';

print '<a name="active">Active Deliveries:</a>';

echo $GLOBALS['HTML']->listTableTop($title);

while ($row = db_fetch_array($res)) {
	echo '
	<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
	<td>&nbsp;<a href="massmail-del.php?id='.$row['id'].'"></a></td>
	<td>'.$row['id'].'</td>
	<td>'.$row['type'].'</td>
	<td>'.$row['subject'].'</td>
	<td>'.date($sys_datefmt, $row['queued_date']).'</td>
	<td> '.$row['last_userid'].'</td>
	</tr>
	';
}

echo $GLOBALS['HTML']->listTableBottom();

site_admin_footer(array());

?>

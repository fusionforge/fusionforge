<?php
/**
 * Site Admin page for setting up massmailings.
 *
 * This is frontend of SF massmail facility, which allows to prepare
 * messages for delivery to target categories of site users. very
 * delivery is performed via cronjob.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
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

require_once('../env.inc.php');
require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}
	$mail_type = getStringFromRequest('mail_type');
	$mail_message = getStringFromRequest('mail_message');
	$mail_subject = getStringFromRequest('mail_subject');

	if (!$mail_type) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Missing parameter, You must select target audience for mailing')
		);
	}

	if (!trim($mail_message)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Missing parameter, You are trying to send empty message')
		);
	}

	if (trim($mail_subject) == '['.$GLOBALS['sys_name'].']') {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Missing parameter, You must give proper subject to the mailing')
		);
	}

	$res = db_query("
		INSERT INTO massmail_queue(type,subject,message,queued_date)
		VALUES ('$mail_type','$mail_subject','$mail_message',".time().")
	");

	if (!$res || db_affected_rows($res)<1) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Error Scheduling Mailing, Could not schedule mailing, database error:') .db_error()
		);
	}

	site_admin_header(array('title'=>_('Massmail admin')));
	print "<p>" ._('Mailing successfully scheduled for delivery'). "</p>";
	site_admin_footer(array());
	exit();
}

site_admin_header(array('title'=>_('Massmail admin')));

print '
<h4>'
.sprintf(_('Mail Engine for %1$s Subscribers'), $GLOBALS['sys_name']) .
'</h4>
';

print '
<p>
<a href="#active">' ._('Active Deliveries').'</a>
</p>

<p>' ._('Be <span class="important">VERY</span> careful with this form, because submitting it WILL lead to sending email to lots of users.').
'</p>
';

print '
<form action="'.getStringFromServer('PHP_SELF').'" method="post">'
.'<input type="hidden" name="form_key" value="'.form_generate_key().'">'
.'<strong>Target Audience:</strong>'.utils_requiredField().'<br />'.html_build_select_box_from_arrays(
	array(0,'SITE','COMMNTY','DVLPR','ADMIN','ALL','SFDVLPR'),
	array(
		_('(select)'),
		_('Subscribers to "Site Updates"'),
		_('Subscribers to "Additional Community Mailings"'),
		_('All Project Developers'),
		_('All Project Admins'),
		_('All Users'),
		$GLOBALS['sys_name']. _('Developers (test)')
	),
	'mail_type',false,false
)
.'<br />';


print '

<p>
<strong>' ._('Subject').':</strong>'.utils_requiredField().'
<br /><input type="text" name="mail_subject" size="50" value="['.$GLOBALS['sys_name'].'] " /></p>

<p><strong>'._('Text of Message'). ':</strong>'.utils_requiredField(). _('(will be appended with unsubscription information, if applicable)').'</p>
<pre><textarea name="mail_message" cols="70" rows="20">
</textarea>
</pre>

<p><input type="submit" name="submit" value="' ._('Schedule for Mailing').'" /></p>

</form>
';


$res = db_query("
	SELECT *
	FROM massmail_queue
	WHERE finished_date=0
");

$title=array();
$title[]='&nbsp;';
$title[]=_('ID');
$title[]=_('Type');
$title[]=_('Subject');
$title[]=_('Date');
$title[]=_('Last user_id mailed');

print '<a name="active">'._('Active Deliveries').':</a>';

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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

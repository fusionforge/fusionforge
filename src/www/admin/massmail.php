<?php
/**
 * Site Admin page for setting up massmailings.
 *
 * This is frontend of SF massmail facility, which allows to prepare
 * messages for delivery to target categories of site users. very
 * delivery is performed via cronjob.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

session_require_global_perm ('forge_admin');

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('admin');
	}
	$mail_type = getStringFromRequest('mail_type');
	$mail_message = getStringFromRequest('mail_message');
	$mail_subject = getStringFromRequest('mail_subject');

	if (!$mail_type) {
		form_release_key(getStringFromRequest('form_key'));
		exit_missing_param('',array(_('Target Audience')),'admin');
	}

	if (!trim($mail_message)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_missing_param('',array(_('No Message')),'admin');
	}

	if (trim($mail_subject) == '['.forge_get_config ('forge_name').']') {
		form_release_key(getStringFromRequest('form_key'));
		exit_missing_param('',array(_('No Subject')),'admin');
	}

	$res = db_query_params ('
		INSERT INTO massmail_queue(type,subject,message,queued_date)
		VALUES ($1,$2,$3,$4)
	',
			array($mail_type,
				$mail_subject,
				$mail_message,
				time()));

	if (!$res || db_affected_rows($res)<1) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Scheduling Mailing, Could not schedule mailing, database error: ').db_error(),'admin');
	}

	$title = _('Massmail admin');
	site_admin_header(array('title'=>$title));
	print "<p class=\"feedback\">" ._('Mailing successfully scheduled for delivery'). "</p>";
	site_admin_footer(array());
	exit();
}

$title = sprintf(_('Mail Engine for %1$s Subscribers'), forge_get_config ('forge_name'));
site_admin_header(array('title'=>$title));

print '
<p>
<a href="#active">' ._('Active Deliveries').'</a>
</p>

<p>' ._('Be <span class="important">VERY</span> careful with this form, because submitting it WILL lead to sending email to lots of users.').
'</p>
';

print '
<form action="'.getStringFromServer('PHP_SELF').'" method="post">'
.'<input type="hidden" name="form_key" value="'.form_generate_key().'" />'
.'<strong>Target Audience:</strong>'.utils_requiredField().'<br />'.html_build_select_box_from_arrays(
	array(0,'SITE','COMMNTY','DVLPR','ADMIN','ALL','SFDVLPR'),
	array(
		_('(select)'),
		_('Subscribers to "Site Updates"'),
		_('Subscribers to "Additional Community Mailings"'),
		_('All Project Developers'),
		_('All Project Admins'),
		_('All Users'),
		forge_get_config ('forge_name'). _('Developers (test)')
	),
	'mail_type',false,false
)
.'<br />';


print '

<p>
<strong>' ._('Subject').':</strong>'.utils_requiredField().'
<br /><input type="text" name="mail_subject" size="50" value="['.forge_get_config ('forge_name').'] " /></p>

<p><strong>'._('Text of Message'). ':</strong>'.utils_requiredField(). _('(will be appended with unsubscription information, if applicable)').'</p>
<pre><textarea name="mail_message" cols="70" rows="20">
</textarea>
</pre>

<p><input type="submit" name="submit" value="' ._('Schedule for Mailing').'" /></p>

</form>
';


$res = db_query_params ('
	SELECT *
	FROM massmail_queue
	WHERE finished_date=0
',
			array()) ;

$title=array();
$title[]='&nbsp;';
$title[]=_('ID');
$title[]=_('Type');
$title[]=_('Subject');
$title[]=_('Date');
$title[]=_('Last user_id mailed');

print '<a name="active">'._('Active Deliveries').':</a>';

$seen = false;

$i = 0;
while ($row = db_fetch_array($res)) {
	if (!$seen) {
		echo $GLOBALS['HTML']->listTableTop($title);
		$seen = true;
	}
	echo '
	<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
	<td>&nbsp;<a href="massmail-del.php?id='.$row['id'].'"></a></td>
	<td>'.$row['id'].'</td>
	<td>'.$row['type'].'</td>
	<td>'.$row['subject'].'</td>
	<td>'.date(_('Y-m-d H:i'), $row['queued_date']).'</td>
	<td> '.$row['last_userid'].'</td>
	</tr>
	';
}

if ($seen) {
       echo $GLOBALS['HTML']->listTableBottom();
} else {
       echo '<p>' . _('No deliveries active.') . "</p>\n";
}

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

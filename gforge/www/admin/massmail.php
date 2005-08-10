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

require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

if (getStringFromRequest('submit')) {
	$mail_type = getStringFromRequest('mail_type');
	$mail_message = getStringFromRequest('mail_message');
	$mail_subject = getStringFromRequest('mail_subject');

	if (!$mail_type) {
		exit_error(
			$Language->getText('admin_massmail','missing_parameter_select_target')
		);
	}

	if (!trim($mail_message)) {
		exit_error(
			$Language->getText('admin_massmail','missing_parameter_empty_message')
		);
	}

	if (trim($mail_subject) == '['.$GLOBALS['sys_name'].']') {
		exit_error(
			$Language->getText('admin_massmail','missing_parameter_proper_subject')
		);
	}

	$res = db_query("
		INSERT INTO massmail_queue(type,subject,message,queued_date)
		VALUES ('$mail_type','$mail_subject','$mail_message',".time().")
	");

	if (!$res || db_affected_rows($res)<1) {
		exit_error(
			$Language->getText('admin_massmail','error_scheduling_mailing') .db_error()
		);
	}

	site_admin_header(array('title'=>$Language->getText('admin_massmail','title')));
	print "<p>" .$Language->getText('admin_massmail','mailing_successfully_scheduled'). "</p>";
	site_admin_footer(array());
	exit();
}

site_admin_header(array('title'=>$Language->getText('admin_massmail','title')));

print '
<h4>'
.$Language->getText('admin_massmail','mail_engine_for',array($GLOBALS['sys_name'])) .
'</h4>
';

print '
<p>
<a href="#active">' .$Language->getText('admin_massmail','active_deliveries').'</a>
</p>

<p>' .$Language->getText('admin_massmail','be_verry_carefull').
'</p>
';

print '
<form action="'.getStringFromServer('PHP_SELF').'" method="post">'
.'<strong>Target Audience:</strong>'.utils_requiredField().'<br />'.html_build_select_box_from_arrays(
	array(0,'SITE','COMMNTY','DVLPR','ADMIN','ALL','SFDVLPR'),
	array(
		$Language->getText('admin_massmail','select'),
		$Language->getText('admin_massmail','subscribers_to_site_updates'),
		$Language->getText('admin_massmail','subscribers_to_additional_community'),
		$Language->getText('admin_massmail','all_project_developers'),
		$Language->getText('admin_massmail','all_project_admins'),
		$Language->getText('admin_massmail','all_users'),
		$GLOBALS['sys_name']. $Language->getText('admin_massmail','developers_test')
	),
	'mail_type',false,false
)
.'<br />';


print '

<p>
<strong>' .$Language->getText('admin_massmail','subject').':</strong>'.utils_requiredField().'
<br /><input type="text" name="mail_subject" size="50" value="['.$GLOBALS['sys_name'].'] " /></p>

<p><strong>'.$Language->getText('admin_massmail','text_of_message'). ':</strong>'.utils_requiredField(). $Language->getText('admin_massmail','will_be_append').'</p>
<pre><textarea name="mail_message" cols="70" rows="20">
</textarea>
</pre>

<p><input type="submit" name="submit" value="' .$Language->getText('admin_massmail','schedule_for_mailing').'" /></p>

</form>
';


$res = db_query("
	SELECT *
	FROM massmail_queue
	WHERE finished_date=0
");

$title=array();
$title[]='&nbsp;';
$title[]=$Language->getText('admin_massmail','id');
$title[]=$Language->getText('admin_massmail','type');
$title[]=$Language->getText('admin_massmail','subject');
$title[]=$Language->getText('admin_massmail','date');
$title[]=$Language->getText('admin_massmail','last_user_id_mailed');

print '<a name="active">'.$Language->getText('admin_massmail','active_deliveries').':</a>';

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

<?php
/**
 * Site Admin page to edit canned responses for project rejection
 *
 * This page is linked from approve-pending.php
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';
require_once $gfwww.'include/canned_responses.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

global $HTML;

site_admin_header(array('title'=>_('Site Admin').(': ')._('Edit Rejection Responses')));

function check_select_value($value) {
	if( $value == "100" ) {
		print('<span class="important">'.sprintf(_('You cannot %s “None”!'), $GLOBALS['action'])."</span><br />\n");
	}
}

echo $HTML->openForm(array('method' => 'post', 'action' => '/admin/responses_admin.php'));
echo _('Existing Responses')._(': '); ?><?php echo get_canned_responses(); ?>
<!-- Reinhard Spisser: commenting localization, since otherwise it will not work -->
<!--
<input name="action" type="submit" value="<?php echo _('Edit'); ?>" />
<input name="action" type="submit" value="<?php echo _('Delete'); ?>" />
-->
<input name="action" type="submit" value="Edit" />
<input name="action" type="submit" value="Delete" />
<input type="checkbox" name="sure" value="<?php echo _('Yes'); ?>" />
<?php echo _('I am Sure');
echo $HTML->closeForm();

echo html_e('br');

$action = getStringFromRequest('action');

if( $action == "Edit" ) {
	$response_id = getIntFromRequest('response_id');
	$action2 = getStringFromRequest('action2');
	$response_title = getStringFromRequest('response_title');
	$response_text = getStringFromRequest('response_text');

	// Edit Response
	check_select_value($response_id);
	if( $action2 ) {
		db_query_params ('UPDATE canned_responses SET response_title=$1, response_text=$2 WHERE response_id=$3',
			array($response_title,
			$response_text,
			$response_id)) ;

		print(" <strong>" ._('Edited Response')."</strong> ");
	} else {
		$res = db_query_params ('SELECT * FROM canned_responses WHERE response_id=$1',
			array($response_id)) ;

		$row = db_fetch_array($res);
		$response_title=$row[1];
		$response_text=$row[2];
	echo _('Edit Response')._(':').html_e('br');
	echo $HTML->openForm(array('method' => 'post', 'action' => '/admin/responses_admin.php'));
	echo _('Response Title').(':'); ?><input type="text" name="response_title" size="30" maxlength="25" value="<?php echo $response_title; ?>" /><br />
<?php echo _('Response Text')._(':'); ?><br />
<textarea name="response_text" cols="50" rows="10"><?php echo $response_text; ?></textarea>
<input type="hidden" name="response_id" value="<?php echo $response_id; ?>" />
<input type="hidden" name="action2" value="<?php echo _('Go'); ?>" />
<input type="hidden" name="action" value="Edit">
<input type="submit" name="actionsubmit" value="<?php echo _('Edit'); ?>" />
<?php
	echo $HTML->closeForm();
	}

} elseif ( $action == "Delete" ) {
	$response_id = getIntFromRequest('response_id');
	$sure = getStringFromRequest('sure');

	// Delete Response
	check_select_value($response_id);
	if( $sure == "yes" ) {
		db_query_params ('DELETE FROM canned_responses WHERE response_id=$1',
			array($response_id)) ;

		print(" <strong>" ._('Deleted Response')."</strong> ");
	} else {
		print( _('If you are not sure then why did you click “Delete”?')."<br />");
		print("<em>" ._('By the way, I didn\'t delete... just in case...')."</em><br />\n");
	}

} elseif ( $action == "Create" ) {
	$response_title = getStringFromRequest('response_title');
	$response_text = getStringFromRequest('response_text');

	// New Response
	add_canned_response($response_title, $response_text);
	print(" <strong>" ._('Added Response')."</strong> ");

} else {

	echo _('Create New Response')._(':').html_e('br');
	echo $HTML->openForm(array('method' => 'post', 'action' => '/admin/responses_admin.php'));
	echo _('Response Title')._(':'); ?><input type="text" name="response_title" size="30" maxlength="25" /><br />
<?php echo _('Response Text')._(':'); ?><br />
<textarea name="response_text" cols="50" rows="10"></textarea>
<br />
<input type="hidden" name="action" value="Create" />
<input type="submit" name="actions" value="<?php echo _('Create'); ?>" />

<?php
echo $HTML->closeForm();
}

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

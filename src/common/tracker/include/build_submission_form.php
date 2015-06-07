<?php
/**
 * Generic Tracker facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems; 2005 GForge, LLC
 * Copyright 2012,2015, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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
require_once 'note.php';
function artifact_submission_form($ath, $group) {
	global $HTML;
	/*
		Show the free-form text submitted by the project admin
	*/
	echo notepad_func();
	echo $ath->renderSubmitInstructions();
	echo $HTML->openForm(array('id' => 'trackeraddform', 'action' => '/tracker/?group_id='.$group->getID().'&atid='.$ath->getID(), 'method' => 'post', 'enctype' => 'multipart/form-data'));
	?>
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
	<input type="hidden" name="func" value="postadd" />
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
	<table>

	<tr>
		<td class="top">
<?php
	if (!session_loggedin()) {
		echo '<div class="login_warning_msg">';
		echo $HTML->warning_msg(_('Please').' '.util_make_link('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI')), _('login')));
		echo _('If you <strong>cannot</strong> login, then enter your email address here')._(':').'<p>
		<input type="text" name="user_email" size="50" maxlength="255" /></p>
		</div>';
	}
?>
		</td>
	</tr>
	<tr>
		<td class="top"><strong><?php echo _('For project')._(':'); ?></strong><br /><?php echo $group->getPublicName(); ?></td>
		<td class="top"><input type="submit" name="submit" value="<?php echo _('Submit'); ?>" /></td>
	</tr>

<?php
	$ath->renderExtraFields(array(),true,'none',false,'Any',array(),false,'UPDATE');

	if (forge_check_perm ('tracker', $ath->getID(), 'manager')) {
		echo '<tr>
		<td><strong>'._('Assigned to')._(':').'</strong><br />';
		echo $ath->technicianBox('assigned_to');
		echo '&nbsp;'.util_make_link('/tracker/admin/?group_id='.$group->getID().'&atid='.$ath->getID().'&update_users=1', '('._('Admin').')' );

		echo '</td><td><strong>'._('Priority')._(':').'</strong><br />';
		build_priority_select_box('priority');
		echo '</td></tr>';
	}
?>
	<tr>
		<td colspan="2"><strong><?php echo _('Summary').utils_requiredField()._(':'); ?></strong><br />
			<input id="tracker-summary" required="required" type="text" name="summary" size="80" maxlength="255" title="<?php echo util_html_secure(html_get_tooltip_description('summary')); ?>" />
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<strong><?php echo _('Detailed description').utils_requiredField()._(':'); ?></strong><?php notepad_button('document.forms.trackeraddform.details'); ?><br />
			<textarea id="tracker-description" required="required" name="details" rows="20" cols="79" title="<?php echo util_html_secure(html_get_tooltip_description('description')); ?> "></textarea>
		</td>
	</tr>

	<tr>
		<td colspan="2">
<?php
	if (!session_loggedin()) {
		echo '<div class="login_warning_msg">';
		echo $HTML->error_msg(_('Please').' '.util_make_link('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI')), _('login')));
		echo _('If you <strong>cannot</strong> login, then enter your email address here').':<p>
		<input type="text" name="user_email" size="30" maxlength="255" /></p>
		</div>';
	}
?>
		<p>&nbsp;</p>
		<span class="important"><?php echo _('DO NOT enter passwords or confidential information in your message!'); ?></span>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<div class="file_attachments">
		<p>
		<strong><?php echo _('Attach Files')._(':'); ?> </strong> <?php echo('('._('max upload size: '.human_readable_bytes(util_get_maxuploadfilesize())).')') ?><br />
		<input type="file" name="input_file0" /><br />
		<input type="file" name="input_file1" /><br />
		<input type="file" name="input_file2" /><br />
		<input type="file" name="input_file3" /><br />
		<input type="file" name="input_file4" />
		</p>
		</div>
		</td>
	</tr>

	<tr><td colspan="2">
		<input type="submit" name="submit" value="<?php echo _('Submit'); ?>" />
		</td>
	</tr>

	</table>
<?php
	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
}

<?php
/**
 * Generic Tracker facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems; 2005 GForge, LLC
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


$ath->header(array ('title'=>_('Submit')));

	/*
		Show the free-form text submitted by the project admin
	*/
	echo notepad_func();
	echo $ath->renderSubmitInstructions();

	echo '<form id="trackeraddform" action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID().'" method="post" enctype="multipart/form-data">
	
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
	<table>';
	echo '
	<tr>
		<td valign="top">
	            <input type="hidden" name="form_key" value="'.form_generate_key().'" />
	            <input type="hidden" name="func" value="postadd" />
	            <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />';
	if (!session_loggedin()) {
		echo '
		<span class="error">'.sprintf(_('Please %1$s login %2$s'), '<a href="'.util_make_url ('/account/login.php?return_to='.urlencode($REQUEST_URI)).'">', '</a>').'</span><<br />
		'._('If you <strong>cannot</strong> login, then enter your email address here').':<p>
		<input type="text" name="user_email" size="50" maxlength="255" /></p>
		';
	} 
	echo '
		</td>
	</tr>
	<tr>
		<td valign="top"><strong>'._('For project').'</strong><br />'.$group->getPublicName().'</td>
		<td valign="top"><input type="submit" name="submit" value="'. _('Submit').'" /></td>
	</tr>';
	
	$ath->renderExtraFields(array(),true,'none',false,'Any','',false,'UPDATE');

	if (forge_check_perm ('tracker', $ath->getID(), 'manager')) {
		echo '<tr>
		<td><strong>'._('Assigned to').': <a href="javascript:help_window(\''.util_make_url ('/help/tracker.php?helpname=assignee').'\')"><strong>(?)</strong></a></strong><br />';
		echo $ath->technicianBox ('assigned_to');
		echo '&nbsp;'.util_make_link ('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_users=1', '('._('Admin').')' );

		echo '</td><td><strong>'._('Priority').': <a href="javascript:help_window(\''.util_make_url ('/help/tracker.php?helpname=priority').'\')"><strong>(?)</strong></a></strong><br />';
		echo build_priority_select_box('priority');
		echo '</td></tr>';
	}
	
	?>
	<tr>
		<td colspan="2"><strong><?php echo _('Summary') ?><?php echo utils_requiredField(); ?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=summary'); ?>')">(?)</a></strong><br />
		<input type="text" name="summary" size="80" maxlength="255" />
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('Detailed description') ?><?php echo utils_requiredField(); ?>:</strong><?php echo notepad_button('document.forms.trackeraddform.details') ?><br /> 
		<textarea name="details" rows="20" cols="79"></textarea>
		</td>
	</tr>

	<tr>
		<td colspan="2">
	<?php 
	if (!session_loggedin()) {
		echo '
		<span class="error">'.sprintf(_('Please %1$s login %2$s'), '<a href="'.util_make_url ('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI'))).'">', '</a>').'</span><br />
		'._('If you <strong>cannot</strong> login, then enter your email address here').':<p>
		<input type="text" name="user_email" size="30" maxlength="255" /></p>
		';

	} 
	?>
		<p>&nbsp;</p>
		<span class="veryimportant"><?php echo _('DO NOT enter passwords or confidential information in your message!') ?></span>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=attach_file'); ?>')"><strong>(?)</strong></a><br />
		<p>
		<strong><?php echo _('Attach Files') ?>:</strong><br />
		<input type="file" name="input_file0" size="30" /><br />
		<input type="file" name="input_file1" size="30" /><br />
		<input type="file" name="input_file2" size="30" /><br />
		<input type="file" name="input_file3" size="30" /><br />
		<input type="file" name="input_file4" size="30" /><br />
		</p>
		</td>
	</tr>

	<tr><td colspan="2">
		<input type="submit" name="submit" value="<?php echo _('Submit')?>" />
		</td>
	</tr>

	<tr><td colspan="2"><br/><?php echo utils_requiredField(); ?> <?php echo _('Indicates required fields.') ?></td></tr>
	</table></form>

	<?php

	$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

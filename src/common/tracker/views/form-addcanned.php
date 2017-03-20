<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2015, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

global $HTML;

//
//  FORM TO ADD CANNED RESPONSES
//
$title = sprintf(_('Manage Canned Responses to %s'), $ath->getName());
$ath->adminHeader(array('title'=>$title, 'modal'=>1));

/*
	List of existing canned responses
*/
$result=$ath->getCannedResponses();
$rows=db_numrows($result);

if ($result && $rows > 0) {
	//code to show existing responses and link to update page
	echo '<h2>'._('Existing Responses').'</h2>';
	$title_arr=array();
	$title_arr[]=_('Id');
	$title_arr[]=_('Title');
	$title_arr[]=_('Operation');

	echo $HTML->listTableTop($title_arr);

	for ($i=0; $i < $rows; $i++) {
		$id = db_result($result, $i, 'id');
		echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>'.
			'<td>'.$id.'</td>'.
			'<td>'.util_make_link('/tracker/admin/?update_canned=1&id='.$id.'&group_id='.$group_id.'&atid='.$ath->getID(), db_result($result, $i, 'title')).'</td>
			<td>'.util_make_link('/tracker/admin/?delete_canned=1&id='.$id.'&group_id='.$group_id.'&atid='. $ath->getID(), _('Delete')).'</td></tr>';
	}

	echo $HTML->listTableBottom();

} else {
	echo $HTML->information(_('No Canned Responses set up in this Project'));
}

echo '<h2>'._('Add New Canned Response').'</h2>';
?>
<p><?php echo _('Creating useful generic messages can save you a lot of time when handling common artifact requests.') ?></p>
<?php
echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&add_canned=1', 'method' => 'post'));
?>
<input type="hidden" name="add_canned" value="y" />
<label for="title">
<strong><?php echo _('Title').utils_requiredField()._(':') ?></strong><br />
</label>
<input id="title" type="text" name="title" required="required" value="" size="80" maxlength="80" />
<p>
<label for="body">
<strong><?php echo _('Message Body').utils_requiredField()._(':') ?></strong><br />
</label>
<textarea id="body" name="body" required="required" rows="15" cols="80"></textarea></p>
<p>
<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
<?php
echo $HTML->closeForm();
$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

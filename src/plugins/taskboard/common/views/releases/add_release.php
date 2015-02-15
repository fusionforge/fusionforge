<?php
/**
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $group_id, $group, $taskboard, $pluginTaskboard, $HTML;

session_require_perm('tracker_admin', $group_id);

$taskboard->header(
		array(
			'title' => _('Taskboard for ').$group->getPublicName()._(': '). _('Releases')._(': ')._('Add release'),
			'pagename' => _('Releases')._(': ')._('Add release'),
			'sectionvals' => array($group->getPublicName()),
			'group' => $group_id
		)
	);

echo '<link rel="stylesheet" type="text/css" href="/plugins/taskboard/css/agile-board.css">';
html_use_jqueryui();

if ( $taskboard->isError() ) {
	echo '<div id="messages" class="error">'.$taskboard->getErrorMessage().'</div>';
} else {
	echo '<div id="messages" style="display: none;"></div>';
}

// prepare list of unused releases
$used_release_elements = array();
$taskboard_releases = $taskboard->getReleases();
foreach ($taskboard_releases as $release ) {
	$used_release_elements[] = $release->getElementID();
}

$release_values = $taskboard->getReleaseValues();

$release_id_arr = array();
$release_name_arr = array();
foreach ( $release_values as $release_name => $release_id ) {
	// show only unused releases
	if ( !in_array($release_id, $used_release_elements ) ) {
		$release_id_arr[] = $release_id;
		$release_name_arr[] = $release_name;
	}
}

$release_box=html_build_select_box_from_arrays ($release_id_arr,$release_name_arr,'_release',$element_id,false);
echo $HTML->openForm(array('action' => '/plugins/'.$pluginTaskboard->name.'/releases/?group_id='.$group_id.'&action=add_release', 'method' => 'post'));
?>

<input type="hidden" name="post_changes" value="y">

<h2><?php echo _('Add release')?>:</h2>
<table>
	<tr><td><strong><?php echo _('Release') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><?php echo $release_box; ?></td></tr>
	<tr><td><strong><?php echo _('Start date') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><input type="text" name="start_date" value="<?php echo $start_date ?>"></td></tr>
	<tr><td><strong><?php echo _('End date') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><input type="text" name="end_date" value="<?php echo $end_date ?>"></td></tr>
	<tr><td><strong><?php echo _('Goals') ?></strong></td><td><textarea name="goals" cols="79" rows="5" ><?php echo htmlspecialchars($goals) ?></textarea></td></tr>
	<tr><td><strong><?php echo _('Page URL') ?></strong></td><td><input type="text" name="page_url" value="<?php echo htmlspecialchars($page_url) ?>"></td></tr>
</table>

<p>
<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
</p>

<?php
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
?>

<script>
jQuery( document ).ready(function( $ ) {
	$( "input[name='start_date'], input[name='end_date']" ).datepicker( {  "dateFormat" : "yy-mm-dd" });
});
</script>

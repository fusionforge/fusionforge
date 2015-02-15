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

session_require_perm('tracker_admin', $group_id) ;

$release_id = getStringFromRequest('release_id','');
$release = new TaskBoardRelease( $taskboard, $release_id );

$taskboard->header(
	array(
		'title'=>'Taskboard for '.$group->getPublicName().' : '._('Release').' : '._('Delete release') ,
		'pagename'=>_('Release').' : '._('Delete release'),
		'sectionvals'=>array(group_getname($group_id)),
		'group'=>$group_id
	)
);

if( $taskboard->isError() ) {
	echo '<div id="messages" class="error">'.$taskboard->getErrorMessage().'</div>';
} else {
	echo '<div id="messages" style="display: none;"></div>';
}

?>
	<form action="<?php echo util_make_url ('/plugins/taskboard/releases/?group_id='.$group_id.'&amp;action=delete_release') ?>" method="post">
	<input type="hidden" name="release_id" value="<?php echo $release_id ?>">

	<h1><?php echo _('Release') ." '".$release->getTitle() ."'"; ?></h1>
	<div>
	<?php echo _('You are about to permanently and irretrievably delete this release with all indicators! ') ?>
	</div>
	<div>
	<input type="checkbox" value="y" name="confirmed"> <?php echo _("I'm Sure") ?>
	</div>

	<p>
	<input type="submit" name="post_delete" value="<?php echo _('Delete') ?>">
	</p>
	</form>

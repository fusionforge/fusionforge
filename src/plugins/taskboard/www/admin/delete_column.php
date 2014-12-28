<?php

/*
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

$column_id = getStringFromRequest('column_id','');
$confirmed = getStringFromRequest('confirmed','');
$column = &taskboard_column_get_object($column_id);

if( $confirmed ) {
	db_begin();
	if( $column->delete() ) {
		db_commit();
		$feedback.=_('Successfully Removed');
	} else {
		db_rollback();
	}

	$action = 'columns';
	include( $gfplugins.'taskboard/www/admin/columns.php' );
} else {
	$taskboard->header(
		array(
			'title'=>'Taskboard for '.$group->getPublicName().' : Administration : Column configuration' ,
			'pagename'=>_('Column configuration'),
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
	<form action="/plugins/taskboard/admin/?group_id=<?php echo $group_id ?>&amp;action=delete_column" method="post">
	<input type="hidden" name="column_id" value="<?php echo $column_id ?>">

	<h1><?php echo _('Column') ." '".$column->getTitle() ."'"; ?></h1>
	<div>
	<?php echo _('You are about to permanently and irretrievably delete this column! ') ?>
	</div>
	<div>
	<input type="checkbox" value="y" name="confirmed"> <?php echo _("I'm Sure") ?>
	</div>

	<p>
	<input type="submit" name="post_delete" value="<?php echo _('Delete') ?>">
	</p>

<?php } ?>

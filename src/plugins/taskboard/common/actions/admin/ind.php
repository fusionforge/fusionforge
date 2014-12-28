<?php
/**
 * Tracker Links
 *
 * Copyright 2010, FusionForge Team
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

//
//  SHOW LINKS TO FUNCTIONS
//

$taskboard->header(
	array(
		'title'=>'Taskboard for '.$group->getPublicName().' : Administration' ,
		'pagename'=>_('Administration'),
		'sectionvals'=>array(group_getname($group_id)),
		'group'=>$group_id
	)
);

if( $taskboard->getID() ) {
	echo '<p>' . util_make_link ('/plugins/taskboard/admin/?group_id='.$group_id.'&amp;action=trackers',
			     '<strong>'._('Configure Trackers').'</strong>').'<br />' ;
	echo _('Choose and configure trackers, used with taskboard.') ;
	echo '</p>';

	echo '<p>' . util_make_link ('/plugins/taskboard/admin/?group_id='.$group_id.'&amp;action=columns',
		'<strong>'._('Configure Columns').'</strong>').'<br />' ;
	echo _('Configure taskboard columns.') ;
	echo '</p>';
} else {
	echo '<p>' . util_make_link ('/plugins/taskboard/admin/?group_id='.$group_id.'&amp;action=init',
		'<strong>'._('Initialize taskboard').'</strong>').'<br />' ;
	echo _('Create initial taskboard configuration') ;
	echo '</p>';

}

?>

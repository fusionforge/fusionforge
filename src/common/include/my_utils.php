<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Originally written by Laurent Julliard 2001, 2002, Codendi Team, Xerox
 * http://www.codendi.com
 *
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

/**
 * my_hide_url() - Generate hide/show urls to expand/collapse sections of the personal page
 *
 * @param	string	$svc		service name to hide/show (sr, bug, pm...)
 * @param	int	$db_item_id	the item (group, forum, task sub-project,...) from the
 *					database that we are currently processing and about to display
 * @param	int	$item_id	the item_id as given in the URL and on which the show/hide switch
 *					is going to apply
 * @param	int	$count
 * @param	bool	$hide		hide param as given in the script URL (-1 means no param was given)
 * @return	array
 *  $hide_url: URL to use in the page to switch from hide to show or vice versa
 *  $count_diff: difference between the number of items in the list between now and
 *     the previous last time the section was open (can be negative if items were removed)
 *  $hide_flag: true if the section must be hidden, false otherwise
 */

function my_hide_url($svc, $db_item_id, $item_id, $count, $hide) {

	$pref_name = 'my_hide_'.$svc.$db_item_id;
	$old_hide = $old_count = $old_pref_value = UserManager::instance()->getCurrentUser()->getPreference($pref_name);
	if ($old_pref_value) {
		list($old_hide,$old_count) = explode('|', $old_pref_value);
	}

	// Make sure they are both 0 if never set before
	if ($old_count == false) {
		$old_count = 0;
	}
	if ($old_hide == false) {
		$old_hide = 0;
	}
	if ($item_id == $db_item_id) {
		if (isset($hide)) {
			$pref_value = "$hide|$count";
		} else {
			$pref_value = "$old_hide|$count";
			$hide = $old_hide;
		}
	} else {
		if ($old_hide) {
			// if items are hidden keep the old count and keep pref as is
			$pref_value = $old_pref_value;
		} else {
			// only update the item count if the items are visible
			// if they are hidden keep reporting the old count
			$pref_value = "$old_hide|$count";
		}
		$hide = $old_hide;
	}

	// Update pref value if needed
	if ($old_pref_value != $pref_value) {
		UserManager::instance()->getCurrentUser()->setPreference($pref_name, $pref_value);
	}

	if ($hide) {
		$hide_url= util_make_link('/my/?hide_'.$svc.'=0&hide_item_id='.$db_item_id, html_image('pointer_right.png', 16, 16, array('title' => _('Expand'), 'alt' => _('Expand')))).' ';
		$hide_now = true;
	} else {
		$hide_url= util_make_link('/my/?hide_'.$svc.'=1&hide_item_id='.$db_item_id, html_image('pointer_down.png', 16, 16, array('title' => _('Collapse'), 'alt' => _('Collapse')))).' ';
		$hide_now = false;
	}

	return array($hide_now, $count-$old_count, $hide_url);
}

function my_hide($svc, $db_item_id, $item_id, $hide) {
	$pref_name = 'my_hide_'.$svc.$db_item_id;
	$old_pref_value = UserManager::instance()->getCurrentUser()->getPreference($pref_name);
	if ($old_pref_value) {
		list($old_hide, $old_count) = explode('|', $old_pref_value);
	}
	if (!isset($old_hide)) {
		$old_hide = false;
	}
	// Make sure they are both 0 if never set before
	if ($old_hide == false) {
		$old_hide = 0;
	}
	if ($item_id == $db_item_id) {
		if (!isset($hide)) {
			$hide = $old_hide;
		}
	} else {
		$hide = $old_hide;
	}
	return $hide;
}

function my_format_as_flag($assigned_to, $submitted_by, $multi_assigned_to=null) {
	$AS_flag = '';
	if ($assigned_to == user_getid()) {
		$AS_flag = 'A';
	} elseif ($multi_assigned_to) {
		// For multiple assigned to
		for ($i=0; $i<count($multi_assigned_to); $i++) {
			if ($multi_assigned_to[$i]==user_getid()) {
				$AS_flag = 'A';
			}
		}
	}
	if ($submitted_by == user_getid()) {
		$AS_flag .= 'S';
	}
	if ($AS_flag) {
		$AS_flag = '[<b>'.$AS_flag.'</b>]';
	}
	return $AS_flag;
}

/* second case */
function my_format_as_flag2($assignee, $submitter) {
	$AS_flag = '';
	if ($assignee) {
		$AS_flag = 'A';
	}
	if ($submitter) {
		$AS_flag .= 'S';
	}
	if ($AS_flag != '') {
		$AS_flag = '[<b>'.$AS_flag.'</b>]';
	}
	return $AS_flag;
}

function my_item_count($total, $new) {
	return '['.$total.($new ? ", <b>".sprintf(ngettext('%d new item', '%d new items', $new), $new)."</b>]" : ']');
}

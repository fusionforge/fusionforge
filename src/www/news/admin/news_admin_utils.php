<?php
/**
 * News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013,2015 Franck Villaume - TrivialDev
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

function show_news_approve_form($qpa_pending, $qpa_rejected, $qpa_approved, $form_url) {
	/*
		Show list of waiting news items
	*/

	global $HTML;

	// function to show single news item
	// factored out because called 3 time below
	function show_news_item($row, $i, $approved, $selectable, $form_url) {
		global $HTML;

		echo '<tr><td>';
		if ($selectable) {
			echo '<input type="checkbox" '
			.'name="news_id[]" value="'
			.$row['id'].'" />';
		}
		echo date(_('Y-m-d'), $row['post_date']).'</td>
		<td style="width: 45%">';
		echo util_make_link($form_url.'?approve=1&id='.$row['id'], $row['summary']);
		echo '</td>

		<td class="onethirdwidth">'
		.util_make_link_g ($row['unix_group_name'], $row['group_id'], $row['group_name'].' ('.$row['unix_group_name'].')')
		.'</td>
		</tr>'
		;
	}

	$title_arr = array(
		_('Date'),
		_('Subject'),
		_('Project')
	);

	$ra = RoleAnonymous::getInstance();

	$result = db_query_qpa($qpa_pending);
	$items = array();
	while ($row_item = db_fetch_array($result)) {
		if ($ra->hasPermission('project_read', $row_item['group_id'])) {
			$items[] = $row_item;
		}
	}
	$rows = count($items);

	if ($rows < 1) {
		echo html_e('h2', array(), _('No pending items found.'));
	} else {
		echo $HTML->openForm(array('action' => '/news/admin/', 'method' => 'post'));
		echo '<input type="hidden" name="mass_reject" value="1" />';
		echo '<input type="hidden" name="post_changes" value="y" />';
		echo '<h2>'.sprintf(_('These items need to be approved (total: %d)'), $rows).'</h2>';
		echo $HTML->listTableTop($title_arr);
		for ($i=0; $i < $rows; $i++) {
			show_news_item($items[$i], $i, false,true, $form_url);
		}
		echo $HTML->listTableBottom();
		echo '<br /><input type="submit" name="submit" value="'._('Reject Selected').'" />';
		echo $HTML->closeForm();
	}

	/*
		Show list of rejected news items for this week
	*/

	$result = db_query_qpa($qpa_rejected);
	$items = array();
	while ($row_item = db_fetch_array($result)) {
		if ($ra->hasPermission('project_read', $row_item['group_id'])) {
			$items[] = $row_item;
		}
	}
	$rows = count($items);

	if ($rows < 1) {
		echo html_e('h2', array(), _('No rejected items found for this week.'));
	} else {
		echo '<h2>'.sprintf(_('These items were rejected this past week or were not intended for front page (total: %d).'), $rows).'</h2>';
		echo $HTML->listTableTop($title_arr);
		for ($i=0; $i<$rows; $i++) {
			show_news_item($items[$i], $i, false, false, $form_url);
		}
		echo $HTML->listTableBottom();
	}

	/*
		Show list of approved news items for this week
	*/

	$result = db_query_qpa($qpa_approved);
	$items = array();
	while ($row_item = db_fetch_array($result)) {
		if ($ra->hasPermission('project_read', $row_item['group_id'])) {
			$items[] = $row_item;
		}
	}
	$rows = count($items);
	if ($rows < 1) {
		echo html_e('h2', array(), _('No approved items found for this week.'));
	} else {
		echo '<h2>'.sprintf(_('These items were approved this past week (total: %d).'), $rows).'</h2>';
		echo $HTML->listTableTop($title_arr);
		for ($i=0; $i < $rows; $i++) {
			show_news_item($items[$i], $i, false, false, $form_url);
		}
		echo $HTML->listTableBottom();
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

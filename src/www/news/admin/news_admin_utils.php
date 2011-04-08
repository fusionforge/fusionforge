<?php
/**
 * News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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

function show_news_approve_form($qpa_pending, $qpa_rejected, $qpa_approved) {
        /*
       		Show list of waiting news items
       	*/

       	// function to show single news item
       	// factored out because called 3 time below
       	function show_news_item($row,$i,$approved,$selectable) {
	        global $HTML;

		echo '<tr '. $HTML->boxGetAltRowStyle($i) . '><td width="20%">';
       		if ($selectable) {
       			echo '<input type="checkbox" '
       			     .'name="news_id[]" value="'
       			     .$row['id'].'" />';
       		}
       		echo date(_('Y-m-d'), $row['post_date']).'</td>
       		<td width="45%">';
       		echo '
       		<a href="'.getStringFromServer('PHP_SELF').'?approve=1&amp;id='.$row['id'].'">'.$row['summary'].'</a>
       		</td>

       		<td width="35%">'
		.util_make_link_g ($row['unix_group_name'],$row['group_id'],$row['group_name'].' ('.$row['unix_group_name'].')')
       		.'</td>
       		</tr>'
       		;
       	}

       	$title_arr = array(
       		_('Date'),
       		_('Subject'),
       		_('Project')
       	);

	$ra = RoleAnonymous::getInstance() ;

       	$result = db_query_qpa($qpa_pending);
	$items = array();
	while ($row_item = db_fetch_array($result)) {
		if ($ra->hasPermission('project_read', $row_item['group_id'])) {
			$items[] = $row_item;
		}
	}
       	$rows = count($items);

       	echo '<form action="'. getStringFromServer('PHP_SELF') .'" method="post">';
       	echo '<input type="hidden" name="mass_reject" value="1" />';
       	echo '<input type="hidden" name="post_changes" value="y" />';

       	if ($rows < 1) {
       		echo '
       			<h2>'._('No Queued Items Found').'</h2>';
       	} else {
       		echo '<h2>'.sprintf(_('These items need to be approved (total: %1$s)'), $rows).'</h2>';
       		echo $GLOBALS['HTML']->listTableTop($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($items[$i],$i,false,true);
       		}
       		echo $GLOBALS['HTML']->listTableBottom();
       		echo '<br /><input type="submit" name="submit" value="'._('Reject Selected').'" />';
       	}
       	echo '</form>';

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
       		echo '
       			<h2>'._('No rejected items found for this week').'</h2>';
       	} else {
       		echo '<h2>'.sprintf(_('These items were rejected this past week or were not intended for front page (total: %1$s)'), $rows).'</h2>';
       		echo $GLOBALS['HTML']->listTableTop($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($items[$i],$i,false,true);
       		}
       		echo $GLOBALS['HTML']->listTableBottom();
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
       		echo '
       			<h2>'._('No approved items found for this week').'</h2>';
       	} else {
       		echo '<h2>'.sprintf(_('These items were approved this past week (total: %1$s)'), $rows).'</h2>';
       		echo $GLOBALS['HTML']->listTableTop($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($items[$i],$i,false,true);
       		}
       		echo $GLOBALS['HTML']->listTableBottom();
       	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

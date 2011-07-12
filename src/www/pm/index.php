<?php
/**
 * Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'pm/include/ProjectGroupHTML.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';

$group_id = getIntFromRequest('group_id');
if (!$group_id) {
	exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error($g->getErrorMessage(),'pm');
}

$pgf = new ProjectGroupFactory($g);
if (!$pgf || !is_object($pgf)) {
	exit_error(_('Could Not Get Factory'),'pm');
} elseif ($pgf->isError()) {
	exit_error($pgf->getErrorMessage(),'pm');
}

$pg_arr = $pgf->getProjectGroups();
if ($pg_arr && $pgf->isError()) {
	exit_error($pgf->getErrorMessage(),'pm');
}

pm_header(array('title'=>sprintf(_('Subprojects for %1$s'), $g->getPublicName())));

plugin_hook("blocks", "tasks index");

if (count($pg_arr) < 1 || $pg_arr == false) {
	echo '<p class="warning_msg">'._('No Subprojects Found').'</p>';
	echo '<p>'._('No subprojects have been set up, or you cannot view them.').'</p>';
	echo '<p class="important">'._('The Admin for this project will have to set up subprojects using the admin page.').'</p>';
} else {
	echo '
	<p>'._('Choose a Subproject and you can browse/edit/add tasks to it.').'</p>';

	/*
		Put the result set (list of projects for this group) into a column with folders
	*/

	$sortcol = util_ensure_value_in_set (getStringFromRequest ('sortcol'),
					     array ('project_id',
						    'project_description',
						    'project_name',
						    'open_count',
						    'total_count')) ;
	$sortorder = util_ensure_value_in_set (getStringFromRequest ('sortorder'),
					       array ('a',
						      'd')) ;

	function build_column_sort_header ($group_id, $title, $val) {
		global $sortcol, $sortorder ;

		if ($sortcol != $val) {
			return util_make_link ("/pm/?group_id=$group_id&amp;sortcol=$val",
					       $title) ;
		} elseif ($sortorder == 'a') {
			return util_make_link ("/pm/?group_id=$group_id&amp;sortcol=$val&amp;sortorder=d",
					       $title.' ▴') ;
		} else {
			return util_make_link ("/pm/?group_id=$group_id&amp;sortcol=$val&amp;sortorder=a",
					       $title.' ▾') ;
		}
	}

	$tablearr = array () ;
	$tablearr[] = build_column_sort_header ($group_id, _('ID'), 'project_id') ;
	$tablearr[] = build_column_sort_header ($group_id, _('Subproject Name'), 'project_name') ;
	$tablearr[] = build_column_sort_header ($group_id, _('Description'), 'project_description') ;
	$tablearr[] = build_column_sort_header ($group_id, _('Open'), 'open_count') ;
	$tablearr[] = build_column_sort_header ($group_id, _('Total'), 'total_count') ;
	echo $HTML->listTableTop($tablearr);

	function project_group_comparator ($a, $b) {
		global $sortcol, $sortorder ;

		switch ($sortcol) {
		case 'project_name':
			$sorttype = 'str' ;
			$va = $a->getName() ;
			$vb = $b->getName() ;
			break;
		case 'project_description':
			$sorttype = 'str' ;
			$va = $a->getDescription() ;
			$vb = $b->getDescription() ;
			break;
		case 'project_id':
			$sorttype = 'int' ;
			$va = $a->getID();
			$vb = $b->getID();
			break;
		case 'open_count':
			$sorttype = 'int' ;
			$va = $a->getOpenCount();
			$vb = $b->getOpenCount();
			break;
		case 'total_count':
			$sorttype = 'int' ;
			$va = $a->getTotalCount();
			$vb = $b->getTotalCount();
			break;
		default:
			return 0;
		}

		switch ($sorttype) {
		case 'str':
			$tmp = strcoll ($va, $vb) ;
			break ;
		case 'int':
			if ($va < $vb) {
				$tmp = -1 ;
			} elseif ($va > $vb) {
				$tmp = 1 ;
			} else {
				$tmp = 0 ;
			}
			break ;
		default:
			return 0;
		}

		if ($sortorder == 'd') {
			return -$tmp ;
		} else {
			return $tmp ;
		}
	}

	usort ($pg_arr, 'project_group_comparator') ;

	for ($j = 0; $j < count($pg_arr); $j++) {
		if (!is_object($pg_arr[$j])) {
			//just skip it
		} elseif ($pg_arr[$j]->isError()) {
			echo $pg_arr[$j]->getErrorMessage();
		} else {
		echo '
		<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td><a href="'.util_make_url ('/pm/task.php?group_project_id='. $pg_arr[$j]->getID().'&amp;group_id='.$group_id.'&amp;func=browse').'">' .
			html_image("ic/taskman20w.png","20","20") . ' &nbsp;'.$pg_arr[$j]->getID() .'</a></td>
			<td><a href="'.util_make_url ('/pm/task.php?group_project_id='. $pg_arr[$j]->getID().'&amp;group_id='.$group_id.'&amp;func=browse').'">' .
		$pg_arr[$j]->getName() .'</a></td>
			<td>'.$pg_arr[$j]->getDescription() .'</td>
			<td style="text-align:right">'. (int) $pg_arr[$j]->getOpenCount().'</td>
			<td style="text-align:right">'. (int) $pg_arr[$j]->getTotalCount().'</td>
		</tr>';
		}
	}
	echo $HTML->listTableBottom();

}

pm_footer(array());

?>

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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'pm/include/ProjectGroupHTML.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';

global $HTML;

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

html_use_tablesorter();
pm_header(array('title' => sprintf(_('Subprojects for %s'), $g->getPublicName())));

plugin_hook('blocks', 'tasks index');

if ($pg_arr == false || empty($pg_arr)) {
	echo $HTML->information(_('No Subprojects Found'));
	echo '<p>'._('No subprojects have been set up, or you cannot view them.').'</p>';
	echo '<p class="important">'._('The Admin for this project will have to set up subprojects using the admin page.').'</p>';
} else {
	echo '
	<p>'._('Choose a Subproject and you can browse/edit/add tasks to it.').'</p>';

	/*
		Put the result set (list of projects for this group) into a column with folders
	*/

	$tablearr = array();
	$tablearr[] = _('Subproject Name');
	$tablearr[] = _('Description');
	$tablearr[] = _('Open');
	$tablearr[] = _('Total');
	$thclass = array(array(), array(), array('class' => 'align-center'), array('class' => 'align-center'));

	echo $HTML->listTableTop($tablearr, array(), 'full sortable sortable_table_pm', 'sortable_table_pm', array(), array(), $thclass);

	for ($j = 0; $j < count($pg_arr); $j++) {
		if (!is_object($pg_arr[$j])) {
			//just skip it
		} elseif ($pg_arr[$j]->isError()) {
			echo $pg_arr[$j]->getErrorMessage();
		} else {
			$cells = array();
			$cells[][] = util_make_link('/pm/task.php?group_project_id='.$pg_arr[$j]->getID().'&group_id='.$group_id.'&func=browse', $HTML->getPmPic(). ' '.$pg_arr[$j]->getName());
			$cells[][] = $pg_arr[$j]->getDescription();
			$cells[] = array((int) $pg_arr[$j]->getOpenCount(), 'class' => 'align-center');
			$cells[] = array((int) $pg_arr[$j]->getTotalCount(), 'class' => 'align-center');
			echo $HTML->multiTableRow(array(), $cells);
		}
	}
	echo $HTML->listTableBottom();
}

pm_footer();

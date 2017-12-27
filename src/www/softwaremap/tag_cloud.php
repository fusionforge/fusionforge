<?php
/**
 * Copyright (C) 2008-2009 Alcatel-Lucent
 * Copyright 2015,2017, Franck Villaume - TrivialDev
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
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Tag Cloud ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/trove.php';
require_once $gfcommon.'include/tag_cloud.php';
require_once $gfwww.'people/people_utils.php';

if (!forge_get_config('use_project_tags')) {
	exit_disabled();
}

global $HTML;

$HTML->header(array('title'=>_('Tag Cloud'),'pagename'=>'softwaremap'));
$HTML->printSoftwareMapLinks();

$selected_tag = getStringFromRequest('tag');

echo tag_cloud(array('selected' => $selected_tag, 'nb_max' => 100)) . '<br /><br />';

if ($selected_tag) {
	$role_id = 1;

	if (session_loggedin()) {
		if (getStringFromRequest('setpaging')) {
			/* store paging preferences */
			$paging = getIntFromRequest('nres');
			if (!$paging) {
				$paging = 25;
			}
			$LUSER->setPreference('paging', $paging);
		}
		/* logged in users get configurable paging */
		$paging = $LUSER->getPreference('paging');
		$userRoles = $LUSER->getRoles();
		if (count($userRoles)) {
			foreach ($userRoles as $r) {
				$role_id .= ', '.$r->getID();
			}
		}
	}

	if(!isset($paging) || !$paging)
		$paging = 25;

	$start = getIntFromRequest('start');

	if ($start < 0) {
		$start = 0;
	}
	$nbProjects = FusionForge::getInstance()->getNumberOfProjectsUsingTags(array('groups.status' => 'A', 'groups.is_template' => 0, 'LOWER(name)' => strtolower($selected_tag)), 'register_time > 0 AND groups.group_id in (select ref_id FROM pfo_role_setting WHERE section_name = \'project_read\' and perm_val = 1 and role_id IN ('.$role_id.'))');
	$projects = group_get_readable_projects_using_tag_asc($selected_tag, $paging, $start);

	$max = ($nbProjects > ($start + $paging)) ? ($start + $paging) : $nbProjects;
	echo $HTML->paging_top($start, $paging, $nbProjects, $max, '/softwaremap/tag_cloud.php?tag='.$selected_tag);

	echo html_e('hr');

	// #################################################################
	// print actual project listings
	for ($i_proj = 0; $i_proj < count($projects); $i_proj++) {
		$row_grp = $projects[$i_proj];

		echo $HTML->listTableTop(array(), array(), 'full');
		$cells = array();
		$content = util_make_link ('/projects/'. strtolower($row_grp['unix_group_name']).'/',
				      '<strong>'.$row_grp['group_name'].'</strong> ');
		if ($row_grp['short_description']) {
			$content .= "- " . $row_grp['short_description'];
		}
		if (forge_get_config('use_trove')) {
			$cells[] = array($content, 'colspan' => 2);
		} else {
			$cells[][] = $content;
		}
		echo $HTML->multiTableRow(array('class' => 'top'), $cells);
		// extra description
		$cells = array();
		if (forge_get_config('use_trove')) {
			$cells[] = array(_('Tags') . _(': ') . list_project_tag($row_grp['group_id']), 'colspan' => 2);
		} else {
			$cells[][] = _('Tags') . _(': ') . list_project_tag($row_grp['group_id']);
		}
		echo $HTML->multiTableRow(array('class' => 'top'), $cells);
		$cells = array();
		if (forge_get_config('use_trove')) {
			$cells[][] = trove_getcatlisting($row_grp['group_id'], 0, 1, 0);
		}
		$res = db_query_params('SELECT percentile, ranking FROM project_weekly_metric WHERE group_id = $1', array($row_grp['group_id']));
		$nb_line = db_numrows($res);
		if ($nb_line) {
			$percentile = html_e('strong', array(), sprintf('%3.0f', number_format(db_result($res, 0, 'percentile'))));
			$ranking = html_e('strong', array(), sprintf('%d', number_format(db_result($res, 0, 'ranking'))));
		} else {
			$percentile = _('N/A');
			$ranking = _('N/A');
		}
		$content = html_e('br')._('Activity Percentile')._(': ').$percentile;
		$content .= html_e('br')._('Activity Ranking')._(': ').$ranking;
		$content .= html_e('br').sprintf(_('Registered') . _(': '));
		$content .= html_e('strong', array(), date(_('Y-m-d H:i'),$row_grp['register_time']));
		$cells[] = array($content, 'class' => 'align-right');
		echo $HTML->multiTableRow(array('class' => 'top'), $cells);
		if (forge_get_config('use_people') && people_group_has_job($row_grp['group_id'])) {
				$cells = array();
				$cells[] = array(util_make_link('/people/?group_id='.$row_grp['group_id'],_('[This project needs help]')), 'colspan' => 2, 'class' => 'align-center');
				echo $HTML->multiTableRow(array('class' => 'top'), $cells);
		}
		echo $HTML->listTableBottom();
		print '<hr />';
	}
	echo $HTML->paging_bottom($start, $paging, $nbProjects, '/softwaremap/tag_cloud.php?tag='.$selected_tag);
}
$HTML->footer();

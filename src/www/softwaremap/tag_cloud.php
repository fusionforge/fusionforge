<?php
/*
 * Copyright (C) 2008-2009 Alcatel-Lucent
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
 * along with Gforge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/trove.php';
require_once $gfcommon.'include/tag_cloud.php';

if (!forge_get_config('use_project_tags')) {
	exit_disabled();
}

$HTML->header(array('title'=>_('Software Map'),'pagename'=>'softwaremap'));
$HTML->printSoftwareMapLinks();

$selected_tag = getStringFromRequest('tag');
$page = getIntFromRequest('page', 1);

echo '<br />' . tag_cloud(array('selected' => $selected_tag, 'nb_max' => 100)) . '<br /><br />';

if ($selected_tag) {
	$res_grp = db_query_params('
		SELECT groups.group_id, group_name, unix_group_name, short_description, register_time
		FROM project_tags, groups
		WHERE name = $1
		AND project_tags.group_id = groups.group_id
		AND status = $2 AND type_id=1 AND register_time > 0
		ORDER BY group_name ASC', 
		array($selected_tag, 'A'), $TROVE_HARDQUERYLIMIT);
	$projects = array();
	$project_ids = array();
	while ($row_grp = db_fetch_array($res_grp)) {
		if (!forge_check_perm('project_read', $row_grp['group_id'])) {
			continue;
		}
		$projects[] = $row_grp;
		$project_ids[] = $row_grp['group_id'];
	}
	$querytotalcount = count($projects);

	// #################################################################
	// limit/offset display

	// store this as a var so it can be printed later as well
	$html_limit = '';
	if ($querytotalcount == $TROVE_HARDQUERYLIMIT){
		$html_limit .= sprintf(_('More than <strong>%1$s</strong> projects have <strong>%2$s</strong> as tag.'), $TROVE_HARDQUERYLIMIT, htmlspecialchars($selected_tag));
	}
	else {
		$html_limit .= sprintf(ngettext('<strong>%d</strong> project in result set.',
						'<strong>%d</strong> projects in result set.',
						$querytotalcount),
						$querytotalcount);
	}

	// only display pages stuff if there is more to display
	if ($querytotalcount > $TROVE_BROWSELIMIT) {
		$html_limit .= ' ' ;
		$html_limit .= sprintf (ngettext ('Displaying %d project per page. Projects sorted by alphabetical order.<br />',
						  'Displaying %d projects per page. Projects sorted by alphabetical order.<br />',
						  $TROVE_BROWSELIMIT),
						$TROVE_BROWSELIMIT);

		// display all the numbers
		for ($i=1 ;$i <= ceil($querytotalcount/$TROVE_BROWSELIMIT); $i++) {
			$html_limit .= ' ';
			if ($page != $i) {
				$html_limit .= util_make_link ('/softwaremap/tag_cloud.php?tag='.$selected_tag.'&page='.$i,
							       '&lt;'.$i.'&gt;');
			} else {
				$html_limit .= '<strong>&lt;'.$i.'&gt;</strong>';
			}				
			$html_limit .= ' ';
		}
	}

	print $html_limit."<hr />\n";

	// #################################################################
	// print actual project listings
	for ($i_proj = 0; $i_proj < $querytotalcount; $i_proj++) {
		$row_grp = $projects[$i_proj];

		// check to see if row is in page range
		if (($i_proj >= (($page-1)*$TROVE_BROWSELIMIT)) && ($i_proj < ($page*$TROVE_BROWSELIMIT))) {
			$viewthisrow = 1;
		} else {
			$viewthisrow = 0;
		}

		if ($row_grp && $viewthisrow) {
			print '<table border="0" cellpadding="0" width="100%">';
			print '<tr valign="top"><td colspan="2">';
			print util_make_link ('/projects/'. strtolower($row_grp['unix_group_name']).'/',
					      '<strong>'.$row_grp['group_name'].'</strong> ');

			if ($row_grp['short_description']) {
				print "- " . $row_grp['short_description'];
			}

			// extra description
			print '</td></tr>';
			print '<tr valign="top"><td colspan="2">';
			print _('Tags'). ':&nbsp;' . list_project_tag($row_grp['group_id']);
			print '</td></tr>';
			print '<tr valign="top"><td>';
			// list all trove categories
			print trove_getcatlisting($row_grp['group_id'],0,1,0);
			print '</td>'."\n".'<td align="right">'; // now the right side of the display
			$res = db_query_params('SELECT percentile, ranking
					FROM project_weekly_metric
					WHERE group_id=$1', array($row_grp['group_id']));
			$nb_line = db_numrows($res);
			if (! $nb_line) {
				$percentile = 'N/A';
				$ranking = 'N/A';
			}
			else {
				$percentile = number_format(db_result($res, 0, 'percentile'));
				$ranking = number_format(db_result($res, 0, 'ranking'));
			}
			printf ('<br />'._('Activity Percentile: <strong>%3.0f</strong>'), $percentile);
			printf ('<br />'._('Activity Ranking: <strong>%d</strong>'), $ranking);
			printf ('<br />'._('Registered: <strong>%s</strong>'), 
				date(_('Y-m-d H:i'),$row_grp['register_time']));
			print '</td></tr>';
			/*
			 if ($row_grp['jobs_count']) {
			 print '<tr><td colspan="2" align="center">'
			 .util_make_link ('/people/?group_id='.$row_grp['group_id'],_("[This project needs help]")).'</td></td>';
			 }
			*/
			print '</table>';
			print '<hr />';
		} // end if for row and range chacking
	}

	// print bottom navigation if there are more projects to display
	if ($querytotalcount > $TROVE_BROWSELIMIT) {
		print $html_limit;
	}
}

$HTML->footer(array());
?>

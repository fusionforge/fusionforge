<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');

/**
* Widget_ProjectLatestFileReleases
*
*/
class Widget_ProjectLatestFileReleases extends Widget {
    var $content;
    function Widget_ProjectLatestFileReleases() {
        $this->Widget('projectlatestfilereleases');
        $request =& HTTPRequest::instance();
        $pm = ProjectManager::instance();
        $project = $pm->getProject($request->get('group_id'));
        if ($project && $this->canBeUsedByProject($project)) {
            $this->content['title'] = _('Latest File Releases');
        }
    }
    function getTitle() {
        return $this->content['title'];
    }
    function getContent() {
        $request =& HTTPRequest::instance();
        $pm = ProjectManager::instance();
	$group_id=$request->get('group_id');
        $project = $pm->getProject($group_id);
	$unix_group_name = $project->getUnixName();
$HTML=$GLOBALS['HTML'];
	echo '
	<table summary="Latest file releases" class="width-100p100">
		<tr class="table-header">
			<th class="align-left" scope="col">
				'._('Package').'
			</th>
			<th scope="col">
				'._('Version').'
			</th>
			<th scope="col">
				'._('Date').'
			</th>
			<th scope="col">
				'._('Notes').'
			</th>
			<th scope="col">
				'._('Monitor').'
			</th>
			<th scope="col">
				'._('Download').'
			</th>
		</tr>';

		//
		//  Members of projects can see all packages
		//  Non-members can only see public packages
		//
		$public_required = 1;
		if (session_loggedin() &&
		    (user_ismember($group_id) || user_ismember(1,'A'))) {
			$public_required = 0 ;
		}

		$res_files = db_query_params ('SELECT frs_package.package_id,frs_package.name AS package_name,frs_release.name AS release_name,frs_release.release_id AS release_id,frs_release.release_date AS release_date
			FROM frs_package,frs_release
			WHERE frs_package.package_id=frs_release.package_id
			AND frs_package.group_id=$1
			AND frs_release.status_id=1
			AND (frs_package.is_public=1 OR 1 != $2)
			ORDER BY frs_package.package_id,frs_release.release_date DESC',
			array ($group_id,
				$public_required));
		$rows_files=db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No releases
			echo '<tr><td colspan="6"><strong>'._('This Project Has Not Released Any Files').'</strong></td></tr>';

		} else {
			/*
				This query actually contains ALL releases of all packages
				We will test each row and make sure the package has changed before printing the row
			*/
			for ($f=0; $f<$rows_files; $f++) {
				if (db_result($res_files,$f,'package_id')==db_result($res_files,($f-1),'package_id')) {
					//same package as last iteration - don't show this release
				} else {
					$rel_date = getdate (db_result ($res_files, $f, 'release_date'));
					$package_name = db_result($res_files, $f, 'package_name');
					$package_release = db_result($res_files,$f,'release_name');
					echo '
                        <tr class="align-center">
						<td class="align-left">
							<strong>' . $package_name . '</strong>
						</td>';
					// Releases to display
//print '<div about="" xmlns:sioc="http://rdfs.org/sioc/ns#" rel="container_of" resource="'.util_make_link ('/frs/?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files,$f,'release_id').'">';
					echo '
                        <td>'
						.$package_release.'
						</td>
						<td>'
						. $rel_date["month"] . ' ' . $rel_date["mday"] . ', ' . $rel_date["year"] .
						'</td>
						<td class="align-center">';
//echo '</div>';

					// -> notes
					// accessibility: image is a link, so alt must be unique in page => construct a unique alt
					$tmp_alt = $package_name . " - " . _('Release Notes');
					$link = '/frs/shownotes.php?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files, $f, 'release_id');
					$link_content = $HTML->getReleaseNotesPic($tmp_alt, $tmp_alt);
					echo util_make_link ($link, $link_content);
					echo '</td>
						<td class="align-center">';

					// -> monitor
					$tmp_alt = $package_name . " - " . _('Monitor this package');
					$link = '/frs/monitor.php?filemodule_id=' .  db_result($res_files,$f,'package_id') . '&amp;group_id='.$group_id.'&amp;start=1';
					$link_content = $HTML->getMonitorPic($tmp_alt, $tmp_alt);
					echo util_make_link ($link, $link_content);
					echo '</td>
						<td class="align-center">';

					// -> download
					$tmp_alt = $package_name." ".$package_release." - ". _('Download');
					$link_content = $HTML->getDownloadPic($tmp_alt, $tmp_alt);
					$t_link_anchor = $HTML->toSlug($package_name)."-".$HTML->toSlug($package_release)."-title-content";
					$link = '/frs/?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files, $f, 'release_id')."#".$t_link_anchor;
					echo util_make_link ($link, $link_content);
					echo '</td>
					</tr>';

				}
			}
		}
		echo '</table>';
		echo '<div class="underline-link">' . util_make_link ('/frs/?group_id='.$group_id, _('View All Project Files')) . '</div>';

    }
    function isAvailable() {
        return isset($this->content['title']);
    }
    function canBeUsedByProject(&$project) {
        return $project->usesFRS();
    }

    function getCategory() {
        return 'File-Release';
    }
    function getDescription() {
		return _(' List the most recent packages available for download along with their revision. <br />A Release Notes icon allows you to see the latest changes and developers comments associated with this revision.<br />Then comes the monitor icon, selecting this icon will cause this package to be monitored for you.<br />Anytime the project development team posts a new release, you will be automatically notified via e-mail. All monitored File Releases are listed in your Personal Page and can be canceled from this page or from the main page of the file release system.');
    }
}

?>

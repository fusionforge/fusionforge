<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfcommon.'frs/FRSPackageFactory.class.php';

/**
 * Widget_ProjectLatestFileReleases
 */

class Widget_ProjectLatestFileReleases extends Widget {
	var $content;
	function __construct() {
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
		$group_id = $request->get('group_id');
		$project = $pm->getProject($group_id);
		global $HTML;

		$frspf = new FRSPackageFactory($project);
		$frsps = $frspf->getFRSs();
		if (count($frsps) < 1) {
			echo $HTML->warning_msg(_('This project has not released any files'));
		} else {
			use_javascript('/frs/scripts/FRSController.js');
			echo $HTML->getJavascripts();
			echo html_ao('script', array('type' => 'text/javascript'));
			?>
			//<![CDATA[
			var controllerFRS;

			jQuery(document).ready(function() {
				controllerFRS = new FRSController();
			});

			//]]>
			<?php
			echo html_ac(html_ap() - 1);
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
						</th>';
			if (session_loggedin()) {
				echo '		<th scope="col">
							'._('Monitor').'
						</th>';
			}
			echo '			<th scope="col">
							'._('Download').'
						</th>
					</tr>';
			foreach ($frsps as $key => $frsp) {
				$frsr = $frsp->getNewestRelease();
				$rel_date = $frsr->getReleaseDate();
				$package_name = $frsp->getName();
				$package_release = $frsr->getName();
				echo '
					<tr class="align-center">
					<td class="align-left">
						<strong>' . $package_name . '</strong>
					</td>';
				// Releases to display
//print '<div about="" xmlns:sioc="http://rdfs.org/sioc/ns#" rel="container_of" resource="'.util_make_link('/frs/?group_id='.$group_id.'&release_id='.db_result($res_files,$f,'release_id').'">';
				echo '
					<td>'
					.$package_release.'
					</td>
					<td>'
					. date(_('Y-m-d'), $rel_date).
					'</td>
					<td class="align-center">';
//echo '</div>';

				// -> notes
				// accessibility: image is a link, so alt must be unique in page => construct a unique alt
				$tmp_alt = $package_name . " - " . _('Release Notes');
				$link = '/frs/?group_id=' . $group_id . '&view=shownotes&release_id='.$frsr->getID();
				$link_content = $HTML->getReleaseNotesPic($tmp_alt, $tmp_alt);
				echo util_make_link($link, $link_content);
				echo '</td>';

				// -> monitor
				if (session_loggedin()) {
					echo '<td class="align-center">';
					$url = '/frs/?group_id='.$group_id.'&package_id='.$frsp->getID().'&action=monitor';
					if($frsp->isMonitoring()) {
						$title = $package_name . " - " . _('Stop monitoring this package');
						$url .= '&status=0';
						$image = $HTML->getStopMonitoringPic($title);
					} else {
						$title = $package_name . " - " . _('Start monitoring this package');
						$url .= '&status=1';
						$image = $HTML->getStartMonitoringPic($title);
					}
					echo util_make_link('#', $image, array('id' => 'pkgid'.$frsp->getID(), 'onclick' => 'javascript:controllerFRS.doAction({action:\''.$url.'\', id:\'pkgid'.$frsp->getID().'\'})'), true);
					echo '</td>';
				}
				echo '	<td class="align-center">';

				// -> download
				$tmp_alt = $package_name." ".$package_release." - ". _('Download');
				$link_content = $HTML->getDownloadPic($tmp_alt, $tmp_alt);
				$t_link_anchor = $HTML->toSlug($package_name)."-".$HTML->toSlug($package_release)."-title-content";
				$link = '/frs/?group_id=' . $group_id . '&amp;release_id='.$frsr->getID()."#".$t_link_anchor;
				echo util_make_link ($link, $link_content);
				echo '</td>
				</tr>';
			}
			echo '</table>';
		}
		echo '<div class="underline-link">'.util_make_link('/frs/?group_id='.$group_id, _('View All Project Files')).'</div>';
	}

	function isAvailable() {
		return isset($this->content['title']);
	}
	function canBeUsedByProject(&$project) {
		return $project->usesFRS();
	}

	function getCategory() {
		return _('File Release System');
	}

	function getDescription() {
		return _('List the most recent packages available for download along with their revision.')
             . '<br />'
             . _('A Release Notes icon allows you to see the latest changes and developers comments associated with this revision.')
             . '<br />'
             . _('Then comes the monitor icon, selecting this icon will cause this package to be monitored for you.')
             . '<br />'
             . _('Anytime the project development team posts a new release, you will be automatically notified via e-mail. All monitored File Releases are listed in your Personal Page and can be canceled from this page or from the main page of the file release system.');
	}
}

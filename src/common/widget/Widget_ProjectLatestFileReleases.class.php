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
require_once $gfcommon.'frs/FRSReleaseFactory.class.php';

/**
 * Widget_ProjectLatestFileReleases
 */

class Widget_ProjectLatestFileReleases extends Widget {
	var $content;
	function __construct() {
		parent::__construct('projectlatestfilereleases');
		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$project = $pm->getProject($request->get('group_id'));
		if ($project && $this->canBeUsedByProject($project) && forge_check_perm('frs_admin', $project->getID(), 'read')) {
			$this->content['title'] = _('Latest File Releases');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getContent() {
		$result = '';

		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$group_id = $request->get('group_id');
		$project = $pm->getProject($group_id);
		global $HTML;

		$frsrf = new FRSReleaseFactory($project);
		$frsrnrs = $frsrf->getFRSRNewReleases(true);
		if (count($frsrnrs) < 1) {
			$result .= $HTML->warning_msg(_('This project has not released any files.'));
		} else {
			use_javascript('/frs/scripts/FRSController.js');
			$result .= $HTML->getJavascripts();
			$result .= html_ao('script', array('type' => 'text/javascript'));
			$result .= '
			//<![CDATA[
			var controllerFRS;
			jQuery(document).ready(function() {
				controllerFRS = new FRSController();
			});
			//]]>';
			$result .= html_ac(html_ap() - 1);
			$titleArr = array(_('Package'), _('Version'), _('Date'), _('Notes'));
			if (session_loggedin()) {
				$titleArr[] = _('Monitor');
			}
			$titleArr[] = _('Download');
			use_javascript('/js/sortable.js');
			$result .= $HTML->getJavascripts();
			$result .= $HTML->listTableTop($titleArr, false, 'sortable_widget_frs_listpackage full', 'sortable');
			foreach ($frsrnrs as $key => $frsrnr) {
				$rel_date = $frsrnr->getReleaseDate();
				$package_name = $frsrnr->FRSPackage->getName();
				$package_release = $frsrnr->getName();
				$cells = array();
				$cells[] = array(html_e('strong', array(), $package_name), 'class' => 'align-left');
				$cells[][] = $package_release;
				$cells[][] = date(_('Y-m-d'), $rel_date);

				// -> notes
				// accessibility: image is a link, so alt must be unique in page => construct a unique alt
				$tmp_alt = $package_name . " - " . _('Release Notes');
				$link = '/frs/?group_id=' . $group_id . '&view=shownotes&release_id='.$frsrnr->getID();
				$link_content = $HTML->getReleaseNotesPic($tmp_alt, $tmp_alt);
				$cells[] = array(util_make_link($link, $link_content), 'class' => 'align-center');
				// -> monitor
				if (session_loggedin()) {
					$url = '/frs/?group_id='.$group_id.'&package_id='.$frsrnr->FRSPackage->getID().'&action=monitor';
					if($frsrnr->FRSPackage->isMonitoring()) {
						$title = $package_name . " - " . _('Stop monitoring this package');
						$url .= '&status=0';
						$image = $HTML->getStopMonitoringPic($title);
					} else {
						$title = $package_name . " - " . _('Start monitoring this package');
						$url .= '&status=1';
						$image = $HTML->getStartMonitoringPic($title);
					}
					$cells[] = array(util_make_link('#', $image, array('id' => 'pkgid'.$frsrnr->FRSPackage->getID(), 'onclick' => 'javascript:controllerFRS.doAction({action:\''.$url.'\', id:\'pkgid'.$frsrnr->FRSPackage->getID().'\'})'), true), 'class' => 'align-center');
				}
				// -> download
				$tmp_alt = $package_name." ".$package_release." - ". _('Download');
				$link_content = $HTML->getDownloadPic($tmp_alt, $tmp_alt);
				$t_link_anchor = $HTML->toSlug($package_name)."-".$HTML->toSlug($package_release)."-title-content";
				$link = '/frs/?group_id=' . $group_id . '&amp;release_id='.$frsrnr->getID()."#".$t_link_anchor;
				$cells[] = array(util_make_link ($link, $link_content), 'class' => 'align-center');
				$result .= $HTML->multiTableRow(array(), $cells);
			}
			$result .= $HTML->listTableBottom();
		}
		$result .= html_e('div', array('class' => 'underline-link'), util_make_link('/frs/?group_id='.$group_id, _('View All Project Files')));

		return $result;
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

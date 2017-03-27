<?php
/**
 * Widget_ProjectLatestArtifacts
 *
 * Copyright 2017, Franck Villaume - TrivialDev
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
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

class Widget_ProjectLatestArtifacts extends Widget {

	/**
	 * Default number of artifacts to display
	 */
	const NB_ARTIFACTS_TO_DISPLAY = 5;

	public function __construct() {
		parent::__construct('projectlatestartifacts');
		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$project = $pm->getProject($request->get('group_id'));
		if ($project && $this->canBeUsedByProject($project)) {
			$atf = new ArtifactTypeFactory($project);
			$ats = $atf->getArtifactTypes();
			if (count($ats) > 0) {
				$this->content['title'] = _('5 Latest Artifacts');
			}
		}
	}

	public function getTitle() {
		return _('5 Latest Artifacts');
	}

	public function _getLinkToArtifact($aid) {
		$artf = artifact_get_object($aid);
		return $artf->getPermalink();
	}

	public function getContent() {
		global $HTML;
		$html = '';
		//$uh = new UserHelper();
		$request = HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$project = $pm->getProject($request->get('group_id'));
		$atf = new ArtifactTypeFactory($project);
		$artifacts = array();
		$ats = $atf->getArtifactTypes();
		$atids = array();
		foreach ($ats as $at) {
			$atids[] = $at->getID();
		}
		$artifacts = db_query_params('SELECT * FROM artifact_vw WHERE group_artifact_id = ANY ($1) ORDER BY last_modified_date DESC, artifact_id', array(db_int_array_to_any_clause($atids)), self::NB_ARTIFACTS_TO_DISPLAY);
		if (db_numrows($artifacts) > 0) {
			html_use_tablesorter();
			$html .= $HTML->getJavascripts();
			$tabletop = array(_('Date'), _('Id'), _('Summary'), _('Tracker'), _('Status'), _('Priority'), _('Last Modified By'));
			$classth = array('', '', '', '', '', '');
			$html .= $HTML->listTableTop($tabletop, array(), 'sortable_widget_tracker_listartifact full', 'sortable', $classth);
			while ($artifact = db_fetch_array($artifacts)) {
				$cells = array();
				$artf = artifact_get_object($artifact['artifact_id'], $artifact);
				$cells[][] = date(_('Y-m-d H:i'), $artifact['last_modified_date']);
				$cells[][] = util_make_link($artf->getPermalink(), $artf->getID());
				$cells[][] = $artifact['summary'];
				$at = artifactType_get_object($artifact['group_artifact_id']);
				$cells[][] = util_make_link('/tracker/?group_id='.$project->getID().'&atid='.$artifact['group_artifact_id'], $at->getName());
				$cells[][] = $artf->getCustomStatusName();
				$cells[][] = $artifact['priority'];
				if ($artf->getLastModifiedBy() != 100) {
					$cells[][] = util_display_user($artf->getLastModifiedUnixName(), $artf->getLastModifiedBy(), $artf->getLastModifiedRealName());
				} else {
					$cells[][] = $artf->getLastModifiedRealName();
				}
				$html .= $HTML->multiTableRow(array(), $cells);
			}
			$html .= $HTML->listTableBottom();
		} else {
			$html .= $HTML->information(_('No artifact found'));
		}
		$html .= html_e('div', array('class' => 'underline-link'), util_make_link('/tracker/?group_id='.$project->getID(), _('Browse Trackers')));
		return $html;
	}

	function getCategory() {
		return _('Trackers');
	}

	function getDescription() {
		return _('List the 5 most recent artifacts in the project.');
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function canBeUsedByProject(&$project) {
		return $project->usesTracker();
	}
}

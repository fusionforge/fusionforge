<?php
/**
 * Widget_ProjectLatestCommits
 *
 * Copyright 2014,2017-2018,2021, Franck Villaume - TrivialDev
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

class Widget_ProjectLatestCommits extends Widget {

	/**
	 * Default number of commits to display
	 */
	const NB_COMMITS_TO_DISPLAY = 5;

	public function __construct() {
		parent::__construct('projectlatestcommits');
		$pm = ProjectManager::instance();
		$project = $pm->getProject(getIntFromRequest('group_id'));
		if ($project && $this->canBeUsedByProject($project) && forge_check_perm('scm', $project->getID(), 'read')) {
			$this->content['title'] = _('5 Latest Commits');
		}
	}

	public function getTitle() {
		return _('5 Latest Commits');
	}

	public function _getLinkToCommit($project, $commit_id, $plugin_name, $repo_name) {
		return util_make_link('/scm/browser.php?group_id='.$project->getID().'&scm_plugin='.$plugin_name.'&commit='.$commit_id.'&extra='.$repo_name, _('commit')._(': ').$commit_id);
	}

	static function commit_dateorder($a, $b) {
		if ($a['date'] == $b['date']) {
			return 0;
		}
		return ($a['date'] > $b['date']) ? -1 : 1;
	}

	public function getContent() {
		global $HTML;
		$html = '';
		$pm = ProjectManager::instance();
		$project = $pm->getProject(getIntFromRequest('group_id'));
		$revisions = array();
		if ($project->usesPlugin('scmsvn') && forge_check_perm('scm', $project->getID(), 'read')) {
			$scmPlugin = plugin_get_object('scmsvn');
			$revisions = array_merge($revisions, $scmPlugin->getCommits($project, null, self::NB_COMMITS_TO_DISPLAY));
		}
		if ($project->usesPlugin('scmgit') && forge_check_perm('scm', $project->getID(), 'read')) {
			$scmPlugin = plugin_get_object('scmgit');
			$revisions = array_merge($revisions, $scmPlugin->getCommits($project, null, self::NB_COMMITS_TO_DISPLAY));
		}
		if (count($revisions) > 0) {
			usort($revisions, array ($this, "commit_dateorder"));
			$revisions = array_slice($revisions, 0, self::NB_COMMITS_TO_DISPLAY, true);
			foreach ($revisions as $key => $revision) {
				$revisionDescription = substr($revision['description'], 0, 255);
				if (strlen($revision['description']) > 255) {
					$revisionDescription .= ' [...]';
				}
				$divattr = array('class' => '', 'style' => 'border-bottom:1px solid #ddd');
				if ((($key + 1) % 2) == 1) {
					$divattr['class'] = 'bgcolor-white';
				} else {
					$divattr['class'] = 'bgcolor-grey';
				}
				$html .= html_e('div', $divattr,
						html_e('div', array('style' => 'font-size:0.98em'),
							$this->_getLinkToCommit($project, $revision['commit_id'], $revision['pluginName'], $revision['repo_name']).
							' '._('on repository').' '.$revision['repo_name'].' '.
							date(_("Y-m-d H:i"), $revision['date'])).
						html_e('div', array('style' => 'padding-left:20px; padding-bottom:4px; color:#555'),
							$revisionDescription));
			}
		} else {
			$html .= $HTML->warning_msg(_('No commit found.'));
		}
		$html .= html_e('div', array('class' => 'underline-link'), util_make_link('/scm/?group_id='.$project->getID(), _('Browse Source Content Management')));
		return $html;
	}

	function getCategory() {
		return _('SCM');
	}

	function getDescription() {
		return _('List the 5 most recent commits by team project.');
	}

	function isAjax() {
		return true;
	}

	function getAjaxUrl($owner_id, $owner_type) {
		$ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
		if (existInRequest('hide_item_id') || existInRequest('hide_scm')) {
			$ajax_url .= '&hide_item_id='.getIntFromRequest('hide_item_id').'&hide_scm='.getIntFromRequest('hide_scm');
		}
		return $ajax_url;
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function canBeUsedByProject(&$project) {
		return $project->usesSCM();
	}
}

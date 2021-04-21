<?php
/**
 * Widget_MyLatestCommits
 *
 * Copyright (c) Xerox Corporation, Codendi 2001-2009 - marc.nazarian@xrce.xerox.com
 * Copyright 2014, 2018,2021, Franck Villaume - TrivialDev
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

class Widget_MyLatestCommits extends Widget {

	/**
	* Default number of commits to display (if user did not change/set preferences)
	*/
	const NB_COMMITS_TO_DISPLAY = 5;

	/**
	* Number of commits to display (user preferences)
	*/
	private $_nb_commits;

	function __construct() {
		parent::__construct('mylatestcommits');
		$this->_nb_commits = UserManager::instance()->getCurrentUser()->getPreference('my_latests_commits_nb_display');
		if($this->_nb_commits === false) {
			$this->_nb_commits = self::NB_COMMITS_TO_DISPLAY;
			UserManager::instance()->getCurrentUser()->setPreference('my_latests_commits_nb_display', $this->_nb_commits);
		}
	}

	public function getTitle() {
		return _('My Latest Commits');
	}

	public function _getLinkToCommit($project, $commit_id, $pluginName, $repo_name) {
		return util_make_link('/scm/browser.php?group_id='.$project->getID().'&scm_plugin='.$pluginName.'&commit='.$commit_id.'&extra='.$repo_name, _('commit')._(': ').$commit_id);
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
		//$uh = new UserHelper();
		$request = HTTPRequest::instance();
		$hp = Codendi_HTMLPurifier::instance();
		$user = UserManager::instance()->getCurrentUser();
		$projects = $user->getGroups();
		$global_nb_revisions = 0;
		foreach ($projects as $project) {
			$vItemId = new Valid_UInt('hide_item_id');
			$vItemId->required();
			if ($request->valid($vItemId)) {
				$hide_item_id = $request->get('hide_item_id');
			} else {
				$hide_item_id = null;
			}
			$vProject = new Valid_WhiteList('hide_scm', array(0, 1));
			$vProject->required();
			if ($request->valid($vProject)) {
				$hide_scm = $request->get('hide_scm');
			} else {
				$hide_scm = null;
			}
			$revisions = array();
			if ($project->usesPlugin('scmsvn') && forge_check_perm('scm', $project->getID(), 'read')) {
				$scmPlugin = plugin_get_object('scmsvn');
				$revisions = array_merge($revisions, $scmPlugin->getCommits($project, $user, $this->_nb_commits));
			}
			if ($project->usesPlugin('scmgit') && forge_check_perm('scm', $project->getID(), 'read')) {
				$scmPlugin = plugin_get_object('scmgit');
				$revisions = array_merge($revisions, $scmPlugin->getCommits($project, $user, $this->_nb_commits));
			}
			if (count($revisions) > 0) {
				usort($revisions, array($this, 'commit_dateorder'));
				$revisions = array_slice($revisions, 0, $this->_nb_commits, true);
				$global_nb_revisions += count($revisions);
				list($hide_now, $count_diff, $hide_url) = my_hide_url('scm', $project->getID(), $hide_item_id, count($projects), $hide_scm);
				$html .= html_e('div', array(), $hide_url.util_make_link('/scm/?group_id='.$project->getID(), $project->getPublicName()));

				if (!$hide_now) {
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
				}
			}
		}
		if (!$global_nb_revisions) {
			$html .= $HTML->warning_msg(_('No commit found.'));
		}
		return $html;
	}

	function getPreferences() {
		$prefs  = _('Maximum number of commits to display per project.');
		$prefs .= html_e('input', array('name' => 'nb_commits', 'type' => 'number', 'size' => 2, 'maxlength' => 3, 'value' => UserManager::instance()->getCurrentUser()->getPreference('my_latests_commits_nb_display')));
		return $prefs;
	}

	function updatePreferences() {
		$request->valid(new Valid_String('cancel'));
		$nbShow = new Valid_UInt('nb_commits');
		$nbShow->required();
		if (!$request->exist('cancel')) {
			if ($request->valid($nbShow)) {
				$this->_nb_commits = $request->get('nb_commits');
			} else {
				$this->_nb_commits = self::NB_COMMITS_TO_DISPLAY;
			}
			UserManager::instance()->getCurrentUser()->setPreference('my_latests_commits_nb_display', $this->_nb_commits);
		}
		return true;
	}


	function hasPreferences() {
		return true;
	}

	function getCategory() {
		return _('SCM');
	}

	function getDescription() {
		return _('List Commits you have done, by project.');
	}

	function isAjax() {
		return true;
	}

	function getAjaxUrl($owner_id, $owner_type) {
		$request =& HTTPRequest::instance();
		$ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
		if ($request->exist('hide_item_id') || $request->exist('hide_scm')) {
			$ajax_url .= '&hide_item_id='.$request->get('hide_item_id').'&hide_scm='.$request->get('hide_scm');
		}
		return $ajax_url;
	}

	function isAvailable() {
		if (!forge_get_config('use_scm')) {
			return false;
		}
		foreach (UserManager::instance()->getCurrentUser()->getGroups(false) as $p) {
			if ($p->usesPlugin('scmsvn') || $p->usesPlugin('scmgit')) {
				return true;
			}
		}
		return false;
	}
}

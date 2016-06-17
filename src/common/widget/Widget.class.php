<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012,2014,2016 Franck Villaume - TrivialDev
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

require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

require_once $gfcommon.'widget/Widget_MySurveys.class.php';
require_once $gfcommon.'widget/Widget_MyProjects.class.php';
require_once $gfcommon.'widget/Widget_MyBookmarks.class.php';
require_once $gfcommon.'widget/Widget_MyMonitoredDocuments.class.php';
require_once $gfcommon.'widget/Widget_MyMonitoredForums.class.php';
require_once $gfcommon.'widget/Widget_MyMonitoredFp.class.php';
require_once $gfcommon.'widget/Widget_MyLatestCommits.class.php';
require_once $gfcommon.'widget/Widget_MyProjectsLastDocuments.class.php';
require_once $gfcommon.'widget/Widget_MyArtifacts.class.php';
//require_once('common/widget/Widget_MySrs.class.php');
require_once $gfcommon.'widget/Widget_MyTasks.class.php';
require_once $gfcommon.'widget/Widget_MyRss.class.php';
require_once $gfcommon.'widget/Widget_MyAdmin.class.php';
require_once $gfcommon.'widget/Widget_MySystasks.class.php';
//require_once 'common/widget/Widget_MyTwitterFollow.class.php';
//require_once('common/widget/Widget_MyWikiPage.class.php');

require_once $gfcommon.'widget/Widget_ProjectDescription.class.php' ;
require_once $gfcommon.'widget/Widget_ProjectMembers.class.php';
require_once $gfcommon.'widget/Widget_ProjectInfo.class.php';
require_once $gfcommon.'widget/Widget_ProjectLatestFileReleases.class.php';
require_once $gfcommon.'widget/Widget_ProjectLatestDocuments.class.php';
require_once $gfcommon.'widget/Widget_ProjectDocumentsActivity.class.php' ;
require_once $gfcommon.'widget/Widget_ProjectLatestNews.class.php';
require_once $gfcommon.'widget/Widget_ProjectPublicAreas.class.php';
require_once $gfcommon.'widget/Widget_ProjectRss.class.php';
require_once $gfcommon.'widget/Widget_ProjectLatestCommits.class.php';
//require_once 'common/widget/Widget_ProjectTwitterFollow.class.php';
//require_once('common/widget/Widget_ProjectWikiPage.class.php');
require_once 'common/widget/Widget_ProjectScmStats.class.php';

require_once $gfcommon.'widget/Widget_HomeDetailActivityMostActiveProjectWeek.class.php';
require_once $gfcommon.'widget/Widget_HomeLatestNews.class.php';
require_once $gfcommon.'widget/Widget_HomeStats.class.php';
require_once $gfcommon.'widget/Widget_HomeTagCloud.class.php';
require_once $gfcommon.'widget/Widget_HomeVersion.class.php';

/**
 * FusionForge Layout Widget
 */

/* abstract */ class Widget {

	var $content_id;
	var $id;
	var $hasPreferences;
	var $buttons;
	var $owner_id;
	var $owner_type;

	function __construct($id) {
		$this->id = $id;
		$this->content_id = 0;
	}

	function display($layout_id, $column_id, $readonly, $is_minimized, $display_preferences, $owner_id, $owner_type) {
		$GLOBALS['HTML']->widget($this, $layout_id, $readonly, $column_id, $is_minimized, $display_preferences, $owner_id, $owner_type);
	}
	function getTitle() {
		return '';
	}
	/**
	 * TODO : Enter description here ...
	 * @return string
	 */
	function getContent() {
		return '';
	}
	function getPreferencesForm($layout_id, $owner_id, $owner_type) {
		global $HTML;
		$prefs  = $HTML->openForm(array('method' => 'post', 'action' => util_make_uri('/widgets/widget.php?owner='. $owner_type.$owner_id .'&action=update&name['. $this->id .']='. $this->getInstanceId() .'&content_id='. $this->getInstanceId() .'&layout_id='. $layout_id)));
		$prefs .= html_ao('fieldset').html_e('legend', array(), _('Preferences'));
		$prefs .= $this->getPreferences();
		$prefs .= html_e('br');
		$prefs .= html_e('input', array('type' => 'submit', 'name' => 'cancel', 'value' => _('Cancel')));
		$prefs .= html_e('input', array('type' => 'submit', 'value' => _('Submit')));
		$prefs .= html_ac(html_ap() - 1);
		$prefs .= $HTML->closeForm();
		return $prefs;
	}
	function getInstallPreferences() {
		return '';
	}
	function getPreferences() {
		return '';
	}
	function hasPreferences() {
		return false;
	}
	function hasButtons() {
		return false;
	}
	function updatePreferences(&$request) {
		return true;
	}
	function hasRss() {
		return false;
	}
	function getRssUrl($owner_id, $owner_type) {
		if ($this->hasRss()) {
			return '/widgets/widget.php?owner='.$owner_type.$owner_id.'&amp;action=rss&amp;name['. $this->id .']='. $this->getInstanceId();
		} else {
			return false;
		}
	}
	function isUnique() {
		return true;
	}
	function isAvailable() {
		return true;
	}
	function isAjax() {
		return false;
	}
	function getInstanceId() {
		return $this->content_id;
	}
	function loadContent($id) {
	}
	function setOwner($owner_id, $owner_type) {
		$this->owner_id = $owner_id;
		$this->owner_type = $owner_type;
	}
	function canBeUsedByProject(&$project) {
		return false;
	}
	/**
	 * cloneContent
	 *
	 * Take the content of a widget, clone it and return the id of the new content
	 *
	 * @param int $id the id of the content to clone
	 * @param int $owner_id the owner of the widget of the new widget
	 * @param int $owner_type the type of the owner of the new widget (see WidgetLayoutManager)
	 * @return int
	 */
	function cloneContent($id, $owner_id, $owner_type) {
		return $this->getInstanceId();
	}
	function create(&$request) {
	}
	function destroy($id) {
	}
	/**
	 * getInstance - Returns an instance of a widget given its name
	 * @param string $widget_name
	 * @return Widget instance
	 */
	static  function & getInstance($widget_name) {
		$o = null;
		switch($widget_name) {
			case 'homelatestnews':
				$o = new Widget_HomeLatestNews();
				break;
			case 'homestats':
				$o = new Widget_HomeStats();
				break;
			case 'hometagcloud':
				$o = new Widget_HomeTagCloud();
				break;
			case 'homeversion':
				$o = new Widget_HomeVersion();
				break;
			case 'homedetailactivitymostactiveprojectweek';
				$o = new Widget_HomeDetailActivityMostActiveProjectWeek();
				break;
			case 'mysurveys':
				$o = new Widget_MySurveys();
				break;
			case 'myprojects':
				$o = new Widget_MyProjects();
				break;
			case 'mybookmarks':
				$o = new Widget_MyBookmarks();
				break;
			case 'mymonitoredforums':
				$o = new Widget_MyMonitoredForums();
				break;
			case 'mymonitoreddocuments':
				$o = new Widget_MyMonitoredDocuments();
				break;
			case 'myprojectslastdocuments':
				$o = new Widget_MyProjectsLastDocuments();
				break;
			case 'myartifacts':
				$o = new Widget_MyArtifacts();
				break;
			case 'myrss':
				$o = new Widget_MyRss();
				break;
			case 'mytasks':
				$o = new Widget_MyTasks();
				break;
			case 'myadmin':
				if (forge_check_global_perm('forge_admin')
					|| forge_check_global_perm('approve_projects')
					|| forge_check_global_perm('approve_news')) {
					$o = new Widget_MyAdmin();
				}
				break;/*
			case 'mysrs':
				$o = new Widget_MySrs();
				break;*/
			case 'mymonitoredfp':
				$o = new Widget_MyMonitoredFp();
				break;
			case 'mylatestcommits':
				$o = new Widget_MyLatestCommits();
				break;
			case 'mysystasks':
				$o = new Widget_MySystasks();
				break;/*
			case 'mytwitterfollow':
				$o = new Widget_MyTwitterFollow();
				break;
			case 'mywikipage':                   //not yet
				$o = new Widget_MyWikiPage();
				break;*/
			case 'projectdescription':
				$o = new Widget_ProjectDescription();
				break;
			case 'projectmembers':
				$o = new Widget_ProjectMembers();
				break;
			case 'projectinfo':
				$o = new Widget_ProjectInfo();
				break;
			case 'projectlatestfilereleases':
				$o = new Widget_ProjectLatestFileReleases();
				break;
			case 'projectlatestdocuments':
				$o = new Widget_ProjectLatestDocuments();
				break;
			case 'projectdocumentsactivity':
				$o = new Widget_ProjectDocumentsActivity();
				break;
			case 'projectlatestnews':
				$o = new Widget_ProjectLatestNews();
				break;
			case 'projectpublicareas':
				$o = new Widget_ProjectPublicAreas();
				break;
			case 'projectrss':
				$o = new Widget_ProjectRss();
				break;/*
			case 'projecttwitterfollow':
				$o = new Widget_ProjectTwitterFollow();
				break;
			case 'projectwikipage':                    //not yet
				$o = new Widget_ProjectWikiPage();
				break;*/
			case 'projectscmstats':
				$o = new Widget_ProjectScmStats();
				break;
			case 'projectlatestcommits':
				$o = new Widget_ProjectLatestCommits();
				break;
			default:
				//$em = EventManager::instance();
				//$em->processEvent('widget_instance', array('widget' => $widget_name, 'instance' => &$o));
				// calls the plugin's hook to get an instance of the widget
				plugin_hook('widget_instance', array('widget' => $widget_name, 'instance' => &$o));
				break;
		}
		if (!$o || !($o instanceof Widget)) {
			$o = null;
		}
		return $o;
	}
	/**
	 * getCodendiWidgets - Static
	 * @param unknown_type $owner_type
	 * @return multitype:
	 */
	static function getCodendiWidgets($owner_type) {
		switch ($owner_type) {
			case WidgetLayoutManager::OWNER_TYPE_USER:
				$widgets = array('myadmin', 'mysurveys', 'myprojects', 'mybookmarks',
						'mymonitoredforums', 'mymonitoredfp', 'myartifacts', 'mysystasks', //'mywikipage' //not yet
						'mytasks', 'mylatestcommits', 'myrss', 'mymonitoreddocuments', 'myprojectslastdocuments',
						);
				break;
			case WidgetLayoutManager::OWNER_TYPE_GROUP:
				// project home widgets
				$widgets = array('projectdescription', 'projectmembers', 'projectinfo', 'projectscmstats',
						'projectlatestfilereleases', 'projectlatestdocuments', 'projectlatestnews', 'projectpublicareas', //'projectwikipage' //not yet
						'projectlatestcommits', 'projecttwitterfollow', 'projectrss', 'projectdocumentsactivity',
						);
				break;
			case WidgetLayoutManager::OWNER_TYPE_HOME:
				$widgets = array('hometagcloud', 'homeversion', 'homelatestnews', 'homestats', 'homedetailactivitymostactiveprojectweek');
				break;
			default:
				$widgets = array();
				break;
		}

		// add widgets of the plugins, declared through hooks
		$plugins_widgets = array();
		//$em =& EventManager::instance();
		//$em->processEvent('widgets', array('codendi_widgets' => &$plugins_widgets, 'owner_type' => $owner_type));
		plugin_hook('widgets', array('codendi_widgets' => &$plugins_widgets, 'owner_type' => $owner_type));
		plugin_hook('widgets', array('fusionforge_widgets' => &$plugins_widgets, 'owner_type' => $owner_type));

		if (is_array($plugins_widgets)) {
			$widgets = array_merge($widgets, $plugins_widgets);
		}
		return $widgets;
	}
	/* static */ function getExternalWidgets($owner_type) {
		switch ($owner_type) {
			case WidgetLayoutManager::OWNER_TYPE_USER:
				$widgets = array(
						);
				break;
			case WidgetLayoutManager::OWNER_TYPE_GROUP:
				$widgets = array(
						);
				break;
			case WidgetLayoutManager::OWNER_TYPE_HOME:
				$widgets = array();
				break;
			default:
				$widgets = array();
				break;
		}

		$plugins_widgets = array();
		$em =& EventManager::instance();
		$em->processEvent('widgets', array('external_widgets' => &$plugins_widgets, 'owner_type' => $owner_type));

		if (is_array($plugins_widgets)) {
			$widgets = array_merge($widgets, $plugins_widgets);
		}
		return $widgets;
	}

	function getCategory() {
		return _('General');
	}
	function getDescription() {
		return '';
	}
	function getPreviewCssClass() {
		$locale = UserManager::instance()->getCurrentUser()->getLanguage();
		$locale = "en_US";
		return 'widget-preview-'.($this->id).'-'.$locale;
	}
	function getAjaxUrl($owner_id, $owner_type) {
		return '/widgets/widget.php?owner='. $owner_type.$owner_id .'&action=ajax&name['. $this->id .']='. $this->getInstanceId();
	}
	function getIframeUrl($owner_id, $owner_type) {
		return '/widgets/widget.php?owner='. $owner_type.$owner_id .'&amp;action=iframe&amp;name['. $this->id .']='. $this->getInstanceId();
	}
}

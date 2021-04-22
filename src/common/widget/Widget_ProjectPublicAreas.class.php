<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012,2014,2016-2017, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once 'Widget.class.php';
require_once $gfcommon.'frs/FRSManager.class.php';
require_once $gfcommon.'docman/DocumentManager.class.php';

/**
 * Widget_ProjectPublicAreas
 */

class Widget_ProjectPublicAreas extends Widget {
	function __construct() {
		parent::__construct('projectpublicareas');
	}

	function getTitle() {
		return _('Public Tools');
	}

	function getContent() {
		global $HTML;
		$result = '';
		$group_id = getIntFromRequest('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);
		// ################# Homepage Link

		$result .= html_e('div', array('class' => 'public-area-box', 'rel' => 'doap:homepage'),
				util_make_link($project->getHomePage(), $HTML->getHomePic(_('Home Page')). ' ' ._('Project Home Page'), false, true));

		// ################## ArtifactTypes

		if ($project->usesTracker()) {
			$result .= '<div class="public-area-box">'."\n";
			$link_content = $HTML->getFollowPic(_('Tracker')) . ' ' . _('Tracker');
			$result .= util_make_link('/tracker/?group_id=' . $group_id, $link_content);

			$res = db_query_params ('SELECT agl.*,aca.count,aca.open_count
					FROM artifact_group_list agl
					LEFT JOIN artifact_counts_agg aca USING (group_artifact_id)
					WHERE agl.group_id=$1
					ORDER BY group_artifact_id ASC',
					array($group_id));

			$rows = array();
			while ($row = db_fetch_array($res)) {
				if (forge_check_perm('tracker', $row['group_artifact_id'], 'read')) {
					$rows[] = $row;
				}
			}

			if (count($rows) < 1) {
				$result .= "<br />\n<em>"._('There are no trackers available').'</em>';
			} else {
				$elementsLi = array();
				foreach ($rows as $row) {
					// tracker REST paths are something like : /tracker/cm/project/A_PROJECT/atid/NUMBER to plan compatibility
					// with OSLC-CM server API
					$group_artifact_id = $row['group_artifact_id'];
					$tracker_stdzd_uri = util_make_url('/tracker/cm/project/'. $project->getUnixName() .'/atid/'. $group_artifact_id);
					$contentLi = '<span rel="http://www.w3.org/2002/07/owl#sameAs">'."\n";
					$contentLi .= util_make_link('/tracker/?atid='. $group_artifact_id . '&group_id='.$group_id.'&func=browse', $row['name']) . ' ' ;
					$contentLi .= "</span>\n"; // /owl:sameAs
					$contentLi .= sprintf(ngettext('(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', '(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', $row['open_count']), $row['open_count'], $row['count']);
					$contentLi .= '<br />';
					$contentLi .= '<span rel="sioc:has_space" resource="" ></span>'."\n";
					$elementsLi[] = array('content' => $contentLi, 'attrs' => array('about' => $tracker_stdzd_uri, 'typeof' => 'sioc:Container'));
				}
				$result .= $HTML->html_list($elementsLi, array('class' => 'tracker', 'rel' => 'doap:bug-database'));
			}

			$result .= "</div>\n";
		}

		// ################## forums

		if ($project->usesForum()) {
			$result .= '<div class="public-area-box">'."\n";
			//	$result .= '<hr size="1" /><a rel="sioc:container_of" href="'.util_make_url ('/forum/?group_id='.$group_id).'">';
			$ff = new ForumFactory($project);
			$f_arr = $ff->getForums();
			$forums_count = count($f_arr);
			$messages_count = 0;
			foreach ($f_arr as $f) {
				$messages_count += $f->getMessageCount();
			}

			$link_content = $HTML->getForumPic() . ' ' . _('Public Forums');
			$result .= util_make_link('/forum/?group_id=' . $group_id, $link_content);
			$result .= ' (';
			$result .= sprintf(ngettext("<strong>%d</strong> message","<strong>%d</strong> messages",$messages_count),$messages_count);
			$result .= ' ' . _('in') . ' ';
			$result .= sprintf(ngettext("<strong>%d</strong> forum","<strong>%d</strong> forums",$forums_count),$forums_count);
			$result .= ')' ;
			$result .= "\n</div>";
		}

		// ##################### Doc Manager

		if ($project->usesDocman()) {
			$result .= '<div class="public-area-box">';
			$link_content = $HTML->getDocmanPic() . ' ' . _('Document Manager');
			//	<a rel="sioc:container_of" xmlns:sioc="http://rdfs.org/sioc/ns#" href="'.util_make_url ('/docman/?group_id='.$group_id).'">';
			$result .= util_make_link('/docman/?group_id='.$group_id, $link_content);
			if (forge_check_perm('docman', $group_id, 'read')) {
				$docm = new DocumentManager($project);
				$result .= ' ('.html_e('strong', array(), $docm->getNbDocs(), true, false).' '._('documents').' '._('in').' '.html_e('strong', array(), $docm->getNbFolders(), true, false).' '._('directories').')';
			}
			$result .= '</div>';
		}

		// ##################### FRS

		if ($project->usesFRS()) {
			$result .= '<div class="public-area-box">';
			$link_content = $HTML->getPackagePic() . ' ' . _('Files');
			//	<a rel="sioc:container_of" xmlns:sioc="http://rdfs.org/sioc/ns#" href="'.util_make_url ('/frs/?group_id='.$group_id).'">';
			$result .= util_make_link('/frs/?group_id='.$group_id, $link_content);
			$frsm = new FRSManager($project);
			$result .= ' ('.html_e('strong', array(), $frsm->getNbReleases(), true, false).' '._('releases').' '._('in').' '.html_e('strong', array(), $frsm->getNbPackages(), true, false).' '._('packages').')';
			$result .= '</div>';
		}

		// ##################### Mailing lists

		if ($project->usesMail()) {
			$result .= '<div class="public-area-box">';
			$link_content = $HTML->getMailPic() . ' ' . _('Mailing Lists');
			$result .= util_make_link('/mail/?group_id='.$group_id, $link_content);
			$n = project_get_mail_list_count($group_id);
			$result .= ' ';
			$result .= sprintf(ngettext('(<strong>%s</strong> public mailing list)', '(<strong>%s</strong> public mailing lists)', $n), $n);
			$result .= "\n</div>\n";
		}

		// ##################### Task Manager

		if ($project->usesPM()) {
			$result .= '<div class="public-area-box">';
			$link_content = $HTML->getPmPic() . ' ' . _('Tasks');
			$result .= util_make_link('/pm/?group_id='.$group_id, $link_content);

			$pgf = new ProjectGroupFactory ($project);
			$pgs = $pgf->getProjectGroups();

			if (count($pgs) < 1) {
				$result .= "<br />\n<em>"._('There are no subprojects available').'</em>';
			} else {
				$result .= "\n".'<ul class="task-manager">';
				foreach ($pgs as $pg) {
					$result .= "\n\t<li>";
					$result .= util_make_link('/pm/task.php?group_project_id='.$pg->getID().'&group_id='.$group_id.'&func=browse',$pg->getName()).' ('.html_e('strong', array(), $pg->getOpenCount(), true, false).' '._('open').' / '.html_e('strong', array(), $pg->getTotalCount()).' '._('total').')';
					$result .= '</li>' ;
				}
				$result .= "\n</ul>";
			}
			$result .= "\n</div>\n";
		}

		// ######################### Surveys

		if ($project->usesSurvey()) {
			$result .= '<div class="public-area-box">'."\n";
			$link_content = $HTML->getSurveyPic() . ' ' . _('Surveys');
			$result .= util_make_link('/survey/?group_id='.$group_id, $link_content);
			$result .= ' (<strong>'. project_get_survey_count($group_id) .'</strong> ' . _('surveys').')';
			$result .= "\n</div>\n";
		}

		// ######################### SCM

		if ($project->usesSCM()) {
			$result .= '<div class="public-area-box">'."\n";

			$link_content = $HTML->getScmPic() . ' ' . _('SCM Repository');
			//	$result .= '<hr size="1" /><a rel="doap:repository" href="'.util_make_url ('/scm/?group_id='.$group_id).'">';
			$result .= util_make_link('/scm/?group_id='.$group_id, $link_content);

			$hook_params = array () ;
			$hook_params['group_id'] = $group_id ;
			$hook_params['result'] = &$result;
			plugin_hook ("scm_stats", $hook_params) ;
			$result .= "\n</div>\n";
		}

		// ######################### Plugins

		$hook_params = array ();
		$hook_params['group_id'] = $group_id;
		$hook_params['result'] = &$result;
		plugin_hook ("project_public_area", $hook_params);

		// ######################## AnonFTP

		// CB hide FTP if desired
		if ($project->usesFTP()) {
			if ($project->isActive()) {
				$result .= '<div class="public-area-box">'."\n";

				$link_content = $HTML->getFtpPic() . ' ' . _('Anonymous FTP Space');
				//		$result .= '<a rel="doap:anonymous root" href="ftp://' . $project->getUnixName() . '.' . forge_get_config('web_host') . '/pub/'. $project->getUnixName() .'/">';
				if (forge_get_config('use_project_vhost')) {
					$result .= util_make_link('ftp://' . $project->getUnixName() . '.' . forge_get_config('web_host') . '/pub/'. $project->getUnixName(), $link_content, false, true);
				} else {
					$result .= util_make_link('ftp://' . forge_get_config('web_host') . '/pub/'. $project->getUnixName(), $link_content, false, true);
				}
				$result .= "\n</div>\n";
			}
		}

		return $result;
	}

	function canBeUsedByProject(&$project) {
		return true;
	}

	function getDescription() {
		return _('List all available services for this project along with some information next to it. Click on any of this item to access a service.')
             . '<br />'
             . _('The role of this area is pretty much equivalent to the Project Main Menu at the top of the screen except that it shows additional information about each of the service (e.g. total number of open bugs, tasks, ...)');
	}
}

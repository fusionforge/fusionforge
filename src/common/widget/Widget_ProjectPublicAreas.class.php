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
 * Widget_ProjectPublicAreas
 */
class Widget_ProjectPublicAreas extends Widget {
	function Widget_ProjectPublicAreas() {
		$this->Widget('projectpublicareas');
	}
	function getTitle() {
		return _('Public Areas');
	}
	function getContent() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);
		$HTML = $GLOBALS['HTML'];
		// ################# Homepage Link

		echo '<div class="public-area-box" rel="doap:homepage">';
		echo util_make_link ('http://' . $project->getHomePage(), $HTML->getHomePic(_('Home Page')) . ' ' . _('Project Home Page'), false, true);
		echo "</div>\n";

		// ################## ArtifactTypes

		if ($project->usesTracker()) {
			echo '<div class="public-area-box">'."\n";
			$link_content = $HTML->getFollowPic(_('Tracker')) . ' ' . _('Tracker');
			echo util_make_link ( '/tracker/?group_id=' . $group_id, $link_content);

			$result=db_query_params ('SELECT agl.*,aca.count,aca.open_count
					FROM artifact_group_list agl
					LEFT JOIN artifact_counts_agg aca USING (group_artifact_id)
					WHERE agl.group_id=$1
					AND agl.is_public=1
					ORDER BY group_artifact_id ASC',
					array($group_id));

			$rows = db_numrows($result);

			if (!$result || $rows < 1) {
				echo "<br />\n<em>"._('There are no public trackers available').'</em>';
			} else {
				echo "\n".'<ul class="tracker" rel="doap:bug-database">'."\n";
				for ($j = 0; $j < $rows; $j++) {
					// tracker REST paths are something like : /tracker/cm/project/A_PROJECT/atid/NUMBER to plan compatibility
					// with OSLC-CM server API
					$group_artifact_id = db_result($result, $j, 'group_artifact_id');
					$tracker_stdzd_uri = util_make_url('/tracker/cm/project/'. $project->getUnixName() .'/atid/'. $group_artifact_id);
					echo "\t".'<li about="'. $tracker_stdzd_uri . '" typeof="sioc:Container">'."\n";
					print '<span rel="http://www.w3.org/2002/07/owl#sameAs">'."\n";
					echo util_make_link ('/tracker/?atid='. $group_artifact_id . '&amp;group_id='.$group_id.'&amp;func=browse',db_result($result, $j, 'name')) . ' ' ;
					echo "</span>\n"; // /owl:sameAs
					printf(ngettext('(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', '(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', (int) db_result($result, $j, 'open_count')), (int) db_result($result, $j, 'open_count'), (int) db_result($result, $j, 'count'));
					echo '<br />'; //.db_result($result, $j, 'description');
					print '<span rel="sioc:has_space" resource="" ></span>'."\n";
					echo "</li>\n";
				}
				echo "</ul>\n";
			}
			echo "</div>\n";
		}

		// ################## forums

		if ($project->usesForum()) {
			echo '<div class="public-area-box">'."\n";
			//	print '<hr size="1" /><a rel="sioc:container_of" href="'.util_make_url ('/forum/?group_id='.$group_id).'">';
			$link_content = $HTML->getForumPic('') . ' ' . _('Public Forums');
			echo util_make_link ( '/forum/?group_id=' . $group_id, $link_content);
			print ' (';
					$messages_count = project_get_public_forum_message_count($group_id);
					$forums_count = project_get_public_forum_count($group_id);
					printf(ngettext("<strong>%d</strong> message","<strong>%d</strong> messages",$messages_count),$messages_count);
					print ' in ';
					printf(ngettext("<strong>%d</strong> forum","<strong>%d</strong> forums",$forums_count),$forums_count);
					print ')' ;
					print "\n</div>";
					}

					// ##################### Doc Manager

					if ($project->usesDocman()) {
					echo '<div class="public-area-box">'."\n";
					$link_content = $HTML->getDocmanPic('') . ' ' . _('DocManager: Project Documentation');
					//	<a rel="sioc:container_of" xmlns:sioc="http://rdfs.org/sioc/ns#" href="'.util_make_url ('/docman/?group_id='.$group_id).'">';
					print util_make_link( '/docman/?group_id='.$group_id, $link_content);
					echo "\n</div>\n";
					}

					// ##################### Mailing lists

					if ($project->usesMail()) {
						echo '<div class="public-area-box">'."\n";
						$link_content = $HTML->getMailPic('') . ' ' . _('Mailing Lists');
						print util_make_link( '/mail/?group_id='.$group_id, $link_content);
						$n = project_get_mail_list_count($group_id);
						echo ' ';
						printf(ngettext('(<strong>%1$s</strong> public mailing list)', '(<strong>%1$s</strong> public mailing lists)', $n), $n);
						echo "\n</div>\n";
					}

					// ##################### Task Manager

					if ($project->usesPm()) {
						echo '<div class="public-area-box">'."\n";
						$link_content = $HTML->getPmPic('') . ' ' . _('Tasks');
						print util_make_link( '/pm/?group_id='.$group_id, $link_content);

						$result = db_query_params ('SELECT * FROM project_group_list WHERE group_id=$1 AND is_public=1',
								array ($group_id));
						$rows = db_numrows($result);
						if (!$result || $rows < 1) {
							echo "<br />\n<em>"._('There are no public subprojects available').'</em>';
						} else {
							echo "\n".'<ul class="task-manager">';
							for ($j = 0; $j < $rows; $j++) {
								echo "\n\t<li>";
								print util_make_link ('/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').'&amp;group_id='.$group_id.'&amp;func=browse',db_result($result, $j, 'project_name'));
								echo '</li>' ;
							}
							echo "\n</ul>";
						}
						echo "\n</div>\n";
					}

					// ######################### Surveys

					if ($project->usesSurvey()) {
						echo '<div class="public-area-box">'."\n";
						$link_content = $HTML->getSurveyPic('') . ' ' . _('Surveys');
						echo util_make_link( '/survey/?group_id='.$group_id, $link_content);
						echo ' (<strong>'. project_get_survey_count($group_id) .'</strong> ' . _('surveys').')';
								echo "\n</div>\n";
								}

								// ######################### SCM

								if ($project->usesSCM()) {
								echo '<div class="public-area-box">'."\n";

								$link_content = $HTML->getScmPic('') . ' ' . _('SCM Repository');
								//	print '<hr size="1" /><a rel="doap:repository" href="'.util_make_url ('/scm/?group_id='.$group_id).'">';
								print util_make_link( '/scm/?group_id='.$group_id, $link_content);

								$hook_params = array () ;
								$hook_params['group_id'] = $group_id ;
								plugin_hook ("scm_stats", $hook_params) ;
								echo "\n</div>\n";
								}

								// ######################### Plugins

								$hook_params = array ();
								$hook_params['group_id'] = $group_id;
								plugin_hook ("project_public_area", $hook_params);

								// ######################## AnonFTP

								// CB hide FTP if desired
								if ($project->usesFTP()) {
									if ($project->isActive()) {
										echo '<div class="public-area-box">'."\n";

										$link_content = $HTML->getFtpPic('') . ' ' . _('Anonymous FTP Space');
										//		print '<a rel="doap:anonymous root" href="ftp://' . $project->getUnixName() . '.' . forge_get_config('web_host') . '/pub/'. $project->getUnixName() .'/">';
										print util_make_link('ftp://' . $project->getUnixName() . '.' . forge_get_config('web_host') . '/pub/'. $project->getUnixName(), $link_content, false, true);
										echo "\n</div>\n";
									}
								}

								//webcalendar
								plugin_hook("cal_link_group",$group_id);
	}
	function canBeUsedByProject(&$project) {
		return true;
	}
	function getDescription() {
		return _('List all available services for this project along with some information next to it. Click on any of this item to access a service.<br />The role of this area is pretty much equivalent to the Project Main Menu at the top of the screen except that it shows additional information about each of the service (e.g. total number of open bugs, tasks, ...)');
	}


}

?>

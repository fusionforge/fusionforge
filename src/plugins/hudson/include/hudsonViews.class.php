<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright (c) 2010 Alcatel-Lucent
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require_once('common/mvc/Views.class.php');
/*require_once('common/include/HTTPRequest.class.php');
  require_once('common/user/UserManager.class.php');
  require_once('common/project/ProjectManager.class.php');*/
//require_once('common/reference/CrossReferenceFactory.class.php');

require_once('HudsonJob.class.php');
require_once('common/dao/CodendiDataAccess.class.php');
require_once('PluginHudsonJobDao.class.php');

class hudsonViews extends Views {

	function hudsonViews(&$controler, $view=null) {
		$request =& HTTPRequest::instance();
		$this->View($controler, $view, array('title'=>$this->_getTitle(),'group' => $request->get('group_id'), 'toptab' => 'hudson'));
	}

	function header() {
		use_javascript('/scripts/prototype/prototype.js');
		use_javascript('/scripts/scriptaculous/scriptaculous.js');

		parent::header();
//		echo $this->_getHelp();
//		echo '<h1>'.$this->_getTitle().'</h1>';
	}
	function _getTitle() {
//		return _("Continuous Integration");
		return _("Hudson");
	}
	function _getHelp($section = '', $questionmark = false) {
		if (trim($section) !== '' && $section{0} !== '#') {
			$section = '#'.$section;
		}
		if ($questionmark) {
			$help_label = '[?]';
		} else {
			$help_label = _("Help");
		}
		return '<b><a href="javascript:help_window(\''.get_server_url().'help/guide/ContinuousIntegrationIntroduction.html'.$section.'\');">'.$help_label.'</a></b>';
	}
	function footer() {
		parent::footer();
	}

	// {{{ Views
	function projectOverview() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$user = UserManager::instance()->getCurrentUser();

		$this->_display_jobs_table($group_id);       
		if ($user->isMember($request->get('group_id'), 'A')) {
			$this->_display_add_job_form($group_id);
		}
		$this->_display_iframe();
		$this->_hide_iframe();
	}

	function job_details() {
		$myfile=fopen('/tmp/jobdetails','a');
		fwrite($myfile,"\n J'ai fait job detail");
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		if ($request->exist('job_id')) {
			$job_id = $request->get('job_id');
			$dar = $job_dao->searchByJobID($job_id);
		} elseif ($request->exist('job')) {
			// used for references (job #MyJob or job #myproject:MyJob)
			$job_name = $request->get('job');
			$dar = $job_dao->searchByJobName($job_name, $group_id);
		}
		if ($dar->valid()) {
			$row = $dar->current();
			/*fwrite($myfile,"appel de Cross REf Factory avec :".$row['name']);
			$crossref_fact= new CrossReferenceFactory($row['name'], 'hudson_job', $group_id);
			$crossref_fact->fetchDatas();
			if ($crossref_fact->getNbReferences() > 0) {
				echo '<b> '._('References').'</b>';
				$crossref_fact->DisplayCrossRefs();
			}*/
			$this->_display_iframe($row['job_url']);
			fwrite($myfile,"display iframe de ".$row['job_url']);
		} else {
			echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
		}
	}

	function last_build() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$job_id = $request->get('job_id');

		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		$dar = $job_dao->searchByJobID($job_id);
		if ($dar->valid()) {
			$row = $dar->current();
			$this->_display_iframe($row['job_url'].'/lastBuild/');
		} else {
			echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
		}
	}

	function build_number() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		if ($request->exist('build')) {
			$build_id = $request->get('build');
		} else {
			$build_id = $request->get('build_id');
		}
		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		if ($request->exist('job_id')) {
			$job_id = $request->get('job_id');
			$dar = $job_dao->searchByJobID($job_id);
		} elseif ($request->exist('job')) {
			// used for references (build #MyJob/175 or job #myproject:MyJob/175 where 175 is the build number required)
			$job_name = $request->get('job');
			$dar = $job_dao->searchByJobName($job_name, $group_id);
		} else {
			// used for references (build #175 where 175 is the build number required)
			// If no job or project is specified, we check if there is only one job associated to the current project and we assume it is this job.
			$dar = $job_dao->searchByGroupID($group_id);
			if ($dar->rowCount() != 1) {
				$dar = null;
			}
		}

		if ($dar && $dar->valid()) {
			$row = $dar->current();
//			$crossref_fact= new CrossReferenceFactory($row['name'].'/'.$build_id, 'hudson_build', $group_id);
//			$crossref_fact->fetchDatas();
//			if ($crossref_fact->getNbReferences() > 0) {
//				echo '<b> '._('References').'</b>';
//				$crossref_fact->DisplayCrossRefs();
//			}
			$this->_display_iframe($row['job_url'].'/'.$build_id.'/');
		} else {
			echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
		}
	}

	function last_test_result() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$job_id = $request->get('job_id');
		$user = UserManager::instance()->getCurrentUser();

		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		$dar = $job_dao->searchByJobID($job_id);
		if ($dar->valid()) {
			$row = $dar->current();
			$this->_display_iframe($row['job_url'].'/lastBuild/testReport/');
		} else {
			echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
		}
	}

	function test_trend() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$job_id = $request->get('job_id');
		$user = UserManager::instance()->getCurrentUser();

		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		$dar = $job_dao->searchByJobID($job_id);
		if ($dar->valid()) {
			$row = $dar->current();
			$this->_display_iframe($row['job_url'].'/test/?width=800&height=600&failureOnly=false');
		} else {
			echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
		}
	}

	function editJob() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$job_id = $request->get('job_id');
		$user = UserManager::instance()->getCurrentUser();
		if ($user->isMember($group_id, 'A')) {

			$project_manager = ProjectManager::instance();
			$project = $project_manager->getProject($group_id);

			$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
			$dar = $job_dao->searchByJobID($job_id);
			if ($dar->valid()) {
				$row = $dar->current();

				echo '<a href="/plugins/hudson/?group_id='.$group_id.'">'._("Back to jobs list").'</a>';

				echo '<h3>'._("Edit job").'</h3>';
				echo ' <form method="post">';
				echo '  <p>';
				echo '   <label for="new_hudson_job_url">'._("Job URL:").'</label>';
				echo '   <input id="new_hudson_job_url" name="new_hudson_job_url" type="text" value="'.$row['job_url'].'" size="64" />';
				echo '  </p>';
				echo '  <p>';
				echo '   <span class="legend">'._("eg: http://myCIserver/hudson/job/myJob").'</span>';
				echo '  </p>';
				echo '  <p>';
				echo '   <label for="new_hudson_job_name">'._("Job name:").'</label>';
				echo '   <input id="new_hudson_job_name" name="new_hudson_job_name" type="text" value="'.$row['name'].'" size="32" />';
				echo '  </p>';
				echo '  <p>';
				echo '   <span class="legend">'.vsprintf(_("Name (with no space) used to make a reference to this job. Eg: job #%s"),  array($row['name'])).'</span>';
				echo '  </p>';
				if ($project->usesService('svn')) {
					echo '  <p>';
					echo '   <label for="new_hudson_use_svn_trigger">'._("Trigger a build after SVN commits:").'</label>';
					if ($row['use_svn_trigger'] == 1) {
						$checked = ' checked="checked" ';
					} else {
						$checked = '';
					}
					echo '   <input id="new_hudson_use_svn_trigger" name="new_hudson_use_svn_trigger" type="checkbox" '.$checked.' />';
					echo '  </p>';
				}
				if ($project->usesService('cvs')) {
					echo '  <p>';
					echo '   <label for="new_hudson_use_cvs_trigger">'._("Trigger a build after CVS commits:").'</label>';
					if ($row['use_cvs_trigger'] == 1) {
						$checked = ' checked="checked" ';
					} else {
						$checked = '';
					}
					echo '   <input id="new_hudson_use_cvs_trigger" name="new_hudson_use_cvs_trigger" type="checkbox" '.$checked.' />';
					echo '  </p>';
				}
				if ($project->usesService('svn') || $project->usesService('cvs')) {
					echo '  <p>';
					echo '   <label for="new_hudson_trigger_token">'._("with (optional) token:").'</label>';
					echo '   <input id="new_hudson_trigger_token" name="new_hudson_trigger_token" type="text" value="'.$row['token'].'" size="32" />';
					echo '  </p>';
				}
				echo '  <p>';
				echo '   <input type="hidden" name="group_id" value="'.$group_id.'" />';
				echo '   <input type="hidden" name="job_id" value="'.$job_id.'" />';
				echo '   <input type="hidden" name="action" value="update_job" />';
				echo '   <input type="submit" value="'._("Update job").'" />';
				echo '  </p>';
				echo ' </form>';

			} else {

			}
		} else {

		}
	}
	// }}}

	function _display_jobs_table($group_id) {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$user = UserManager::instance()->getCurrentUser();
		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		$dar = $job_dao->searchByGroupID($group_id);

		if ($dar && $dar->valid()) {

			$project_manager = ProjectManager::instance();
			$project = $project_manager->getProject($group_id);

			echo '<table id="jobs_table">';
			echo ' <tr class="boxtable">';
			echo '  <th class="boxtitle">&nbsp;</th>';
			echo '  <th class="boxtitle">'._("Job").'</th>';
			echo '  <th class="boxtitle">'._("Last Success").'</th>';
			echo '  <th class="boxtitle">'._("Last Failure").'</th>';
			echo '  <th class="boxtitle">'._("RSS").'</th>';
			if ($project->usesService('svn')) {
				echo '  <th class="boxtitle">'._("SVN trigger").'</th>';
			}
			if ($project->usesService('cvs')) {
				echo '  <th class="boxtitle">'._("CVS trigger").'</th>';
			}
			if ($user->isMember($request->get('group_id'), 'A')) {
				echo '  <th class="boxtitle">'._("Actions").'</th>';
			}
			echo ' </tr>';

			$cpt = 1;
			while ($dar->valid()) {
				$row = $dar->current();
				if ($cpt % 2 == 0) {
					$class="boxitemalt bgcolor-white";
				} else {
					$class="boxitem bgcolor-grey";
				}
				echo ' <tr class="'. $class .'">';

				try {
					$job = new HudsonJob($row['job_url']);

					echo '  <td><img src="'.$job->getStatusIcon().'" alt="'.$job->getStatus().'" title="'.$job->getStatus().'" /></td>';
					// function toggle_iframe is in script plugins/hudson/www/hudson_tab.js
					echo '  <td class="boxitem"><a href="'.$job->getUrl().'" onclick="toggle_iframe(this); return false;" title="'.vsprintf(_("Show job %s"),  array($row['name'])).'">'.$row['name'].'</a></td>';
					if ($job->getLastSuccessfulBuildNumber() != '') {
						echo '  <td><a href="'.$job->getLastSuccessfulBuildUrl().'" onclick="toggle_iframe(this); return false;" title="'.vsprintf(_("Show build #%s of job %s"),  array($job->getLastSuccessfulBuildNumber(), $row['name'])).'">'._("build").' #'.$job->getLastSuccessfulBuildNumber().'</a></td>';
					} else {
						echo '  <td>&nbsp;</td>';
					}
					if ($job->getLastFailedBuildNumber() != '') {
						echo '  <td><a href="'.$job->getLastFailedBuildUrl().'" onclick="toggle_iframe(this); return false;" title="'.vsprintf(_("Show build #%s of job %s"),  array($job->getLastFailedBuildNumber(), $row['name'])).'">'._("build").' #'.$job->getLastFailedBuildNumber().'</a></td>';
					} else {
						echo '  <td>&nbsp;</td>';
					}
					echo '  <td align="center"><a href="'.$job->getUrl().'/rssAll" onclick="toggle_iframe(this); return false;"><img src="'.$this->getControler()->getIconsPath().'rss_feed.png" alt="'.vsprintf(_("RSS feed of all builds for %s job"),  array($row['name'])).'" title="'.vsprintf(_("RSS feed of all builds for %s job"),  array($row['name'])).'" /></a></td>'."\n";

					if ($project->usesService('svn')) {
						if ($row['use_svn_trigger'] == 1) {
							echo '  <td align="center"><img src="'.$this->getControler()->getIconsPath().'server_lightning.png" alt="'._("SVN commit will trigger a build").'" title="'._("SVN commit will trigger a build").'" /></td>';
						} else {
							echo '  <td>&nbsp;</td>';
						}
					}
					if ($project->usesService('cvs')) {
						if ($row['use_cvs_trigger'] == 1) {
							echo '  <td align="center"><img src="'.$this->getControler()->getIconsPath().'server_lightning.png" alt="'._("CVS commit will trigger a build").'" title="'._("CVS commit will trigger a build").'" /></td>';
						} else {
							echo '  <td>&nbsp;</td>';
						}
					}

				} catch (Exception $e) {
					echo '  <td><img src="'.$this->getControler()->getIconsPath().'link_error.png" alt="'.$e->getMessage().'" title="'.$e->getMessage().'" /></td>';
					$nb_columns = 4;
					if ($project->usesService('svn')) { $nb_columns++; }
					if ($project->usesService('cvs')) { $nb_columns++; }
					echo '  <td colspan="'.$nb_columns.'"><span class="error">'.$e->getMessage().'</span></td>';
				}

				if ($user->isMember($request->get('group_id'), 'A')) {
					echo '  <td>';
					// edit job
					echo '   <span class="job_action">';
					echo '    <a href="?action=edit_job&amp;group_id='.$group_id.'&amp;job_id='.$row['job_id'].'"><img src="'.$this->getControler()->getIconsPath().'edit.png" alt="'._("Edit this job").'" title="'._("Edit this job").'" />';
					echo '</a>';
					echo '   </span>';
					// delete job
					echo '   <span class="job_action">';
					echo '    <a href="?action=delete_job&amp;group_id='.$group_id.'&amp;job_id='.$row['job_id'].'" onclick="return confirm(';
					echo "'" . vsprintf(_("Are you sure you want to delete Job %s from project %s?"),  array($row['name'], $project->getUnixName())) . "'";
					echo ');"><img src="'.$this->getControler()->getIconsPath().'cross.png" alt="'._("Delete this job").'" title="'._("Delete this job").'" />';
					echo '</a>';
					echo '   </span>';
					echo '  </td>';
				}

				echo ' </tr>';

				$dar->next();
				$cpt++;
			}
			echo '</table>';   
		} else {
			echo '<p>'._("No Hudson jobs associated with this project. To add a job, select the link just below.").'</p>';
		}
	}

	function _display_add_job_form($group_id) {
		$project_manager = ProjectManager::instance();
		$project = $project_manager->getProject($group_id);

		// function toggle_addurlform is in script plugins/hudson/www/hudson_tab.js
		echo '<a href="#" onclick="toggle_addurlform(); return false;">' . getIcon("ic/add.png") . ' '._("Add job").'</a>';
		echo ' '.$this->_getHelp('HudsonService', true);
		echo '<div id="hudson_add_job">';
		echo ' <form action="">'."\n";
		echo '   <label for="hudson_job_url">'._("Job URL:").'</label>'."\n";
		echo '   <input id="hudson_job_url" name="hudson_job_url" type="text" size="64" />'."\n";
		echo '   <input type="hidden" name="group_id" value="'.$group_id.'" />'."\n";
		echo '   <input type="hidden" name="action" value="add_job" />'."\n";
		echo '   <br />'."\n";
		echo '   <span class="legend">'._("eg: http://myCIserver/hudson/job/myJob").'</span>'."\n";
		echo '   <br />';
		//echo '  <p>';
		if ($project->usesService('svn')) {
			echo _("Trigger a build after commits:");
			if ($project->usesService('svn')) {
				echo '   <label for="hudson_use_svn_trigger">'._("SVN").'</label>';
				echo '   <input id="hudson_use_svn_trigger" name="hudson_use_svn_trigger" type="checkbox" />';
			}
			if ($project->usesService('cvs')) {
				echo '   <label for="hudson_use_cvs_trigger">'._("CVS").'</label>';
				echo '   <input id="hudson_use_cvs_trigger" name="hudson_use_cvs_trigger" type="checkbox" />';
			}
			//echo '  </p>';
			//echo '  <p>';
			echo '   <label for="hudson_trigger_token">'._("with (optional) token:").'</label>';
			echo '   <input id="hudson_trigger_token" name="hudson_trigger_token" type="text" size="32" />';
			//echo '  </p>';
			echo '   <br />';
		}
		echo '   <input type="submit" value="Add job" />';
		echo ' </form>';
		echo '</div>';
		echo "<script type=\"text/javascript\">Element.toggle('hudson_add_job', 'slide');</script>\n";
	}

	function _display_iframe($url = '') {
		echo '<div id="hudson_iframe_div">';
		htmlIframe($url, array('id' => 'hudson_iframe', 'class' => 'iframe_service'));
		echo '</div>';
	}
	function _hide_iframe() {
		echo "<script type=\"text/javascript\">Element.toggle('hudson_iframe_div', 'slide');</script>";
	}
}


?>

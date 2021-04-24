<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2013-2014,2021, Franck Villaume - TrivialDev
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

require_once 'common/mvc/Controler.class.php';
require_once 'hudsonViews.class.php';
require_once 'hudsonActions.class.php';
/**
 * hudson
 */
class hudson extends Controler {

	private $themePath;

	function hudson() {
		$p = PluginManager::instance()->getPluginByName('hudson');
		$this->themePath = '/'.$p->getThemePath();
	}

	function getThemePath() {
		return $this->themePath;
	}

	function getIconsPath() {
		return $this->themePath.'/images/ic/';
	}

	function request() {
		global $feedback, $error_msg;
		$group_id = getFilteredIntFromRequest('group_id', '\d');
		$project = group_get_object($group_id);
		if ($project->usesService('hudson')) {
			$user = session_get_user();
			if (forge_check_perm('plugin_hudson_read', $group_id, 'read')) {
				$action = getStringFromRequest('action');
				switch($action) {
						case 'add_job':
							if ($user->isMember($group_id, 'A')) {
								if (existInRequest('hudson_job_url') && (getStringFromRequest('hudson_job_url') != '')) {
									$this->action = 'addJob';
								} else {
									$error_msg .= _('Missing Hudson job url (eg: http://myCIserver:8080/hudson/job/MyJob)');
								}
								$this->view = 'projectOverview';
							} else {
								$error_msg .= _('Permission denied.');
								$this->view = 'projectOverview';
							}
							break;
						case 'edit_job':
							if ($user->isMember($group_id,'A')) {
								if (existInRequest('job_id')) {
									$this->view = 'editJob';
								} else {
									$error_msg .= _('Missing Hudson job ID');
								}
							} else {
								$error_msg .= _('Permission denied.');
								$this->view = 'projectOverview';
							}
							break;
						case 'update_job':
							if ($user->isMember($group_id, 'A')) {
								if (existInRequest('job_id')) {
									if (existInRequest('new_hudson_job_url') && (getStringFromRequest('new_hudson_job_url') != '')) {
										$this->action = 'updateJob';
									} else {
										$error_msg .= _('Missing Hudson job url (eg: http://myCIserver:8080/hudson/job/MyJob)');
									}
								} else {
									$error_msg .= _('Missing Hudson job ID');
								}
								$this->view = 'projectOverview';
							} else {
								$error_msg .= _('Permission denied.');
								$this->view = 'projectOverview';
							}
							break;
						case 'delete_job':
							if ($user->isMember($group_id, 'A')) {
								if (existInRequest('job_id')) {
									$this->action = 'deleteJob';
								} else {
									$error_msg .= _('Missing Hudson job ID');
								}
								$this->view = 'projectOverview';
							} else {
								$error_msg .= _('Permission denied.');
								$this->view = 'projectOverview';
							}
							break;
						case "view_job":
							$this->view = 'job_details';
							break;
						case "view_build":
							$this->view = 'build_number';
							break;
						case "view_last_build":
							$this->view = 'last_build';
							break;
						case "view_last_test_result":
							$this->view = 'last_test_result';
							break;
						case "view_test_trend":
							$this->view = 'test_trend';
							break;
						default:
							$this->view = 'projectOverview';
							break;
				}
			} else {
				$error_msg .= _('Permission denied.');
			}
		} else {
			$error_msg .= _('Hudson service is not enabled.');
		}
	}
}

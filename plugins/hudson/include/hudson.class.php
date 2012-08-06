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

require_once('common/mvc/Controler.class.php');
require_once('hudsonViews.class.php');
require_once('hudsonActions.class.php');
/**
 * hudson */
class hudson extends Controler {

    private $themePath;

    function hudson() {
        $p = PluginManager::instance()->getPluginByName('hudson');
        $this->themePath = $p->getThemePath();
    }

    function getThemePath() {
        return $this->themePath;
    }
    function getIconsPath() {
        return $this->themePath . "/images/ic/";
    }

    function request() {
		global $feedback, $error_msg;
        $request =& HTTPRequest::instance();
        $vgi = new Valid_GroupId();
        $vgi->required();
        if ($request->valid($vgi)) {
            $group_id = $request->get('group_id');
            $pm = ProjectManager::instance();
            $project = $pm->getProject($group_id);
            if ($project->usesService('hudson')) {
                $user = UserManager::instance()->getCurrentUser();
                if ($user->isMember($group_id)) {
                    switch($request->get('action')) {
                        case 'add_job':
                            if ($user->isMember($group_id, 'A')) {
                                if ( $request->exist('hudson_job_url') && trim($request->get('hudson_job_url') != '') ) {
                                    $this->action = 'addJob';
                                } else {
                                    $error_msg .= _("Missing Hudson job url (eg: http://myCIserver:8080/hudson/job/MyJob)");
                                }
                                $this->view = 'projectOverview';
                            } else {
                                $error_msg .= _("Permission Denied");
                                $this->view = 'projectOverview';
                            }
                            break;
                        case 'edit_job':
                            if ($user->isMember($group_id,'A')) {
                                if ($request->exist('job_id')) {
                                    $this->view = 'editJob';
                                } else {
                                    $error_msg .= _("Missing Hudson job ID");
                                }
                            } else {
                                $error_msg .= _("Permission Denied");
                                $this->view = 'projectOverview';
                            }
                            break;
                        case 'update_job':
                            if ($user->isMember($group_id,'A')) {
                                if ($request->exist('job_id')) {
                                    if ($request->exist('new_hudson_job_url') && $request->get('new_hudson_job_url') != '') {
                                        $this->action = 'updateJob';
                                    } else {
                                        $error_msg .= _("Missing Hudson job url (eg: http://myCIserver:8080/hudson/job/MyJob)");
                                    }
                                } else {
                                    $error_msg .= _("Missing Hudson job ID");
                                }
                                $this->view = 'projectOverview';
                            } else {
                                $error_msg .= _("Permission Denied");
                                $this->view = 'projectOverview';
                            }
                            break;
                        case 'delete_job':
                            if ($user->isMember($group_id,'A')) {
                                if ($request->exist('job_id')) {
                                    $this->action = 'deleteJob';
                                } else {
                                    $error_msg .= _("Missing Hudson job ID");
                                }
                                $this->view = 'projectOverview';
                            } else {
                                $error_msg .= _("Permission Denied");
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
                    $error_msg .= _("Permission Denied");
                }

            } else {
                $error_msg .= _("Hudson service is not enabled");
            }
        } else {
            $error_msg .= _("Missing group_id parameter.");
        }
    }
}

?>

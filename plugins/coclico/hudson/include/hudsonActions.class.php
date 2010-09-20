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


require_once('common/mvc/Actions.class.php');
require_once('common/include/HTTPRequest.class.php');

require_once('HudsonJob.class.php');
require_once('PluginHudsonJobDao.class.php');

/**
 * hudsonActions
 */
class hudsonActions extends Actions {
    function hudsonActions(&$controler, $view=null) {
        $this->Actions($controler);
	}
	
	// {{{ Actions
    function addJob() {
        global $feedback, $error_msg;
        $request =& HTTPRequest::instance();
        $group_id = $request->get('group_id');
        $job_url = $request->get('hudson_job_url');
        try {
            $job = new HudsonJob($job_url);
            $use_svn_trigger = ($request->get('hudson_use_svn_trigger') === 'on');
            $use_cvs_trigger = ($request->get('hudson_use_cvs_trigger') === 'on');
            if ($use_svn_trigger || $use_cvs_trigger) {
                $token = $request->get('hudson_trigger_token');
            } else {
                $token = null;
            }
            $job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
            if ( ! $job_dao->createHudsonJob($group_id, $job_url, $job->getName(), $use_svn_trigger, $use_cvs_trigger, $token)) {
                $error_msg .= _("Unable to add Hudson job.");
            } else {
                $feedback .= _("Hudson job added.");
                $feedback .= ' '._('Please wait 1 hour for triggers to be updated.');
            }
        } catch (Exception $e) {
            $error_msg .= $e->getMessage();
        }
    }
    function updateJob() {
        global $feedback, $error_msg;
        $request =& HTTPRequest::instance();
        $group_id = $request->get('group_id');
        $job_id = $request->get('job_id');
        $new_job_url = $request->get('new_hudson_job_url');
        $new_job_name = $request->get('new_hudson_job_name');
        if (strpos($new_job_name, " ") !== false) {
            $new_job_name = str_replace(" ", "_", $new_job_name);
            $error_msg .= _('Spaces are not allowed in job name. They were replaced by "_".');
        }
        $new_use_svn_trigger = ($request->get('new_hudson_use_svn_trigger') === 'on');
        $new_use_cvs_trigger = ($request->get('new_hudson_use_cvs_trigger') === 'on');
        if ($new_use_svn_trigger || $new_use_cvs_trigger) {
            $new_token = $request->get('new_hudson_trigger_token');
        } else {
            $new_token = null;
        }
        $job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
        if ( ! $job_dao->updateHudsonJob($job_id, $new_job_url, $new_job_name, $new_use_svn_trigger, $new_use_cvs_trigger, $new_token)) {
            $error_msg .= _("Unable to update Hudson job");
        } else {
            $feedback .= _("Hudson job updated.");
            $feedback .= ' '._('Please wait 1 hour for triggers to be updated.');
        }
    }
    function deleteJob() {
        global $feedback, $error_msg;
        $request =& HTTPRequest::instance();
        $group_id = $request->get('group_id');
        $job_id = $request->get('job_id');
        $job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
        if ( ! $job_dao->deleteHudsonJob($job_id)) {
            $error_msg .= _("Unable to delete Hudson job");
        } else {
            $feedback .= _("Hudson job deleted.");
        }
    }
    // }}}
   
}

?>

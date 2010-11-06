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


require_once('HudsonJobWidget.class.php');
require_once('common/include/HTTPRequest.class.php');
require_once('PluginHudsonJobDao.class.php');
require_once('HudsonJob.class.php');

require_once('HudsonTestResult.class.php');

class hudson_Widget_JobTestTrend extends HudsonJobWidget {
    
    function hudson_Widget_JobTestTrend($owner_type, $owner_id) {
        $request =& HTTPRequest::instance();
        if ($owner_type == WidgetLayoutManager::OWNER_TYPE_USER) {
            $this->widget_id = 'plugin_hudson_my_jobtesttrend';
            $this->group_id = $owner_id;
        } else {
            $this->widget_id = 'plugin_hudson_project_jobtesttrend';
            $this->group_id = $request->get('group_id');
        }
        $this->Widget($this->widget_id);
        
        $this->setOwner($owner_id, $owner_type);
    }
    
    function getTitle() {
        if ($this->job) {
            return sprintf(_('%s Test Result Trend'), $this->job->getName());
        } else {
            return _('Test Result Trend');
        }
    }
    
    function getDescription() {
        return _("Show the test result trend for the selected job. To display something, your job needs to have tests. The graph will show the number of tests (failed and successfull) along  time. The number of tests is increasing while the number of build and commits are increasing too.");
    }
    
    function loadContent($id) {
        $sql = "SELECT * FROM plugin_hudson_widget WHERE widget_name=$1 AND owner_id=$2 AND owner_type=$3 AND id=$4";
        $res = db_query_params($sql,array($this->widget_id,$this->owner_id,$this->owner_type,$id));
        if ($res && db_numrows($res)) {
            $data = db_fetch_array($res);
            $this->job_id    = $data['job_id'];
            $this->content_id = $id;
            
            $jobs = $this->getAvailableJobs();
            
            if (array_key_exists($this->job_id, $jobs)) {
                $used_job = $jobs[$this->job_id];
                $this->job_url = $used_job->getUrl();
                $this->job = $used_job;
                
                try {
                    $this->test_result = new HudsonTestResult($this->job_url);
                } catch (Exception $e) {
                    $this->test_result = null;
                }
                
            } else {
                $this->job = null;
                $this->test_result = null;
            }
            
        }
    }
    
    function getContent() {
        $html = '';
        if ($this->job != null && $this->test_result != null) {

            $job = $this->job;
            
            $html .= '<div style="padding: 20px;">';
            $html .= '<a href="/plugins/hudson/?action=view_test_trend&group_id='.$this->group_id.'&job_id='.$this->job_id.'">';
            $html .= '<img src="'.$job->getUrl().'/test/trend?width=320&height=240" alt="'.vsprintf(_("%s Test Result Trend"),  array($this->job->getName())).'" title="'.vsprintf(_("%s Test Result Trend"),  array($this->job->getName())).'" />';
            $html .= '</a>';
            $html .= '</div>';

        } else {
            if ($this->job != null) {
                $html .= _("No test found for this job.");
            } else {
                $html .= _("Job not found.");
            }
        }
            
        return $html;
    }
}

?>

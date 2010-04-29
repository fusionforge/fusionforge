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
require_once('HudsonWidget.class.php');
require_once('common/widget/Widget.class.php');
require_once('PluginHudsonJobDao.class.php');

abstract class HudsonJobWidget extends HudsonWidget {
    
    var $widget_id;
    var $group_id;
    
    var $job;
    var $job_url;
    var $job_id;
    
    function isUnique() {
        return false;
    }
    
    function create(&$request) {
        $content_id = false;
        $vId = new Valid_Uint('job_id');
        $vId->setErrorMessage("Can't add empty job id");
        $vId->required();
        if ($request->valid($vId)) {
            $job_id = $request->get('job_id');
            $sql = 'INSERT INTO plugin_hudson_widget (widget_name, owner_id, owner_type, job_id) VALUES ("' . $this->id . '", '. $this->owner_id .", '". $this->owner_type ."', " . db_escape_int($job_id) ." )";
            $res = db_query($sql);
            $content_id = db_insertid($res);
        }
        return $content_id;
    }
    
    function destroy($id) {
        $sql = 'DELETE FROM plugin_hudson_widget WHERE id = '. $id .' AND owner_id = '. $this->owner_id ." AND owner_type = '". $this->owner_type ."'";
        db_query($sql);
    }
    
    function getInstallPreferences() {
        $prefs  = '';
        $prefs .= '<strong>'._("Monitored job:").'</strong><br />';
        
        $jobs = $this->getAvailableJobs();
        
        foreach ($jobs as $job_id => $job) {
            $prefs .= '<input type="radio" name="job_id" value="'.$job_id.'"> '.$job->getName().'<br />';
        }
        return $prefs;
    }
    function hasPreferences() {
        return true;
    }
    function getPreferences() {
        $prefs  = '';
        $prefs .= '<strong>'._("Monitored job:").'</strong><br />';
        
        $jobs = $this->getAvailableJobs();
        
        foreach ($jobs as $job_id => $job) {
            $selected = ($job_id == $this->job_id)?'checked="checked"':'';
            $prefs .= '<input type="radio" name="' . $this->id . '" value="'.$job_id.'" ' . $selected . '> '.$job->getName().'<br />';
        }
        return $prefs;
    }
    
    function updatePreferences(&$request) {
        $request->valid(new Valid_String('cancel'));
        if (!$request->exist('cancel')) {
            $job_id = $request->get($this->id);
            $sql = "UPDATE plugin_hudson_widget SET job_id=". $job_id ." WHERE owner_id = ". $this->owner_id ." AND owner_type = '". $this->owner_type ."' AND id = ". (int)$request->get('content_id');
            $res = db_query($sql); 
        }
        return true;
    }
    
}

?>
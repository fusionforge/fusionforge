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
require_once('hudson.class.php');
require_once('HudsonJobURLMalformedException.class.php');
require_once('HudsonJobURLFileException.class.php');
require_once('HudsonJobURLFileNotFoundException.class.php');
 
class HudsonJob {

    protected $hudson_job_url;
    protected $hudson_dobuild_url;
    protected $hudson_config_job_url;
    protected $dom_job;
    protected $config_job;
    private $icons_path;
    
    private $context;
        
    /**
     * Construct an Hudson job from a job URL
     */
    function HudsonJob($hudson_job_url) {
        $parsed_url = parse_url($hudson_job_url);
        
        if ( ! $parsed_url || ! array_key_exists('scheme', $parsed_url) ) {
            throw new HudsonJobURLMalformedException(vsprintf(_("Wrong Job URL: %s"),  array($hudson_job_url)));
        }
                
        $this->hudson_job_url = $hudson_job_url . "/api/xml";
        $this->hudson_dobuild_url = $hudson_job_url . "/build";
        $this->hudson_config_job_url = $hudson_job_url . "/config.xml";
        
        $controler = $this->getHudsonControler(); 
        $this->icons_path = $controler->getIconsPath();
        
        $this->_setStreamContext();
        
        $this->buildJobObject();
        
    }
    function getHudsonControler() {
        return new hudson();
    }
    
    public function buildJobObject() {
        $this->dom_job = $this->_getXMLObject($this->hudson_job_url);
    }
    
    public function configJobObject() {
	if ($this->config_job) {
	    return;
	}
        $this->config_job = $this->_getXMLObject($this->hudson_config_job_url);
    }
    
    protected function _getXMLObject($hudson_job_url) {

        // If enabled, use APC cache (1sec) to reduce RSS fetching that may cause big delays.
        if (function_exists('apc_fetch')) {
            $xmlstr = apc_fetch($hudson_job_url);
            if ($xmlstr === false) {
        		$xmlstr = @file_get_contents($hudson_job_url, false, $this->context);
                apc_store($hudson_job_url, $xmlstr, 1);
            }
        } else {
            $xmlstr = @file_get_contents($hudson_job_url, false, $this->context);
        }

        if ($xmlstr !== false) {
            $xmlobj = simplexml_load_string($xmlstr);
            if ($xmlobj !== false) {
                return $xmlobj;
            } else {
                throw new HudsonJobURLFileException(vsprintf(_("Unable to read file at URL: %s"),  array($hudson_job_url)));
            }
        } else {
            throw new HudsonJobURLFileNotFoundException(vsprintf(_("File not found at URL: %s"),  array($hudson_job_url))); 
        }
    }
    
    private function _setStreamContext() {
        if (array_key_exists('sys_proxy', $GLOBALS) && $GLOBALS['sys_proxy']) {
            $context_opt = array(
                'http' => array(
                    'method' => 'GET',
                    'proxy' => $GLOBALS['sys_proxy'],
                    'request_fulluri' => True,
                    'timeout' => 5.0,
                ),
            );
            $this->context = stream_context_create($context_opt);
        } else {
            $this->context = null;
        }
    }
    
    function getProjectStyle() {
        return $this->dom_job->getName();
    }
    function getName() {
        return $this->dom_job->name;
    }
    function getUrl() {
        return $this->dom_job->url;
    }
    function getColor() {
        return $this->dom_job->color;
    }
    function getColorNoAnime() {
        $color = $this->getColor();
        if (strpos($color, "_anime")) {
            $color = substr($color, 0, strpos($color, "_anime"));
        }
        return $color;
    }
    function getStatus() {
        switch ($this->getColor()) {
            case "blue":
                // The last build was successful.
                return _("Success");
                break;
            case "blue_anime":
                // The last build was successful. A new build is in progress.
                return _("In progress");
                break;
            case "yellow":
                // The last build was successful but unstable. This is primarily used to represent test failures.
                return _("Unstable"); 
                break;
            case "yellow_anime":
                // The last build was successful but unstable. This is primarily used to represent test failures. A new build is in progress.
                return _("In progress"); 
                break;
            case "red":
                // The last build fatally failed.
                return _("Failure");
                break;
            case "red_anime":
                // The last build fatally failed. A new build is in progress.
                return _("In progress");
                break;
            case "grey":
                // The project has never been built before, or the project is disabled.
                return _("Pending");
                break;
            case "grey_anime":
                // The project has never been built before, or the project is disabled. The first build of this project is in progress.
                return _("In progress");
                break;
            default:
                // Can we have anime icons here?
                return _("Unknown status");
                break;
        }
    }
    
    function getIconsPath() {
        return $this->icons_path;
    }
    function getStatusIcon() {
        switch ($this->getColor()) {
            case "blue":
                // The last build was successful.
                return $this->getIconsPath()."status_blue.png";
                break;
            case "blue_anime":
                // The last build was successful. A new build is in progress.
                return $this->getIconsPath()."status_blue.png";
                break;
            case "yellow":
                // The last build was successful but unstable. This is primarily used to represent test failures.
                return $this->getIconsPath()."status_yellow.png"; 
                break;
            case "yellow_anime":
                // The last build was successful but unstable. A new build is in progress.
                return $this->getIconsPath()."status_yellow.png";
                break;
            case "red":
                // The last build fatally failed.
                return $this->getIconsPath()."status_red.png";
                break;
            case "red_anime":
                // The last build fatally failed. A new build is in progress.
                return $this->getIconsPath()."status_red.png";
                break;
            case "grey":
                // The project has never been built before, or the project is disabled.
                return $this->getIconsPath()."status_grey.png";
                break;
            case "grey_anime":
                // The first build of the project is in progress.
                return $this->getIconsPath()."status_grey.png";
                break;
            default:
                // Can we have anime icons here?
                return $this->getIconsPath()."status_unknown.png";
                break;
        }
    }
    
    function isBuildable() {
        return ($this->dom_job->buildable == "true");
    }
    
    function hasBuilds() {
        return ((int)$this->getLastBuildNumber() !== 0); 
    }
    
    function getLastBuildNumber() {
        return $this->dom_job->lastBuild->number;
    }
    function getLastBuildUrl() {
        return $this->dom_job->lastBuild->url;
    }
    
    function getLastSuccessfulBuildNumber() {
        return $this->dom_job->lastSuccessfulBuild->number;
    }
    function getLastSuccessfulBuildUrl() {
        return $this->dom_job->lastSuccessfulBuild->url;
    }
    
    function getLastFailedBuildNumber() {
        return $this->dom_job->lastFailedBuild->number;
    }
    function getLastFailedBuildUrl() {
        return $this->dom_job->lastFailedBuild->url;
    }
    
    function getNextBuildNumber() {
        return $this->dom_job->nextBuildNumber;
    }
    
    function getHealthScores() {
        $scores = array();
        foreach ($this->dom_job->healthReport as $health_report) {
            $scores[] = $health_report->score;
        }
        return $scores;
    }
    function getHealthDescriptions() {
        $descs = array();
        foreach ($this->dom_job->healthReport as $health_report) {
            $scores[] = $health_report->description;
        }
        return $descs;
    }
    function getHealthAverageScore() {
        $arr = $this->getHealthScores();
        $sum = 0;
        foreach ($arr as $score) {
            $sum += (int)$score;
        }
        $num = sizeof($arr);
        if ($num != 0) {
            return floor($sum/$num);
        } else {
            return null;
        }
    }
    
    function getWeatherReportIcon() {
        $score = $this->getHealthAverageScore();
        if ($score >= 80) {
            return $this->getIconsPath()."health_80_plus.gif";
        } elseif ($score >= 60) {
            return $this->getIconsPath()."health_60_to_79.gif";
        } elseif ($score >= 40) {
            return $this->getIconsPath()."health_40_to_59.gif";
        } elseif ($score >= 20) {
            return $this->getIconsPath()."health_20_to_39.gif";
        } else {
            return $this->getIconsPath()."health_00_to_19.gif";
        }
    }
    
    function getSvnLocation() {
        $this->configJobObject();
        return $this->config_job->scm->locations->{'hudson.scm.SubversionSCM_-ModuleLocation'}->remote;
    }
    
    /**
     * Launch a Build for this job on the Continuous Integration server.
     * 
     * @exception if unable to open build URL or if response is an error
     *  
     * @param string $token if CI server has activated security (login/password), then a token is mandatory to build jobs. This token is defined in the job configuration.
     * @return response of build call.
     */
    function launchBuild($token = null) {
        $url = $this->hudson_dobuild_url;
        if ($token != null) {
            $url .= '?token='.$token;
        }
        $params = array('http' => array(
                     'method' => 'POST',
                     'content' => ''
                ));
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url");
        }
        $response = stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $url");
        }
        return $response;
    }
    
}

?>

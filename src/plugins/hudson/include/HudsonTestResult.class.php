<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2013-2014,2016, Franck Villaume - TrivialDev
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

require_once 'hudson.class.php';
require_once 'HudsonJobURLMalformedException.class.php';
require_once 'HudsonJobURLFileException.class.php';
require_once 'HudsonJobURLFileNotFoundException.class.php';

class HudsonTestResult {

	protected $hudson_test_result_url;
	protected $dom_job;
	private $context;

	/**
	 * Construct an Hudson job from a job URL
	 */
	function HudsonTestResult($hudson_job_url) {
		$parsed_url = parse_url($hudson_job_url);

		if ( ! $parsed_url || ! array_key_exists('scheme', $parsed_url) ) {
			throw new HudsonJobURLMalformedException(vsprintf(_("Wrong Job URL: %s"),  array($hudson_job_url)));
		}

		$this->hudson_test_result_url = $hudson_job_url . "/lastBuild/testReport/api/xml/";
		$this->_setStreamContext();
		$this->buildJobObject();
	}

	function getHudsonControler() {
		return new hudson();
	}

	public function buildJobObject() {
		$this->dom_job = $this->_getXMLObject($this->hudson_test_result_url);
	}

	protected function _getXMLObject($hudson_test_result_url) {
		$xmlstr = @file_get_contents($hudson_test_result_url, false, $this->context);
		if ($xmlstr !== false) {
			$xmlobj = simplexml_load_string($xmlstr);
			if ($xmlobj !== false) {
				return $xmlobj;
			} else {
				throw new HudsonJobURLFileException(vsprintf(_("Unable to read file at URL: %s"),  array($hudson_test_result_url)));
			}
		} else {
			throw new HudsonJobURLFileNotFoundException(vsprintf(_("File not found at URL: %s"),  array($hudson_test_result_url)));
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

	function getFailCount() {
		return $this->dom_job->failCount;
	}

	function getPassCount() {
		return $this->dom_job->passCount;
	}

	function getSkipCount() {
		return $this->dom_job->skipCount;
	}

	function getTotalCount() {
		return $this->getFailCount() + $this->getPassCount() + $this->getSkipCount();
	}

	function getTestResultPieChart() {
		global $HTML;
		html_use_jqueryjqplotpluginPie();
		html_use_jqueryjqplotpluginhighlighter();
		html_use_jqueryjqplotpluginCanvas();
		echo $HTML->getJavascripts();
		echo $HTML->getStylesheets();
		$chartid = md5($this->hudson_test_result_url);
		$pie_labels = array();
		$pie_vals = array();
		$pie_labels[0] = vsprintf(_("Pass (%s)"), $this->getPassCount());
		$pie_vals[0] = $this->getPassCount();
		$pie_labels[1] = vsprintf(_("Fail (%s)"), $this->getFailCount());
		$pie_vals[1] = $this->getFailCount();
		$pie_labels[2] = vsprintf(_("Skip (%s)"), $this->getSkipCount());
		$pie_vals[2] = $this->getSkipCount();
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var data'.$chartid.' = new Array();';
		for ($i = 0; $i < count($pie_vals); $i++) {
			echo 'data'.$chartid.'.push([\''.htmlentities($pie_labels[$i]).'\','.$pie_vals[$i].']);';
		}
		echo 'var plot'.$chartid.';';
		echo 'jQuery(document).ready(function(){
			plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', [data'.$chartid.'],
				{
					title : \'Test result: '.$this->getPassCount().'/'.$this->getTotalCount().'\',
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer,
						rendererOptions: {
							showDataLabels: true,
							dataLabels: \'percent\',
						}
					},
					legend: {
						show:true, location: \'e\',
					},
				}
				);
			});';
		echo 'jQuery(window).resize(function() {
				plot'.$chartid.'.replot( { resetAxes: true } );
			});'."\n";
		echo '//]]></script>';
		echo '<div id="chart'.$chartid.'"></div>';
	}

}

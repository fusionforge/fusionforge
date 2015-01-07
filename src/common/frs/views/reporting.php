<?php
/**
 * Copyright (C) 2009-2012 Alain Peyrat, Alcatel-Lucent
 * Copyright 2012,2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The program ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $group_id; // id of group
global $HTML; // html object


$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

session_require_perm('frs_admin', $group_id, 'read');

$report = new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage(), 'frs');
}

if (!$start || !$end) $z =& $report->getMonthStartArr();

if (!$start) {
	$start = $z[0];
}

if (!$end) {
	$end = $z[count($z) - 1];
}

if ($end < $start) list($start, $end) = array($end, $start);

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginhighlighter();
echo $HTML->getJavascripts();
echo $HTML->getStylesheets();

$report = new ReportDownloads($group_id, $package_id, $start, $end);
if ($report->isError()) {
	echo $HTML->error_msg($report->getErrorMessage());
} else {

	echo $HTML->openForm(array('action' => util_make_url('/frs/?view=reporting&group_id='.$group_id), 'method' => 'post', 'class' => 'align-center'));
	echo html_e('strong', array(), _('Package')._(':')).report_package_box($group_id,'package_id',$package_id);
	echo html_e('strong', array(), _('Start Date')._(':')).report_months_box($report, 'start', $start);
	echo html_e('strong', array(), _('End Date')._(':')).report_months_box($report, 'end', $end);
	echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Refresh')));
	echo $HTML->closeForm();

	$data = $report->getData();

	if ($start == $end) {
		echo $HTML->error_msg(_('Start and end dates must be different'));
	} elseif (count($data) == 0) {
		echo $HTML->information(_('There have been no downloads for this package.'));
	} else {
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var ticks = new Array();';
		echo 'var values = new Array();';
		$arr =& $report->getMonthStartArr();
		$arr2 = array();
		$valuesArr = array();
		for ($i=0; $i < count($arr); $i++) {
			if ($arr[$i] >= $start && $arr[$i] <= $end) {
				$arr2[$i] = date(_('Y-m'), $arr[$i]);
				$valuesArr[$i] = 0;
			}
		}
		foreach ($arr2 as $key) {
			echo 'ticks.push("'.$key.'");';
		}
		for ($i=0; $i < count($data); $i++) {
			echo 'var labels = [{label:\''.$data[$i][0].'\'}];';
			$this_date = date(_('Y-m'), mktime(0, 0, 0, substr($data[$i][4], 4, 2), 0, substr($data[$i][4], 0, 4)));
			$index_key = array_search($this_date, $arr2);
			$valuesArr[$index_key+1]++;
		}
		foreach ($valuesArr as $key) {
			echo 'values.push('.$key.');';
		}
		echo 'var plot1;';
		echo 'jQuery(document).ready(function(){
				plot1 = jQuery.jqplot (\'chart1\', [values], {
						axesDefaults: {
							tickOptions: {
								angle: -90,
								fontSize: \'8px\',
								showGridline: false,
								showMark: false,
							},
						},
						legend: {
							show: true,
							placement: \'insideGrid\',
							location: \'nw\'
						},
						series:
							labels
						,
						axes: {
							xaxis: {
								label: "'._('Month').'",
								renderer: jQuery.jqplot.CategoryAxisRenderer,
								ticks: ticks,
								pad: 0,
							},
							yaxis: {
								label: "'._('Downloads').'",
								padMin: 0,
								tickOptions: {
									angle: 0,
									showMark: true,
								}
							}
						},
						highlighter: {
							show: true,
							sizeAdjust: 2.5,
						},
					});
			});';
		echo 'jQuery(window).resize(function() {
				plot1.replot( { resetAxes: true } );
			});'."\n";
		echo '//]]></script>';
		echo $HTML->html_chartid('chart1');
		echo $HTML->listTableTop(array(_('Package'), _('Release'), _('File'), _('User'), _('Date')),
				false, true, 'Download');
		for ($i=0; $i<count($data); $i++) {
			$date = preg_replace('/^(....)(..)(..)$/', '\1-\2-\3', $data[$i][4]);
			$cells = array();
			$cells[][] = $data[$i][0];
			$cells[][] = $data[$i][1];
			$cells[][] = basename($data[$i][2]);
			if ($data[$i][6] != 100) {
				$cells[][] = util_display_user($data[$i][5], $data[$i][6], $data[$i][3]);
			} else {
				$cells[][] = $data[$i][3];
			}
			$cells[] = array($date, 'class' => 'align-center');
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	}
}

<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright (C) 2009-2012 Alain Peyrat, Alcatel-Lucent
 * Copyright 2012-2015, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $HTML;  // Layout object
global $group_id; // id of group
global $g; // Group object
global $warning_msg;

if ( !forge_check_perm('docman', $group_id, 'admin')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

$report = new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage(), 'docman');
}

if (!$start || !$end) $z =& $report->getMonthStartArr();

if (!$start) {
	$start = $z[0];
}

if (!$end) {
	$end = $z[ count($z)-1 ];
}
if ($end < $start) list($start, $end) = array($end, $start);

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginhighlighter();
echo $HTML->getJavascripts();
echo $HTML->getStylesheets();

$report = new ReportPerGroupDocmanDownloads($group_id, $start, $end);
if ($report->isError()) {
	echo $HTML->error_msg($report->getErrorMessage());
} else {
	echo html_ao('div', array('id' => 'div_form_reporting'));
	echo $HTML->openForm(array('action' => '/docman/?group_id='.$group_id.'&view=reporting', 'method' => 'post', 'class' => 'align-center'));
	echo html_e('strong', array(), _('Start Date')._(':'), false);
	echo report_months_box($report, 'start', $start);
	echo html_e('strong', array(), _('End Date')._(':'), false);
	echo report_months_box($report, 'end', $end);
	echo html_e('input', array('type' => 'submit', 'value' => _('Refresh')));
	echo $HTML->closeForm();
	echo html_ac(html_ap() -1);

	$data = $report->getData();

	if (count($data) == 0) {
		echo $HTML->information(_('There have been no viewed documents for this project yet.'));
	} else {
		echo html_ao('script', array('type' => 'text/javascript'));
		echo '//<![CDATA['."\n";
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
			$this_date = date(_('Y-m'), mktime(0, 0, 0, substr($data[$i][2], 4, 2), 0, substr($data[$i][2], 0, 4)));
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
		echo '//]]>';
		echo html_ac(html_ap() -1);
		echo $HTML->html_chartid('chart1');
		$tabletop = array(_('Folder'), _('Document'), _('User'), _('Date'));
		$classth = array('', '', '', '');
		echo $HTML->listTableTop($tabletop, array(), 'sortable_docman_listfile', 'sortable', $classth);
		for ($i = 0; $i < count($data); $i++) {
			$ndg = documentgroup_get_object($data[$i][3], $group_id);
			$cells = array();
			$cells[][] = $ndg->getPath(true);
			$cells[][] = $data[$i][0];
			if ( $data[$i][1] != 100) {
				$userObject = user_get_object($data[$i][1]);
				$cells[][] = util_display_user($userObject->getUnixName(), $data[$i][1], $userObject->getRealName());
			} else {
				$cells[][] = _('Anonymous user');
			}
			$cells[] = array(preg_replace('/^(....)(..)(..)$/', '\1-\2-\3', $data[$i][2]), 'class' => 'align-center');
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	}
}

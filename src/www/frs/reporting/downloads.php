<?php
/**
 * Copyright (C) 2009-2012 Alain Peyrat, Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/include/frs_utils.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/ReportDownloads.class.php';

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

if (!$group_id) {
	exit_no_group();
}

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(), 'frs');
}

session_require_perm('frs', $group_id, 'write');

$report = new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage(), 'frs');
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

frs_header(array('title' => _('File Release Reporting'),
		 'group' => $group_id,
		 'pagename' => 'project_showfiles',
		 'sectionvals' => group_getname($group_id)));

$report = new ReportDownloads($group_id, $package_id, $start, $end);
if ($report->isError()) {
	echo '<p class="error_msg">'.$report->getErrorMessage().'</p>';
	frs_footer();
	exit;
}

?>

<form action="<?php echo util_make_url('/frs/reporting/downloads.php') ?>"
	  method="get"
	  class="align-center">
	<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
	<strong><?php echo _('Package')._(':'); ?></strong>
	<?php echo report_package_box($group_id,'package_id',$package_id); ?>
	<strong><?php echo _('Start Date')._(':'); ?></strong>
	<?php echo report_months_box($report, 'start', $start); ?>
	<strong><?php echo _('End Date')._(':'); ?></strong>
	<?php echo report_months_box($report, 'end', $end); ?>
	<input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" />
</form>

<?php

$data = $report->getData();

if ($start == $end) {
    echo '<p class="error">'._('Start and end dates must be different').'</p>';
} elseif (count($data) == 0) {
	echo '<p class="information">';
	echo _('There have been no downloads for this package.');
	echo '</p>';
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
		$thisdate = date(_('Y-m'), mktime(0, 0, 0, substr($data[$i][4], 4, 2), 0, substr($data[$i][4], 0, 4)));
		$indexkey = array_search($thisdate, $arr2);
		$valuesArr[$indexkey+1]++;
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
	echo '<div id="chart1"></div>';
	echo $HTML->listTableTop(array(_('Package'), _('Release'), _('File'), _('User'), _('Date')),
                              false, true, 'Download');
	for ($i=0; $i<count($data); $i++) {
		$date = preg_replace('/^(....)(..)(..)$/', '\1-\2-\3', $data[$i][4]);
		echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>'.
			'<td>'. $data[$i][0] .'</td>'.
			'<td>'. $data[$i][1] .'</td>'.
			'<td>'. basename($data[$i][2]) .'</td>';
		if ($data[$i][6] != 100) {
			echo '<td><a href="/users/'.urlencode($data[$i][5]).'/">'. $data[$i][3] .'</a></td>';
		} else {
			echo '<td>'.$data[$i][3].'</td>';
		}
		echo '<td class="align-center">'. $date .'</td></tr>';
	}
	echo $HTML->listTableBottom();
}

frs_footer();

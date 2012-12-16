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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $HTML; // html object
global $d_arr; // document array
global $group_id; // id of group

if ( !forge_check_perm('docman', $group_id, 'admin')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
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
echo $HTML->getJavascripts();
echo $HTML->getStylesheets();


$report = new ReportPerGroupDocmanDownloads($group_id, $start, $end);
if ($report->isError()) {
	echo '<p class="information">'.$report->getErrorMessage().'</p>';
} else {
?>

<form action="<?php echo util_make_url('/docman/') ?>" method="get">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="view" value="reporting" />
<table><tr>
<td><strong><?php echo _('Start'); ?>:</strong><br />
<?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo _('End'); ?>:</strong><br />
<?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" /></td>
</tr></table>
</form>

<?php

$data = $report->getData();

if (count($data) == 0) {
	echo '<p class="information">';
	echo _('There have been no documents for this project.');
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
		$thisdate = date(_('Y-m'), mktime(0, 0, 0, substr($data[$i][2], 4, 2), 0, substr($data[$i][2], 0, 4)));
		$indexkey = array_search($thisdate, $arr2);
		$valuesArr[$indexkey]++;
	}
	foreach ($valuesArr as $key) {
		echo 'values.push('.$key.');';
	}
	echo 'var plot1;';
	echo 'jQuery(document).ready(function(){
			plot1 = jQuery.jqplot (\'chart1\', [values], {
					axes: {
						xaxis: {
							label: "'._('Month').'",
							renderer: jQuery.jqplot.CategoryAxisRenderer,
							ticks: ticks,
							pad: 0,
						},
						yaxis: {
							label: "'._('Download').'",
							padMin: 0,
						}
					}
				});
		});';
	echo 'jQuery(window).resize(function() {
			plot1.replot( { resetAxes: true } );
		});'."\n";
	echo '//]]></script>';
	echo '<div id="chart1"></div>';
	echo $HTML->listTableTop(array(_('Path'), _('File'), _('User'), _('Date')), false, true, 'Download');
	for ($i=0; $i<count($data); $i++) {
		$date = preg_replace('/^(....)(..)(..)$/', '\1-\2-\3', $data[$i][2]);
		echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>'.
			'<td>PATH</td>'.
			'<td>'. $data[$i][0] .'</td>'.
			'<td><a href="/users/'.urlencode($data[$i][3]).'/">'. $data[$i][1] .'</a></td>'.
			'<td class="align-center">'. $date .'</td></tr>';
	}
	echo $HTML->listTableBottom();
}

}
 

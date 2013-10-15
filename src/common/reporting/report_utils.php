<?php
/**
 * FusionForge reporting system
 *
 * Copyright 2003-2004, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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

function report_header($title) {
	$t = sprintf(_('%s Reporting'), forge_get_config ('forge_name')) . _(': ') . $title;
	site_header(array('title'=>$t));
}

function report_footer() {
	global $HTML;
	$HTML->footer(array());
}

function report_span_box($name='SPAN', $selected='1', $suppress_daily=false) {
	if ($suppress_daily) {
		$vals=array(2,3);
		$titles=array(_('Weekly'),
			_('Monthly'));
	} else {
		$vals=array(1,2,3);
		$titles=array(_('Daily'),
			_('Weekly'),
			_('Monthly'));
	}
	return html_build_select_box_from_arrays ($vals,$titles,$name,$selected,false);
}

function report_weeks_box($Report, $name='week', $selected=false) {
	$arr =& $Report->getWeekStartArr();

	$arr2=array();
	for ($i=0; $i<count($arr); $i++) {
		$arr2[$i]=date(_('Y-m-d'), $arr[$i]) .' '._('to').' '. date(_('Y-m-d'), ($arr[$i]+6*24*60*60));
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_day_adjust_box() {
	$days = array('0', '1', '2', '3', '4', '5', '6');
	$names = array(_('Sunday'), _('Monday'), _('Tuesday'),
				   _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'));
	return html_build_select_box_from_arrays($days, $names, 'days_adjust', false, false);
}

function report_months_box($Report, $name='month', $selected=false) {
	$arr =& $Report->getMonthStartArr();

	$arr2=array();
	for ($i=0; $i<count($arr); $i++) {
		$arr2[$i]=date(_('Y-m'),$arr[$i]);
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_useract_box($name='dev_id', $selected='1', $start_with='') {
	if ($start_with) {
		$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_user_act_daily WHERE user_id=users.user_id)) AND (lower(lastname) LIKE $2 OR lower(user_name) LIKE $3) ORDER BY lastname',
					array ('A',
							strtolower("$start_with%"),
							strtolower("$start_with%"))) ;
	} else {
		$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_user_act_daily WHERE user_id=users.user_id)) ORDER BY lastname',
					array ('A')) ;
	}
	return html_build_select_box($res, $name, $selected, false);
}

function report_usertime_box($name='dev_id', $selected='1', $start_with='') {
	if ($start_with) {
		$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_time_tracking WHERE user_id=users.user_id)) AND (lower(lastname) LIKE $2 OR lower(user_name) LIKE $3) ORDER BY lastname',
					array ('A',
							strtolower("$start_with%"),
							strtolower("$start_with%"))) ;
	} else {
		$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_time_tracking WHERE user_id=users.user_id)) ORDER BY lastname',
					array ('A')) ;
	}
	return html_build_select_box($res, $name, $selected, false);
}

function report_group_box($name='g_id', $selected='1') {

	$res = db_query_params ('SELECT group_id,group_name FROM groups WHERE status=$1 ORDER BY group_name',
				array ('A')) ;
	return html_build_select_box($res, $name, $selected, false);
}

function report_area_box($name='area', $selected='1', $Group=false) {
	$arr = array () ;
	$arr2 = array () ;
	if ($Group) {
		$use_tracker = $Group->usesTracker();
		$use_forum = $Group->usesForum();
		$use_docman = $Group->usesDocman();
		$use_pm = $Group->usesPM();
		$use_frs = $Group->usesFRS();
		$use_pageviews = true;
	} else {
		$use_tracker = forge_get_config('use_tracker');
		$use_forum = forge_get_config('use_forum');
		$use_docman = forge_get_config('use_docman');
		$use_pm = forge_get_config('use_pm');
		$use_frs = false; // Not implemented in ReportUserAct: forge_get_config('use_frs');
		$use_pageviews = false;
	}
	if ($use_tracker) {
		$arr[]='tracker';
		$arr2[]=_('Tracker');
	}
	if ($use_forum) {
		$arr[]='forum';
		$arr2[]=_('Forums');
	}
	if ($use_docman) {
		$arr[]='docman';
		$arr2[]=_('Docs');
	}
	if ($use_pm) {
		$arr[]='taskman';
		$arr2[]=_('Tasks');
	}
	if ($use_frs) {
		$arr[]='downloads';
		$arr2[]=_('Downloads');
	}
	if ($use_pageviews) {
		$arr[]='pageviews';
		$arr2[]=_('Page Views');
	}

	if (is_object($Group) && $Group->getID()) {
		$hookParams['group'] = $Group->getID();
		$hookParams['group_id'] = $Group->getID();
		$hookParams['show'] = array('none'); // No display => No compute this time.
		$hookParams['ids'] = &$arr;
		$hookParams['texts'] = &$arr2;
		plugin_hook ("activity", $hookParams) ;
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_tracker_box($name='datatype', $selected='1') {
	$arr = array();
	$arr2 = array();
	if (forge_get_config('use_tracker')) {
		$arr[]=_('Bugs');
		$arr[]=_('Support');
		$arr[]=_('Patches');
		$arr[]=_('Feature Requests');
		$arr[]=_('Other Trackers');
		$arr2[]='1';
		$arr2[]='2';
		$arr2[]='3';
		$arr2[]='4';
		$arr2[]='0';
	}
	if (forge_get_config('use_forum')) {
		$arr[]=_('Forum Messages');
		$arr2[]='5';
	}
	if (forge_get_config('use_pm')) {
		$arr[]=_('Tasks');
		$arr2[]='6';
	}
	if (forge_get_config('use_frs')) {
		$arr[]=_('Downloads');
		$arr2[]='7';
	}
	return html_build_select_box_from_arrays ($arr2,$arr,$name,$selected,false);
}

function report_time_category_box($name='category',$selected=false) {
	global $report_time_category_res;
	if (!$report_time_category_res) {
		$report_time_category_res = db_query_params ('SELECT * FROM rep_time_category', array());
	}
	return html_build_select_box($report_time_category_res,$name,$selected,false);
}

//
//	Takes an array of labels and an array values and removes vals < 2% and sets up an "other"
//
function report_pie_arr($labels, $vals, $format=1) {
	global $pie_labels,$pie_vals;

	$total = 0;
	//first get sum of all values
	for ($i=0; $i<count($vals); $i++) {
		$total += $vals[$i];
	}

	//now prune out vals where < 2%
	$rem = 0;
	for ($i=0; $i<count($vals); $i++) {
		if (($vals[$i]/$total) < .02) {
			$rem += $vals[$i];
		} else {
			$pie_labels[]=util_unconvert_htmlspecialchars($labels[$i])." (". number_format($vals[$i],$format) .") ";
			$pie_vals[]=$vals[$i];
		}
	}
	if ($rem > 0) {
		$pie_labels[]=_('Other')." (". number_format($rem,$format) .") ";
		$pie_vals[]=$rem;
	}

}

function report_package_box($group_id, $name='dev_id', $selected='') {

	$res = db_query_params ('SELECT package_id, name FROM frs_package WHERE frs_package.group_id = $1',
				array ($group_id));
	return html_build_select_box($res, $name, $selected, false);
}

function trackeract_graph($group_id, $area, $SPAN, $start, $end, $atid) {
	$now = time();
	if ($now < $end) {
		$end = $now;
	}
	if (!strlen($area)) {
		echo '<p class="information">'._('No selected area.').'</p>';
		return true;
	}
	$report = new ReportTrackerAct($SPAN, $group_id, $atid, $start, $end);
	if ($report->isError()) {
		echo '<p class="error">'.$report->getErrorMessage().'</p>';
		return false;
	}
	$rdates = $report->getRawDates();
	if (!$rdates) {
		return false;
	}
	$ydata[] =& $report->getAverageTimeData();
	$label[] = _('Avg Time Open (in days)');
	$ydata[] =& $report->getOpenCountData();
	$label[] = _('Total Opened');
	$ydata[] =& $report->getStillOpenCountData();
	$label[] = _('Total Still Open');
	$chartid = 'projecttrackergraph_'.$group_id;
	$yMax = 0;
	echo '<script type="text/javascript">//<![CDATA['."\n";
	echo 'var values = new Array();';
	echo 'var ticks = new Array();';
	echo 'var labels = new Array();';
	echo 'var series = new Array();';
	echo 'var plot'.$chartid.';';
	for ($z = 0; $z < count($ydata); $z++) {
		echo 'values['.$z.'] = new Array();';
		echo 'labels.push({label:\''.$label[$z].'\'});';
	}
	$tickArr = array();
	switch ($SPAN) {
		case REPORT_TYPE_MONTHLY : {
			$formatDate = 'Y/m';
			break;
		}
		case REPORT_TYPE_WEEKLY : {
			$formatDate = 'Y/W';
			break;
		}
	}
	for ($j = 0; $j < count($rdates); $j++) {
		for ($z = 0; $z < count($ydata); $z++) {
			if (isset($ydata[$z][$j])) {
				if ($ydata[$z][$j] === false || $ydata[$z][$j] === NULL) {
					$ydata[$z][$j] = 0;
				}
				if ($ydata[$z][$j] > $yMax) {
					$yMax = $ydata[$z][$j];
				}
				echo 'values['.$z.'].push('.$ydata[$z][$j].');';
			} else {
				echo 'values['.$z.'].push(0);';
			}
		}
		$tickArr[] = date($formatDate, $rdates[$j]);
		echo 'ticks.push(\''.$tickArr[$j].'\');';
	}
	for ($z = 0; $z < count($ydata); $z++) {
		echo 'series.push(values['.$z.']);';
	}
	echo 'jQuery(document).ready(function(){
		plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', series, {
			title : \''._('Tracker Activity').' ('.strftime('%x',$start).' - '.strftime('%x',$end).') \',
			axesDefaults: {
				tickOptions: {
					angle: -90,
					fontSize: \'8px\',
					showGridline: false,
					showMark: false,
				},
				pad: 0,
			},
			seriesDefaults: {
				showMarker: false,
				lineWidth: 1,
				fill: true,
				renderer:jQuery.jqplot.BarRenderer,
				rendererOptions: {
					fillToZero: true,
				},
			},
			legend: {
				show:true, location: \'ne\',
			},
			series:
				labels
			,
			axes: {
				xaxis: {
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					ticks: ticks,
				},
				yaxis: {
					max: '.++$yMax.',
					min: 0,
					tickOptions: {
						angle: 0,
						showMark: true,
						formatString: \'%d\'
					}
				},
			},
			highlighter: {
				show: true,
				sizeAdjust: 2.5,
			},
		});
	});';
	echo 'jQuery(window).resize(function() {
		plot'.$chartid.'.replot();
		});'."\n";
	echo '//]]></script>';
	echo '<div id="chart'.$chartid.'"></div>';
	return true;
}

function trackerpie_graph($group_id, $area, $SPAN, $start, $end, $atid) {
	$now = time();
	if ($now < $end) {
		$end = $now;
	}
	if (!strlen($area)) {
		echo '<p class="information">'._('No selected area.').'</p>';
		return true;
	}
	$report = new ReportTrackerAct($SPAN, $group_id, $atid, $start, $end);
	if ($report->isError()) {
		echo '<p class="error">'.$report->getErrorMessage().'</p>';
		return false;
	}
	switch ($area) {
		default: {
			$dbres = $report->getPerAssignee($atid, $start, $end);
			$areaname = _('Per assignee');
			break;
		}
	}
	$chartid = 'projecttrackerpie_'.$group_id;
	echo '<script type="text/javascript">//<![CDATA['."\n";
	echo 'var plot'.$chartid.';';
	echo 'var data = new Array();';
	while ($row = db_fetch_array($dbres)) {
		echo 'data.push([\''.htmlentities($row[0]).'\',\''.$row[1].'\']);';
	}
	echo 'jQuery(document).ready(function(){
		plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', [data],
			{
				title : \''.$areaname." (".strftime('%x',$start) ." - ". strftime('%x',$end) .")".'\',
				seriesDefaults: {
					// Make this a pie chart.
					renderer: jQuery.jqplot.PieRenderer,
					rendererOptions: {
						// Put data labels on the pie slices.
						// By default, labels show the percentage of the slice.
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
	return true;
}

function report_graph($type, $SPAN, $start, $end) {
	$now = time() - 60*60*24; // 1 day
	if ($now < $end) {
		$end = $now;
	}
	switch ($type) {
		case 'usercumul': {
			$report = new ReportUserCum($SPAN, $start, $end);
			$label[0] = _('Cumulative Users');
			break;
		}
		case 'useradded': {
			$report = new ReportUserAdded($SPAN, $start, $end);
			$label[0] = _('Users Added');
			break;
		}
		case 'groupadded': {
			$report = new ReportGroupAdded($SPAN, $start, $end);
			$label[0] = _('Projects Added');
			break;
		}
		case 'groupcumul': {
			$report = new ReportGroupCum($SPAN, $start, $end);
			$label[0] = _('Cumulative Projects');
			break;
		}
	}

	if ($report->isError()) {
		echo '<p class="error">'.$report->getErrorMessage().'</p>';
		return false;
	}
	$rdates = $report->getRawDates();
	if (!$rdates) {
		echo '<p class="information">'._('No data to display.').'</p>';
		return false;
	}
	$ydata[0]  = $report->getData();

	if ($SPAN == REPORT_TYPE_DAILY) {
		$i = 0;
		$formatDate = 'Y-m-d';
		$looptime = $start;
		while ($looptime < $end) {
			$timeStampArr[$i] = $looptime;
			$timeStampArrFormat[$i] = date($formatDate, $looptime);
			$looptime += REPORT_DAY_SPAN;
			$i++;
		}
	} elseif ($SPAN == REPORT_TYPE_WEEKLY) {
		$timeStampArr = $report->getWeekStartArr();
		$timeStampArrFormat = $report->getWeekStartArrFormat();
		$formatDate = 'Y/W';
	} elseif ($SPAN == REPORT_TYPE_MONTHLY) {
		$timeStampArr = $report->getMonthStartArr();
		$timeStampArrFormat = $report->getMonthStartArrFormat();
		$formatDate = 'Y/m';
	}

	$chartid = '_useradded';
	$yMax = 0;
	echo '<script type="text/javascript">//<![CDATA['."\n";
	echo 'var plot'.$chartid.';';
	echo 'var '.$chartid.'values = new Array();';
	echo 'var '.$chartid.'labels = new Array();';
	echo 'var '.$chartid.'series = new Array();';
	for ($z = 0; $z < count($ydata); $z++) {
		echo $chartid.'values['.$z.'] = new Array();';
		echo $chartid.'labels.push({label:\''.$label[$z].'\'});';
		switch ($SPAN) {
			case REPORT_TYPE_DAILY:
			case REPORT_TYPE_MONTHLY: {
				for ($j = 0; $j < count($timeStampArr); $j++) {
					if (in_array($timeStampArr[$j], $rdates)) {
						$thekey = array_search($timeStampArr[$j], $rdates);
						if (isset($ydata[$z][$thekey])) {
							if ($ydata[$z][$thekey] === false) {
								$ydata[$z][$thekey] = 0;
							}
							if ($ydata[$z][$thekey] > $yMax) {
								$yMax = $ydata[$z][$thekey];
							}
							echo 'var '.$chartid.'datevalues = '.$ydata[$z][$thekey].';';
						} else {
							echo 'var '.$chartid.'datevalues = 0;';
						}
					} else {
						echo 'var '.$chartid.'datevalues = 0;';
					}
					echo 'var '.$chartid.'date = \''.$timeStampArrFormat[$j].'\';';
					echo $chartid.'values['.$z.'].push(['.$chartid.'date, '.$chartid.'datevalues]);';
				}
				break;
			}
			case REPORT_TYPE_WEEKLY: {
				for ($j = 0; $j < count($rdates); $j++) {
					$wrdates[$j] = date($formatDate, $rdates[$j]);
				}
				for ($j = 0; $j < count($timeStampArr); $j++) {
					if (in_array($timeStampArrFormat[$j], $wrdates)) {
						$thekey = array_search($timeStampArr[$j], $wrdates);
						if (isset($ydata[$z][$thekey])) {
							if ($ydata[$z][$thekey] === false) {
								$ydata[$z][$thekey] = 0;
							}
							if ($ydata[$z][$thekey] > $yMax) {
								$yMax = $ydata[$z][$thekey];
							}
							echo 'var '.$chartid.'datevalues = '.$ydata[$z][$thekey].';';
						} else {
							echo 'var '.$chartid.'datevalues = 0;';
						}
					} else {
						echo 'var '.$chartid.'datevalues = 0;';
					}
					echo 'var '.$chartid.'date = \''.$timeStampArrFormat[$j].'\';';
					echo $chartid.'values['.$z.'].push(['.$chartid.'date, '.$chartid.'datevalues]);';
				}
				break;
			}
		}
	}
	for ($z = 0; $z < count($ydata); $z++) {
		echo $chartid.'series.push('.$chartid.'values['.$z.']);';
	}
	echo 'jQuery(document).ready(function(){
		plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', '.$chartid.'series, {
			axesDefaults: {
				tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
				tickOptions: {
					angle: 60,
					showGridline: false,
				}
			},
			legend: {
				show:true, location: \'ne\',
			},
			series:
				'.$chartid.'labels
			,
			axes: {
				xaxis: {
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					label: \''.$label[0].'\',
				},
				yaxis: {
					max: '.++$yMax.',
					min: 0,
					tickOptions: {
						angle: 0,
						formatString: \'%d\'
					},
				}
			},
			highlighter: {
				show: true,
				sizeAdjust: 2.5,
				showTooltip: true,
				tooltipAxes: \'y\',
			},
		});
	});';
	echo 'jQuery(window).resize(function() {
			plot'.$chartid.'.replot( { resetAxes: true } );
		});'."\n";
	echo '//]]></script>';
	echo '<div id="chart'.$chartid.'"></div>';
	return true;
}

function report_actgraph($type, $SPAN, $start, $end, $id, $area) {
	$now = time() - 60*60*24; // 1 day
	if ($now < $end) {
		$end = $now;
	}
	switch ($type) {
		case 'user': {
			$report = new ReportUserAct($SPAN, $id, $start, $end);
			$u = user_get_object($id);
			if (!$u || $u->isError()) {
				exit_error(_("Could Not Get User"));
			}
			break;
		}
		case 'project': {
			$report = new ReportProjectAct($SPAN, $id, $start, $end);
			$g = group_get_object($id);
			if (!$g || !is_object($g)) {
				exit_no_group();
			} elseif ($g->isError()) {
				exit_error($g->getErrorMessage(), '');
			}
			break;
		}
		case 'sitewide': {
			$report = new ReportSiteAct($SPAN, $start, $end);
			break;
		}
	}

	if ($report->isError()) {
		echo '<p class="error">'.$report->getErrorMessage().'</p>';
		return false;
	}
	$rdates = $report->getRawDates();
	if (!$rdates) {
		echo '<p class="information">'._('No data to display.').'</p>';
		return false;
	}
	if (!$SPAN) {
		$SPAN = REPORT_TYPE_DAILY;
	}

	if ($SPAN == REPORT_TYPE_DAILY) {
		$i = 0;
		$looptime = $start;
		while ($looptime < $end) {
			$timeStampArr[$i] = $looptime;
			$looptime += REPORT_DAY_SPAN;
			$i++;
		}
		$formatDate = 'Y/m/d';
	} elseif ($SPAN == REPORT_TYPE_WEEKLY) {
		$timeStampArr = $report->getWeekStartArr();
		$formatDate = 'Y/W';
	} elseif ($SPAN == REPORT_TYPE_MONTHLY) {
		$timeStampArr = $report->getMonthStartArr();
		$formatDate = 'Y/m';
	}

	$initialSizeOfTimeStampArr = count($timeStampArr);
	for ($j = 0; $j < $initialSizeOfTimeStampArr; $j++) {
		if ($timeStampArr[$j] < $start || $timeStampArr[$j] >= $end) {
			unset($timeStampArr[$j]);
		}
	}

	$timeStampArr = array_values($timeStampArr);
	for ($j = 0; $j < count($timeStampArr); $j++) {
		$tickArr[] = date($formatDate, $timeStampArr[$j]);
	}

	switch ($area) {
		case 'docman': {
			$ydata[] =& $report->getDocs();
			$areaname = _('Docs');
			$label[] = _('Documents');
			break;
		}
		case 'downloads': {
			$ydata[] =& $report->getDownloads();
			$areaname = _('Downloads');
			$label[] = _('Downloads');
			break;
		}
		case 'forum': {
			$ydata[] =& $report->getForum();
			$areaname = _('Forums');
			$label[] = _('Forums');
			break;
		}
		case 'pageviews': {
			$ydata[] =& $report->getPageViews();
			$areaname = _('Page Views');
			$label[] = _('Page Views');
			break;
		}
		case 'taskman': {
			$ydata[] =& $report->getTaskOpened();
			$ydata[] =& $report->getTaskClosed();
			$areaname = _('Tasks');
			$label[] = _('Task open');
			$label[] = _('Task close');
			break;
		}
		case 'tracker': {
			$ydata[] =& $report->getTrackerOpened();
			$ydata[] =& $report->getTrackerClosed();
			$areaname = _('Trackers');
			$label[] = _('Tracker items opened');
			$label[] = _('Tracker items closed');
			break;
		}
		default: {
			$results = array();
			$ids = array();
			$texts = array();
			$show[] = $area;

			$hookParams['group'] = $id;
			$hookParams['results'] = &$results;
			$hookParams['show'] = &$show;
			$hookParams['begin'] = $start;
			$hookParams['end'] = $end;
			$hookParams['ids'] = &$ids;
			$hookParams['texts'] = &$texts;
			plugin_hook("activity", $hookParams);

			$areaname = $texts[0];
			$label[] = $texts[0];
			$sum = array();
			foreach ($results as $arr) {
				$dd = date($formatDate, $arr['activity_date']);
				switch ($SPAN) {
					case REPORT_TYPE_MONTHLY : {
						$d = mktime(0, 0, 0, substr($dd, 5, 2) , 1, substr($dd, 0, 4));
						break;
					}
					case REPORT_TYPE_WEEKLY: {
						$d = strtotime(substr($dd, 0, 4).'-W'.substr($dd, 5, 2));
						break;
					}
					case REPORT_TYPE_DAILY: {
						$d = mktime(0, 0, 0, substr($dd, 5, 2) , substr($dd, 8, 2), substr($dd, 0, 4));
						break;
					}
				}
				@$sum[$d]++;
			}

			// Now, stores the values in the ydata array for the graph.
			$ydata[] = array();
			$i = 0;
			foreach ($rdates as $d) {
				$ydata[0][$i++] = isset($sum[$d]) ? $sum[$d] : 0;
			}
			break;
		}
	}

	$chartid = 'report_actgraph_'.$id;
	$yMax = 0;
	echo '<script type="text/javascript">//<![CDATA['."\n";
	echo 'var plot'.$chartid.';';
	echo 'var values = new Array();';
	echo 'var ticks = new Array();';
	echo 'var labels = new Array();';
	echo 'var series = new Array();';
	for ($z = 0; $z < count($ydata); $z++) {
		echo 'values['.$z.'] = new Array();';
		echo 'labels.push({label:\''.$label[$z].'\'});';
	}
	switch ($SPAN) {
		case REPORT_TYPE_DAILY:
		case REPORT_TYPE_MONTHLY: {
			for ($j = 0; $j < count($timeStampArr); $j++) {
				for ($z = 0; $z < count($ydata); $z++) {
					if (in_array($timeStampArr[$j], $rdates)) {
						$thekey = array_search($timeStampArr[$j], $rdates);
						if (isset($ydata[$z][$thekey])) {
							if ($ydata[$z][$thekey] === false) {
								$ydata[$z][$thekey] = 0;
							}
							if ($ydata[$z][$thekey] > $yMax) {
								$yMax = $ydata[$z][$thekey];
							}
							echo 'values['.$z.'].push('.$ydata[$z][$thekey].');';
						} else {
							echo 'values['.$z.'].push(0);';
						}
					} else {
						echo 'values['.$z.'].push(0);';
					}
				}
				echo 'ticks.push(\''.$tickArr[$j].'\');';
			}
			break;
		}
		case REPORT_TYPE_WEEKLY : {
			for ($j = 0; $j < count($rdates); $j++) {
				$wrdates[$j] = date($formatDate, $rdates[$j]);
			}
			for ($j = 0; $j < count($tickArr); $j++) {
				for ($z = 0; $z < count($ydata); $z++) {
					if (in_array($tickArr[$j], $wrdates)) {
						$thekey = array_search($tickArr[$j], $wrdates);
						if (isset($ydata[$z][$thekey])) {
							if ($ydata[$z][$thekey] === false) {
								$ydata[$z][$thekey] = 0;
							}
							if ($ydata[$z][$thekey] > $yMax) {
								$yMax = $ydata[$z][$thekey];
							}
							echo 'values['.$z.'].push('.$ydata[$z][$thekey].');';
						} else {
							echo 'values['.$z.'].push(0);';
						}
					} else {
						echo 'values['.$z.'].push(0);';
					}
				}
				echo 'ticks.push(\''.$tickArr[$j].'\');';
			}
			break;
		}
	}
	for ($z = 0; $z < count($ydata); $z++) {
		echo 'series.push(values['.$z.']);';
	}
	echo 'jQuery(document).ready(function(){
			plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', series, {
				title : \''.$areaname.' ('.strftime('%x', $start).' - '.strftime('%x', $end).') \',
				axesDefaults: {
					tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
					tickOptions: {
						angle: 90,
						fontSize: \'8px\',
						showGridline: false,
						showMark: false,
					},
					pad: 0,
				},
				seriesDefaults: {
					showMarker: false,
					lineWidth: 1,
					fill: true,
					renderer:jQuery.jqplot.BarRenderer,
					rendererOptions: {
						fillToZero: true,
					},
				},
				legend: {
					show:true, location: \'ne\',
				},
				series:
					labels
				,
				axes: {
					xaxis: {
						renderer: jQuery.jqplot.CategoryAxisRenderer,
						ticks: ticks,
					},
					yaxis: {
						max: '.++$yMax.',
						min: 0,
						tickOptions: {
							angle: 0,
							showMark: true,
							formatString: \'%d\'
						}
					},
				},
				highlighter: {
					show: true,
					sizeAdjust: 2.5,
					showTooltip: true,
					tooltipAxes: \'y\',
				},
			});
		});';
	echo 'jQuery(window).resize(function() {
		plot'.$chartid.'.replot();
	});'."\n";
	echo '//]]></script>';
	echo '<div id="chart'.$chartid.'"></div>';
	return true;
}

function report_toolspiegraph($datatype = 0, $start, $end) {
	$now = time() - 60*60*24; // 1 day
	if ($now < $end) {
		$end = $now;
	}
	if ($datatype < 5) {
		$res = db_query_params ('SELECT g.group_name,count(*) AS count
		FROM groups g, artifact_group_list agl, artifact a
		WHERE g.group_id=agl.group_id
		AND agl.group_artifact_id=a.group_artifact_id
		AND a.open_date BETWEEN $1 AND $2
		AND agl.datatype=$3
		GROUP BY group_name
		ORDER BY count DESC',
					array ($start,
					$end,
					$datatype));
	} elseif ($datatype == 5) {
		$res = db_query_params ('SELECT g.group_name,count(*) AS count
		FROM groups g, forum_group_list fgl, forum f
		WHERE g.group_id=fgl.group_id
		AND fgl.group_forum_id=f.group_forum_id
		AND f.post_date BETWEEN $1 AND $2
		GROUP BY group_name
		ORDER BY count DESC',
					array ($start,
					$end));
	} elseif ($datatype == 6) {
		$res = db_query_params ('SELECT g.group_name,count(*) AS count
		FROM groups g, project_group_list pgl, project_task pt
		WHERE g.group_id=pgl.group_id
		AND pgl.group_project_id=pt.group_project_id
		AND pt.start_date BETWEEN $1 AND $2
		GROUP BY group_name
		ORDER BY count DESC',
					array ($start,
					$end));
	} else {
		$res = db_query_params ('SELECT g.group_name,count(*) AS count
		FROM groups g, frs_package fp, frs_release fr, frs_file ff, frs_dlstats_file fdf
		WHERE g.group_id=fp.group_id
		AND fp.package_id=fr.package_id
		AND fr.release_id=ff.release_id
		AND ff.file_id=fdf.file_id
		AND (((fdf.month > $1) OR (fdf.month = $1 AND fdf.day >= $2))
		AND ((fdf.month < $3) OR (fdf.month = $3 AND fdf.day < $4)))
		GROUP BY group_name
		ORDER BY count DESC',
					array (date('Ym',$start),
					date('d',$start),
					date('Ym',$end),
					date('d',$end)));
	}

	if (db_error()) {
		exit_error(db_error(), '');
	}

	$arr[1] = _('Bugs');
	$arr[2] = _('Support Requests');
	$arr[3] = _('Patches');
	$arr[4] = _('Feature Requests');
	$arr[0] = _('Other Trackers');
	$arr[5] = _('Forum Messages');
	$arr[6] = _('Tasks');
	$arr[7] = _('Downloads');

	$chartid = 'toolspie';
	if (db_numrows($res)) {
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var data'.$chartid.' = new Array();';
		while ($row = db_fetch_array($res)) {
			echo 'data'.$chartid.'.push([\''.htmlentities($row[0]).'\','.$row[1].']);';
		}
		echo 'var plot'.$chartid.';';
		echo 'jQuery(document).ready(function(){
			plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', [data'.$chartid.'],
				{
					title : \''.$arr[$datatype].' ('.strftime('%x', $start) .' - '. strftime('%x', $end) .')\',
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer,
						rendererOptions: {
							showDataLabels: true,
							dataLabels: \'percent\',
							sliceMargin: 5
						}
					},
					legend: {
						show:true, location: \'e\',
						rendererOptions: {numberRows: 11, numberColumns: 2}
					},
				}
				);
			});';
		echo 'jQuery(window).resize(function() {
				plot'.$chartid.'.replot( { resetAxes: true } );
			});'."\n";
		echo '//]]></script>';
		echo '<div id="chart'.$chartid.'"></div>';
	} else {
		echo '<p class="information" >'._('No data to display.').'</p>';
	}
}

function report_timegraph($type = 'site', $area = 'tasks', $start, $end, $id = 0) {
	global $pie_labels, $pie_vals;

	$now = time() - 60*60*24; // 1 day
	if ($now < $end) {
		$end = $now;
	}
	switch($type) {
		case 'site': {
			$report = new ReportSiteTime($area, $start, $end);
			break;
		}
		case 'project': {
			$report = new ReportProjectTime($id, $area, $start, $end);
			break;
		}
		case 'user': {
			$report = new ReportUserTime($id, $area, $start, $end);
			break;
		}
	}

	$arr['tasks'] = _('By Task');
	$arr['category'] = _('By Category');
	$arr['subproject'] = _('By Subproject');
	$arr['user'] = _('By User');

	report_pie_arr($report->labels, $report->getData());

	$chartid = 'timegraph';
	if (count($pie_vals)) {
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var data'.$chartid.' = new Array();';
		for ($i = 0; $i < count($pie_vals); $i++) {
			echo 'data'.$chartid.'.push([\''.htmlentities($pie_labels[$i]).'\','.$pie_vals[$i].']);';
		}
		echo 'var plot'.$chartid.';';
		echo 'jQuery(document).ready(function(){
			plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', [data'.$chartid.'],
				{
					title : \''.$arr[$area].' ('.strftime('%x', $start) .' - '. strftime('%x', $end) .')\',
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
	} else {
		echo '<p class="information" >'._('No data to display.').'</p>';
	}
}

function report_sitetimebargraph($start, $end) {
	$now = time() - 60*60*24; // 1 day
	if ($now < $end) {
		$end = $now;
	}

	$res = db_query_params('SELECT week,sum(hours)
		FROM rep_time_tracking
		WHERE week
		BETWEEN $1 AND $2 GROUP BY week',
				array($start, $end));

	$report = new Report();
	if ($report->isError()) {
		exit_error($report->getErrorMessage());
	}
	$report->setDates($res,0);
	$report->setData($res,1);
	$chartid = 'sitetimebargraph';
	$areaname = _('Hours Recorded');
	$yMax = 0;
	$dates[0] = $report->getDates();
	$ydata[0] = $report->getData();
	$label[0] = _(' Hours');
	if (count($ydata[0])) {
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var plot'.$chartid.';';
		echo 'var values = new Array();';
		echo 'var ticks = new Array();';
		echo 'var labels = new Array();';
		echo 'var series = new Array();';
		for ($z = 0; $z < count($ydata); $z++) {
			echo 'values['.$z.'] = new Array();';
			echo 'labels.push({label:\''.$label[$z].'\'});';
			for ($j = 0; $j < count($ydata[$z]); $j++) {
				echo 'values['.$z.'].push('.$ydata[$z][$j].')';
				echo 'ticks.push('.$dates[$z][$j].')';
			}
		}
		for ($z = 0; $z < count($ydata); $z++) {
			echo 'series.push(values['.$z.']);';
		}
		echo 'jQuery(document).ready(function(){
				plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', series, {
					title : \''.$areaname.' ('.strftime('%x', $start).' - '.strftime('%x', $end).') \',
					axesDefaults: {
						tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
						tickOptions: {
							angle: 90,
							fontSize: \'8px\',
							showGridline: false,
							showMark: false,
						},
						pad: 0,
					},
					seriesDefaults: {
						showMarker: false,
						lineWidth: 1,
						fill: true,
						renderer:jQuery.jqplot.BarRenderer,
						rendererOptions: {
							fillToZero: true,
						},
					},
					legend: {
						show:true, location: \'ne\',
					},
					series:
						labels
					,
					axes: {
						xaxis: {
							renderer: jQuery.jqplot.CategoryAxisRenderer,
							ticks: ticks,
						},
						yaxis: {
							max: '.++$yMax.',
							min: 0,
							tickOptions: {
								angle: 0,
								showMark: true,
								formatString: \'%d\'
							}
						},
					},
					highlighter: {
						show: true,
						sizeAdjust: 2.5,
						showTooltip: true,
						tooltipAxes: \'y\',
					},
				});
			});';
		echo 'jQuery(window).resize(function() {
			plot'.$chartid.'.replot();
		});'."\n";
		echo '//]]></script>';
		echo '<div id="chart'.$chartid.'"></div>';
	} else {
		echo '<p class="information">'._('No data to display.').'</p>';
	}
	return true;
}

function report_pm_hbar($id, $values, $ticks, $labels, $stackSeries = false) {
	$yMax = 0;
	echo '<script type="text/javascript">//<![CDATA['."\n";
	echo 'var plot'.$id.';';
	echo 'var values'.$id.' = new Array();';
	echo 'var ticks'.$id.' = new Array();';
	echo 'var labels'.$id.' = new Array();';
	echo 'var series'.$id.' = new Array();';
	for ($z = 0; $z < count($values); $z++) {
		echo 'values'.$id.'['.$z.'] = new Array();';
		echo 'labels'.$id.'.push({label:\''.$labels[$z].'\'});';
	}
	for ($j = 0; $j < count($ticks); $j++) {
		for ($z = 0; $z < count($values); $z++) {
			if ($stackSeries !== false && $stackSeries[$j] > $yMax) {
				$yMax = $stackSeries[$j];
			} elseif ($values[$z][$j] > $yMax) {
				$yMax = $values[$z][$j];
			}
			echo 'values'.$id.'['.$z.'].push('.$values[$z][$j].');';
		}
		echo 'ticks'.$id.'.push(\''.$ticks[$j].'\');';
	}
	for ($z = 0; $z < count($values); $z++) {
		echo 'series'.$id.'.push(values'.$id.'['.$z.']);';
	}
	$height = 40 + 50 * count($ticks);

	echo 'jQuery(document).ready(function(){
			plot'.$id.' = jQuery.jqplot (\'chart'.$id.'\', series'.$id.', {
				height: '.$height.',';
	if ($stackSeries) {
		echo '		stackSeries: true,';
	}
	echo '			axesDefaults: {
					tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
					tickOptions: {
						angle: 0,
						fontSize: \'8px\',
						showGridline: false,
						showMark: false,
					},
					pad: 0
				},
				seriesDefaults: {
					showMarker: false,
					lineWidth: 1,
					fill: true,
					renderer:jQuery.jqplot.BarRenderer,
					pointLabels: {
						show:true,
						stackedValue: true
					},
					rendererOptions: {
						barDirection: \'horizontal\',
						fillToZero: true
					}
				},
				legend: {
					show:true, location: \'ne\'
				},
				series:
					labels'.$id.'
				,
				axes: {
					xaxis: {
						max: '.++$yMax.',
						min: 0,
						tickOptions: {
							angle: 0,
							showMark: true,
							formatString: \'%d\'
						}
					},
					yaxis: {
						renderer: jQuery.jqplot.CategoryAxisRenderer,
						ticks: ticks'.$id.'
					},
				},
			});
		});';
	echo 'jQuery(window).resize(function() {
		plot'.$id.'.replot();
	});'."\n";
	echo '//]]></script>';
	echo '<div id="chart'.$id.'"></div>';
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

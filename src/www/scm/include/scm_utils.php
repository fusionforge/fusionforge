<?php
/**
 * FusionForge SCM Library
 *
 * Copyright 2004-2005 (c) GForge LLC, Tim Perdue
 * Copyright 2010 (c), Franck Villaume - Capgemini
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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

function scm_header($params) {
	global $HTML;
	if (!forge_get_config('use_scm')) {
		exit_disabled();
	}

	$project = group_get_object($params['group']);
	if (!$project || !is_object($project)) {
		exit_no_group();
	} elseif ($project->isError()) {
		exit_error($project->getErrorMessage(),'scm');
	}

	if (!$project->usesSCM()) {
		exit_disabled();
	}
	/*
		Show horizontal links
	*/
	if (session_loggedin()) {
		$params['TITLES'][] = _('View Source Code');
		$params['DIRS'][] = '/scm/?group_id='.$params['group'];
		$params['TOOLTIPS'][] = array('title' => _('Online Source code browsing'), 'class' => 'tabtitle');
		$params['TITLES'][] = _('Reporting');
		$params['DIRS'][] = '/scm/reporting/?group_id='.$params['group'];
		$params['TOOLTIPS'][] = array('title' => _('Global statistics on this SCM repository'), 'class' => 'tabtitle');
		$params['TITLES'][] = _('Administration');
		$params['DIRS'][] = '/scm/admin/?group_id='.$params['group'];
		$params['TOOLTIPS'][] = array('title' => _('Administration page : enable / disable options'), 'class' => 'tabtitle');

		if (forge_check_perm('project_admin', $project->getID())) {
			$params['submenu'] = $HTML->subMenu(
				$params['TITLES'],
				$params['DIRS'],
				$params['TOOLTIPS']
				);
		}
	}

	$params['toptab'] = 'scm';
	site_project_header($params);
	echo '<div id="scm" class="scm">';
}

function scm_footer() {
	echo '</div>';
	site_project_footer(array());
}

function commitstime_graph($group_id, $chartid) {
	$g = group_get_object($group_id);
	$end = time();
	$res = db_query_params ('SELECT month, sum(commits) AS count
				FROM stats_cvs_group
				WHERE group_id = $1
				GROUP BY month ORDER BY month ASC',
				array($group_id));

	if (db_error()) {
		exit_error(db_error(),'scm');
	}

	echo '<script type="text/javascript">//<![CDATA['."\n";
	echo 'var values = new Array();';

	$firstDateInDB = 0;
	$data = array();
	while ($row = db_fetch_array($res)) {
		$data[$row[0]] = $row[1];
		if (!$firstDateInDB)
			$firstDateInDB = $row[0];
	}

	$start = $g->getStartDate();
	$monthsArr[] = date('Ym', $start);
	if ( $firstDateInDB < $monthsArr[0] ) {
		$monthsArr[0] = $firstDateInDB;
		$start = mktime(0, 0, 0, substr($monthsArr[0], 4, 2) , 1, substr($monthsArr[0], 0, 4));
	}
	$timeStampArr[] = mktime(0, 0, 0, substr($monthsArr[0], 4, 2) , 1, substr($monthsArr[0], 0, 4));
	$i = 0;
	while($start < $end) {
		$start = strtotime(date('Y-m-d', $start).' +1 month');
		$i++;
		$monthsArr[$i] = date('Ym', $start);
		$timeStampArr[$i] = mktime(0, 0, 0, substr($monthsArr[$i], 4, 2) , 1, substr($monthsArr[$i], 0, 4));
		if ($monthsArr[$i] == date('Ym', strtotime(date('Y-m-d', $end).' +1 month'))) {
			array_pop($monthsArr);
			array_pop($timeStampArr);
			$i--;
		}
	}

	$yMax = 0;
	for ($j = 0; $j < count($monthsArr); $j++) {
		echo 'var date = new Date(0);';
		echo 'date.setUTCSeconds('.$timeStampArr[$j].');';
		$indexkey = array_key_exists($monthsArr[$j], $data);
		if ($indexkey !== false) {
			echo 'var datevalues = '.$data[$monthsArr[$j]].';';
			if ($data[$monthsArr[$j]] > $yMax) {
				$yMax = $data[$monthsArr[$j]];
			}
		} else {
			echo 'var datevalues = 0;';
		}
		echo 'values.push([date, datevalues]);';
	}
	echo 'var plot'.$chartid.';';
	echo 'var minDate = new Date(0);';
	echo 'minDate.setUTCSeconds('.$timeStampArr[0].');';
	echo 'jQuery(document).ready(function(){
			plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', [values], {
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
				},
				legend: {
					show: false,
				},
				axes: {
					xaxis: {
						renderer: jQuery.jqplot.DateAxisRenderer,
						min: minDate,
						tickInterval: \'1 month\',
						tickOptions: {
							formatString: \'%Y/%m\'
						}
					},
					yaxis: {
						max: '.++$yMax.',
						min: 0,
						tickOptions: {
							angle: 0,
							showMark: true,
							formatString: \'%d\'
						}
					}
				},
				highlighter: {
					show: true,
					sizeAdjust: 2.5,
					showTooltip: true,
					tooltipAxes: \'y\'
				},
			});
		});';
	echo 'jQuery(window).resize(function() {
			plot'.$chartid.'.replot();
		});'."\n";
	echo '//]]></script>';
	echo '<div id="chart'.$chartid.'"></div>';
}

function commits_graph($group_id, $days, $chartid) {
	$start = time() - ($days * 60 * 60 * 24);
	$g = group_get_object($group_id);
	$end=time();
	if ( $start < $g->getStartDate()) {
		$start = $g->getStartDate();
	}
	$formattedmonth = date('Ym', $start);
	$res = db_query_params('SELECT u.realname,sum(commits) AS count
			FROM stats_cvs_user scu, users u
			WHERE u.user_id = scu.user_id
			AND scu.month >= $1
			AND group_id = $2
			GROUP BY realname ORDER BY count DESC',
			array($formattedmonth, $group_id));

	if (db_error()) {
		exit_error(db_error(), 'scm');
	}

	if (db_numrows($res)) {
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var data'.$chartid.' = new Array();';
		$i = 1;
		$lastvalue = 0;
		while ($row = db_fetch_array($res)) {
			if ($i <= 10) {
				echo 'data'.$chartid.'.push([\''.htmlentities($row[0]).' ('.$row[1].')\','.$row[1].']);';
			} elseif ($i > 10) {
				$lastvalue += $row[1];
			}
			$i++;
		}
		if ($i > 10) {
			echo 'data'.$chartid.'.push([\''._('Others').' ('.$lastvalue.')\','.$lastvalue.']);';
		}
		echo 'var plot'.$chartid.';';
		echo 'jQuery(document).ready(function(){
			plot'.$chartid.' = jQuery.jqplot (\'chart'.$chartid.'\', [data'.$chartid.'],
				{
					title : \''.utf8_decode(_("Commits By User")." (".strftime('%x',$start) ." - ". strftime('%x',$end) .")").'\',
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer,
						rendererOptions: {
							showDataLabels: true,
							dataLabels: \'percent\',
							sliceMargin: 5
						}
					},
					legend: {
						show:true,
						location: \'e\'
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
		echo '<p class="information">'._('No commits during this period.').'</p>';
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

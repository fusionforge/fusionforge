<?php
/**
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
 * Copyright 2017, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $group_id, $group, $taskboard;

$release_id = getIntFromRequest('release_id', NULL);

$release = new TaskBoardRelease($taskboard, $release_id);

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotplugindateAxisRenderer();
html_use_jqueryjqplotpluginhighlighter();

$taskboard->header(
		array(
			'title' => $taskboard->getName()._(': '). _('Releases')._(': ')._('Burndown chart')._(': ').$release->getTitle() ,
			'pagename' => _('Releases')._(': ')._('Burndown chart')._(': ').$release->getTitle(),
			'sectionvals' => array($group->getPublicName()),
			'group' => $group_id
		)
	);

if ($taskboard->isError()) {
	echo $HTML->error_msg($taskboard->getErrorMessage());
} else {
	echo html_e('div', array('id' => 'messages', 'style' => 'display: none;'), '', false);
}


// $xaxisData is used to have an every date on the X axis
$xaxisData = array();
$chartDate = $release->getStartDate();
while($chartDate <= $release->getEndDate()) {
	$xaxisData[] = array(date( 'r', $chartDate) , 0);
	$chartDate += 86400;
}

$release_volume = $release->getVolume();

if (!$release_volume) {
	echo $HTML->error_msg($taskboard->getErrorMessage());
}

// ideal burndown
$dataIdeal = array(
	array( $release->getStartDate() * 1000,  $release_volume['tasks']),
	array( $release->getEndDate() * 1000, 0)
);

$release_snapshots = $release->getSnapshots();
$dataRemainingTasks = array();
$dataRemainingEfforts = array();

foreach ($release_snapshots as $snapshot) {
	if (empty($dataRemainingTasks) && $snapshot['snapshot_date'] != $release->getStartDate()) {
		// initialize start point if snapshot is missing for the first day
		$dataRemainingTasks[] = array($release->getStartDate() * 1000, $release_volume['tasks']);
		$dataRemainingEfforts[] = array($release->getStartDate() * 1000, $release_volume['man_days']);
	}

	$dataRemainingTasks[] = array($snapshot['snapshot_date'] * 1000, ($release_volume['tasks'] - $snapshot['completed_tasks']));
	$dataRemainingEfforts[] = array($snapshot['snapshot_date'] * 1000, ($release_volume['man_days'] - $snapshot['completed_man_days']));
}

?>
<div id="taskboard-burndown-chart-nav">
	<button id="taskboard-view-btn"><?php echo _('Task Board'); ?></button>
	<br/>
</div>

<figure>
	<figcaption><?php echo  _("Burndown chart")._(': ').$release->getTitle() ?></figcaption>
	<div id="taskboard-burndown-chart">
	</div>
</figure>

<script>
	var burndownChart;
	var xaxisData = <?php echo json_encode($xaxisData); ?>;
	var dataRemainingTasks = <?php echo json_encode($dataRemainingTasks); ?>;
	var dataRemainingEfforts = <?php echo json_encode($dataRemainingEfforts); ?>;
	var dataRemainingIdeal = <?php echo json_encode($dataIdeal); ?>;

	jQuery( document ).ready(function( $ ) {
		jQuery('#taskboard-view-btn').click( function ( e ) {
			window.location = '<?php echo util_make_url('/plugins/'.$pluginTaskboard->name.'/?group_id='.$group_id.'&taskboard_id='.$taskboard->getID().'&_release='.$release_id ); ?>';
			e.preventDefault();
		});

		burndownChart = jQuery.jqplot(
			'taskboard-burndown-chart',
			[ xaxisData, dataRemainingIdeal, dataRemainingTasks, dataRemainingEfforts ],
			{
				axesDefaults: {
					pad : 1
				},
				seriesColors: [ '#000', '#DDDDDD', '#00FA9A', '#B22222' ],
				legend: {
					show: true,
					location: 'ne',
					xoffset: 12,
					yoffset: 12
				},
				axes : {
					xaxis : {
						renderer : jQuery.jqplot.DateAxisRenderer,
						tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
						tickOptions:{
							angle: -90,
							fontSize : '1.3em',
							formatString : '%Y-%m-%d'
						},
						numberTicks: <?php echo count($xaxisData) - 2; ?>,
						min: <?php echo $release->getStartDate() * 1000; ?>,
						max: <?php echo $release->getEndDate() * 1000; ?>
					},
					yaxis : {
						autoscale:true,
						min : 0,
						label: "<?php echo _('Completed tasks') ?>" ,
						labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
							labelOptions:{
							fontSize : '12px'
						}
					},
					y2axis: {
						autoscale:true,
						min : 0,
						tickOptions:{
						isMinorTick: true,
						formatString: "%.1f <?php echo _('m/d') ?>"
						}
					}
				},
				series:[
					{ show : false }, // to indicate all dates
					{ label : "<?php echo _('Ideal burndown') ;?>", lineWidth:1, markerOptions : { style : 'circle', size : 5 } },
					{ label : "<?php echo _('Remaining tasks') ;?>", lineWidth:1, markerOptions : { style : 'circle', size : 5 },  yaxis: 'yaxis' },
					{ label : "<?php echo _('Remaining efforts') ;?>", lineWidth:1, markerOptions : { style : 'circle', size : 5 } , yaxis:'y2axis' }
				],
				highlighter: {
					show: true,
					sizeAdjust: 8
				},
				cursor: {
					show: false
				}
			}
		);
	});
</script>

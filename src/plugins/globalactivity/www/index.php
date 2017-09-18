<?php
/**
 * Global Activity Page
 *
 * Copyright 1999 dtype
 * Copyright 2006 (c) GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Benoit Debaenst - TrivialDev
 * Copyright 2016, Roland Mas
 * http://fusionforge.org/
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/Activity.class.php';

global $HTML;

$received_begin = getStringFromRequest("start_date");
$received_end = getStringFromRequest("end_date");
$show = getArrayFromRequest('show');

$date_format = _('%Y-%m-%d');
$date_format_js = _('yy-mm-dd');

if (!$received_begin || $received_begin == 0) {
	$begin = (time() - (30 * 86400));
	$rendered_begin = strftime($date_format, $begin);
} else {
	$tmp = strptime($received_begin, $date_format);
	if (!$tmp) {
		$begin = (time() - (7 * 86400));
		$rendered_begin = strftime($date_format, $begin);
	} else {
		$begin = mktime(0, 0, 0, $tmp['tm_mon'] + 1, $tmp['tm_mday'], $tmp['tm_year'] + 1900);
		$rendered_begin = $received_begin;
	}
}
if ($begin < 0) {
    $begin = 0;
    $rendered_begin = strftime($date_format, $begin);
}

if (!$received_end || $received_end == 0) {
	$end = time();
	$rendered_end = strftime($date_format, $end);
} else {
	$tmp = strptime($received_end, $date_format);
	if (!$tmp) {
		$end = time();
		$rendered_end = strftime($date_format, $end);
	} else {
		$end = mktime(23, 59, 59, $tmp['tm_mon'] + 1, $tmp['tm_mday'], $tmp['tm_year'] + 1900);
		$rendered_end = $received_end;
	}
}

$plugin = plugin_get_object('globalactivity');

if (!forge_get_config('use_activity')) {
	exit_disabled();
}
if (!$plugin) {
	exit_disabled();
}

site_header(array('title'=>_('Global activity')));

$ids = array();
$texts = array();

try {
	$results = $plugin->getData($begin, $end, $show, $ids, $texts);
} catch (Exception $e) {
	exit_error($e->getMessage(), 'home');
}

if (count($ids) < 1) {
	echo $HTML->information(_('No Activity Found'));
} else {
?>

<div id="activity">
<div id="activity_left">

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div id="activity_select" >
<div id="activity_label"><?php echo _('Activity')._(':'); ?></div>
<?php echo html_build_multiple_select_box_from_arrays($ids, $texts, 'show[]', $show, count($texts), false); ?>
</div>

<div id="activity_startdate" >
<div id="activity_label_startdate"><?php echo _('Start Date')._(':'); ?></div>
<input id="datepicker_start" name="start_date" value="<?php echo $rendered_begin; ?>" size="10" maxlength="10" />
</div>

<div id="activity_enddate" >
<div id="activity_label_enddate"><?php echo _('End Date')._(':'); ?></div>
<input id="datepicker_end" name="end_date" value="<?php echo $rendered_end; ?>" size="10" maxlength="10" />
</div>

<div id="activity_submit" >
<input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" />
</div>

</form>
</div>

<div id="activity_right">
<?php
	if (count($results) < 1) {
		echo $HTML->information(_('No Activity Found'));
	} else {
		$displayTableTop = 0;
		$last_day = 0;
		$ffactivity = new Activity();
		foreach ($results as $arr) {

			$displayinfo = $ffactivity->getDisplayInfo($arr);
			if (!$displayinfo) {
				continue;
			}

			$group_id = $arr['group_id'];
			if (!$displayTableTop) {
				$theader = array();
				$theader[] = _('Project');
				$theader[] = _('Time');
				$theader[] = _('Activity');
				$theader[] = _('By');

				echo $HTML->listTableTop($theader);
				$displayTableTop = 1;
			}
			if ($last_day != strftime($date_format, $arr['activity_date'])) {
				echo '<tr class="tableheading"><td colspan="4">'.strftime($date_format, $arr['activity_date']).'</td></tr>';
				$last_day=strftime($date_format, $arr['activity_date']);
			}

			$cells = array();
			$cells[][] = util_make_link_g(group_get_object($group_id)->getUnixName(),$group_id,group_get_object($group_id)->getPublicName());
			$cells[][] = date('H:i:s',$arr['activity_date']);
			$cells[][] = $displayinfo;
			if (isset($arr['user_name']) && $arr['user_name']) {
				$cells[][] = util_display_user($arr['user_name'], $arr['user_id'],$arr['realname']);
			} else {
				$cells[][] = $arr['realname'];
			}
			echo $HTML->multiTableRow(array(), $cells);
		}
		if ($displayTableTop) {
			echo $HTML->listTableBottom();
		}
		if (!$displayTableTop) {
			echo $HTML->information(_('No Activity Found'));
		}
	}

	echo '</div>';
	echo '</div>';
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[

jQuery('#datepicker_start').datepicker({
  dateFormat: "<?php echo $date_format_js ?>"
});
jQuery('#datepicker_end').datepicker({
  dateFormat: "<?php echo $date_format_js ?>"
});

//]]>
<?php
echo html_ac(html_ap() - 1);
site_project_footer();

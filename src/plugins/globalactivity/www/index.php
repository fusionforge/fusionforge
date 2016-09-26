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

global $HTML;

$received_begin = getStringFromRequest("start_date");
$received_end = getStringFromRequest("end_date");
$show = getArrayFromRequest("show");

$date_format = _('%Y-%m-%d');

if (!$received_begin || $received_begin==0) {
	$begin = (time()-(30*86400));
	$rendered_begin = strftime($date_format, $begin);
} else {
	$tmp = strptime($received_begin, $date_format);
	if (!$tmp) {
		$begin = (time()-(7*86400));
		$rendered_begin = strftime($date_format, $begin);
	} else {
		$begin = mktime(0, 0, 0, $tmp['tm_mon']+1, $tmp['tm_mday'], $tmp['tm_year'] + 1900);
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
		$end = mktime(23, 59, 59, $tmp['tm_mon']+1, $tmp['tm_mday'], $tmp['tm_year'] + 1900);
		$rendered_end = $received_end;
	}
}

if ($begin > $end) {
	$tmp = $end;
	$end = $begin;
	$begin = $tmp;
	$tmp = $rendered_end;
	$rendered_end = $rendered_begin;
	$rendered_begin = $tmp;
}

if (!forge_get_config('use_activity')) {
	exit_disabled();
}

site_header(array('title'=>_('Global activity')));

$ids = array();
$texts = array();

if (forge_get_config('use_forum')) {
	$ids[]		= 'forumpost';
	$texts[]	= _('Forum Post');
}

if (forge_get_config('use_tracker')) {
	$ids[]		= 'trackeropen';
	$texts[]	= _('Tracker Opened');
	$ids[]		= 'trackerclose';
	$texts[]	= _('Tracker Closed');
}

if (forge_get_config('use_news')) {
	$ids[]		= 'news';
	$texts[]	= _('News');
}

if (forge_get_config('use_pm')) {
	$ids[]		= 'taskopen';
	$texts[]	= _('Tasks Opened');
	$ids[]		= 'taskclose';
	$texts[]	= _('Tasks Closed');
	$ids[]		= 'taskdelete';
	$texts[]	= _('Tasks Deleted');
}

if (forge_get_config('use_frs')) {
	$ids[]		= 'frsrelease';
	$texts[]	= _('FRS Release');
}

if (forge_get_config('use_docman')) {
	$ids[]		= 'docmannew';
	$texts[]	= _('New Documents');
	$ids[]		= 'docmanupdate';
	$texts[]	= _('Updated Documents');
	$ids[]		= 'docgroupnew';
	$texts[]	= _('New Directories');
}

if (count($show) < 1) {
	$section = $ids;
} else {
	$section = $show;
}

$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
			AND section = ANY ($3) ORDER BY activity_date DESC',
			array($begin,
				$end,
				db_string_array_to_any_clause($section)));

if (db_error()) {
	exit_error(db_error(), 'home');
}

$results = array();
while ($arr = db_fetch_array($res)) {
	$results[] = $arr;
}

$res = db_query_params('SELECT group_id FROM groups WHERE status=$1',
    array('A'));

if (db_error()) {
	exit_error(db_error(), 'home');
}

// If plugins wants to add activities.
while ($arr = db_fetch_array($res)) {
	$group_id = $arr['group_id'];
	$hookParams['group'] = $group_id;
	$hookParams['results'] = &$results;
	$hookParams['show'] = &$show;
	$hookParams['begin'] = $begin;
	$hookParams['end'] = $end;
	$hookParams['ids'] = &$ids;
	$hookParams['texts'] = &$texts;
	plugin_hook("activity", $hookParams);
}

if (count($show) < 1) {
	$show = $ids;
}

foreach ($show as $showthis) {
	if (array_search($showthis, $ids) === false) {
		exit_error(_('Invalid Data Passed to query'), 'home');
	}
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
<input name="start_date" value="<?php echo $rendered_begin; ?>" size="10" maxlength="10" />
</div>

<div id="activity_enddate" >
<div id="activity_label_enddate"><?php echo _('End Date')._(':'); ?></div>
<input name="end_date" value="<?php echo $rendered_end; ?>" size="10" maxlength="10" />
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

		function date_compare($a, $b)
		{
			if ($a['activity_date'] == $b['activity_date']) {
				return 0;
			}
			return ($a['activity_date'] > $b['activity_date']) ? -1 : 1;
		}

		$cached_perms = array();
		function check_perm_for_activity($arr) {
			global $cached_perms;
			$s = $arr['section'];
			$ref = $arr['ref_id'];
			$group_id = $arr['group_id'];

			if (!isset($cached_perms[$s][$ref])) {
				switch ($s) {
					case 'scm': {
						$cached_perms[$s][$ref] = forge_check_perm('scm', $group_id, 'read');
						break;
					}
					case 'trackeropen':
					case 'trackerclose': {
						$cached_perms[$s][$ref] = forge_check_perm('tracker', $ref, 'read');
						break;
					}
					case 'frsrelease': {
						$cached_perms[$s][$ref] = forge_check_perm('frs', $ref, 'read');
						break;
					}
					case 'forumpost':
					case 'news': {
						$cached_perms[$s][$ref] = forge_check_perm('forum', $ref, 'read');
						break;
					}
					case 'taskopen':
					case 'taskclose':
					case 'taskdelete': {
						$cached_perms[$s][$ref] = forge_check_perm('pm', $ref, 'read');
						break;
					}
					case 'docmannew':
					case 'docmanupdate':
					case 'docgroupnew': {
						$cached_perms[$s][$ref] = forge_check_perm('docman', $group_id, 'read');
						break;
					}
					default: {
						// Must be a bug somewhere, we're supposed to handle all types
						$cached_perms[$s][$ref] = false;
					}
				}
			}
			return $cached_perms[$s][$ref];
		}

		usort($results, 'date_compare');

		$displayTableTop = 0;
		$j = 0;
		$last_day = 0;
		foreach ($results as $arr) {

			$group_id = $arr['group_id'];
			if (!forge_check_perm('project_read', $group_id)) {
				continue;
			}

			if (!check_perm_for_activity($arr)) {
				continue;
			}
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
				//	echo $HTML->listTableBottom($theader);
				echo '<tr class="tableheading"><td colspan="4">'.strftime($date_format, $arr['activity_date']).'</td></tr>';
				//	echo $HTML->listTableTop($theader);
				$last_day=strftime($date_format, $arr['activity_date']);
			}
			switch (@$arr['section']) {
				case 'scm': {
					$icon = html_image('ic/cvs16b.png','','',array('alt'=>_('Source Code')));
					$url = util_make_link('/scm/'.$arr['ref_id'].$arr['subref_id'],_('scm commit')._(': ').$arr['description']);
					break;
				}
				case 'trackeropen': {
					$icon = html_image('ic/tracker20g.png','','',array('alt'=>_('Trackers')));
					$url = util_make_link('/tracker/?func=detail&atid='.$arr['ref_id'].'&aid='.$arr['subref_id'].'&group_id='.$arr['group_id'],_('Tracker Item').' [#'.$arr['subref_id'].'] '.$arr['description'].' '._('Opened'));
					break;
				}
				case 'trackerclose': {
					$icon = html_image('ic/tracker20g.png','','',array('alt'=>_('Trackers')));
					$url = util_make_link('/tracker/?func=detail&atid='.$arr['ref_id'].'&aid='.$arr['subref_id'].'&group_id='.$arr['group_id'],_('Tracker Item').' [#'.$arr['subref_id'].'] '.$arr['description'].' '._('Closed'));
					break;
				}
				case 'frsrelease': {
					$icon = html_image('ic/cvs16b.png','','',array('alt'=>_('Files')));
					$url = util_make_link('/frs/?release_id='.$arr['subref_id'].'&group_id='.$arr['group_id'],_('FRS Release').' '.$arr['description']);
					break;
				}
				case 'forumpost': {
					$icon = html_image('ic/forum20g.png','','',array('alt'=>_('Forum')));
					$url = util_make_link('/forum/message.php?msg_id='.$arr['subref_id'].'&group_id='.$arr['group_id'],_('Forum Post').' '.$arr['description']);
					break;
				}
				case 'news': {
					$icon = html_image('ic/write16w.png','','',array('alt'=>_('News')));
					$url = util_make_link('/forum/forum.php?forum_id='.$arr['subref_id'],_('News').' '.$arr['description']);
					break;
				}
				case 'taskopen': {
					$icon = html_image('ic/taskman20w.png','','',array('alt'=>_('Tasks')));
					$url = util_make_link('/pm/task.php?func=detailtask&project_task_id='.$arr['subref_id'].'&group_id='.$arr['group_id'].'&group_project_id='.$arr['ref_id'],_('Tasks').' '.$arr['description']);
					break;
				}
				case 'taskclose': {
					$icon = html_image('ic/taskman20w.png','','',array('alt'=>_('Tasks')));
					$url = util_make_link('/pm/task.php?func=detailtask&project_task_id='.$arr['subref_id'].'&group_id='.$arr['group_id'].'&group_project_id='.$arr['ref_id'],_('Tasks').' '.$arr['description']);
					break;
				}

				case 'taskdelete': {
					$icon = html_image('ic/taskman20w.png','','',array('alt'=>_('Tasks')));
					$url = util_make_link('/pm/task.php?func=detailtask&project_task_id='.$arr['subref_id'].'&group_id='.$arr['group_id'].'&group_project_id='.$arr['ref_id'],_('Tasks').' '.$arr['description']);
					break;
				}
				case 'docmannew':
				case 'docmanupdate': {
					$icon = html_image('ic/docman16b.png', '', '', array('alt'=>_('Documents')));
					$url = util_make_link('docman/?group_id='.$arr['group_id'].'&view=listfile&dirid='.$arr['ref_id'],_('Document').' '.$arr['description']);
					break;
				}
				case 'docgroupnew': {
					$icon = html_image('ic/cfolder15.png', '', '', array("alt"=>_('Directory')));
					$url = util_make_link('docman/?group_id='.$arr['group_id'].'&view=listfile&dirid='.$arr['subref_id'],_('Directory').' '.$arr['description']);
					break;
				}
				default: {
					$icon = isset($arr['icon']) ? $arr['icon'] : '';
					$url = '<a href="'.$arr['link'].'">'.$arr['title'].'</a>';
				}
			}
			$cells = array();
			$cells[][] = util_make_link_g(group_get_object($group_id)->getUnixName(),$group_id,group_get_object($group_id)->getPublicName());
			$cells[][] = date('H:i:s',$arr['activity_date']);
			$cells[][] = $icon .' '.$url;
			if (isset($arr['user_name']) && $arr['user_name']) {
				$cells[][] = util_display_user($arr['user_name'], $arr['user_id'],$arr['realname']);
			} else {
				$cells[][] = $arr['realname'];
			}
			echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($j++, true)), $cells);
		}
		if ($displayTableTop) {
			echo $HTML->listTableBottom($theader);
		}
		if (!$displayTableTop) {
			echo $HTML->information(_('No Activity Found'));
		}
	}

	echo '</div>';
	echo '</div>';
}


site_project_footer();

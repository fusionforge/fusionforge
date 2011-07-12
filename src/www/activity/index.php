<?php
/**
 * Project Activity Page
 *
 * Copyright 1999 dtype
 * Copyright 2006 (c) GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest("group_id");
$received_begin = getStringFromRequest("start_date");
$received_end = getStringFromRequest("end_date");
$show = getArrayFromRequest("show");

session_require_perm('project_read', $group_id);

$date_format = _('%Y-%m-%d');

if (!$received_begin || $received_begin==0) {
	$begin = (time()-(30*86400));
	$rendered_begin = strftime($date_format, $begin);
} else {
	$tmp = strptime($received_begin, $date_format);
	if (!$tmp) {
		$begin = (time()-(30*86400));
		$rendered_begin = strftime($date_format, $begin);
	} else {
		$begin = mktime(0, 0, 0, $tmp['tm_mon']+1, $tmp['tm_mday'], $tmp['tm_year'] + 1900);
		$rendered_begin = $received_begin;
	}
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
		$end = mktime(0, 0, 0,$tmp['tm_mon']+1,$tmp['tm_mday'],$tmp['tm_year'] + 1900);
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

if (!$group_id) {
	exit_no_group();
}
$group=group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_permission_denied('home');
}

site_project_header(array('title'=>_('Activity'), 'group'=>$group_id, 'toptab'=>'activity'));

$ids = array();
$texts = array();

if (forge_get_config('use_forum') && $group->usesForum()) {
	$ids[]		= 'forumpost';
	$texts[]	= _('Forum Post');
}

if (forge_get_config('use_tracker') && $group->usesTracker()) {
	$ids[]		= 'trackeropen';
	$texts[]	= _('Tracker Opened');
	$ids[]		= 'trackerclose';
	$texts[]	= _('Tracker Closed');
}

if (forge_get_config('use_news') && $group->usesNews()) {
	$ids[]		= 'news';
	$texts[]	= _('News');
}

if (forge_get_config('use_frs') && $group->usesFRS()) {
	$ids[]		= 'frsrelease';
	$texts[]	= _('FRS Release');
}

if (forge_get_config('use_frs') && $group->usesDocman()) {
	$ids[]		= 'docmannew';
	$texts[]	= _('New Documents');
	$ids[]		= 'docmanupdate';
	$texts[]	= _('Updated Documents');
}

if (count($show) < 1) {
	$section = $ids;
} else {
	$section = $show;
}

$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
			AND group_id = $3 AND section = ANY ($4) ORDER BY activity_date DESC',
			array($begin,
				$end,
				$group_id,
				db_string_array_to_any_clause($section)));

if (db_error()) {
	exit_error(db_error(), 'home');
}

$results = array();
while ($arr = db_fetch_array($res)) {
	$results[] = $arr;
}

// If plugins wants to add activities.
$hookParams['group'] = $group_id;
$hookParams['results'] = &$results;
$hookParams['show'] = &$show;
$hookParams['begin'] = $begin;
$hookParams['end'] = $end;
$hookParams['ids'] = &$ids;
$hookParams['texts'] = &$texts;
plugin_hook("activity", $hookParams);

if (count($show) < 1) {
	$show = $ids;
}
foreach ($show as $showthis) {
	if (array_search($showthis, $ids) === false) {
		exit_error(_('Invalid Data Passed to query'), 'home');
	}
}
$multiselect = html_build_multiple_select_box_from_arrays($ids, $texts, 'show[]', $show, 5, false);

?>
<br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<table border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td><strong><?php echo _('Activity') ?></strong></td>
		<td><strong><?php echo _('Start') ?></strong></td>
		<td><strong><?php echo _('End') ?></strong></td>
		<td></td>
	</tr>
	<tr>
		<td><?php echo $multiselect; ?></td>
		<td valign="top"><input name="start_date"
			value="<?php echo $rendered_begin; ?>" size="10" maxlength="10" /></td>
		<td valign="top"><input name="end_date"
			value="<?php echo $rendered_end; ?>" size="10" maxlength="10" /></td>
		<td valign="top"><input type="submit" name="submit"
			value="<?php echo _('Submit'); ?>" /></td>
	</tr>
</table>
</form>
<?php
if (count($results) < 1) {
	echo '<p class="warning_msg">' . _('No Activity Found') . '</p>';
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
				case 'commit':
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
				case 'docmannew':
				case 'docmanupdate': {
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

	$theader = array();
	$theader[] = _('Time');
	$theader[] = _('Activity');
	$theader[] = _('By');

	echo '<br/>';
	echo $HTML->listTableTop($theader);

	$j = 0;
	$last_day = 0;
	foreach ($results as $arr) {
		if (!check_perm_for_activity($arr)) {
			continue;
		}
		if ($last_day != strftime($date_format,$arr['activity_date'])) {
			//	echo $HTML->listTableBottom($theader);
			echo '<tr class="tableheading"><td colspan="3">'.strftime($date_format,$arr['activity_date']).'</td></tr>';
			//	echo $HTML->listTableTop($theader);
			$last_day=strftime($date_format,$arr['activity_date']);
		}
		switch (@$arr['section']) {
			case 'commit': {
				$icon = html_image("ic/cvs16b.png","20","20",array("alt"=>"SCM"));
				$url = util_make_link('/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Commit for Tracker Item').' [#'.$arr['subref_id'].'] '.$arr['description']);
				break;
			}
			case 'trackeropen': {
				$icon = html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
				$url = util_make_link('/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Tracker Item').' [#'.$arr['subref_id'].' '.$arr['description'].' ] '._('Opened'));
				break;
			}
			case 'trackerclose': {
				$icon = html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
				$url = util_make_link('/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Tracker Item').' [#'.$arr['subref_id'].' '.$arr['description'].' ] '._('Closed'));
				break;
			}
			case 'frsrelease': {
				$icon = html_image("ic/cvs16b.png","20","20",array("alt"=>"SCM"));
				$url = util_make_link('/frs/?release_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('FRS Release').' '.$arr['description']);
				break;
			}
			case 'forumpost': {
				$icon = html_image("ic/forum20g.png","20","20",array("alt"=>"Forum"));
				$url = util_make_link('/forum/message.php?msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Forum Post ').' '.$arr['description']);
				break;
			}
			case 'news': {
				$icon = html_image("ic/write16w.png","20","20",array("alt"=>"News"));
				$url = util_make_link('/forum/forum.php?forum_id='.$arr['subref_id'],_('News').' '.$arr['description']);
				break;
			}
			case 'docmannew':
			case 'docmanupdate': {
				$icon = html_image("ic/docman16b.png", "20", "20", array("alt"=>"Documents"));
				$url = util_make_link('docman/?group_id='.$arr['group_id'].'&view=listfile&dirid='.$arr['ref_id'],_('Document').' '.$arr['description']);
				break;
			}
			default: {
				$icon = isset($arr['icon']) ? $arr['icon'] : '';
				$url = '<a href="'.$arr['link'].'">'.$arr['title'].'</a>';
			}
		}
		echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;'.date('H:i:s',$arr['activity_date']).'</td>
		<td>'.$icon .' '.$url.'</td><td>';

		if (isset($arr['user_name']) && $arr['user_name']) {
			echo util_display_user($arr['user_name'], $arr['user_id'],$arr['realname']);
		} else {
			echo $arr['realname'];
		}
		echo '</td></tr>';
	}
	echo $HTML->listTableBottom($theader);
}

site_project_footer(array());

?>

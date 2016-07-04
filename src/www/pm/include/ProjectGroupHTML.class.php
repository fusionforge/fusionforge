<?php
/**
 * Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

require_once $gfcommon.'pm/ProjectGroup.class.php';

function pm_header($params) {
	// XXX ogi: What to do with these?
	global $group_id,$group_project_id,$HTML,$pg;

	if (!forge_get_config('use_pm')) {
		exit_disabled();
	}

	// Required by site_project_header
	$params['group']=$group_id;
	$params['toptab']='pm';

	// Only projects can use the Project Manager, and only if they have it turned on
	$project = group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}

	if (!$project->usesPM()) {
		exit_disabled('home');
	}

	$labels = array();
	$links = array();
	$attr = array();

	if (forge_check_perm('pm_admin', $group_id)) {
		$labels[] = _('General Admin');
		$links[] = '/pm/admin/?group_id='.$group_id;
		$attr[] = '';
	}

	$labels[] = _('View Subprojects');
	$links[]  = '/pm/?group_id='.$group_id;
	$attr[] = '';

	if ($group_project_id) {
		$labels[] = (($pg) ? $pg->getName() : '');
		$links[]  = '/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=browse';
		$attr[] = '';
		if (session_loggedin()) {
			$labels[] = _('Add Task');
			$links[]  = '/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=addtask';
			$attr[] = '';
		}
		if ($group_project_id) {
			$gantt_width = 820;
			$gantt_height = 680;
			$gantt_url = "/pm/task.php?group_id=$group_id&group_project_id=$group_project_id&func=ganttpage";
			$gantt_title = _('Gantt Chart');
			$gantt_winopt = 'scrollbars=yes,resizable=yes,toolbar=no,height=' . $gantt_height . ',width=' . $gantt_width;
			$labels[] = $gantt_title;
			$links[]  = $gantt_url;
			$attr[] = array('onclick' => 'window.open(this.href, \''.preg_replace('/\s/' , '_' , $gantt_title).'\', \''.$gantt_winopt.'\'); return false;');
		}

		// Import/Export using CSV files.
		$labels[] = _('Import/Export CSV');
		$links[]  = '/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=csv';
		$attr[] = '';
	}

	if ($pg && is_object($pg) && forge_check_perm ('pm', $pg->getID(), 'manager')) {
		$labels[] = _('Reporting');
		$links[]  = '/pm/reporting/?group_id='.$group_id;
		$attr[] = '';
		$labels[] = _('Administration');
		$links[]  = '/pm/admin/?group_id='.$group_id.'&group_project_id='.$group_project_id.'&update_pg=1';
		$attr[] = '';
	} elseif (forge_check_perm ('pm_admin', $group_id)) {
		$labels[] = _('Reporting');
		$links[]  = '/pm/reporting/?group_id='.$group_id;
		$attr[] = '';
		$labels[] = _('Administration');
		$links[]  = '/pm/admin/?group_id='.$group_id;
		$attr[] = '';
	}

	if(!empty($labels)) {
		$params['submenu'] = $HTML->subMenu($labels, $links, $attr);
	}

	site_project_header($params);

	if ($pg)
		plugin_hook ("blocks", "tasks_".$pg->getName());
}

function pm_footer($params = array()) {
	site_project_footer($params);
}

class ProjectGroupHTML extends ProjectGroup {

	function __construct(&$Group, $group_project_id=false, $arr=false) {
		parent::__construct($Group, $group_project_id, $arr);
	}

	function statusBox($name='status_id',$checked='xyxy',$show_100=true,$text_100='None') {
		return html_build_select_box($this->getStatuses(),$name,$checked,$show_100,$text_100);
	}

	function categoryBox($name='category_id',$checked='xzxz',$show_100=true,$text_100='None') {
		return html_build_select_box($this->getCategories(),$name,$checked,$show_100,$text_100);
	}

	function groupProjectBox($name='group_project_id',$checked='xzxz',$show_100=true,$text_100='None') {
		$res=db_query_params ('SELECT group_project_id,project_name
			FROM project_group_list
			WHERE group_id=$1',
			array($this->Group->getID()));
		return html_build_select_box($res,$name,$checked,$show_100,$text_100);
	}

	function percentCompleteBox($name = 'percent_complete', $selected = 0, $display = true) {
		$vals = array(0);
		$texts = array(_('Not Started'));
		$checked_val = 0;
		for ($i = 5; $i < 101; $i += 5) {
			$vals[] = $i;
			$texts[] = $i.'%';
			if ($i == $selected) {
				$checked_val = $i;
			}
		}
		$html = html_build_select_box_from_arrays($vals, $texts, $name, $checked_val, false, '', false, '');
		if ($display) {
			echo $html;
		} else {
			return $html;
		}
	}

	function showMonthBox($name, $select_month = 0, $display = true) {
		$vals = array();
		$monthlist = array(
			_('January'),
			_('February'),
			_('March'),
			_('April'),
			_('May'),
			_('June'),
			_('July'),
			_('August'),
			_('September'),
			_('October'),
			_('November'),
			_('December'));
		$checked_val = 1;
		for ($i = 1; $i <= count($monthlist); $i++) {
			$vals[] = $i;
			if ($i == $select_month) {
				$checked_val = $i;
			}
		}
		$html = html_build_select_box_from_arrays($vals, $monthlist, $name, $checked_val, false, '', false, '');
		if ($display) {
			echo $html;
		} else {
			return $html;
		}
	}

	function showDayBox($name, $day = 1, $display = true) {
		$vals = array();
		$checked_val = 1;
		for ($i = 1; $i <= 31; $i++) {
			$vals[] = $i;
			if ($i == $day) {
				$checked_val = $i;
			}
		}
		$html = html_build_select_box_from_arrays($vals, $vals, $name, $checked_val, false, '', false, '');
		if ($display) {
			echo $html;
		} else {
			return $html;
		}
	}

	function showYearBox($name, $year = 1, $display = true) {
		$vals = array();
		$current_year = date('Y');
		$checked_val = $current_year;
		for ($i = $current_year - 5; $i <= $current_year + 8; $i++) {
			$vals[] = $i;
			if ($i == $year) {
				$checked_val = $i;
			}
		}
		$html = html_build_select_box_from_arrays($vals, $vals, $name, $checked_val, false, '', false, '');
		if ($display) {
			echo $html;
		} else {
			return $html;
		}
	}

	function showHourBox($name, $hour = 1, $display = true) {
		$vals = array();
		$checked_val = 1;
		for ($i=0; $i<=23; $i++) {
			$vals[] = $i;
			if ($i == $hour) {
				$checked_val = $i;
			}
		}
		$html = html_build_select_box_from_arrays($vals, $vals, $name, $checked_val, false, '', false, '');
		if ($display) {
			echo $html;
		} else {
			return $html;
		}
	}

	function showMinuteBox($name, $minute = 0, $display = true) {
		$vals = array();
		$checked_val = 0;
		for ($i = 0; $i <= 45; $i = $i + 15) {
			$vals[] = $i;
			if ($i == $minute) {
				$checked_val = $i;
			}
		}
		$html = html_build_select_box_from_arrays($vals, $vals, $name, $checked_val, false, '', false, '');
		if ($display) {
			echo $html;
		} else {
			return $html;
		}
	}

	function renderAssigneeList($assignee_ids) {
		$techs = user_get_objects($assignee_ids);
		$return = '';
		for ($i=0; $i<count($techs); $i++) {
			$return .= $techs[$i]->getRealName().'<br />';
		}
		return $return;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

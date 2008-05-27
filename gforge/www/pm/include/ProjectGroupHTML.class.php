<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once $gfcommon.'pm/ProjectGroup.class.php';

function pm_header($params) {
	// XXX ogi: What to do with these?
	global $group_id,$is_pm_page,$words,$group_project_id,$HTML,$order,$pg,$sys_use_pm;

	if (!$sys_use_pm) {
		exit_disabled();
	}

	//required by site_project_header
	$params['group']=$group_id;
	$params['toptab']='pm';

	//only projects can use the bug tracker, and only if they have it turned on
	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}

	if (!$project->usesPm()) {
		exit_error(_('Error'),_('This Project Has Turned Off The Task Manager'));
	}

	site_project_header($params);

	$labels = array();
	$links = array();

	if ($group_project_id) {
		$labels[] = (($pg) ? $pg->getName() .': ' : '') ._('Browse tasks');
		$links[]  = '/pm/task.php?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=browse';
		if (session_loggedin()) {
			$labels[] = _('Add task');
			$links[]  = '/pm/task.php?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=addtask';
		}
		if ($group_project_id) {
			$gantt_width = 820;
			$gantt_height = 680;
			$gantt_url = "/pm/task.php?group_id=$group_id&group_project_id=$group_project_id&func=ganttpage";
			$gantt_title = _('Gantt Chart');
			$gantt_winopt = 'scrollbars=yes,resizable=yes,toolbar=no,height=' . $gantt_height . ',width=' . $gantt_width;
			$labels[] = $gantt_title;
			$links[]  = $gantt_url . '" onclick="window.open(this.href, \'' . preg_replace('/\s/' , '_' , $gantt_title)
			. '\', \'' . $gantt_winopt . '\'); return false;';
		}
		//upload/download as CSV files
		$labels[] = _('Download as CSV');
		$links[]  = '/pm/task.php?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=downloadcsv';
		$labels[] = _('Upload CSV');
		$links[]  = '/pm/task.php?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=uploadcsv';
	}
	if ($pg && is_object($pg) && $pg->userIsAdmin()) {
		$labels[] = _('Reporting');
		$links[]  = '/pm/reporting/?group_id='.$group_id;
		$labels[] = _('Admin');
		$links[]  = '/pm/admin/?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;update_pg=1';
	}
	if(!empty($labels)) {
		echo ($HTML->subMenu($labels,$links));
	}

}

function pm_footer($params) {
	site_project_footer($params);
}

class ProjectGroupHTML extends ProjectGroup {

	function ProjectGroupHTML(&$Group, $group_project_id=false, $arr=false) {
		if (!$this->ProjectGroup($Group,$group_project_id,$arr)) {
			return false;
		} else {
			return true;
		}
	}

	function statusBox($name='status_id',$checked='xyxy',$show_100=true,$text_100='None') {
		return html_build_select_box($this->getStatuses(),$name,$checked,$show_100,$text_100);
	}

	function categoryBox($name='category_id',$checked='xzxz',$show_100=true,$text_100='None') {
		return html_build_select_box($this->getCategories(),$name,$checked,$show_100,$text_100);
	}

	function groupProjectBox($name='group_project_id',$checked='xzxz',$show_100=true,$text_100='None') {
		$res=db_query("SELECT group_project_id,project_name 
			FROM project_group_list 
			WHERE group_id='".$this->Group->getID()."'");
		return html_build_select_box($res,$name,$checked,$show_100,$text_100);
	}

	function percentCompleteBox($name='percent_complete',$selected=0) {
		echo '
		<select name="'.$name.'">';
		echo '
		<option value="0">'._('Not Started'). '</option>';
		for ($i=5; $i<101; $i+=5) {
			echo '
			<option value="'.$i.'"';
			if ($i==$selected) {
				echo ' selected="selected"';
			}
			echo '>'.$i.'%</option>';
		}
		echo '
		</select>';
	}

	function showMonthBox($name,$select_month=0) {
		echo '
		<select name="'.$name.'" size="1">';
		$monthlist = array(
			'1'=>_('January'),
			'2'=>_('February'),
			'3'=>_('March'),
			'4'=>_('April'),
			'5'=>_('May'),
			'6'=>_('June'),
			'7'=>_('July'),
			'8'=>_('August'),
			'9'=>_('September'),
			'10'=>_('October'),
			'11'=>_('November'),
			'12'=>_('December'));

		for ($i=1; $i<=count($monthlist); $i++) {
			if ($i == $select_month) {
				echo '
				<option selected="selected" value="'.$i.'">'.$monthlist[$i].'</option>';
			} else {
				echo '
				<option value="'.$i.'">'.$monthlist[$i].'</option>';
			}
		}
		echo '
		</select>';
	}

	function showDayBox($name,$day=1) {
		echo '
		<select name="'.$name.'" size="1">';
		for ($i=1; $i<=31; $i++) {
			if ($i == $day) {
				echo '
				<option selected="selected" value="'.$i.'">'.$i.'</option>';
			} else {
				echo '
				<option value="'.$i.'">'.$i.'</option>';
			}
		}
		echo '
		</select>';
	}

	function showYearBox($name,$year=1) {
		echo '
		<select name="'.$name.'" size="1">';
		for ($i=1999; $i<=2013; $i++) {
			if ($i == $year) {
				echo '
				<option selected="selected" value="'.$i.'">'.$i.'</option>';
			} else {
				echo '
				<option value="'.$i.'">'.$i.'</option>';
			}
		}
		echo '
		</select>';
	}

	function showHourBox($name,$hour=1) {

		echo '
		<select name="'.$name.'" size="1">';
		for ($i=0; $i<=23; $i++) {
			if ($i == $hour) {
				echo '
				<option selected="selected" value="'.$i.'">'.$i.'</option>';
			} else {
				echo '
				<option value="'.$i.'">'.$i.'</option>';
			}
		}
		echo '
		</select>';
	}

	function showMinuteBox($name,$minute=0) {
		echo '	<select name="'.$name.'" size="1">';
		for ($i=0; $i<=45; $i=$i+15) {
			if ($i == $minute) {
				echo '	<option selected="selected" value="'.$i.'">'.$i.'</option>';
			} else {
				echo '
				<option value="'.$i.'">'.$i.'</option>';
			}
		}
		echo '
		</select>';
	}

	function renderAssigneeList($assignee_ids) {
		$techs =& user_get_objects($assignee_ids);
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

?>

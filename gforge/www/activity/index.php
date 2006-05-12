<?php
/**
 * Project Activity Page
 *
 * Copyright 2006 (c) GForge, LLC
 * http://gforge.org
 *
 * @version   $Id$
 */


require_once('pre.php');    

/*
	Project Summary Page
	Written by dtype Oct. 1999
*/
$group_id = getIntFromRequest("group_id");
$begin = getStringFromRequest("start_date");
$end = getStringFromRequest("end_date");
$show=getArrayFromRequest("show");

if (!$begin || $begin==0) {
	$begin = (time()-(30*86400));
} else {
	$begin = strtotime($begin);
}
if (!$end || $end==0) {
	$end = time();
} else {
	$end=strtotime($end)+86400;
}
if ($begin > $end) {
	$endtmp=$end;
	$end=$begin;
	$begin=$endtmp;
}
if (!$group_id) {
	exit_no_group();
}
$group=group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_permission_denied();
}

site_project_header(array('title'=>$Language->getText('projectactivity','headertype'),'group'=>$group_id,'toptab'=>'activity'));

$ids=array();
$ids[]='commit';
$ids[]='trackeropen';
$ids[]='trackerclose';
$ids[]='frsrelease';
$ids[]='forumpost';

$texts=array();
$texts[]='Commits';
$texts[]='Tracker Opened';
$texts[]='Tracker Closed';
$texts[]='FRS Release';
$texts[]='Forum Post';

if (count($show) < 1) {
	$show=$ids;
}
foreach ($show as $showthis) {
	if (array_search($showthis,$ids) === false) {
		exit_error('Error','Invalid Data Passed to query');
	}
}
$multiselect=html_build_multiple_select_box_from_arrays($ids,$texts,'show[]',$show,5,false);

$sql="SELECT * FROM activity_vw WHERE activity_date BETWEEN '".$begin."' AND '".$end."'
	AND group_id='$group_id' AND section IN ('".implode("','",$show)."') ORDER BY activity_date DESC";
//echo $sql;
$res=db_query($sql);
echo db_error();

$rows=db_numrows($res);
if ($rows<1) {
	echo 'No Activity Found';
} else {

	?>
<br />
<table border="0" cellspacing="0" cellpadding="3">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<tr>
	<td><strong><?php echo $Language->getText('projectactivity','headeractivity') ?></strong></td>
	<td><strong><?php echo $Language->getText('projectactivity','startdate') ?></strong></td>
	<td><strong><?php echo $Language->getText('projectactivity','enddate') ?></strong></td>
	<td></td>
</tr>
<tr>
	<td><?php echo $multiselect; ?></td>
	<td valign="top"><input name="start_date" value="<?php echo date('Y-m-d',$begin); ?>" size="10" maxlength="10" /></td>
	<td valign="top"><input name="end_date" value="<?php echo date('Y-m-d',$end); ?>" size="10" maxlength="10" /></td>
	<td valign="top"><input type="submit" name="submit" value="Submit"></td>
</tr>
</form>
</table>
<br />
	<?php

	$theader=array();
	$theader[]=$Language->getText('projectactivity','headertime');
	$theader[]=$Language->getText('projectactivity','headeractivity');
	$theader[]=$Language->getText('projectactivity','headerperson');

	echo $HTML->listTableTop($theader);

	$j=0;
	while ($arr =& db_fetch_array($res)) {
		if ($last_day != date('Y-M-d',$arr['activity_date'])) {
		//	echo $HTML->listTableBottom($theader);
			echo '<tr class="tableheading"><td colspan="3">'.date('Y-M-d',$arr['activity_date']).'</td>';
		//	echo $HTML->listTableTop($theader);
			$last_day=date('Y-M-d',$arr['activity_date']);
		}
		switch ($arr['section']) {
			case 'commit': {
				$icon=html_image("ic/cvs16b.png","20","20",array("border"=>"0","ALT"=>"SCM"));
				$url='<a href="/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'].'">Commit for Tracker Item [#'.$arr['subref_id'].'] '.$arr['description'].' </a>';
				break;
			}
			case 'trackeropen': {
				$icon=html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
				$url='<a href="/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'].'">Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].' ] Opened</a>';
				break;
			}
			case 'trackerclose': {
				$icon=html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
				$url='<a href="/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'].'">Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].' ] Closed</a>';
				break;
			}
			case 'frsrelease': {
				$icon=html_image("ic/cvs16b.png","20","20",array("border"=>"0","ALT"=>"SCM"));
				$url='<a href="/frs/?release_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'].'">FRS Release '.$arr['description'].'</a>';
				break;
			}
			case 'forumpost': {
				$icon=html_image("ic/forum20g.png","20","20",array("border"=>"0","ALT"=>"Forum"));
				$url='<a href="/forum/message.php?msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'].'">Forum Post '.$arr['description'].'</a>';
				break;
			}
		}
		echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;'.date('H:i:s',$arr['activity_date']).'</td>
			<td>'.$icon .' '.$url.'</td>
			<td><a href="/users/'.$arr['user_name'].'/">'.$arr['realname'].'</a></td>
			</tr>';
	}

	echo $HTML->listTableBottom($theader);

}

site_project_footer(array());

?>

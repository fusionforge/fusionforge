<?php
/**
 * Project Activity Page
 *
 * Copyright 2006 (c) GForge, LLC
 * http://gforge.org
 *
 * @version   $Id$
 */


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';    

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

site_project_header(array('title'=>_('Activity'),'group'=>$group_id,'toptab'=>'activity'));

$ids=array();
$texts=array();

if ($GLOBALS['sys_use_forum']) {
	$ids[]='forumpost';
	$texts[]=_('Forum Post');
}

if ($GLOBALS['sys_use_tracker']) {
	$ids[]='trackeropen';
	$texts[]=_('Tracker Opened');
	$ids[]='trackerclose';
	$texts[]=_('Tracker Closed');
}

if ($GLOBALS['sys_use_news']) {
	$ids[]='news';
	$texts[]=_('News');
}

if ($GLOBALS['sys_use_scm']) {
	$ids[]='commit';
	$texts[]=_('Commits');
}

if ($GLOBALS['sys_use_frs']) {
	$ids[]='frsrelease';
	$texts[]=_('FRS Release');
}

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
	echo _('No Activity Found');
} else {

	?>
<br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>"/>
<table border="0" cellspacing="0" cellpadding="3">
<tr>
	<td><strong><?php echo _('Activity') ?></strong></td>
	<td><strong><?php echo _('Start') ?></strong></td>
	<td><strong><?php echo _('End') ?></strong></td>
	<td></td>
</tr>
<tr>
	<td><?php echo $multiselect; ?></td>
	<td valign="top"><input name="start_date" value="<?php echo date(_('Y-m-d'),$begin); ?>" size="10" maxlength="10" /></td>
	<td valign="top"><input name="end_date" value="<?php echo date(_('Y-m-d'),$end); ?>" size="10" maxlength="10" /></td>
	<td valign="top"><input type="submit" name="submit" value="<?php echo _('Submit'); ?>"/></td>
</tr>
</table>
</form>
<br />
	<?php

	$theader=array();
	$theader[]=_('Time');
	$theader[]=_('Activity');
	$theader[]=_('By');

	echo $HTML->listTableTop($theader);

	$j=0;
	$last_day = 0;
	while ($arr =& db_fetch_array($res)) {
		if ($last_day != date('Y-M-d',$arr['activity_date'])) {
		//	echo $HTML->listTableBottom($theader);
			echo '<tr class="tableheading"><td colspan="3">'.date(_('Y-m-d'),$arr['activity_date']).'</td></tr>';
		//	echo $HTML->listTableTop($theader);
			$last_day=date('Y-M-d',$arr['activity_date']);
		}
		switch ($arr['section']) {
			case 'commit': {
				$icon=html_image("ic/cvs16b.png","20","20",array("border"=>"0","alt"=>"SCM"));
				$url=util_make_link ('/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Commit for Tracker Item').' [#'.$arr['subref_id'].'] '.$arr['description']);
				break;
			}
			case 'trackeropen': {
				$icon=html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
				$url=util_make_link ('/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Tracker Item').' [#'.$arr['subref_id'].' '.$arr['description'].' ] '._('Opened'));
				break;
			}
			case 'trackerclose': {
				$icon=html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
				$url=util_make_link ('/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Tracker Item').' [#'.$arr['subref_id'].' '.$arr['description'].' ] '._('Closed'));
				break;
			}
			case 'frsrelease': {
				$icon=html_image("ic/cvs16b.png","20","20",array("border"=>"0","alt"=>"SCM"));
				$url=util_make_link ('/frs/?release_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('FRS Release').' '.$arr['description']);
				break;
			}
			case 'forumpost': {
				$icon=html_image("ic/forum20g.png","20","20",array("border"=>"0","alt"=>"Forum"));
				$url=util_make_link ('/forum/message.php?msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'],_('Forum Post ').' '.$arr['description']);
				break;
			}
			case 'news': {
				$icon=html_image("ic/write16w.png","20","20",array("border"=>"0","alt"=>"News"));
				$url=util_make_link ('/forum/forum.php?forum_id='.$arr['subref_id'],_('News').' '.$arr['description']);
				break;
			}
		}
		echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;'.date('H:i:s',$arr['activity_date']).'</td>
			<td>'.$icon .' '.$url.'</td>
			<td>'.util_make_link_u ($arr['user_name'],$arr['user_id'],$arr['realname']).'</td>
			</tr>';
	}

	echo $HTML->listTableBottom($theader);

}

site_project_footer(array());

?>

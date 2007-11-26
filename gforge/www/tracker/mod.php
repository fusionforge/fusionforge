<?php
/**
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  */


$ath->header(array ('title'=>_('Modify').': '.$ah->getID(). ' - ' . $ah->getSummary(),'atid'=>$ath->getID() ));

echo notepad_func();

?>
	<h3>[#<?php echo $ah->getID(); ?>] <?php echo $ah->getSummary(); ?></h3>

	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>"  enctype="multipart/form-data" method="post">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
	<input type="hidden" name="func" value="postmod"/>
	<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>"/>

	<table width="80%">
<?php
if (session_loggedin()) {
?>
		<tr>
			<td><?php
				if ($ah->isMonitoring()) {
					$img="xmail16w.png";
					$key="monitorstop";
					$text=_('Stop monitor');
				} else {
					$img="mail16w.png";
					$key="monitor";
					$text=_('Monitor');
				}
				echo '
				<a href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor"><strong>'.
					html_image('ic/'.$img.'','20','20',array()).' '.$text.'</strong></a>';
				?>
			</td>
			<td><?php
				if ($group->usesPM()) {
					echo '
				<a href="'.getStringFromServer('PHP_SELF').'?func=taskmgr&amp;group_id='.$group_id.'&amp;atid='.$atid.'&amp;aid='.$aid.'">'.
					html_image('ic/taskman20w.png','20','20',array()).'<strong>'._('Build Task Relation').'</strong></a>';
				}
				?>
			</td>
			<td>
				<a href="<?php echo getStringFromServer('PHP_SELF')."?func=deleteartifact&amp;aid=$aid&amp;group_id=$group_id&amp;atid=$atid"; ?>"><strong><?php echo html_image('ic/trash.png','16','16',array()) . _('Delete'); ?></strong></a>
			</td>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
</table>
<br />
<?php } ?>
<table border="0" width="80%">
	<tr>
		<td>
			<strong><?php echo _('Submitted by') ?>:</strong><br />
			<?php echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				?>
				(<tt><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/users/<?php echo $submittedUnixName; ?>"><?php echo $submittedUnixName; ?></a></tt>)
			<?php } ?>
		</td>
		<td><strong><?php echo _('Date Submitted') ?>:</strong><br />
		<?php
		echo date(_('Y-m-d H:i'), $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<br /><strong>'._('Date Closed').':</strong><br />'
				.date(_('Y-m-d H:i'), $close_date);
		}
		?>
		</td>
	</tr>

	<tr>
		<td><strong><?php echo _('Data Type') ?>: <a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=data_type')"><strong>(?)</strong></a></strong><br />
		<?php

//
//  kinda messy - but works for now
//  need to get list of data types this person can admin
//
	$perm =& $group->getPermission(session_get_user());
	if ($perm->isArtifactAdmin()) {
		$alevel=' >= 0';	
	} else {
		$alevel=' > 1';	
	}
	$sql="SELECT agl.group_artifact_id, agl.name 
		FROM artifact_group_list agl, role_setting rs, user_group ug
		WHERE agl.group_artifact_id=rs.ref_id
		AND ug.user_id='". user_getid() ."' 
		AND rs.value $alevel
		AND agl.group_id='$group_id'
                AND ug.role_id = rs.role_id
                AND rs.section_name='tracker'";
	$res=db_query($sql);

	echo html_build_select_box ($res,'new_artifact_type_id',$ath->getID(),false);

		?>
		</td>
		<td>
		</td>
	</tr>

	<?php
		$ath->renderExtraFields($ah->getExtraFieldData(),true);
	?>

	<tr>
		<td><strong><?php echo _('Assigned to')?>: <a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=assignee')"><strong>(?)</strong></a></strong><br />
		<?php
		echo $ath->technicianBox('assigned_to', $ah->getAssignedTo() );
		echo '&nbsp;<a href="'.$GLOBALS['sys_urlprefix'].'/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;update_users=1">('._('Admin').')</a>';
		?>
		</td><td>
		<strong><?php echo _('Priority') ?>: <a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=priority')"><strong>(?)</strong></a></strong><br />
		<?php
		/*
			Priority of this request
		*/
		build_priority_select_box('priority',$ah->getPriority());
		?>
		</td>
	</tr>

	<tr>
		<td>
		<?php if (!$ath->usesCustomStatuses()) { ?>
		<strong><?php echo _('State') ?>: <a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=status')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->statusBox ('status_id', $ah->getStatusID() );
		}
		?>
		</td>
		<td>
		</td>
	</tr>

	<tr>
		<td><strong><?php echo _('Summary')?>: <a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=summary')"><strong>(?)</strong></a></strong><br />
		<input type="text" name="summary" size="70" value="<?php
			echo $ah->getSummary(); 
			?>" maxlength="255" />
		</td>
		<td>
		</td>
	</tr>
	<tr><td colspan="2">
		<?php echo $ah->showDetails(); ?>
	</td></tr>
</table>
<br />
<br />
<script type="text/javascript" src="/tabber/tabber.js"></script>
<div id="tabber" class="tabber">
<div class="tabbertab" title="<?php echo _('Followups'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<br /><strong><?php echo _('Use Canned Response') ?>: <a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=canned_response')"><strong>(?)</strong></a></strong><br />
		<?php
		echo $ath->cannedResponseBox('canned_response');
		echo '&nbsp;<a href="'.$GLOBALS['sys_urlprefix'].'/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;add_canned=1">('._('Admin').')</a>';
		?>
		<p>
		<strong><?php echo _('OR Attach A Comment') ?>:<?php echo notepad_button('document.forms[1].details') ?><a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=comment')"><strong>(?)</strong></a></strong><br />
		<textarea name="details" rows="7" cols="60"></textarea></p>
		<h3><?php echo _('Followup') ?>:</h3>
		<?php
			echo $ah->showMessages(); 
		?>
	</td></tr>
</table>
</div>
<?php
$tabcnt=0;
if ($group->usesPM()) {
?>
<div class="tabbertab" title="<?php echo _('Related Tasks'); ?>">
		<h3><?php echo _('Related Tasks'); ?>:</h3>
<table border="0" width="80%">
		<?php
		$tabcnt++;
		$result = $ah->getRelatedTasks();
		$taskcount = db_numrows($ah->relatedtasks);
		if ($taskcount > 0) {
			echo '<tr><td colspan="2">';
			$titles[] = _('Task Id');
			$titles[] = _('Task Summary');
			$titles[] = _('Start Date');
			$titles[] = _('End Date');
			echo $GLOBALS['HTML']->listTableTop($titles,'',$tabcnt);
			for ($i = 0; $i < $taskcount; $i++) {
				$taskinfo  = db_fetch_array($ah->relatedtasks, $i);
				$taskid    = $taskinfo['project_task_id'];
				$projectid = $taskinfo['group_project_id'];
				$groupid   = $taskinfo['group_id'];
				$summary   = util_unconvert_htmlspecialchars($taskinfo['summary']);
				$startdate = date(_('Y-m-d H:i'), $taskinfo['start_date']);
				$enddate   = date(_('Y-m-d H:i'), $taskinfo['end_date']);
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td>'.$taskid.'</td>
						<td><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?func=detailtask&amp;project_task_id='.$taskid.
						'&amp;group_id='.$groupid.'&amp;group_project_id='.$projectid.'">'.$summary.'</a></td>
						<td>'.$startdate.'</td>
						<td>'.$enddate.'</td>
				</tr>';
			}
			echo $GLOBALS['HTML']->listTableBottom();
		} else {
			echo '<tr><td colspan="3">'._('No Related Tasks').'</td></tr>';
		}
      ?>
</table>
</div>
<?php } ?>
<div class="tabbertab" title="<?php echo _('Attachments'); ?>">
		<h3><?php echo _('Existing Files') ?>:</h3>
<table border="0" width="80%">
	<tr><td colspan="2">
        <strong><?php echo _('Attach Files') ?>:</strong><br />
        <input type="file" name="input_file[]" size="30" /><br />
        <input type="file" name="input_file[]" size="30" /><br />
        <input type="file" name="input_file[]" size="30" /><br />
        <input type="file" name="input_file[]" size="30" /><br />
        <input type="file" name="input_file[]" size="30" /><br />
		<?php
		//
		//	print a list of files attached to this Artifact
		//
		$file_list =& $ah->getFiles();
		
		$count=count($file_list);
		$tabcnt++;
		$title_arr=array();
		$title_arr[]=_('Delete');
		$title_arr[]=_('Name');
		$title_arr[]=_('Download');
		echo $GLOBALS['HTML']->listTableTop ($title_arr,'',$tabcnt);

		if ($count > 0) {

			for ($i=0; $i<$count; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td><input type="CHECKBOX" name="delete_file[]" value="'. $file_list[$i]->getID() .'">'._('Delete').' </td>'.
				'<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
				<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName() .'">'._('Download').'</a></td>
				</tr>';
			}

		} else {
			echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle(0).'><td colspan="4">'._('No Files Currently Attached').'</td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();
		?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>">
<table border="0" width="80%">
	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Changes'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<h3><?php echo _('Change Log') ?>:</h3>
		<?php 
			echo $ah->showHistory(); 
		?>
	</td></tr>
</table>
</div>
</div>
		</form>

<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

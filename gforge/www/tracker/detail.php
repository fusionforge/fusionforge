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

echo $ath->header(array ('title'=>_('Detail').': '.$ah->getID(). ' '.util_unconvert_htmlspecialchars($ah->getSummary()),'atid'=>$ath->getID()));

echo notepad_func();

?>
	<h3>[#<?php echo $ah->getID(); ?>] <?php echo util_unconvert_htmlspecialchars($ah->getSummary()); ?></h3>

	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>" method="post" enctype="multipart/form-data">

<?php if (session_loggedin()) { ?>
	<table cellpadding="0" width="100%">
		<tr>
			<td>
				<?php 
					if ($ah->isMonitoring()) {
						$img="xmail16w.png";
						$key="stop_monitoring";
					} else {
						$img="mail16w.png";
						$key="monitor";
					}
					echo '
					<a href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor"><strong>'.
						html_image('ic/'.$img.'','20','20',array()).' '.$Language->getText('tracker_utils',$key).'</strong></a>';
					?>&nbsp;<a href="javascript:help_window('<?php echo $GLOBALS['sys_urlprefix']; ?>/help/tracker.php?helpname=monitor')"><strong>(?)</strong></a>

				<?php /* } else { ?>

				<span class="error">
				<?php printf(_('Please %1$s login %2$s'), '<a href="'.$GLOBALS['sys_urlprefix'].'/account/login.php?return_to='.urlencode($REQUEST_URI).'">', '</a>') ?></span>
				<?php if ($ath->allowsAnon()) { ?>
				<?php echo _('If you <strong>cannot</strong> login, then enter your email address here') ?>
				<br />
				<p>
				<input type="TEXT" name="user_email" SIZE="20" MAXLENGTH="255">
				<?php } ?>

				<?php  } */ ?>
			</td>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
	</table>
<?php } ?>
	<table border="0" width="80%">
		<tr>
			<td><strong><?php echo _('Date') ?>:</strong><br /><?php echo date( $sys_datefmt, $ah->getOpenDate() ); ?></td>
			<td><strong><?php echo _('Priority') ?>:</strong><br /><?php echo $ah->getPriority(); ?></td>
		</tr>

		<tr>
			<td><strong><?php echo _('State') ?>:</strong><br /><?php echo $ah->getStatusName(); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><strong><?php echo _('Submitted by') ?>:</strong><br />
			<?php
			echo $ah->getSubmittedRealName();
			/*if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				?>
				(<tt><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/users/<?php echo $submittedUnixName; ?>"><?php echo $submittedUnixName; ?></a></tt>)
			<?php }*/ ?>
			</td>
			<td><strong><?php echo _('Assigned to') ?>:</strong><br />
			<?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		</tr>

	<?php
		//$ath->renderExtraFields($ah->getExtraFieldData(),true);
	?>

		<tr><td colspan="2"><strong><?php echo _('Summary') ?>:</strong><br /><?php echo $ah->getSummary(); ?></td></tr>

		<tr><td colspan="2">
			<br />
			<?php echo $ah->showDetails(); ?>
		</td></tr>
</table>
<script type="text/javascript" src="/tabber/tabber.js"></script>

<div id="tabber" class="tabber">
<div class="tabbertab" title="<?php echo _('Followups'); ?>">
	<table border="0" width="80%">
		<tr><td colspan="2">
			<?php if ($ath->allowsAnon() || session_loggedin()) { ?>
			<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
			<input type="hidden" name="func" value="postmod">
			<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>">
			<p>
			<strong><?php echo _('Add A Comment') ?>:</strong> 
			<?php echo notepad_button('document.forms[1].details') ?><br />
			<textarea name="details" ROWS="10" COLS="60" WRAP="SOFT"></textarea>
			<?php } ?>
		</td></tr>
		<tr><td colspan="2">
		<h3><?php echo _('Followup') ?></h3>
		<p>
		<?php
		echo $ah->showMessages();
		?>
		</td></tr>
</table>
</div>
<?php
if ($group->usesPM()) {
?>
<div class="tabbertab" title="<?php echo _('Related Tasks'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<h3><?php echo _('Related Tasks'); ?>:</h3>
		<?php
		$result = $ah->getRelatedTasks();
		$taskcount = db_numrows($ah->relatedtasks);
		if ($taskcount > 0) {
			$titles[] = _('Task Id');
			$titles[] = _('Task Summary');
			$titles[] = _('Start Date');
			$titles[] = _('End Date');
			echo $GLOBALS['HTML']->listTableTop($titles);
			for ($i = 0; $i < $taskcount; $i++) {
				$taskinfo  = db_fetch_array($ah->relatedtasks, $i);
				$taskid	= $taskinfo['project_task_id'];
				$projectid = $taskinfo['group_project_id'];
				$groupid   = $taskinfo['group_id'];
				$summary   = util_unconvert_htmlspecialchars($taskinfo['summary']);
				$startdate = date($sys_datefmt, $taskinfo['start_date']);
				$enddate   = date($sys_datefmt, $taskinfo['end_date']);
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td>'.$taskid.'</td>
						<td><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?func=detailtask&project_task_id='.$taskid.
						'&amp;group_id='.$groupid.'&amp;group_project_id='.$projectid.'">'.$summary.'</a></td>
						<td>'.$startdate.'</td>
						<td>'.$enddate.'</td>
				</tr>';
			}
			echo $GLOBALS['HTML']->listTableBottom();
		} else {
			echo _('No Related Tasks');
		}
	  ?>
	</td></tr>
</table>
</div>
<?php } ?>
<div class="tabbertab" title="<?php echo _('Attachments'); ?>">
<table border="0" width="80%">
	<tr><td colspan=2>
	<?php if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) { ?>
		<strong><?php echo _('Attach Files'); ?></strong><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<p>
	<?php } ?>
	<h3><?php echo _('Attached Files') ?>:</h3>
	<?php
	//
	//  print a list of files attached to this Artifact
	//
	$file_list =& $ah->getFiles();

	$count=count($file_list);

	if ($count > 0) {

		$title_arr=array();
		$title_arr[]=_('Name');
		$title_arr[]=_('Download');
		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		for ($i=0; $i<$count; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.
				$file_list[$i]->getID().'/'. $file_list[$i]->getName() .'">'._('Download').'</a></td>
			</tr>';
		}
		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo _('No Files Currently Attached');
	}
	

?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>" >
<table border="0" width="80%">
	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Changes'); ?>">
<table border="0" width="80%">
	<tr>
	<td colspan="2">
	<h3><?php echo _('Changes') ?>:</h3>
	<p>
	<?php

	echo $ah->showHistory();

	?>
	</td>
	</tr>
</table>
</div>
</div>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

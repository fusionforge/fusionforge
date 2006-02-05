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


$ath->header(array ('title'=>$Language->getText('tracker_mod','title').': '.$ah->getID(). ' - ' . $ah->getSummary(),'pagename'=>'tracker','atid'=>$ath->getID(),'sectionvals'=>array($group->getPublicName()) ));

?>
	<h2>[#<?php echo $ah->getID(); ?>] <?php echo $ah->getSummary(); ?></h2>

	<table width="100%">
<?php
if (session_loggedin()) {
?>
		<tr>
			<td><?php
				if ($ah->isMonitoring()) {
					$img="xmail16w.png";
					$key="stop_monitoring";
				} else {
					$img="mail16w.png";
					$key="monitor";
				}
				echo '
				<a href="index.php?group_id='.$group_id.'&artifact_id='.$ah->getID().'&atid='.$ath->getID().'&func=monitor"><strong>'.
					html_image('ic/'.$img.'','20','20',array()).' '.$Language->getText('tracker_utils',$key).'</strong></a>';
				?>&nbsp;<a href="javascript:help_window('/help/tracker.php?helpname=monitor')"><strong>(?)</strong></a>
			</td>
			<td><?php
				if ($group->usesPM()) {
					echo '
				<a href="'.$PHP_SELF.'?func=taskmgr&group_id='.$group_id.'&atid='.$atid.'&aid='.$aid.'">'.
					html_image('ic/taskman20w.png','20','20',array()).'<strong>'.$Language->getText('tracker_mod','build_task_relation').'</strong></a>';
				}
				?>
			</td>
		</tr>
<?php } ?>
	<form action="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST" enctype="multipart/form-data">
	<input type="hidden" name="func" value="postmod">
	<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>">

	<tr>
		<td><strong><?php echo $Language->getText('tracker','submitted_by') ?>:</strong><br />
			<?php
			echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				?>
				(<tt><a href="/users/<?php echo $submittedUnixName; ?>"><?php echo $submittedUnixName; ?></a></tt>)
			<?php } ?>
		</td>
		<td><strong><?php echo $Language->getText('tracker_mod','date_submitted') ?>:</strong><br />
		<?php
		echo date($sys_datefmt, $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<br /><strong>'.$Language->getText('tracker_mod','date_closed').':</strong><br />'
				 .date($sys_datefmt, $close_date);
		}
		?>
		</td>
	</tr>

    <?php
        $ath->renderExtraFields($ah->getExtraFieldData(),true);
    ?>

	<tr>
		<td><strong><?php echo $Language->getText('tracker','assigned_to')?>: <a href="javascript:help_window('/help/tracker.php?helpname=assignee')"><strong>(?)</strong></a></strong><br />
            <?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		<td>
		<strong><?php echo $Language->getText('tracker','priority') ?>: <a href="javascript:help_window('/help/tracker.php?helpname=priority')"><strong>(?)</strong></a></strong><br />
		<?php
		/*
			Priority of this request
		*/
		echo $ah->getPriority();
		?>
		</td>
	</tr>

	<tr>
		<td>
		<?php if (!$ath->usesCustomStatuses()) { ?>
		<strong><?php echo $Language->getText('tracker','status') ?>: <a href="javascript:help_window('/help/tracker.php?helpname=status')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->statusBox ('status_id', $ah->getStatusID() );
		}
		?>
		</td>
		<td>
		</td>
	</tr>

	<tr>
		<td colspan="2"><strong><?php echo $Language->getText('tracker','summary')?>: <a href="javascript:help_window('/help/tracker.php?helpname=summary')"><strong>(?)</strong></a></strong><br />
			<?php echo $ah->getSummary(); ?>
		</td>
	</tr>

	<tr><td colspan="2">
		<br />
		<?php echo $ah->showDetails(); ?>
	</td></tr>

	<tr><td colspan="2">
		<br /><strong><?php echo $Language->getText('tracker_mod','attach_comment') ?>: <?php echo notepad_button('document.forms[1].details') ?> <a href="javascript:help_window('/help/tracker.php?helpname=comment')"><strong>(?)</strong></a></strong><br />
		<textarea name="details" rows="7" cols="60" wrap="hard"></textarea></p>
		<p>
		<h3><?php echo $Language->getText('tracker','followups') ?>:</h3>
		<?php
			echo $ah->showMessages(); 
		?>
	</td></tr>

	<tr><td colspan="2">
		<?php echo $Language->getText('tracker','file_upload') ?><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<p>
		<h4><?php echo $Language->getText('tracker_detail','attached_files') ?>:</h4>
		<?php
		//
		//  print a list of files attached to this Artifact
		//
		$file_list =& $ah->getFiles();

		$count=count($file_list);

		$title_arr=array();
		$title_arr[]=$Language->getText('tracker_mod','delete');
		$title_arr[]=$Language->getText('tracker_detail','name');
		$title_arr[]=$Language->getText('tracker_detail','description');
		$title_arr[]=$Language->getText('tracker_detail','download');
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		if ($count > 0) {

			for ($i=0; $i<$count; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td><input type="CHECKBOX" name="delete_file[]" value="'. $file_list[$i]->getID() .'">'.$Language->getText('tracker_mod','delete').' </td>
				<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
				<td>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</td>
				<td><a href="/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName() .'">'.$Language->getText('tracker_mod','download').'</a></td>
				</tr>';
			}

		} else {
			echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle(0).'><td colspan=3>'.$Language->getText('tracker_detail','no_files_attached').'</td></tr>';
		}
		echo $GLOBALS['HTML']->listTableBottom();
		?>
	</td><tr>
	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
	<tr><td colspan="2">
		<h4><?php echo $Language->getText('tracker_mod','changelog') ?>:</h4>
		<?php 
			echo $ah->showHistory(); 
		?>
	</td></tr>

	<tr><td colspan="2" align="middle">
		<input type="submit" name="submit" value="<?php echo $Language->getText('general','submit') ?>">
		</form>
	</td></tr>

	</table>

<?php

$ath->footer(array());

?>

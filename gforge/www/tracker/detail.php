<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

echo $ath->header(array ('title'=>'Detail: '.$ah->getID(). ' '.util_unconvert_htmlspecialchars($ah->getSummary()),'pagename'=>'tracker_detail','atid'=>$ath->getID(),'sectionvals'=>array($ath->getName())));

?>
	<h2>[ #<?php echo $ah->getID(); ?> ] <?php echo util_unconvert_htmlspecialchars($ah->getSummary()); ?></h2>

	<table cellpadding="0" width="100%">
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
			<td>
				<a href="<?php echo "$PHP_SELF?func=taskmgr&group_id=$group_id&atid=$atid&aid=$aid"; ?>"><?php echo
					html_image('ic/taskman20w.png','20','20',array()); ?><strong>Build Task Relation</strong></a>
			</td>
		</tr>
<?php } ?>
		<tr>
			<td><strong>Date:</strong><br /><?php echo date( $sys_datefmt, $ah->getOpenDate() ); ?></td>
			<td><strong>Priority:</strong><br /><?php echo $ah->getPriority(); ?></td>
		</tr>

		<tr>
			<td><strong>Submitted By:</strong><br /><?php echo $ah->getSubmittedRealName(); ?> (<?php echo $ah->getSubmittedUnixName(); ?>)</td>
			<td><strong>Assigned To:</strong><br /><?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		</tr>

		<tr>
			<td><strong>Category:</strong><br /><?php echo $ah->getCategoryName(); ?></td>
			<td><strong>Status:</strong><br /><?php echo $ah->getStatusName(); ?></td>
		</tr>

		<tr><td colspan="2"><strong>Summary:</strong><br /><?php echo $ah->getSummary(); ?></td></tr>

		<form action="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST">

		<tr><td colspan="2">
			<?php echo nl2br( $ah->getDetails() ); ?>
			<input type="hidden" name="func" value="postaddcomment">
			<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>">
			<p>
			<strong>Add A Comment:</strong><br />
			<textarea name="details" ROWS="10" COLS="60" WRAP="HARD"></textarea>
		</td></tr>

		<tr><td colspan="2">
	<?php

	if (!session_loggedin()) {
		?>
		<h3><FONT COLOR="RED">Please <a href="/account/login.php?return_to=<?php echo urlencode($REQUEST_URI); ?>">log in!</a></FONT></h3><br />
		If you <strong>cannot</strong> login, then enter your email address here:<p>
		<input type="TEXT" name="user_email" SIZE="20" MAXLENGTH="40">
		<?php
	}
	?>
		<p>
		<h3>DO NOT enter passwords or confidential information in your message!</h3>
		<p>
		<input type="SUBMIT" name="SUBMIT" value="SUBMIT">
		</form>
	</td></tr>

	<tr><td colspan="2">
	<h3>Followups:</h3>
	<p>
	<?php

	echo $ah->showMessages();

	?>
	</td></tr>

	<tr><td colspan=2>
	<H4>Attached Files:</H4>
	<?php
	//
	//  print a list of files attached to this Artifact
	//
	$file_list =& $ah->getFiles();

	$count=count($file_list);

	$title_arr=array();
	$title_arr[]='Name';
	$title_arr[]='Description';
	$title_arr[]='Download';
	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	if ($count > 0) {

		for ($i=0; $i<$count; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
			<td>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</td>
			<td><a href="/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'. $file_list[$i]->getName() .'">Download</a></td>
			</tr>';
		}

	} else {
		echo '<tr><td colspan=3>No Files Currently Attached</td></tr>';
	}
	
	echo $GLOBALS['HTML']->listTableBottom();

	?>
	</td></tr>

	<tr>
	<td colspan="2">
	<h3>Changes:</h3>
	<p>
	<?php

	echo $ah->showHistory();

	?>
	</td>
	</tr>
</TABLE>
<?php

$ath->footer(array());

?>

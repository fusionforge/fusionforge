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


$ath->header(array ('title'=>'Modify: '.$ah->getID(). ' - ' . $ah->getSummary(),'pagename'=>'tracker','atid'=>$ath->getID(),'sectionvals'=>array($group->getPublicName()) ));

?>
	<h2>[ #<?php echo $ah->getID(); ?> ] <?php echo $ah->getSummary(); ?></h2>

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
			<td>
				<a href="<?php echo "$PHP_SELF?func=taskmgr&group_id=$group_id&atid=$atid&aid=$aid"; ?>"><?php echo 
					html_image('ic/taskman20w.png','20','20',array()); ?><strong>Build Task Relation</strong></a>
			</td>
		</tr>
<?php } ?>
	<form action="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST" enctype="multipart/form-data">
	<input type="hidden" name="func" value="postmod">
	<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>">

	<tr>
		<td><strong>Submitted By:</strong><br /><?php echo $ah->getSubmittedRealName(); ?> (<tt><?php echo $ah->getSubmittedUnixName(); ?></tt>)</td>
		<td><strong>Date Submitted:</strong><br />
		<?php
		echo date($sys_datefmt, $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<br /><strong>Date Closed:</strong><br />'
				 .date($sys_datefmt, $close_date);
		}
		?>
		</td>
	</tr>

	<tr>
		<td><strong>Data Type: <a href="javascript:help_window('/help/tracker.php?helpname=data_type')"><strong>(?)</strong></a></strong><br />
		<?php

//
//  kinda messy - but works for now
//  need to get list of data types this person can admin
//
	if ($ath->userIsAdmin()) {
		$alevel=' >= 0';	
	} else {
		$alevel=' > 1';	
	}
	$sql="SELECT agl.group_artifact_id,agl.name 
		FROM artifact_group_list agl,artifact_perm ap
		WHERE agl.group_artifact_id=ap.group_artifact_id 
		AND ap.user_id='". user_getid() ."' 
		AND ap.perm_level $alevel
		AND agl.group_id='$group_id'";
	$res=db_query($sql);

	echo html_build_select_box ($res,'new_artfact_type_id',$ath->getID(),false);

		?>
		</td>
		<td>
			<input type="submit" name="submit" value="Submit Changes" />
		</td>
	</tr>

	<tr>
		<td><strong>Category: <a href="javascript:help_window('/help/tracker.php?helpname=category')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->categoryBox('category_id', $ah->getCategoryID() );
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;add_cat=1">(admin)</a>';

		?>
		</td>
		<td><strong>Group: <a href="javascript:help_window('/help/tracker.php?helpname=group')"><strong>(?)</strong></a></strong><br />
		<?php
		
		echo $ath->artifactGroupBox('artifact_group_id', $ah->getArtifactGroupID() );
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&add_group=1">(admin)</a>';
		
		?>
		</td>
	</tr>

	<tr>
		<td><strong>Assigned To: <a href="javascript:help_window('/help/tracker.php?helpname=assignee')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->technicianBox('assigned_to', $ah->getAssignedTo() );
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;update_users=1">(admin)</a>';
		?>
		</td><td>
		<strong>Priority: <a href="javascript:help_window('/help/tracker.php?helpname=priority')"><strong>(?)</strong></a></strong><br />
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
		<strong>Status: <a href="javascript:help_window('/help/tracker.php?helpname=status')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->statusBox ('status_id', $ah->getStatusID() );

		?>
		</td>
		<td>
		<?php
		if ($ath->useResolution()) {
			echo '
			<strong>Resolution: <a href="javascript:help_window(\'/help/tracker.php?helpname=resolution\')"><strong>(?)</strong></a></strong><br />';
			echo $ath->resolutionBox('resolution_id',$ah->getResolutionID());
		} else {
			echo '&nbsp;
			<input type="hidden" name="resolution_id" value="100">';
		}
		?>
		</td>
	</tr>

	<tr>
		<td colspan="2"><strong>Summary: <a href="javascript:help_window('/help/tracker.php?helpname=summary')"><strong>(?)</strong></a></strong><br />
		<input type="text" name="summary" size="45" value="<?php
			echo $ah->getSummary(); 
			?>" maxlength="60" />
		</td>
	</tr>

	<tr><td colspan="2">
		<?php echo nl2br($ah->getDetails()); ?>
	</td></tr>

	<tr><td colspan="2">
		<strong>Use Canned Response: <a href="javascript:help_window('/help/tracker.php?helpname=canned_response')"><strong>(?)</strong></a></strong><br />
		<?php
		echo $ath->cannedResponseBox('canned_response');
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;add_canned=1">(admin)</a>';
		?>
		<p>
		<strong>OR Attach A Comment: <a href="javascript:help_window('/help/tracker.php?helpname=comment')"><strong>(?)</strong></a></strong><br />
		<textarea name="details" rows="7" cols="60" wrap="hard"></textarea></p>
		<p>&nbsp;</p>
		<h3>Followups:</h3>
		<p>&nbsp;</p>
		<?php
			echo $ah->showMessages(); 
		?>
	</td></tr>

	<tr><td colspan="2">
		<strong>Check to Upload &amp; Attach File:</strong> <input type="checkbox" name="add_file" value="1" />
		<a href="javascript:help_window('/help/tracker.php?helpname=attach_file')"><strong>(?)</strong></a><br />
		<p>
		<input type="file" name="input_file" size="30" /></p>
		<p>
		<strong>File Description:</strong><br />
		<input type="text" name="file_description" size="40" maxlength="255" /></p>
		<p>&nbsp;</p>
		<h4>Existing Files:</h4>
		<?php
		//
		//	print a list of files attached to this Artifact
		//
		$file_list =& $ah->getFiles();
		
		$count=count($file_list);

		$title_arr=array();
		$title_arr[]='Delete';
		$title_arr[]='Name';
		$title_arr[]='Description';
		$title_arr[]='Download';
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		if ($count > 0) {

			for ($i=0; $i<$count; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><input type="CHECKBOX" name="delete_file[]" value="'. $file_list[$i]->getID() .'"> Delete</td>'.
				'<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
				<td>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</td>
				<td><a href="/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName() .'">Download</a></td>
				</tr>';
			}

		} else {
			echo '<tr><td colspan=3>No Files Currently Attached</td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();
		?>
	</td><tr>

	<tr><td colspan="2">
		<H4>Change Log:</H4>
		<?php 
			echo $ah->showHistory(); 
		?>
	</td></tr>

	<tr><td colspan="2" align="MIDDLE">
		<input type="SUBMIT" name="SUBMIT" value="Submit Changes">
		</form>
	</td></tr>

	</table>

<?php

$ath->footer(array());

?>

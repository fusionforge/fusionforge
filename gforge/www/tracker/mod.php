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
	<H2>[ #<?php echo $ah->getID(); ?> ] <?php echo $ah->getSummary(); ?></H2>

	<TABLE WIDTH="100%">
<?php
if (session_loggedin()) {
?>

            <FORM ACTION="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST">
            <INPUT TYPE="HIDDEN" NAME="func" VALUE="monitor"> 
            <INPUT TYPE="HIDDEN" NAME="artifact_id" VALUE="<?php echo $ah->getID(); ?>">
        <TR>
            <TD COLSPAN=2">
            <INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Monitor">&nbsp;<A href="javascript:help_window('/help/tracker.php?helpname=monitor')"><B>(?)</B></A>
            </FORM>
            </TD>
        </TR>
<?php } ?>
	<FORM ACTION="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST" enctype="multipart/form-data">
	<INPUT TYPE="HIDDEN" NAME="func" VALUE="postmod">
	<INPUT TYPE="HIDDEN" NAME="artifact_id" VALUE="<?php echo $ah->getID(); ?>">

	<TR>
		<TD><B>Submitted By:</B><BR><?php echo $ah->getSubmittedRealName(); ?> (<tt><?php echo $ah->getSubmittedUnixName(); ?></tt>)</TD>
		<TD><B>Date Submitted:</B><BR>
		<?php
		echo date($sys_datefmt, $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<BR><B>Date Closed:</B><BR>'
			     .date($sys_datefmt, $close_date);
		}
		?>
		</TD>
	</TR>

	<TR>
		<TD><B>Data Type: <A href="javascript:help_window('/help/tracker.php?helpname=data_type')"><B>(?)</B></A></B><BR>
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
		</TD>
		<TD>
			<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Submit Changes">
		</TD>
	</TR>

	<TR>
		<TD><B>Category: <A href="javascript:help_window('/help/tracker.php?helpname=category')"><b>(?)</b></a></B><BR>
		<?php

		echo $ath->categoryBox('category_id', $ah->getCategoryID() );
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&add_cat=1">(admin)</A>';

		?>
		</TD>
		<TD><B>Group: <A href="javascript:help_window('/help/tracker.php?helpname=group')"><b>(?)</b></a></B><BR>
		<?php
		
		echo $ath->artifactGroupBox('artifact_group_id', $ah->getArtifactGroupID() );
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&add_group=1">(admin)</A>';
		
		?>
		</TD>
	</TR>

	<TR>
		<TD><B>Assigned To: <A href="javascript:help_window('/help/tracker.php?helpname=assignee')"><b>(?)</b></a></B><BR>
		<?php

		echo $ath->technicianBox('assigned_to', $ah->getAssignedTo() );
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&update_users=1">(admin)</A>';
		?>
		</TD><TD>
		<B>Priority: <A href="javascript:help_window('/help/tracker.php?helpname=priority')"><b>(?)</b></a></B><BR>
		<?php
		/*
			Priority of this request
		*/
		build_priority_select_box('priority',$ah->getPriority());
		?>
		</TD>
	</TR>

	<TR>
		<TD>
		<B>Status: <A href="javascript:help_window('/help/tracker.php?helpname=status')"><b>(?)</b></a></B><BR>
		<?php

		echo $ath->statusBox ('status_id', $ah->getStatusID() );

		?>
		</TD>
		<TD>
		<?php
		if ($ath->useResolution()) {
			echo '
			<B>Resolution: <A href="javascript:help_window(\'/help/tracker.php?helpname=resolution\')"><b>(?)</b></a></B><BR>';
			echo $ath->resolutionBox('resolution_id',$ah->getResolutionID());
		} else {
			echo '&nbsp;
			<INPUT TYPE="HIDDEN" NAME="resolution_id" VALUE="100">';
		}
		?>
		</TD>
	</TR>

	<TR>
		<TD COLSPAN="2"><B>Summary: <A href="javascript:help_window('/help/tracker.php?helpname=summary')"><b>(?)</b></a></B><BR>
		<INPUT TYPE="TEXT" NAME="summary" SIZE="45" VALUE="<?php 
			echo $ah->getSummary(); 
			?>" MAXLENGTH="60">
		</TD>
	</TR>

	<TR><TD COLSPAN="2">
		<?php echo nl2br($ah->getDetails()); ?>
	</TD></TR>

	<TR><TD COLSPAN="2">
		<B>Use Canned Response: <A href="javascript:help_window('/help/tracker.php?helpname=canned_response')"><b>(?)</b></a></B><BR>
		<?php
		echo $ath->cannedResponseBox('canned_response');
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&add_canned=1">(admin)</A>';
		?>
		<P>
		<B>OR Attach A Comment: <A href="javascript:help_window('/help/tracker.php?helpname=comment')"><b>(?)</b></a></B><BR>
		<TEXTAREA NAME="details" ROWS="7" COLS="60" WRAP="HARD"></TEXTAREA>
		<P>
		<H3>Followups:</H3>
		<P>
		<?php
			echo $ah->showMessages(); 
		?>
	</TD></TR>

	<TR><TD COLSPAN=2>
		<B>Check to Upload &amp; Attach File:</B> <input type="checkbox" name="add_file" VALUE="1"> 
		<A href="javascript:help_window('/help/tracker.php?helpname=attach_file')"><b>(?)</b></a><BR>
		<P>
		<input type="file" name="input_file" size="30">
		<P>
		<B>File Description:</B><BR>
		<input type="text" name="file_description" size="40" maxlength="255">
		<P>
		<H4>Existing Files:</H4>
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
				<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><TD><INPUT TYPE="CHECKBOX" NAME="delete_file[]" VALUE="'. $file_list[$i]->getID() .'"> Delete</TD>'.
				'<TD>'. htmlspecialchars($file_list[$i]->getName()) .'</TD>
				<TD>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</TD>
				<TD><A HREF="/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName() .'">Download</A></TD>
				</TR>';
			}

		} else {
			echo '<TR><TD COLSPAN=3>No Files Currently Attached</TD></TR>';
		}

		echo $GLOBALS['HTML']->listTableBottom();
		?>
	</TD><TR>

	<TR><TD COLSPAN="2">
		<H4>Change Log:</H4>
		<?php 
			echo $ah->showHistory(); 
		?>
	</TD></TR>

	<TR><TD COLSPAN="2" ALIGN="MIDDLE">
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Submit Changes">
		</FORM>
	</TD></TR>

	<tr>
		<td colspan=2><b>Task Manager Integration:</b>
			<a href="<?php echo "$PHP_SELF?func=taskmgr&group_id=$group_id&atid=$atid&aid=$aid"; ?>"><b>Create New Task Or Attach To Existing Task</b></a>
		</td>
	</tr>
	</table>

<?php

$ath->footer(array());

?>

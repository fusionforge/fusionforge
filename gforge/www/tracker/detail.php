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
	<H2>[ #<?php echo $ah->getID(); ?> ] <?php echo util_unconvert_htmlspecialchars($ah->getSummary()); ?></H2>

	<TABLE CELLPADDING="0" WIDTH="100%">
			<FORM ACTION="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="func" VALUE="monitor">
			<INPUT TYPE="HIDDEN" NAME="artifact_id" VALUE="<?php echo $ah->getID(); ?>">
		<TR>
			<TD COLSPAN=2">
			<?php
			if (!user_isloggedin()) {
				?>
				<B>Email:</B> &nbsp;
				<INPUT TYPE="TEXT" NAME="user_email" SIZE="20" MAXLENGTH="40">
				<?php
			}
			?>
			<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Monitor">&nbsp;<A href="javascript:help_window('/help/tracker.php?helpname=monitor')"><B>(?)</B></A>
			</FORM>
			</TD>
		</TR>
		<TR>
			<TD><B>Date:</B><BR><?php echo date( $sys_datefmt, $ah->getOpenDate() ); ?></TD>
			<TD><B>Priority:</B><BR><?php echo $ah->getPriority(); ?></TD>
		</TR>

		<TR>
			<TD><B>Submitted By:</B><BR><?php echo $ah->getSubmittedRealName(); ?> (<?php echo $ah->getSubmittedUnixName(); ?>)</TD>
			<TD><B>Assigned To:</B><BR><?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</TD>
		</TR>

		<TR>
			<TD><B>Category:</B><BR><?php echo $ah->getCategoryName(); ?></TD>
			<TD><B>Status:</B><BR><?php echo $ah->getStatusName(); ?></TD>
		</TR>

		<TR><TD COLSPAN="2"><B>Summary:</B><BR><?php echo  util_unconvert_htmlspecialchars($ah->getSummary()); ?></TD></TR>

		<FORM ACTION="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST">

		<TR><TD COLSPAN="2">
			<?php echo nl2br( $ah->getDetails() ); ?>
			<INPUT TYPE="HIDDEN" NAME="func" VALUE="postaddcomment">
			<INPUT TYPE="HIDDEN" NAME="artifact_id" VALUE="<?php echo $ah->getID(); ?>">
			<P>
			<B>Add A Comment:</B><BR>
			<TEXTAREA NAME="details" ROWS="10" COLS="60" WRAP="HARD"></TEXTAREA>
		</TD></TR>

		<TR><TD COLSPAN="2">
	<?php

	if (!user_isloggedin()) {
		?>
		<h3><FONT COLOR="RED">Please <A HREF="/account/login.php?return_to=<?php echo urlencode($REQUEST_URI); ?>">log in!</A></FONT></h3><BR>
		If you <B>cannot</B> login, then enter your email address here:<P>
		<INPUT TYPE="TEXT" NAME="user_email" SIZE="20" MAXLENGTH="40">
		<?php
	}
	?>
		<P>
		<H3>DO NOT enter passwords or confidential information in your message!</H3>
		<P>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
		</FORM>
	</TD></TR>

	<TR><TD COLSPAN="2">
	<H3>Followups:</H3>
	<P>
	<?php

	echo $ah->showMessages();

	?>
	</TD></TR>

	<TR><TD COLSPAN=2>
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
	echo html_build_list_table_top ($title_arr);

	if ($count > 0) {

		for ($i=0; $i<$count; $i++) {
			echo '<TR>
			<TD>'. $file_list[$i]->getName() .'</TD>
			<TD>'.  $file_list[$i]->getDescription() .'</TD>
			<TD><A HREF="/tracker/download.php?group_id='.$group_id.'&atid='. $ath->getID().'&file_id='.$file_list[$i]->getID().'&aid='. $ah->getID() .'">Download</A></TD>
			</TR>';
		}

	} else {
		echo '<TR><TD COLSPAN=3>No Files Currently Attached</TD></TR>';
	}
	
	echo '</TABLE>';
	?>
	</TD></TR>

	<TR>
	<TD COLSPAN="2">
	<H3>Changes:</H3>
	<P>
	<?php

	echo $ah->showHistory();

	?>
	</TD>
	</TR>
</TABLE>
<?php

$ath->footer(array());

?>

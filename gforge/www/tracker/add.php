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


$ath->header(array ('title'=>'Submit','pagename'=>'tracker_add','sectionvals'=>array($ath->getName())));

	echo '
	<P>';
	/*
	    Show the free-form text submitted by the project admin
	*/
	echo $ath->getSubmitInstructions();

	echo '<P>
	<FORM ACTION="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'" METHOD="POST" enctype="multipart/form-data">
	<INPUT TYPE="HIDDEN" NAME="func" VALUE="postadd">
	<TABLE>
	<TR><TD VALIGN="TOP" COLSPAN="2"><B>For Project:</B><BR>'.$group->getPublicName().'</TD></TR>
	<TR><TD VALIGN="TOP"><B>Category: <A href="javascript:help_window(\'/help/tracker.php?helpname=category\')"><B>(?)</B></A></B><BR>';

		echo $ath->categoryBox('category_id');
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&add_cat=1">(admin)</A>';
	?>
	</TD><TD><B>Group: <A href="javascript:help_window('/help/tracker.php?helpname=group')"><b>(?)</b></a></B><BR>
	<?php
		echo $ath->artifactGroupBox('artifact_group_id');
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&add_group=1">(admin)</A>';
	?>
	</TD></TR>
	<?php 
	if ($ath->userIsAdmin()) {
		echo '<TR><TD><B>Assigned To: <A href="javascript:help_window(\'/help/tracker.php?helpname=assignee\')"><b>(?)</b></a></B><BR>';
		echo $ath->technicianBox ('assigned_to');
		echo '&nbsp;<A HREF="/tracker/admin/?group_id='.$group_id.'&atid='. $ath->getID() .'&update_users=1">(admin)</A>';

		echo '</TD><TD><B>Priority: <A href="javascript:help_window(\'/help/tracker.php?helpname=priority\')"><b>(?)</b></a></B><BR>';
		echo build_priority_select_box('priority');
		echo '</TD></TR>';
	}
	?>
	<TR><TD COLSPAN="2"><B>Summary: <A href="javascript:help_window('/help/tracker.php?helpname=summary')"><b>(?)</b></a></B><BR>
		<INPUT TYPE="TEXT" NAME="summary" SIZE="35" MAXLENGTH="40">
	</TD></TR>

	<TR><TD COLSPAN="2">
		<B>Detailed Description:</B>
		<P>
		<TEXTAREA NAME="details" ROWS="30" COLS="55" WRAP="HARD"></TEXTAREA>
	</TD></TR>

	<TR><TD COLSPAN="2">
	<?php 
	if (!session_loggedin()) {
		echo '
		<h3><FONT COLOR="RED">Please <A HREF="/account/login.php?return_to='. urlencode($REQUEST_URI) .'">log in!</A></FONT></h3><BR>
		If you <B>cannot</B> login, then enter your email address here:<P>
		<INPUT TYPE="TEXT" NAME="user_email" SIZE="30" MAXLENGTH="35">
		';

	} 
	?>
		<P>
		<H3><FONT COLOR=RED>DO NOT enter passwords or other confidential information!</FONT></H3>
		<P>
	</TD></TR>

	<TR><TD COLSPAN=2>
		<B>Check to Upload &amp; Attach File:</B> <input type="checkbox" name="add_file" VALUE="1"> 
		<A href="javascript:help_window('/help/tracker.php?helpname=comment')"><b>(?)</b></a><BR>
		<P>
		<input type="file" name="input_file" size="30">
		<P>
		<B>File Description:</B><BR>
		<input type="text" name="file_description" size="40" maxlength="255">
		<P>
	</TD><TR>

	<TR><TD COLSPAN=2>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
		</FORM>
		<P>
	</TD></TR>

	</TABLE>

	<?php

	$ath->footer(array());

?>

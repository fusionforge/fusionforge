<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: intelapprove.php,v 1.9 2001/04/24 14:34:37 pfalcon Exp $

require_once('pre.php');    

session_require(array('group'=>'5914','admin_flags'=>'A'));

// group public choice
if ($submit) {
	/*
		update the project flag to active
	*/
	$accept_count=count($accept);
	$delete_count=count($delete);

	$approve_message='Congratulations!  Your proposal has been accepted and you now have
access to the Itanium(tm) processor prototype compile farm.  This is a
great opportunity for members of the Open Source community and
SourceForge is excited to be able to provide this access.  Please be
understanding if there are any issues with accessing the Itanium
processor prototype compile farm during the start-up phase.  We will be
continually improving access to the machines.

Remember that your access to the compile farm is subject to the terms
and conditions set forth by Intel and VA Linux as documented in the
click through license.

Please wait at least 6 hours for your account to be activated
before trying to login.

If you have questions, READ THE DOCS at http://sourceforge.net/compilefarm/

For find more resources on porting to IA-64 and for mailing lists, see
http://sourceforge.net/project/?group_id=1196';

	$delete_message='I am sorry to inform you that your proposal to access the Itanium(tm)
processor prototype compile farm has been rejected because you did not 
meet our criteria.';


	for ($i=0; $i<$accept_count; $i++){
		$result=db_query("UPDATE intel_agreement SET is_approved='1' WHERE user_id='$accept[$i]'");
		if (!$result) {
			echo db_error();
		}
		$sql="SELECT email FROM users WHERE user_id='$accept[$i]'";
		$result=db_query($sql);
		mail (db_result($result,0,'email'),'You Have Been Approved',$approve_message,'From: esindelar@users.sourceforge.net');
	}

	for ($i=0; $i<$delete_count; $i++){
		$result=db_query("DELETE FROM intel_agreement WHERE user_id='$delete[$i]'");
		if (!$result) {
			echo db_error();
		}
		$sql="SELECT email FROM users WHERE user_id='$delete[$i]'";
		$result=db_query($sql);
		mail (db_result($result,0,'email'),'You Could Not Be Approved',$delete_message,'From: esindelar@users.sourceforge.net');
	}


}


// get current information
$result = db_query("SELECT users.user_name,users.email,users.user_id,intel_agreement.message ".
		"FROM users,intel_agreement ".
		"WHERE users.user_id=intel_agreement.user_id AND is_approved='0'");

if (db_numrows($result) < 1) {
	exit_error("None Found","No Pending Requests to Approve");
}

$HTML->header(array('title'=>'Approving Pending Requests'));

echo '<TABLE BORDER="1">';

echo '<TR><TD>user_name</TD><TD>Email</TD><TD>Justification</TD><TD>Accept</TD><TD>Delete</TD></TR>

	<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">';

$rows=db_numrows($result);

for ($i=0; $i<$rows; $i++) {
	echo '<TR><TD><A HREF="/developer/?form_dev='. db_result($result,$i,'user_id') .'">'. db_result($result,$i,'user_name') .'</A></TD><TD>'. db_result($result,$i,'email') .'</TD><TD>'. nl2br(db_result($result,$i,'message')) .'</TD>
		<TD VALIGN="TOP"><INPUT TYPE="CHECKBOX" NAME="accept[]" VALUE="'. db_result($result,$i,'user_id') .'"></TD>
		<TD VALIGN="TOP"><INPUT TYPE="CHECKBOX" NAME="delete[]" VALUE="'. db_result($result,$i,'user_id') .'"></TD></TR>';
}

echo '<TR><TD COLSPAN="5"><INPUT TYPE="SUBMIT" NAME="submit" VALUE="Process"></TD></TR></FORM></TABLE>';

$HTML->footer(array());

?>

<HTML>
<HEAD>
<TITLE>Templating Standards</TITLE>
</HEAD>
<BODY BGCOLOR="WHITE">

<H3>Coding Example:</H3>
<P>
The following code examples demonstrate how all coding on SourceForge 
is going to be done in the future. The first example shows the "switchbox" 
page (taken from www/tracker/index.php) - where the various objects 
are included, instantiated and checked for errors every step of the way.
<P>
Once the objects are instantiated, the template file can be included. In this 
example, the template file is detail.php (example2).
<P>
<?php
#highlight_string($string);

$example ='<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: templating.php,v 1.2 2001/03/06 16:04:31 tperdue Exp $


//
//	Include core objects
//
require(\'pre.php\');
require_once(\'common/tracker/Artifact.class\');
require_once(\'common/tracker/ArtifactFile.class\');

//
//	Verify proper params passed in
//
if ($group_id && $atid) {

	//
	//	  get the Group object
	//
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}

	//
	//	  Create the ArtifactType object
	//
	$ath = new ArtifactTypeHtml($group,$atid);
	if (!$ath || !is_object($ath)) {
		exit_error(\'Error\',\'ArtifactType could not be created\');
	}
	if ($ath->isError()) {
		exit_error(\'Error\',$ath->getErrorMessage());
	}

	//
	//	Fusebox-like architecture
	//
	switch ($func) {

		case \'detail\' : {
			//
			//	  users can modify their own tickets if they submitted them
			//	  even if they are not artifact admins
			//
			$ah=new ArtifactHtml($ath,$aid);
			if (!$ah || !is_object($ah)) {
				exit_error(\'ERROR\',\'Artifact Could Not Be Created\');
			} else if ($ah->isError()) {
				exit_error(\'ERROR\',$ah->getErrorMessage());
			} else {
				//
				//	Include the template file
				//
				include \'../tracker/detail.php\';
			}
			break;
		}
		default : {
				//foo
		}

	}

} else {
	exit_missing_params();
}

?>';

$example2='<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: templating.php,v 1.2 2001/03/06 16:04:31 tperdue Exp $

echo $ath->header(array (\'title\'=>\'Detail: \'.$ah->getID(). \' \'.$ah->getSummary()));

?>
	<H2>[ #<?php echo $ah->getID(); ?> ] <?php echo $ah->getSummary(); ?></H2>

	<TABLE CELLPADDING="0" WIDTH="100%">
			<FORM ACTION="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="func" VALUE="monitor">
			<INPUT TYPE="HIDDEN" NAME="artifact_id" VALUE="<?php echo $ah->getID(); ?>">
		<TR>
			<TD COLSPAN=2">
			<?php
			if (!session_loggedin()) {
				?>
				<B>Email:</B> &nbsp;
				<INPUT TYPE="TEXT" NAME="user_email" SIZE="20" MAXLENGTH="40">
				<?php
			}
			?>
			<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Monitor">
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

	<TR><TD COLSPAN="2">
		<P>
		<H3>DO NOT enter passwords or confidential information in your message!</H3>
		<P>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
		</FORM>
	</TD></TR>

	</TABLE>
	</FORM>
<?php

$ath->footer(array());

?>';

echo '<H4>Switchbox page:</H4><P>';

echo highlight_string($example);


echo '<H4>Template page (detail.php):</H4><P>';

echo highlight_string($example2);

?>


</BODY>
</HTML>

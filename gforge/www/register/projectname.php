<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: projectname.php,v 1.35 2000/11/03 02:17:32 tperdue Exp $

require "pre.php";    // Initial db and session library, opens session
session_require(array('isloggedin'=>'1'));
require "account.php";

// push received vars
if ($insert_purpose && $form_purpose) { 

	srand((double)microtime()*1000000);
	$random_num=rand(0,1000000);

	// make group entry
	$result = db_query("INSERT INTO groups (group_name,is_public,unix_group_name,http_domain,homepage,status,"
		. "unix_box,cvs_box,license,register_purpose,register_time,license_other,rand_hash) VALUES ("
		. "'__$random_num',"
		. "1," // public
		. "'__$random_num',"
		. "'__$random_num',"
		. "'__$random_num',"
		. "'I'," // status
		. "'shell1'," // unix_box
		. "'cvs1'," // cvs_box
		. "'__$random_num',"
		. "'".htmlspecialchars($form_purpose)."',"
		. time() . ","
		. "'__$random_num','__".md5($random_num)."')");

	if (!$result) {
		exit_error('ERROR','INSERT QUERY FAILED. Please notify admin@'.$GLOBALS['sys_default_domain']);
	} else {
		$group_id=db_insertid($result,'groups','group_id');
	}

} else {
	exit_error('Error','Missing Information. <B>PLEASE</B> fill in all required information.');
}

$HTML->header(array('title'=>'Project Name'));

echo "<H2>".$Language->REGISTER_step4_title."</H2>";
echo $Language->REGISTER_step4;

?>

<UL>
<LI>A web site at unixname.<?php echo $GLOBALS['sys_default_domain']; ?> 
<LI>Email at aliases@unixname.<?php echo $GLOBALS['sys_default_domain']; ?> 
<LI>A CVS Repository root of /cvsroot/unixname
<LI>Shell access to unixname.<?php echo $GLOBALS['sys_default_domain']; ?> 
<LI>Search engines throughout the site
</UL>

<P>Please make your selections.

<P><B>Project Name</B>
<FONT size=-1>
<FORM action="license.php" method="post">
<INPUT TYPE="HIDDEN" NAME="insert_group_name" VALUE="y">
<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php echo $group_id; ?>">  
<INPUT TYPE="HIDDEN" NAME="rand_hash" VALUE="<?php echo md5($random_num); ?>">
Full Name:
<BR>
<INPUT size="30" maxlength="30" type=text name="form_full_name">
<P>Unix Name:
<BR>
<INPUT type=text maxlength="15" SIZE="15" name="form_unix_name">
<P>
<H2><FONT COLOR="RED"><?php echo $Language->REGISTER_step4_warn; ?></FONT></H2>
<INPUT type=submit name="Submit" value="<?php echo $Language->REGISTER_step5_title; ?>">
</FORM>
</FONT>

<?php
$HTML->footer(array());

?>


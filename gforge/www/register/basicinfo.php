<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: basicinfo.php,v 1.16 2000/08/31 06:11:35 gherteg Exp $

require "pre.php";    // Initial db and session library, opens session
session_require(array('isloggedin'=>'1'));
$HTML->header(array('title'=>'Basic Project Information'));

echo $Language->REGISTER_step3;
?>

<FONT size=-1>
<FORM action="projectname.php" method="post">
<INPUT TYPE="HIDDEN" NAME="insert_purpose" VALUE="y">
<TEXTAREA name=form_purpose wrap=virtual cols=70 rows=20></TEXTAREA>
<BR><INPUT type=submit name="Submit" value="<?php echo $Language->REGISTER_step4_title; ?>">
</FORM>
</FONT>

<?php
$HTML->footer(array());

?>

<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
session_require(array('isloggedin'=>1));
site_user_header(array('title'=>"$Language->EMAILADDR $Language->CHANGE"));
?>

<P><B><?php echo "$Language->EMAILADDR $Language->CHANGE"; ?></B>

<?php echo "$Language->CHANGEEMAIL_desc"; ?>

<FORM action="change_email-confirm.php" method="post">
<P><INPUT type="hidden" name="form_user" value="<?php print user_getid(); ?>">
<?php echo "$Language->NEW $Language->EMAILADDR"; ?>:
<INPUT type="text" name="form_newemail">
<INPUT type="submit" name="Send Confirmation to New Address" value="Send Confirmation to New Address">
</FORM>

<P><A href="/">[Return to <?php echo $GLOBALS["sys_name"]; ?>]</A>

<?php
site_user_footer(array());

?>

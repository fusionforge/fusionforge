<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
$HTML->header(array('title'=>$Language->ACCOUNT_LOSTPW_title));
?>

<?php echo $Language->ACCOUNT_LOSTPW_desc; ?>:

<FORM action="lostpw-confirm.php" method="post">
<P><INPUT type="hidden" name="form_user" value="<?php print $form_user; ?>">
<?php echo $Language->LOGIN_NAME; ?>:
<INPUT type="text" name="form_loginname">
<INPUT type="submit" name="Send Lost PW Hash" value="Send Lost PW Hash">
</FORM>

<P><A href="/">[Return to Main Page]</A>

<?php
$HTML->footer(array());

?>

<?php
/**
  *
  * Project Registration: Terms of Service (legal)
  *
  * This page presents Terms of Service Agreement and requires user
  * subscription to it to continue registration.
  *
  * Next in sequence: projectinfo.php
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: tos.php,v 1.38 2001/05/13 17:57:29 pfalcon Exp $
  *
  */


require_once("pre.php");

session_require( array( isloggedin=>1 ) );

$HTML->header(array(title=>"Terms of Service",'pagename'=>'register_tos'));

include_once('../tos/tos_text.php');

?> 

<BR><HR><BR>

<P align=center>By clicking below, you acknowledge that you have read 
and understand the Terms of Service agreement. Clicking "I AGREE" will
constitute your legal signature on this document.

<table align="center">
<tr>
<td>
<form action="projectinfo.php" method="POST">
<input type="submit" value="I AGREE">
</form>
</td>

<td>
<form action="/" method="POST">
<input type="submit" value="I DISAGREE">
</form>
</td>
</tr>
</table>

<?php

$HTML->footer(array());

?>


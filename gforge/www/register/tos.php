<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: tos.php,v 1.34 2000/08/31 06:11:36 gherteg Exp $

require("pre.php");    // Initial db and session library, opens session
session_require( array( isloggedin=>1 ) );

$HTML->header(array(title=>"Terms of Service"));

print "<p><h2>".$Language->REGISTER_step2_title."</h2></p>";

include("../tos/tos_text.php");

?> 

<BR><HR><BR>

<?php
echo $Language->REGISTER_step2_bottom;

$HTML->footer(array());

?>


<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: requirements.php,v 1.18 2000/08/31 06:11:36 gherteg Exp $

require "pre.php";    // Initial db and session library, opens session
session_require(array(isloggedin=>1));
$HTML->header(array(title=>"Project Requirements"));

echo $Language->REGISTER_step1;
echo '<BR><H3 align=center><a href="tos.php">'.$Language->REGISTER_step2_title.'</a></H3>';

$HTML->footer(array());
?>


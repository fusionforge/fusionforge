<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    // Initial db and session library, opens session
session_require(array(isloggedin=>1));
$HTML->header(array(title=>"Project Registration"));
?>

<H2><?php echo $Language->NEW_PROJECT; ?></H2>

<?php
echo $Language->REGISTER_start;

$HTML->footer(array());

?>


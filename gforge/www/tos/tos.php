<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: tos.php,v 1.5 2000/08/31 06:11:36 gherteg Exp $

require("pre.php");  // Initial db and session library, opens session

$HTML->header( array( title=>"Terms of Service Agreement" ) );

include("tos_text.php");

$HTML->footer( array() );

?>


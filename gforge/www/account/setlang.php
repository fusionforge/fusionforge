<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

setcookie('cookie_language_id',$language_id,(time()+2592000),'/','',0);
$cookie_language_id=$language_id;

require ('pre.php');

echo $HTML->header(array('title'=>"Change Language"));

?>

<H2>Language Updated</H2>
<P>
Your language preference has been saved in a cookie and will be 
remembered next time you visit the site.
<P>

<?php

echo $HTML->footer(array());

?>

<?php
/**
  *
  * About SourceForge Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: about.php,v 1.31 2001/05/22 21:39:30 pfalcon Exp $
  *
  */

require_once('pre.php');
$HTML->header(array(title=>"About this site"));

echo $Language->getText('about', 'about_blurb');

$HTML->footer(array());
?>

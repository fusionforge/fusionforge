<?php
/**
  *
  * SourceForge Staff Members
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: staff.php,v 1.39 2001/05/22 21:39:31 pfalcon Exp $
  *
  */


require_once('pre.php');    

$HTML->header(array('title'=>'Sourceforge Staff'));

echo $Language->getText('staff', 'about_blurb');

$HTML->footer(array());

?>
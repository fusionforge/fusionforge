<?php
/**
  *
  * SourceForge Contact Info
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: contact.php,v 1.17 2001/05/22 21:39:30 pfalcon Exp $
  *
  */

require_once('pre.php');

$HTML->header(array('title'=>'Contact '.$GLOBALS['sys_name'],'pagename'=>'contact'));

echo $Language->getText('contact', 'about_blurb');

$HTML->footer(array());
?>

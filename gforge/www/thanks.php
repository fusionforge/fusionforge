<?php
/**
  *
  * SourceForge Words of Gratitude Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: thanks.php,v 1.11 2001/05/22 21:39:31 pfalcon Exp $
  *
  */


require_once('pre.php');

$HTML->header(array(title=>"About ".$GLOBALS['sys_name']));

echo $Language->getText('thanks', 'about_blurb');

$HTML->footer(array());

?>


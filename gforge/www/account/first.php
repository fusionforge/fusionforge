<?php
/**
  *
  * Welcome page
  *
  * This is the page user is redirerected to after first site login
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

site_user_header(array(title=>"Welcome to ".$GLOBALS['sys_name'],'pagename'=>'account_first'));

echo $Language->getText('account_first','about_blurb', $GLOBALS[sys_name]);

site_user_footer(array());

?>

<?php
/**
  *
  * SourceForge Code Snippets Repository
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/vars.php');
require_once('www/snippet/snippet_utils.php');
require_once('www/include/snippet_caching.php');

snippet_header(array('title'=>'Snippet Library', 'header'=>'Snippet Library','pagename'=>'snippet'));

echo snippet_mainpage();

snippet_footer(array());

?>

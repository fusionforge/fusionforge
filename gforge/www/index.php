<?php
/**
  *
  * SourceForge Front Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');    // Initial db and session library, opens session
require_once('www/news/news_utils.php');
require_once('common/forum/Forum.class');
require_once('features_boxes.php');

$HTML->header(array('title'=>$Language->getText('index','welcome'),'pagename'=>'home'));

// Main page content is now themeable;
// Default is index_std.php;
include ( $HTML->getRootIndex() );

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

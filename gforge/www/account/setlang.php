<?php
/**
  *
  * Set default language for not-logged-on sessions (via cookie)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

setcookie('cookie_language_id',$language_id,(time()+2592000),'/','',0);
$cookie_language_id = $language_id;

echo $HTML->header(array('title'=>"Change Language"));

?>

<H2>Language Updated</H2>
<P>
Your language preference has been saved in a cookie and will be 
remembered next time you visit the site.
</P>

<?php

echo $HTML->footer(array());

?>

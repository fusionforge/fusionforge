<?php
/**
  *
  * SourceForge Partners Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: partners.php,v 1.13 2001/06/09 18:36:32 pfalcon Exp $
  *
  */


require_once('pre.php');

$HTML->header(array(title=>"Partners"));
?>

<P><B>Partners</B>

<?php
	
echo $Language->getText('partners', 'about_blurb', array(html_image('/images/others/cosource142x31.gif','142','31',array())));

$HTML->footer(array());

?>

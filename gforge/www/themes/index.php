<?php
/**
 * GForge Web Theme Control Page
 *
 * Copyright 2002 (c) GFORGE LLC
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
 *
 * @version   $Id$
 */


require_once('pre.php');

require "pre.php";    
session_require(array('isloggedin'=>'1'));

$u =& session_get_user();

if ($submit) {
	if (!$u->setThemeID($new_theme_id)) {
		exit_error('Error',$u->getErrorMessage());
	} else {
		$feedback='Successful';
	}
}

$title = 'Choose Your Theme';
echo site_user_header(array('title'=>$title,'pagename'=>'themes'));

?>
<p>Welcome!</b>
<p>
You can change your theme from here. 
<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<?php $HTML->boxTop("New User Theme");

$res=db_query("SELECT theme_id, fullname FROM themes");

echo html_build_select_box($res,'new_theme_id',$u->getThemeID(),false);

?>
<input type="submit" name="submit" value="Submit Changes">
</form>
<?php

$HTML->boxBottom(); 


echo site_user_footer(array());

?>

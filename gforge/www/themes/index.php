<?php
/**
 * GForge Web Theme Control Page
 *
 * Copyright 2002 (c) GFORGE LLC
 *
 * @version   $Id$
 */


require_once('pre.php');

session_require(array('isloggedin'=>'1'));

$u =& session_get_user();

if ($submit) {
	if (!$u->setThemeID($new_theme_id)) {
		exit_error('Error',$u->getErrorMessage());
	} else {
		header("Location: /themes/?feedback=Successful");
	}
}

$title = 'Choose Your Theme';
echo site_user_header(array('title'=>$title,'pagename'=>'themes'));

?>
<p>Welcome!</strong>
<p>
You can change your theme from here. 
<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<?php $HTML->boxTop("New User Theme");

$res=db_query("SELECT theme_id, fullname FROM themes WHERE enabled=true");

echo html_build_select_box($res,'new_theme_id',$u->getThemeID(),false);

?>
 <input type="submit" name="submit" value="Submit Changes">
</form>
<?php

$HTML->boxBottom(); 


echo site_user_footer(array());

?>

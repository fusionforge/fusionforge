<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: change_timezone.php,v 1.11 2000/10/11 19:55:39 tperdue Exp $

require ('pre.php');
require ('account.php');
require ('timezones.php');

if (!user_isloggedin()) {
	exit_not_logged_in();
}

if ($submit) {	
	if (!$timezone || !$language_id) {
		$feedback .= ' Nothing Updated ';
	} else {
		//save the cookie for future visits where not logged in
		setcookie('cookie_language_id',$language_id,(time()+2592000),'/','',0);

		// if we got this far, it must be good
		db_query("UPDATE users SET timezone='$timezone',language='$language_id' WHERE user_id=" . user_getid());
		session_redirect("/account/");
	}
}

site_user_header(array('title'=>"$Language->LANGUAGE / $Language->TIMEZONE $Language->CHANGE"));

?>
<H3><?php echo "$Language->LANGUAGE / $Language->TIMEZONE $Language->CHANGE"; ?></h3>
<P>
<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<?php

echo '<H4>'.$feedback.'</H4>';

echo "<P>$Language->TIMEZONE:<BR>";
echo html_get_timezone_popup ('timezone',user_get_timezone());

echo "<P>$Language->LANGUAGE:<BR>";
echo html_get_language_popup ($Language,'language_id',user_get_language());

?>
<P>
<input type="submit" name="submit" value="Update">
</form>

<?php

site_user_footer(array());

?>

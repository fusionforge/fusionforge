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

site_user_header(array('title'=>"Change Timezone &amp; Language"));

?>
<H3>Timezone/Language Change</h3>
<P>
Now, no matter where you live, you can see all dates and times throughout SourceForge 
as if it were in your neighborhood.
<P>
<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<?php

echo '<H4>'.$feedback.'</H4>';

echo '
<P>
Timezone:<BR>';
echo html_get_timezone_popup ('timezone',user_get_timezone());

echo '
<P>
Language:<BR>';
echo html_get_language_popup ($Language,'language_id',user_get_language());

?>
<P>
<input type="submit" name="submit" value="Update">
</form>

<?php

site_user_footer(array());

?>

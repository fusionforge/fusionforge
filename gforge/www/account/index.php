<?php
/**
  *
  * User account main page - show settings with means to change them
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');    
require_once('common/include/timezones.php');

session_require(array('isloggedin'=>'1'));

// get global users vars
$u =& user_get_object(user_getid());
exit_assert_object($u, 'User');

if ($submit) {

	if (trim($realname)=="") {
		exit_error(
		    'Missing Paramater',
		    'Please fill in real name.'
		);
	}

	if ($remember_user) {
		// set cookie, expire in 3 months
		setcookie("sf_user_hash",$u->getID().'_'.substr($u->getMD5Passwd(),0,16),time()+90*24*60*60,'/');
	} else {
		// remove cookie
		setcookie("sf_user_hash",'',0,'/');
	}

	if (!$u->update($realname, $language, $timezone, $mail_site, $mail_va, $use_ratings)) {
		$feedback .= $u->getErrorMessage().'<br>';
	} else {
		$feedback .= 'Updated<br>';
	}

}

site_user_header(array('title'=>"Account Maintenance",'pagename'=>'account'));

echo '<FORM action="'.$PHP_SELF.'" method="post">';
$HTML->box1_top("Account Maintenance: " . $u->getUnixName()); ?>

<p>Welcome, <b><?php print $u->getUnixName(); ?></b>. 
<p>You can view/change all of your account features from here. You may also wish
to view your developer/consultant profiles and ratings.

<UL>
<LI><A href="/users/<?php print $u->getUnixName(); ?>/"><B>View My Developer Profile</B></A>
<LI><A HREF="/people/editprofile.php"><B>Edit My Skills Profile</B></A>
<LI><A HREF="/themes/"><B>Change My Theme</B></A>
</UL>
<?php $HTML->box1_bottom(); ?>

&nbsp;<BR>
<TABLE width=100% border=0>

<TR valign=top>
<TD>Member Since: </TD>
<TD><B><?php print date($sys_datefmt,$u->getAddDate()); ?></B></TD>
</TR>
<TR valign=top>
<TD>User ID: </TD>
<TD><B><?php print $u->getID(); ?></B></TD>
</TR>

<TR valign=top>
<TD>Login Name: </TD>
<TD><B><?php print $u->getUnixName(); ?></B>
<br><A href="change_pw.php">[Change Password]</A></TD>
</TR>

<TD>Real Name: </TD>
<TD><input type="text" name="realname" value="<?php print $u->getRealName(); ?>"></B>
</TD>
</TR>

<TR valign=top>
<TD>Language: </TD>
<TD><?php echo html_get_language_popup ($Language,'language',$u->getLanguage()); ?>
</TD>
</TR>

<TR valign=top>
<TD>Timezone: </TD>
<TD><?php echo html_get_timezone_popup('timezone', $u->getTimeZone()); ?>
</TD>
</TR>

<TR valign=top>
<TD>Email Addr: </TD>
<TD><B><?php print $u->getEmail(); ?></B>
<br><A href="change_email.php">[Change Email Addr]</A>
</TD>
</TR>

<TR>
<TD COLSPAN=2>
<?php 
// ############################# Preferences
$HTML->box1_top("Preferences"); ?>

<INPUT type="checkbox" name="mail_site" value="1"<?php 
	if ($u->getMailingsPrefs('site')) print " checked"; ?>> Receive Email for Site Updates
<I>(This is very low traffic and will include security notices. Highly recommended.)</I>

<P><INPUT type="checkbox"  name="mail_va" value="1"<?php
	if ($u->getMailingsPrefs('va')) print " checked"; ?>> Receive additional community mailings. 
<I>(Low traffic.)</I>

<P><INPUT type="checkbox"  name="remember_user" value="1"<?php
	if ($sf_user_hash) print " checked"; ?> > "Remember me".
<I>(Allows to access your <a href="/my/">personal page</a> without being logged
in. You will still need to login explicitly before making any changes.)</I>

<P><INPUT type="checkbox"  name="use_ratings" value="1"<?php
	if ($u->usesRatings()) print " checked"; ?> > Participate in peer ratings
<I>(Allows you to rate other users using several criteria as well as to be
rated by others. More information is available on <a href="/users/<?php echo $u->getUnixName(); ?>">
your user page</a> if you have chosen to participate in ratings).
</I>

<P align=center>
<?php $HTML->box1_bottom(); 

// ############################### Shell Account

if ($u->getUnixStatus() == 'A') {
	$HTML->box1_top("Shell Account Information"); 
	print '&nbsp;
<BR>Shell box: <b>'.$u->getUnixBox().'</b>
<BR>CVS/SSH Shared Authorized Keys: <B>';
	// get shared key count from db
	$expl_keys = explode("\n", $u->getAuthorizedKeys());
	if ($expl_keys[0]) {
		print (sizeof($expl_keys));
	} else {
		print '0';
	}
	print '</B> <A href="editsshkeys.php">[Edit Keys]</A>';
	$HTML->box1_bottom(); 
} 
?>

</TD>
</TR>

</TABLE>

<CENTER>
<INPUT type="submit" name="submit" value="Update">
<INPUT type="reset" name="reset" value="Reset Changes">
</CENTER>
</FORM>

<?php
site_user_footer(array());
?>

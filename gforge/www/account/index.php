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

	if (!$u->update($realname, $language, $timezone, $mail_site, $mail_va, $use_ratings, 
		$jabber_address,$jabber_only)) {
		$feedback .= $u->getErrorMessage().'<br>';
	} else {
		$feedback .= 'Updated<br>';
	}

}

site_user_header(array('title'=>$Language->getText('account_options', 'title'),'pagename'=>'account'));




echo '<FORM action="'.$PHP_SELF.'" method="post">';


echo $HTML->boxTop($Language->getText('account_options', 'title'));
?>
 

<p> <?php echo $Language->getText('account_options', 'welcome'); ?> <b><?php print $u->getUnixName(); ?></b>. 
<p>

<?php echo $Language->getText('account_options', 'intro'); ?> 

<UL>
<LI><A href="/users/<?php print $u->getUnixName(); ?>/"><B><?php echo $Language->getText('account_options', 'view_developer_profile'); ?></B></A>
<LI><A HREF="/people/editprofile.php"><B><?php echo $Language->getText('account_options', 'edit_skills_profile'); ?></B></A>
<LI><A HREF="/themes/"><B>Change My Theme</B></A>
</UL>
<?php echo $HTML->boxBottom(); ?>

&nbsp;<BR>
<TABLE width=100% border=0>

<TR valign=top>
<TD><?php echo $Language->getText('account_options', 'member_since'); ?> </TD>
<TD><B><?php print date($sys_datefmt,$u->getAddDate()); ?></B></TD>
</TR>
<TR valign=top>
<TD><?php echo $Language->getText('account_options', 'user_id'); ?> </TD>
<TD><B><?php print $u->getID(); ?></B></TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->getText('account_options', 'login_name'); ?> </TD>
<TD><B><?php print $u->getUnixName(); ?></B>
<br><A href="change_pw.php">[<?php echo $Language->getText('account_options', 'change_password'); ?>]</A></TD>
</TR>

<TD><?php echo $Language->getText('account_options', 'real_name'); ?></TD>
<TD><input type="text" name="realname" value="<?php print $u->getRealName(); ?>"></B>
</TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->getText('account_options', 'language'); ?> </TD>
<TD><?php echo html_get_language_popup ($Language,'language',$u->getLanguage()); ?>
</TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->getText('account_options', 'timezone'); ?> </TD>
<TD><?php echo html_get_timezone_popup('timezone', $u->getTimeZone()); ?>
</TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->getText('account_options', 'email_address'); ?> </TD>
<TD><B><?php print $u->getEmail(); ?></B>
<br><A href="change_email.php">[<?php echo $Language->getText('account_options', 'change_email_address'); ?>]</A>
</TD>
</TR>

<?php
if ($sys_use_jabber) {
    echo '<TR VALIGN=top>
<TD>'. $Language->getText('account_register','jabberaddr') .'</TD>
<TD>
    <INPUT size=30 type="text" name="jabber_address" value="'. $u->getJabberAddress() .'"><P>
	<INPUT type="checkbox" name="jabber_only" value="1" '.(($u->getJabberOnly()) ? 'CHECKED' : '' ).'>
	'.$Language->getText('account_register','jabberonly').'.
</TD></TR>';

}
?>


<TR>
<TD COLSPAN=2>
<?php 
// ############################# Preferences
echo $HTML->boxTop("Preferences"); ?>

<INPUT type="checkbox" name="mail_site" value="1"<?php 
	if ($u->getMailingsPrefs('site')) print " checked"; ?>> 
	<?php echo $Language->getText('account_register','siteupdate'); ?>

<P><INPUT type="checkbox"  name="mail_va" value="1"<?php
	if ($u->getMailingsPrefs('va')) print " checked"; ?>> 
	<?php echo $Language->getText('account_register','communitymail'); ?>

<P><INPUT type="checkbox"  name="remember_user" value="1"<?php
	if ($sf_user_hash) print " checked"; ?> > 
<?php echo $Language->getText('account_register','remember_me','<a href="/my/">');?>

<P><INPUT type="checkbox"  name="use_ratings" value="1"<?php
	if ($u->usesRatings()) print " checked"; ?> > 
	<?php echo $Language->getText('account_register','partecipate_peer_ratings','<a href="/users/'.$u->getUnixName().'">'); ?>

</I>

<P align=center>
<?php echo $HTML->boxBottom(); 

// ############################### Shell Account

if ($u->getUnixStatus() == 'A') {
	echo $HTML->boxTop("Shell Account Information"); 
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
	echo $HTML->boxBottom(); 
} 
?>

</TD>
</TR>

</TABLE>

<CENTER>
<INPUT type="submit" name="submit" value="<?php echo $Language->getText('account_register','update'); ?>">
<INPUT type="reset" name="reset" value="<?php echo $Language->getText('account_register','reset'); ?>">
</CENTER>
</FORM>

<?php
site_user_footer(array());
?>

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

	// Refresh page if language changed
	if ($language != $u->getLanguage()) {
		$refresh = 1;
	}
	
	if (!$u->update($realname, $language, $timezone, $mail_site, $mail_va, $use_ratings, 
		$jabber_address,$jabber_only)) {
		$feedback .= $u->getErrorMessage().'<br />';
	} else {
		$feedback .= 'Updated<br />';
	}

	if ($refresh) {
		header("Location: /account/?feedback=".urlencode($feedback));
	}

}

site_user_header(array('title'=>$Language->getText('account_options', 'title'),'pagename'=>'account'));




echo '<form action="'.$PHP_SELF.'" method="post">';


echo $HTML->boxTop($Language->getText('account_options', 'title'));
?>
 

<p> <?php echo $Language->getText('account_options', 'welcome'); ?> <strong><?php print $u->getUnixName(); ?></strong>. 
<p>

<?php echo $Language->getText('account_options', 'intro'); ?> 

<ul>
<li><a href="/users/<?php print $u->getUnixName(); ?>/"><strong><?php echo $Language->getText('account_options', 'view_developer_profile'); ?></strong></a>
<li><a href="/people/editprofile.php"><strong><?php echo $Language->getText('account_options', 'edit_skills_profile'); ?></strong></a>
<li><a href="/themes/"><strong>Change My Theme</strong></a>
</ul>
<?php echo $HTML->boxBottom(); ?>

&nbsp;<br />
<table width=100% border=0>

<tr valign=top>
<td><?php echo $Language->getText('account_options', 'member_since'); ?> </td>
<td><strong><?php print date($sys_datefmt,$u->getAddDate()); ?></strong></td>
</tr>
<tr valign=top>
<td><?php echo $Language->getText('account_options', 'user_id'); ?> </td>
<td><strong><?php print $u->getID(); ?></strong></td>
</tr>

<tr valign=top>
<td><?php echo $Language->getText('account_options', 'login_name'); ?> </td>
<td><strong><?php print $u->getUnixName(); ?></strong>
<br /><a href="change_pw.php">[<?php echo $Language->getText('account_options', 'change_password'); ?>]</a></td>
</tr>

<td><?php echo $Language->getText('account_options', 'real_name'); ?></td>
<td><input type="text" name="realname" value="<?php print $u->getRealName(); ?>"></strong>
</td>
</tr>

<tr valign=top>
<td><?php echo $Language->getText('account_options', 'language'); ?> </td>
<td><?php echo html_get_language_popup ($Language,'language',$u->getLanguage()); ?>
</td>
</tr>

<tr valign=top>
<td><?php echo $Language->getText('account_options', 'timezone'); ?> </td>
<td><?php echo html_get_timezone_popup('timezone', $u->getTimeZone()); ?>
</td>
</tr>

<tr valign=top>
<td><?php echo $Language->getText('account_options', 'email_address'); ?> </td>
<td><strong><?php print $u->getEmail(); ?></strong>
<br /><a href="change_email.php">[<?php echo $Language->getText('account_options', 'change_email_address'); ?>]</a>
</td>
</tr>

<?php
if ($sys_use_jabber) {
    echo '<tr valign=top>
<td>'. $Language->getText('account_register','jabberaddr') .'</td>
<td>
    <input size=30 type="text" name="jabber_address" value="'. $u->getJabberAddress() .'"><p>
	<input type="checkbox" name="jabber_only" value="1" '.(($u->getJabberOnly()) ? 'CHECKED' : '' ).'>
	'.$Language->getText('account_register','jabberonly').'.
</td></tr>';

}
?>


<tr>
<td colspan=2>
<?php 
// ############################# Preferences
echo $HTML->boxTop("Preferences"); ?>

<input type="checkbox" name="mail_site" value="1"<?php 
	if ($u->getMailingsPrefs('site')) print " checked"; ?>> 
	<?php echo $Language->getText('account_register','siteupdate'); ?>

<p><input type="checkbox"  name="mail_va" value="1"<?php
	if ($u->getMailingsPrefs('va')) print " checked"; ?>> 
	<?php echo $Language->getText('account_register','communitymail'); ?>

<p><input type="checkbox"  name="remember_user" value="1"<?php
	if ($sf_user_hash) print " checked"; ?> > 
<?php echo $Language->getText('account_register','remember_me','<a href="/my/">');?>

<p><input type="checkbox"  name="use_ratings" value="1"<?php
	if ($u->usesRatings()) print " checked"; ?> > 
	<?php echo $Language->getText('account_register','partecipate_peer_ratings','<a href="/users/'.$u->getUnixName().'">'); ?>

</em>

<P align=center>
<?php echo $HTML->boxBottom(); 

// ############################### Shell Account

if ($u->getUnixStatus() == 'A') {
	echo $HTML->boxTop("Shell Account Information"); 
	print '&nbsp;
<br />Shell box: <strong>'.$u->getUnixBox().'</strong>
<br />CVS/SSH Shared Authorized Keys: <strong>';
	// get shared key count from db
	$expl_keys = explode("\n", $u->getAuthorizedKeys());
	if ($expl_keys[0]) {
		print (sizeof($expl_keys));
	} else {
		print '0';
	}
	print '</strong> <a href="editsshkeys.php">[Edit Keys]</a>';
	echo $HTML->boxBottom(); 
} 
?>

</td>
</tr>

</TABLE>

<CENTER>
<input type="submit" name="submit" value="<?php echo $Language->getText('account_register','update'); ?>">
<input type="reset" name="reset" value="<?php echo $Language->getText('account_register','reset'); ?>">
</CENTER>
</form>

<?php
site_user_footer(array());
?>

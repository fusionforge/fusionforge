<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');    

session_require(array('isloggedin'=>'1'));

site_user_header(array('title'=>"Account Maintenance"));

// get global users vars
$res_user = db_query("SELECT * FROM users WHERE user_id=" . user_getid());
$row_user = db_fetch_array($res_user);

$HTML->box1_top("Account Maintenance: " . user_getname()); ?>

<p>Welcome, <b><?php print user_getname(); ?></b>. 
<p>You can view/change all of your account features from here. You may also wish
to view your developer/consultant profiles and ratings.

<UL>
<LI><A href="/users/<?php print strtolower($row_user['user_name']); ?>/"><B>View My Developer Profile</B></A>
<LI><A HREF="/people/editprofile.php"><B>Edit My Skills Profile</B></A>
</UL>
<?php $HTML->box1_bottom(); ?>

&nbsp;<BR>
<TABLE width=100% border=0>

<TR valign=top>
<TD>Member Since: </TD>
<TD><B><?php print date($sys_datefmt,$row_user['add_date']); ?></B></TD>
</TR>
<TR valign=top>
<TD>User ID: </TD>
<TD><B><?php print $row_user['user_id']; ?></B></TD>
</TR>

<TR valign=top>
<TD>Login Name: </TD>
<TD><B><?php print strtolower($row_user['user_name']); ?></B>
<BR><A href="change_pw.php">[Change Password]</A></TD>
</TR>

<TR valign=top>
<TD>Timezone/Language: </TD>
<TD><B><?php print $row_user['timezone']; ?></B> / <B><?php echo $Language->getLanguageName($row_user['language']); ?></B>
<BR><A href="change_timezone.php">[Change]</A></TD>
</TR>

<TD>Real Name: </TD>
<TD><B><?php print $row_user['realname']; ?></B>
<BR><A href="change_realname.php">[Change Real Name]</A></TD>
</TR>

<TR valign=top>
<TD>Email Addr: </TD>
<TD><B><?php print $row_user['email']; ?></B>
<BR><A href="change_email.php">[Change Email Addr]</A>
</TD>
</TR>

<TR valign=top>
<TD>Skills Profile: </TD>
<TD><A href="/people/editprofile.php">[Edit Skills Profile]</A></TD>
</TR>

<TR>
<TD COLSPAN=2>
<?php 
// ############################# Preferences
$HTML->box1_top("Preferences"); ?>
<FORM action="updateprefs.php" method="post">

<INPUT type="checkbox" name="form_mail_site" value="1"<?php 
	if ($row_user['mail_siteupdates']) print " checked"; ?>> Receive Email for Site Updates
<I>(This is very low traffic and will include security notices. Highly recommended.)</I>

<P><INPUT type="checkbox"  name="form_mail_va" value="1"<?php
	if ($row_user['mail_va']) print " checked"; ?>> Receive additional community mailings. 
<I>(Low traffic.)</I>

<P><INPUT type="checkbox"  name="form_remember_user" value="1"<?php
	if ($sf_user_hash) print " checked"; ?>> "Remember me".
<I>(Allows to access your <a href="/my/">personal page</a> without being logged
in. You will still need to login explicitly before making any changes.)</I>

<P align=center><CENTER><INPUT type="submit" name="Update" value="Update"></CENTER>
</FORM>
<?php $HTML->box1_bottom(); 

// ############################### Shell Account

if ($row_user['unix_status'] == 'A') {
	$HTML->box1_top("Shell Account Information"); 
	print '&nbsp;
<BR>Shell box: <b>'.$row_user['unix_box'].'</b>
<BR>CVS/SSH Shared Keys: <B>';
	// get shared key count from db
	$expl_keys = explode("###",$row_user['authorized_keys']);
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

<?php
site_user_footer(array());
?>

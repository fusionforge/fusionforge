<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');    

session_require(array('isloggedin'=>'1'));

site_user_header(array('title'=>$Language->ACCOUNT_MAINTENANCE));

// get global users vars
$res_user = db_query("SELECT * FROM users WHERE user_id=" . user_getid());
$row_user = db_fetch_array($res_user);

$HTML->box1_top($Language->ACCOUNT_MAINTENANCE.": " . $user->getRealName()); ?>

<?php echo $Language->ACCOUNT_welcome; ?>
<UL>
<LI><A href="/users/<?php print strtolower($row_user['user_name']); ?>/"><B>View My Developer Profile</B></A>
<LI><A HREF="/people/editprofile.php"><B>Edit My Skills Profile</B></A>
</UL>
<?php $HTML->box1_bottom(); ?>

&nbsp;<BR>
<TABLE width=100% border=0>

<TR valign=top>
<TD><?php echo $Language->MEMBER_SINCE; ?>: </TD>
<TD><B><?php print date($sys_datefmt,$row_user['add_date']); ?></B></TD>
</TR>
<TR valign=top>
<TD><?php echo $Language->USER_ID; ?>: </TD>
<TD><B><?php print $row_user['user_id']; ?></B></TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->LOGIN_NAME; ?>: </TD>
<TD><B><?php print strtolower($row_user['user_name']); ?></B>
<BR><A href="change_pw.php">[<?php echo $Language->PASSWORD.' '.$Language->CHANGE; ?>]</A></TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->TIMEZONE; ?>/<?php echo $Language->LANGUAGE; ?>: </TD>
<TD><B><?php print $row_user['timezone']; ?></B> / <B><?php echo $Language->getLanguageName($row_user['language']); ?></B>
<BR><A href="change_timezone.php">[<?php echo $Language->CHANGE; ?>]</A></TD>
</TR>

<TD><?php echo $Language->REALNAME; ?>: </TD>
<TD><B><?php print $row_user['realname']; ?></B>
<BR><A href="change_realname.php">[<?php echo $Language->CHANGE; ?>]</A></TD>
</TR>

<TR valign=top>
<TD><?php echo $Language->EMAILADDR; ?>: </TD>
<TD><B><?php print $row_user['email']; ?></B>
<BR><A href="change_email.php">[<?php echo $Language->CHANGE; ?>]</A>
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
	if ($row_user['mail_siteupdates']) print " checked"; ?>> 
	<?php echo $Language->ACCOUNTREGISTER_siteupdate; ?>

<P><INPUT type="checkbox"  name="form_mail_va" value="1"<?php
	if ($row_user['mail_va']) print " checked"; ?>>
	<?php echo $Language->ACCOUNTREGISTER_communitymail; ?>

<P><INPUT type="checkbox"  name="form_remember_user" value="1"<?php
	if ($sf_user_hash) print " checked"; ?>>
	<?php echo $Language->ACCOUNT_rememberme; ?>

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

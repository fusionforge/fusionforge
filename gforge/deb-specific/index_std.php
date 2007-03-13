<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<?php
printf(_('<P>%1$s is a <B>free service to <A href="http://www.opensource.org/">Open Source</A> developers</B> offering easy access to the best in CVS, mailing lists, bug tracking, message boards/forums, task management, site hosting, permanent file archival, full backups, and total web-based administration.</P><P><B>Site Feedback and Participation</B></P><P>In order to get the most out of %1$s, you\'ll need to <A href="/account/register.php">register as a site user</A>. This will allow you to participate fully in all we have to offer. You may of course browse the site without registering, but will not have access to participate fully.</P><P><B>Set Up Your Own Project</B></P><P><A href="/account/register.php">Register as a site user</A>, then <A HREF="/account/login.php">Login</A> and finally, <A HREF="/register/">Register Your Project</A>.</P><P>Thanks... and enjoy the site.</P>'), $GLOBALS['sys_name'], $GLOBALS['sys_default_domain']) ;

echo $HTML->boxTop(_('Latest News'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->boxBottom();
?>

</td>

<td width="35%" valign="top">

<?php
echo show_features_boxes();
?>

</td></tr></table>

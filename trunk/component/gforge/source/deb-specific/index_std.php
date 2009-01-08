<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<?php
	 printf(_('<p>%1$s is a <b>free service to <a href="http://www.opensource.org/">Open Source</a> developers</b> offering easy access to the best in CVS, mailing lists, bug tracking, message boards/forums, task management, site hosting, permanent file archival, full backups, and total web-based administration.</p><p><b>Site Feedback and Participation</b></p><p>In order to get the most out of %1$s, you\'ll need to <a href="%2$s">register as a site user</a>. This will allow you to participate fully in all we have to offer. You may of course browse the site without registering, but will not have access to participate fully.</p><p><b>Set Up Your Own Project</b></p><p><a href="%2$s">Register as a site user</a>, then <a href="%3$s">Login</a> and finally, <a href="%4$s">Register Your Project</a>.</p><p>Thanks... and enjoy the site.</p>'),
		$GLOBALS['sys_name'],
		util_make_url ('/account/register.php'), 
		util_make_url ('/account/login.php'), 
		util_make_url ('/register/')) ;

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

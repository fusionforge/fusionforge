<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<h3>FusionForge helps you manage the entire development life cycle</h3>
<p>
FusionForge has tools to help your team collaborate, like message forums 
and mailing lists; tools to create and control access to Source Code 
Management repositories like CVS and Subversion. FusionForge automatically 
creates a repository and controls access to it depending on the role 
settings of the project.
</p>
<p>
Additional Features:
</p>
<ul>
	<li>Manage File Releases.</li>
	<li>Document Management.</li>
	<li>News announcements.</li>
	<li>Surveys for users and admins.</li>
	<li>Issue tracking with "unlimited" numbers of categories, text fields, etc.</li>
	<li>Task management.</li>
	<li>Wiki (using MediaWiki or phpWiki).</li>
	<li>A powerful plugin system to add new features.</li>
</ul>
<h3>What's new in FusionForge 4.7</h3>
<p>
<ul>
	<li>A new name to avoid confusion with proprietary versions of GForge.</li>
    <li>Support for php5.</li>
    <li>Support for postgresql 8.x.</li>
    <li>Translations are now managed by gettext.</li>
    <li>Support for several configurations running on the same code.</li>
    <li>Imroved security, now PHP register_globals safe.</li>
    <li>Available as full install CD.</li>
    <li>New wiki plugins (using mediawiki or phpwiki).</li> 
	<li>New online_help plugin.</li>
	<li>New phpwebcalendar plugin.</li>
	<li>New project hierarchy plugin/</li>
</ul>
</p>
<?php
echo $HTML->boxTop(_('Latest News'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->boxBottom();
?>

</td>

<td width="35%" valign="top">
<?php echo $HTML->boxTop('Getting FusionForge'); ?>
<strong>Download:</strong><br />
<a href="http://fusionforge.org/project/showfiles.php?group_id=6">FusionForge archive</a><br />
<a href="http://postgresql.org/">PostgreSQL</a><br />
<a href="http://www.php.net/">PHP</a><br />
<a href="http://www.apache.org/">Apache</a><br />
<a href="http://www.gnu.org/software/mailman/">Mailman</a> <i>(optional)</i><br />
<p />
<strong>Get Help</strong><br />
<a href="http://fusionforge.org/mail/?group_id=6"><strong>Mailing lists</strong></a><br />
<a href="http://embed.mibbit.com/?server=irc.freenode.net&channel=%23fusionforge"><strong>Online Help</strong></a><br />
<p />
<strong>Contribute!</strong><br />
<a href="http://fusionforge.org/projects/fusionforge/"><strong>FusionForge Project Page</strong></a><br />
<a href="http://fusionforge.org/tracker/?atid=105&group_id=6&func=browse"><strong>Bug Tracker</strong></a><br />
<a href="http://fusionforge.org/tracker/?atid=107&group_id=6&func=browse"><strong>Patch Submissions</strong></a><br />
<p />
<a href="http://www.debian.org/"><strong>Debian "lenny" Users</strong></a>
can simply "apt-get install fusionforge" to get a complete FusionForge system.
<p />
<?php
echo $HTML->boxBottom();
echo show_features_boxes();
?>

</td></tr></table>

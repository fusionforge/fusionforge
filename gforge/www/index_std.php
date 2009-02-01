<?php
require_once $gfcommon.'include/FusionForge.class.php';
?>
<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<div align="center"><img src="<?php echo util_make_url ('/images/fusionforge-resized.png') ?>" /></div>
	  <h3><?php print _('FusionForge helps you manage the entire development life cycle'); ?></h3>
<p>

	  <?php print _('FusionForge has tools to help your team collaborate, like message forums and mailing lists; tools to create and control access to Source Code Management repositories like CVS and Subversion. FusionForge automatically creates a repository and controls access to it depending on the role settings of the project.'); ?>

</p>
<p><?php print _('Additional Features:'); ?></p>
<ul>
<li><?php print _('Manage File Releases.'); ?></li>
<li><?php print _('Document Management.'); ?></li>
<li><?php print _('News announcements.'); ?></li>
<li><?php print _('Surveys for users and admins.'); ?></li>
<li><?php print _('Issue tracking with "unlimited" numbers of categories, text fields, etc.'); ?></li>
<li><?php print _('Task management.'); ?></li>
<li><?php print _('Wiki (using MediaWiki or phpWiki).'); ?></li>
<li><?php print _('A powerful plugin system to add new features.'); ?></li>
</ul>
<h3><?php print _("What's new in FusionForge 4.7"); ?></h3>
<p>
<ul>
<li><?php print _('A new name to avoid confusion with proprietary versions of GForge.'); ?></li>
<li><?php print _('Support for PHP5.'); ?></li>
<li><?php print _('Support for PostgreSQL 8.x.'); ?></li>
<li><?php print _('Translations are now managed by gettext.'); ?></li>
<li><?php print _('Support for several configurations running on the same code.'); ?></li>
<li><?php print _('Improved security, no need for PHP register_globals.'); ?></li>
<li><?php print _('Available as full install CD.'); ?></li>
<li><?php print _('New wiki plugins (using MediaWiki or phpWiki).'); ?></li> 
<li><?php print _('New online_help plugin.'); ?></li>
<li><?php print _('New phpwebcalendar plugin.'); ?></li>
<li><?php print _('New project hierarchy plugin.'); ?></li>
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
<?php print _('<a href="http://fusionforge.org/project/showfiles.php?group_id=6">FusionForge archive</a>'); ?><br />
<?php print _('<a href="http://postgresql.org/">PostgreSQL</a>'); ?><br />
<?php print _('<a href="http://www.php.net/">PHP</a>'); ?><br />
<?php print _('<a href="http://www.apache.org/">Apache</a>'); ?><br />
<?php print _('<a href="http://www.gnu.org/software/mailman/">Mailman</a> <i>(optional)</i>'); ?><br />
<p />
<strong><?php print _('Get Help'); ?></strong><br />
<a href="http://fusionforge.org/mail/?group_id=6"><strong><?php print _('Mailing lists'); ?></strong></a><br />
<a href="http://embed.mibbit.com/?server=irc.freenode.net&channel=%23fusionforge"><strong><?php print _('Online help via IRC'); ?></strong></a><br />
<p />
<strong><?php print _('Contribute!'); ?></strong><br />
<a href="http://fusionforge.org/projects/fusionforge/"><strong><?php print _('FusionForge Project Page'); ?></strong></a><br />
<a href="http://fusionforge.org/tracker/?atid=105&group_id=6&func=browse"><strong><?php print _('Bug Tracker'); ?></strong></a><br />
<a href="http://fusionforge.org/tracker/?atid=107&group_id=6&func=browse"><strong><?php print _('Patch Submissions'); ?></strong></a><br />
<p />
<?php print _('Packaging for <a
href="http://www.debian.org/"><strong>Debian</strong></a>/<a
href="http://www.ubuntulinux.com/"><strong>Ubuntu</strong></a> and <a
href="http://www.redhat.com/"><strong>Red Hat</strong></a>/<a
href="http://www.fedoraproject.org/"><strong>Fedora</strong></a> is in
progress.  Users of these systems will soon be able to "apt-get
install fusionforge" and get a complete FusionForge system.
'); ?><p />
<?php
echo $HTML->boxBottom();
echo show_features_boxes();
?>

</td></tr></table>
<center>
<?php
			$forge = new FusionForge() ;
		printf (_('This site is running %1$s version %2$s'),
			$forge->software_name,
			$forge->software_version) ;
?>

<?php
/**
 * FusionForge Standard Index Page
 *
 * Copyright 1999-2013, Fusionforge Team
 * Copyright 2014, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/FusionForge.class.php';
?>
<!-- whole page table -->
<table id="bd" class="fullwidth" summary="">
<tr>
<td id="bd-col1">
	<h2 id="title-home-page"><img src="<?php echo util_make_uri ('/images/fusionforge-resized.png') ?>" alt="FusionForge"/></h2>
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
<li><?php print _('Issue tracking with “unlimited” numbers of categories, text fields, etc.'); ?></li>
<li><?php print _('Task management.'); ?></li>
<li><?php print _('Wiki (using MediaWiki or phpWiki).'); ?></li>
<li><?php print _('A powerful plugin system to add new features.'); ?></li>
</ul>
<h3><?php print _("What's new in FusionForge 5.3"); ?></h3>
<p><?php print _('Checkout the roadmap for closed issues (bugs, patches, features requests) ') ?><a href="/tracker/roadmap.php?group_id=6&roadmap_id=1&release=5.3"><?php echo _('here') ?></a></p>
<p><?php print _('Standards features:') ?></p>
<ul>
<li><?php print _('Widget: MyArtifacts Enhancement: add monitored artifacts (TrivialDev)'); ?></li>
<li><?php print _('Trackers: Attachements moved to filesystem to allow larger attachments & reduce DB size (Alcatel-Lucent)'); ?></li>
<li><?php print _('Docman: Files moved to filesystem using the Storage generic class (TrivialDev)'); ?></li>
<li><?php print _('Users: notify admins when user has validated his account (TrivialDev)'); ?></li>
<li><?php print _('Allow project to disable the Project Activity (Alcatel-Lucent)'); ?></li>
<li><?php print _('User: account ssh key management: rewrite backend, add more informations such as fingerprint, deploy flag, easy delete (TrivialDev)'); ?></li>
<li><?php print _('Docman: Directory monitoring (TrivialDev)'); ?></li>
<li><?php print _('Activity: New Directory appears now in activity (TrivialDev)'); ?></li>
<li><?php print _('Docman: Display number of download per file, max upload size (TrivialDev)'); ?></li>
<li><?php print _('Widget: Project Document Activity: new or updates files, new directories, in the last 4 weeks (TrivialDev)'); ?></li>
<li><?php print _('Frs: Download statistics are available as graph now (TrivialDev)'); ?></li>
<li><?php print _('New SOAP services to handle adding/removing groups, users and tasks (patch by Pasquale Vitale)'); ?></li>
<li><?php print _('Docman: add report view as in FRS (TrivialDev)'); ?></li>
<li><?php print _('New javascript based graphics (bybye jpgraph) (TrivialDev)'); ?></li>
</ul>
<p><?php print _('Plugins:') ?></p>
<ul>
<li><?php print _('headermenu: new plugin to handle links in headermenu, outermenu & groupmenu (TrivialDev)'); ?></li>
<li><?php print _('scmgit: add browsing capability for user personal repository (TrivialDev)'); ?></li>
<li><?php print _('scmgit: basic activity support (TrivialDev)'); ?></li>
<li><?php print _('scmgit: multiple repositories per project (developed for/sponsored by AdaCore)'); ?></li>
<li><?php print _('scmhg: merge patch from Denise Patzker: add http support, online browse, stats (TrivialDev)'); ?></li>
<li><?php print _('webanalytics: new plugin to add support for piwik or google analytics tool (TrivialDev)'); ?></li>
<li><?php print _('scmhook: Support added for pre-revprop-changehooks to change properties (Alcatel-Lucent)'); ?></li>
<li><?php print _('scmhook: Add commitEmail support for scmhg plugin (TrivialDev)'); ?></li>
<li><?php print _('new admssw plugin to provide ADMS.SW compatible RDF descriptions of projects'); ?></li>
<li><?php print _('blocks: improved with a new HTML widget for the project summary page (Alcatel-Lucent)'); ?></li>
<li><?php print _('svntracker: this plugin is superseed by scmhook'); ?></li>
<li><?php print _('svncommitemail: this plugin is superseed by scmhook'); ?></li>
<li><?php print _('new phpcaptcha plugin: enable a captcha in the register page. (TrivialDev)'); ?></li>
</ul>
<h3><?php print _("What's new in FusionForge 5.2"); ?></h3>
<ul>

<li><?php print _('Docman: inject ZIP as a tree (Capgemini)') ?></li>
<li><?php print _('Widget: New User Widget: Last 5 documents published in my project (Capgemini)') ?></li>
<li><?php print _('Docman: mass action (Capgemini)') ?></li>
<li><?php print _('New Message plugin to display global messages like planned upgrade or outage (Alcatel-Lucent).') ?></li>
<li><?php print _('Docman: complete rewritten of trash and pending view (Capgemini)') ?></li>
<li><?php print _('New Scmhook: complete library to handle hooks for any scm available in fusionforge. Currently supporting post-commit and pre-commit hook. scmsvn pre-commit and post-commit library is provided (Capgemini)') ?></li>
<li><?php print _('New Widget: smcgit personal URL of cloned repositories. Currently just a list of URLs of your personal repository cloned from project you belong. (Capgemini)') ?></li>
<li><?php print _('Docman: interaction with the projects-hierarchy plugin to enable hierarchical browsing. (Capgemini)') ?></li>
<li><?php print _('Admin: User add membership to multiples projects in one shot (Capgemini)') ?></li>
<li><?php print _('New MoinMoinWiki plugin (AdaCore)') ?></li>
<li><?php print _('Trackers: New view to display roadmaps view for trackers (Alcatel-Lucent)') ?></li>
<li><?php print _('scmsvn: private project can now be browsed with viewvc using user rights management (TrivialDev).') ?></li>
<li><?php print _('scmsvn: basic activity support (TrivialDev).') ?></li>
</ul>

<h3><?php print _("What's new in FusionForge 5.1"); ?></h3>
<ul>
<li><?php print _('New Funky Theme (Capgemini).'); ?></li>
<li><?php print _('New UI and features for the document manager (download as ZIP, locking, referencing documents by URL) (Capgemini).'); ?></li>
<li><?php print _('New progress bar displaying completion state of trackers using a custom status field.'); ?></li>
<li><?php print _('Improved sorting in trackers (Alcatel-Lucent).'); ?></li>
<li><?php print _('More flexible and more powerful role-based access control system (Coclico).'); ?></li>
<li><?php print _('New unobtrusive tooltip system based on jquery and tipsy to replace old help window (Alcatel-Lucent)'); ?></li>
<li><?php print _('New plugins: Blocks, to add free HTML blocks on top of each tool of the project; Gravatar, to display user faces; OSLC, implementing the OSLC-CM API for tracker interoperability with external tools.'); ?></li>
<li><?php print _('scmgit plugin: Personal Git repositories for project members (AdaCore).'); ?></li>
<li><?php print _('Template projects: there can be several of them, and users registering new projects can pick which template to clone from for their new projects (Coclico).'); ?></li>
<li><?php print _('Simplified configuration system, using standard *.ini files.'); ?></li>
<li><?php print _('Reorganised, modular Apache configuration.'); ?></li>
<li><?php print _('RPM packages for Red Hat (and derived) distributions.'); ?></li>
</ul>

<h3><?php print _("What's new in FusionForge 5.0"); ?></h3>
<ul>
<li><?php print _('Many improvements to the trackers: configurable display, workflow management, links between artifacts, better searches, and more'); ?></li>
<li><?php print _('Rewritten SCM subsystem, with new plugins for Bazaar, Darcs and Git'); ?></li>
<li><?php print _('New version of Mediawiki plugin, providing independent wikis for each project'); ?></li>
<li><?php print _('Various new plugins: projectlabels, globalsearch, extratabs'); ?></li>
<li><?php print _('An in-depth rewrite of the theme for better accessibility and XHTML conformance'); ?></li>
</ul>

<h3><?php print _("What's new in FusionForge 4.8"); ?></h3>
<ul>
<li><?php print _('New project classification by tags (tag cloud).'); ?></li>
<li><?php print _('New reporting item for the File Release System: downloads per package.'); ?></li>
<li><?php print _('List of all projects added in Project List'); ?></li>
<li><?php print _('New version of phpWiki plugin, using lastest svn code'); ?></li>
</ul>

<h3><?php print _("What's new in FusionForge 4.7"); ?></h3>
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
<?php
if(forge_get_config('use_news')) {
	echo $HTML->boxTop(_('Latest News'), 'Latest_News');
	echo news_show_latest(forge_get_config('news_group'), 5, true, false, false, 5);
	echo $HTML->boxBottom();
}
?>

</td>

<td id="bd-col2">
<?php
echo show_features_boxes();
?>

</td></tr></table>

<div id="ft">
<?php
		$forge = new FusionForge();
		printf (_('This site is running %1$s version %2$s'),
			$forge->software_name,
			$forge->software_version) ;
		printf('<div about="" typeof="planetforge:ForgeService">'."\n"
				.'<div rel="planetforge:operated_by">'."\n"
				.'<div about="#forge" typeof="planetforge:ForgeSoftware">'."\n"
				.'<span property="planetforge:name" content="%1$s"></span>'."\n"
				.'<span property="planetforge:version" content="%2$s"></span>'."\n"
				."</div>\n"
				."</div>\n"
				."</div>\n",
				$forge->software_name,
				$forge->software_version);
?>
</div>

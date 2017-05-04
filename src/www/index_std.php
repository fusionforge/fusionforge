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
<table id="bd" class="fullwidth">
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

<h3><?php print _("What's new in FusionForge 6.0"); ?></h3>
<p><?php print _('Checkout the roadmap for closed issues (bugs, patches, features requests) ') ?><a href="https://fusionforge.org/tracker/roadmap.php?group_id=6&amp;roadmap_id=1&amp;release=6.0"><?php echo _('here') ?></a></p>

<p><?php print _('Standards features:') ?></p>
<ul>

<li><?php print _('New install system [#710] (Inria)'); ?></li>
<li><?php print _('Reactivity: system replication is now performed immediately (rather than waiting for cron jobs) [#147] (Inria)'); ?></li>

<li><?php print _('SCM:'); ?>
  <ul>
  <li><?php print _('Concurrent SSH and HTTPS access, relying on Apache mod_itk [#519] (Roland Mas and Inria)'); ?></li>
  <li><?php print _('Allow projects to use several SCM engines in parallel [#751] (Roland Mas, for CEA)'); ?></li>
  <li><?php print _('Support read-only access to private projects via SSH (Inria)'); ?></li>
  <li><?php print _('Browsing support for (Git) private repositories [#519] (Roland Mas, for AdaCore)'); ?></li>
  <li><?php print _('Anonymous read-only access through xinetd and rsync (Inria)'); ?></li>
  </ul>
</li>

<li><?php print _('System: users now use a common default group ("users") rather than per-user group; avoids conflicts with project groups [#760] (Inria)'); ?></li>

<li><?php print _('Docman:'); ?>
  <ul>
  <li><?php print _('Basic Webdav write mkcol, delete, put, move support [#658] (TrivialDev)'); ?></li>
  <li><?php print _('Add move mass actions [#657] (TrivialDev)'); ?></li>
  <li><?php print _('Direct link to file details [#747] (TrivialDev)'); ?></li>
  <li><?php print _('Rewrite parser using unoconv, support more file formats [#749] (Roland Mas, for CEA)'); ?></li>
  </ul>
</li>

<li><?php print _('FRS:'); ?>
  <ul>
  <li><?php print _('Enable widget "My monitored packages" [#697] (TrivialDev)'); ?></li>
  <li><?php print _('Provide new role settings [#705] (TrivialDev)'); ?></li>
  <li><?php print _('Add delete packages, releases or files mass action [#713] (TrivialDev)'); ?></li>
  <li><?php print _('Provide link to download any release as ZIP file [#737] (TrivialDev)'); ?></li>
  <li><?php print _('Fix RBAC migration script [#765] (TrivialDev)'); ?></li>
  <li><?php print _('Reorganise code [#692] (TrivialDev)'); ?></li>
  </ul>
</li>

<li><?php print _('Web UI:'); ?>
  <ul>
  <li><?php print _('Drop tipsy plugin, use standard jQuery UI tooltip already provided [#656] (TrivialDev)'); ?></li>
  <li><?php print _('FusionForge Theme & jQuery UI theme sync [#663] (TrivialDev)'); ?></li>
  <li><?php print _('Update the jQuery & jQuery UI frameworks [#664] (TrivialDev)'); ?></li>
  <li><?php print _('Feedback, error_msg, warning_msg are now store in cookie [#669] (TrivialDev)'); ?></li>
  <li><?php print _('Update the jQuery Auto-height plugin [#716] (TrivialDev)'); ?></li>
  <li><?php print _('Updated French translation (Stéphane Aulery and Inria)'); ?></li>
	</ul>
</li>

<li><?php print _('Widgets:'); ?>
  <ul>
  <li><?php print _('Public Area: display FRS link [#684] (TrivialDev)'); ?></li>
  <li><?php print _('My Latest Commits: New widget to display user commits on "My Page" [#743] (TrivialDev)'); ?></li>
  <li><?php print _('Project Latest Commits: New widget to display the 5 latest commits on the project page  (TrivialDev)'); ?></li>
  <li><?php print _('Project Latest Documents: enhancement, add actions buttons (monitor, delete) [#745] (TrivialDev)'); ?></li>
  </ul>
</li>

<li><?php print _('Tracker: enable support for multi-select extrafield in roadmap [#655] (TrivialDev)'); ?></li>

<li><?php print _('Forum: store the attached file on FS [#662] (TrivialDev)'); ?></li>

<li><?php print _('vhosts: allow customization from &lt;config_dir&gt;/custom/httpd.vhosts.tmpl (Inria)'); ?></li>
</ul>

<p><?php print _('Plugins:') ?></p>
<ul>

<li><?php print _('SCM SVN: Improved ViewVC integration, using external installation [#719] (Inria, TrivialDev)'); ?></li>
<li><?php print _('SCM Git: Activity log entry link to commit log in SCM browsing tab [#719] (TrivialDev)'); ?></li>

<li><?php print _('SCM Hg (Mercurial)'); ?>
  <ul>
  <li><?php print _('Display the Repository History stats block [#724] (TrivialDev)'); ?></li>
  <li><?php print _('Add support for project activity tab [#725] (TrivialDev)'); ?></li>
  <li><?php print _('Activity log entry link to commit log in SCM browsing tab [#726] (TrivialDev)'); ?></li>
  </ul>
</li>

<li><?php print _('scmhook'); ?>
  <ul>
  <li><?php print _('Update git post-receive email hook (tarent solutions GmbH, Teckids e.V.)'); ?></li>
  <li><?php print _('Install hooks as the requesting system user (Inria)'); ?></li>
  </ul>
</li>

<li><?php print _('AuthLDAP: Support LPAP_OPT_REFERRALS option, needed by ActiveDirectory Server [#734] (TrivialDev)'); ?></li>

<li><?php print _('Task Board: New Agile TaskBoard supporting Scrum and Kanban methodologies (Vitaliy Pylypiv and TrivialDev)'); ?></li>
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
		$forge = FusionForge::getInstance();
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

<?php
/**
 * FusionForge Standard Index Page
 *
 * Copyright 1999-2013, Fusionforge Team
 * Copyright 2014,2017, Franck Villaume - TrivialDev
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
global $HTML;

echo $HTML->listTableTop(array(), array(), 'fullwidth', 'bd');
?>
<!-- whole page table -->
<tr>
<td id="bd-col1">
<?php
echo html_e('h2', array('id' => 'title-home-page'), '<img src='.util_make_uri('/images/fusionforge-resized.png').' alt="FusionForge"/>');
echo html_e('h3', array(), _('FusionForge helps you manage the entire development life cycle'));
echo html_e('p', array(), _('FusionForge has tools to help your team collaborate, like message forums and mailing lists; tools to create and control access to Source Code Management repositories like CVS and Subversion. FusionForge automatically creates a repository and controls access to it depending on the role settings of the project.'));

echo html_e('p', array(), _('Additional Features')._(':'));

$liElements = array();
$liElements[]['content'] = _('Manage File Releases.');
$liElements[]['content'] = _('Document Management.');
$liElements[]['content'] = _('News announcements.');
$liElements[]['content'] = _('Surveys for users and admins.');
$liElements[]['content'] = _('Issue tracking with “unlimited” numbers of categories, custom fields, etc.');
$liElements[]['content'] = _('Task management.');
$liElements[]['content'] = _('Wiki (using MediaWiki or MoinMoin).');
$liElements[]['content'] = _('A powerful plugin system to add new features.');
echo $HTML->html_list($liElements);

echo html_e('h3', array(), _("What's new in FusionForge 6.1"));
echo html_e('p', array(), _('Checkout the roadmap for closed issues (bugs, patches, features requests)').' '.util_make_link("https://fusionforge.org/tracker/roadmap.php?group_id=6&amp;roadmap_id=1&amp;release=6.1", _('here'), false, true));

echo html_e('p', array(), _('Standards features')._(':'));

$liElements = array();
$liElements[]['content'] = _('Forge Home page supports widget system in parallel of the current customization page (TrivialDev)');
$liElements[]['content'] = _('cross ref document/release. Use [DNNN]/[RNNN] where NNN is the ID of the document/frs release. (TrivialDev)');
$liElements[]['content'] = _('Install System: add support for OpenSuSE. [#849] (Ralf Habacker)');
$liElements[]['content'] = _('CLI dump/import functions to extract or import massively into FusionForge [#848] (TrivialDev)');
$liElements[]['content'] = _('new dynamic quickNav menu: based on user activity to select 5 more visited projects (TrivialDev)');
$liElements[]['content'] = _('Projects Page: add paging system in full_list and tag_cloud subpages (TrivialDev)');
$liElements[]['content'] = _('support object association n-n, bidirectional (Artifact, Document, FRSRelease) (TrivialDev)');
$liElements[]['content'] = _('url_prefix configuration parameter complete support. [#643] (TrivialDev)');
$subliElements = array();
$subliElements[]['content'] = _('minimum password length is now 8 (Sylvain Beucler)');
$subliElements[]['content'] = _('add minimal constraints to password (upper-case, lower-case, non-alphanumeric check) [#826] (Inria)');
$liElements[]['content'] = _('Accounts').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('limit number of returned documents on search query. Use paging system [#794] (TrivialDev)');
$subliElements[]['content'] = _('limit search using from & to dates [#798] (TrivialDev)');
$subliElements[]['content'] = _('use standard search engine: unify results between "search in project" & "search in the docs" tab (TrivialDev)');
$subliElements[]['content'] = _('searchengine: DocsAll & Docs unified. (TrivialDev)');
$subliElements[]['content'] = _('searchengine: add edit file action on result. (TrivialDev)');
$subliElements[]['content'] = _('notify users on document. (TrivialDev)');
$subliElements[]['content'] = _('support private status on directory. (TrivialDev)');
$subliElements[]['content'] = _('support document versioning. (TrivialDev)');
$subliElements[]['content'] = _('support cross ref. forum, documents, task or artifact. (TrivialDev)');
$subliElements[]['content'] = _('add new document review feature. to review version, document and post comment to a document. (TrivialDev)');
$subliElements[]['content'] = _('add manual upload method to inject zip feature. (TrivialDev)');
$liElements[]['content'] = _('Document Management')._(' aka docman').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('link package release to tracker roadmap. (TrivialDev)');
$liElements[]['content'] = _('File Release System')._(' aka FRS').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('keep values in artifact new submit form on error. (TrivialDev)');
$subliElements[]['content'] = _('add new option on customfield text: regex pattern validation (TrivialDev)');
$subliElements[]['content'] = _('add new customfield: User (TrivialDev)');
$subliElements[]['content'] = _('add new customfield: DateTime (TrivialDev)');
$subliElements[]['content'] = _('add new customfield: Release (TrivialDev)');
$subliElements[]['content'] = _('add support for mandatory fields on workflow of artifact (TrivialDev)');
$subliElements[]['content'] = _('add support for description on customfield to be used in tooltip (TrivialDev)');
$subliElements[]['content'] = _('fix customfield cloning when not using default template fusionforge project [#829] (TrivialDev)');
$subliElements[]['content'] = _('add support for autoassign [#151] &amp; [#149] (TrivialDev)');
$subliElements[]['content'] = _('add default value support for custom fields (TrivialDev)');
$subliElements[]['content'] = _('CSV export, support lastModifiedDate filtering (TrivialDev)');
$subliElements[]['content'] = _('CSV export, add comments [#853] (Dassault Aviation)');
$subliElements[]['content'] = _('add new customfield: Effort (TrivialDev)');
$subliElements[]['content'] = _('new widget oriented display to replace the old "2 columns" view. (TrivialDev)');
$subliElements[]['content'] = _('support Markdown syntax in artifact detailled description and comments (TrivialDev)');
$liElements[]['content'] = _('Trackers').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('support only FTI queries (TrivialDev)');
$subliElements[]['content'] = _('index project tags and use them for search (Roland Mas)');
$subliElements[]['content'] = _('provide language-specific settings for better indexation/search (Roland Mas)');
$liElements[]['content'] = _('Search').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('add paging system in userlist page [#799] (TrivialDev)');
$liElements[]['content'] = _('Site Admin').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('markdown support & fix code syntax highlight [#865] (TrivialDev)');
$liElements[]['content'] = _('Snippet').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('getArtifacts tracker function: support changed_from parameter (TrivialDev)');
$subliElements[]['content'] = _('getFlattedArtifacts function: to return as CSV export. All data in 1 call (TrivialDev)');
$liElements[]['content'] = _('SOAP').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('upgrade splitter jquery plugin to 0.20.0 (TrivialDev)');
$subliElements[]['content'] = _('upgrade jquery ui to 1.12.1 (TrivialDev)');
$subliElements[]['content'] = _('upgrade jquery to 1.12.4 (Nokia)');
$subliElements[]['content'] = _('HTML 5 Doctype; use HTML 5 &lt;header&gt;, &lt;main&gt; and &lt;footer&gt; tags in Funky theme (Nokia)');
$liElements[]['content'] = _('Web UI').$HTML->html_list($subliElements);
$subliElements = array();
$subliElements[]['content'] = _('MySystasks: new widget for user to display systasks perform on user projects (TrivialDev)');
$subliElements[]['content'] = _('ProjectlatestArtifact: new widget for project to display the 5 more recent artifacts (TrivialDev)');
$subliElements[]['content'] = _('ProjectScmStats: new widget for project to display SCM stats (TrivialDev)');
$subliElements[]['content'] = _('HomeRss: new widget for Forge home page to display RSS flow (TrivialDev)');
$subliElements[]['content'] = _('HomeLatestFileReleases: new widget for Forge home page to display 5 latest File releases across the forge (TrivialDev)');
$liElements[]['content'] = _('Widgets').$HTML->html_list($subliElements);
echo $HTML->html_list($liElements);

echo html_e('p', array(), _('Plugins')._(':'));
$liElements = array();
$liElements[]['content'] = _('Plugin AuthBuiltin: add captcha after 3 attempts with the same login [#795] (TrivialDev)');
$liElements[]['content'] = _('Plugin AuthLDAP: support X_FORWARD_USER to delegate authentication and then retrieve user from LDAP (TrivialDev)');
$liElements[]['content'] = _('Plugin Blocks: support Markdown syntax. (TrivialDev)');
$liElements[]['content'] = _('Plugin GlobalActivity: forge-wide aggregation for project activities (Roland Mas)');
$liElements[]['content'] = _('Plugin Mediawiki: support activity for public project (TrivialDev)');
$liElements[]['content'] = _('Plugin Mediawiki: upgrade to MW 1.23 on CentOS, to MW 1.27 on Debian [#746] (TrivialDev)');
$liElements[]['content'] = _('Plugin phptexcaptcha: new plugin to support standard php captcha library (TrivialDev)');
$liElements[]['content'] = _('Plugin REST: new REST api (Alain Peyrat & TrivialDev)');
$liElements[]['content'] = _('Plugin Scmhook: add CVS commitTracker hook [#700] (Philipp Keidel & TrivialDev)');
$liElements[]['content'] = _('Plugin Taskboard: support multiple taskboards per project [#785] (TrivialDev)');
$liElements[]['content'] = _('Plugin Taskboard: support filtering tasks [#786] (TrivialDev)');
$liElements[]['content'] = _('SCM plugins: support stats per repository (TrivialDev)');
echo $HTML->html_list($liElements);

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

</td></tr>
<?php
echo $HTML->listTableBottom();
?>

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

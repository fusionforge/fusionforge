<?php
/**
 * Site Admin main page
 *
 * This pages lists all global administration facilities for the
 * site, including user/group properties editing, maintanance of
 * site meta-information (Trove maps, metadata for file releases),
 * etc.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2013, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/FusionForge.class.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'include/role_utils.php';

site_admin_header(array('title'=>_('Site Admin')));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

?>

<div class="info-box">
<h2><?php echo _('User Maintenance'); ?></h2>
	<ul>
	<li><?php
		$gforge = new FusionForge();
		printf(_('Active site users: <strong>%d</strong>'),
		    $gforge->getNumberOfActiveUsers());
	?></li>
	<li><a href="userlist.php"><?php echo _('Display Full User List/Edit Users'); ?></a></li>
	<li><?php
	echo _('Display Users Beginning with:').' ';
	for ($i=0; $i < count($abc_array); $i++) {
		echo '<a href="userlist.php?user_name_search='.$abc_array[$i].'">'.$abc_array[$i].'</a>|';
	}
?>
	<br />
		<form name="usersrch" action="search.php" method="post">
		<?php echo _('Search <em>(userid, username, realname, email)</em>'); ?>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="usersearch" value="1" />
		<input type="submit" value="<?php echo _('Search'); ?>" />
		</form>
	</li>
	<li><a href="../account/register.php"><?php
	echo _('Register a New User');
	?></a>
	</li>
	<li><a href="userlist.php?status=P"><?php echo _('Pending users'); ?></a></li>
</ul>
<?php
	$params = array('result' => '');
	$plugins_site_admin_user_html = '';
	plugin_hook_by_reference("site_admin_user_maintenance_hook", $params);
	if ($params['result']) {
			$plugins_site_admin_user_html = $params['result'];
	}
	if ($plugins_site_admin_user_html) {
		echo '<h3>'.  _('Plugins User Maintenance') .'</h3>';
		echo '<ul>';
		echo $plugins_site_admin_user_html;
		echo '</ul>';
	}
?></div>

<div class="info-box">
<h2><?php echo _('Global roles and permissions'); ?></h2>
	<ul>
	<li><?php

		echo '<form action="globalroleedit.php" method="post"><p>';
		echo global_role_box('role_id');
		echo '<input type="submit" name="edit" value="'._("Edit Role").'" /></p></form>';
?>
</li>
<li>
<?php

		echo '<form action="globalroleedit.php" method="post"><p>';
		echo '<input type="text" name="role_name" size="10" value="" required="required" />';
		echo '<input type="submit" name="add" value="'._("Create Role").'" /></p></form>';
	?></li>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Project Maintenance'); ?></h2>
<ul>
	<li><?php
		$res = db_query_params('SELECT count(*) AS count FROM groups
			WHERE group_id > 4
			    AND register_time > 0
			    AND is_template = 0',
		    array());
		$row = db_fetch_array($res);
		printf(_('Registered projects: <strong>%d</strong>'), $row['count']);
	?></li>
	<li><?php
		$res = db_query_params('SELECT count(*) AS count FROM groups
			WHERE group_id > 4
			    AND status = $1
			    AND register_time > 0
			    AND is_template = 0',
		    array('A'));
		$row = db_fetch_array($res);
		printf(_('Active projects: <strong>%d</strong>'), $row['count']);
	?></li>
	<li><?php
		$res = db_query_params('SELECT count(*) AS count FROM groups
			WHERE group_id > 4
			    AND status = $1
			    AND register_time > 0
			    AND is_template = 0',
		    array('P'));
		$row = db_fetch_array($res);
		printf(_('Pending projects: <strong>%d</strong>'), $row['count']);
	?></li>
	<li><a href="grouplist.php"><?php echo _('Display Full Project List/Edit Projects'); ?></a></li>

	<li><?php echo _('Display Projects Beginning with:').' ';
	for ($i=0; $i < count($abc_array); $i++) {
		echo '<a href="grouplist.php?group_name_search='.$abc_array[$i].'">'.$abc_array[$i].'</a>|';
	}
?>
	<br />
		<form name="gpsrch" action="search.php" method="post">
		<?php echo _('Search <em>(groupid, project Unix name, project full name)</em>'); ?>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="groupsearch" value="1" />
		<input type="submit" value="<?php echo _('Search'); ?>" />
		</form>
	</li>
	<li><?php echo util_make_link ('/register/',_('Register New Project')); ?></li>
	<li><a href="approve-pending.php"><?php echo _('Pending projects (new project approval)'); ?></a></li>
	<li><form name="projectsearch" action="search.php">
	<?php echo _('Projects with status'); ?>
	<select name="status">
			<option value="A"><?php echo _('Active (A)'); ?></option>
			<option value="H"><?php echo _('Hold (H)'); ?></option>
			<option value="P"><?php echo _('Pending (P)'); ?></option>
	</select>
	<input type="hidden" name="groupsearch" value="1"/>
	<input type="hidden" name="search" value="%"/>
	<input type="submit" value="<?php echo _('Submit');?> "/>
	</form></li>
	<li><a href="search.php?groupsearch=1&amp;is_public=0"><?php echo _('Private Projects'); ?></a></li>
</ul>
<?php
	$params = array('result' => '');
	$plugins_site_admin_project_html = '';
	plugin_hook_by_reference("site_admin_project_maintenance_hook", $params);
	if ($params['result']) {
			$plugins_site_admin_project_html = $params['result'];
	}
	if ($plugins_site_admin_project_html) {
		echo '<h3>'.  _('Plugins Project Maintenance') .'</h3>';
		echo '<ul>';
		echo $plugins_site_admin_project_html;
		echo '</ul>';
	}
?>
</div>

<?php if(forge_get_config('use_news')) {?>
<div class="info-box">
<h2><?php echo _('News'); ?></h2>
<ul>
	<li><?php echo util_make_link ('/admin/pending-news.php',_('Pending news (moderation for front-page)')); ?></li>
</ul>
</div>
<?php } ?>

<div class="info-box">
<h2><?php echo _('Stats'); ?></h2>
<ul>
	<li><?php echo util_make_link('/stats/',_('Site-Wide Stats')); ?></li>
	<?php plugin_hook('webanalytics_admin', array()); ?>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Trove Project Tree'); ?></h2>
<ul>
	<li><a href="trove/trove_cat_list.php"><?php echo _('Display Trove Map'); ?></a></li>
	<li><a href="trove/trove_cat_add.php"><?php echo _('Add to the Trove Map'); ?></a></li>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Site Utilities'); ?></h2>
<ul>
	<li><a href="massmail.php"><?php printf(_('Mail Engine for %s Subscribers'), forge_get_config ('forge_name')); ?></a></li>
	<li><a href="unsubscribe.php"><?php echo forge_get_config ('forge_name'); ?> <?php echo _('Site Mailings Maintenance'); ?></a></li>
	<li><a href="edit_frs_filetype.php"><?php echo _('Add, Delete, or Edit File Types'); ?></a></li>
	<li><a href="edit_frs_processor.php"><?php echo _('Add, Delete, or Edit Processors'); ?></a></li>
	<li><a href="edit_theme.php"><?php echo _('Add, Delete, or Edit Themes'); ?></a></li>
	<li><a href="<?php echo util_make_url ('/stats/lastlogins.php'); ?>"><?php echo _('Most Recent Opened Sessions'); ?></a></li>
	<li><a href="cronman.php"><?php echo _('Cron Manager'); ?></a></li>
	<li><a href="pluginman.php"><?php echo _('Plugin Manager'); ?></a></li>
	<li><a href="configman.php"><?php echo _('Config Manager'); ?></a></li>
	<?php plugin_hook("site_admin_option_hook", false); ?>
</ul>

<?php if(forge_get_config('use_project_database') || forge_get_config('use_project_vhost') || forge_get_config('use_people')) { ?>
<ul>
	<?php if(forge_get_config('use_project_vhost')) { ?>
		<li><a href="vhost.php"><?php echo _('Virtual Host Admin Tool'); ?></a></li>
	<?php
	}
	if(forge_get_config('use_project_database')) { ?>
		<li><a href="database.php"><?php echo _('Project Database Administration'); ?></a></li>
	<?php }
	if(forge_get_config('use_people')) { ?>
		<li><a href="<?php echo util_make_url ('/people/admin/'); ?>"><?php echo _('Job / Categories Administration'); ?></a></li>
	<?php } ?>
</ul>
</div>
<?php }
?>
<?php
site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

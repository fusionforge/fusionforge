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
 * Copyright 2013-2014, Franck Villaume - TrivialDev
 * Copyright 2017, Stéphane-Eymeric Bredthauer - TrivialDev
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

echo html_ao('div', array('class' => 'info-box'));
echo html_e('h2', array(), _('User Maintenance'));
$lielements = array();
$forge = FusionForge::getInstance();
$lielements[] = array('content' => sprintf(_('Active site users: <strong>%d</strong>'), $forge->getNumberOfActiveUsers()));
$lielements[] = array('content' => util_make_link('/admin/userlist.php', _('Display Full User List/Edit Users')));
$localcontent = _('Display Users Beginning with')._(': ');
$localcontent .= html_ao('div', array('class' => 'abc'));
for ($i = 0; $i < count($abc_array); $i++) {
	$localcontent .= util_make_link('/admin/userlist.php?user_name_search='.$abc_array[$i], $abc_array[$i]);
}
$localcontent .= html_ac(html_ap() -1); // </div> .abc
$localcontent .= html_e('br');
$localcontent .= $HTML->openForm(array('name' => 'usersrch', 'action' => '/admin/search.php', 'method' => 'post'));
$localcontent .= _('Search <em>(userid, username, realname, email)</em>');
$localcontent .= html_e('input', array('type' => 'text', 'name' => 'search'));
$localcontent .= html_e('input', array('type' => 'hidden', 'name' => 'substr', 'value' => 1));
$localcontent .= html_e('input', array('type' => 'hidden', 'name' => 'usersearch', 'value' => 1));
$localcontent .= html_e('input', array('type' => 'submit', 'value' => _('Search')));
$localcontent .= $HTML->closeForm();
$lielements[] = array('content' => $localcontent);
$lielements[] = array('content' => util_make_link('/account/register.php', _('Register a New User')));
$lielements[] = array('content' => util_make_link('/admin/userlist.php?status=P', _('Pending users')));
echo $HTML->html_list($lielements);

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
echo html_ac(html_ap() - 1);
?>

<div class="info-box">
<h2><?php echo _('Global roles and permissions'); ?></h2>
	<ul>
	<li><?php

		echo $HTML->openForm(array('action' => '/admin/globalroleedit.php', 'method' => 'post'));
		echo global_role_box('role_id');
		echo '<input type="submit" name="edit" value="'._("Edit Role").'" />'.$HTML->closeForm();
?>
</li>
<li>
<?php

		echo $HTML->openForm(array('action' => '/admin/globalroleedit.php', 'method' => 'post'));
		echo '<input type="text" name="role_name" size="10" value="" required="required" />';
		echo '<input type="submit" name="add" value="'._("Create Role").'" />'.$HTML->closeForm();
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
		echo _('Registered projects')._(': ').'<strong>'.$row['count'].'</strong>';
	?></li>
	<li><?php
		$res = db_query_params('SELECT count(*) AS count FROM groups
			WHERE group_id > 4
			    AND status = $1
			    AND register_time > 0
			    AND is_template = 0',
		    array('A'));
		$row = db_fetch_array($res);
		echo _('Active projects')._(': ').'<strong>'.$row['count'].'</strong>';
	?></li>
	<li><?php
		$res = db_query_params('SELECT count(*) AS count FROM groups
			WHERE group_id > 4
			    AND status = $1
			    AND register_time > 0
			    AND is_template = 0',
		    array('P'));
		$row = db_fetch_array($res);
		echo _('Pending projects')._(': ').'<strong>'.$row['count'].'</strong>';
	?></li>
	<li><?php echo util_make_link('/admin/grouplist.php', _('Display Full Project List/Edit Projects')); ?></li>

	<li><?php echo _('Display Projects Beginning with:').' ';
	echo html_ao('div', array('class' => 'abc'));
	for ($i=0; $i < count($abc_array); $i++) {
		echo util_make_link('/admin/grouplist.php?group_name_search='.$abc_array[$i], $abc_array[$i]);
	}
	echo html_ac(html_ap() -1); // </div> .abc
	echo html_e('br');
	echo $HTML->openForm(array('name'=> 'gpsrch', 'action' => '/admin/search.php', 'method' => 'post'));
		echo _('Search <em>(groupid, project Unix name, project full name)</em>'); ?>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="groupsearch" value="1" />
		<input type="submit" value="<?php echo _('Search'); ?>" />
		<?php echo $HTML->closeForm(); ?>
	</li>
	<li><?php echo util_make_link('/register/',_('Register New Project')); ?></li>
	<li><?php echo util_make_link('/admin/approve-pending.php', _('Pending projects (new project approval)')); ?></li>
	<li><?php echo $HTML->openForm(array('name' => 'projectsearch', 'action' => '/admin/search.php', 'method' => 'post')); ?>
	<label for="status">
	<?php echo _('Projects with status'); ?>
	</label>
	<select id="status" name="status">
		<option value="A"><?php echo _('Active (A)'); ?></option>
		<option value="H"><?php echo _('Hold (H)'); ?></option>
		<option value="P"><?php echo _('Pending (P)'); ?></option>
	</select>
	<input type="hidden" name="groupsearch" value="1"/>
	<input type="hidden" name="search" value="%"/>
	<input type="submit" value="<?php echo _('Submit');?> "/>
	<?php echo $HTML->closeForm(); ?></li>
	<li><?php echo util_make_link('/admin/search.php?groupsearch=1&is_public=0', _('Private Projects')); ?></li>
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
<?php echo html_e('h2', array(), _('News')); ?>
	<ul>
		<li><?php echo util_make_link('/admin/pending-news.php', _('Pending news (moderation for front-page)')); ?></li>
	</ul>
</div>
<?php }

if(forge_get_config('use_diary')) {?>
<div class="info-box">
<?php echo html_e('h2', array(), _('Diary & Notes')); ?>
	<ul>
		<li><?php echo util_make_link('/admin/pending-diary.php', _('Pending diary & notes (moderation for headlines front-page)')); ?></li>
	</ul>
</div>
<?php } ?>

<div class="info-box">
<?php echo html_e('h2', array(), _('Stats')); ?>
	<ul>
		<li><?php echo util_make_link('/stats/', _('Site-Wide Stats')); ?></li>
		<?php plugin_hook('webanalytics_admin', array()); ?>
	</ul>
</div>

<div class="info-box">
<?php echo html_e('h2', array(), _('Trove Project Tree')); ?>
	<ul>
		<li><?php echo util_make_link('/admin/trove/trove_cat_list.php', _('Display Trove Map')); ?></li>
		<li><?php echo util_make_link('/admin/trove/trove_cat_add.php', _('Add to the Trove Map')); ?></li>
</ul>
</div>

<div class="info-box">
<?php echo html_e('h2', array(), _('Site Utilities')); ?>
<ul>
	<li><?php echo util_make_link('/admin/massmail.php', sprintf(_('Mail Engine for %s Subscribers'), forge_get_config ('forge_name'))); ?></li>
	<li><?php echo util_make_link('/admin/unsubscribe.php', forge_get_config('forge_name').' '._('Site Mailings Maintenance')); ?></li>
	<li><?php echo util_make_link('/admin/effortunitsedit.php', _('Manage Effort Units')); ?></li>
	<li><?php echo util_make_link('/admin/edit_frs_filetype.php', _('Add, Delete, or Edit File Types')); ?></li>
	<li><?php echo util_make_link('/admin/edit_frs_processor.php', _('Add, Delete, or Edit Processors')); ?></li>
	<li><?php echo util_make_link('/admin/edit_theme.php', _('Add, Delete, or Edit Themes')); ?></li>
	<li><?php echo util_make_link('/stats/lastlogins.php', _('Most Recent Opened Sessions')); ?></li>
	<li><?php echo util_make_link('/admin/cronman.php', _('Cron Manager')); ?></li>
	<li><?php echo util_make_link('/admin/pluginman.php', _('Plugin Manager')); ?></li>
	<li><?php echo util_make_link('/admin/configman.php', _('Config Manager')); ?></li>
	<?php plugin_hook("site_admin_option_hook", array()); ?>
</ul>

<?php if(forge_get_config('use_project_vhost') || forge_get_config('use_people')) { ?>
<ul>
	<?php if(forge_get_config('use_project_vhost')) { ?>
		<li><?php echo util_make_link('/admin/vhost.php', _('Virtual Host Admin Tool')); ?></li>
	<?php }
	if(forge_get_config('use_people')) { ?>
		<li><?php echo util_make_link('/people/admin/', _('Job / Categories Administration')); ?></li>
	<?php } ?>
</ul>
</div>
<?php
}
site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

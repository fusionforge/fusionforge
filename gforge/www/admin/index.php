<?php
/**
 * GForge Site Admin main page
 *
 * This pages lists all global administration facilities for the
 * site, including user/group properties editing, maintanance of
 * site meta-information (Trove maps, metadata for file releases),
 * etc.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('pre.php');
require_once('www/admin/admin_utils.php');

site_admin_header(array('title'=>$Language->getText('admin_index','title')));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

?>

<p><strong><?php echo $Language->getText('admin_index','user_maintaince'); ?></strong></p>
<ul>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM users WHERE status='A'");
		$row = db_fetch_array($res);
		echo $Language->getText('admin_index','active_users_count', array($row['count']));
	?></li>
</ul>
<ul>
	<li><a href="userlist.php"><?php echo $Language->getText('admin_index','display_full_user_list'); ?></a>&nbsp;&nbsp;</li>
	<li><?php
	echo $Language->getText('admin_index','display_user_beginning_with').' ';
	for ($i=0; $i < count($abc_array); $i++) {
		echo '<a href="userlist.php?user_name_search='.$abc_array[$i].'">'.$abc_array[$i].'</a>|';
	}
?>
	<br />
		<form name="usersrch" action="search.php" method="post">
		<?php echo $Language->getText('admin_index','search_users'); ?>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="usersearch" value="1" />
		<input type="submit" value="<?php echo $Language->getText('admin_index','get'); ?>" />
		</form>
	</li>
</ul>
<p>
<strong><?php echo $Language->getText('admin_index','group_maintaince'); ?></strong>
</p>
<ul>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM groups");
		$row = db_fetch_array($res);
		echo $Language->getText('admin_index','registered_projects_count', array($row['count']));
	?></li>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
		$row = db_fetch_array($res);
		echo $Language->getText('admin_index','active_projects_count', array($row['count']));
	?></li>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
		$row = db_fetch_array($res);
		echo $Language->getText('admin_index','pending_projects_count', array($row['count']));
	?></li>
</ul>
<ul>
	<li><a href="grouplist.php"><?php echo $Language->getText('admin_index','display_full_group'); ?></a></li>

	<li>
	<?php
	echo $Language->getText('admin_index','display_groups_beginning_with').' ';
	for ($i=0; $i < count($abc_array); $i++) {
		echo '<a href="grouplist.php?group_name_search='.$abc_array[$i].'">'.$abc_array[$i].'</a>|';
	}
?>
	<br />
		<form name="gpsrch" action="search.php" method="post">
		<?php echo $Language->getText('admin_index','search_groups'); ?>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="groupsearch" value="1" />
		<input type="submit" value="<?php echo $Language->getText('admin_index','get'); ?>" />
		</form>
	</li>
</ul>
<ul>
	<li><a href="/register/"><?php echo $Language->getText('admin_index','register_new_project'); ?></a></li>
	<li><?php echo $Language->getText('admin_index','groups_with_status'); ?> <a href="approve-pending.php"><?php echo $Language->getText('admin_index','project_pending'); ?> <em><?php echo $Language->getText('admin_index','new_project_approval'); ?></em></a></li>
	<li><form name="projectsearch" action="search.php">
	<?php echo $Language->getText('admin_index','groups_with_status'); ?>
	<select name="status">
			<option value="D"><?php echo $Language->getText('admin_index','project_deleted'); ?></option>
			<option value="A"><?php echo $Language->getText('admin_index','project_active'); ?></option>
			<option value="H"><?php echo $Language->getText('admin_index','project_hold'); ?></option>
			<option value="P"><?php echo $Language->getText('admin_index','project_pending'); ?></option>
	</select>
	<input type="hidden" name="groupsearch" value="1">
	<input type="hidden" name="search" value="%">
	<input type="submit" value="<?php echo $Language->getText('general','submit');?> "/>
	</form></li>
	<li><a href="search.php?groupsearch=1&amp;search=%&amp;is_public=0"><?php echo $Language->getText('admin_index','private_groups'); ?></a></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','news'); ?></strong>
</p>
<ul>
	<li><a href="/news/admin/"><?php echo $Language->getText('admin_index','approve_reject'); ?></a> <?php echo $Language->getText('admin_index','front_page_news'); ?></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','stats'); ?></strong>
</p>
<ul>
	<li><a href="/stats/"><?php echo $Language->getText('admin_index','site_wide_stats'); ?></a></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','trove_project_tree'); ?></strong>
</p>
<ul>
	<li><a href="trove/trove_cat_list.php"><?php echo $Language->getText('admin_index','display_trove_map'); ?></a></li>
	<li><a href="trove/trove_cat_add.php"><?php echo $Language->getText('admin_index','add_to_trove_map'); ?></a></li>
</ul>

<p><strong><?php echo $Language->getText('admin_index','site_utilities'); ?></strong></p>
<ul>
	<li><a href="massmail.php"><?php echo $Language->getText('admin_index','mail_engine',array($GLOBALS['sys_name'])); ?></a></li>
	<li><a href="unsubscribe.php"><?php echo $GLOBALS['sys_name']; ?> <?php echo $Language->getText('admin_index','site_mailing_maintaince'); ?></a></li>
	<li><a href="edit_supported_languages.php"><?php echo $Language->getText('admin_index','add_delete_edit_laguage'); ?></a></li>
	<li><a href="edit_frs_filetype.php"><?php echo $Language->getText('admin_index','add_delete_edit_file_types'); ?></a></li>
	<li><a href="edit_frs_processor.php"><?php echo $Language->getText('admin_index','add_edit_delete_processors'); ?></a></li>
	<li><a href="edit_theme.php"><?php echo $Language->getText('admin_index','add_edit_delete_themes'); ?></a></li>
	<li><a href="edit_licenses.php"><?php echo $Language->getText('admin_index','add_edit_delete_licenses'); ?></a></li>
	<li><a href="/admin/languages/loadtabfiles.php"><?php echo $Language->getText('admin_index','translation_file_tool'); ?></a></li>
	<li><a href="/stats/lastlogins.php"><?php echo $Language->getText('admin_index','recent_logins'); ?></a></li>
	<li><a href="cronman.php"><?php echo $Language->getText('admin_index','cronman'); ?></a></li>
	<?php plugin_hook("site_admin_option_hook", false); ?>
</ul>

<?php if($GLOBALS['sys_use_project_database'] || $GLOBALS['sys_use_project_vhost']) { ?>
<ul>
	<?php if($GLOBALS['sys_use_project_vhost']) { ?>
		<li><a href="vhost.php"><?php echo $Language->getText('admin_index','virtual_host_admin_tool'); ?></a></li>
	<?php
	}
	if($GLOBALS['sys_use_project_database']) { ?>
		<li><a href="database.php"><?php echo $Language->getText('admin_index','project_database_administration'); ?></a></li>
	<?php } ?>
</ul>
<?php }

site_admin_footer(array());

?>

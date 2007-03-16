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


require_once('../env.inc.php');
require_once('pre.php');
require_once('www/admin/admin_utils.php');

site_admin_header(array('title'=>_('Site Admin')));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

?>

<p><strong><?php echo _('User Maintenance'); ?></strong></p>
	<ul>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM users WHERE status='A'");
		$row = db_fetch_array($res);
		printf(_('Active site users: <strong>%1$s</strong>'), $row['count']);
	?></li>
</ul>
<ul>
	<li><a href="userlist.php"><?php echo _('Display Full User List/Edit Users'); ?></a>&nbsp;&nbsp;</li>
	<li><?php
	echo _('Display Users Beginning with :').' ';
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
		<input type="submit" value="<?php echo _('get'); ?>" />
		</form>
	</li>
    <li><a href="../account/register.php"><?php
    echo _('Register a New User');
    ?></a>
    </li>
</ul>
<p>
<strong><?php echo _('Group Maintenance'); ?></strong>
</p>
<ul>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM groups");
		$row = db_fetch_array($res);
		printf(_('Registered projects: <strong>%1$s</strong>'), $row['count']);
	?></li>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
		$row = db_fetch_array($res);
		printf(_('Active projects: <strong>%1$s</strong>'), $row['count']);
	?></li>
	<li><?php
		$res=db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
		$row = db_fetch_array($res);
		printf(_('Pending projects: <strong>%1$s</strong>'), $row['count']);
	?></li>
</ul>
<ul>
	<li><a href="grouplist.php"><?php echo _('Display Full Group List/Edit Groups'); ?></a></li>

	<li>
	<?php
	echo _('Display Groups Beginning with :').' ';
	for ($i=0; $i < count($abc_array); $i++) {
		echo '<a href="grouplist.php?group_name_search='.$abc_array[$i].'">'.$abc_array[$i].'</a>|';
	}
?>
	<br />
		<form name="gpsrch" action="search.php" method="post">
		<?php echo _('Search <em>(groupid, group unix name, full name)</em>'); ?>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="groupsearch" value="1" />
		<input type="submit" value="<?php echo _('get'); ?>" />
		</form>
	</li>
</ul>
<ul>
	<li><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/register/"><?php echo _('Register New Project'); ?></a></li>
	<li><?php echo _('Groups with status'); ?> <a href="approve-pending.php"><?php echo _('Pending (P)'); ?> <em><?php echo _('(New Project Approval)'); ?></em></a></li>
	<li><form name="projectsearch" action="search.php">
	<?php echo _('Groups with status'); ?>
	<select name="status">
			<option value="A"><?php echo _('Active (A)'); ?></option>
			<option value="H"><?php echo _('Hold (H)'); ?></option>
			<option value="P"><?php echo _('Pending (P)'); ?></option>
	</select>
	<input type="hidden" name="groupsearch" value="1"/>
	<input type="hidden" name="search" value="%"/>
	<input type="submit" value="<?php echo _('Submit');?> "/>
	</form></li>
	<li><a href="search.php?groupsearch=1&amp;search=%&amp;is_public=0"><?php echo _('Private Groups'); ?></a></li>
</ul>

<p>
<strong><?php echo _('News'); ?></strong>
</p>
<ul>
	<li><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/news/admin/"><?php echo _('Approve/Reject'); ?></a> <?php echo _('Front-page news'); ?></li>
</ul>

<p>
<strong><?php echo _('Stats'); ?></strong>
</p>
<ul>
	<li><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/stats/"><?php echo _('Site-Wide Stats'); ?></a></li>
</ul>

<p>
<strong><?php echo _('Trove Project Tree'); ?></strong>
</p>
<ul>
	<li><a href="trove/trove_cat_list.php"><?php echo _('Display Trove Map'); ?></a></li>
	<li><a href="trove/trove_cat_add.php"><?php echo _('Add to the Trove Map'); ?></a></li>
</ul>

<p><strong><?php echo _('Site Utilities'); ?></strong></p>
<ul>
	<li><a href="massmail.php"><?php printf(_('Mail Engine for %1$s Subscribers'), $GLOBALS['sys_name']); ?></a></li>
	<li><a href="unsubscribe.php"><?php echo $GLOBALS['sys_name']; ?> <?php echo _('Site Mailings Maintenance'); ?></a></li>
	<li><a href="edit_supported_languages.php"><?php echo _('Add, Delete, or Edit Supported Languages'); ?></a></li>
	<li><a href="edit_frs_filetype.php"><?php echo _('Add, Delete, or Edit File Types'); ?></a></li>
	<li><a href="edit_frs_processor.php"><?php echo _('Add, Delete, or Edit Processors'); ?></a></li>
	<li><a href="edit_theme.php"><?php echo _('Add, Delete, or Edit Themes'); ?></a></li>
	<li><a href="edit_licenses.php"><?php echo _('Add, Delete, or Edit Licenses'); ?></a></li>
	<li><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/admin/languages/loadtabfiles.php"><?php echo _('Translation file tool'); ?></a></li>
	<li><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/stats/lastlogins.php"><?php echo _('Recent logins'); ?></a></li>
	<li><a href="cronman.php"><?php echo _('Cron Manager'); ?></a></li>
	<li><a href="pluginman.php"><?php echo _('Plugin Manager'); ?></a></li>
	<li><a href="configman.php"><?php echo _('Config Manager'); ?></a></li>
	
	<?php 
	plugin_hook("site_admin_option_hook", false); ?>
</ul>

<?php if($GLOBALS['sys_use_project_database'] || $GLOBALS['sys_use_project_vhost']) { ?>
<ul>
	<?php if($GLOBALS['sys_use_project_vhost']) { ?>
		<li><a href="vhost.php"><?php echo _('Virtual Host Admin Tool'); ?></a></li>
	<?php
	}
	if($GLOBALS['sys_use_project_database']) { ?>
		<li><a href="database.php"><?php echo _('Project Database Administration'); ?></a></li>
	<?php } ?>
</ul>
<?php }

site_admin_footer(array());

?>

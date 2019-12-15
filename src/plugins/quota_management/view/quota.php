<?php
/**
 * Project Admin page to manage quotas disk and database
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011,2016, Franck Villaume - Capgemini
 * Copyright 2019, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

session_require_global_perm('forge_admin');

global $HTML;

$quota_management = plugin_get_object('quota_management');

$_quota_block_size = intval(trim(shell_exec('echo $BLOCK_SIZE'))) + 0;
if ($_quota_block_size == 0) $_quota_block_size = 1024;

$subMenuTitle = array();
$subMenuUrl = array();
$subMenuAttr = array();
$subMenuTitle[] = _('Ressources usage and quota');
$subMenuUrl[] = '/plugins/'.$quota_management->name.'/?type=globaladmin';
$subMenuAttr[] = array('title' => _('View quota and usage per project and user.'));
$subMenuTitle[] = _('Admin');
$subMenuUrl[] = '/plugins/'.$quota_management->name.'/?type=globaladmin&view=admin';
$subMenuAttr[] = array('title' => _('Administrate quotas per project.'));
echo $HTML->subMenu($subMenuTitle, $subMenuUrl, $subMenuAttr);

// stock projects infos in array
$quotas = array();

// all projects list
$res_db = db_query_params('SELECT plugin_quota_management.*, groups.group_name, groups.unix_group_name FROM plugin_quota_management, groups
			WHERE plugin_quota_management.group_id = groups.group_id ORDER BY group_id',
			array());
if (db_numrows($res_db) > 0) {
	while($e = db_fetch_array($res_db)) {
		$quotas["$e[group_id]"]["group_id"] = $e["group_id"];
		$quotas["$e[group_id]"]["name"] = $e["group_name"];
		$quotas["$e[group_id]"]["unix_name"] = $e["unix_group_name"];
		$quotas["$e[group_id]"]["database_size"] = 0;
		$quotas["$e[group_id]"]["disk_size_1"] = 0;
		$quotas["$e[group_id]"]["disk_size_scm"] = 0;
		$quotas["$e[group_id]"]["quota_hard"] = $e["quota_hard"] * $_quota_block_size;
		$quotas["$e[group_id]"]["quota_soft"] = $e["quota_soft"] * $_quota_block_size;
		$quotas["$e[group_id]"]["quota_db_hard"] = $e["quota_db_hard"] * $_quota_block_size;
		$quotas["$e[group_id]"]["quota_db_soft"] = $e["quota_db_soft"] * $_quota_block_size;
	}
}

// documents database size
if (forge_get_config('use_docman')) {
	$res_db = $quota_management->getDocumentsSizeQuery();
	if (db_numrows($res_db) > 0) {
		while($e = db_fetch_array($res_db)) {
			$q = array();
			$quotas["$e[group_id]"]["database_size"] += $e["size"];
			$quotas["$e[group_id]"]["database_size"] += $e["size1"];
		}
	}
}

// trackers database size
if (forge_get_config('use_tracker')) {
	$res_db = $quota_management->getTrackersSizeQuery();
	if (db_numrows($res_db) > 0) {
		while($e = db_fetch_array($res_db)) {
			$q = array();
			$quotas["$e[group_id]"]["database_size"] += $e["size"];
		}
	}
}

// FRS database size
if (forge_get_config('use_frs')) {
	$res_db = $quota_management->getFRSSizeQuery();
	if (db_numrows($res_db) > 0) {
		while($e = db_fetch_array($res_db)) {
			$q = array();
			$quotas["$e[group_id]"]["database_size"] += $e["size"];
		}
	}
}

// PM database size
if (forge_get_config('use_pm')) {
	$res_db = $quota_management->getPMSizeQuery();
	if (db_numrows($res_db) > 0) {
		while($e = db_fetch_array($res_db)) {
			$q = array();
			$quotas["$e[group_id]"]["database_size"] += $e["size"];
		}
	}
}

// news database size
if (forge_get_config('use_news')) {
	$res_db = $quota_management->getNewsSizeQuery();
	if (db_numrows($res_db) > 0) {
		while($e = db_fetch_array($res_db)) {
			$quotas["$e[group_id]"]["database_size"] += $e["size"];
		}
	}
}

// forums database size
if (forge_get_config('use_forums')) {
	$res_db = $quota_management->getForumSizeQuery();
	if (db_numrows($res_db) > 0) {
		while($e = db_fetch_array($res_db)) {
			$quotas["$e[group_id]"]["database_size"] += $e["size"];
		}
	}
}

// disk space size
foreach ($quotas as $p) {
	$group_id = $p["group_id"];
	if (forge_get_config('use_ftp')) {
		// ftp dir disk space
		$size = $quota_management->getFTPSize($group_id);
		$quotas["$group_id"]["disk_size_1"] += $size;
	}
	if (forge_get_config('use_shell')) {
		// home dir disk space
		$size = $quota_management->getHomeSize($group_id);
		$quotas["$group_id"]["disk_size_1"] += $size;
	}
}

// users disk space size
$ftp_dir = forge_get_config('homedir_prefix');
$users = array();
$res_db = db_query_params('SELECT user_id, user_name, realname, unix_status FROM users ORDER BY user_id ',
			array());
if (db_numrows($res_db) > 0) {
	while($e = db_fetch_array($res_db)) {
		if ($e["unix_status"] != "N") {
			$users["$e[user_id]"]["user_id"] = $e["user_id"];
			$users["$e[user_id]"]["user_name"] = "$e[user_name]";
			$users["$e[user_id]"]["realname"] = "$e[realname]";
			$users["$e[user_id]"]["unix_status"] = "$e[unix_status]";
			$users["$e[user_id]"]["disk_size"] = 0;
		}
	}
}
foreach ($users as $u) {
	$user_id = $u["user_id"];
	$dir = $ftp_dir.'/'.$u["user_name"];
	$size = $quota_management->get_dir_size($dir);
	$users["$user_id"]["disk_size"] += human_readable_bytes($size);
}

?>
<table width="900" cellpadding="2" cellspacing="0" border="0">
	<tr style="">
		<td style="border-top:thick solid #808080;font-weight:bold" colspan="3">
			<?php echo _('Projects ressources use'); ?>
		</td>
		<td style="border-top:thick solid #808080" colspan="9">
			<span style="font-size:10px">
				(&nbsp;
				<?php echo _('project'); ?>* :
				<?php echo 'FTP, ' . _('Home'); ?>
				&nbsp;)
			</span>
		</td>
	</tr>
	<tr>
		<td style="border-top:thin solid #808080">
			<?php echo _('id'); ?>
		</td>
		<td style="border-top:thin solid #808080">
			<?php echo _('name'); ?>
		</td>
		<td style="border-top:thin solid #808080"><br /></td>
		<td style="border-top:thin solid #808080;background:#e0e0e0; text-align: right">
			<?php echo _('database'); ?>
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0; text-align: right">
			<?php echo _('project'); ?>*
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0; text-align: right">
			<?php echo _('SCM'); ?>
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0; text-align: right">
			<?php echo _('total'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('database quota soft'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('database quota hard'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('disk quota soft'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('disk quota hard'); ?>
		</td>
	</tr>
	<?php
	$total_database = 0;
	$total_disk = 0;
	$total_disk_1 = 0;
	$total_disk_scm = 0;
	foreach ($quotas as $q) {
		$total_database += $q["database_size"];
		$total_disk_1 += $q["disk_size_1"];
		$total_disk_scm += $q["disk_size_scm"];
		$total_disk += $q["disk_size_1"]+$q["disk_size_scm"];
		$local_disk_size = $q["database_size"]+$q["disk_size_1"]+$q["disk_size_scm"];
		$color1 = "#e0e0e0";
		$color2 = "#ffffff";
		$color0 = $color1;
		$colorq = $color2;
		$colorqb = $color2;
		if ($q["quota_soft"] > 0) {
			$color0 = "#E5ECB1";
			$colorq = $color0;
		}
		if ($q["quota_db_soft"] > 0) {
			$color0 = "#f0f0f0";
			$colorqb = $color0;
		}
		if (($q["disk_size_1"] > $q["quota_soft"] || $q["disk_size_scm"] > $q["quota_soft"]) && $q["quota_soft"] > 0) {
			$color1 = "#FF9898";
			$color2 = "#FFDCDC";
			$color0 = $color1;
			$colorq = $color2;
		}
		?>
		<tr>
			<td style="border-top:thin solid #808080;background:<?php echo $color2; ?>"><?php echo $q["group_id"]; ?></td>
			<td style="border-top:thin solid #808080;background:<?php echo $color2; ?>">
				<?php echo util_make_link('/plugins/'.$quota_management->name.'/?type=projectadmin&group_id='.$q['group_id'], $q['unix_name']); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color2; ?>">
				<?php echo $q["name"]; ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color1; ?>; text-align: right">
				<?php echo human_readable_bytes($q["database_size"]); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color0; ?>; text-align: right">
				<?php echo human_readable_bytes($q["disk_size_1"]); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color0; ?>; text-align: right">
				<?php echo human_readable_bytes($q["disk_size_scm"]); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color1; ?>;font-weight:bold; text-align: right">
				<?php echo human_readable_bytes($local_disk_size); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $colorqb; ?>; text-align: right">
				<?php
					if ($q["quota_db_soft"] > 0) 					{
						echo human_readable_bytes($q["quota_db_soft"]);
					} else {
						echo "---";
					}
				?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $colorqb; ?>; text-align: right">
				<?php
					if ($q["quota_db_hard"] > 0) {
						echo human_readable_bytes($q["quota_db_hard"]);
					} else {
						echo "---";
					}
				?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $colorq; ?>; text-align: right">
				<?php
					if ($q["quota_soft"] > 0) {
						echo human_readable_bytes($q["quota_soft"]);
					} else {
						echo "---";
					}
				?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $colorq; ?>; text-align: right">
				<?php
					if ($q["quota_hard"] > 0) {
						echo human_readable_bytes($q["quota_hard"]);
					} else {
						echo "---";
					}
				?>
			</td>
		</tr>
		<?php
	}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">
			<?php echo _('total'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0; text-align: right">
			<?php echo human_readable_bytes($total_database); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0; text-align: right">
			<?php echo human_readable_bytes($total_disk_1); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0; text-align: right">
			<?php echo human_readable_bytes($total_disk_scm); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0; text-align: right">
			<?php echo human_readable_bytes($total_database+$total_disk_1+$total_disk_scm); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
	</tr>
</table>
<br />
<br />
<table width="700" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080" colspan="6">
			<?php echo _('Users disk use'); ?>
		</td>
	</tr>
	<tr>
		<td style="border-top:thin solid #808080"><?php echo _('id'); ?></td>
		<td style="border-top:thin solid #808080"><?php echo _('name'); ?></td>
		<td style="border-top:thin solid #808080"><br /></td>
		<td style="border-top:thin solid #808080"><br /></td>
		<td style="border-top:thin solid #808080"><br /></td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('disk'); ?>
		</td>
	</tr>
	<?php
	$total = 0;
	foreach ($users as $u) {
		$total += $u["disk_size"];
		?>
		<tr>
			<td style="border-top:thin solid #808080"><?php echo $u["user_id"]; ?></td>
			<td style="border-top:thin solid #808080"><?php echo $u["user_name"]; ?></td>
			<td style="border-top:thin solid #808080"><?php echo $u["realname"]; ?></td>
			<td style="border-top:thin solid #808080"><br /></td>
			<td style="border-top:thin solid #808080"><br /></td>
			<td style="border-top:thin solid #808080; text-align: right">
				<?php echo $u["disk_size"]; ?>
			</td>
		</tr>
		<?php
	}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">
			<?php echo _('total'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080; text-align: right">
			<?php echo $total; ?>
		</td>
	</tr>
</table>
<?php

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

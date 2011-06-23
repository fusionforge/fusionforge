<?php
/**
 * Project Admin page to manage quotas disk and database
 *
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011, Franck Villaume - Capgemini
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

$_quota_block_size = 1024;
$_quota_block_size = trim(shell_exec("echo $BLOCK_SIZE")) + 0;
if ($_quota_block_size == 0) $_quota_block_size = 1024;

site_admin_header(array('title'=>_('Site admin')));
?>
<h4>
	<?php echo _('Ressources usage and quota'); ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="quota_admin.php">Admin</a>
</h4>
<?php
// stock projects infos in array
$quotas = array();

// all projects list
$res_db = db_query_params ('SELECT group_id, group_name, unix_group_name, quota_soft, quota_hard FROM groups ORDER BY group_id ',
			array ());
if (db_numrows($res_db) > 0)
{
	while($e = db_fetch_array($res_db))
	{
		$quotas["$e[group_id]"]["group_id"] = $e["group_id"];
		$quotas["$e[group_id]"]["name"] = $e["group_name"];
		$quotas["$e[group_id]"]["unix_name"] = $e["unix_group_name"];
		$quotas["$e[group_id]"]["database_size"] = 0;
		$quotas["$e[group_id]"]["disk_size_other"] = 0;
		$quotas["$e[group_id]"]["disk_size_1"] = 0;
		$quotas["$e[group_id]"]["disk_size_scm"] = 0;
		$quotas["$e[group_id]"]["quota_hard"] = $e["quota_hard"] * $_quota_block_size;
		$quotas["$e[group_id]"]["quota_soft"] = $e["quota_soft"] * $_quota_block_size;
	}
}

// documents database size
$res_db = db_query_params ('SELECT group_id, SUM(octet_length(data)) as size, SUM(octet_length(data_words)) as size1 FROM doc_data GROUP BY group_id ',
			array ());
if (db_numrows($res_db) > 0)
{
	while($e = db_fetch_array($res_db))
	{
		$q = array();
		print_debug("doc grp=$e[group_id] size=$e[size]");
		$quotas["$e[group_id]"]["database_size"] += $e["size"];
		$quotas["$e[group_id]"]["database_size"] += $e["size1"];
	}
}

// news database size
$res_db = db_query_params ('SELECT group_id, SUM(octet_length(summary) + octet_length(details)) as size FROM news_bytes GROUP BY group_id',
			array ());
if (db_numrows($res_db) > 0)
{
	while($e = db_fetch_array($res_db))
	{
		print_debug("news grp=$e[group_id] size=$e[size]");
		$quotas["$e[group_id]"]["database_size"] += $e["size"];
	}
}

// forums database size
$res_db = db_query_params ('SELECT forum_group_list.group_id as group_id, SUM(octet_length(subject)+octet_length(body)) as size FROM forum INNER JOIN forum_group_list ON forum.group_forum_id = forum_group_list.group_forum_id GROUP BY group_id',
			array ());
if (db_numrows($res_db) > 0)
{
	while($e = db_fetch_array($res_db))
	{
		print_debug("forum grp=$e[group_id] size=$e[size]");
		$quotas["$e[group_id]"]["database_size"] += convert_bytes_to_mega($e["size"]);
	}
}

// disk space size
$chroot_dir = forge_get_config('chroot');
$ftp_dir = forge_get_config('ftp_upload_dir')."/pub/";
$upload_dir = forge_get_config('upload_dir');
$group_dir = $chroot_dir.forge_get_config('groupdir_prefix')."/";
$cvs_dir = $chroot_dir.$cvsdir_prefix."/";
$svn_dir = $chroot_dir.$svndir_prefix."/";


foreach ($quotas as $p)
{
	$group_id = $p["group_id"];
	// upload dir disk space
	$dir = $upload_dir .  $p["unix_name"];
	$size = get_dir_size($dir);
	$quotas["$group_id"]["disk_size_other"] += $size;
	print_debug("upload=$dir size=$size");
	// ftp dir disk space
	$dir = $ftp_dir .  $p["unix_name"];
	$size = get_dir_size($dir);
	$quotas["$group_id"]["disk_size_1"] += $size;
	print_debug("ftp=$dir size=$size");
	// home dir disk space
	$dir = $group_dir .  $p["unix_name"];
	$size = get_dir_size($dir);
	$quotas["$group_id"]["disk_size_1"] += $size;
	print_debug("home=$dir size=$size");
	// cvs dir disk space
	$dir = $cvs_dir .  $p["unix_name"];
	$size = get_dir_size($dir);
	$quotas["$group_id"]["disk_size_scm"] += $size;
	print_debug("cvs=$dir size=$size");
	// svn dir disk space
	$dir = $svn_dir .  $p["unix_name"];
	$size = get_dir_size($dir);
	$quotas["$group_id"]["disk_size_scm"] += $size;
	print_debug("svn=$dir size=$size");
}

// users disk space size
$chroot_dir = forge_get_config('chroot');
$ftp_dir = $chroot_dir."/home/users/";
$users = array();
$res_db = db_query_params ('SELECT user_id, user_name, realname, unix_status FROM users ORDER BY user_id ',
			array ());
if (db_numrows($res_db) > 0)
{
	while($e = db_fetch_array($res_db))
	{
		print_debug("users name=$e[user_name] status=$e[unix_status]");
		if ($e["unix_status"] != "N")
		{
			$users["$e[user_id]"]["user_id"] = $e["user_id"];
			$users["$e[user_id]"]["user_name"] = "$e[user_name]";
			$users["$e[user_id]"]["realname"] = "$e[realname]";
			$users["$e[user_id]"]["unix_status"] = "$e[unix_status]";
			$users["$e[user_id]"]["disk_size"] = 0;
		}
	}
}
foreach ($users as $u)
{
	print_debug("---------------------------------------");
	$user_id = $u["user_id"];
	$dir = $ftp_dir .  $u["user_name"];
	$size = get_dir_size($dir);
	$users["$user_id"]["disk_size"] += convert_bytes_to_mega($size);
	print_debug("$user_id user=$u[user_name] dir=$dir size=$size");
}

?>
<table width="800px" cellpadding="2" cellspacing="0" border="0">
	<tr style="">
		<td style="border-top:thick solid #808080;font-weight:bold" colspan="3">
			<?php echo _('Projects ressources use'); ?>
		</td>
		<td style="border-top:thick solid #808080" colspan="7">
			<span style="font-size:10px">
				(&nbsp;
				<?php echo _('project'); ?>* :
				<?php echo _('Ftp, Home'); ?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php echo _('scm'); ?>* :
				<?php echo _('Cvs, Svn'); ?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php echo _('others'); ?>* :
				<?php echo _('Download - without quota control'); ?>
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
		<td style="border-top:thin solid #808080;background:#e0e0e0" align="right">
			<?php echo _('database'); ?>
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0" align="right">
			<?php echo _('project'); ?>*
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0" align="right">
			<?php echo _('scm'); ?>*
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0" align="right">
			<?php echo _('others'); ?>*
		</td>
		<td style="border-top:thin solid #808080;background:#e0e0e0" align="right">
			<?php echo _('total'); ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo _('disk quota soft'); ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo _('disk quota hard'); ?>
		</td>
	</tr>
	<?php
	$total_database = 0;
	$total_disk = 0;
	foreach ($quotas as $q)
	{
		$total_database += $q["database_size"];
		$total_disk_1 += $q["disk_size_1"];
		$total_disk_other += $q["disk_size_other"];
		$total_disk_scm += $q["disk_size_scm"];
		$total_disk += $q["disk_size_1"]+$q["disk_size_scm"]+$q["disk_size_other"];
		$local_disk_size = $q["database_size"]+$q["disk_size_1"]+$q["disk_size_scm"]+$q["disk_size_other"];
		$color1 = "#e0e0e0";
		$color2 = "#ffffff";
		$color0 = $color1;
		$colorq = $color2;
		if ($q["quota_soft"] > 0)
		{
			$color0 = "#E5ECB1";
			$colorq = $color0;
		}
		// echo "size $q[disk_size] quota $q[quota_soft] <br />";
		if (($q["disk_size_1"] > $q["quota_soft"] || $q["disk_size_scm"] > $q["quota_soft"]) && $q["quota_soft"] > 0)
		{
			$color1 = "#FF9898";
			$color2 = "#FFDCDC";
			$color0 = $color1;
			$colorq = $color2;
		}
		?>
		<tr>
			<td style="border-top:thin solid #808080;background:<?php echo $color2; ?>"><?php echo $q["group_id"]; ?></td>
			<td style="border-top:thin solid #808080;background:<?php echo $color2; ?>">
				<a href="quota_project.php?group_id=<?php echo $q["group_id"]; ?>">
					<?php echo $q["unix_name"]; ?>
				</a>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color2; ?>">
				<?php echo $q["name"]; ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color1; ?>" align="right">
				<?php echo add_numbers_separator(convert_bytes_to_mega($q["database_size"])); ?>
				<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color0; ?>" align="right">
				<?php echo add_numbers_separator(convert_bytes_to_mega($q["disk_size_1"])); ?>
				<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color0; ?>" align="right">
				<?php echo add_numbers_separator(convert_bytes_to_mega($q["disk_size_scm"])); ?>
				<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color1; ?>" align="right">
				<?php echo add_numbers_separator(convert_bytes_to_mega($q["disk_size_other"])); ?>
				<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $color1; ?>;font-weight:bold" align="right">
				<?php echo add_numbers_separator(convert_bytes_to_mega($local_disk_size)); ?>
				<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $colorq; ?>" align="right">
				<?php
					if ($q["quota_soft"] > 0)
					{
						echo add_numbers_separator(convert_bytes_to_mega($q["quota_soft"]));
						echo " ";
						echo _('Mb');
					}
					else
					{
						echo "---";
					}
				?>
			</td>
			<td style="border-top:thin solid #808080;background:<?php echo $colorq; ?>" align="right">
				<?php
					if ($q["quota_hard"] > 0)
					{
						echo add_numbers_separator(convert_bytes_to_mega($q["quota_hard"]));
						echo " ";
						echo _('Mb');
					}
					else
					{
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
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($total_database)); ?>
			<?php echo _('Mb'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($total_disk_1)); ?>
			<?php echo _('Mb'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($total_disk_scm)); ?>
			<?php echo _('Mb'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($total_disk_other)); ?>
			<?php echo _('Mb'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080;background:#e0e0e0" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($total_database+$total_disk_1+$total_disk_scm+$total_disk_other)); ?>
			<?php echo _('Mb'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
	</tr>
</table>
<br />
<br />
<table width="700px" cellpadding="2" cellspacing="0" border="0">
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
		<td style="border-top:thin solid #808080" align="right">
			<?php echo _('disk'); ?>
		</td>
	</tr>
	<?php
	$total = 0;
	foreach ($users as $u)
	{
		$total += $u["disk_size"];
		?>
		<tr>
			<td style="border-top:thin solid #808080"><?php echo $u["user_id"]; ?></td>
			<td style="border-top:thin solid #808080"><?php echo $u["user_name"]; ?></td>
			<td style="border-top:thin solid #808080"><?php echo $u["realname"]; ?></td>
			<td style="border-top:thin solid #808080"><br /></td>
			<td style="border-top:thin solid #808080"><br /></td>
			<td style="border-top:thin solid #808080" align="right">
				<?php echo add_numbers_separator($u["disk_size"]); ?>
				<?php echo _('Mb'); ?>
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
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080" align="right">
			<?php echo add_numbers_separator($total); ?>
			<?php echo _('Mb'); ?>
		</td>
	</tr>
</table>
<?php


print_debug(print_r($quotas, true));
print_debug(print_r($users, true));

site_admin_footer(array());
?>

<?php

function convert_bytes_to_mega ($mega)
{
	$b = round($mega / (1024*1024), 0);
	return $b;
}

function print_debug ($text)
{
//	echo "<pre>$text</pre>";
}
function add_numbers_separator ($val, $sep=' ')
{
	$size = "$val";
	$size = strrev($size);
	$size = wordwrap($size, 3, $sep, 1);
	$size = strrev($size);
	return $size;
}
function get_dir_size ($dir)
{
	$size = "";
	$cmd = "/usr/bin/du -bs $dir";
	$res = shell_exec ($cmd);
	$a = explode("\t", $res);
	if (isset($a[1])) $size = $a[0];
	return "$size";
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

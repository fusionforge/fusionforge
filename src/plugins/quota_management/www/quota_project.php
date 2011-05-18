<?php
/**
 * Project Admin page to manage quotas project
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team 
 * http://fusionforge.org/
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


require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

if (!forge_get_config('use_project_vhost')) {
	exit_disabled('home');
}

session_require_perm ('project_admin', $group_id) ;

$group = &group_get_object($group_id);

if (!$group || !is_object($group)) {
        exit_no_group();
} else if ($group->isError()) {
        exit_error($group->getErrorMessage(),'home');
}


project_admin_header(array('title'=>_('Project quota manager'),'group'=>$group->getID(),'pagename'=>'project_admin_quotas','sectionvals'=>array(group_getname($group_id))));
?>

<h4><?php echo _('Project quota manager'); ?></h4>

<?php
$quotas = array();
$res_db = db_query_params ('SELECT SUM(octet_length(data)) as size, SUM(octet_length(data_words)) as size1, count(*) as nb FROM doc_data WHERE group_id = $1 ',
			array ($group_id));
$q = array();
$q["name"] = _('Documents');
$q["nb"] = 0; $q["size"] = 0;
$q1 = array();
$q1["name"] = _('Documents search engine');
$q["size"] = 0;
if (db_numrows($res_db) > 0) 
{
	$e = db_fetch_array($res_db);
	$q["nb"] = $e["nb"];
	$q["size"] = $e["size"];
	// $q1["nb"] = $e["nb"];
	$q1["size"] = $e["size1"];
}
$quotas[0] = $q;
$quotas[1] = $q1;

$res_db = db_query_params ('SELECT SUM(octet_length(summary) + octet_length(details)) as size, count(*) as nb FROM news_bytes WHERE group_id = $1 ',
			array ($group_id));
$q = array();
$q["name"] = _('News');
$q["nb"] = 0; $q["size"] = 0;
if (db_numrows($res_db) > 0) 
{
	$e = db_fetch_array($res_db);
	$q["nb"] = $e["nb"];
	$q["size"] = $e["size"];
}
$quotas[2] = $q;


$res_db = db_query_params ('SELECT SUM(octet_length(subject)+octet_length(body)) as size, count(*) as nb FROM forum INNER JOIN forum_group_list ON forum.group_forum_id = forum_group_list.group_forum_id WHERE group_id = $1 ',
			array ($group_id));
$q = array();
$q["name"] = _('Forums');
$q["nb"] = 0; $q["size"] = 0;
if (db_numrows($res_db) > 0) 
{
	$e = db_fetch_array($res_db);
	$q["nb"] = $e["nb"];
	$q["size"] = $e["size"];
}
$quotas[3] = $q;

$quotas_disk = array();

// espace disque
// disk_total_space
$_quota_block_size = 1024;
$_quota_block_size = trim(shell_exec("echo $BLOCK_SIZE")) + 0;
if ($_quota_block_size == 0) $_quota_block_size = 1024;
$quota_soft = "";
$quota_hard = "";
$res_db = db_query_params ('SELECT quota_soft, quota_hard FROM groups WHERE group_id = $1',
			array ($group_id));
if (db_numrows($res_db) > 0) 
{
	$e = db_fetch_array($res_db);
	$quota_hard = $e["quota_hard"];
	$quota_soft = $e["quota_soft"];
	$quota_hard = round(($_quota_block_size * $quota_hard) / (1024*1024), 0);
	$quota_soft = round(($_quota_block_size * $quota_soft) / (1024*1024), 0);
}

$quota_tot_other = 0;
$quota_tot_1 = 0;
$quota_tot_scm = 0;

$upload_dir = forge_get_config('upload_dir') .  $group->getUnixName();
$chroot_dir = forge_get_config('chroot');
$ftp_dir = forge_get_config('ftp_upload_dir')."/pub/".$group->getUnixName();
$group_dir = $chroot_dir.forge_get_config('groupdir_prefix') . "/" . $group->getUnixName();
$cvs_dir = $chroot_dir.$cvsdir_prefix . "/" . $group->getUnixName();
$svn_dir = $chroot_dir.$svndir_prefix . "/" . $group->getUnixName();

$q["name"] = _('Download project directory');
$q["path"] = "$upload_dir";
$q["quota_label"] = _('Without quota control');
$q["size"] = get_dir_size ("$upload_dir");
$quota_tot_other += $q["size"];
$quotas_disk[] = $q;

$q["name"] = _('Home project directory');
$q["path"] = "$group_dir"; $q["size"] = get_dir_size ("$group_dir");
$q["quota_label"] = _('With ftp and home quota control');
$quota_tot_1 += $q["size"];
$quotas_disk[] = $q;

$q["name"] = _('FTP project directory');
$q["path"] = "$ftp_dir"; $q["size"] = get_dir_size ("$ftp_dir");
$q["quota_label"] = _('With ftp and home quota control');
$quota_tot_1 += $q["size"];
$quotas_disk[] = $q;

$q["name"] = _('CVS project directory');
$q["path"] = "$cvs_dir"; $q["size"] = get_dir_size ("$cvs_dir");
$q["quota_label"] = _('With cvs and svn quota control');
$quota_tot_scm += $q["size"];
$quotas_disk[] = $q;

$q["name"] = _('Subversion project directory');
$q["path"] = "$svn_dir"; $q["size"] = get_dir_size ("$svn_dir");
$q["quota_label"] = _('With cvs and svn quota control');
$quota_tot_scm += $q["size"];
$quotas_disk[] = $q;

//echo "chroot = $chroot_dir <br />";
//echo "ftp = $ftp_dir <br />";
// echo "group = $group_dir <br />";
// echo "svn = $svn_dir <br />";
// echo "cvs = $cvs_dir <br />";





// print_debug(print_r($quotas, true));
?>

<table width="500px" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="3" style="border-top:thick solid #808080" align="center"><?php echo _('Database'); ?></td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080"><?php echo _('quota type'); ?></td>
		<td style="border-top:thin solid #808080" align="right"><?php echo _('quantity'); ?></td>
		<td style="border-top:thin solid #808080" align="right"><?php echo _('size'); ?></td>
	</tr>
<?php 
	$sizetot = 0;
foreach ($quotas as $q) 
{ 
	if ($q["size"] != "")
	{
		$sizetot += $q["size"];
		?>
			<tr>
				<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
				<td style="border-top:thin solid #808080" align="right"><?php echo $q["nb"]; ?></td>
				<td style="border-top:thin solid #808080" align="right"><?php echo add_numbers_separator(convert_bytes_to_mega($q["size"]))." "._('Mb'); ?></td>
			</tr>
<?php 
	} 
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">
			<?php echo _('Total'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">&nbsp;</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($sizetot))." "._('Mb'); ?>
		</td>
	</tr>
</table>
<br />
<br />
<table width="500px" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="3" style="border-top:thick solid #808080" align="center">
			<?php echo _('Disk space'); ?>
		</td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo _('quota type'); ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">&nbsp;</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo _('size'); ?>
		</td>
	</tr>
<?php 
	$sizetot = 0;
foreach ($quotas_disk as $q) 
{ 
	if ($q["size"] != "")
	{
		$sizetot += $q["size"];
?>
	<tr>
		<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo $q["quota_label"]; ?>&nbsp;
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($q["size"]))." "._('Mb'); ?>
		</td>
	</tr>
<?php 
	} 
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><?php echo _('Total'); ?></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">&nbsp;</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($sizetot))." "._('Mb'); ?>
		</td>
	</tr>
</table>
<br />
<br />


<?php
	
	$color1 = "#ffffff";
	$color2 = "#ffffff";
	$msg1 = "&nbsp;";
	$msg2 = "&nbsp;";
	$qs = $quota_soft * 1024 * 1024;
	if (($quota_tot_1+0) > ($qs+0) && ($qs+0) > 0)
	{
		$color1 = "#FFDCDC";
		$msg1 = _('Quota exceeded');
	}
	if (($quota_tot_scm+0) > ($qs+0) && ($qs+0) > 0)
	{
		$color2 = "#FFDCDC";
		$msg2 = _('Quota exceeded');
	}
?>

<table width="500px" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="4" style="border-top:thick solid #808080" align="center"><?php echo _('Quota disk management'); ?></td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo _('Quota settings'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold" align="right">
			&nbsp;
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo _('Quota soft'); ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo _('Quota hard'); ?>
		</td>
	</tr>
	<tr style="background:<?php echo $color1; ?>">
		<td style="border-top:thin solid #808080">
			<?php echo _('Home, Ftp'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold;color:red" align="right">
			<?php echo $msg1; ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php 
				if ($quota_soft == 0)
				{
					echo "---";
				}
				else
				{
					echo "$quota_soft";  
					echo _('Mb'); 
				}
			?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php 
				if ($quota_hard == 0)
				{
					echo "---";
				}
				else
				{
					echo "$quota_hard";  
					echo _('Mb'); 
				}
			?>
		</td>
	</tr>
	<tr style="background:<?php echo $color2; ?>">
		<td style="border-top:thin solid #808080">
			<?php echo _('Cvs, Svn'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold;color:red" align="right">
			<?php echo $msg2; ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php 
				if ($quota_soft == 0)
				{
					echo "---";
				}
				else
				{
					echo "$quota_soft";  
					echo _('Mb'); 
				}
			?>
			</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php 
				if ($quota_hard == 0)
				{
					echo "---";
				}
				else
				{
					echo "$quota_hard";  
					echo _('Mb'); 
				}
			?>
		</td>
	</tr>
	<tr style="font-weight:bold">
		<td colspan="4" style="border-top:thick solid #808080" align="center">&nbsp;</td>
	</tr>
</table>


<?php project_admin_footer(array()); ?>

<?php
function print_debug ($text)
{
	echo "<pre>$text</pre>";
}

function convert_bytes_to_mega ($mega)
{
	$b = round($mega / (1024*1024), 0);
	return $b;
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
?>

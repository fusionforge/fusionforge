<?php
/**
 * Project Admin page to manage quotas project
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id: 08_IMPROVQUOTA_90_quota_management.dpatch,v 1.1 2006/01/11 17:02:45 fabio Exp $
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
require_once('www/project/admin/project_admin_utils.php');

if (!$sys_use_project_vhost) {
	exit_disabled();
}

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group = &group_get_object($group_id);

if (!$group || !is_object($group)) {
        exit_error('Error','Error creating group object');
} else if ($group->isError()) {
        exit_error('ERROR',$group->getErrorMessage());
}


project_admin_header(array('title'=>$Language->getText('project_admin_quotas','title'),'group'=>$group->getID(),'pagename'=>'project_admin_quotas','sectionvals'=>array(group_getname($group_id))));
?>

<h4><?php echo $Language->getText('project_admin_quotas','title'); ?></h4>

<?php
$quotas = array();
$SQL = "SELECT SUM(octet_length(data)) as size, SUM(octet_length(data_words)) as size1, count(*) as nb ";
$SQL .= "FROM doc_data ";
$SQL .= "WHERE group_id = '$group_id' ";
$res_db = db_query($SQL);
$q = array();
$q["name"] = $Language->getText('project_admin_quotas','documents_title');
$q["nb"] = 0; $q["size"] = 0;
$q1 = array();
$q1["name"] = $Language->getText('project_admin_quotas','search_engine_title');
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

$SQL = "SELECT SUM(octet_length(summary) + octet_length(details)) as size, count(*) as nb FROM news_bytes WHERE group_id = '$group_id' ";
$res_db = db_query($SQL);
$q = array();
$q["name"] = $Language->getText('project_admin_quotas','news_title');
$q["nb"] = 0; $q["size"] = 0;
if (db_numrows($res_db) > 0) 
{
	$e = db_fetch_array($res_db);
	$q["nb"] = $e["nb"];
	$q["size"] = $e["size"];
}
$quotas[2] = $q;


$SQL = "SELECT SUM(octet_length(subject)+octet_length(body)) as size, count(*) as nb FROM forum INNER JOIN forum_group_list ";
$SQL .= "ON forum.group_forum_id = forum_group_list.group_forum_id WHERE group_id = '$group_id' ";
$res_db = db_query($SQL);
$q = array();
$q["name"] = $Language->getText('project_admin_quotas','forums_title');
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
$SQL = "SELECT quota_soft, quota_hard FROM groups WHERE group_id = $group_id";
$res_db = db_query($SQL);
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

$upload_dir = $sys_upload_dir .  $group->getUnixName();
$chroot_dir = $sys_chroot;
$ftp_dir = $sys_ftp_upload_dir."/pub/".$group->getUnixName();
$group_dir = $chroot_dir.$groupdir_prefix . "/" . $group->getUnixName();
$cvs_dir = $chroot_dir.$cvsdir_prefix . "/" . $group->getUnixName();
$svn_dir = $chroot_dir.$svndir_prefix . "/" . $group->getUnixName();

$q["name"] = $Language->getText('project_admin_quotas','upload_title');
$q["path"] = "$upload_dir";
$q["quota_label"] = $Language->getText('project_admin_quotas','without');
$q["size"] = get_dir_size ("$upload_dir");
$quota_tot_other += $q["size"];
$quotas_disk[] = $q;

$q["name"] = $Language->getText('project_admin_quotas','home_title');
$q["path"] = "$group_dir"; $q["size"] = get_dir_size ("$group_dir");
$q["quota_label"] = $Language->getText('project_admin_quotas','with_ftp_home');
$quota_tot_1 += $q["size"];
$quotas_disk[] = $q;

$q["name"] = $Language->getText('project_admin_quotas','ftp_title');
$q["path"] = "$ftp_dir"; $q["size"] = get_dir_size ("$ftp_dir");
$q["quota_label"] = $Language->getText('project_admin_quotas','with_ftp_home');
$quota_tot_1 += $q["size"];
$quotas_disk[] = $q;

$q["name"] = $Language->getText('project_admin_quotas','cvs_title');
$q["path"] = "$cvs_dir"; $q["size"] = get_dir_size ("$cvs_dir");
$q["quota_label"] = $Language->getText('project_admin_quotas','with_scm');
$quota_tot_scm += $q["size"];
$quotas_disk[] = $q;

$q["name"] = $Language->getText('project_admin_quotas','svn_title');
$q["path"] = "$svn_dir"; $q["size"] = get_dir_size ("$svn_dir");
$q["quota_label"] = $Language->getText('project_admin_quotas','with_scm');
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
		<td colspan="3" style="border-top:thick solid #808080" align="center"><?php echo $Language->getText('project_admin_quotas','database_space_label'); ?></td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080"><?php echo $Language->getText('project_admin_quotas','table_head_name'); ?></td>
		<td style="border-top:thin solid #808080" align="right"><?php echo $Language->getText('project_admin_quotas','table_head_nb'); ?></td>
		<td style="border-top:thin solid #808080" align="right"><?php echo $Language->getText('project_admin_quotas','table_head_size'); ?></td>
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
				<td style="border-top:thin solid #808080" align="right"><?php echo add_numbers_separator(convert_bytes_to_mega($q["size"]))." ".$Language->getText('project_admin_quotas','size_metric'); ?></td>
			</tr>
<?php 
	} 
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">
			<?php echo $Language->getText('project_admin_quotas','total_label'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">&nbsp;</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($sizetot))." ".$Language->getText('project_admin_quotas','size_metric'); ?>
		</td>
	</tr>
</table>
<br />
<br />
<table width="500px" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="3" style="border-top:thick solid #808080" align="center">
			<?php echo $Language->getText('project_admin_quotas','disk_space_label'); ?>
		</td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo $Language->getText('project_admin_quotas','table_head_name'); ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">&nbsp;</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo $Language->getText('project_admin_quotas','table_head_size'); ?>
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
			<?php echo add_numbers_separator(convert_bytes_to_mega($q["size"]))." ".$Language->getText('project_admin_quotas','size_metric'); ?>
		</td>
	</tr>
<?php 
	} 
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><?php echo $Language->getText('project_admin_quotas','total_label'); ?></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">&nbsp;</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080" align="right">
			<?php echo add_numbers_separator(convert_bytes_to_mega($sizetot))." ".$Language->getText('project_admin_quotas','size_metric'); ?>
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
		$msg1 = $Language->getText('project_admin_quotas','quota_exceed');
	}
	if (($quota_tot_scm+0) > ($qs+0) && ($qs+0) > 0)
	{
		$color2 = "#FFDCDC";
		$msg2 = $Language->getText('project_admin_quotas','quota_exceed');
	}
?>

<table width="500px" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="4" style="border-top:thick solid #808080" align="center"><?php echo $Language->getText('project_admin_quotas','quota_space_label'); ?></td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo $Language->getText('project_admin_quotas','quota_settings_label'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold" align="right">
			&nbsp;
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo $Language->getText('project_admin_quotas','quota_soft_label'); ?>
		</td>
		<td style="border-top:thin solid #808080" align="right">
			<?php echo $Language->getText('project_admin_quotas','quota_hard_label'); ?>
		</td>
	</tr>
	<tr style="background:<?php echo $color1; ?>">
		<td style="border-top:thin solid #808080">
			<?php echo $Language->getText('project_admin_quotas','quota_group1_label'); ?>
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
					echo $Language->getText('admin_quotas','mbytes'); 
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
					echo $Language->getText('admin_quotas','mbytes'); 
				}
			?>
		</td>
	</tr>
	<tr style="background:<?php echo $color2; ?>">
		<td style="border-top:thin solid #808080">
			<?php echo $Language->getText('project_admin_quotas','quota_groupscm_label'); ?>
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
					echo $Language->getText('admin_quotas','mbytes'); 
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
					echo $Language->getText('admin_quotas','mbytes'); 
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
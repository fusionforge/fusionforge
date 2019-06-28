<?php
/**
 * Project Admin page to manage quotas project
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2019, Franck Villaume - TrivialDev
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

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

global $group_id;
global $quota_management;

session_require_perm('project_admin', $group_id);

$group = group_get_object($group_id);

if (!$group || !is_object($group)) {
	exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(), 'home');
}

$quotas = array();
if ($group->usesDocman()) {
	$res_db = $quota_management->getDocumentsSizeForProject($group_id);
	$q = array();
	$q["name"] = _('Documents');
	$q["nb"] = 0; $q["size"] = 0;
	$q1 = array();
	$q1["name"] = _('Documents search engine');
	$q1["size"] = 0;
	if (db_numrows($res_db) > 0) {
		$e = db_fetch_array($res_db);
		$q["nb"] = $e["nb"];
		$q["size"] = $e["size"];
		$q1["nb"] = $e["nb"];
		$q1["size"] = $e["size1"];
	}
	$quotas[] = $q;
	$quotas[] = $q1;
}

if ($group->usesNews()) {
	$res_db = db_query_params('SELECT SUM(octet_length(summary) + octet_length(details)) as size, count(*) as nb FROM news_bytes WHERE group_id = $1 ',
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
	$quotas[] = $q;
}

if ($group->usesForum()) {
	$res_db = db_query_params('SELECT SUM(octet_length(subject)+octet_length(body)) as size, count(*) as nb FROM forum INNER JOIN forum_group_list ON forum.group_forum_id = forum_group_list.group_forum_id WHERE group_id = $1 ',
				array ($group_id));
	$q = array();
	$q["name"] = _('Forums');
	$q["nb"] = 0; $q["size"] = 0;
	if (db_numrows($res_db) > 0) {
		$e = db_fetch_array($res_db);
		$q["nb"] = $e["nb"];
		$q["size"] = $e["size"];
	}
	$quotas[] = $q;
}

if ($group->usesTracker()) {
	$res_db = $quota_management->getTrackerSizeForProject($group_id);
	$q = array();
	$q["name"] = _('Trackers');
	$q["nb"] = 0; $q["size"] = 0;
	if (db_numrows($res_db) > 0) {
		$e = db_fetch_array($res_db);
		$q["nb"] = $e["nb"];
		$q["size"] = $e["size"];
	}
	$quotas[] = $q;
}

if ($group->usesFRS()) {
	$res_db = $quota_management->getFRSSizeForProject($group_id);
	$q = array();
	$q["name"] = _('FRS');
	$q["nb"] = 0; $q["size"] = 0;
	if (db_numrows($res_db) > 0) {
		$e = db_fetch_array($res_db);
		$q["nb"] = $e["nb"];
		$q["size"] = $e["size"];
	}
	$quotas[] = $q;
}

if ($group->usesPM()) {
	$res_db = $quota_management->getPMSizeForProject($group_id);
	$q = array();
	$q["name"] = _('PM');
	$q["nb"] = 0; $q["size"] = 0;
	if (db_numrows($res_db) > 0) {
		$e = db_fetch_array($res_db);
		$q["nb"] = $e["nb"];
		$q["size"] = $e["size"];
	}
	$quotas[] = $q;
}

$quotas_disk = array();

// disk_total_space
$_quota_block_size = trim(shell_exec('echo $BLOCK_SIZE')) + 0;
if ($_quota_block_size == 0) $_quota_block_size = 1024;
$quota_soft = "";
$quota_hard = "";
$res_db = db_query_params('SELECT quota_soft, quota_hard FROM plugin_quota_management WHERE group_id = $1',
			array($group_id));
if (db_numrows($res_db) > 0) {
	$e = db_fetch_array($res_db);
	$quota_hard = $e["quota_hard"];
	$quota_soft = $e["quota_soft"];
	$quota_hard = round(($_quota_block_size * $quota_hard) / (1024*1024), 0);
	$quota_soft = round(($_quota_block_size * $quota_soft) / (1024*1024), 0);
}

$quota_tot_other = 0;
$quota_tot_1 = 0;
$quota_tot_scm = 0;

if (forge_get_config('use_shell')) {
	$q["name"] = _('Home project directory');
	$q["size"] = $quota_management->getHomeSize($group_id);
	$q["quota_label"] = _('With Home quota control');
	$quota_tot_1 += $q["size"];
	$quotas_disk[] = $q;
}

if ($group->usesFTP()) {
	$q["name"] = _('FTP project directory');
	$q["size"] = $quota_management->getFTPSize($group_id);
	$q["quota_label"] = _('With FTP quota control');
	$quota_tot_1 += $q["size"];
	$quotas_disk[] = $q;
}

plugin_hook_by_reference('quota_display', $quotas_disk);

?>

<table width="500" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="3" style="border-top:thick solid #808080; text-align: center"><?php echo _('Database'); ?></td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080"><?php echo _('quota type'); ?></td>
		<td style="border-top:thin solid #808080; text-align: right"><?php echo _('quantity'); ?></td>
		<td style="border-top:thin solid #808080; text-align: right"><?php echo _('size'); ?></td>
	</tr>
<?php
$sizetot = 0;
foreach ($quotas as $q) {
	if ($q["size"] != "") {
		$sizetot += $q["size"];
		?>
			<tr>
				<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
				<td style="border-top:thin solid #808080; text-align: right"><?php echo $q["nb"]; ?></td>
				<td style="border-top:thin solid #808080; text-align: right"><?php echo human_readable_bytes($q["size"]); ?></td>
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
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080; text-align: right">
			<?php echo human_readable_bytes($sizetot); ?>
		</td>
	</tr>
</table>
<br />
<br />
<?php if (count($quotas_disk) > 0) { ?>
<table width="500" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="3" style="border-top:thick solid #808080; text-align: center">
			<?php echo _('Disk space'); ?>
		</td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo _('quota type'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">&nbsp;</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('size'); ?>
		</td>
	</tr>
<?php
$sizetot = 0;
foreach ($quotas_disk as $q) {
	if ($q["size"] != "") {
		$sizetot += $q["size"];
?>
	<tr>
		<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo $q["quota_label"]; ?>&nbsp;
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo human_readable_bytes($q["size"]); ?>
		</td>
	</tr>
<?php
	}
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><?php echo _('Total'); ?></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">&nbsp;</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080; text-align: right">
			<?php echo human_readable_bytes($sizetot); ?>
		</td>
	</tr>
</table>
<br />
<br />

<?php
}
$color1 = "#ffffff";
$color2 = "#ffffff";
$msg1 = "&nbsp;";
$msg2 = "&nbsp;";
$qs = $quota_soft * 1024 * 1024;
if (($quota_tot_1+0) > ($qs+0) && ($qs+0) > 0) {
	$color1 = "#FFDCDC";
	$msg1 = _('Quota exceeded');
}
if (($quota_tot_scm+0) > ($qs+0) && ($qs+0) > 0) {
	$color2 = "#FFDCDC";
	$msg2 = _('Quota exceeded');
}
?>

<table width="500" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td colspan="4" style="border-top:thick solid #808080; text-align: center"><?php echo _('Quota disk management'); ?></td>
	</tr>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo _('Quota settings'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold; text-align: right">
			&nbsp;
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('Quota soft'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('Quota hard'); ?>
		</td>
	</tr>
	<tr style="background:<?php echo $color1; ?>">
		<td style="border-top:thin solid #808080">
			<?php echo _('Home, Ftp'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold;color:red; text-align: right">
			<?php echo $msg1; ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php
				if ($quota_soft == 0) {
					echo "---";
				} else {
					echo "$quota_soft";
					echo _('MB');
				}
			?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php
				if ($quota_hard == 0) {
					echo "---";
				} else {
					echo "$quota_hard";
					echo _('MB');
				}
			?>
		</td>
	</tr>
<?php if ($group->usesSCM()) { ?>
	<tr style="background:<?php echo $color2; ?>">
		<td style="border-top:thin solid #808080">
			<?php echo _('SCM'); ?>
		</td>
		<td style="border-top:thin solid #808080;font-weight:bold;color:red; text-align: right">
			<?php echo $msg2; ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php
				if ($quota_soft == 0) {
					echo "---";
				} else {
					echo "$quota_soft";
					echo _('MB');
				}
			?>
			</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php
				if ($quota_hard == 0) {
					echo "---";
				} else {
					echo "$quota_hard";
					echo _('MB');
				}
			?>
		</td>
	</tr>
<?php } ?>
	<tr style="font-weight:bold">
		<td colspan="4" style="border-top:thick solid #808080">&nbsp;</td>
	</tr>
</table>
<?php project_admin_footer();

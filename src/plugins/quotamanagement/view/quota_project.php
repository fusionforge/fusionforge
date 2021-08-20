<?php
/**
 * Project Admin page to manage quotas project
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2019,2021, Franck Villaume - TrivialDev
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
global $quotamanagement;
global $HTML;

session_require_perm('project_admin', $group_id);

$group = group_get_object($group_id);

if (!$group || !is_object($group)) {
	exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(), 'home');
}

$quotas = array();
if ($group->usesDocman()) {
	$res_db = $quotamanagement->getDocumentsSizeForProject($group_id);
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
	$res_db = $quotamanagement->getNewsSizeForProject($group_id);
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
	$res_db = $quotamanagement->getForumSizeForProject($group_id);
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
	$res_db = $quotamanagement->getTrackerSizeForProject($group_id);
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
	$res_db = $quotamanagement->getFRSSizeForProject($group_id);
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
	$res_db = $quotamanagement->getPMSizeForProject($group_id);
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

plugin_hook_by_reference('quota_display_db', $quotas);

$quotas_disk = array();

// disk_total_space
$_quota_block_size = intval(trim(shell_exec('echo $BLOCK_SIZE'))) + 0;
if ($_quota_block_size == 0) {
	$_quota_block_size = 1024;
}
$quota_soft = "";
$quota_hard = "";
$res_db = db_query_params('SELECT * FROM plugin_quotamanagement WHERE group_id = $1',
			array($group_id));
if (db_numrows($res_db) > 0) {
	$e = db_fetch_array($res_db);
	$quota_hard = $e["quota_hard"];
	$quota_soft = $e["quota_soft"];
	$quota_hard = round(($_quota_block_size * $quota_hard) / (1024*1024), 0);
	$quota_soft = round(($_quota_block_size * $quota_soft) / (1024*1024), 0);
	$quota_db_hard = $e["quota_db_hard"] / $_quota_block_size;
	$quota_db_soft = $e["quota_db_soft"] / $_quota_block_size;
}

if (forge_get_config('use_shell')) {
	$q["name"] = _('Home project directory');
	$q["size"] = $quotamanagement->getHomeSize($group_id);
	$quotas_disk[] = $q;
}

if ($group->usesFTP()) {
	$q["name"] = _('FTP project directory');
	$q["size"] = $quotamanagement->getFTPSize($group_id);
	$quotas_disk[] = $q;
}

plugin_hook_by_reference('quota_display_disks', $quotas_disk);

echo $HTML->listTableTop(array(_('Database'), _('DB Quota')), array(), '', 'quota', array(), array(), array(array('colspan' => 2, 'style' => 'text-align: center'), array('colspan' => 2, 'style' => 'text-align: center')));

?>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080"><?php echo _('Category'); ?></td>
		<td style="border-top:thin solid #808080; text-align: right"><?php echo _('Size'); ?></td>
		<td>&nbsp;</td><td>&nbsp;</td>
	</tr>
<?php
$sizetot = 0;
foreach ($quotas as $index => $q) {
	if ($q["size"] != "") {
		$sizetot += $q["size"];
		?>
			<tr>
				<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
				<td style="border-top:thin solid #808080; text-align: right"><?php echo human_readable_bytes($q["size"]); ?></td>
				<?php
					if ($index == max(array_keys($quotas))) {
						echo '<td style="border-top:thin solid #808080; text-align: center">'._('Soft').'</td><td style="border-top:thin solid #808080; text-align: center">'.('Hard').'</td>';
					} else {
						echo '<td>&nbsp;</td><td>&nbsp;</td>';
					}
				?>
			</tr>
<?php
	}
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080">
			<?php echo _('Total'); ?>
		</td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080; text-align: right">
			<?php echo human_readable_bytes($sizetot); ?>
			<?php
				if ($quota_db_soft == 0) {
					echo '<td style="border-top:thin solid #808080; text-align: center">---</td>';
				} else {
					echo '<td style="border-top:thin solid #808080; text-align: center">'.$quota_db_soft._('MB').'</td>';
				}
				if ($quota_db_hard == 0) {
					echo '<td style="border-top:thin solid #808080; text-align: center">---</td>';
				} else {
					echo '<td style="border-top:thin solid #808080; text-align: center">'.$quota_db_hard._('MB').'</td>';
				}

			?>
	</tr>
<?php
echo $HTML->listTableBottom(); ?>
<br />
<br />
<?php
if (!empty($quotas_disk)) {
	echo $HTML->listTableTop(array(_('Disk'), _('Quota')), array(), '', 'quota', array(), array(), array(array('colspan' => 2, 'style' => 'text-align: center'), array('colspan' => 2, 'style' => 'text-align: center')));
?>
	<tr style="font-weight:bold">
		<td style="border-top:thin solid #808080">
			<?php echo _('Category'); ?>
		</td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo _('size'); ?>
		</td>
		<td>&nbsp;</td><td>&nbsp;</td>
	</tr>
<?php
$sizetot = 0;
foreach ($quotas_disk as $index => $q) {
	if ($q["size"] != "") {
		$sizetot += $q["size"];
?>
	<tr>
		<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
		<td style="border-top:thin solid #808080; text-align: right">
			<?php echo human_readable_bytes($q["size"]); ?>
		</td>
		<?php
			if ($index == max(array_keys($quotas_disk))) {
				echo '<td style="border-top:thin solid #808080; text-align: center">'._('Soft').'</td><td style="border-top:thin solid #808080; text-align: center">'.('Hard').'</td>';
			} else {
				echo '<td>&nbsp;</td><td>&nbsp;</td>';
			}
		?>
	</tr>
<?php
	}
}
$bgcolorstyle = '';
$msg1 = '';
$qs = $quota_soft * 1024 * 1024;
if ($sizetot > $qs && $qs > 0) {
	$bgcolorstyle = 'background-color:#FFDCDC; color:white;';
	$msg1 = _('Quota exceeded');
}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><?php echo _('Total'); ?></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080; text-align: right; <?php echo $bgcolorstyle; ?>">
			<?php echo human_readable_bytes($sizetot); ?>
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
<?php
	echo $HTML->listTableBottom();
}
project_admin_footer();

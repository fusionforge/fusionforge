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

$cmd = getStringFromRequest('cmd');

$quota_management = plugin_get_object('quota_management');

$_quota_block_size = trim(shell_exec('echo $BLOCK_SIZE')) + 0;
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
$res_db = db_query_params('SELECT groups.group_id, groups.group_name, groups.unix_group_name, plugin_quota_management.quota_soft, plugin_quota_management.quota_hard FROM plugin_quota_management, groups ORDER BY group_id ',
			array());
if (db_numrows($res_db) > 0) {
	while($e = db_fetch_array($res_db)) {
		$qh = $e["quota_hard"] / $_quota_block_size;
		$qs = $e["quota_soft"] / $_quota_block_size;
		$quotas["$e[group_id]"]["group_id"] = $e["group_id"];
		$quotas["$e[group_id]"]["name"] = $e["group_name"];
		$quotas["$e[group_id]"]["unix_name"] = $e["unix_group_name"];
		$quotas["$e[group_id]"]["database_size"] = 0;
		$quotas["$e[group_id]"]["disk_size"] = 0;
		$quotas["$e[group_id]"]["quota_hard"] = $qh;
		$quotas["$e[group_id]"]["quota_soft"] = $qs;
	}
}
?>
<table width="700px" cellpadding="2" cellspacing="0" border="0">
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080" colspan="6"><?php echo _('Projects disk quota'); ?></td>
	</tr>
	<tr>
		<td style="border-top:thin solid #808080"><?php echo _('id'); ?></td>
		<td style="border-top:thin solid #808080"><?php echo _('name'); ?></td>
		<td style="border-top:thin solid #808080"><br /></td>
		<td style="border-top:thin solid #808080" align="right"><?php echo _('disk quota soft'); ?></td>
		<td style="border-top:thin solid #808080" align="right"><?php echo _('disk quota hard'); ?></td>
		<td style="border-top:thin solid #808080"><br /></td>
	</tr>
	<?php
	$total_database = 0;
	$total_disk = 0;
	foreach ($quotas as $q)
	{
		$total_database += $q["database_size"];
		$total_disk += $q["disk_size"];
		echo $HTML->openForm(array('action' => '/plugins/'.$quota_management->name.'/?type=globaladmin&action=update', 'method' => 'post'));
		?>
		<input type="hidden" name="group_id" value="<?php echo $q["group_id"]; ?>" />
		<tr>
			<td style="border-top:thin solid #808080"><?php echo $q["group_id"]; ?></td>
			<td style="border-top:thin solid #808080">
			<?php echo util_make_link('/plugins/quota_management/?group_id='.$q['group_id'].'&type=projectadmin', $q['unix_name']) ?>
			</td>
			<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
			<td style="border-top:thin solid #808080" align="right">
				<input type="text" name="qs"
					size="12"
					value="<?php echo $q["quota_soft"]; ?>"
					style="background:#ffffd0;text-align:right" />
					<?php echo _('MB'); ?>
			</td>
			<td style="border-top:thin solid #808080" align="right">
				<input type="text" name="qh"
					size="12"
					value="<?php echo $q["quota_hard"]; ?>"
					style="background:#ffffd0;text-align:right" />
				<?php echo _('MB'); ?>
			</td>
			<td style="border-top:thin solid #808080" align="right">
				<input type="submit" value="<?php echo _('Modify'); ?>" />
			</td>
		</tr>
		<?php
		echo $HTML->closeForm();
	}
?>
	<tr style="font-weight:bold">
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
		<td style="border-top:thick solid #808080;border-bottom:thick solid #808080"><br /></td>
	</tr>
</table>
<?php

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

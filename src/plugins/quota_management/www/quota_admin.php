<?php
/**
 * Project Admin page to manage quotas disk and database
 *
 * 
 * Fabio Bertagnin November 2005
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
require_once $gfwww.'admin/admin_utils.php';


$_quota_block_size = 1024;
$_quota_block_size = trim(shell_exec("echo $BLOCK_SIZE")) + 0;
if ($_quota_block_size == 0) $_quota_block_size = 1024;

site_admin_header(array('title'=>_('Site admin')));

?>
<h4>
	<a href="quota.php"><?php echo _('Ressources usage and quota'); ?></a>
	&nbsp;&nbsp;
	<?php echo _('Quota Manager Admin'); ?>
</h4>
<?php

// echo "<pre>".print_r($_POST, true)."</pre>";

// quota update 
if ($_POST["cmd"] == "maj")
{
	$qs = $_POST["qs"] * $_quota_block_size;
	$qh = $_POST["qh"] * $_quota_block_size;
	if ($qs > $qh)
	{
		$message = utf8_encode(_('Input error: Hard quota must be greater than soft quota'));
		echo "<h3 style=\"color:red\">$message</h3>";
	}
	else
	{
		db_query_params ('UPDATE groups SET quota_soft = $1, quota_hard = $2 WHERE group_id = $3',
				 array ($qs,
					$qh,
					getIntFromRequest ('group_id')));
		$message = utf8_encode(_('Successfully updated quota'));
		echo "<h3 style=\"color:red\">$message</h3>";
	}
}


// stock projects infos in array
$quotas = array();

// all projects list
$res_db = db_query_params ('SELECT group_id, group_name, unix_group_name, quota_soft, quota_hard FROM groups ORDER BY group_id ',
			array ());
if (db_numrows($res_db) > 0) 
{
	while($e = db_fetch_array($res_db))
	{
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
		?>
		<form action="quota_admin.php" method="POST">
		<input type="hidden" name="cmd" value="maj" />
		<input type="hidden" name="group_id" value="<?php echo $q["group_id"]; ?>" />
		<tr>
			<td style="border-top:thin solid #808080"><?php echo $q["group_id"]; ?></td>
			<td style="border-top:thin solid #808080"><a href="<?php echo util_make_url ('/project/admin/quota.php?group_id='.$q['group_id']); ?>">
				<?php echo $q["unix_name"]; ?>
			</a></td>
			<td style="border-top:thin solid #808080"><?php echo $q["name"]; ?></td>
			<td style="border-top:thin solid #808080" align="right">
				<input type="text" name="qs" 
					size="12" 
					value="<?php echo $q["quota_soft"]; ?>" 
					style="background:#ffffd0;text-align:right" /> 
					<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080" align="right">
				<input type="text" name="qh" 
					size="12" 
					value="<?php echo $q["quota_hard"]; ?>" 
					style="background:#ffffd0;text-align:right" /> 
				<?php echo _('Mb'); ?>
			</td>
			<td style="border-top:thin solid #808080" align="right">
				<input type="submit" value="<?php echo _('Modify'); ?>" />
			</td>
		</tr>
		</form>
		<?php
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
<br />
<br />
<?php


print_debug(print_r($quotas, true));
print_debug(print_r($users, true));

site_admin_footer(array());
?>

<?php
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

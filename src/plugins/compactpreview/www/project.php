<?php

/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Institut
 * TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';


$project = getStringFromRequest('project');

$project_obj = group_get_object_by_name($project);

$public_name = $project_obj->getPublicName();
$unix_name = $project_obj->getUnixName();
$id = $project_obj->getID();
$home_page = util_make_url ("/projects/$unix_name/");
$description = $project_obj->getDescription();
$start_date = $project_obj->getStartDate();
$status = $project_obj->getStatus();
switch ($status){
	case 'A':
		$project_status = 'Active';
		break;
	case 'H':
		$project_status = 'Hold';
		break;
	case 'P':
		$project_status = 'Pending';
		break;
	case 'I':
		$project_status = 'Incomplete';
		break;
	default:
		break;
}
if ($project_obj->isPublic()) {
	$public = 'Yes';
} else {
	$public = 'No';
}


?>

<html>
<head>
<title>Project: <?php echo $public_name;?> (<?php echo $unix_name;?>)</title>
</head>
<body>
	<table>

		<tr>
			<td colspan="2"><i>Project Compact Preview</i></td>
		</tr>
		<tr>
			<td rowspan="8"><img src="/plugins/compactpreview/images/userTooltip/oslc.png" />
			</td>
			<td><b>Project name:</b>
			
			
			
			 <?php echo $public_name;?></td>
		</tr>
		<tr>
			<td><b>Project short name:</b>
			
			
			
			 <?php echo $unix_name;?></td>
		</tr>
		<tr>
			<td><b>Identifier:</b>
			
			
			
			  <?php echo $id;?></td>
		</tr>
		<tr>
			<td><b>Started since:</b>
			
			
			
			 <?php print date(_('Y-m-d H:i'), $start_date); ?></td>
		</tr>
		<tr>
			<td><b>Status:</b>
			
			
			
			  <?php echo $project_status; ?></td>
		</tr>
		<tr>
			<td><b>Is Public:</b>
			
			
			
			  <?php echo $public; ?></td>
		</tr>
		<tr>
			<td><b>Description:</b>
			
			
			
			  <?php echo $description; ?></td>
		</tr>
		<tr>
			<td><small><b>Home Page:</b> <a href="<?php echo $home_page;?>"><?php echo $home_page;?>
				</a> </small></td>
		</tr>

	</table>
</body>
</html>

<?php

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

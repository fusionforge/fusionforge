<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

echo '<' . '?xml version="1.0" encoding="utf-8" ?' . ">\n" .
    $sysDTDs['transitional']['doctype']; ?>
<html <?php echo $sysXMLNSs; ?> xml:lang="en">

  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo _('Gantt Chart');?></title>
  </head>
  <body>
<?php

$_assigned_to = getIntFromRequest('_assigned_to', 0);
$_category_id = getIntFromRequest('_category_id');
$_order = getIntFromRequest('_order');
$_resolution = getStringFromRequest('_resolution');
$_size = getIntFromRequest('_size', 800);
$_status = getIntFromRequest('_status', 100);
$_order = getStringFromRequest('_order');

$engine = RBACEngine::getInstance () ;
$techs = $engine->getUsersByAllowedAction ('pm', $pg->getID(), 'tech') ;

$tech_id_arr = array () ;
$tech_name_arr = array () ;

foreach ($techs as $tech) {
	$tech_id_arr[] = $tech->getID() ;
	$tech_name_arr[] = $tech->getRealName() ;
}
$tech_id_arr[]='0';
$tech_name_arr[]=_('Any');

$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,_('Unassigned'), true, _('Any'));

$status_box=html_build_select_box($pg->getStatuses(),'_status',$_status, false, '', true, _('Any'));

$cat_box=html_build_select_box($pg->getCategories(), '_category_id', $_category_id, true, _('None'), true, _('Any'));

/*
	Creating a custom sort box
*/
$title_arr=array();
$title_arr[]=_('Task Id');
$title_arr[]=_('Task Summary');
$title_arr[]=_('Start Date');
$title_arr[]=_('End Date');
$title_arr[]=_('Percent Complete');

$order_col_arr=array();
$order_col_arr[]='project_task_id';
$order_col_arr[]='summary';
$order_col_arr[]='start_date';
$order_col_arr[]='end_date';
$order_col_arr[]='percent_complete';
$order_box=html_build_select_box_from_arrays ($order_col_arr,$title_arr,'_order',$_order,false);

$dispres_col_arr=array();
$dispres_col_arr[]='Years';
$dispres_col_arr[]='Months';
$dispres_col_arr[]='Weeks';
$dispres_col_arr[]='Days';

$dispres_title_arr=array();
$dispres_title_arr[]=_('Years');
$dispres_title_arr[]=_('Months');
$dispres_title_arr[]=_('Weeks');
$dispres_title_arr[]=_('Days');
if (!$_resolution) {
	$_resolution=_('Months');
}
$dispres_box=html_build_select_box_from_arrays ($dispres_col_arr,$dispres_title_arr,'_resolution',$_resolution,false);

/*
	Graph Size Box
*/
$size_col_arr=array();
$size_col_arr[]=640;
$size_col_arr[]=800;
$size_col_arr[]=1024;
$size_col_arr[]=1600;

$size_title_arr=array();
$size_title_arr[]='640 x 480';
$size_title_arr[]='800 x 600';
$size_title_arr[]='1024 x 768';
$size_title_arr[]='1600 x 1200';

$size_box=html_build_select_box_from_arrays ($size_col_arr,$size_title_arr,'_size',$_size,false);

/*
	Show the new pop-up boxes to select assigned to and/or status
*/
	global $_size;
		if ($_size==640) {
			$gantt_width=740;
			$gantt_height=620;
		} elseif ($_size==1024) {
			$gantt_width=1084;
			$gantt_height=920;
		} elseif ($_size==1600) {
			$gantt_width=1660;
			$gantt_height=1340;
		} else {
			$gantt_width=860;
			$gantt_height=740;
		}
		//echo "XX $_size $gantt_width $gantt_height XX";
		?>
		<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
		function setSize(width,height) {
			if (window.outerWidth) {
				window.outerWidth = width;
				window.outerHeight = height;
				window.resize();
			}
			else if (window.resizeTo) {
				window.resizeTo(width,height);
			}
			else {
				alert("Not supported.");
			}
		}
		window.setSize(<?php echo $gantt_width; ?>,<?php echo $gantt_height; ?>);
		/* ]]> */</script>
		<?php

echo '	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=ganttpage" method="post">
	<table width="10%" border="0" class="tableheading">
	<tr>
		<td>'._('Assignee').'<br />'. $tech_box .'</td>
		<td>'._('Status').'<br />'. $status_box .'</td>
		<td>'._('Category').'<br />'. $cat_box .'</td>
		<td>'._('Sort On').'<br />'. $order_box .'</td>
		<td>'._('Resolution').'<br />'. $dispres_box .'</td>
		<td>'._('Size').'<br />'. $size_box .'</td>
		<td><input type="submit" name="submit" value="'._('Browse').'" /></td>
	</tr></table></form>';

echo '<img src="'. getStringFromServer('PHP_SELF') .
		'?func=ganttchart&amp;group_id='.$group_id.
		'&amp;group_project_id='.$group_project_id.
		'&amp;_assigned_to='.$_assigned_to.
		'&amp;_status='.$_status.
		'&amp;_order='.$_order.
		'&amp;_resolution='.$_resolution.
		'&amp;_category_id='.$_category_id.
		'&amp;_size='.$_size.
		'&amp;rand='.util_randnum().'" alt="'. _('Gantt Chart').'" />';

//pm_footer(array());
?>
</body>
</html>

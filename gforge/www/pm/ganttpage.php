<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

//pm_header(array('title'=>'Browse Tasks','pagename'=>$pagename,'group_project_id'=>$group_project_id,'sectionvals'=>$g->getPublicName()));

?>

<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>

<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en   ">

  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $Language->getText('pm_include_grouphtml', 'gantt_chart');?></title>
  </head>
  <body>
<?php
/*
		creating a custom technician box which includes "any" and "unassigned"
*/

$res_tech=$pg->getTechnicians();

$tech_id_arr=util_result_column_to_array($res_tech,0);
$tech_id_arr[]='0';  //this will be the 'any' row

$tech_name_arr=util_result_column_to_array($res_tech,1);
$tech_name_arr[]=$Language->getText('pm','tech_any');

if ( empty($_assigned_to) ) {
	$_assigned_to='0';
}
$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,$Language->getText('pm','unassigned'));

/*
		creating a custom category box which includes "any" and "none"
*/

$res_cat=$pg->getCategories();

$cat_id_arr=util_result_column_to_array($res_cat,0);
$cat_id_arr[]='0';  //this will be the 'any' row

$cat_name_arr=util_result_column_to_array($res_cat,1);
$cat_name_arr[]=$Language->getText('pm','cat_any');

$cat_box=html_build_select_box_from_arrays ($cat_id_arr,$cat_name_arr,'_category_id',$_category_id,true,$Language->getText('pm','none').$Language->getText('pm','none'));

/*
	Creating a custom sort box
*/
$title_arr=array();
$title_arr[]=$Language->getText('pm','task_id');
$title_arr[]=$Language->getText('pm','summary');
$title_arr[]=$Language->getText('pm','start_date');
$title_arr[]=$Language->getText('pm','end_date');
$title_arr[]=$Language->getText('pm','percent_complete');

$order_col_arr=array();
$order_col_arr[]='project_task_id';
$order_col_arr[]='summary';
$order_col_arr[]='start_date';
$order_col_arr[]='end_date';
$order_col_arr[]='percent_complete';
$order_box=html_build_select_box_from_arrays ($order_col_arr,$title_arr,'_order',$_order,false);

$dispres_title_arr=array();
$dispres_title_arr[]=$Language->getText('pm_ganttpage','months');
$dispres_title_arr[]=$Language->getText('pm_ganttpage','weeks');
$dispres_title_arr[]=$Language->getText('pm_ganttpage','days');
if (!$_resolution) {
	$_resolution=$Language->getText('pm_ganttpage','months');
}
$dispres_box=html_build_select_box_from_arrays ($dispres_title_arr,$dispres_title_arr,'_resolution',$_resolution,false);

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
if (!$_size) {
	$_size='1600';
}
$size_box=html_build_select_box_from_arrays ($size_col_arr,$size_title_arr,'_size',$_size,false);

if (!$_status) {
	$_status='100';
}

/*
	Show the new pop-up boxes to select assigned to and/or status
*/
echo '	<form action="'. $PHP_SELF .'?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=ganttpage" method="post">
	<table width="10%" border="0">
	<tr>
		<td><font size="-1">'.$Language->getText('pm_ganttpage','assignee').':<br />'. $tech_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm','status').':<br />'. $pg->statusBox('_status',$_status,'Any') .'</font></td>
		<td><font size="-1">'.$Language->getText('pm','category').':<br />'. $cat_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm_ganttpage','sort_on').':<br />'. $order_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm_ganttpage','resolution').':<br />'. $dispres_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm_ganttpage','size').':<br />'. $size_box .'</font></td>
		<td><font size="-1"><input type="submit" name="submit" value="'.$Language->getText('general','browse').'" /></font></td>
	</tr></table></form>';

echo '<img src="'. $PHP_SELF .
		'?func=ganttchart&amp;group_id='.$group_id.
		'&amp;group_project_id='.$group_project_id.
		'&amp;_assigned_to='.$_assigned_to.
		'&amp;_status='.$_status.
		'&amp;_order='.$_order.
		'&amp;_resolution='.$_resolution.
		'&amp;_category_id='.$_category_id.
		'&amp;_size='.$_size.
		'&amp;rand='.time().'" alt="'. $Language->getText('pm_include_grouphtml', 'gantt_chart').'" />';

//pm_footer(array());
?>
</body>
</html>

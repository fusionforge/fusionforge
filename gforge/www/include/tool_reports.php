<?php
/**
 * tool_reports.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */


/**
 * reports_quick_graph() - Show a quick graph of data.
 * 
 * @param		string	Graph title
 * @param		int		First query resource ID
 * @param		int		Second query resource ID
 * @param		string	The bar colors
 */
function reports_quick_graph($title,$sql1,$sql2,$bar_colors) {
	$result1=db_query($sql1);
	$result2=db_query($sql2);
	if ($result1 && $result2 && db_numrows($result2) > 0) {

		$assoc_open=util_result_columns_to_assoc($result1);
		$assoc_all=util_result_columns_to_assoc($result2);
		while (list($key,$val)=each($assoc_all)) {
			$titles[]=$key;
			$all[]=$val;
			if ($assoc_open[$key])	$open[]=$assoc_open[$key];
			else $open[]=0;
		}

/*	       	for ($i=0; $i<db_numrows($result1); $i++) {
			echo "$titles[$i]=>$opened[$i]/$all[$i]<br />";
		}
*/
		$scale=graph_calculate_scale(array($opened,$all),400);
		$props["scale"]=$scale;
		$props["cellspacing"]=5;
		$props = hv_graph_defaults($props);
		start_graph($props, $titles);

		horizontal_multisection_graph(
			$titles,
			array($open,$all),
			$bar_colors,
			$props
		);
		end_graph();
		print '<p><br />';
		print '<table cellspacing="0" border="0"><tr align="center"><td width="15%">Key:</td><td width="5%">(</td><td width="35%" style="background-color:'.$bar_colors[0].'">Open </td>'.
		      '<td width="5%">/</td><td width="35%" style="background-color:'.$bar_colors[1].'">All </td><td width="5%">)</td></tr></table>';
		print '</p>';
//      		GraphResult($result,$title);
	} else {
		echo "<h2>No data found to report</h2>";
	}
}


/**
 * reports_header() - Show the reports header
 * 
 * @param		int		The group ID
 * @param		array	Array of select box values
 * @param		string	The select box title
 * @param		string	Any additional HTML
 */
function reports_header($group_id, $vals, $titles, $html='') {
	global $what;
	global $period;
	global $span;

	print "<form method=\"GET\" action=\"$PHP_SELF#b\">";

	print $html;

	print html_build_select_box_from_arrays ($vals,$titles,
						 'what',$what,false);

	$periods=array('day'=>'Last day','week'=>'Last week');
	$vals=array('day','week','month','year','lifespan');
	$texts=array('Last day(s)','Last week(s)','Last month(s)','Last year(s)','Project lifespan');
	if (!$period) $period="lifespan";

	print " for ";
	print html_build_select_box_from_arrays (
		array('','1','4','7','12','14','30','52'),
		array('','1','4','7','12','14','30','52'),
		'span',$span,false);
	print html_build_select_box_from_arrays ($vals,$texts,'period',$period,false);

	print "<input type=\"hidden\" name=\"group_id\" value=\"$group_id\" />";
	print ' <input type="submit" value="Show" />';
	print "</form>\n";
}

?>

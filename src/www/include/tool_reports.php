<?php
/**
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
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

/**
 * reports_quick_graph() - Show a quick graph of data.
 *
 * @param		string	Graph title
 * @param		int		First query resource ID
 * @param		int		Second query resource ID
 * @param		string	The bar colors
 */
function reports_quick_graph($title,$qpa1,$qpa2,$bar_colors) {
	$result1=db_query_qpa($qpa1);
	$result2=db_query_qpa($qpa2);
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
		$scale=graph_calculate_scale(array($open,$all),400);
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
		print '<p /><br />';
		print '<table cellspacing="0" border="0"><tr align="center"><td width="15%">'._('Key').':</td><td width="5%">(</td><td width="35%" style="background-color:'.$bar_colors[0].'">'._('Open').'</td>'.
		      '<td width="5%">/</td><td width="35%" style="background-color:'.$bar_colors[1].'">'._('All').' </td><td width="5%">)</td></tr></table>';
		print '<p />';
//      		GraphResult($result,$title);
	} else {
		echo "<p class='warning_msg'>"._('No data found to report')."</p>";
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

	print '<form method="get" action="'.getStringFromServer('PHP_SELF').'#b">';

	print $html;

	print html_build_select_box_from_arrays ($vals,$titles,
						 'what',$what,false);

	$periods=array('day'=>'Last day','week'=>'Last week');
	$vals=array('day','week','month','year','lifespan');
	$texts=array(
		_('Last day(s)'),
		_('Last week(s)'),
		_('Last month(s)'),
		_('Last year(s)'),
		_('Project lifespan'));

	if (!$period) $period="lifespan";

	print _('for');
	print html_build_select_box_from_arrays (
		array('','1','4','7','12','14','30','52'),
		array('','1','4','7','12','14','30','52'),
		'span',$span,false);
	print html_build_select_box_from_arrays ($vals,$texts,'period',$period,false);

	print "<input type=\"hidden\" name=\"group_id\" value=\"$group_id\" />";
	print ' <input type="submit" value="'._('Show').'" />';
	print "</form>\n";
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

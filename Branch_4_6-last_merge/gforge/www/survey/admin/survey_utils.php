<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
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

Function  ShowResultsEditSurvey($result) {
	global $group_id;
	global $Language;
	$rows  =  db_NumRows($result);
	$cols  =  db_NumFields($result);
	echo "<h3>". $Language->getText('survey_admin_utils','found',array($rows))."</h3>";

	if ($rows > 0) {
		echo "<table border=\"0\">\n";

		/*  Create  the  headers  */
		echo "<tr class=\"tableheading\">\n";
		for ($i  =  0;  $i  <  $cols;  $i++)  {
			echo "<th>".$Language->getText('survey_admin_utils',db_fieldname($result,$i))."</th>\n";
		}
		echo "</tr>";
		for($j  =  0;  $j  <  $rows;  $j++)  {

			echo "<tr class=\".$HTML->boxGetAltRowStyle($j)\">\n";
			echo "<td><a href=\"edit_survey.php?group_id=$group_id&amp;survey_id=".db_result($result,$j,0)."\">".db_result($result,$j,0)."</a></td>";
			for ($i = 1; $i < $cols; $i++)  {
				echo "<td>".db_result($result,$j,$i)."</td>\n";
			}

			echo "</tr>";
		}
		echo "</table>"; 
	}
}


?>

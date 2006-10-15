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

require_once('../../env.inc.php');
require_once('pre.php');
require_once('HTML_Graphs.php');
require_once('www/survey/survey_utils.php');

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

if (!session_loggedin() || !user_ismember($group_id,'A')) {
        echo "<h1>".$Language->getText('survey_show_results_csv','permission_denied')."</h1>";
	exit;
}

function strip_commas($string) {
	return ereg_replace(",","",$string);
}

/*
	Select this survey from the database
*/

$sql="select * from surveys where survey_id='$survey_id'";

$result=db_query($sql);

/*
	Select the questions for this survey and show as top row
*/

$questions=db_result($result, 0, "survey_questions");
$questions=str_replace(" ", "", $questions);
$quest_array=explode(',', $questions);
$count=count($quest_array);

echo "<html><pre>";
/*
#
#
#
#
#                  clean up later
#
#
#
#
*/

echo "cust_id,first_name,field_1,email,field2,phone,field3,field4,field5,year,month,day,";

for ($i=0; $i<$count; $i++) {
	$result=db_query("select question from questions where question_id='$quest_array[$i]' AND question_type <> '4'");
	if ($result && db_numrows($result) > 0) {
		echo strip_commas(db_result($result, 0, 0)).",";
	}
}

echo "\n";

/*
	Now show the customer rows
*/

$sql="SELECT DISTINCT customer_id FROM responses WHERE survey_id='$survey_id'";

$result=db_query($sql);

$rows=db_numrows($result);

for ($i=0; $i<$rows; $i++) {

	/*
		Get this customer's info
	*/
	$sql="SELECT DISTINCT cust_id,first_name,people.last_name,people.email,people.email2,people.phone,".
		"people.beeper,people.cell,people.yes_interested,responses.response_year,".
		"responses.response_month,responses.response_day FROM people,responses ".
		"WHERE cust_id='".db_result($result, $i, "customer_id")."' AND cust_id=responses.customer_id";

	$result2=db_query($sql);

	if (db_numrows($result2) > 0) {

		$cols=db_numfields($result2);

		for ($i2=0; $i2<$cols; $i2++) {
			echo strip_commas(db_result($result2, 0, $i2)).",";
		}

		/*
			Get this customer's responses. may have to be ordered by original question order
		*/
		$sql="SELECT response FROM responses WHERE customer_id='".db_result($result, $i, "customer_id")."' AND survey_id='$survey_id'";

		$result3=db_query($sql);

		$rows3=db_numrows($result3);

		for ($i3=0; $i3<$rows3; $i3++) {
			echo strip_commas(db_result($result3, $i3, "response")).",";
		}

		/*
			End of this customer
		*/
		echo "\n";

	}

}

?>

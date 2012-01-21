<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
 *
 * @version   $Id: show_results.php 4561 2005-08-17 12:34:37Z danper $
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'survey/Survey.class.php';
require_once $gfcommon.'survey/SurveyFactory.class.php';
require_once $gfcommon.'survey/SurveyQuestion.class.php';
require_once $gfcommon.'survey/SurveyQuestionFactory.class.php';
require_once $gfcommon.'survey/SurveyResponse.class.php';
require_once $gfcommon.'survey/SurveyResponseFactory.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$graph = getStringFromRequest('graph');
$show_comment = getStringFromRequest('show_comment');

$html = getStringFromRequest('html');

/* We need a group_id */
if (!$group_id) {
    exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$is_admin_page='y';
$sh = new  SurveyHtml();

$is_admin_page='y';

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<p class="error">'._('Permission denied').'</p>';
	$sh->footer(array());
	exit;
}

if ($survey_id) {
    $s = new Survey($g, $survey_id);
		
    /* Get questions of this survey */
    $questions = & $s->getQuestionInstances();
    foreach ($questions as $cur_question){
	    $qid = $cur_question->getId();
	    $lib = $cur_question->getQuestion();
	    $type = $cur_question->getQuestionType();
	    $header[$qid]=$lib;
	    $types[$qid]=$type;
	}

    $one_question = $questions[0];
    $srf = new SurveyResponseFactory($s, $one_question);
    if (!$srf || !is_object($srf)) {
		echo '<p class="error">'._("Error"). ' ' . _('Cannot get Survey Response Factory') ."</p>";
    } else if ( $srf->isError()) {
		echo '<p class="error">'._("Error"). $srf->getErrorMessage() ."</p>";
    } else {
		$s2=$srf->getDetailResults();
        if ($html) {
			$sh->header(array());
			print "\n".'<table border="1">'."\n";
			print "<tr>";
			//print "<td>User</td>";
			foreach ($header as $id=>$col){
				print "<td>$col</td>";
			}
			print "</tr>\n";
			foreach ($s2 as $k=>$val){
				print "<tr>";
				//print "<td>$k</td>";
				$val = array_reverse($val);
				foreach ($val as $k1=>$val1){
			 		$res = format($val1,$types[$k1]);
					print "<td>$res</td>";
		        }
				print "</tr>\n";
			}
			print "</table>";
			$sh->footer(array());
        } else {
			// CSV mode
		    header('Content-type: text/csv');
		    list($year, $month) = explode('-', date('Y-m'));
		    header('Content-disposition: filename="survey-'.$year.'-'.$month.'.csv"');
	
		    foreach ($header as $id=>$col){
				echo '"'.fix4csv($col).'";';
		    }
	
		    foreach ($s2 as $k=>$val){
				echo "\n";
			    	foreach ($header as $id=>$col){
			    	$res = format($val[$id],$types[$id]);
			    	echo '"'.$res.'";';
				}
		    }
		}
    }
}

/*
 *                      1: Radio Buttons 1-5
 *                      2: Text Area
 *                      3: Radio Buttons Yes/No
 *                      4: Comment Only
 *                      5: Text Field
 *                      6: None
 */


function format ($f,$type) {
//$radio_button = array("","5 (hight)", "4 (good)", "3 (mean)", "2 (low)", "1 (low)");
$radio_button = array( "", "1 (low)", "2 (low)", "3 (mean)", "4 (good)", "5 (hight)" );
$yes_no = array("","Yes","","","","No");
	if($type == 1){
		if($f < 0 OR $f > 5){ return ""; }
		//return($radio_button[$f]);
		return($f);
	}
	if($type == 3){
		if($f < 0 OR $f > 5){ return ""; }
		return($yes_no[$f]);
	}
	if($type == 4){ // Comment only => ""
		return("");
	}
	if($type == 6){
		return "None";
	}
	// 2 - Text Area :: 5: Text Field
	return(fix4csv($f));
}

function fix4csv ($value) {
	$value =& util_unconvert_htmlspecialchars( $value );
	$value =& str_replace("\r\n", "\n", $value);
	$value =& str_replace('"', '""', $value);
	return $value;
}

?>

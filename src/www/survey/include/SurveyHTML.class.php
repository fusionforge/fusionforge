<?php
/**
 * FusionForge Survey HTML Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team - Sung Kim
 * Copyright 2008-2010 (c) FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
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

require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/note.php';

/**
 * Survey HTML related functions
 */
class SurveyHTML extends Error {

	/**
	 * Dummy constructor 
	 */
	function SurveyHTML() {
		return true;
	}

	/**
	 * Show survey header 
	 */
	function header($params) {
		global $group_id,$is_admin_page,$HTML;

		if (!forge_get_config('use_survey')) {
			exit_disabled();
		}
		
		$params['toptab']='surveys';
		$params['group']=$group_id;
		
		if ($project = group_get_object($group_id)){
			if (!$project->usesSurvey()) {
			exit_disabled();
			}
			
			if ($is_admin_page && $group_id) {
				$params['submenu'] = $HTML->subMenu(
					array(
						_('Add Survey'),
						_('Add Question'),
						_('Show Results'),
						_('Administration')
					),
					array(
						'/survey/admin/survey.php?group_id='.$group_id,
						'/survey/admin/question.php?group_id='.$group_id,
						'/survey/admin/show_results.php?group_id='.$group_id,
						'/survey/admin/?group_id='.$group_id
					)
				);
			} else {
				$labels[] = _('Views Surveys');
				$links[]  = '/survey/?group_id='.$group_id;
				if (forge_check_perm ('project_admin', $group_id)) {
						$labels[] = _('Administration');
						$links[]  = '/survey/admin/?group_id='.$group_id;
				}
				$params['submenu'] = $HTML->subMenu($labels,$links); 
			}
			site_project_header($params);
		}// end if (valid group id)
	}
	
	/**
	 * Show Survey footer
	 */
	function footer($params) {
		site_project_footer($params);
	}

	/**
         * Show Add/Modify Question Forums
         * @param Survey Question Question Object
         * Return string 
         */
	function showAddQuestionForm( &$q ) {
		global $group_id;

		/* Default is add */
		$question_button = _('Add this Question');

		/* If we have a question object, it is a Modify */
		if ($q && is_object($q) && !$q->isError() && $q->getID()) {
			$warning_msg = '<p class="warning_msg">'.
				_('WARNING! It is a bad idea to change a question after responses to it have been submitted').
				'</p>';
			$question_id = $q->getID();
			$question = $q->getQuestion();
			$question_type = $q->getQuestionType();
			$question_button = _('Submit Changes');
		} else {
			$warning_msg = '';
			$question = '';
			$question_id = '';
			$question_type = '';
		}

		$ret = $warning_msg;
		$ret.='<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
		$ret.='<p><input type="hidden" name="post" value="Y" />';
		$ret.='<input type="hidden" name="group_id" value="'.$group_id.'" />';
		$ret.='<input type="hidden" name="question_id" value="'.$question_id.'" />';
		$ret.='<input type="hidden" name="form_key" value="' . form_generate_key() . '" />';
		$ret.=_('Question').':<br />';
		$ret.='<input type="text" name="question" value="'.$question.'" size="60" maxlength="150" /></p>';
		$ret.='<p>'. _('Question Type').':<br />';
	
		$result = db_query_params ('SELECT * FROM survey_question_types',
					   array());
		$ret.= html_build_select_box($result,'question_type',$question_type,false);

		$ret.='</p><p><input type="submit" name="submit" value="'.$question_button.'" /></p>';
		$ret.='</form>';
		
		return $ret;
	}
	
        /**
         * Show Add/Modify Question Forums
         * @param Survey Question Question Object
         * Return string 
         */
	function showAddSurveyForm( &$s) {
		global $group_id;
		global $survey_id;

		/* If no question is available */
		if (! $survey_id && ! count($s->getAddableQuestionInstances())) {
			$ret = '<p>' . sprintf(_('Please %1$s create a question %2$s before creating a survey'),
								  '<a href="'.util_make_url('/survey/admin/question.php?group_id='.$group_id).'">',
             					  '</a>') .
				   '</p>';
			return $ret;
		}
		
		/* Default is add */
		$survey_button = _('Add this Survey');
		$active = ' checked="checked" ';
		$inactive = '';

		/* If we have a survey object, it is a Modify */
		if ($s && is_object($s) && !$s->isError() && $s->getID()) {
			$warning_msg = '<p class="warning_msg">'.
				_('WARNING! It is a bad idea to edit a survey after responses have been posted').'</p>';
			$survey_id = $s->getID();
			$survey_title = $s->getTitle();
			$survey_questions = $s->getQuestionString();
			$survey_button = _('Submit Changes');
			if (!$s->isActive()) {
				$inactive = 'checked ="checked" ';
				$active ='';
			}
		} else {
			$warning_msg = '';
			$survey_questions = ''; 
			$survey_title = ''; 
		}

		$ret = $warning_msg;
		$ret.='<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
		$ret.='<input type="hidden" name="post" value="Y" />';
		$ret.='<input type="hidden" name="group_id" value="'.$group_id.'" />';
		$ret.='<input type="hidden" name="survey_id" value="'.$survey_id.'" />';
		$ret.='<input type="hidden" name="survey_questions" value="'.$survey_questions.'" />';
		$ret.='<input type="hidden" name="form_key" value="' . form_generate_key() . '">';
		$ret.='<strong>'. _('Name Of Survey:').'</strong>' .utils_requiredField();
		$ret.= '<input type="text" name="survey_title" value="'.$survey_title.'" length="60" maxlength="150" /><p>';
		
		$ret.='<p><strong>'. _('Is Active?').'</strong>';
		$ret.='<br /><input type="radio" name="is_active" value="1"' .$active. '/>'._('Yes');
		$ret.='<br /><input type="radio" name="is_active" value="0"' .$inactive. '/>'._('No');

		$arr_to_add = & $s->getAddableQuestionInstances();
		$arr_to_del = & $s->getQuestionInstances();
		
		if (count($arr_to_add)>0) {
			$ret.='<h2>'. _('Addable Questions').'</h2>';
			$title_arr[] = "&nbsp;";
			$title_arr[] = _('Questions');
			$title_arr[] = "&nbsp;";
			$ret.=$GLOBALS['HTML']->listTableTop ($title_arr);
		}

		for($i = 0;  $i  <  count($arr_to_add);  $i++)  {
			
			if ($arr_to_add[$i]->isError()) {
				echo $arr_to_add[$i]->getErrorMessage();
				continue;
			}

			if ($i%3==0) {
				$ret.= "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($i) .">\n";
			}
			
			$ret.= '<td><input type="checkbox" name="to_add[]" value="'.$arr_to_add[$i]->getID().'" />'.
				$arr_to_add[$i]->getQuestion().' ('.
				$arr_to_add[$i]->getQuestionStringType().')</td>';
			
			if ($i%3==2) {
				$ret.= "</tr>";
			}
		}

		if (count($arr_to_add)>0) {
			/* Fill the remain cells */
			if ($i%3==1) {
				$ret.='<td>&nbsp;</td><td>&nbsp;</td></tr>';
			} else if ($i%3==2) {
				$ret.='<td>&nbsp;</td></tr>';
			}
		
			$ret.= $GLOBALS['HTML']->listTableBottom();
		}
	
		/* Deletable questions */
		if (count($arr_to_del) > 0) {
			$ret.='<p><strong>'. _('Questions in this Survey').'</strong></p>';
			$title_arr = array('Question ID', 'Question', 'Type', 'Order', 'Delete from this Survey');
			$ret.=$GLOBALS['HTML']->listTableTop ($title_arr);
		}
			
		for($i = 0;  $i  <  count($arr_to_del);  $i++)  {
			if ($arr_to_del[$i]->isError()) {
				echo $arr_to_del[$i]->getErrorMessage();
				continue;
			}
			
			$ret.= "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($i) .">\n";
			
			$ret.= '<td>'.$arr_to_del[$i]->getID().'</td>';
			$ret.= '<td>'.$arr_to_del[$i]->getQuestion().'</td>';
			$ret.= '<td>'.$arr_to_del[$i]->getQuestionStringType().'</td>';
			$ret.= '<td><center>['.util_make_link ('/survey/admin/survey.php?group_id='.$group_id.'&amp;survey_id='. $survey_id.'&amp;is_up=1&amp;updown=Y'.'&amp;question_id='.$arr_to_del[$i]->getID(),_('Up')).'] ';
			$ret.= '['.util_make_link ('/survey/admin/survey.php?group_id='.$group_id.'&amp;survey_id='. $survey_id.'&amp;is_up=0&amp;updown=Y'.'&amp;question_id='.$arr_to_del[$i]->getID(),_('Down')).']</center></td>';
			
			$ret.= '<td><center><input type="checkbox" name="to_del[]" value="'.$arr_to_del[$i]->getID().'" /></center></td>';
			$ret.= '</tr>';
			
		}
		
		if (count($arr_to_del)) {
			$ret.= $GLOBALS['HTML']->listTableBottom();
		}
	
		/* Privous style question input text box. deprecated.		
		$ret.= _('List question numbers, in desired order, separated by commas. <strong>Refer to your list of questions</strong> so you can view the question id\'s. Do <strong>not</strong> include spaces or end your list with a comma. <br />Ex: 1,2,3,4,5,6,7');
		$ret.='<br /><input type="text" name="survey_questions" value="" length="90" maxlength="1500" /></p>';
		*/
				
		$ret.='<p><input type="submit" name="submit" value="'.$survey_button.'" /></p>';
		$ret.='</form>';
		
		return $ret;
	}

	/**
	 * Show list of questions
	 */
	function  ShowQuestions(&$questions) {
		global $group_id;
		
		$n = count($questions);
		$ret = "<h2>" . sprintf(ngettext("%d question found", "%d questions found", $n), $n)."</h2>";
					
		/* Head information */
		$title_arr = array ('Question ID', 'Question', 'Type', 'Edit/Delete');
		$ret.=$GLOBALS['HTML']->listTableTop ($title_arr);
				
		for($i = 0;  $i  <  count($questions);  $i++)  {
			if ($questions[$i]->isError()) {
				echo $questions[$i]->getErrorMessage();
				continue;
			}

			$ret.= "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($i) .">\n";
			$ret.= "<td><a href=\"question.php?group_id=$group_id&amp;question_id=".
				$questions[$i]->getID()."\">".$questions[$i]->getID()."</a></td>\n";
			
			$ret.= '<td>'.$questions[$i]->getQuestion().'</td>';
			$ret.= '<td>'.$questions[$i]->getQuestionStringType().'</td>';

			/* Edit/Delete Link */
			$ret.= "<td>[<a href=\"question.php?group_id=$group_id&amp;question_id=".$questions[$i]->getID().'">';
			$ret.= _('Edit').'</a>] ';
			$ret.= "[<a href=\"question.php?delete=Y&amp;group_id=$group_id&amp;question_id=".$questions[$i]->getID().'">';
			$ret.= _('Delete').'</a>]</td>';

			$ret.= "</tr>";
		}
		$ret.= $GLOBALS['HTML']->listTableBottom();
		return $ret;
	}
	
	/**
	 * Show list of surveys
         *
         * Show surveys with many options
         * have to set $user_id to get the right show_vote option
         *
         *
         */
	function  ShowSurveys(&$surveys, $show_id=0, $show_questions=0, 
			      $show_number_questions=0, $show_number_votes=0, 
			      $show_vote=0, $show_edit=0, $show_result=0, 
			      $show_result_graph=0, $show_result_comment=0, 
			      $show_inactive=0 ) {
		global $user_id;
		global $group_id;

		$ret = '<h2>'. ngettext('Existing Survey', 'Existing Surveys', count($surveys)). '</h2>';

		/* Head information */
		if ($show_id) {
			$title_arr[] = _('Survey ID');
		}

		$title_arr[] = _('Survey Title');
		
		if ($show_questions) {
			$title_arr[] = _('Questions');
		}
		if ($show_number_questions) {
			$title_arr[] = _('Number of Questions');
		}
		if ($show_number_votes) {
			$title_arr[] = _('Number of Votes');
		}		
		if ($show_vote && $user_id) {
			$title_arr[] = _('Did I Vote?');
		}
		if ($show_edit) {
			$title_arr[] = _('Edit');
		}
		if ($show_result) {
			$title_arr[] = _('Result');
		}
		if ($show_result_graph) {
			$title_arr[] = _('Result with Graph');
		}
		if ($show_result_comment) {
			$title_arr[] = _('Result with Graph and Comments');
		}

		$ret.=$GLOBALS['HTML']->listTableTop ($title_arr);
			
		/* Color index for table */
		$color_index=0;
		for($i = 0;  $i  <  count($surveys);  $i++)  {
			if ($surveys[$i]->isError()) {
				echo $surveys[$i]->getErrorMessage();
				continue;
			}

			if (!$surveys[$i]->isActive()) {
				if ($show_inactive) {
					$strike_open="<strike>";
					$strike_close="</strike>";
				} else {
					continue;
				}
			} else {
				$strike_open="";
				$strike_close="";
					
			}

			$ret.= "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($color_index++) .">\n";
			if ($show_id) {
				$ret.= '<td>'.$surveys[$i]->getID().'</td>';
			}
			
			$ret.= '<td>'.$strike_open.util_make_link ('/survey/survey.php?group_id='.$group_id.'&amp;survey_id='. $surveys[$i]->getID(), $surveys[$i]->getTitle()). $strike_close.'</td>';

			if ($show_questions) {
				// add a space after comma
				$ret.= '<td>'.str_replace(",", ", ", $surveys[$i]->getQuestionString()).'</td>';
			}
			if ($show_number_questions) {
				$ret.= '<td>'.$surveys[$i]->getNumberOfQuestions().'</td>';
			}
			if ($show_number_votes) {
				$ret.= '<td>'.$surveys[$i]->getNumberOfVotes().'</td>';
			}		
			if ($show_vote && $user_id) {
				if ($surveys[$i]->isUserVote($user_id)) {
					$ret.='<td>'. _('Yes') . '</td>';
				} else {
					$ret.='<td>'. _('No') . '</td>';
				}
			}
			if ($show_edit) {
				/* Edit/Delete Link */
				$ret.= '<td>['.util_make_link ('/survey/admin/survey.php?group_id='.$group_id.'&amp;survey_id='. $surveys[$i]->getID(),_('Edit')).'] ';

				/* We don;t support delete yet. Need to delete all results as well */
				/*
				$ret.= '['.util_make_link ('/survey/admin/survey.php?delete=Y&amp;group_id='.$group_id.'&amp;survey_id='. $surveys[$i]->getID(),_('Delete')).']';
                                */
                                $ret.='</td>';
			}
			if ($show_result) {
				/* Edit/Delete Link */
				$ret.= '<td>['.util_make_link ('/survey/admin/show_results.php?group_id='.$group_id.'&amp;survey_id='. $surveys[$i]->getID(),_('Result')).']</td>';
			}
			if ($show_result_graph) {
				/* Edit/Delete Link */
				$ret.= '<td>['.util_make_link ('/survey/admin/show_results.php?graph=yes&amp;group_id='.$group_id.'&amp;survey_id='.  $surveys[$i]->getID(),_('Result with Graph')).']</td>';
			}
			if ($show_result_comment) {
				/* Edit/Delete Link */
				$ret.= '<td>['.util_make_link ('/survey/admin/show_results.php?graph=yes&amp;show_comment=yes&amp;group_id='.$group_id.'&amp;survey_id='.$surveys[$i]->getID(),_('Result with Graph and Comments')).']</td>';
			}
			$ret.= "</tr>\n";
		}
		
		$ret.= $GLOBALS['HTML']->listTableBottom();
		return $ret;
	}
	
	/**
         * Show survey form - Show all forums of Survey
	 */
	function ShowSurveyForm( &$s ) {
		global $group_id;
		global $survey_id;
		
		if (!$s->isActive()) {
			return '<div class="error">'. _('Error - you can\'t vote for inactive survey').'</div>';
		}
		/* Get questions of this survey */
		$questions = & $s->getQuestionInstances();
		
		$ret="";
		if ($s->isUserVote(user_getid())) {
			$ret.= '<p class="warning_msg">'. _('Warning - you are about to vote a second time on this survey.').'</p>';
		} 
		$ret.= '<form action="/survey/survey_resp.php" method="post">'.
			'<input type="hidden" name="group_id" value="'.$group_id.'" />'.
			'<input type="hidden" name="survey_id" value="'.$survey_id. '" />';

		$ret.= '<table border="0">';

		/* Keep question numbers */
		$index = 1;
		$last_question_type = "";
		for($i = 0; $i < count($questions); $i++)  {
			if ($questions[$i]->isError()) {
				echo $questions[$i]->getErrorMessage();
				continue;
			}
			$question_type = $questions[$i]->getQuestionType();
			$question_id = $questions[$i]->getID();
			$question_title = stripslashes($questions[$i]->getQuestion());
			
			if ($question_type == '4') {
				/* Don't show question number if it's just a comment */
				$ret.='<tr><td valign="top">&nbsp;</td><td>';
			} else {
				$ret.= '<tr><td valign="top"><strong>';
				/* If it's a 1-5 question box and first in series, move Quest number down a bit	*/
				if (($question_type != $last_question_type) && (($question_type == '1') || ($question_type == '3'))) {
					$ret.= '&nbsp;<br />';
				}
				
				$ret.= $index++.'&nbsp;&nbsp;&nbsp;&nbsp;<br /></td><td>';
			}
			
			switch($question_type) {
			case 1: /* This is a radio-button question. Values 1-5.
			  Show the 1-5 markers only if this is the first in a series */
				if ($question_type != $last_question_type) {
					$ret.='	<strong>1</strong>'._('Low').
						'  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>5</strong>' .
						_('High').'<br />';
				}
				
				for ($j=1; $j<=5; $j++) {
					$ret.= '<input type="radio" name="_'.$question_id.'" value="'.$j.'" />';
				}
				
				$ret.= '&nbsp; '.$question_title;
				break;
				
			case 2:	/* This is a text-area question. */
				$ret.= $question_title.'<br />';
				$ret.='<textarea name="_'.$question_id.'" rows="5" cols="60"></textarea>';
				break;
			case 3:	/* This is a Yes/No question. 
                           Show the Yes/No only if this is the first in a series */
				if ($question_type != $last_question_type) {
					$ret.= '<strong>Yes / No</strong><br />';
				}
				
				$ret.='<input type="radio" name="_'.$question_id.'" value="1" />';
				$ret.='<input type="radio" name="_'.$question_id.'" value="5" />';
				$ret.='&nbsp; '.$question_title;
				break;
			case 4:	/* This is a comment only. */
				$ret.= '&nbsp;<br /><strong>'.util_make_links($question_title).'</strong>';
				$ret.= '<input type="hidden" name="_'.$question_id.'" value="-666" />';
				break;
			case 5:	/* This is a text-field question. */
				$ret.= $question_title. '<br />';
				$ret.= '<input type="text" name="_'.$question_id.'" size="20" maxlength="70" />';
				break;
			default:
				$ret.= $question_title. '<br />';
			}
			
			$ret.= '</td></tr>';
			$last_question_type=$question_type;
		}
		
		$ret.='<tr><td style="text-align:center" colspan="2">'.
			'<input type="submit" name="submit" value="'._('Submit').'" />'.
			'<br />'.util_make_link ('/survey/privacy.php',_('Survey Privacy')).
			'</td></tr></form></table>';
		
		return $ret;
	}

		
	/**
         * Show survey Result
         * 
         *    @param Object a Survey Response Factory
	 */
	function ShowResult( &$sr, $show_comment=0, $q_num="", $show_graph=0) {
		global $group_id;

		$Survey = $sr->getSurvey();
		$Question = $sr->getQuestion();

		$ret='<strong>';
		if ($q_num) {
			$ret.= $q_num . '. ';
		}
		
		$ret.=$Question->getQuestion().'</strong><br />';
		$results = $sr->getResults();
		if ($sr->isError()){
			echo ($sr->getErrorMessage());
		}
		
		$totalCount = $sr->getNumberOfSurveyResponses();
		$votes = $Survey->getNumberOfVotes();
		
		/* No votes, no result to show */
		if ($votes==0) {
			$ret.= '<ul><li>'._('No Votes').'</li></ul>';
			return $ret;
		}

		switch($Question->getQuestionType()) {
		case 1: /* This is a radio-button question. Values 1-5.
			  Show the 1-5 markers only if this is the first in a series */
			$arr_name=array('No Answer', 'Low 1', '2', '3', '4', 'High 5', 'No Answer');
			$arr_color=array('black', 'red', 'blue', 'yellow', 'green', 'brown', 'black');
			$results[0] =  $votes - $results[1] - $results[2] - $results[3] - $results[4] - $results[5];
	
			if ($show_graph) {
				$url ='graphs.php?type=vbar';
				for ($j=5; $j>=0; $j--) {
					$percent = sprintf("%02.1f%%", (float)$results[$j]*100/$votes);

					$url.='&amp;legend[]='.urlencode($arr_name[$j].' ('. $percent.')');
					$url.='&amp;value[]='.urlencode($results[$j]);
				}
				$ret.= '<img border="0"  src="'.$url.'" alt="Graph of '.$Question->getQuestion().'"></img>';
			} else {
				$ret.= '<dd><table border="0" cellspacing="0" cellpadding="0" width=100%>';
				
				for ($j=5; $j>=0; $j--) {
					$percent = (float)$results[$j]*100/$votes;
					$ret.= $this->_makeBar($arr_name[$j].' ('.$results[$j].')', $percent, $arr_color[$j]);
				}
				$ret.= '</table></dd>';
			}
			$ret.='<p/>';
			break;

		case 3:	/* This is a Yes/No question. */
			
			$arr_name=array('', 'YES', 'NO', 'No Answer');
			$arr_color=array('', 'red', 'blue', 'black');
			
			$res[1] = $results[1]; /* Yes */
			$res[2] =  $results[5]; /* No */
			$res[3] =  $votes - $res[1] -$res[2];
		
			if ($show_graph) {
				$url ='graphs.php?type=pie';
				for ($j=1; $j<=3; $j++) {
					$url.='&amp;legend[]='.urlencode($arr_name[$j].'('.$res[$j].')');
					$url.='&amp;value[]='.urlencode($res[$j]);
				}
				$ret.= '<img border="0"  src="'.$url.'" alt="Graph of '.$Question->getQuestion().'"></img>';
			} else {
				$ret.= '<dd><table border="0" cellspacing="0" cellpadding="0" width=100%>';
				for ($j=1; $j<=3; $j++) {
					$result_per[$j] = (float)$res[$j]*100/$votes;
					$ret.= $this->_makeBar($arr_name[$j].' ('.$res[$j].')', $result_per[$j], $arr_color[$j]);
				}
				$ret.= '</table></dd>';
			}
			$ret.='<p/>';
			break;
			
		case 4:	/* This is a comment only. */
			break;

		case 2:	/* This is a text-area question. */
		case 5:	/* This is a text-field question. */
			if ($show_comment) {
				for($j=0; $j<$totalCount; $j++) {
					$ret.='<hr /><strong>'._('Comments').
						' # '.($j+1).'/'.$totalCount. '</strong><p/>';
					$ret.='<pre>';
					$words = explode(" ",$results[$j]);
					$i = 0;
					$linelength = 0;
					//print 100 chars in words per line
					foreach ($words as $word) {
						// if we have a stupidly strange word with lots of letters, we'll make a new line for it and split it
						if ( (strlen($word)>100) && ((strlen($word)+$linelength)>100)) {	
							$chunks = $this->split_str($word,50);
							foreach ($chunks as $chunk) {
								$ret .= $chunk;
								$ret .= "<br />";
							}
							$linelength = 0;
						} else { 
							$linelength += strlen($word);
							if ($linelength>100) {
								$ret .= "<br />";
								$linelength = 0;
							} else {
								$ret .= $word . " ";
							}
						}
					}
					$ret.='</pre>';
				}
			} else {
				$ret.='<ul><li><a href="show_results.php?survey_id='.$Survey->getID().
					'&amp;question_id='.$Question->getID().
					'&amp;group_id='.$group_id.'">'.
					sprintf(ngettext('View All %1$s Comment', 'View All %1$s Comments', $totalCount), $totalCount).
					'</a></ul><p/>';
			}
		
			break;
		default:
			break;
		}
	
		return $ret;
	} 

	/**
         * split_str - works as str_split of PHP5 -  Converts a string to an array.
	 *
	 * @param String str
         * @param int length of chunk
	 * @return array array of chunks of the string
	 */
		function split_str($str,$split_lengt=1) {
			$cnt = strlen($str);
			for ($i=0;$i<$cnt;$i+=$split_lengt) {
				$rslt[]= substr($str,$i,$split_lengt);
			}
	 		return $rslt;
		}
	
	/**
         * _makeBar - make Precentage bar as a cell in a table. Starts with <tr> and ends with </tr>
	 *
	 * @param String name Name
         * @param int percentage of the name
	 * @return string
	 */
	function _makeBar($name, $percent, $color) {
		$ret = '<tr><td width="30%">'.$name.'</td><td>';
		$ret.= '<table width="'.$percent.'%" border="0"  cellspacing="0" cellpadding="0"><tr>';
		if ($percent) {
			$ret.='<td width="90%" bgcolor="'.$color.'">&nbsp;</td>';
		}
		
		$ret.= '<td>'.sprintf("%.2f", $percent).'%</td></tr></table></td></tr>'."\n";
		
		return $ret;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

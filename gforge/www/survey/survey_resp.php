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


require_once('../env.inc.php');
require_once('pre.php');
require_once('common/survey/Survey.class.php');
require_once('common/survey/SurveyResponse.class.php');
require_once('www/survey/include/SurveyHTML.class.php');

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$sh = new  SurveyHtml();
$sh->header(array('title'=>_('Survey Complete')));

if (!$survey_id) {
	/*
		Quit if params are not provided
	*/
	echo "<h1>"._('Error - For some reason group_id and/or survey_id did not make it he')."</h1>";
	$sh->footer(array());
	exit;
}

if (!session_loggedin()) {
	/*
		Tell them they need to be logged in
	*/
	echo "<h1>"._('You need to be logged in')."</h1>";
	echo "<p>"._('Unfortunately, you have to be logged in to participate in surveys.')."</p>";
	$sh->footer(array());
	exit;
}

?>

<p><?php echo _('Thank you for taking time to complete this survey.'); ?></p>
<p>&nbsp;</p>
<?php echo _('Regards'); ?>,
<p>&nbsp;</p>
<strong><?php printf(_('The %1$s Crew'), $GLOBALS['sys_name']); ?></strong>
<p>&nbsp;</p>
<?php
/*
	Delete this customer's responses in case they had back-arrowed
*/
$result=db_query("DELETE FROM survey_responses WHERE survey_id='" . addslashes($survey_id) . "' AND group_id='" . addslashes($group_id) . "' AND user_id='".user_getid()."'");

/*
	Select this survey from the database
*/
$s = new Survey($g, $survey_id);
$quest_array= & $s->getQuestionArray();

$count=count($quest_array);
$now=time();

/* Make a dummy SurveyResponses for creating */
$sr = new SurveyResponse($g);

for ($i=0; $i<$count; $i++) {
    /*	Insert each form value into the responses table */
    
    $val="_" . $quest_array[$i];
    $response = getStringFromRequest($val);;

    $sr->create(user_getid(), $survey_id, $quest_array[$i], $response);
    if ($sr->isError()) {
	echo $sr->getErrorMessage();
    }
}

$sh->footer(array());

?>

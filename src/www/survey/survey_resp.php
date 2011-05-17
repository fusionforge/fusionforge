<?php
/**
 * Survey Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
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


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'survey/Survey.class.php';
require_once $gfcommon.'survey/SurveyResponse.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$sh = new SurveyHtml();
$title = _('Survey Complete');
$sh->header(array('title'=>$title));

if (!$survey_id) {
	/*
		Quit if params are not provided
	*/
	echo '<div class="error">'._('For some reason, the Project ID or Survey ID did not make it to this page').'</div>';
	$sh->footer(array());
	exit;
}

if (!session_loggedin()) {
	exit_not_logged_in();
}

echo '<p>' . _('Thank you for taking time to complete this survey.') . '</p>';
echo '<p>' . _('Regards,') . '</p>';
echo '<p><strong>';
printf(_('The %1$s Crew'), forge_get_config ('forge_name'));
echo '</strong></p>';

/*
	Delete this customer's responses in case they had back-arrowed
*/
$result = db_query_params ('DELETE FROM survey_responses WHERE survey_id=$1 AND group_id=$2 AND user_id=$3',
			   array($survey_id,
				 $group_id,
				 user_getid()));
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

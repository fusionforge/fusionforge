<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$sh = new  SurveyHtml();
$sh->header(array('title'=>_('Survey Administration')));

if (!$group_id) {
    exit_no_group();
}
$group=group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
    exit_error('Error', $group->getErrorMessage());
}

echo '<h1>'._('Survey Administration').'</h1>';

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<div class="error">'._('Permission denied').'</div>';
	$sh->footer(array());
	exit;
}

?>

<p>
<?php print(_('It\'s simple to create a survey.')); ?>
</p>
<ol>
    <li>
    <?php print(_('Create questions and comments using the forms above.')); ?>
    </li>
    <li>
    <?php print(_('Create a survey, listing the questions in order (choose from <strong>your</strong> list of questions).')); ?>
    </li>
    <li>
    <?php printf(_('Link to the survey using this format: %1$s where XX is the survey number'),
                 '<br /><strong>'.util_make_url('/survey/survey.php?group_id='.$group_id.'&amp;survey_id=XX').'</strong>'); ?>
    </li>
</ol>
<p>
<?php printf(_('You can now activate/deactivate surveys on the %1$s Edit Existing Surveys %2$s page'),
             '<a href="'.util_make_url('/survey/admin/survey.php?group_id='.$group_id).'">',
             '</a>');
?>
</p>

<?php

$sh->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

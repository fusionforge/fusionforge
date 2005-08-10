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

require_once('pre.php');
require_once('www/survey/include/SurveyHTML.class');

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$sh = new  SurveyHtml();
$sh->header(array('title'=>$Language->getText('survey_admin_index','title'),'pagename'=>'survey_admin'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<h1>'.$Language->getText('survey_admin_index','permission_denied').'</h1>';
	$sh->footer(array());
	exit;
}

?>

<?php echo $Language->getText('survey_admin_index','its_simple_to_create', array('<p><strong>/survey/survey.php?group_id='.$group_id.'&survey_id=XX</strong>')); ?>

<p><?php echo $Language->getText('survey_admin_index','you_can_now_activate',array('<a href="/survey/admin/survey.php?group_id='.$group_id.'">','</a>')); ?>

</p>
<p>
<?php

$sh->footer(array());

?>
</p>

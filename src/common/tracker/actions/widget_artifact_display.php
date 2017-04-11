<?php
/**
 * Tracker Widget Artifact Display page
 *
 * Copyright 2016 Franck Villaume - TrivialDev
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

require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

global $ath;
global $ah;
global $group_id;
global $group;
global $aid;
global $atid;
global $HTML;

if (!isset($func)) {
	$func = getStringFromRequest('func');
}

html_use_jqueryui();
html_use_jquerydatetimepicker();
use_javascript('/widgets/scripts/WidgetController.js');
use_javascript('/tracker/scripts/js-ie-fix.js');
if ($func == 'add') {
	$ath->header(array('title' => _('Submit New'), 'modal' => 1));
} elseif ($func == 'detail') {
	$ath->header(array('title'=> $ah->getStringID().' '. $ah->getSummary(), 'atid'=>$ath->getID()));
}

echo $HTML->openForm(array('id' => 'trackerform', 'name' => 'trackerform', 'action' => '/tracker/?group_id='.$group_id.'&atid='.$ath->getID(), 'enctype' => 'multipart/form-data', 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key(), 'form' => 'trackerform'));
if ($func == 'add') {
	echo html_e('input', array('type' => 'hidden', 'name' => 'func', 'value' => 'postadd', 'form' => 'trackerform'));
} elseif ($func == 'detail') {
	echo html_e('input', array('type' => 'hidden', 'name' => 'func', 'value' => 'postmod', 'form' => 'trackerform'));
	echo html_e('input', array('type' => 'hidden', 'name' => 'artifact_id', 'value' => $ah->getID(), 'form' => 'trackerform'));
}
echo $HTML->closeForm();
$lm = new WidgetLayoutManager();
$lm->displayLayout($atid, WidgetLayoutManager::OWNER_TYPE_TRACKER);
$ath->footer();

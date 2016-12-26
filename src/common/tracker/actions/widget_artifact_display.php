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

$lm = new WidgetLayoutManager();
$sql = "SELECT l.* FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
		WHERE o.owner_type = $1
		AND o.owner_id = $2
		AND o.is_default = 1";
$res = db_query_params($sql,array('t', $atid));
if($res && db_numrows($res) < 1) {
	$lm->createDefaultLayoutForTracker($atid);
	$res = db_query_params($sql,array('t', $atid));
}
$id = db_result($res, 0 , 'id');

html_use_jqueryui();
html_use_jquerydatetimepicker();
if ($func == 'add') {
	$ath->header(array('title' => _('Submit New'), 'modal' => 1));
} elseif ($func == 'detail') {
	$ath->header(array('title'=> $ah->getStringID().' '. $ah->getSummary(), 'atid'=>$ath->getID()));
}

if (forge_check_perm('tracker_admin', $atid)) {
	$url = '/widgets/widgets.php?owner=t'.$atid.'&layout_id='.$id;
	$labels = array(_('Add widgets'), _('Customize Layout'));
	$urls = array($url, $url.'&update=layout');
	$attrs = array();
	$attrs[] = array('title' => _('Customfields must be linked to a widget to be displayed. Use “Add widgets” to create new widget to link and organize your customfields.'));
	$attrs[] = array('title' => _('General layout to display “Submit New” form or detailed view of an existing artifact can be customize. Use “Customize Layout” to that purpose.'));
	$elementsLi = array();
	for ($i = 0; $i < count($urls); $i++) {
		$elementsLi[] = array('content' => util_make_link($urls[$i], $labels[$i]), 'attrs' => $attrs[$i]);
	}
	echo $HTML->html_list($elementsLi, array('class' => 'widget_toolbar'));
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
$lm->displayLayout($atid, WidgetLayoutManager::OWNER_TYPE_TRACKER);
$ath->footer();

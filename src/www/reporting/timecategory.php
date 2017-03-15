<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/ReportSetup.class.php';

session_require_global_perm ('forge_stats', 'admin') ;

$time_code = getIntFromRequest('time_code');
$category_name = trim(getStringFromRequest('category_name'));

if (getStringFromRequest('submit')) {
	if (getStringFromRequest('add')) {

		$r = new ReportSetup();
		if (!$r->addTimeCode($category_name)) {
			$error_msg = $r->getErrorMessage();
		} else {
			$feedback = _('Successfully Added');
		}

	} elseif (getStringFromRequest('update')) {

		$r = new ReportSetup();

		if (!$r->updateTimeCode($time_code,$category_name)) {
			$error_msg = $r->getErrorMessage();
		} else {
			$feedback = _('Successfully Updated');
		}

		$time_code=false;
		$category_name='';
	}
}

report_header(_('Manage Time Tracker Categories'));

if ($time_code) {
	$res1=db_query_params ('SELECT * FROM rep_time_category WHERE time_code=$1',
			array($time_code));
	$category_name=db_result($res1,0,'category_name');
}
$res = db_query_params('SELECT * FROM rep_time_category ORDER BY time_code', array());

$arr[]=_('Time Code');
$arr[]=_('Category Name');

echo $HTML->listTableTop($arr);

for ($i=0; $i<db_numrows($res); $i++) {
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'><td>'.db_result($res,$i,'time_code').'</td>
		<td><a href="timecategory.php?time_code='.db_result($res,$i,'time_code').'">'.db_result($res,$i,'category_name').'</a></td></tr>';
}

echo $HTML->listTableBottom();

?>
<p>
<?php echo _('You can create categories for how time might be spent when completing a particular task. Examples of categories include “Meeting”, “Coding”, “Testing”.'); ?>
</p>
<?php echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post')); ?>
<p>
<input type="hidden" name="submit" value="1" />
<input type="hidden" name="time_code" value="<?php echo $time_code; ?>" />
<label for="category_name">
<strong><?php echo _('Category Name')._(':'); ?></strong><br />
<input required="required" type="text" id="category_name" name="category_name"
	   value="<?php echo $category_name; ?>" />
</label>
</p>
<p>
<?php

if ($time_code) {
	echo '<input type="submit" name="update" value="'._('Update').'" />';
} else {
	echo '<input type="submit" name="add" value="'._('Add').'" />';
}

?>
</p>
<?php
echo $HTML->closeForm();

report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
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
			exit_error($r->getErrorMessage());
		} else {
			$feedback=_('Successful');
		}

	} elseif (getStringFromRequest('update')) {

		$r = new ReportSetup();

		if (!$r->updateTimeCode($time_code,$category_name)) {
			exit_error($r->getErrorMessage());
		} else {
			$feedback=_('Successful');
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
$res=db_query_params ('SELECT * FROM rep_time_category',
			array());

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
<?php echo _('You can create categories for how time might be spent when completing a particular task. Examples of categories include "Meeting", "Coding", "Testing".'); ?>
</p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<p>
<input type="hidden" name="submit" value="1" />
<input type="hidden" name="time_code" value="<?php echo $time_code; ?>" />
<strong><?php echo _('Category Name'); ?>:</strong><br />
<input type="text" name="category_name" value="<?php echo $category_name; ?>" />
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
</form>

<?php

report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

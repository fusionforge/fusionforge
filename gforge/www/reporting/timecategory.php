<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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
require_once('common/reporting/report_utils.php');
require_once('common/reporting/ReportSetup.class');

session_require( array('group'=>$sys_stats_group,'A') );
global $Language;

$time_code = getStringFromRequest('time_code');
$category_name = getStringFromRequest('category_name');

if (getStringFromRequest('submit')) {
	if (getStringFromRequest('add')) {

		$r = new ReportSetup();
		if (!$r->addTimeCode($category_name)) {
			exit_error('Error',$r->getErrorMessage());
		} else {
			$feedback=$Language->getText('reporting_tc','successful');
		}

	} elseif (getStringFromRequest('update')) {

		$r = new ReportSetup();

		if (!$r->updateTimeCode($time_code,$category_name)) {
			exit_error('Error',$r->getErrorMessage());
		} else {
			$feedback=$Language->getText('reporting_tc','successful');
		}

		$time_code=false;
		$category_name='';
	}

}

echo report_header($Language->getText('reporting_tc','title'));

if ($time_code) {
	$res1=db_query("SELECT * FROM rep_time_category WHERE time_code='$time_code'");
	$category_name=db_result($res1,0,'category_name');
}
$res=db_query("SELECT * FROM rep_time_category");

$arr[]=$Language->getText('reporting_tc','time_code');
$arr[]=$Language->getText('reporting_tc','category_name');

echo $HTML->listTableTop($arr);

for ($i=0; $i<db_numrows($res); $i++) {
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'><td>'.db_result($res,$i,'time_code').'</td>
		<td><a href="timecategory.php?time_code='.db_result($res,$i,'time_code').'">'.db_result($res,$i,'category_name').'</a></td></tr>';
}

echo $HTML->listTableBottom();

?>
<h3><?php echo $Language->getText('reporting_tc','title2'); ?></h3>
<p>
<?php echo $Language->getText('reporting_tc','description'); ?>
<p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="submit" value="1" />
<input type="hidden" name="time_code" value="<?php echo $time_code; ?>" />
<strong><?php echo $Language->getText('reporting_tc','category_name'); ?>:</strong><br />
<input type="text" name="category_name" value="<?php echo $category_name; ?>" >
<p>
<?php

if ($time_code) { 
	echo '<input type="submit" name="update" value="'.$Language->getText('reporting','update').'" />';
} else {
	echo '<input type="submit" name="add" value="'.$Language->getText('reporting','add').'" />';
}

?>
</form>

<?php

echo report_footer();

?>

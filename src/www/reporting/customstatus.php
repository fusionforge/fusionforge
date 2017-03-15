<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2015, nitendra tripathi
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

$status_id = getIntFromRequest('status_id');
$status_name = trim(getStringFromRequest('status_name'));

if (getStringFromRequest('submit')) {
	if (getStringFromRequest('add')) {

		$r = new ReportSetup();
		if (!$r->addStatusId($status_name)) {
			$error_msg = $r->getErrorMessage();
		} else {
			$feedback = _('Successfully Added');
		}

	} elseif (getStringFromRequest('update')) {

		$r = new ReportSetup();

		if (!$r->updateStatusID($status_id,$status_name)) {
			$error_msg = $r->getErrorMessage();
		} else {
			$feedback = _('Successfully Updated');
		}

		$status_id=false;
		$status_name='';
	}
}

report_header(_('Manage Project Task&apos;s Statuses'));

if ($status_id) {
	$res1=db_query_params ('SELECT * FROM project_status WHERE status_id=$1',
			array($status_id));
	$status_name=db_result($res1,0,'status_name');
}
$res = db_query_params('SELECT * FROM project_status ORDER BY status_id', array());

$arr[]=_('Status Id');
$arr[]=_('Status Name');

if ($status_id) {
	echo '[ '.util_make_link('/reporting/customstatus.php', _('Add')). ' ]';
}

echo $HTML->listTableTop($arr);

for ($i=0; $i<db_numrows($res); $i++) {
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'><td>'.db_result($res,$i,'status_id').'</td>
		<td>'.util_make_link('/reporting/customstatus.php?status_id='.db_result($res,$i,'status_id'), db_result($res,$i,'status_name')).'</td></tr>';
}

echo $HTML->listTableBottom();

?>
<p>
<?php echo _('You can create statuses to classify a particular task&apos;s status. Examples of statuses include &quot;Open&quot;, &quot;Close&quot;, &quot;Deleted&quot;.'); ?>
</p>
<?php echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post')); ?>
<p>
<input type="hidden" name="submit" value="1" />
<input type="hidden" name="status_id" value="<?php echo $status_id; ?>" />
<label for="status_name">
<strong><?php echo _('Status Name')._(':'); ?></strong><br />
<input required="required" type="text" id="status_name" name="status_name"
	   value="<?php echo $status_name; ?>" />
</label>
</p>
<p>
<?php

if ($status_id) {
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

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
require_once('common/reporting/Report.class');
require_once('common/reporting/ReportSetup.class');

session_require( array('group'=>$sys_stats_group,'A') );

global $Language;

	echo report_header($Language->getText('reporting_rebuild','title'));
if (getStringFromRequest('submit') && getStringFromRequest('im_sure')) {
		


	$r = new ReportSetup();

	if (!$r->initialSetup()) {
		echo $r->getErrorMessage();
		form_release_key(getStringFromRequest("form_key"));
	} else {
		Header("Location: index.php?feedback=Successfully+Rebuilt");
	}

}
	
	echo $Language->getText('reporting_rebuild','message');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<input type="checkbox" name="im_sure" value="1"><?php echo $Language->getText('reporting_rebuild','imsure'); ?><p>
<p>
<input type="submit" name="submit" value="<?php echo $Language->getText('reporting_rebuild','pressonlyonce'); ?>">
</form>

<?php

echo report_footer();

?>

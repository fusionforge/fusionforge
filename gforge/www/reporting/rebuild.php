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

require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');
require_once('common/reporting/ReportSetup.class');

session_require( array('group'=>$sys_stats_group,'A') );

echo report_header('Main Page');

if ($submit && $im_sure) {

	$r = new ReportSetup();

	if (!$r->initialSetup()) {
		echo $r->getErrorMessage();
	} else {
		Header("Location: index.php?feedback=Successfully+Rebuilt");
	}

}

?>
<h3>Reporting System Initialization</h3>
<p>
Occasionally, if cronjobs failed or the database was damaged, 
you may have to rebuild the reporting tables.
<p>
If you are sure you want to rebuild all the reporting tables, 
check the "I'm Sure" box and click the button below.
<p>
This could take a couple minutes, so DO NOT CLICK MORE THAN ONCE.
<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="checkbox" name="im_sure" value="1">I'm Sure<p>
<p>
<input type="submit" name="submit" value="Press ONLY ONCE">
</form>

<?php

echo report_footer();

?>

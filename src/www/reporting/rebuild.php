<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright 2010 (c) Franck Villaume
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
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/ReportSetup.class.php';

session_require_global_perm ('forge_stats', 'admin') ;

if (getStringFromRequest('submit') && getStringFromRequest('im_sure')) {

	$r = new ReportSetup();

	if (!$r->initialSetup()) {
		$error_msg = $r->getErrorMessage();
		form_release_key(getStringFromRequest("form_key"));
	} else {
		$feedback = _('Successfully Rebuilt');
	}

}

report_header(_('Reporting System Initialization'));

echo '<p>';
echo _('Occasionally, if cronjobs failed or the database was damaged, you may have to rebuild the reporting tables.');
echo '</p>';
echo '<p>';
echo _('If you are sure you want to rebuild all the reporting tables, check the "I am sure" box and click the button below.');
echo '</p>';
echo '<p>';
echo _('This could take a couple minutes, so DO NOT CLICK MORE THAN ONCE.');
echo '</p>';
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<p>
<input type="checkbox" name="im_sure" value="1" /><?php echo _('I am sure'); ?>
</p>
<p>
<input type="submit" name="submit" value="<?php echo _('Press ONLY ONCE'); ?>" />
</p>
</form>

<?php

report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

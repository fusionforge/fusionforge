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


require_once('../env.inc.php');
require_once('pre.php');
require_once('vote_function.php');
require_once('www/survey/survey_utils.php');

echo $HTML->header(array('title'=>_('Survey')));

?>

<h1><?php echo _('Survey Privacy'); ?></h1>
<?php echo _('<p>The information collected in these surveys will never be sold to third parties or used to solicit you to purchase any goods or services.</p><p>This information is being gathered to build a profile of the projects and developers being surveyed. That profile will help visitors to the site understand the quality of a given project.</p><p>The ID\'s of those who answer surveys are suppressed and not viewable by project administrators or the public or third parties.</p><p>The information gathered is used only in aggregate form, not to single out specific users or developers.</p><p>If any changes are made to this policy, it will affect only future data that is collected and the user will of course have the ability to \'opt-out\'.'); ?>
</p>

<p><strong><?php printf(_('The %1$s Team'), $GLOBALS['sys_name']); ?></strong></p>

<?php

echo $HTML->footer(array());

?>

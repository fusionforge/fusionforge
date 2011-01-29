<?php
/**
 * Survey Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
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
require_once $gfcommon.'survey/SurveyFactory.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

/* Show header */
$title = _('Survey Privacy');
$sh = new SurveyHtml();
$sh->header(array('title'=>$title));

echo '<p>';
echo _("The information collected in these surveys will never be sold to third parties or used to solicit you to purchase any goods or services.");
echo '</p>';
echo '<p>';
echo _("This information is being gathered to build a profile of the projects and developers being surveyed. That profile will help visitors to the site understand the quality of a given project.");
echo '</p>';
echo '<p>';
echo _("The ID's of those who answer surveys are suppressed and not viewable by project administrators or the public or third parties.");
echo '</p>';
echo '<p>';
echo _("The information gathered is used only in aggregate form, not to single out specific users or developers.");
echo '</p>';
echo '<p>';
echo _("If any changes are made to this policy, it will affect only future data that is collected and the user will of course have the ability to 'opt-out'."); 
echo '</p>';

?>

<p><strong><?php printf(_('The %1$s Team'), forge_get_config ('forge_name')); ?></strong></p>

<?php echo $HTML->footer(array()); ?>

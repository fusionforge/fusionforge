<?php
/**
 * Tracker Links
 *
 * Copyright 2010, FusionForge Team
 * Copyright 2012, Franck Villaume - TrivialDev
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

global $group_id;
global $ath;

//
//  SHOW LINKS TO FUNCTIONS
//
$ath->header(array ('title'=>_('Administration of tracker')._(': ').$ath->getName()));

echo '<p>';
echo util_make_link('/tracker/admin/?group_id='.$group_id, '<strong>'._('New Tracker').'</strong>');
echo '<br />';
echo _('Create a new tracker.');
echo '</p>';

echo '<p>';
echo util_make_link('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_type=1', '<strong>'._('Update Settings').'</strong>');
echo '<br />';
echo _('Set up preferences like expiration times, email addresses.');
echo '</p>';

echo '<p>';
echo util_make_link('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_extrafield=1', '<strong>'._('Manage Custom Fields').'</strong>');
echo '<br />';
echo _('Add new boxes like Phases, Quality Metrics, Components, etc.  Once added they can be used with other selection boxes (for example, Categories or Groups) to describe and browse bugs or other artifact types.');
echo '</p>';

echo '<p>';
echo util_make_link('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;workflow=1', '<strong>'._('Manage Workflow').'</strong>');
echo '<br />';
echo _('Edit tracker workflow.');
echo '</p>';

echo '<p>';
echo util_make_link('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;customize_list=1', '<strong>'._('Customize List').'</strong>');
echo '<br />';
echo _('Customize display for the tracker.');
echo '</p>';

echo '<p>';
echo util_make_link('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_canned=1', '<strong>'._('Manage Canned Responses').'</strong>');
echo '<br />';
echo _('Create/change generic response messages for the tracker.');
echo '</p>';

echo '<p>';
echo util_make_link ('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;clone_tracker=1', '<strong>'._('Apply Template Tracker').'</strong>');
echo '<br />';
echo _('Duplicate parameters and fields from a template trackers in this one.');
echo '</p>';

echo '<p>' . util_make_link ('/tracker/admin/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;delete=1', '<strong>'._('Delete').'</strong>');
echo '<br />';
echo _('Permanently delete this tracker.');
echo '</p>';

$ath->footer(array());

<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2014,2016 Franck Villaume - TrivialDev
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
global $mantisbt;
global $HTML;

echo '<div style="width:98%; text-align:right; padding:5px;" >';
echo $HTML->openForm(array('name' => 'jump',  'method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&view=viewIssue'));
echo '<span>'. _('Jump to ticket')._(':'). '</span>';
echo '<input type="text" name="idBug" />';
echo '<input type="submit" value="'._('Ok').'" />';
echo $HTML->closeForm();
echo '</div>';

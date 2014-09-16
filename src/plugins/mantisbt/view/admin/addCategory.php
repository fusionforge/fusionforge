<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2014, Franck Villaume - TrivialDev
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

global $HTML;
global $group_id;
global $mantisbt;
/* add category to a dedicated project */

echo $HTML->openForm(array('method' => 'post', 'name' => 'addCategory', 'action' => util_make_uri('/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=addCategory')));
echo $HTML->boxTop(_('Add a new category'));
echo '<table><tr>';
echo '<td><label >'._('Name').'</label><input name="nameCategory" type="text" /></td>';
echo '<td>';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo '</tr></table>';
echo $HTML->boxBottom();
echo $HTML->closeForm();

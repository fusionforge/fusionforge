<?php

/*
 * Copyright 2010, Capgemini
 * Author: Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/* add a new version */

echo '<form method="POST" name="addVersion" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=addVersion">';
echo $HTML->boxTop('Ajouter une version');
echo '<td>';
echo '<input name="version" type="text"></input>';
echo '<input name="transverse" type="checkbox" value="1" >version transverse (fils inclus)</input>';
echo '</td>';
echo '<td>';
print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
      <div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
      <a href="javascript:document.addVersion.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Ajouter</a>
      </div>
      <div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
echo '</td>';
echo $HTML->boxBottom();
echo '</form>';
?>

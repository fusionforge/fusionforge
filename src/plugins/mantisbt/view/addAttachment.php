<?php

/*
 * Copyright 2010, Capgemini
 * Authors: Franck Villaume - capgemini
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

echo '<form method="POST" Action="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&action=addAttachment&view=viewIssue" enctype="multipart/form-data">';
echo	'<table class="innertabs">';
echo '<tr><td>';
echo '     Fichier : <input type="file" name="attachment">';
echo '</td></tr></table>';
echo '<br/><input type="button" onclick="this.form.submit();this.disabled=true;" name="envoyer" value="Envoyer le fichier">';
echo '</form>';

?>

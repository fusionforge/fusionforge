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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

echo '<div style="width:98%; text-align:right; padding:5px;" >';
echo '<form name="jump" method="post" action="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&view=viewIssue">';
echo '<span>Aller au ticket :</span>';
echo '<input type="text" name="idBug">';
echo '<input type="submit" value="Ok" />';
echo '</form>';
echo '</div>';
?>

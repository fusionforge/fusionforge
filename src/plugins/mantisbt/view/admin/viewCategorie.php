<?php
/*
 * Copyright 2010, Franck Villaume - Capgemini
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

/* view categorie in MantisBt for the dedicated group */
global $HTML;
global $idProjetMantis;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->boxTop('Gestion des categories');
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo			'<td class="FullBoxTitle">Catégorie</td>';
	echo			'<td colspan="3" class="FullBoxTitle">Actions</td>';
	echo		'</tr>';
	$i = 0;
	foreach ($listCategories as $key => $category){ 
		if ( $i % 2 == 0 ) {
			echo '<tr class="LignePaire">';
		} else {
			echo '<tr class="LigneImpaire">';
		}
		if ( $category != 'General' ) {
			echo '<td class="InText">'.$category.'</td>';
			echo '<td>';

			echo '<form method="POST" name="rename'.$i.'" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=renameCategory">';
			echo '<input type="hidden" name="renameCategory" value="'.htmlspecialchars($category).'" />';
			echo '<input name="newCategoryName" type="text"></input>';
			echo '</td><td>';
			print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
				<div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
				<a href="javascript:document.rename'.$i.'.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Renommer</a>
				</div>
				<div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
			echo '</td>';
			echo '</form>';
			echo '<td class="InText">';
			echo '<form method="POST" name="delete'.$i.'" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=deleteCategory">';
			echo '<input type="hidden" name="deleteCategory" value="'.htmlspecialchars($category).'" />';
			print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
				<div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
				<a href="javascript:document.delete'.$i.'.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Supprimer</a>
				</div>
				<div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
			echo '</form>';
			echo '</td></tr>';
			$i++;
		}
	}
	echo '</table>';
	echo $HTML->boxBottom();
}
?>

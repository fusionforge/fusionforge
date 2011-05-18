<?php
/*
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/* view categorie in MantisBt for the dedicated group */
global $HTML;
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Manage categories'));
	// General category is shared so no edit...
	if (count($listCategories) > 1) {
		echo	'<table>';
		echo		'<tr>';
		echo			'<td>'._('Category').'</td>';
		echo			'<td colspan="2">'._('Actions').'</td>';
		echo		'</tr>';
		$i = 1;
		foreach ($listCategories as $key => $category){
			echo '<tr '.$HTML->boxGetAltRowStyle($i).'">';
			if ( $category != 'General' ) {
				echo '<td>'.$category.'</td>';
				echo '<td>';
				echo '<form method="POST" action="?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=renameCategory">';
				echo '<input type="hidden" name="renameCategory" value="'.htmlspecialchars($category).'" />';
				echo '<input name="newCategoryName" type="text"></input>';
				echo '<input type="submit" value="'._('Rename').'" />';
				echo '</td>';
				echo '</form>';
				echo '<td>';
				echo '<form method="POST" action="?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=deleteCategory">';
				echo '<input type="hidden" name="deleteCategory" value="'.htmlspecialchars($category).'" />';
				echo '<input type="submit" value="'._('Delete').'" />';
				echo '</form>';
				echo '</td></tr>';
				$i++;
			}
		}
		echo '</table>';
	} else {
		echo '<p class="warning">'._('No Categories').'</p>';
	}
	echo $HTML->boxBottom();
}
?>

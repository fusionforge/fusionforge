<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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

/* view categorie in MantisBt for the dedicated group */
global $HTML;
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP)) {
		$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
	}
	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt']));
} catch (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Manage categories'));
	// General category is shared so no edit...
	if (count($listCategories) > 1) {
		echo	$HTML->listTableTop();
		echo		'<tr>';
		echo			'<td>'._('Category').'</td>';
		echo			'<td colspan="2">'._('Actions').'</td>';
		echo		'</tr>';
		foreach ($listCategories as $key => $category){
			$cells = array();
			if ( $category != 'General' ) {
				$cells[][] = $category;
				$cells[][] = $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=renameCategory')).
						'<input type="hidden" name="renameCategory" value="'.htmlspecialchars($category).'" />'.
						'<input name="newCategoryName" type="text" />'.
						'<input type="submit" value="'._('Rename').'" />'.
						$HTML->closeForm();
				$cells[][] = $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=deleteCategory')).
						'<input type="hidden" name="deleteCategory" value="'.htmlspecialchars($category).'" />'.
						'<input type="submit" value="'._('Delete').'" />'.
						$HTML->closeForm();
			}
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	} else {
		echo $HTML->information(_('No Categories'));
	}
	echo $HTML->boxBottom();
}

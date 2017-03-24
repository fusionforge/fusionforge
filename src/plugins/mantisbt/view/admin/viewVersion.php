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

/* view version of a dedicated group in MantisBt */

/* main display */
global $HTML;
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));

	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt']));
} catch  (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Manage versions'));
	if (sizeof($listVersions)) {
		$titleArr = array(_('Version'), _('Description'), _('Target Date'), _('Type'), _('Actions'));
		echo $HTML->listTableTop($titleArr);
		foreach ($listVersions as $key => $version){
			$cells = array();
			$cells[][] = $version->name;
			(isset($version->description))? $description_value = $version->description : $description_value = '';
			$cells[][] = $description_value;
			$cells[][] = strftime(_("%d/%m/%Y"),strtotime($version->date_order));
			if ( $version->released ) {
				$cells[][] = _('Release');
			} else {
				$cells[][] = _('Milestone');
			}
			$cells[][] = util_make_link('/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&view=editVersion&idVersion='.$version->id, _('Update'));
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	} else {
		echo $HTML->information(_('No versions'));
	}
	echo $HTML->boxBottom();
}

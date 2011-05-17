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

/* view version of a dedicated group in MantisBt */

/* main display */
global $HTML;

try {
    /* do not recreate $clientSOAP object if already created by other pages */
    if (!isset($clientSOAP))
        $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

    $listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
} catch  (SoapFault $soapFault) {
    echo    '<div class="warning" >Un problème est survenu lors de la récupération des données : '.$soapFault->faultstring.'</div>';
    $errorPage = true;
}

if (!isset($errorPage)){
    echo $HTML->boxTop('Gestion des Versions');
    echo '<table class="innertabs">';
    echo	'<tr>';
    echo            '<td class="FullBoxTitle">Version</td>';
    echo		'<td class="FullBoxTitle">Date Livraison</td>';
    echo		'<td class="FullBoxTitle">Type</td>';
    echo 		'<td class="FullBoxTitle">Action</td>';
    echo	'</tr>';
    $i = 0;
    foreach ($listVersions as $key => $version){
	    if ( $i % 2 == 0 ) {
		    echo '<tr class="LignePaire">';
	    } else {
		    echo '<tr class="LigneImpaire">';
	    }
	    echo '<td class="InText">'.$version->name.'</td>';
	    echo '<td class="InText">'.strftime("%d/%m/%Y",strtotime($version->date_order)).'</td>';
	    /* est-ce une version release ? */
	    if ( $version->released ) {
		    echo '<td class="InText">Release</td>';
	    /* juste une milestone alors */
	    } else {
		    echo '<td class="InText">Milestone</td>';
	    }
	    echo '<td>';
	    print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
		    <div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
		    <a href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&view=editVersion&idVersion='.$version->id.'" style="color:white;font-size:0.8em;font-weight:bold;">Modifier</a>
		    </div>
		    <div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
	    echo '</td></tr>';
	    $i++;
    }
    echo '</table>';
    echo $HTML->boxBottom();
}

?>

<?php

/*
 * Copyright 2010, Capgemini
 * Author: Franck Villaume -- Capgemini
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

/* main display */
global $HTML;

$idVersion=getIntFromRequest('idVersion');

try {
    $clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
    $detailVersion = $clientSOAP->__soapCall('mc_project_get_version_details', array("username" => $username, "password" => $password, "version_id" => $idVersion));
} catch (SoapFault $soapFault) {
    echo $soapFault->faultstring;
    echo "<br/>";
    $errorPage = true;
}

if ($errorPage){
    echo    '<div>Un problème est survenu lors de la récupération des données</div>';
} else {
    echo $HTML->boxTop('Detail Version');
    echo '<form method="POST" name="updateVersion'.$detailVersion->id.'" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=updateVersion">';
    echo '<table class="innertabs">';
    echo    '<tr>';
    echo            '<td class="FullBoxTitle">Version</td>';
    echo            '<td class="FullBoxTitle">Date Livraison</td>';
    echo            '<td class="FullBoxTitle">Type</td>';
    echo    '</tr>';
    echo    '<tr>';
    echo            '<td><input type="text" name="version_name" value="'.htmlspecialchars($detailVersion->name,ENT_QUOTES).'" /></td>';
    echo            '<td><input type="text" name="version_date_order" size="32" value="'.strftime("%d/%m/%Y",strtotime($detailVersion->date_order)).'" />(format : DD/MM/YYYY)</td>';
    echo            '<td>';
    echo               '<select name="version_release">';
    if ( $detailVersion->released ) {
        echo               '<option value="1" selected>Release</option>';
        echo               '<option value="0" >Milestone</option>';
    } else {
        echo               '<option value="1" >Release</option>';
        echo               '<option value="0" selected>Milestone</option>';
    }
    echo               '</select>';
    echo            '</td>';
    echo     '</tr>';
    echo '</table>';
    echo '<input type="checkbox" name="transverse" value="1">mise à jour transverse (fils inclus)</input>';
    echo '<input type="hidden" name="version_id" value="'.$detailVersion->id.'"></input>';
    echo '<input type="hidden" name="version_old_name" value="'.$detailVersion->name.'"></input>';
    echo '<br/>';
    print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
	    <div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
	    <a href="javascript:document.updateVersion'.$detailVersion->id.'.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Mettre &agrave; jour</a>
	    </div>
	    <div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
    echo '</form>';
    echo $HTML->boxBottom();

    echo '<form method="POST" name="deleteVersion'.$detailVersion->id.'" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=deleteVersion">';
    echo '<input type="hidden" name="deleteVersion" value="'.$detailVersion->id.'"></input>';
    print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
	    <div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
	    <a href="javascript:document.deleteVersion'.$detailVersion->id.'.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Supprimer cette version</a>
	    </div>
	    <div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
    echo '</form>';
}
?>

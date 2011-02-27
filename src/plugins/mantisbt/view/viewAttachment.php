<?php
/*
 * MantisBT plugin
 *
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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

if (empty($msg)) {
    if (!isset($defect)){
	    try{
		    $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		    $defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
	    }catch (SoapFault $soapFault) {
		    $error_attachment = $soapFault->faultstring;
		    $errorPage = true;
	    }
    }

    if ($errorPage){
	    echo 	'<div>Un probl&egrave;me est survenu lors de la r&eacute;cup&eacute;ration des donn&eacute;es : '.$error_attachment.'</div>';
    }else {
        echo "<h2 style='border-bottom: 1px solid black'>Fichiers attach&eacute;s</h2>";
        $boxTitle = 'Fichiers attach&eacute;s (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&view=addAttachment">Ajouter un fichier</a>)';
	    if ($defect->attachments) {
		    echo	'<table class="innertabs">';
		    echo '<tr>';
		    echo '<td class="FullBoxTitle">Nom du fichier</td>';
		    echo '<td class="FullBoxTitle">Actions</td>';
		    echo '</tr>';
		    foreach ($defect->attachments as $key => $attachement){
			    echo	'<tr>';
			    echo		'<td class="FullBox">'.$attachement->filename.'</td>';
			    echo 		'<td class="FullBox">';
			    echo			'<input type=button value="Télécharger" onclick="window.location.href=\'getAttachment.php/'.$attachement->id.'/'.$attachement->filename.'\'">';
			    echo			'<input type=button value="Supprimer" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&idAttachment='.$attachement->id.'&action=deleteAttachment&view=viewIssue\'">';
			    echo		'</td>';
			    echo 	'</tr>';
		    }
            echo "</table>";
	    } else {
            echo "Il n'y a pas de fichier attach&eacute; pour ce ticket.";
        }
    }
?>
<style>
.notice_title {
    background-color: #D7E0EB;
    padding: 10px;
    font-weight: bold;
    margin-bottom:0px;
    cursor: pointer;
    color: #4F93C3;
}

.notice_content {
    border: 1px solid #D7E0EB;
    padding: 10px;
    font-weight: bold;
    -moz-border-radius-bottomright: 8px;
    -moz-border-radius-bottomleft: 8px;
    -webkit-border-bottom-right-radius: 8px;
    -webkit-border-bottom-left-radius: 8px;
    margin-top:0px;
}
</style>
<script type="text/javascript">
    $(document).ready(function() {
        $("#expandable_file").hide();
    });    

</script>
<p class="notice_title" onclick='$("#expandable_file").slideToggle(300)'>Ajouter un fichier</p>
<div id='expandable_file' class="notice_content">
<?php
    include("addAttachment.php");
?>
</div>
<br/>
<br/>
<?php
}
?>

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

//$msg is coming from previous soap error
if (empty($msg)) {
    if (!isset($defect)){
        /* do not recreate $clientSOAP object if already created by other pages */
        if (!isset($clientSOAP))
	        $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	    $defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
    }

    $boxTitle = 'Notes';

    echo "<h2 style='border-bottom: 1px solid black'>Notes</h2>";

    if (isset($defect->notes)){
        echo    '<table class="innertabs">';
	    foreach ($defect->notes as $key => $note){
		    echo	'<tr>';
		    echo		'<td width="10%" class="FullBoxTitle">';
		    echo 			'('.sprintf($format,$note->id).')';
		    echo 			'<br/>';
		    echo			$note->reporter->name;
		    echo 			'<br/>';
		    // TODO
		    //date_default_timezone_set("UTC");
		    echo 			date("Y-m-d G:i",strtotime($note->date_submitted));
		    echo 		'</td>';
		    echo		'<td width="9%" class="FullBoxTitle">';
		    echo 			'<input type=button name="upNote" value="Modifier" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&idNote='.$note->id.'&view=editNote\'">';
		    echo 			'<input type=button name="delNote" value="Supprimer" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&idNote='.$note->id.'&action=deleteNote&view=viewIssue\'">';
		    echo 		"</td>";
		    echo 		'<td class="FullBox">';
		    echo		'<textarea disabled name="description" style="width:99%; background-color:white; color:black; border: none;" row="3">'.htmlspecialchars($note->text, ENT_QUOTES).'</textarea>';
		    echo 		"</td>";
		    echo 	'</tr>';
	    }
        echo "</table>";
    } else {
        echo "Il n'y a pas de notes pour ce ticket.";
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
        $("#expandable_note").hide();
    });    

</script>
<p class="notice_title" onclick='$("#expandable_note").slideToggle(300)'>Ajouter une note</p>
<div id='expandable_note' class="notice_content">
<?php
    include("addOrEditNote.php");
?>
</div>

<br/>

<?php
}
?>

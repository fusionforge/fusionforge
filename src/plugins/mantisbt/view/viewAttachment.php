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

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

if (empty($msg)) {
	if (!isset($defect)){
		try{
			$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
		}catch (SoapFault $soapFault) {
			echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
			$errorPage = true;
		}
	}

	if (!isset($errorPage)){
		echo '<h2 style="border-bottom: 1px solid black">'._('Attached files').'</h2>';
		$boxTitle = 'Fichiers attach&eacute;s (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=addAttachment">Ajouter un fichier</a>)';
		if (isset($defect->attachments) && count($defect->attachments) > 0) {
			echo	'<table class="innertabs">';
			echo '<tr>';
			echo '<td class="FullBoxTitle">'._('Filename').'</td>';
			echo '<td class="FullBoxTitle">'._('Actions').'</td>';
			echo '</tr>';
			foreach ($defect->attachments as $key => $attachement){
				echo	'<tr>';
				echo		'<td class="FullBox">'.$attachement->filename.'</td>';
				echo 		'<td class="FullBox">';
				echo			'<input type=button value="'._('Download').'" onclick="window.location.href=\'getAttachment.php/'.$group_id.'/'.$attachement->id.'/'.$attachement->filename.'\'">';
				echo			'<input type=button value="'._('Delete').'" onclick="window.location.href=\'?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&idAttachment='.$attachement->id.'&action=deleteAttachment&view=viewIssue\'">';
				echo		'</td>';
				echo 	'</tr>';
			}
		echo "</table>";
		} else {
			echo '<p class="warning">'._('No attached files for this ticket').'</p>';
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
    jQuery(document).ready(function() {
        jQuery("#expandable_file").hide();
    });

</script>
<p class="notice_title" onclick='jQuery("#expandable_file").slideToggle(300)'><?php echo _('Add file') ?></p>
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

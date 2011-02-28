<?php
/*
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
global $gfplugins;

if (!isset($defect)) {
	try {
        /* do not recreate $clientSOAP object if already created by other pages */
        if (!isset($clientSOAP))
		    $clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

		$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
	} catch (SoapFault $soapFault) {
		echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
		$errorPage = true;
	}
}

if (!isset($errorPage)){
    include('jumpToIssue.php');
    echo "<h2 style='border-bottom: 1px solid black'>Détail du ticket #$idBug</h2>";
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Category').'</td>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Severity').'</td>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Reproducibility').'</td>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Submit Date').'</td>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Update Date').'</td>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Found in').'</td>';
	echo 			'<td width="14%" class="FullBoxTitle">'._('Target').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">'. (isset($defect->category)) ? '' : $defect->category .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->severity->name)) ? '' : $defect->severity->name .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->reproducibility->name)) ? '' : $defect->reproducibility->name .'</td>';
	// TODO a revoir le problème des dates
	date_default_timezone_set("UTC");
	echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->date_submitted)).'</td>';
	echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->last_updated)).'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->version)) ? '' : $defect->version .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->target_version)) ? '' : $defect->target_version .'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBoxTitle">'._('Reporter').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Assigned to').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Priority').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Resolution').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Status').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Fixed in').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">'. (isset($defect->reporter->name)) ? '' : $defect->reporter->name .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->handler->name)) ? '' : $defect->handler->name .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->priority->name)) ? '' : $defect->priority->name .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->resolution->name)) ? '' : $defect->resolution->name .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->status->name)) ? '' : $defect->status->name .'</td>';
	echo 			'<td class="FullBox">'. (isset($defect->fixed_in_version)) ? '' : $defect->fixed_in_version .'</td>';
	echo		'</tr>';
	echo	'</table>';
	echo	'<br />';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="25%" class="FullBoxTitle">'._('Summary').'</td>';
	echo			'<td width="75%" class="FullBox">'.htmlspecialchars($defect->summary,ENT_QUOTES).'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td width="25%" class="FullBoxTitle">'._('Description').'</td>';
	echo			'<td width="75%" class="FullBox"><textarea disabled name="description" style="width:99%; background-color:white; color:black; border: none;" rows="6">'.htmlspecialchars($defect->description, ENT_QUOTES).'</textarea></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td width="25%" class="FullBoxTitle">'. _('Additional Informations').'</td>';
	echo			'<td width="75%" class="FullBox"><textarea disabled name="description" style="width:99%; background-color:white; color:black; border: none;" rows="6">'.htmlspecialchars($defect->additional_information, ENT_QUOTES).'</textarea></td>';
	echo		'</tr>';
	echo	'</table>';
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
        $("#expandable_edition").hide();
    });

</script>
<p class="notice_title" onclick='jQuery("#expandable_edition").slideToggle(300)'><?php echo _('Edit ticket') ?></p>
<div id='expandable_edition' class="notice_content">
<?php
	if (!isset($errorPage)) {
		include($gfplugins.$mantisbt->name."/view/editIssue.php");
	}
}
?>
</div>
<?php
	if (!isset($errorPage)) {
		include($gfplugins.$mantisbt->name."/view/viewNote.php");
		include($gfplugins.$mantisbt->name."/view/viewAttachment.php");
}

<?php
/**
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
global $editable;

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

if (!isset($errorPage)) {
    include('jumpToIssue.php');
    echo '<h2>'._('Ticket').' #'.$idBug.'</h2>';
	echo	'<table>';
	echo		'<tr style="background-color: gray;">';
	echo 			'<td width="14%" >'._('Category').'</td>';
	echo 			'<td width="14%" >'._('Severity').'</td>';
	echo 			'<td width="14%" >'._('Reproducibility').'</td>';
	echo 			'<td width="14%" >'._('Submit Date').'</td>';
	echo 			'<td width="14%" >'._('Update Date').'</td>';
	echo 			'<td width="14%" >'._('Found in').'</td>';
	echo 			'<td width="14%" >'._('Target').'</td>';
	echo		'</tr>';
	echo		'<tr>';

	(isset($defect->category)) ? $category_value = $defect->category : $category_value = '';
	echo 			'<td>'. $category_value.'</td>';
	(isset($defect->severity->name)) ? $severity_value = $defect->severity->name : $severity_value = '';
	echo 			'<td>'. $severity_value .'</td>';
	(isset($defect->reproducibility->name)) ? $reproducibility_value = $defect->reproducibility->name : $reproducibility_value = '';
	echo 			'<td>'. $reproducibility_value .'</td>';
	// TODO a revoir le problème des dates
	date_default_timezone_set("UTC");
	echo 			'<td>'.date("Y-m-d G:i",strtotime($defect->date_submitted)).'</td>';
	echo 			'<td>'.date("Y-m-d G:i",strtotime($defect->last_updated)).'</td>';
	(isset($defect->version)) ? $version_value = $defect->version : $version_value = '';
	echo 			'<td>'. $version_value .'</td>';
	(isset($defect->target_version)) ? $target_value = $defect->target_version : $target_value = '';
	echo 			'<td>'. $target_value .'</td>';
	echo		'</tr>';
	echo		'<tr style="background-color: gray;">';
	echo 			'<td>'._('Reporter').'</td>';
	echo 			'<td>'._('Assigned to').'</td>';
	echo 			'<td>'._('Priority').'</td>';
	echo 			'<td>'._('Resolution').'</td>';
	echo 			'<td>'._('Status').'</td>';
	echo 			'<td>'._('Fixed in').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	(isset($defect->reporter->name)) ? $reporter_value = $defect->reporter->name : $reporter_value = '';
	echo 			'<td>'. $reporter_value .'</td>';
	(isset($defect->handler->name)) ? $handler_value = $defect->handler->name : $handler_value = '';
	echo 			'<td>'. $handler_value .'</td>';
	(isset($defect->priority->name)) ? $priority_value = $defect->priority->name : $priority_value = '';
	echo 			'<td>'. $priority_value .'</td>';
	(isset($defect->resolution->name)) ? $resolution_value = $defect->resolution->name : $resolution_value = '';
	echo 			'<td>'. $resolution_value .'</td>';
	(isset($defect->status->name)) ? $status_value = $defect->status->name : $status_value = '';
	echo 			'<td>'. $status_value .'</td>';
	(isset($defect->fixed_in_version)) ? $fixed_value = $defect->fixed_in_version : $fixed_value = '';
	echo 			'<td>'. $fixed_value .'</td>';
	echo		'</tr>';
	echo	'</table>';
	echo	'<br />';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="25%">'._('Summary').'</td>';
	echo			'<td width="75%">'.htmlspecialchars($defect->summary,ENT_QUOTES).'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td width="25%">'._('Description').'</td>';
	echo			'<td width="75%"><textarea disabled name="description" style="width:99%; background-color:white; color:black; border: none;" rows="6">'.htmlspecialchars($defect->description, ENT_QUOTES).'</textarea></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td width="25%">'. _('Additional Informations').'</td>';
	(isset($defect->additional_information))? $additional_value = htmlspecialchars($defect->additional_information, ENT_QUOTES) : $additional_value = '';
	echo			'<td width="75%"><textarea disabled name="description" style="width:99%; background-color:white; color:black; border: none;" rows="6">'. $additional_value .'</textarea></td>';
	echo		'</tr>';
	echo	'</table>';
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#expandable_edition").hide();
    });

</script>
<?php
	if ($editable) {
?>
<p class="notice_title" onclick='jQuery("#expandable_edition").slideToggle(300)'><?php echo _('Edit ticket') ?></p>
<div id='expandable_edition' class="notice_content">
<?php
		if (!isset($errorPage)) {
			include($gfplugins.$mantisbt->name."/view/editIssue.php");
		}
	}
}
?>
</div>
<?php
	if (!isset($errorPage)) {
		include($gfplugins.$mantisbt->name."/view/viewNote.php");
		include($gfplugins.$mantisbt->name."/view/viewAttachment.php");
	}
?>
<?php
/**
 * MantisBT plugin
 *
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;
global $editable;
global $HTML;

if (empty($msg)) {
	if (!isset($defect)){
		try {
			$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
			$defect = $clientSOAP->__soapCall('mc_issue_get', array('username' => $username, 'password' => $password, 'issue_id' => $idBug));
		} catch (SoapFault $soapFault) {
			echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
			$errorPage = true;
		}
	}

	if (!isset($errorPage)){
		echo html_e('h2', array(), _('Attached Files'));
		if (isset($defect->attachments) && count($defect->attachments) > 0) {
			$titleArr = array(_('File Name'), _('Actions'));
			echo $HTML->listTableTop($titleArr);
			foreach ($defect->attachments as $key => $attachement){
				$cells = array();
				$cells[][] = $attachement->filename;
				$contentCell = '<input type=button value="'._('Download').'" onclick="window.location.href=\'/plugins/'.$mantisbt->name.'/getAttachment.php/'.$group_id.'/'.$attachement->id.'/'.$attachement->filename.'\'">';
				if ($editable) {
					$contentCell .= '<input type=button value="'._('Delete').'" onclick="window.location.href=\'/plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&idBug='.$idBug.'&idAttachment='.$attachement->id.'&action=deleteAttachment&view=viewIssue\'">';
				}
				$cells[][] = $contentCell;
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		} else {
			echo $HTML->information(_('No attached files for this ticket.'));
		}
	}
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#expandable_file").hide();
    });

</script>
<?php
	if ($editable) {
?>
<p class="notice_title" onclick='jQuery("#expandable_file").slideToggle(300)'><?php echo _('Add File') ?></p>
<div id='expandable_file' class="notice_content">
<?php
		include 'addAttachment.php';
	}
?>
</div>
<br/>
<br/>
<?php
}

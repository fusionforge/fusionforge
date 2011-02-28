<?php
/*
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
global $idBug;

//$msg is coming from previous soap error
if (empty($msg)) {
	if (!isset($defect)){
		/* do not recreate $clientSOAP object if already created by other pages */
		if (!isset($clientSOAP))
			$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

		$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
	}

	echo '<h2 style="border-bottom: 1px solid black">'._('Notes').'</h2>';

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
		    echo 			'<input type=button name="upNote" value="'._('Modify').'" onclick="window.location.href=\'?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$defect->id.'&idNote='.$note->id.'&view=editNote\'">';
		    echo 			'<input type=button name="delNote" value="'._('Delete').'" onclick="window.location.href=\'?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$defect->id.'&idNote='.$note->id.'&action=deleteNote&view=viewIssue\'">';
		    echo 		"</td>";
		    echo 		'<td class="FullBox">';
		    echo		'<textarea disabled name="description" style="width:99%; background-color:white; color:black; border: none;" row="3">'.htmlspecialchars($note->text, ENT_QUOTES).'</textarea>';
		    echo 		"</td>";
		    echo 	'</tr>';
		}
		echo "</table>";
	} else {
		echo '<p class="warning">'._('No notes for this ticket').'</p>';
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
        jQuery("#expandable_note").hide();
    });

</script>
<p class="notice_title" onclick='jQuery("#expandable_note").slideToggle(300)'><?php echo _('Add note') ?></p>
<div id='expandable_note' class="notice_content">
<?php
    include("addOrEditNote.php");
?>
</div>

<br/>

<?php
}
?>

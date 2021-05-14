<?php
/**
 * MantisBT plugin
 *
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2011,2014,2016, Franck Villaume - TrivialDev
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

/**
 * View All Issues
 * - for a specific group id
 * - for a specific user
 */

/*
 * @todo : remove all css and js
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $type;
global $editable;
global $HTML;

global $prioritiesImg, $bugPerPage;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	switch ($type) {
		case 'user': {
			global $mantisbt_userid;
			global $userMantisBTConf;
			$idsBugAll = array();
			$listStatus = array();
			foreach ($userMantisBTConf as $userMantisBTConfEntry) {
				$accountDataArray = array('id' => $userMantisBTConfEntry['mantisbt_userid']);
				$clientSOAP = new SoapClient($userMantisBTConfEntry['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
				$idsBugAll = array_merge($idsBugAll, $clientSOAP->__soapCall('mc_project_get_issues_for_user', array('username' => $userMantisBTConfEntry['user'], 'password' => $userMantisBTConfEntry['password'], 'project_id' => 0, 'filter_type' => 'assigned', 'target_user' => $accountDataArray)));
				$listStatus = array_merge($listStatus, $clientSOAP->__soapCall('mc_enum_status', array('username' => $userMantisBTConfEntry['user'], 'password' => $userMantisBTConfEntry['password'])));
			}
			break;
		}
		case 'group': {
			$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
			$idsBugAll = $clientSOAP->__soapCall('mc_project_get_issue_headers', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt'],  'page_number' => -1, 'per_page' => -1));
			$listStatus = $clientSOAP->__soapCall('mc_enum_status', array('username' => $username, 'password' => $password));
			break;
		}
	}
} catch (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
	$errorPage = true;
}
if (!isset($clientSOAP) && !isset($errorPage)) {
	echo $HTML->warning_msg(_('No data to retrieve.'));
} elseif (!isset($errorPage) && isset($clientSOAP)) {
	echo html_ao('script', array('type' => 'text/javascript'));
	?>
	//<![CDATA[
		jQuery(document).ready(function() {
			jQuery("#expandable_ticket").hide();
		});
	//]]>
	<?php
	echo html_ac(html_ap() - 1);
	// recuperation des bugs
	$listBug = array();

	$pageActuelle = getIntFromRequest('page');
	if (empty($pageActuelle)) {
		$pageActuelle = 1;
	}
	// calcul pour la pagination
	$nombreBugs = count ($idsBugAll);
	$nombreDePages=ceil($nombreBugs/$bugPerPage);
	// Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
	if($pageActuelle > $nombreDePages) {
		$pageActuelle = $nombreDePages;
	}
	$indexMin = ($pageActuelle - 1) * $bugPerPage;
	$indexMax = ($pageActuelle * $bugPerPage) -1;
	// construction du tableau
	$listBugAll = array();
	foreach ($idsBugAll as $defect) {
		foreach ($listStatus as $loopStatus) {
			if ($loopStatus->id == $defect->status) {
				$statusname = $loopStatus->name;
			}
		}
		$listBugAll[] = array('id'=> $defect->id, 'idPriority' => $defect->priority,
					'category' => $defect->category,'project' => $defect->project,
					'severityId' => $defect->severity, 'statusId' => $defect->status,
					'last_updated' => $defect->last_updated, 'status_name' => $statusname,
					'summary' => htmlspecialchars($defect->summary,ENT_QUOTES), 'view_state' => $defect->view_state,
			);
	}

	if(!empty($listBugAll)) {
		foreach ($listBugAll as $key => $defect) {
			if ( ($indexMin <= $key) && ($indexMax >= $key) ){
				$listBug[] = $defect;
			}
		}
	}

	// affichage page
	if (empty($listBug)) {
		echo $HTML->warning_msg(_('No tickets to display.'));
	} else {
		html_use_tablesorter();
		echo $HTML->getJavascripts();

		include ($gfplugins.$mantisbt->name.'/view/jumpToIssue.php');
		$titleArray = array();
		$thTitleArray = array();
		$thOtherAttrsArray = array();
		$titleArray[] = 'P';
		$thTitleArray[] = _('Priority');
		$thOtherAttrsArray[] = array('width' => '2%');
		$titleArray[] = 'ID';
		$thTitleArray[] = _('Bug ID');
		$thOtherAttrsArray[] = array('width' => '3%');
		$titleArray[] = _('Category');
		$thTitleArray[] = '';
		$thOtherAttrsArray[] = array('width' => '7%');
		$titleArray[] = _('Project');
		$thTitleArray[] = '';
		$thOtherAttrsArray[] = array('width' => '7%');
		$titleArray[] =  _('Severity');
		$thTitleArray[] = '';
		$thOtherAttrsArray[] = array('width' => '7%');
		$titleArray[] =  _('Status');
		$thTitleArray[] = '';
		$thOtherAttrsArray[] = array('width' => '15%');
		$titleArray[] =  _('Last Update');
		$thTitleArray[] = '';
		$thOtherAttrsArray[] = array('width' => '7%');
		$titleArray[] =  _('Summary');
		$thTitleArray[] = '';
		$thOtherAttrsArray[] = array('width' => '29%');
		echo $HTML->listTableTop($titleArray, array(), 'sortable_listissues full', 'sortable', array(), $thTitleArray, $thOtherAttrsArray);
		foreach($listBug as $key => $bug) {
			$cells = array();
			if($prioritiesImg[$bug['idPriority']] != "") {
				$cells[][] = '<img src="./img/'.$prioritiesImg[$bug['idPriority']].'">';
			}else{
				$cells[][] = '';
			}
			$cells[][] = util_make_link('/plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&idBug='.$bug['id'].'&view=viewIssue', $bug['id']);
			$cells[][] = $bug['category'];
			$cells[][] = $bug['project'];
			$content = '';
			if($bug['severityId'] > 50) {
				$content .= '<b>';
			}
			$content .= $bug['severityId'];
			if($bug['severityId'] > 50) {
				$content .= '</b>';
			}
			$cells[][] = $content;
			$cells[][] = $bug['status_name'];
			$cells[][] = strftime(_('%d/%m/%Y'),strtotime($bug['last_updated']));
			$content = $bug['summary'];
			if ($bug['view_state'] == 50) {
				$content .= '<img src="./img/protected.gif">';
			}
			$cells[][] = $content;
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	}

	// Add new issue
	if ($type == 'group' && $editable) {
	?>
		<p class="notice_title" onclick='jQuery("#expandable_ticket").slideToggle(300);'><?php echo _('Add a new ticket') ?></p>
		<div id='expandable_ticket' class="notice_content">
		<?php include ($gfplugins.$mantisbt->name.'/view/addIssue.php'); ?>
		</div>
		<br/>
	<?php
	}

	// Creation de la pagination
	echo '<div align="center">';
	for($i=1; $i < $nombreDePages; $i++)
	{
		if($i==$pageActuelle) //Si il s'agit de la page actuelle...
		{
			echo '| <b>'.$i.'</b>';
		} else {
			echo $HTML->openForm(array('style' => 'display:inline', 'name' => 'page'.$i, 'method' => 'post',  'action' => '/plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&page='.$i));
			echo 	'<input type="hidden" name="sort" value="'.$bugfilter['sort'].'" />';
			echo 	'<input type="hidden" name="dir" value="'.$bugfilter['dir'].'" />';
			if ( isset($bugfilter['show_status'])) {
				foreach ($bugfilter['show_status'] as $key => $childStatus) {
					echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
				}
			}
			if ( isset($bugfilter['project_id'])) {
				foreach ($bugfilter['project_id'] as $key => $childId) {
					echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
				}
			}
			echo '| <a href="javascript:document.page'.$i.'.submit();">'.$i.'</a>';
			echo $HTML->closeForm();
		}
	}
	echo 	'</div>';
}

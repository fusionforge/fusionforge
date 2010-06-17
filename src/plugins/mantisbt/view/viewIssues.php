<?php

/*
 * Copyright 2010, Capgemini
 * Author: Franck Villaume - Capgemini
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

GLOBAL $HTML;

global $prioritiesImg,$bugPerPage;

// creation du filtre par defaut
$bugfilter = array ();
$bugfilter['_view_type'] = "simple";
if ( $_POST['sort'] ) {
	$bugfilter['sort'] = $_POST['sort'];
} else {
	$bugfilter['sort'] = "last_updated";
}
if ( $_POST['dir'] ) {
	$bugfilter['dir'] = $_POST['dir'];
} else {
	$bugfilter['dir'] = "ASC";
}

// filtres initiaux
echo $HTML->boxTop("Règles de filtrage");
echo    '<form name="viewissues" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
if ( $_POST['sort'] ) {
	echo '<input type="hidden" name="sort" value="'.$_POST['sort'].'" />';
}
if ( $_POST['dir'] ) {
	echo '<input type="hidden" name="dir" value="'.$_POST['dir'].'" />';
}
echo    '<div>';
echo    '<input type="checkbox" name="addClosed" value="1" ';
if ( $_POST['addClosed'] ) {
       	echo 'checked';
	$bugfilter['_view_type'] = 'full';
}
echo    '>Inclure les tickets fermés</input>';
echo    '</div>';
print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
       	<div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
       	<a href="javascript:document.viewissues.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Recharger</a>
       	</div>
       	<div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
echo	'</form>';
echo $HTML->boxBottom();

// recuperation des bugs
$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$listBug = array();
try {
	if ($type == "user"){
		$idsBugAll = $clientSOAP->__soapCall('mc_issue_get_filtered_by_user', array("username" => $username, "password" => $password, "filter" => $bugfilter ));
	}else if ($type == "group"){
		$idsBugAll = $clientSOAP->__soapCall('mc_project_get_issues', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis,  "page_number" => -1, "per_page" => -1, "filter" => $bugfilter));
	}
}catch (SoapFault $soapFault) {
	echo $soapFault->faultstring;
	echo "<br/>";
	$errorPage = true;
}

if (!$errorPage){
	$pageActuelle = getIntFromRequest('page');
	if (empty($pageActuelle)) {
		$pageActuelle = 1;
	}
	// calcul pour la pagination
	$nombreBugs = count ($idsBugAll);
	$nombreDePages=ceil($nombreBugs/$bugPerPage);
	// Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
	if($pageActuelle>$nombreDePages){
		$pageActuelle=$nombreDePages;
	}
	$indexMin = ($pageActuelle - 1) * $bugPerPage;
	$indexMax = ($pageActuelle * $bugPerPage) -1;
	// construction du tableau
	foreach ($idsBugAll as $defect) {
		$nbNote=0;
		if (isset($defect->notes)){
			$nbNote = count($defect->notes);
		}
		$listBugAll[] = array( "id"=> $defect->id, "idPriority"=> $defect->priority->id,
					"category"=> $defect->category,"project" => $defect->project->name, 
					"severityId" => $defect->severity->id, "severity" => $defect->severity->name, 
					"status" => $defect->status->name, "statusId" => $defect->status->id,
					"last_updated" => $defect->last_updated, "nb_note" => $nbNote, "handler" => $defect->handler->name,
					"summary" => $defect->summary, "view_state" => $defect->view_state->id,
					"version" => $defect->version, "fixed_in_version" => $defect->fixed_in_version,
					"target_version" => $defect->target_version
				);
	}

	if(count($listBugAll) >0){
		foreach ($listBugAll as $key => $defect) {
			if ( ($indexMin <= $key) && ($indexMax >= $key) ){
				$listBug[] = $defect;
			}
		}
	}
}

// affichage page
$nbligne=0;

if ($type == "group"){
	$boxTitle = 'Liste des tickets (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&view=addIssue">Rapporter un ticket</a>)';
}else {
	$boxTitle = 'Liste des tickets';
}

echo $HTML->boxTop($boxTitle,InTextBorder);

if ($errorPage){
	echo 	'<div>Un problème est survenu lors de la récupération des données</div>';
	echo $HTML->boxBottom();
}else {
	$picto_haut = util_make_url('themes/gforge/images/picto_fleche_haut_marron.png');
	$picto_bas = util_make_url('themes/gforge/images/picto_fleche_bas_marron.png');
	$nbligne++;
	echo    '<tr>';
	echo		'<th class="InText" width="2%"></th>';
	echo		'<th class="InText" width="2%"></th>';
	// Priority
	echo 		'<th class="InText" width="2%">';
	echo			'<form name="filterprority" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="priority" />';
	echo				'<a class="DataLink" href="javascript:document.filterprority.submit();">P';
	if ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// ID
	echo		'<th class="InText" width="3%">';
	echo			'<form name="filterid" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="id" />';
	echo				'<a class="DataLink" href="javascript:document.filterid.submit();">ID';
	if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// Note
	echo 		'<th class="InText" width="1%"> # </th>';
	// Catégorie
	echo		'<th class="InText" width="7%">';
	echo			'<form name="filtercat" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="category_id" />';
	echo				'<a class="DataLink" href="javascript:document.filtercat.submit();">Catégorie';
	if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// Projet
	echo 		'<th class="InText" width="7%">';
	echo			'<form name="projectid" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="project_id" />';
	echo				'<a class="DataLink" href="javascript:document.projectid.submit();">Projet';
	if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// Sévérité
	echo 		'<th class="InText" width="7%">';
	echo			'<form name="severity" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="severity" />';
	echo				'<a class="DataLink" href="javascript:document.severity.submit();">Sévérité';
	if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// Etat
	echo 		'<th class="InText" width="15%">';
	echo			'<form name="statusid" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="status" />';
	echo				'<a class="DataLink" href="javascript:document.statusid.submit();">Etat';
	if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// Mis a jour (date)
	echo 		'<th class="InText" width="7%">';
	echo			'<form name="lastupdate" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="last_updated" />';
	echo				'<a class="DataLink" href="javascript:document.lastupdate.submit();">Mis à jour';
	if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// résumé
	echo 		'<th class="InText" width="29%">';
	echo			'<form name="summary" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="summary" />';
	echo				'<a class="DataLink" href="javascript:document.summary.submit();">Résumé';
	if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// version de détection
	echo 		'<th class="InText" width="6%">';
	echo			'<form name="detected" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "detected" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "detected" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="detected" />';
	echo				'<a class="DataLink" href="javascript:document.detected.submit();">Détecté en';
	if ($bugfilter['sort'] == "detected" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "detected" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// corrigé en version
	echo		'<th class="InText" width="6%">';
	echo			'<form name="fixed" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="fixed_in_version" />';
	echo				'<a class="DataLink" href="javascript:document.fixed.submit();">Corrigé en';
	if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	// version cible : Milestone
	echo		'<th class="InText" width="6%">';
	echo			'<form name="target" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt">';
	if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "ASC") {
		echo			'<input type=hidden name="dir" value="DESC"/>';
	}else if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "DESC") {
		echo			'<input type="hidden" name="dir" value="ASC"/>';
	}
	if ($_POST['addClosed']) {
		echo			'<input type="hidden" name="addClosed" value="1"; />';
	}
	echo				'<input type=hidden name="sort" value="target_version" />';
	echo				'<a class="DataLink" href="javascript:document.target.submit();">Milestone';
	if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "ASC" ){
		echo				'<img src="'.$picto_haut.'">';
	}else if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "DESC" ) {
		echo				'<img src="'.$picto_bas.'">';
	}
	echo 				'</a>';
	echo			'</form>';
	echo 		'</th>';
	echo	'</tr>';
	$cpt = 0;
	$format = "%07d";
	foreach($listBug as $key => $bug){
		$nbligne++;
		if ($nbligne % 2 == 0) {
			echo	'<tr class="LignePaire">';
		} else {
			echo '<tr class="LigneImpaire">';
		}
		echo		'<td class="InText"><input type="checkbox" name="check_'.$cpt.'" value="action_bug"></td>';
		echo		'<input type="hidden" name="idBug_'.$cpt.'" value="'.$bug['id'].'">';
		echo		'<td class="InText"><a class="DataLink" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$bug['id'].'&view=editIssue"><img src="./img/update.png"></a></td>';
		if($prioritiesImg[$bug['idPriority']] != ""){
			echo		'<td class="InText"><img src="./img/'.$prioritiesImg[$bug['idPriority']].'"></td>';
		}else{
			echo		'<td class="InText"></td>';
		}
		echo		'<td class="InText"><a class="DataLink" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$bug['id'].'&view=viewIssue">'.sprintf($format,$bug['id']).'</a></td>';
		if ($bug['nb_note'] != 0){
			echo		'<td class="InText"><a class="DataLink" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$bug['id'].'&view=viewNote">'.$bug['nb_note'].'</a></td>';
		}else{
			echo		'<td class="InText"></td>';
		}
		echo 		'<td class="InText">'.$bug['category'].'</td>';
		echo 		'<td class="InText">'.$bug['project'].'</td>';
		echo 		'<td class="InText">';
		if($bug['severityId'] > 50){
			echo		'<b>';
		}
		echo			$bug['severity'];
		if($bug['severityId'] > 50){
			echo		'</b>';
		}
		echo		'</td>';
		echo 		'<td class="InText">'.$bug['status'].' ('.$bug['handler'].')</td>';
		echo 		'<td class="InText">'.strftime("%d/%m/%Y",strtotime($bug['last_updated'])).'</td>';
		echo 		'<td class="InText">'.$bug['summary'];
		if ($bug['view_state'] == 50){
			echo '<img src="./img/protected.gif">';
		}
		echo 		'</td>';
		echo 		'<td class="InText">'.$bug['version'].'</td>';
		echo 		'<td class="InText">'.$bug['fixed_in_version'].'</td>';
		echo 		'<td class="InText">'.$bug['target_version'].'</td>';
		echo	'</tr>';
		$cpt ++;
	}
	echo $HTML->boxBottom();
	echo 	'<div align="center">';
	// Creation de la pagination
	for($i=1; $i<=$nombreDePages; $i++)
	{
		if($i==$pageActuelle) //Si il s'agit de la page actuelle...
		{
			echo '| <b>'.$i.'</b>';
		} else {
			echo '<form style="display:inline" name="page'.$i.'" method="post" action="?type='.$type.'&id='.$id.'&pluginname=mantisbt&page='.$i.'" >';
			echo 	'<input type="hidden" name="sort" value="'.$bugfilter['sort'].'" />';
			echo 	'<input type="hidden" name="dir" value="'.$bugfilter['dir'].'" />';
			if ($_POST['addClosed']) {
				echo 	'<input type="hidden" name="addClosed" value="1" />';
			}
			echo '| <a href="javascript:document.page'.$i.'.submit();">'.$i.'</a>';
			echo '</form>';
		}
	}
	echo 	'</div>';
}
?>

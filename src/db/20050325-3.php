#! /usr/bin/php
<?php
/**
 * GForge Extra Field Conversion Script
 *
 * Copyright 2004 GForge, LLC
 * http://fusionforge.org/
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

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';

// First of all, try to create the "alias" field if it doesn't exist
$res = db_query_params ('SELECT alias FROM artifact_extra_field_list',
			array()) ;

if (!$res) {		// error, the field doesn't exist
	$res = db_query_params ('ALTER TABLE artifact_extra_field_list ADD COLUMN alias TEXT',
			array()) ;

	if (!$res) {
		echo db_error();
		exit(16);
	}
} 

//
//  Set up this script to run as the site admin
//

$res = db_query_params ('SELECT user_id FROM user_group WHERE admin_flags=$1 AND group_id=$2',
			array('A',
			'1')) ;


if (!$res) {
    echo db_error();
    exit(1);
}

if (db_numrows($res) == 0) {
    // There are no Admins yet, aborting without failing
    echo "SUCCESS\n";
    exit(0);
}

$id=db_result($res,0,0);
session_set_new($id);

db_begin();

$res=db_query_params ('SELECT group_id,group_artifact_id,use_resolution FROM artifact_group_list',
			array()) ;


for ($i=0; $i<db_numrows($res); $i++) {

	$group_id=db_result($res,$i,'group_id');
	$gaid=db_result($res,$i,'group_artifact_id');
	$ur=db_result($res,$i,'use_resolution');

	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		echo "\nCould Not Get Group: $group_id";
		db_rollback();
		exit(2);
	}

	$at = new ArtifactType($g,$gaid);
	if (!$at || !is_object($at)) {
		echo "\nCould Not Get ArtifactType: $gaid";
		db_rollback();
		exit(3);
	}
	//
	//	Convert ArtifactCategory To Extra Field
	//
	$aef=new ArtifactExtraField($at);
	$aef->create('Category',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0);
	$catbox_id=$aef->getID();
	if (!$catbox_id) {
		echo "\nCould Not Get New Category Box ID: $gaid ".$aef->getErrorMessage();
		db_rollback();
		exit(4);
	}
	$resc=db_query_params ('SELECT * FROM artifact_category WHERE group_artifact_id=$1',
			array($gaid)) ;

	for ($j=0; $j<db_numrows($resc); $j++) {
		$cat_id=db_result($resc,$j,'id');
		$cat_name=addslashes(db_result($resc,$j,'category_name'));
		if (strlen($cat_name) < 1) {
			$cat_name='[empty]';
		}

		$efe=new ArtifactExtraFieldElement($aef);
		#DEBUG
		#$efe->create($cat_name);
		if (!$efe->create($cat_name)) {
			echo 'Group: '.$group_id.' Could not create category element: '.$cat_name.' '.$efe->getErrorMessage();
			db_rollback();
			exit(5);
		}
		$efe_id=$efe->getID();
//echo 'Artifact Category: Group: '.$group_id;
//print_r($efe->data_array);
		if (!$efe_id) {
			#DEBUG echo "\nDid Not Get efe_id";
			echo "\nDid Not Get efe_id (group_id: $group_id)";
			db_rollback();
			exit(5);
		}
		$res2=db_query_params ('INSERT INTO artifact_extra_field_data (artifact_id,field_data,extra_field_id)
			SELECT artifact_id,$efe_id,$catbox_id FROM artifact 
			WHERE category_id=$1 AND group_artifact_id=$2',
			array($cat_id,
			$gaid)) ;

		if (!$res2) {
			echo "Could Not Insert AEFD for category " . db_error();
			db_rollback();
			exit(6);
		}
		$res3=db_query_params ('UPDATE artifact_history SET old_value=$1,field_name=$2
			WHERE old_value=$3 AND field_name=$4 AND artifact_id IN
            (SELECT artifact_id FROM artifact WHERE group_artifact_id=$5)',
			array($cat_name,
			'Category',
			$cat_id,
			'category_id',
			$gaid)) ;

		if (!$res3) {
			echo "Could Not update history category " . db_error();
			db_rollback();
			exit(7);
		}
	}

	//
	//	Convert ArtifactGroup To Extra Field
	//
	$aef=new ArtifactExtraField($at);
	$aef->create('Group',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0);
	$groupbox_id=$aef->getID();
	if (!$groupbox_id) {
		echo "\nCould Not Get groupbox_id ".$aef->getErrorMessage();
		db_rollback();
		exit(8);
	}
	$resc=db_query_params ('SELECT * FROM artifact_group WHERE group_artifact_id=$1',
			array($gaid)) ;

	for ($j=0; $j<db_numrows($resc); $j++) {
		$artgroup_id=db_result($resc,$j,'id');
		$group_name=addslashes(db_result($resc,$j,'group_name'));
		if (strlen($group_name) < 1) {
			$group_name='[empty]';
		}

		$efe=new ArtifactExtraFieldElement($aef);
		//$efe->create($group_name);
		if (!$efe->create($group_name)) {
			echo 'Group: '.$group_id.' Could not create group element: '.$group_name.' '.$efe->getErrorMessage();
			db_rollback();
			exit(5);
		}
//echo 'Artifact Group: Group: '.$group_id;
//print_r($efe->data_array);
		$efe_id=$efe->getID();
		if (!$efe_id) {
			echo "\nDid Not Get efe_id";
			db_rollback();
			exit(9);
		}
		$res2=db_query_params ('INSERT INTO artifact_extra_field_data (artifact_id,field_data,extra_field_id)
			SELECT artifact_id,$efe_id,$groupbox_id FROM artifact 
			WHERE artifact_group_id=$1 AND group_artifact_id=$2',
			array($artgroup_id,
			$gaid)) ;

		if (!$res2) {
			echo "Could Not Insert AEFD for artifactgroup " . db_error();
			db_rollback();
			exit(10);
		}
		$res3=db_query_params ('UPDATE artifact_history SET old_value=$1,field_name=$2
			WHERE old_value=$3 AND field_name=$4 AND artifact_id IN
            (SELECT artifact_id FROM artifact WHERE group_artifact_id=$5)',
			array($group_name,
			'Group',
			$artgroup_id,
			'artifact_group_id',
			$gaid)) ;

		if (!$res3) {
			echo "Could Not update history artifactgroup " . db_error();
			db_rollback();
			exit(11);
		}
	}

	if ($ur) {
		//
		//	Convert ArtifactResolution To Extra Field
		//
		$aef=new ArtifactExtraField($at);
		$aef->create('Resolution',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0);
		$resolutionbox_id=$aef->getID();
		if (!$resolutionbox_id) {
			echo "\nCould Not Get resolutionbox_id ".$aef->getErrorMessage();
			db_rollback();
			exit(12);
		}
		$resc=db_query_params ('SELECT * FROM artifact_resolution',
			array()) ;

		for ($j=0; $j<db_numrows($resc); $j++) {
			$resolution_id=db_result($resc,$j,'id');
			$resolution_name=addslashes(db_result($resc,$j,'resolution_name'));
			if (strlen($resolution_name) < 1) {
				$resolution_name='[empty]';
			}
			$efe=new ArtifactExtraFieldElement($aef);
		//	$efe->create($resolution_name);
			if (!$efe->create($resolution_name)) {
				echo 'Group: '.$group_id.' Could not create resolution element: '.$resolution_name.' '.$efe->getErrorMessage();
				db_rollback();
				exit(5);
			}
//echo 'Artifact Group: Group: '.$group_id;
//print_r($efe->data_array);
			$efe_id=$efe->getID();
			if (!$efe_id) {
				echo "\nDid Not Get efe_id";
				db_rollback();
				exit(13);
			}
			$res2=db_query_params ('INSERT INTO artifact_extra_field_data (artifact_id,field_data,extra_field_id)
				SELECT artifact_id,$efe_id,$resolutionbox_id FROM artifact 
				WHERE resolution_id=$1 AND group_artifact_id=$2',
			array($resolution_id,
			$gaid)) ;

			if (!$res2) {
				echo "Could Not Insert AEFD for resolution " . db_error();
				db_rollback();
				exit(14);
			}
			$res3=db_query_params ('UPDATE artifact_history SET old_value=$1,field_name=$2
				WHERE old_value=$3 AND field_name=$4 AND artifact_id IN 
				(SELECT artifact_id FROM artifact WHERE group_artifact_id=$5)',
			array($resolution_name,
			'Resolution',
			$resolution_id,
			'resolution_id',
			$gaid)) ;

			if (!$res3) {
				echo "Could Not update history resolution " . db_error();
				db_rollback();
				exit(15);
			}
		}
	}
}
db_commit();


echo "\nSUCCESS";

?>

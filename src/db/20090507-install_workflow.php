<?php

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'common/tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';
require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';

/* Need full power, switching to an admin guy */
$res = db_query_params ('SELECT user_id FROM user_group WHERE group_id=1',
			array()) ;

$admin_id = db_result($res,0,'user_id');
session_set_new($admin_id);

			      $res = db_query_params ('SELECT group_id, artifact_group_list.group_artifact_id, element_id, artifact_extra_field_elements.extra_field_id
		FROM artifact_extra_field_list, artifact_extra_field_elements, artifact_group_list
		WHERE 
			artifact_extra_field_list.extra_field_id=artifact_extra_field_elements.extra_field_id
		AND 	artifact_group_list.group_artifact_id = artifact_extra_field_list.group_artifact_id
		AND	field_type=7',
						      array ()) ;
while($row = db_fetch_array($res)) {
	print "Upgrading group_id=".$row['group_id']." (group_artifact_id=".$row['group_artifact_id'].")\n";
	$group =& group_get_object($row['group_id']);
	$ath = new ArtifactTypeHtml($group, $row['group_artifact_id']);
	$efarr =& $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS));
	$keys=array_keys($efarr);
	$field_id = $keys[0];
	    	
	$atw = new ArtifactWorkflow($ath, $field_id);
	$atw->addNode($row['element_id']);
	$atw->_addEvent('100', $row['element_id']);
}

echo "SUCCESS";

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

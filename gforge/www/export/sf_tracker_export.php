<?php
/**
  * sf_tracker_export.php
  *
  * SourceForge Exports: Export tracker contents in XML
  *
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @author		Darrell Brogdon <dbrogdon@valinux.com>
  * @version	$Id$
  *
  */

set_time_limit(0);

require_once('pre.php');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/Artifacts.class');
require_once('common/tracker/ArtifactFile.class');
require_once('common/tracker/ArtifactType.class');
require_once('common/tracker/ArtifactGroup.class');
require_once('common/tracker/ArtifactCategory.class');
require_once('common/tracker/ArtifactCanned.class');
require_once('common/tracker/ArtifactResolution.class');

header("Content-Type: text/plain");
?>
<tracker version="1.0" xmlns:xsi="http://www.w3.org/2000/10/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://<?php echo $sys_default_domain; ?>/export/sf_tracker_export.xsd">
<?php

$group_id = $_GET['group_id'];
$atid = $_GET['atid'];

if ($group_id && $atid) {
	//
	//	get the Group object
	//
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		echo("	<error>Could not get the Group object</error>\n");
		$errors = true;
	}

	//
	//	Create the ArtifactType object
	//
	$ath = new ArtifactType($group,$atid);
	if (!$ath || !is_object($ath)) {
		echo("	<error>ArtifactType could not be created</error>\n");
		$errors = true;
	}
	if ($ath->isError()) {
		echo('	<error>' . $ath->getErrorMessage() . "</error>\n");
		$errors = true;
	}

	//
	// Create the Artifacts object
	//
	$artifacts = new Artifacts($ath);	
	if (!$artifacts || !is_object($ath)) {
		echo("	<error>Artifacts could not be created</error>\n");
		$errors = true;
	}
	if ($artifacts->isError()) {
		echo('	<error>' . $artifacts->getErrorMessage() . "</error>\n");
		$errors = true;
	}

	//
	// Loop through each artifact object and show the results
	//
	if (!$alist =& $artifacts->getArtifacts($offset)) {
		echo('	<error>' . $artifacts->getErrorMessage() . "</error>\n");
		$errors = true;
	}

	if ($errors) {
		echo ('</tracker>');
		exit;
	}

	for ($i=0; $i<count($alist); $i++) {
?>
	<artifact id="<?php echo $alist[$i]->getID(); ?>">
		<submitted_by><?php echo $alist[$i]->getSubmittedUnixName(); ?></submitted_by>
		<submitted_date><?php echo date( $sys_datefmt, $alist[$i]->getOpenDate() ); ?></submitted_date>
		<artifact_type id="<?php echo $ath->getID(); ?>"><?php echo $ath->getID(); ?></artifact_type>
		<category id="<?php echo $alist[$i]->getCategoryID(); ?>"><?php echo $alist[$i]->getCategoryName(); ?></category>
		<artifact_group id="<?php echo $alist[$i]->getArtifactGroupID(); ?>"><?php echo $alist[$i]->getArtifactGroupID; ?></artifact_group>
		<assigned_to><?php echo $alist[$i]->getAssignedRealName(); ?></assigned_to>
		<priority id="<?php echo $alist[$i]->getPriority(); ?>"><?php echo $alist[$i]->getPriority(); ?></priority>
		<status><?php echo $alist[$i]->getStatusName(); ?></status>
		<resolution><?php echo $alist[$i]->getResolutionName(); ?></resolution>
		<summary><?php echo $alist[$i]->getSummary(); ?></summary>
		<detail><?php echo $alist[$i]->getDetails(); ?></detail>
<?php
	$result = $alist[$i]->getMessages();
	$rows = db_numrows($result);
	if ($rows > 0) {
?>
		<follow_ups>
<?php
		for ($x=0; $x<$rows; $x++) {
?>
			<item>
				<date><?php echo db_result($result, $x, 'adddate'); ?></date>
				<sender><?php echo db_result($result, $x, 'user_name'); ?></sender>
				<text><?php echo db_result($result, $x, 'body'); ?></text>
			</item>
<?php
		}
?>
		</follow_ups>
<?php
	}

	$file_list =& $alist[$i]->getFiles();
	$count=count($file_list);
	if ($count > 0) {
?>
		<existingfiles>
<?php
		for ($x=0; $x<$count; $x++) {
?>
			<file>
				<id><?php echo $file_list[$x]->getID(); ?></id>
				<name><?php echo $file_list[$x]->getName(); ?></name>
				<description><?php echo $file_list[$x]->getDescription(); ?></description>
				<filesize><?php echo $file_list[$x]->getSize(); ?></filesize>
				<filetype><?php echo $file_list[$x]->getType(); ?></filetype>
				<adddate><?php echo $file_list[$x]->getDate(); ?></adddate>
				<submitted_by><?php echo $file_list[$x]->getSubmittedBy(); ?></submitted_by>
			</file>
<?php
		}
?>
		</existingfiles>
<?php
	}

	$result = $alist[$i]->getHistory();
	$rows = db_numrows($result);

	if ($rows > 0) {
?>
		<change_log>
<?php
		for ($x=0; $x<$rows; $x++) {
?>
			<item>
				<field><?php echo db_result($result, $x, 'field_name'); ?></field>
				<old_value><?php echo db_result($result, $x, 'old_value'); ?></old_value>
				<date><?php echo db_result($result, $x, 'entrydate'); ?></date>
				<by><?php echo db_result($result, $x, 'user_name'); ?></by>
			</item>
<?php
		}
?>
		</change_log>
<?php
	}
?>
	</artifact>
<?php
	}
} else {
	print("    <error>Group ID Not Set</error>\n");
}
?>
</tracker>

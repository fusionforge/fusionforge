<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: download.php,v 1.4 2000/04/20 15:13:54 tperdue Exp $

require ('pre.php');

$sql="SELECT code FROM patch WHERE patch_id='$id'";
$result=db_query($sql);

if ($result && db_numrows($result) > 0) {
	header('Content-Type: text/plain');
	if (strlen(db_result($result,0,'code')) > 1) {
		echo util_unconvert_htmlspecialchars(db_result($result,0,'code'));
	} else {
		echo 'nothing in here';
	}
} else {
	echo 'Error';
}

?>

<?php
/**
  *
  * Patch Tracker Placeholder Page
  *
  * This page redirects URL of the old patch tracker to the instance
  * of specific tracker which holds pathes. This page probably should
  * stay, as it supports human-recognizable URLs to access patch tracker
  * (at least until better method will be available).
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: index.php,v 1.14 2001/05/22 15:33:12 pfalcon Exp $
  *
  */


if ($group_id) {

	$atid=($group_id+300000);
	$aid=($patch_id+300000);

	switch ($func) {

		case 'addpatch' : {
			Header ("Location: /tracker/?group_id=$group_id&atid=$atid&func=add");
			break;
		}

		case 'detailpatch' : {
			Header ("Location: /tracker/?group_id=$group_id&atid=$atid&aid=$aid&func=detail");
			break;
		}

		default : {
			Header ("Location: /tracker/?group_id=$group_id&atid=$atid");
			break;
		}

	}

} else {

	echo "No Group Id";

}

?>

<?php
/**
  *
  * Bugtracker Placeholder Page
  *
  * This page redirects URL of the old bugtracker to the instance
  * of specific tracker which holds bugs. This page probably should
  * stay, as it supports human-recognizable URLs to access bug tracker
  * (at least until better method will be available).
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: index.php,v 1.48 2001/05/22 15:33:12 pfalcon Exp $
  *
  */

if ($group_id) {

	$atid=($group_id+100000);
	$aid=($bug_id+100000);

	switch ($func) {

		case 'addbug' : {
			Header ("Location: /tracker/?group_id=$group_id&atid=$atid&func=add");
			break;
		}

		case 'detailbug' : {
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

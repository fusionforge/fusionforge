<?php
/**
  *
  * Support Tracker Placeholder Page
  *
  * This page redirects URL of the old support tracker to the instance
  * of specific tracker which holds support tickets. This page probably
  * should stay, as it supports human-recognizable URLs to access
  * support tracker (at least until better method will be available).
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: index.php,v 1.25 2001/05/22 15:33:12 pfalcon Exp $
  *
  */



if ($group_id) {

	$atid=($group_id+200000);
	$aid=($support_id+200000);

	switch ($func) {

		case 'addsupport' : {
			Header ("Location: /tracker/?group_id=$group_id&atid=$atid&func=add");
			break;
		}

		case 'detailsupport' : {
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

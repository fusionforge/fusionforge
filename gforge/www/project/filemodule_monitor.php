<?php
/**
  *
  * Package Monitor Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: filemodule_monitor.php,v 1.6 2001/05/22 19:42:19 pfalcon Exp $
  *
  */


require_once('pre.php');

if (user_isloggedin()) {
	/*
		User obviously has to be logged in to monitor
		a file module
	*/

	$HTML->header(array('title'=>'Monitor A Package'));

	if ($filemodule_id) {
		/*
			First check to see if they are already monitoring
			this thread. If they are, say so and quit.
			If they are NOT, then insert a row into the db
		*/

		echo '
			<H2>Monitor a Package</H2>';

		$sql="SELECT * FROM filemodule_monitor WHERE user_id='".user_getid()."' AND filemodule_id='$filemodule_id';";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring this filemodule, so 
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO filemodule_monitor (filemodule_id,user_id) VALUES ('$filemodule_id','".user_getid()."')";

			$result = db_query($sql);

			if (!$result) {
				echo '
					<FONT COLOR="RED">Error inserting into filemodule_monitor</FONT>';
				echo db_error();
			} else {
				echo '
					<FONT COLOR="RED"><H3>Package is now being monitored</H3></FONT>
					<P>
					You will now be emailed when new files are released.
					<P>
					To turn off monitoring, simply click the <B>Monitor Package</B> link again.';
			}

		} else {

			$sql="DELETE FROM filemodule_monitor WHERE user_id='".user_getid()."' AND filemodule_id='$filemodule_id';";
			$result = db_query($sql);
			echo '
				<FONT COLOR="RED"><H3>Monitoring has been turned off</H3></FONT>
				<P>
				You will not receive any more emails from this package.';

		}

	} else {
		echo '
			<H1>Error - Choose a package First</H1>';
	} 

	$HTML->footer(array());

} else {
	exit_not_logged_in();
}
?>

<?php
/**
  *
  * SourceForge Mailing Lists Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


function mail_header($params) {
	global $group_id;

	//required for site_project_header
	$params['group']=$group_id;
	$params['toptab']='mail';

	$project =& group_get_object($group_id);

	if (!$project->usesMail()) {
		exit_error('Error','This Project Has Turned Off Mailing Lists');
	}


	site_project_header($params);
	echo '
		<P><B><A HREF="/mail/admin/?group_id='.$group_id.'">Admin</A></B><P>';
}

function mail_footer($params) {
	site_project_footer($params);
}

?>

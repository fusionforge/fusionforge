<?php

/**
 * GForge Forum Attachments download Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* attachment download
	by Daniel Perez - 2005
*/

require_once('pre.php');

/**
	 *  goodbye - Just prints a message and a close button.
	 *
	 *  @param  string	 The message.
	 */

function goodbye($msg) {
	echo "<center>" . $msg . "</center><p>";
	die ('<center><form method="post"><input type="button" value="Close Window" onclick="window.close()"></form></center>');
}


//TODO check user perms

$attachid = getIntFromRequest("attachid");
$delete = getStringFromRequest("delete");
$attach_userid = getIntFromRequest("attach_userid");
$group_id = getIntFromRequest("group_id");

if ($delete == "yes") {
	//only the user that created the attach can delete it (safecheck)
	if ($attach_userid != user_getid()) {
		goodbye("You cannot delete this attachment");
	}	else {
		db_query ("DELETE FROM forum_attachment where attachmentid=$attachid");
		goodbye("Attachment deleted");
	}
}

$sql = "SELECT  * FROM forum_attachment as attachment where attachment.attachmentid=$attachid";
$res = db_query($sql);
$extension = substr(strrchr(strtolower(db_result($res,0,'filename')), '.'), 1);
$sql = "SELECT * FROM forum_attachmenttype where extension = '$extension' and group_id = '$group_id'";
$res2 = db_query($sql);
if (db_numrows($res2)<1) {
	//use the default one
	$sql = "SELECT * FROM forum_attachmenttype where extension = '$extension' and group_id = -1";
	$res2 = db_query($sql);
}

if ( (!$res) || (!$res2) ) {
	exit_error("Attachment Download error","DB Error");
}

if ( db_numrows($res2)<1) {
	goodbye("The attachment type has been removed by the administrator.");
}

if ( (db_result($res2,0,'enabled') == 0 )) {
	goodbye("Attachment Type has been disabled by the administrator.");
}



$last = gmdate('D, d M Y H:i:s', db_result($res,0,'dateline'));
header('X-Powered-By:');
header('Cache-control: max-age=31536000');
header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
header('Last-Modified: ' . $last . ' GMT');
header('ETag: "' . db_result($res,0,'attachmentid') . '"');

if ($extension != 'txt') {
	header("Content-disposition: inline; filename=\"" . db_result($res,0,'filename') . "\"");
	header('Content-transfer-encoding: binary');
}	else {
	header("Content-disposition: attachment; filename=\"" . db_result($res,0,'filename') . "\"");
}

header('Content-Length: ' . db_result($res,0,'filesize') );

$mimetype = unserialize(db_result($res2,0,'mimetype'));
if (is_array($mimetype))
{
	foreach ($mimetype AS $index => $header) {
		header($header);
	}
}	else {
	header('Content-type: unknown/unknown');
}

$count = 1;
$filedata = base64_decode(db_result($res,0,'filedata'));
echo $filedata;
flush();
?>
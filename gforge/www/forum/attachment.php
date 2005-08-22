<?php

require_once('pre.php');

/**
	 *  goodbye - Just prints a message and a close button.
	 *
	 *  @param  string	 The message.
	 */

function goodbye($msg) {
	echo "<center>" . $msg . "</center><p>";
	die ('<form method="post"><input type="button" value="Close Window" onclick="window.close()"></form>');
}


//TODO check user perms

$attachid = getIntFromRequest("attachid");
$delete = getStringFromRequest("delete");
$attach_userid = getIntFromRequest("attach_userid");

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
$sql = "SELECT * FROM forum_attachmenttype where extension = '$extension'";
$res2 = db_query($sql);

if ( (!$res) || (!$res2) ) {
	exit_error("Attachment Download error","DB Error");
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
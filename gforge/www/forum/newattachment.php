<?php
/**
 * GForge New attachment page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
 *
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

/* 
	by Daniel Perez - 2005
*/

require_once('pre.php');
require_once('www/forum/include/AttachManager.class');

/**
 *  printmain() - Print the main form (first time)
 *
 */
function printmain() {
	$msg = '
	<script type="text/javascript">
	<!--
	function verify_upload(formobj)
	{
		var haveupload = false;
		for (var i=0; i < formobj.elements.length; i++)
		{
			var elm = formobj.elements[i];
			if (elm.type == \'file\')
			{
				if (elm.value != "")
				{
					haveupload = true;
				}
			}
		}

		if (haveupload)
		{
			toggle_display(\'uploading\');
			return true;
		}
		else
		{
			alert("Please select a file to attach using the \"Browse...\" button.");
			return false;
		}
	}
	//-->
	</script>';
	$msg .= 
		'	
		<form enctype="multipart/form-data" action="newattachment.php" name="newattachment" method="post">
		<input type="hidden" name="do" value="manageattach" />
		<input type="hidden" name="poststarttime" value="' . getStringFromRequest("poststarttime") . '1124376888" />
		<input type="hidden" name="editpost" value="0" />
		<input type="hidden" name="forum_id" value="' . getStringFromRequest("forum_id") . '" />
		<input type="hidden" name="group_id" value="' . getStringFromRequest("group_id") . '" />
		<input type="hidden" name="user_id" value="' . getStringFromRequest("user_id") . '" />
		<input type="hidden" name="posthash" value="' . getStringFromRequest("posthash") . '" />

		<table>
			<tr>
				<td>Use the "Browse" button to find the file you want to attach, then click "Upload" to add it to this post.</td>
			</tr>
			<tr>
				<td>File to Upload:   <input type="file" name="attachment1"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="upload" value="Upload" style="width:70px" onclick="return verify_upload(this.form);" /></td>
			</tr>
		</table>		
		';
	echo $msg;
}

require_once('pre.php');


//TODO // Guests can not post attachments


$sql = "SELECT * FROM forum_attachmenttype";
$res = db_query($sql);
$attachtypes = array();

//fill the datastore array with the supported filetypes
global $sys_db_row_pointer;
for ($i=0;$i<db_numrows($res);$i++) {
	$aux = db_fetch_array($res);
	$attachtypes[$aux[0]] = $aux;
}

if (!getIntFromRequest("forum_id"))
{
	die("missing params"); //TODO
}

if (getStringFromRequest("do")=="manageattach") {
	//uploading
	
	

	//foreach ($_FILES AS $upload => $attach)
	$attach = getUploadedFile("attachment1");
	
	$attachment = trim($attach['tmp_name']);
	$attachment_name = trim($attach['name']);
	$attachment_size = trim($attach['size']);
	
	
	//TODO algun control de filesize
	
	if ($attachment == 'none' OR empty($attachment) OR empty($attachment_name))
	{
		die("error, problem with the attachment");
	}
	
	$attachment_name2 = strtolower($attachment_name);
	$extension = substr(strrchr($attachment_name2, '.'), 1);
	
	if (!$attachtypes["$extension"] OR !$attachtypes["$extension"]['enabled'])
	{
		// invalid extension
		exit("invalid extension");
	}
	
	$maxattachsize = $attachtypes["$extension"]['size'];
	$filesize = filesize($attachment);
	
	if ($maxattachsize != 0 AND $filesize > $maxattachsize)
	{
		// too big
		@unlink($attachment);
		die("file too big for that file type");
	}
	
	if (!is_uploaded_file($attachment) || !($filestuff = @file_get_contents($attachment)) )
	{
		die("error, problem with the attachment file uploaded into the server");
	}
	
	
	$am = &getStringFromServer("currentam");
	$am->fillvalues(getStringFromRequest("user_id"),time(),addslashes($attachment_name),pg_escape_string($filestuff),$filesize,1,addslashes(md5($filestuff)),addslashes(getStringFromRequest("posthash")));
	
	@unlink($attachment);
	
}	else {
	printmain(); //first time call
}

?>

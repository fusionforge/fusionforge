<?php
/**
  *
  * Project Admin: Edit Multimedia Data
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


// Quota in bytes
$QUOTA = 1048576;

require_once('pre.php');    
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

function check_file_size($size) {
	global $feedback;

	if (($size > 20) && ($size < 256000)) {
		return true;
	} else {
		//too big or small
		$feedback .= ' ERROR - file must be > 20 bytes and < 256000 bytes in length<br />';
		return false;
	}
}

function store_file($id, $input_file, $input_file_name, $input_file_type) {
	global $group_id;
	global $description;
	global $feedback;

	if (!util_check_fileupload($input_file)) {
		exit_error("Error","Invalid filename");
	}

	$dimensions = @getimagesize($input_file);
	$size = @filesize($input_file);
	$data = addslashes(base64_encode(fread( fopen($input_file, 'rb'), $size)));
	$width=$dimensions[0];
	$height=$dimensions[1];

	if (check_file_size($size)) {
		$curtime = time();
		$sql="";
		$width = ((!$width) ? "0" : $width);
		$height = ((!$height) ? "0" : $height);
		if (!$id) {
			$sql="	INSERT INTO db_images 
					(group_id,description,bin_data, 
					 filename,filesize,filetype, 
					 width,height,upload_date,version) 
					VALUES 
					('$group_id','$description', 
					 '$data','$input_file_name', 
					 '$size','$input_file_type', 
					 '$width','$height','$curtime',1)";
		} else {
			$sql="	UPDATE db_images 
					SET description='$description', 
					 bin_data='$data', 
					 filename='$input_file_name', 
					 filesize='$size', 
					 filetype='$input_file_type', 
					 width='$width', 
					 height='$height', 
					 upload_date='$curtime',
					 version=version+1
					WHERE group_id='$group_id' 
					AND id='$id' ";
		}	

		$res = db_query($sql);

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= 'ERROR: DB: Cannot store multimedia file<br />';
			echo db_error();
		} else {
			$feedback .= ' Multimedia File Uploaded ';
		}
	}
}

if ($submit) {

	if (!util_check_fileupload($input_file)) {
		exit_error("Error","Invalid filename");
	}

	if ($add) {
		if ($input_file == "none" || $description == "") {
			$feedback .= ' Both file name and description are required';
		} else {
			//see if they have too many data in the system
			$res=db_query("SELECT sum(filesize) WHERE group_id='$group_id'");
			if (db_result($res,0,'sum') < $QUOTA) {
				store_file(0, $input_file, $input_file_name, $input_file_type);
			} else {
				$feedback .= ' Sorry - you are over your '.$QUOTA.' quota ';
			}
		}

	} else if ($remove) {

		$res=db_query("DELETE FROM db_images WHERE id='$id' AND group_id='$group_id'");

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= 'ERROR: DB: Cannot delete multimedia file<br />';
			echo db_error();
		} else {
			$feedback .= ' Multimedia File Deleted ';
		}

	} else if ($edit) {
		if ($description == "") {
			$feedback .= 'File description is required<b />';
		} else {
			if ($input_file=="none" || $input_file=="") {

				// Just replace description/mime type

				$res = db_query("UPDATE db_images 
						SET description='$description', 
						 filetype='$filetype' 
						WHERE group_id='$group_id' 
						AND id='$id' ");

				if (!$res || db_affected_rows($res) < 1) {
					$feedback .= 'ERROR: DB: Cannot update multimedia file<br />';
					echo db_error();
				} else {
					$feedback .= 'Multimedia File Properties Updated<br />';
				}

			} else {

				// new version of the file is uploaded
				// use new description, but not user-input
				// mime type

				//see if they have too many data in the system
				$res=db_query("	SELECT sum(filesize) 
						WHERE group_id='$group_id' 
						AND id<>'$id'");

				$size = @filesize($input_file);
				if (db_result($res,0,'sum')+$size < $QUOTA) {

					store_file($id, $input_file, $input_file_name, $input_file_type);

				} else {

					$feedback .= ' Sorry - you are over your 1MB quota ';

				}
			}
			
		}
	}
}

project_admin_header(array('title'=>'Edit Your Multimedia Data','pagename'=>'project_admin_editimages','sectionvals'=>array(group_getname($group_id))));

echo '
	<p>
	You can store up to '.sprintf("%.2f",$QUOTA/(1024*1024)).'MB 
	of multimedia data (bitmap and vector
	graphics, sound clips, 3D models) in the database. Use this
	page to add/delete your project multimedia data.</p>
	<p>
';

if ($mode == "edit") {
	$result=db_query("	SELECT * 
				FROM db_images 
				WHERE group_id='$group_id' 
				AND id='$id'");

	if (!$result || db_numrows($result)!=1) {
		$feedback .= "Cannot edit multimedia file<br />";
		project_admin_footer(array());
		exit();
	}

	echo '</p><h4>Edit Multimedia Data</h4>
	<p>
	<form action="'. $PHP_SELF .'" method="post" enctype="multipart/form-data">
	<input type="hidden" name="group_id" value="'.$group_id.'" />
	<input type="hidden" name="id" value="'.$id.'" />

	<strong>Replace with new file (optional):</strong><br />
	<input type="file" name="input_file" size="30" />
	<p>

	<strong>Description:</strong><br />
	<input type="text" name="description" size="40" maxlength="255" value="'.db_result($result,$i,'description').'" />
	</p>
	<p>
	<strong>MIME Type:</strong><br />
	<input type="text" name="filetype" size="40" maxlength="255" value="'.db_result($result,$i,'filetype').'" />

	<input type="hidden" name="edit" value="1" />

	<input type="submit" value="Submit Changes" name="submit" />
	<input type="reset" value="Undo" /><br />
	</form></p>
	';
} else {
	$result=db_query("	SELECT * 
				FROM db_images 
				WHERE group_id='$group_id' 
				ORDER BY id");

	echo '<h4>Add Multimedia Data</h4>
	<p>
	<form action="'. $PHP_SELF .'" method="post" enctype="multipart/form-data">
	<input type="hidden" name="group_id" value="'.$group_id.'" />
	<strong>Local filename:</strong>'.utils_requiredField().'<br />
	<input type="file" name="input_file" size="30" />
	<p>
	<strong>Description:</strong>'.utils_requiredField().'<br />
	<input type="text" name="description" size="40" maxlength="255" /></p><p>
	<input type="hidden" name="add" value="1" />
	<input type="submit" value="Add File" name="submit" /><br /></p>
	</form></p>
	';
}

$arr=array();
$arr[]='Edit';
$arr[]='ID';
$arr[]='Uploaded';
$arr[]='Name';
$arr[]='MIME Type';
$arr[]='Size';
$arr[]='Dims';
$arr[]='Description';

echo $GLOBALS['HTML']->listTableTop($arr);

$rows=db_numrows($result);
for ($i=0; $i<$rows; $i++) {

	// Show dimensions only for images
	$w = db_result($result,$i,'width');
	$h = db_result($result,$i,'height');
	if ($w || $h) {
		$dims = '('.$w.'x'.$h.')';
	} else {
		$dims = '&nbsp;';
	}

	echo '	  
	<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'
	.'<td align="center">'
	 .'<a href="'. $PHP_SELF .'?submit=1&amp;group_id='.$group_id.'&amp;remove=1&amp;id='
	 .db_result($result,$i,'id').'">'
	 .'[Del]'.'</a>'
	 .'<a href="'. $PHP_SELF .'?submit=1&amp;group_id='.$group_id.'&amp;mode=edit&amp;id='
	 .db_result($result,$i,'id').'"> '
	 .'[Edit]'.'</a>'
	.'</td>'

	.'<td>'.db_result($result,$i,'id').'</td>'

	.'<td>'.date('Y-m-d', db_result($result, $i, 'upload_date')).'</td>'

	.'<td><a href="/dbimage.php?id='.db_result($result,$i,'id').'">'
	     .stripslashes(db_result($result,$i,'filename')).'</a></td>'
	.'<td>'.db_result($result,$i,'filetype').'</td>'
	.'<td align="right">'.db_result($result,$i,'filesize').'</td>'
	.'<td align="right">'.$dims.'</td>'
	.'<td>'.stripslashes(db_result($result,$i,'description')).'</td>'
	.'</tr>';
}

echo $GLOBALS['HTML']->listTableBottom();

project_admin_footer(array());

?>

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
		$feedback .= ' ERROR - file must be > 20 bytes and < 256000 bytes in length<br>';
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
		if (!$id) {
			$res=db_query("	INSERT INTO db_images 
					(group_id,description,bin_data, 
					 filename,filesize,filetype, 
					 width,height,upload_date,version) 
					VALUES 
					('$group_id','$description', 
					 '$data','$input_file_name', 
					 '$size','$input_file_type', 
					 '$width','$height','$curtime',1)");
		} else {
			$res=db_query("	UPDATE db_images 
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
					AND id='$id' ");
		}	

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= 'ERROR: DB: Cannot store mutimedia file<br>';
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
			$feedback .= 'ERROR: DB: Cannot delete mutimedia file<br>';
			echo db_error();
		} else {
			$feedback .= ' Multimedia File Deleted ';
		}

	} else if ($edit) {
		if ($description == "") {
			$feedback .= 'File description is required<br>';
		} else {
			if ($input_file=="none" || $input_file=="") {

				// Just replace description/mime type

				$res = db_query("UPDATE db_images 
						SET description='$description', 
						 filetype='$filetype' 
						WHERE group_id='$group_id' 
						AND id='$id' ");

				if (!$res || db_affected_rows($res) < 1) {
					$feedback .= 'ERROR: DB: Cannot update mutimedia file<br>';
					echo db_error();
				} else {
					$feedback .= 'Multimedia File Properties Updated<br>';
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
	<P>
	You can store up to '.sprintf("%.2f",$QUOTA/(1024*1024)).'MB 
	of multimedia data (bitmap and vector
	graphics, sound clips, 3D models) in our database. Use this
	page to add/delete your project multimedia data.
	<P>
';

if ($mode == "edit") {
	$result=db_query("	SELECT * 
				FROM db_images 
				WHERE group_id='$group_id' 
				AND id='$id'");

	if (!$result || db_numrows($result)!=1) {
		$feedback .= "Cannot edit multimedia file<br>";
		project_admin_footer(array());
		exit();
	}

	echo '<H4>Edit Multimedia Data</H4>
	<P>
	<FORM ACTION="'. $PHP_SELF .'" METHOD="POST" enctype="multipart/form-data">
	<input type="hidden" name="group_id" VALUE="'.$group_id.'">
	<input type="hidden" name="id" VALUE="'.$id.'">

	<B>Replace with new file (optional):</B><BR>
	<input type="file" name="input_file" size="30">
	<P>

	<B>Description ("alt"):</B><BR>
	<input type="text" name="description" size="40" maxlength="255" value="'.db_result($result,$i,'description').'"><P>

	<P>
	<B>MIME Type:</B><BR>
	<input type="text" name="filetype" size="40" maxlength="255" value="'.db_result($result,$i,'filetype').'"><P>

	<input type="hidden" name="edit" VALUE="1">

	<input type="submit" value="Submit Changes" NAME="submit">
	<input type="reset" value="Undo"><BR>
	</form>
	';
} else {
	$result=db_query("	SELECT * 
				FROM db_images 
				WHERE group_id='$group_id' 
				ORDER BY id");

	echo '<H4>Add Multimedia Data</H4>
	<P>
	<FORM ACTION="'. $PHP_SELF .'" METHOD="POST" enctype="multipart/form-data">
	<input type="hidden" name="group_id" VALUE="'.$group_id.'">
	<B>Local filename:</B><BR>
	<input type="file" name="input_file" size="30">
	<P>
	<B>Description ("alt"):</B><BR>
	<input type="text" name="description" size="40" maxlength="255"><P>
	<input type="hidden" name="add" VALUE="1">
	<input type="submit" value="Add File" NAME="submit"><BR>
	</form>
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

echo html_build_list_table_top($arr);

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
	<TR BGCOLOR="'. html_get_alt_row_color($i) .'">'
	.'<TD ALIGN="MIDDLE">'
	 .'<A HREF="'. $PHP_SELF .'?submit=1&group_id='.$group_id.'&remove=1&id='
	 .db_result($result,$i,'id').'">'
//	.html_image("images/ic/trash.png","16","16",array("BORDER"=>"0")) . '</A></TD>'
	 .'[Del]'.'</A>'
	 .'<A HREF="'. $PHP_SELF .'?submit=1&group_id='.$group_id.'&mode=edit&id='
	 .db_result($result,$i,'id').'"> '
	 .'[Edit]'.'</A>'
	.'</TD>'

	.'<TD>'.db_result($result,$i,'id').'</TD>'

	.'<TD>'.date('Y-m-d', db_result($result, $i, 'upload_date')).'</TD>'

	.'<TD><a href="/dbimage.php?id='.db_result($result,$i,'id').'">'
	     .stripslashes(db_result($result,$i,'filename')).'</a></TD>'
	.'<TD>'.db_result($result,$i,'filetype').'</TD>'
	.'<TD align=right>'.db_result($result,$i,'filesize').'</TD>'
	.'<TD align=right>'.$dims.'</TD>'
	.'<TD>'.stripslashes(db_result($result,$i,'description')).'</TD>'
	.'</TR>';
}
echo '</TABLE>';

project_admin_footer(array());

?>

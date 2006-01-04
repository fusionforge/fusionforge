<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/



require_once('pre.php');
require_once('common/docman/Document.class');
require_once('common/docman/DocumentGroupFactory.class');
require_once('include/doc_utils.php');
require_once('include/DocumentGroupHTML.class');
require_once('common/include/TextSanitizer.class'); // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
if (!$group_id) {
	exit_no_group();	
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_error('Error','Could Not Get Group');	
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());	
}

$upload_dir = $sys_ftp_upload_dir . "/" . $g->getUnixName();

if (getStringFromRequest('submit')) {
	$doc_group = getIntFromRequest('doc_group');
	$title = getStringFromRequest('title');
	$description = getStringFromRequest('description');
	$file_url = getStringFromRequest('file_url');
	$ftp_filename = getStringFromRequest('ftp_filename');
	$uploaded_data = getUploadedFile('uploaded_data');
	$language_id = getIntFromRequest('language_id');
	$type = getStringFromRequest('type');
	$name = getStringFromRequest('name');

	if (!$doc_group || $doc_group == 100) {
		//cannot add a doc unless an appropriate group is provided		
		exit_error($Language->getText('general','error'),$Language->getText('docman_new','no_valid_group'));
	}
	
	if (!$title || !$description || (!$uploaded_data && !$file_url && !$ftp_filename && (!$editor && !$name ) )) {		
		exit_missing_param();
	}

	$d = new Document($g);
	if (!$d || !is_object($d)) {		
		exit_error($Language->getText('general','error'),$Language->getText('docman_new','error_blank_document'));
	} elseif ($d->isError()) {	
		exit_error($Language->getText('general','error'),$d->getErrorMessage());
	}
	
	switch ($type) {
		case 'editor' : {
			$data = getStringFromRequest('data');
			$uploaded_data_name = $name;
			$sanitizer = new TextSanitizer();
			$data = $sanitizer->SanitizeHtml($data);
			if (strlen($data)<1) {
				exit_error($Language->getText('general','error'),$Language->getText('docman_new','error_blank_document'));
			}
			$uploaded_data_type='text/html';
			break;
		}
		case 'pasteurl' : {
			$data = '';
			$uploaded_data_name=$file_url;
			$uploaded_data_type='URL';		
			break;
		}
		case 'httpupload' : {
			if (!is_uploaded_file($uploaded_data['tmp_name'])) {			
				exit_error($Language->getText('general','error'),$Language->getText('general','invalid_filename'));
			}
			$data = addslashes(fread(fopen($uploaded_data['tmp_name'], 'r'), $uploaded_data['size']));
			$file_url='';
			$uploaded_data_name=$uploaded_data['name'];
			$uploaded_data_type=$uploaded_data['type'];
			break;
		}
		case 'ftpupload' : {	
			$uploaded_data_name=$upload_dir.'/'.$ftp_filename;
			$data = addslashes(fread(fopen($uploaded_data_name, 'r'), filesize($uploaded_data_name)));
		}
	}
	
	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description)) {
			exit_error($Language->getText('general','error'),$d->getErrorMessage());
	} else {		
		if ($type == 'editor') {
			//release the cookie for the document contents (should expire at the end of the session anyway)
			setcookie ("gforgecurrentdocdata", "", time() - 3600);
		}
		Header("Location: /docman/?group_id=$group_id&feedback=".$Language->getText('docman_new','submitted_successfully'));
		exit;
	}

} else {
	
	if (getStringFromRequest('Option')) {
		//option was selected, proceed to show each one
		$option_selected = getStringFromRequest('option_selected');
		docman_header($Language->getText('docman_new','title'),$Language->getText('docman_new','section'));
		echo'<p>'. $Language->getText('docman_new','intro') .'</p>
		<form name="adddata" action="'. getStringFromServer('PHP_SELF').'?group_id='.$group_id.'" method="post" enctype="multipart/form-data">
		<table border="0" width="75%">
		<tr>
			<td>
			<strong>'. $Language->getText('docman_new','doc_title').' :</strong>'. utils_requiredField(). $Language->getText('general', 'min_characters', array(5)).'<br />
			<input type="text" name="title" size="40" maxlength="255" />
			</td>
		</tr>
	
		<tr>
			<td>
			<strong>'. $Language->getText('docman_new','description') .' :</strong>'. utils_requiredField() . $Language->getText('general', 'min_characters', array(10)).'<br />
			<input type="text" name="description" size="50" maxlength="255" />
			</td>
		</tr>';		
		switch ($option_selected) {
			case 'httpupload' : {
				echo '
					<tr>
						<td>
						<strong>'. $Language->getText('docman_new','upload_file') .' :</strong>'. utils_requiredField() .'<br />
						<input type="file" name="uploaded_data" size="30" /><br /><br />
						<input type="hidden" name="type" value="httpupload">
						</td>
					</tr>';
				break;
			}
			case 'ftpupload' : {
				if ($sys_use_ftpuploads) {
					echo '
						<tr>
							<td>
							<strong>'.$Language->getText('docman_new','ftpupload_new_file',array($sys_ftp_upload_host)).'<br />';
					echo $Language->getText('docman_new','ftpupload_choosefile').'</strong>'. utils_requiredField() .'<br />';
					$ftp_files_arr=array_merge($arr,ls($upload_dir,true));
					echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename','');
					echo '</td></tr><input type="hidden" name="type" value="ftpupload">';
					break;
				} else {
					exit_error($Language->getText('docman_new','enableftpfirst'));
				}
				break;
			}
			case 'pasteurl' : {
				echo '
					<tr>
						<td>
						<strong>'. $Language->getText('docman_new','upload_url').' :</strong>'. utils_requiredField() .'<br />
						<input type="text" name="file_url" size="50" />
						<input type="hidden" name="type" value="pasteurl">
						</td>
					</tr>';
				break;
			}
			case 'editor' : {
				echo '<SCRIPT LANGUAGE="JavaScript">';
				echo 'function openEditor(group) {
				newwin=window.open("doceditor.php?group_id=" + group, "dispwin", "width=850,height=550,scrollbars=yes,menubar=no");
				}';
				echo '</SCRIPT>';
				echo '
					<tr>
						<td>';
				
				echo "<strong>" . $Language->getText('docman_new','name') . '</strong>'.utils_requiredField().'<input type="text" name="name" ><br>';
				echo '<a href="javascript:openEditor('.$group_id.');">'.$Language->getText('docman_new','edit').'</a>';
				echo '<input type="hidden" name="data">';
				echo '<input type="hidden" name="type" value="editor">';
				echo '</td>
						</tr>';
				break;		
			}
		}
		echo '
			<tr>
				<td>
				<strong>'. $Language->getText('docman_new','language').' :</strong><br />
				'. html_get_language_popup($Language,'language_id',1) .'
				</td>
			</tr>
			<tr>
				<td>
				<strong>'. $Language->getText('docman_new','group').' :</strong><br />';
		$dgf = new DocumentGroupFactory($g);
		if ($dgf->isError()) {
			exit_error('Error',$dgf->getErrorMessage());
		}
		$dgh = new DocumentGroupHTML($g);
		if ($dgh->isError()) {
			exit_error('Error',$dgh->getErrorMessage());
		}
		//display_groups_option($group_id);
		$selected_doc_group=getIntFromRequest('selected_doc_group');
		$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $selected_doc_group);
		echo '
				</td>
			</tr>
		</table>
		<input type="submit" name="submit" value="'. $Language->getText('docman_new','submit').' " />
			</form>';
		docman_footer(array());
	} else {
		docman_header($Language->getText('docman_new','title'),$Language->getText('docman_new','section'));
		?>
		
		<?php
		echo '<form name="select_opt" action="'. getStringFromServer('PHP_SELF').'?group_id='.$group_id.'" method="post">';
		$vals['httpupload'] = $Language->getText('docman','httpupload');
		$vals['ftpupload'] = $Language->getText('docman','ftpupload');
		$vals['pasteurl'] = $Language->getText('docman','pasteurl');
		$vals['editor'] = $Language->getText('docman','editor');
		echo $Language->getText('docman','select');
		echo html_build_select_box_from_assoc($vals,'option_selected');
		echo '   <input type="submit" value="'. $Language->getText('docman_new','selectsubmit') .'" name="Option">';
		echo '</form>';
		docman_footer(array());
	}		
}

?>

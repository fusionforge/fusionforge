<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2010-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012,2015-2016, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'docman/include/utils.php';

global $warning_msg;
global $error_msg;

$sysdebug_enable = false;

$arr = explode('/', getStringFromServer('REQUEST_URI'));
$group_id = (int) $arr[3];
$docid = isset($arr[4])? $arr[4]: '';

$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	if ($g->isPermissionDeniedError()) {
		exit_permission_denied();
	}
	exit_error($g->getErrorMessage(), 'docman');
}

if (is_numeric($docid)) {
	session_require_perm('docman', $group_id, 'read');
	$d = new Document($g, $docid);

	if (!$d || !is_object($d)) {
		exit_error(_('Document is not available.'), 'docman');
	} elseif ($d->isError()) {
		exit_error($d->getErrorMessage(), 'docman');
	}

	/**
	 * except for active (1), we need more right access than just read
	 */
	switch ($d->getStateID()) {
		case '2':
		case '3':
		case '4':
		case '5': {
			session_require_perm('docman', $group_id, 'approve');
			break;
		}
	}

	ob_end_clean();
	$file_path = $d->getFilePath();
	$length = filesize($file_path);
	$d->downloadUp();
	utils_headers_download($d->getFileName(), $d->getFileType(), $length);
	readfile_chunked($file_path);
} elseif ($docid === 'versions') {
	// let get the specific version
	$doc_id = (int) $arr[5];
	$version_id = (int) $arr[6];
	session_require_perm('docman', $group_id, 'read');
	$d = new Document($g, $doc_id);

	if (!$d || !is_object($d)) {
		exit_error(_('Document is not available.'), 'docman');
	} elseif ($d->isError()) {
		exit_error($d->getErrorMessage(), 'docman');
	}

	/**
	 * except for active (1), we need more right access than just read
	 */
	switch ($d->getStateID()) {
		case '2':
		case '3':
		case '4':
		case '5': {
			session_require_perm('docman', $group_id, 'approve');
			break;
		}
	}

	$dv = documentversion_get_object($version_id, $doc_id, $group_id);
	if (!$dv || !is_object($dv)) {
		exit_error(_('Document Version is not available.'), 'docman');
	} elseif ($dv->isError()) {
		exit_error($dv->getErrorMessage(), 'docman');
	}

	if (!$dv->isCurrent()) {
		session_require_perm('docman', $group_id, 'approve');
	}

	ob_end_clean();
	$file_path = $dv->getFilePath();
	$length = filesize($file_path);
	$d->downloadUp();
	utils_headers_download($dv->getFileName(), $dv->getFileType(), $length);
	readfile_chunked($file_path);

} elseif ($docid === 'backup') {
	if (extension_loaded('zip')) {
		session_require_perm('docman', $group_id, 'admin');

		$df = new DocumentFactory($g);
		if ($df->isError())
			exit_error($df->getErrorMessage(), 'docman');

		$dgf = new DocumentGroupFactory($g);
		if ($dgf->isError())
			exit_error($dgf->getErrorMessage(), 'docman');

		$nested_groups = $dgf->getNested(array(1, 5));

		if ( $nested_groups != NULL ) {
			$filename = 'docman-'.$g->getUnixName().'-'.$docid.'.zip';
			$file = forge_get_config('data_path').'/docman/'.$filename;

			$zip = new ZipArchive;
			if ( !$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
				@unlink($file);
				exit_error(_('Unable to open ZIP archive for backup'), 'docman');
			}

			if ( !docman_fill_zip($zip, $nested_groups, $df)) {
				@unlink($file);
				exit_error(_('Unable to fill ZIP archive for backup'), 'docman');
			}

			if ( !$zip->close()) {
				@unlink($file);
				exit_error(_('Unable to close ZIP archive for backup'), 'docman');
			}

			header('Content-disposition: attachment; filename="'.$filename.'"');
			// please do not set the Content-type: it breaks IE support.
			if (!preg_match('/trident/i', $_SERVER['HTTP_USER_AGENT'])) {
				header('Content-type: application/zip');
			}
			header("Content-Transfer-Encoding: binary");
			ob_end_clean();

			if(!readfile_chunked($file)) {
				@unlink($file);
				$error_msg = _('Unable to download backup file');
				session_redirect('/docman/?group_id='.$group_id.'&view=admin');
			}
			@unlink($file);
		} else {
			$warning_msg = _('No documents to backup.');
			session_redirect('/docman/?group_id='.$group_id.'&view=admin');
		}
	} else {
		$warning_msg = _('ZIP extension is missing: no backup function');
		session_redirect('/docman/?group_id='.$group_id.'&view=admin');
	}
} elseif ($docid === 'webdav') {
	if (forge_get_config('use_webdav') && $g->useWebdav()) {
		require_once $gfcommon.'docman/include/webdav.php';
		$_SERVER['SCRIPT_NAME'] = '';
		/* we need the group id for check authentification. */
		$_SERVER['AUTH_TYPE'] = $group_id;
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="Webdav Access (No anonymous access)"');
			header('HTTP/1.0 401 Unauthorized');
			echo _('Webdav Access Canceled by user');
			die();
		}
		$server = new HTTP_WebDAV_Server_Docman;
		$server->ServeRequest();
	} else {
		$warning_msg = _('No Webdav interface enabled.');
		session_redirect('/docman/?group_id='.$group_id);
	}
} elseif ($docid === 'zip') {
	session_require_perm('docman', $group_id, 'read');
	if (extension_loaded('zip')) {
		if ( $arr[5] === 'full' ) {

			$dirid = $arr[6];

			$dg = new DocumentGroup($g, $dirid);
			if ($dg->isError())
				exit_error($dg->getErrorMessage(), 'docman');

			$df = new DocumentFactory($g);
			if ($df->isError())
				exit_error($df->getErrorMessage(), 'docman');

			$dgf = new DocumentGroupFactory($g);
			if ($dgf->isError())
				exit_error($dgf->getErrorMessage(), 'docman');

			$stateidArr = array(1);
			if (forge_check_perm('docman', $g->getID(), 'approve')) {
				$stateidArr = array(1, 4, 5);
			}

			$nested_groups = $dgf->getNested($stateidArr);

			if ($dg->hasDocuments($nested_groups, $df)) {
				$filename = 'docman-'.$g->getUnixName().'-'.$dg->getID().'.zip';
				$file = forge_get_config('data_path').'/docman/'.$filename;
				@unlink($file);
				$zip = new ZipArchive;
				if ( !$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
					@unlink($file);
					exit_error(_('Unable to open ZIP archive for download as ZIP'), 'docman');
				}

				// ugly workaround to get the files at doc_group_id level
				$stateidArr = array(1);
				$stateIdDg = 1;
				if (forge_check_perm('docman', $df->Group->getID(), 'approve')) {
					$stateidArr = array(1, 4, 5);
					$stateIdDg = 5;
				}
				$df->setDocGroupID($dg->getID());
				$df->setStateID($stateidArr);
				$df->setDocGroupState($stateIdDg);
				$docs = $df->getDocuments(1);	// no caching
				if (is_array($docs) && count($docs) > 0) {	// this group has documents
					foreach ($docs as $doc) {
						if ($doc->isURL()) {
							continue;
						}
						if (!$zip->addFromString(iconv("UTF-8", "ASCII//TRANSLIT", $doc->getFileName()), $doc->getFileData()))
							exit_error(_('Unable to fill ZIP file.'), 'docman');
					}
				}
				if ( !docman_fill_zip($zip, $nested_groups, $df, $dg->getID())) {
					@unlink($file);
					exit_error(_('Unable to fill ZIP archive for download as ZIP'), 'docman');
				}

				if ( !$zip->close()) {
					@unlink($file);
					exit_error(_('Unable to close ZIP archive for download as ZIP'), 'docman');
				}

				header('Content-disposition: attachment; filename="'.$filename.'"');
				// please do not set the Content-type: it breaks IE support.
				if (!preg_match('/trident/i', $_SERVER['HTTP_USER_AGENT'])) {
					header('Content-type: application/zip');
				}
				header("Content-Transfer-Encoding: binary");
				ob_end_clean();

				if(!readfile_chunked($file)) {
					unlink($file);
					$error_msg = _('Unable to download ZIP archive');
					session_redirect('/docman/?group_id='.$group_id.'&view=admin');
				}
				unlink($file);
			} else {
				$warning_msg = _('This documents folder is empty.');
				session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
			}
		} elseif ( $arr[5] === 'selected' ) {
			$dirid = $arr[6];
			$arr_fileid = explode(',', $arr[7]);
			$filename = 'docman-'.$g->getUnixName().'-selected-'.time().'.zip';
			$file = forge_get_config('data_path').'/docman/'.$filename;
			@unlink($file);
			$zip = new ZipArchive;
			if (!$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
				@unlink($file);
				exit_error(_('Unable to open ZIP archive for download as ZIP'), 'docman');
			}
			foreach ($arr_fileid as $docid) {
				if (!empty($docid)) {
					if (strpos($docid, '-') !== false) {
						$docArr = explode('-', $docid);
						$g = group_get_object($docArr[0]);
						$docid =  $docArr[1];
					}
					$d = new Document($g, $docid);
					if (!$d || !is_object($d)) {
						@unlink($file);
						exit_error(_('Document is not available.'), 'docman');
					} elseif ($d->isError()) {
						@unlink($file);
						exit_error($d->getErrorMessage(), 'docman');
					}
					if ($d->isURL()) {
						continue;
					}
					if (!$zip->addFromString(iconv('UTF-8', 'ASCII//TRANSLIT', $d->getFileName()), $d->getFileData())) {
						@unlink($file);
						exit_error(_('Unable to fill ZIP file.'), 'docman');
					}
				} else {
					$zip->close();
					unlink($file);
					$warning_msg = _('No action to perform');
					$redirect_url = '/docman/?group_id='.$group_id.'&view=listfile';
					if (is_numeric($dirid)) {
						$redirect_url .= '&dirir='.$dirid;
					}
					session_redirect($redirect_url);
				}
			}

			if (!$zip->close()) {
				@unlink($file);
				exit_error(_('Unable to close ZIP archive for download as ZIP'), 'docman');
			}

			header('Content-disposition: attachment; filename="'.$filename.'"');
			// please do not set the Content-type: it breaks IE support.
			if (!preg_match('/trident/i', $_SERVER['HTTP_USER_AGENT'])) {
				header('Content-type: application/zip');
			}
			header('Content-Transfer-Encoding: binary');
			ob_end_clean();

			if(!readfile_chunked($file)) {
				unlink($file);
				$error_msg = _('Unable to download ZIP archive');
				//session_redirect('/docman/?group_id='.$group_id);
			}
			unlink($file);
		} else {
			exit_error(_('No document to display - invalid or inactive document number.'), 'docman');
		}
	} else {
		exit_error(_('PHP ZIP extension is missing.'), 'docman');
	}
} else {
	exit_error(_('No document to display - invalid or inactive document number.'), 'docman');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2010-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012,2014, Franck Villaume - TrivialDev
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

$no_gz_buffer = true;

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'docman/include/utils.php';

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
	$docname = urldecode($arr[5]);

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
		case "2":
		case "3":
		case "4":
		case "5": {
			session_require_perm('docman', $group_id, 'approve');
			break;
		}
	}

	/**
	 * If the served document has wrong relative links, then
	 * theses links may redirect to the same document with another
	 * name, this way a search engine may loop and stress the
	 * server.
	 */
	if ($d->getFileName() != $docname) {
		session_redirect('/docman/view.php/'.$group_id.'/'.$docid.'/'.urlencode($d->getFileName()));
	}

	header('Content-disposition: attachment; filename="'.str_replace('"', '', $d->getFileName()) . '"');
	header("Content-type: ".$d->getFileType());
	header("Content-Transfer-Encoding: binary");
	ob_end_clean();

	$file_path = $d->getFilePath();
	$length = filesize($file_path);
	$d->downloadUp();
	header("Content-length: $length");
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

		$nested_groups = $dgf->getNested();

		if ( $nested_groups != NULL ) {
			$filename = 'docman-'.$g->getUnixName().'-'.$docid.'.zip';
			$file = forge_get_config('data_path').'/docman/'.$filename;

			$zip = new ZipArchive;
			if ( !$zip->open($file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
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
				session_redirect('/docman/?group_id='.$group_id.'&view=admin&error_msg='.urlencode($error_msg));
			}
			@unlink($file);
		} else {
			$warning_msg = _('No documents to backup.');
			session_redirect('/docman/?group_id='.$group_id.'&view=admin&warning_msg='.urlencode($warning_msg));
		}
	} else {
		$warning_msg = _('ZIP extension is missing: no backup function');
		session_redirect('/docman/?group_id='.$group_id.'&view=admin&warning_msg='.urlencode($warning_msg));
	}
} elseif ($docid === 'webdav') {
	if (forge_get_config('use_webdav') && $g->useWebDav()) {
		require_once $gfcommon.'docman/include/webdav.php';
		$_SERVER['SCRIPT_NAME'] = '';
		/* we need the group id for check authentification. */
		$_SERVER["AUTH_TYPE"] = $group_id;
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="Webdav Access" (For anonymous access : click enter)');
			header('HTTP/1.0 401 Unauthorized');
			echo _('Webdav Access Canceled by user');
			die();
		}
		$server = new HTTP_WebDAV_Server_Docman;
		$server->ServeRequest();
	} else {
		$warning_msg = _('No Webdav interface enabled.');
		session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($warning_msg));
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

			$nested_groups = $dgf->getNested();

			if ($dg->hasDocuments($nested_groups, $df)) {
				$filename = 'docman-'.$g->getUnixName().'-'.$dg->getID().'.zip';
				$file = forge_get_config('data_path').'/docman/'.$filename;
				@unlink($file);
				$zip = new ZipArchive;
				if ( !$zip->open($file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
					@unlink($file);
					exit_error(_('Unable to open ZIP archive for download as ZIP'), 'docman');
				}

				// ugly workaround to get the files at doc_group_id level
				$df->setDocGroupID($dg->getID());
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
					session_redirect('/docman/?group_id='.$group_id.'&view=admin&error_msg='.urlencode($error_msg));
				}
				unlink($file);
			} else {
				$warning_msg = _('This documents folder is empty.');
				session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($warning_msg));
			}
		} elseif ( $arr[5] === 'selected' ) {
			$dirid = $arr[6];
			$arr_fileid = explode(',',$arr[7]);
			$filename = 'docman-'.$g->getUnixName().'-selected-'.time().'.zip';
			$file = forge_get_config('data_path').'/docman/'.$filename;
			@unlink($file);
			$zip = new ZipArchive;
			if (!$zip->open($file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
				@unlink($file);
				exit_error(_('Unable to open ZIP archive for download as ZIP'), 'docman');
			}

			foreach($arr_fileid as $docid) {
				if (!empty($docid)) {
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
					if (!$zip->addFromString(iconv("UTF-8", "ASCII//TRANSLIT", $d->getFileName()), $d->getFileData())) {
						@unlink($file);
						exit_error(_('Unable to fill ZIP file.'), 'docman');
					}
				} else {
					$zip->close();
					unlink($file);
					$warning_msg = _('No action to perform');
					session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($warning_msg));
				}
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
				session_redirect('/docman/?group_id='.$group_id.'&view=admin&error_msg='.urlencode($error_msg));
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

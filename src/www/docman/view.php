<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'docman/include/utils.php';

$sysdebug_enable = false;

$arr = explode('/', getStringFromServer('REQUEST_URI'));
$group_id = $arr[3];
$docid = $arr[4];

$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error($g->getErrorMessage(), 'docman');
}

if ($docid != 'backup' && $docid != 'webdav' && $docid != 'zip') {
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
	 *
	 * A workaround is to serve only the document if the given
	 * name is correct.
	 */
	if ($d->getFileName() != $docname) {
		exit_error(_('No document to display - invalid or inactive document number'), 'docman');
	}

	header('Content-disposition: attachment; filename="'.str_replace('"', '', $d->getFileName()) . '"');

	if (strstr($d->getFileType(), 'app')) {
		header("Content-type: application/binary");
	} else {
		header("Content-type: ".$d->getFileType());
	}
	header("Content-Transfer-Encoding: binary");
	ob_end_clean();
	echo $d->getFileData();

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
			$file = forge_get_config('data_path').'/'.$filename;
			$zip = new ZipArchive;
			if ( !$zip->open($file, ZIPARCHIVE::OVERWRITE)) {
				exit_error(_('Unable to open zip archive for backup'), 'docman');
			}

			if ( !docman_fill_zip($zip, $nested_groups, $df))
				exit_error(_('Unable to fill zip archive for backup'), 'docman');

			if ( !$zip->close())
				exit_error(_('Unable to close zip archive for backup'), 'docman');

			header('Content-disposition: attachment; filename="'.$filename.'"');
			header('Content-type: application/zip');
			header("Content-Transfer-Encoding: binary");
			ob_end_clean();

			if(!readfile_chunked($file)) {
				unlink($file);
				$error_msg = _('Unable to download backup file');
				session_redirect('/docman/?group_id='.$group_id.'&view=admin&error_msg='.urlencode($error_msg));
			}
			unlink($file);
		} else {
			$warning_msg = _('No documents to backup.');
			session_redirect('/docman/?group_id='.$group_id.'&view=admin&warning_msg='.urlencode($warning_msg));
		}
	} else {
		$warning_msg = _('Zip extension is missing: no backup function');
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
		$warning_msg = _('No webdav interface enabled.');
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
				$file = forge_get_config('data_path').'/'.$filename;
				$zip = new ZipArchive;
				if ( !$zip->open($file, ZIPARCHIVE::OVERWRITE))
					exit_error(_('Unable to open zip archive for download as zip'), 'docman');

				// ugly workaround to get the files at doc_group_id level
				$df->setDocGroupID($dg->getID());
				$docs = $df->getDocuments(1);	// no caching
				if (is_array($docs) && count($docs) > 0) {	// this group has documents
					foreach ($docs as $doc) {
						if ( !$zip->addFromString($doc->getFileName(),$doc->getFileData()))
							return false;
					}
				}
				if ( !docman_fill_zip($zip, $nested_groups, $df, $dg->getID()))
					exit_error(_('Unable to fill zip archive for download as zip'), 'docman');

				if ( !$zip->close())
					exit_error(_('Unable to close zip archive for download as zip'), 'docman');

				header('Content-disposition: attachment; filename="'.$filename.'"');
				header('Content-type: application/zip');
				header("Content-Transfer-Encoding: binary");
				ob_end_clean();

				if(!readfile_chunked($file)) {
					unlink($file);
					$error_msg = _('Unable to download zip archive');
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
			$file = forge_get_config('data_path').'/'.$filename;
			$zip = new ZipArchive;
			if ( !$zip->open($file, ZIPARCHIVE::OVERWRITE))
				exit_error(_('Unable to open zip archive for download as zip'), 'docman');

			foreach($arr_fileid as $docid) {
				if (!empty($docid)) {
					$d = new Document($g, $docid);
					if (!$d || !is_object($d)) {
						exit_error(_('Document is not available.'), 'docman');
					} elseif ($d->isError()) {
						exit_error($d->getErrorMessage(), 'docman');
					}

					if ( !$zip->addFromString($d->getFileName(),$d->getFileData()))
						return false;
				} else {
					$zip->close();
					unlink($file);
					$warning_msg = _('No action to perform');
					session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($warning_msg));
				}
			}
			if ( !$zip->close())
				exit_error(_('Unable to close zip archive for download as zip'), 'docman');

			header('Content-disposition: attachment; filename="'.$filename.'"');
			header('Content-type: application/zip');
			header("Content-Transfer-Encoding: binary");
			ob_end_clean();

			if(!readfile_chunked($file)) {
				unlink($file);
				$error_msg = _('Unable to download zip archive');
				session_redirect('/docman/?group_id='.$group_id.'&view=admin&error_msg='.urlencode($error_msg));
			}
			unlink($file);
		} else {
			exit_error(_('No document to display - invalid or inactive document number.'), 'docman');
		}
	} else {
		exit_error(_('PHP extension is missing.'), 'docman');
	}
} else {
	exit_error(_('No document to display - invalid or inactive document number.'), 'docman');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

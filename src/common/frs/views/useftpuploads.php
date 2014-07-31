<?php
/**
 * FusionForge FRS : use ftp upload include view
 *
 * Copyright 2014 Franck Villaume - TrivialDev
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $content; // the preexistant html content
global $upload_dir; // the upload diretory of the project

$localcontent = sprintf(_('Alternatively, you can use FTP to upload a new file at %s.'), forge_get_config('ftp_upload_host'));
$localcontent .= html_e('br');
$localcontent .= _('Choose an FTP file instead of uploading')._(':').html_e('br');
$ftp_files_arr = frs_filterfiles(ls($upload_dir, true));
$localcontent .= html_build_select_box_from_arrays($ftp_files_arr, $ftp_files_arr, 'ftp_filename', '');
$content .= html_e('p', array(), $localcontent);

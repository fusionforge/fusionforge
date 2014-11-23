<?php
/**
 * FusionForge FRS : use manual upload include view
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
global $g; // group object
global $content; // the preexistant html content
global $HTML;

$incoming = forge_get_config('groupdir_prefix').'/'.$g->getUnixName().'/incoming';
$localcontent = sprintf(_('Alternatively, you can use a file you already uploaded (by SFTP or SCP) to the <a href="%2$s">project\'s incoming directory</a> (%1$s).'),
                       $incoming, 'sftp://'.forge_get_config('web_host').$incoming.'/');
$localcontent .= ' ' . _('This direct <samp>sftp://</samp> link only works with some browsers, such as Konqueror.') .html_e('br');
$localcontent .= _('Choose an already uploaded file:').html_e('br');
$manual_files_arr = frs_filterfiles(ls($incoming, true));
if (count($manual_files_arr)) {
	$localcontent .= html_build_select_box_from_arrays($manual_files_arr, $manual_files_arr, 'manual_filename', '');
} else {
	$localcontent .= $HTML->information(_('No uploaded file available.'));
}
$content .= html_e('p', array(), $localcontent);

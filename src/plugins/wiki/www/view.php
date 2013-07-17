<?php
/*
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Configuration File ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

$no_gz_buffer = true;

require_once '../../env.inc.php';
require_once $gfcommon . 'include/pre.php';

$sysdebug_enable = false;

if (isset($group_id) && $group_id) {
    if (!isset($project) || !$project) {
        $project = group_get_object($group_id);
    }
} elseif (isset($project) && is_object($project)) {
    $group_id = $project->getID();
}

if (!isset($group_id) || !isset($project)) {
    exit_no_group();
} elseif (!($project->usesPlugin("wiki"))) {
    exit_disabled('home');
}

// If project is private, check membership.
if (!$project->isPublic()) {
    session_require_perm('project_read', $project->getID());
}

$arr = explode('/', urldecode(getStringFromServer('REQUEST_URI')));
array_shift($arr);
array_shift($arr);
array_shift($arr);
array_shift($arr);
array_shift($arr);
$path = join('/', $arr);

$basepath = realpath(forge_get_config('groupdir_prefix') .'/'. $project->getUnixName() . '/www/uploads/');
$filepath = realpath($basepath . '/' . $path);
$filename = basename($filepath);

if (strncmp($basepath, $filepath, strlen($basepath)) !== 0) {
    error_log("DEBUG: basepath=$basepath, filepath=$filepath");
    exit_error('Invalid path: No access');
}

if ($filepath && is_file($filepath)) {
    if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
        # workaround for IE filename bug with multiple periods/ multiple dots in filename
        # that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
        $filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
    }
    $filename = str_replace('"', '', $filename);
    header('Content-disposition: filename="' . $filename . '"');

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $filepath);
    } else {
        $mimetype = 'application/octet-stream';
    }
    header("Content-type: $mimetype");

    $length = filesize($filepath);
    header("Content-length: $length");

    readfile_chunked($filepath);

} else {
    header("HTTP/1.0 404 Not Found");
    require_once $gfwww . '404.php';
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/**
 * Copyright © 2005,2007 Reini Urban
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
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

// FIXME! This is a mess. Everything.
require_once 'lib/stdlib.php';

$remove = 0;
if (preg_match('/^(http|ftp|https):\/\//i', $_REQUEST['url'])) {

    $data_path = '';
    list($usec, $sec) = explode(" ", microtime());

    $fp = fopen('config/config.ini', 'r');
    while ($config = fgetcsv($fp, 1024, ';')) {
        if (preg_match('/DATA_PATH/', $config[0])) {
            list($key, $value) = explode('=', $config[0]);
            $data_path = trim($value) . '/';
            break;
        }
    }
    fclose($fp);
    @mkdir($data_path . "uploads/thumbs", 0775);
    $file = $data_path . "uploads/thumbs/image_" . ((float)$usec + (float)$sec);
    $source = url_get_contents($_REQUEST['url']);

    @$fp = fopen($file, 'w+');
    if (!$fp) {
        header("Content-type: text/html");
        echo "<!DOCTYPE html>\n";
        echo "<html xml:lang=\"en\" lang=\"en\">\n";
        echo "<head>\n";
        echo "<title>ERROR: unable to open $file in write mode</title>\n";
        echo "</head>\n";
        echo "<body>\n";
        echo "<p>ERROR: unable to open $file in write mode</p>\n";
        echo "</body>\n";
        echo "</html>";
    }
    fwrite($fp, $source);
    $remove = 1;

} else {
    @$fp = fopen($_REQUEST['url'], "r");

    if (!$fp) {

        header("Content-type: text/html");
        echo "<!DOCTYPE html>\n";
        echo "<html xml:lang=\"en\" lang=\"en\">\n";
        echo "<head>\n";
        echo "<title>Not an image</title>\n";
        echo "</head>\n";
        echo "<body>\n";
        echo "<p>Not an image</p>\n";
        echo "</body>\n";
        echo "</html>";
        exit();

    } else {
        $file = $_REQUEST['url'];
        fclose($fp);
    }
}
list ($a, $b, $type, $attr) = @getimagesize($file);

if (!$type) {
    $type = basename($_REQUEST['url']);
    $type = preg_split('/\./', $type);
    $type = array_pop($type);
}

switch ($type) {
    case '2':
        if (function_exists("imagecreatefromjpeg"))
            $img = @imagecreatefromjpeg($file);
        else
            show_plain();
        break;
    case '3':
        if (function_exists("imagecreatefrompng"))
            $img = @imagecreatefrompng($file);
        else
            show_plain();
        break;
    case '1':
        if (function_exists("imagecreatefromgif"))
            $img = @imagecreatefromgif($file);
        else
            show_plain();
        break;
    case '15':
        if (function_exists("imagecreatefromwbmp"))
            $img = @imagecreatefromwbmp($file);
        else
            show_plain();
        break;
    case '16':
        if (function_exists("imagecreatefromxbm"))
            $img = @imagecreatefromxbm($file);
        else
            show_plain();
        break;
    case 'xpm':
        if (function_exists("imagecreatefromxpm"))
            $img = @imagecreatefromxpm($file);
        else
            show_plain();
        break;
    case 'gd':
        if (function_exists("imagecreatefromgd"))
            $img = @imagecreatefromgd($file);
        else
            show_plain();
        break;
    case 'gd2':
        if (function_exists("imagecreatefromgd2"))
            $img = @imagecreatefromgd2($file);
        else
            show_plain();
        break;
    default:
        //we are not stupid...
        header("Content-type: text/html");
        echo "<!DOCTYPE html>\n";
        echo "<html xml:lang=\"en\" lang=\"en\">\n";
        echo "<head>\n";
        echo "<title>Not an image</title>\n";
        echo "</head>\n";
        echo "<body>\n";
        echo "<p>Not an image</p>\n";
        echo "</body>\n";
        echo "</html>";
        exit();
        break;
}

$width = @imagesx($img);
$height = @imagesy($img);

$newwidth = $_REQUEST['width'];
if (empty($newidth)) $newidth = 50;

$newheight = $_REQUEST['height'];
if (empty($newheight)) $newheight = round($newwidth * ($height / $width));

// php-4.2.x is stupid enough to define on gd only a stub for imagecopyresampled.
// So function_exists('imagecopyresampled') will fail.
if (!extension_loaded('gd2') and (substr(PHP_OS, 0, 3) != 'WIN'))
    loadPhpExtension('gd2');
if (extension_loaded('gd2')) {
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $img = imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
} else {
    $thumb = imagecreate($newwidth, $newheight);
    $img = imagecopyresized($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
}

if ($remove == 1) unlink($file);

header("Content-type: image/png");
imagepng($thumb);

function show_plain()
{
    $mime = mime_content_type($_REQUEST['url']);
    header("Content-type: $mime");
    readfile($_REQUEST['url']);
    exit();
}

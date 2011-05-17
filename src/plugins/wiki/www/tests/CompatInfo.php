<?php
/* Get the Compatibility info for phpwiki
   http://pear.php.net/package/PHP_CompatInfo

   $Id: CompatInfo.php 7638 2010-08-11 11:58:40Z vargenau $
*/
/*
 * Copyright (C) 2004 Reini Urban <rurban@x-ray.at>
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'PHP/CompatInfo.php';

function out_row($row, $header = false) {
    if (empty($row)) return;
    echo "<tr>";
    $tag = $header ? "th" : "td";
    // link to file?
    $file = $row[0];
    if (!empty($file) and substr($file,0,3) != '<b>' and $file != 'File') {
        $row[0] = '<a href="'.$PHP_SELF.'?file='.urlencode($file).'">'.$file.'</a>';
    }
    foreach ($row as $r) {
        echo "<$tag>", empty($r) ? '&nbsp;' : "$r", "</$tag>";
    }
    echo "</tr>\n";
}

$info = new PHP_CompatInfo;
$dir = str_replace(array('\\','/'), '/', dirname(__FILE__));
$dir = preg_replace('/\/'.basename(dirname(__FILE__)).'$/', '', $dir);
$debug = !empty($_GET['debug']);
$detail = !empty($_GET['detail']);
// echo $dir;
$options = array('file_ext' 	=> array('php'),
                 'ignore_files' => array(__FILE__),
                 'recurse_dir'  => true,
                 'ignore_functions' => array(),
                 'debug' => $debug or $detail,
                 );
// var_dump($options);
set_time_limit(240);
$cols = array('File','Version','Extensions','Constants');

if (empty($_GET['file'])) {
    $file = false;
    echo "<h1>All Files</h1>\n";
    echo " Show Details: <a href=\"",$_SERVER["SCRIPT_NAME"],"?detail=1\">YES</a>";
    echo " <a href=\"",$_SERVER["SCRIPT_NAME"],"\">NO</a> <br>\n";
    $r = $info->parseFolder($dir, $options);
} else {
    $file = urldecode($_GET['file']);
    echo "<h1>File $file</h1>\n";
    echo " Show Details: <a href=\"",$_SERVER["SCRIPT_NAME"],"?file=$file&detail=1\">YES</a>";
    echo " <a href=\"",$_SERVER["SCRIPT_NAME"],"?file=$file\">NO</a> <br>\n";
    echo " =&gt; <a href=\"",$_SERVER["SCRIPT_NAME"],"\">Back to All Files</a><br>\n";
    $r = $info->parseFile("$dir/$file", $options);
}

echo "<table border=\"1\">\n";

out_row($cols,1);

foreach ($r as $key => $info) {

    if ($key == 'extensions')
        out_row(array("<b>$key</b>", '', join(', ',$info), ''));
    elseif ($key == 'constants')
        out_row(array("<b>$key</b>", '', '', join(', ',$info)));
    elseif ($key == 'version')
        out_row(array("<b>$key</b>", $info, '', ''));
    elseif ($key == 'ignored_files')
        out_row(array("<b>$key</b>", join(',',$info), ''));
    else{
        if (empty($_GET['file'])) {
            $file = str_replace(array('\\','/'),'/',$key);
            if (strlen($key) > strlen($dir))
                $file = substr(str_replace($dir,'',$file),1);
            if (!isset($info['extensions'][0])) {
                $ext = '';
            } else {
                $ext = array_shift($info['extensions']);
            }
            if (!isset($info['constants'][0])) {
                $const = '';
            } else {
                $const = array_shift($info['constants']);
            }
            out_row(array($file, $info['version'], $ext, $const));
          
            if (is_array($info['extensions'])
                and sizeof($info['extensions']) >= sizeof($info['constants'])) {
                foreach ($info['extensions'] as $i => $ext) {
                    if (isset($info['constants'][$i])) {
                        $const = $info['constants'][$i];
                    } else {
                        $const = '';
                    }
                    out_row(array('','', $ext, $const));
                }
            } elseif (is_array($info['constants'])) {
                foreach ($info['constants'] as $i => $const) {
                    if (isset($info['extensions'][$i])) {
                        $ext = $info['extensions'][$i];
                    } else {
                        $ext = '';
                    }
                    out_row(array('', '', $ext, $const));
                }
            }
        }
        if ($detail and is_array($info)) {
            out_row(array('<b><i>DETAIL</i></b>','Version','Function','Extension'),1);
            unset($info['version']);
            unset($info['constants']);
            unset($info['extensions']);
            if (!empty($_GET['file'])) {
                $version = $key;
                foreach ($info as $func) {
                    out_row(array('',$version,$func['function'],$func['extension']));
                }
            } else {
                foreach ($info as $version => $functions) {
                    foreach ($functions as $func) {
                        out_row(array('',$version,$func['function'],$func['extension']));
                    }
                }
            }
        }
    }
}
echo "</table>\n";

if ($debug) {
    echo "<pre>\n";
    var_dump ($r);
    echo "</pre>\n";
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:  
?>

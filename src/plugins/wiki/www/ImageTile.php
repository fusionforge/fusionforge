<?php // -*-php-*- $Id: ImageTile.php 7960 2011-03-04 13:58:21Z vargenau $
// FIXME! This is a mess. Everything.
require_once('lib/stdlib.php');

$remove = 0;
if (preg_match('/^(http|ftp|https):\/\//i',$_REQUEST['url'])) {
    
    $data_path = '';
    list($usec, $sec) = explode(" ", microtime());
    
    $fp = fopen('config/config.ini','r');
    while ($config = fgetcsv($fp,1024,';')) {
        if (preg_match('/DATA_PATH/',$config[0])) {
            list($key,$value) = explode('=', $config[0]);
            $data_path = trim($value).'/';
        break;
    }
    }
    fclose($fp);
    @mkdir($data_path."uploads/thumbs",0775);
    $file = $data_path."uploads/thumbs/image_" . ((float)$usec + (float)$sec);
    $source = url_get_contents($_REQUEST['url']);

    @$fp = fopen($file,'w+');
    if (!$fp) {
        header ("Content-type: text/html");
        echo "<html><head></head><body>ERROR : unable to open $file in write mode</body></html>";
    }
    fwrite($fp,$source);
    $remove = 1;
    
} else {
    @$fp = fopen($_REQUEST['url'],"r");
    
    if (!$fp) {
    
        header ("Content-type: text/html");
        echo "<html><head></head><body>Not an image</body></html>";
        exit();

    } else {
        $file = $_REQUEST['url'];
        fclose($fp);
    }
}
list ($a, $b, $type, $attr) = @getimagesize ($file);

if (!$type) {
    $type = basename ($_REQUEST['url']);
    $type = preg_split ('/\./',$type);
    $type = array_pop ($type);
}

switch ($type) {
    case '2':
        if (function_exists("imagecreatefromjpeg"))
            $img = @imagecreatefromjpeg ($file);
        else
            show_plain ($file);
        break;
    case '3':
        if (function_exists("imagecreatefrompng"))
            $img = @imagecreatefrompng ($file);
        else
            show_plain ($file);
        break;
    case '1':
        if (function_exists("imagecreatefromgif"))
            $img = @imagecreatefromgif ($file);
        else
            show_plain ($file);
        break;
    case '15':
        if (function_exists("imagecreatefromwbmp"))
            $img = @imagecreatefromwbmp ($file);
        else
            show_plain ($file);
        break;
    case '16':
        if (function_exists("imagecreatefromxbm"))
            $img = @imagecreatefromxbm ($file);
        else
            show_plain ($file);
        break;
    case 'xpm':
        if (function_exists("imagecreatefromxpm"))
            $img = @imagecreatefromxpm ($file);
        else
            show_plain ($file);
        break;
    case 'gd':
        if (function_exists("imagecreatefromgd"))
            $img = @imagecreatefromgd ($file);
        else
            show_plain ($file);
        break;
    case 'gd2':
        if (function_exists("imagecreatefromgd2"))
            $img = @imagecreatefromgd2 ($file);
        else
            show_plain ($file);
        break;
    default:
        //we are not stupid...
        header ("Content-type: text/html");
        echo "<html><head></head><body>Not an image</body></html>";
        exit();
        break;
}    

$width  = @imagesx($img);
$height = @imagesy($img);

$newwidth = $_REQUEST['width'];
if (empty($newidth)) $newidth = 50;
    
$newheight = $_REQUEST['height'];
if (empty($newheight)) $newheight = round($newwidth * ($height / $width)) ;

// php-4.2.x is stupid enough to define on gd only a stub for imagecopyresampled.
// So function_exists('imagecopyresampled') will fail.
if (!extension_loaded('gd2') and (substr(PHP_OS,0,3) != 'WIN'))
    loadPhpExtension('gd2');
if (extension_loaded('gd2')) {
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $img = imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
} else {
    $thumb = imagecreate($newwidth, $newheight);
    $img = imagecopyresized($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
}

if ($remove == 1) unlink ($file);

header ("Content-type: image/png");
imagepng($thumb);

function show_plain () {
    $mime = mime_content_type ($_REQUEST['url']);
    header ("Content-type: $mime");
    readfile($_REQUEST['url']);
    exit();
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

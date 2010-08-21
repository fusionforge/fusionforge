<?php // -*-php-*-
// rcs_id('$Id: PhotoAlbum.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
 * Copyright 2003,2004,2005,2007 $ThePhpWikiProgrammingTeam
 * Copyright 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Display an album of a set of photos with optional descriptions.
 *
 * @author: Ted Vinke <teddy@jouwfeestje.com>
 *          Reini Urban (local fs)
 *          Thomas Harding (slides mode, real thumbnails)
 *
 * Usage:
 * <<PhotoAlbum
 *          src="http://server/textfile" or localfile or localdir
 *          mode=[normal|column|row|thumbs|tiles|list|slide]
 *          desc=true
 *          numcols=3
 *          height=50%
 *          width=50%
 *          thumbswidth=80
 *          align=[center|left|right]
 *          duration=6
 * >>
 *
 * "src": textfile of images or directory of images or a single image (local or remote)
 *      Local or remote e.g. http://myserver/images/MyPhotos.txt or http://myserver/images/
 *      or /images/ or Upload:photos/
 *      Possible content of a valid textfile:
 *     photo-01.jpg; Me and my girlfriend
 *     photo-02.jpg
 *     christmas.gif; Merry Christmas!
 *
 *     Inside textfile, filenames and optional descriptions are seperated by
 *     semi-colon on each line. Listed files must be in same directory as textfile
 *     itself, so don't use relative paths inside textfile.
 *
 * "url": defines the the webpath to the srcdir directory (formerly called weblocation)
 */

/**
 * TODO:
 * - specify picture(s) as parameter(s)
 * - limit amount of pictures on one page
 * - use PHP to really resize or greyscale images (only where GD library supports it)
 *   (quite done for resize with "ImageTile.php")
 *
 * KNOWN ISSUES:
 * - reading height and width from images with spaces in their names fails.
 *
 * Fixed album location idea by Philip J. Hollenback. Thanks!
 */

class ImageTile extends HtmlElement
{
    // go away, hack!
    function image_tile (/*...*/) {
        $el = new HTML ('img');
        $tag = func_get_args();
        $path = DATA_PATH . "/ImageTile.php";
        $params = "<img src=\"$path?url=". $tag[0]['src'];
        if (!@empty($tag[0]['width']))
            $params .= "&width=" . $tag[0]['width'];
        if (!@empty($tag[0]['height']))
            $params .= "&height=" . $tag[0]['height'];
        if (!@empty($tag[0]['width']))
            $params .= '" width="' . $tag[0]['width'];
        if (!@empty($tag[0]['height']))
            $params .= '" height="' . $tag[0]['height'];

        $params .= '" alt="' . $tag[0]['alt'] . '" />';
        return $el->raw ($params);
    }
}

class WikiPlugin_PhotoAlbum
extends WikiPlugin
{
    function getName () {
        return _("PhotoAlbum");
    }

    function getDescription () {
        return _("Displays a set of photos listed in a text file with optional descriptions");
    }

// Avoid nameclash, so it's disabled. We allow any url.
// define('allow_album_location', true);
// define('album_location', 'http://kw.jouwfeestje.com/foto/redactie');
// define('album_default_extension', '.jpg');
// define('desc_separator', ';');

    function getDefaultArguments() {
        return array('src'      => '',          // textfile of image list, or local dir.
                     'url'      => '',          // if src=localfs, url prefix (webroot for the links)
                     'mode'    => 'normal',     // normal|thumbs|tiles|list
                         // "normal" - Normal table which shows photos full-size
                         // "thumbs" - WinXP thumbnail style
                         // "tiles"  - WinXP tiles style
                         // "list"   - WinXP list style
                         // "row"    - inline thumbnails
                         // "column" - photos full-size, displayed in 1 column
                         // "slide"  - slideshow mode, needs javascript on client
                     'numcols'    => 3,        // photos per row, columns
                     'showdesc'    => 'both',    // none|name|desc|both
                         // "none"   - No descriptions next to photos
                         // "name"   - Only filename shown
                         // "desc"   - Only description (from textfile) shown
                         // "both"     - If no description found, then filename will be used
                     'link'    => true,     // show link to original sized photo
                         // If true, each image will be hyperlinked to a page where the single
                         // photo will be shown full-size. Only works when mode != 'normal'
                     'attrib'    => '',        // 'sort, nowrap, alt'
                         // attrib arg allows multiple attributes: attrib=sort,nowrap,alt
                         // 'sort' sorts alphabetically, 'nowrap' for cells, 'alt' to use
                        // descs instead of filenames in image ALT-tags
                     'bgcolor'  => '#eae8e8',        // cell bgcolor (lightgrey)
                     'hlcolor'        => '#c0c0ff',        // highlight color (lightblue)
                     'align'        => 'center',        // alignment of table
                     'height'   => 'auto',        // image height (auto|75|100%)
                     'width'    => 'auto',        // image width (auto|75|100%)
                     // Size of shown photos. Either absolute value (e.g. "50") or
                     // HTML style percentage (e.g. "75%") or "auto" for no special
                     // action.
                     'cellwidth'=> 'image',        // cell (auto|equal|image|75|100%)
                     // Width of cells in table. Either absolute value in pixels, HTML
                     // style percentage, "auto" (no special action), "equal" (where
                     // all columns are equally sized) or "image" (take height and
                     // width of the photo in that cell).
                     'tablewidth'=> false,    // table (75|100%)
                     'p'    => false,     // "displaythissinglephoto.jpg"
                     'h'    => false,     // "highlightcolorofthisphoto.jpg"
                     'duration' => 6, // in slide mode, in seconds
                     'thumbswidth' => 80 //width of thumbnails
                     );
    }
    // descriptions (instead of filenames) for image alt-tags

    function run($dbi, $argstr, &$request, $basepage) {

        extract($this->getArgs($argstr, $request));

        $attributes = $attrib ? explode(",", $attrib) : array();
        $photos = array();
        $html = HTML();
        $count = 0;
        // check all parameters
        // what type do we have?
        if (!$src) {
            $showdesc  = 'none';
            $src   = $request->getArg('pagename');
            $error = $this->fromLocation($src, $photos);
        } else {
            $error = $this->fromFile($src, $photos, $url);
        }
        if ($error) {
            return $this->error($error);
        }

        if ($numcols < 1) $numcols = 1;
        if ($align != 'left' && $align != 'center' && $align != 'right') {
            $align = 'center';
        }
        if (count($photos) == 0) return;

        if (in_array("sort", $attributes))
            sort($photos);

        if ($p) {
            $mode = "normal";
        }

        if ($mode == "column") {
            $mode="normal";
            $numcols="1";
        }

        // set some fixed properties for each $mode
        if ($mode == 'thumbs' || $mode == 'tiles') {
            $attributes = array_merge($attributes, "alt");
            $attributes = array_merge($attributes, "nowrap");
            $cellwidth  = 'auto'; // else cell won't nowrap
            if ($width == 'auto') $width = 70;
        } elseif ($mode == 'list') {
            $numcols    = 1;
            $cellwidth  = "auto";
            if ($width == 'auto') $width = 50;
        } elseif ($mode == 'slide' ) {
            $tableheight = 0;
            $cell_width = 0;
            $numcols = count($photos);
            $keep = $photos;
            while (list($key, $value) = each($photos)) {
                list($x,$y,$s,$t) = @getimagesize($value['src']);
                if ($height != 'auto') $y = $this->newSize($y, $height);
                if ($width != 'auto') $y = round($y * $this->newSize($x, $width) / $x);
                if ($x > $cell_width) $cell_width = $x;
                if ($y > $tableheight) $tableheight = $y;
            }
            $tableheight += 50;
            $photos = $keep;
            unset ($x,$y,$s,$t,$key,$value,$keep);
        }

        $row = HTML();
        $duration = 1000 * $duration;
        if ($mode == 'slide')
            $row->pushContent(JavaScript("
i = 0;
function display_slides() {
  j = i - 1;
  cell0 = document.getElementsByName('wikislide' + j);
  cell = document.getElementsByName('wikislide' + i);
  if (cell0.item(0) != null)
    cell0.item(0).style.display='none';
  if (cell.item(0) != null)
    cell.item(0).style.display='block';
  i += 1;
  if (cell.item(0) == null) i = 0;
  setTimeout('display_slides()',$duration);
}
display_slides();"));

        while (list($key, $value) = each($photos))  {
            if ($p && basename($value["name"]) != "$p") {
                continue;
            }
            if ($h && basename($value["name"]) == "$h") {
                $color = $hlcolor ? $hlcolor : $bgcolor;
            } else {
                $color = $bgcolor;
            }
            // $params will be used for each <img > tag
            $params = array('src'    => $value["name"],
                            'src_tile' => $value["name_tile"],
                            'alt'    => ($value["desc"] != "" and in_array("alt", $attributes))
                                            ? $value["desc"]
                                            : basename($value["name"]));
            if (!@empty($value['location']))
                $params = array_merge($params, array("location" => $value['location']));
            // check description
            switch ($showdesc) {
            case 'none':
                $value["desc"] = '';
                break;
            case 'name':
                $value["desc"] = basename($value["name"]);
                break;
            case 'desc':
                break;
            default: // 'both'
                if (!$value["desc"]) $value["desc"] = basename($value["name"]);
                break;
            }

            // FIXME: get getimagesize to work with names with spaces in it.
            // convert $value["name"] from webpath to local path
            $size = @getimagesize($value["name"]); // try " " => "\\ "
            if (!$size and !empty($value["src"])) {
                $size = @getimagesize($value["src"]);
                if (!$size) {
                    trigger_error("Unable to getimagesize(".$value["name"].")",
                                  E_USER_NOTICE);
                }
            }
            $newwidth = $this->newSize($size[0], $width);
            if ($width != 'auto' && $newwidth > 0) {
                $params = array_merge($params, array("width" => $newwidth));
            }
            if (($mode == 'thumbs' || $mode == 'tiles' || $mode == 'list')) {
                if (!empty($size[0])) {
                    $newheight = round ($newwidth * $size[1] / $size[0]);
                    $params['width'] = $newwidth;
                    $params['height'] = $newheight;
                } else  $newheight = '';
                if ($height == 'auto') $height=150;
            }
            else {
                $newheight = $this->newSize($size[1], $height);
                if ($height != 'auto' && $newheight > 0) {
                    $params = array_merge($params, array("height" => $newheight));
                }
            }

            // cell operations
            $cell = array('align'   => "center",
                          'valign'  => "top",
                          'class'   => 'photoalbum cell',
                          'bgcolor' => "$color");
            if ($cellwidth != 'auto') {
                if ($cellwidth == 'equal') {
                    $newcellwidth = round(100/$numcols)."%";
                } else if ($cellwidth == 'image') {
                    $newcellwidth = $newwidth;
                } else {
                    $newcellwidth = $cellwidth;
                }
                $cell = array_merge($cell, array("width" => $newcellwidth));
            }
            if (in_array("nowrap", $attributes)) {
                $cell = array_merge($cell, array("nowrap" => "nowrap"));
            }
            //create url to display single larger version of image on page
            $url = WikiURL($request->getPage(),
                           array("p" => basename($value["name"])))
                . "#"
                . basename($value["name"]);

            $b_url = WikiURL($request->getPage(),
                             array("h" => basename($value["name"])))
                . "#"
                . basename($value["name"]);
            $url_text = $link
                ? HTML::a(array("href" => "$url"), basename($value["desc"]))
                : basename($value["name"]);
            if (! $p) {
                if ($mode == 'normal' || $mode == 'slide') {
                    if(!@empty($params['location'])) $params['src'] = $params['location'];
                    unset ($params['location'],$params['src_tile']);
                    $url_image = $link ? HTML::a(array("id" => basename($value["name"]),
                                                       "href" => "$url"), HTML::img($params))
                                       : HTML::img($params);
                } else {
                    $keep = $params;
                    if (!@empty ($params['src_tile']))
                        $params['src'] = $params['src_tile'] ;
                    unset ($params['location'],$params['src_tile']);
                    $url_image = $link ? HTML::a(array("id" => basename($value["name"]),
                                                       "href" => "$url"),
                                                 ImageTile::image_tile($params))
                                       : HTML::img($params);
                    $params = $keep;
                    unset ($keep);
                }
            } else {
                if(!@empty($params['location'])) $params['src'] = $params['location'];
                unset ($params['location'],$params['src_tile']);
                $url_image = $link ? HTML::a(array("id" =>  basename($value["name"]),
                                                   "href" => "$b_url"), HTML::img($params))
                                   : HTML::img($params);
            }
            if ($mode == 'list')
                $url_text = HTML::a(array("id" => basename($value["name"])),
                                      $url_text);
            // here we use different modes
            if ($mode == 'tiles') {
                $row->pushContent(
                    HTML::td($cell,
                             HTML::div(array('valign' => 'top'), $url_image),
                             HTML::div(array('valign' => 'bottom'),
                                       HTML::div(array('class'=>'boldsmall'),
                                                  ($url_text)),
                                       HTML::br(),
                                       HTML::div(array('class'=>'gensmall'),
                                                  ($size[0].
                                                   " x ".
                                                   $size[1].
                                                   " pixels"))))
                    );
            } elseif ($mode == 'list') {
                $desc = ($showdesc != 'none') ? $value["desc"] : '';
                $row->pushContent(
                    HTML::td(array("valign"  => "top",
                                   "nowrap"  => 0,
                                   "bgcolor" => $color),
                                   HTML::div(array('class'=>'boldsmall'),($url_text))));
                $row->pushContent(
                    HTML::td(array("valign"  => "top",
                                   "nowrap"  => 0,
                                   "bgcolor" => $color),
                                   HTML::div(array('class'=>'gensmall'),
                                              ($size[0].
                                               " x ".
                                               $size[1].
                                               " pixels"))));

                if ($desc != '')
                    $row->pushContent(
                        HTML::td(array("valign"  => "top",
                                       "nowrap"  => 0,
                                       "bgcolor" => $color),
                                       HTML::div(array('class'=>'gensmall'),$desc)));

            } elseif ($mode == 'thumbs') {
                $desc = ($showdesc != 'none') ?
                            HTML::p(HTML::a(array("href" => "$url"),
                                    $url_text)) : '';
                $row->pushContent(
                        (HTML::td($cell,
                                  $url_image,
                                  // FIXME: no HtmlElement for fontsizes?
                                  // rurban: use ->setAttr("style","font-size:small;")
                                  //         but better use a css class
                                  HTML::div(array('class'=>'gensmall'),$desc)
                                  )));
            } elseif ($mode == 'normal') {
                $desc = ($showdesc != 'none') ? HTML::p($value["desc"]) : '';
                $row->pushContent(
                        (HTML::td($cell,
                                  $url_image,
                                  // FIXME: no HtmlElement for fontsizes?
                                  HTML::div(array('class'=>'gensmall'),$desc)
                                  )));
            } elseif ($mode == 'slide') {
                if ($newwidth == 'auto' || !$newwidth)
                    $newwidth = $this->newSize($size[0],$width);
                if ($newwidth == 'auto' || !$newwidth)
                    $newwidth = $size[0];
                if ($newheight != 'auto') $newwidth = round($size[0] *  $newheight / $size[1]);
                $desc = ($showdesc != 'none') ? HTML::p($value["desc"]) : '';
                if ($count == 0)
                    $cell=array('style' => 'display: block; '
                                . 'position: absolute; '
                                . 'left: 50% ; '
                                . 'margin-left: -'.round($newwidth / 2).'px;'
                                . 'text-align: center; '
                                . 'vertical-align: top',
                                'name' => "wikislide".$count);
                else
                    $cell=array('style' => 'display: none; '
                                . 'position: absolute ;'
                                . 'left: 50% ;'
                                . 'margin-left: -'.round($newwidth / 2).'px;'
                                . 'text-align: center; '
                                . 'vertical-align: top',
                                'name' => "wikislide".$count);
                if ($align == 'left' || $align == 'right') {
                    if ($count == 0)
                        $cell=array('style' => 'display: block; '
                                              .'position: absolute; '
                                              . $align.': 50px; '
                                              .'vertical-align: top',
                                    'name' => "wikislide".$count);
                    else
                        $cell=array('style' => 'display: none; '
                                              .'position: absolute; '
                                              . $align.': 50px; '
                                              .'vertical-align: top',
                                    'name' => "wikislide".$count);
                    }
                $row->pushContent(
                                  (HTML::td($cell,
                                            $url_image,
                                            HTML::div(array('class'=>'gensmall'), $desc)
                                            )));
                $count ++;
            } elseif ($mode == 'row') {
                $desc = ($showdesc != 'none') ? HTML::p($value["desc"]) : '';
                $row->pushContent(
                                  HTML::table(array("style" => "display: inline",
                                                    'class' > "photoalbum row"),
                              HTML::tr(HTML::td($url_image)),
                              HTML::tr(HTML::td(array("class" => "gensmall",
                                                      "style" => "text-align: center; "
                                                                ."background-color: $color"),
                                                $desc))
                                    ));
            } else {
                return $this->error(fmt("Invalid argument: %s=%s", 'mode', $mode));
            }

            // no more images in one row as defined by $numcols
            if ( ($key + 1) % $numcols == 0 ||
                 ($key + 1) == count($photos) ||
                 $p) {
                    if ($mode == 'row')
                        $html->pushcontent(HTML::div($row));
                    else
                        $html->pushcontent(HTML::tr($row));
                    $row->setContent('');
            }
        }

        //create main table
        $table_attributes = array("border"      => 0,
                                  "cellpadding" => 5,
                                  "cellspacing" => 2,
                                  "class"       => "photoalbum",
                                  "width"       => $tablewidth ? $tablewidth : "100%");

        if (!empty($tableheight))
            $table_attributes = array_merge($table_attributes,
                                            array("height"  => $tableheight));
        if ($mode != 'row')
            $html = HTML::table($table_attributes, $html);
        // align all
        return HTML::div(array("align" => $align), $html);
    }

    /**
     * Calculate the new size in pixels when the original size
     * with a value is given.
     *
     * @param integer $oldSize Absolute no. of pixels
     * @param mixed $value Either absolute no. or HTML percentage e.g. '50%'
     * @return integer New size in pixels
     */
    function newSize($oldSize, $value) {
        if (trim(substr($value,strlen($value)-1)) != "%") {
            return $value;
        }
        $value = str_replace("%", "", $value);
        return round(($oldSize*$value)/100);
    }

    /**
    * fromLocation - read only one picture from fixed album_location
    * and return it in array $photos
    *
    * @param string $src Name of page
    * @param array $photos
    * @return string Error if fixed location is not allowed
    */
    function fromLocation($src, &$photos) {
            /*if (!allow_album_location) {
                return $this->error(_("Fixed album location is not allowed. Please specify parameter src."));
        }*/
        //FIXME!
        if (! IsSafeURL($src)) {
            return $this->error(_("Bad url in src: remove all of <, >, \""));
        }
            $photos[] = array ("name" => $src, //album_location."/$src".album_default_extension,
                           "desc" => "");
    }

    /**
     * fromFile - read pictures & descriptions (separated by ;)
     *            from $src and return it in array $photos
     *
     * @param string $src path to dir or textfile (local or remote)
     * @param array $photos
     * @return string Error when bad url or file couldn't be opened
     */
    function fromFile($src, &$photos, $webpath='') {
        $src_bak = $src;
        if (preg_match("/^Upload:(.*)$/", $src, $m)) {
            $src = getUploadFilePath() . $m[1];
            $webpath = getUploadDataPath() . $m[1];
        }
        //there has a big security hole... as loading config/config.ini !
        if (!preg_match('/(\.csv|\.jpg|\.jpeg|\.png|\.gif|\/)$/',$src)) {
           return $this->error(_("File extension for csv file has to be '.csv'"));
        }
        if (! IsSafeURL($src)) {
            return $this->error(_("Bad url in src: remove all of <, >, \""));
        }
        if (preg_match('/^(http|ftp|https):\/\//i', $src)) {
            $contents = url_get_contents($src);
            $web_location = 1;
        } else {
            $web_location = 0;
            if (string_ends_with($src,"/"))
               $src = substr($src,0,-1);
        }
        if (!file_exists($src) and @file_exists(PHPWIKI_DIR . "/$src")) {
            $src = PHPWIKI_DIR . "/$src";
        }
        // check if src is a directory
        if (file_exists($src) and filetype($src) == 'dir') {
            //all images
            $list = array();
            foreach (array('jpeg','jpg','png','gif') as $ext) {
                $fileset = new fileSet($src, "*.$ext");
                $list = array_merge($list, $fileset->getFiles());
            }
            // convert dirname($src) (local fs path) to web path
            natcasesort($list);
            if (! $webpath ) {
                // assume relative src. default: "themes/Hawaiian/images/pictures"
                $webpath = DATA_PATH . '/' . $src_bak;
            }
            foreach ($list as $file) {
                // convert local path to webpath
                $photos[] = array ("src" => $file,
                                   "name" => $webpath . "/$file",
                                   "name_tile" =>  $src . "/$file",
                                   "src"  => $src . "/$file",
                                   "desc" => "");
            }
            return;
        }
        // check if $src is an image
        foreach (array('jpeg','jpg','png','gif') as $ext) {
            if (preg_match("/\.$ext$/", $src)) {
                if (!file_exists($src) and @file_exists(PHPWIKI_DIR . "/$src"))
                    $src = PHPWIKI_DIR . "/$src";
                if ($web_location == 1 and !empty($contents)) {
                    $photos[] = array ("src" => $src,
                                       "name" => $src,
                                       "name_tile" => $src,
                                       "src"  => $src,
                                       "desc" => "");
                    return;
                }
                if (!file_exists($src))
                    return $this->error(fmt("Unable to find src='%s'", $src));
                $photos[] = array ("src" => $src,
                                   "name" => "../".$src,
                                   "name_tile" =>  $src,
                                   "src"  => $src,
                                   "desc" => "");
                return;
            }
        }
        if ($web_location == 0) {
            $fp = @fopen($src, "r");
            if (!$fp) {
                return $this->error(fmt("Unable to read src='%s'", $src));
            }
            while ($data = fgetcsv($fp, 1024, ';')) {
                if (count($data) == 0 || empty($data[0])
                                      || preg_match('/^#/',$data[0])
                                      || preg_match('/^[[:space:]]*$/',$data[0]))
                    continue;
                if (empty($data[1])) $data[1] = '';
                $photos[] = array ("name" => dirname($src)."/".trim($data[0]),
                                   "location" => "../".dirname($src)."/".trim($data[0]),
                                   "desc" => trim($data[1]),
                                   "name_tile" => dirname($src)."/".trim($data[0]));
            }
            fclose ($fp);

        } elseif ($web_location == 1) {
            //TODO: check if the file is an image
            $contents = preg_split('/\n/',$contents);
            while (list($key,$value) = each($contents)) {
                $data = preg_split('/\;/',$value);
                if (count($data) == 0 || empty($data[0])
                                      || preg_match('/^#/',$data[0])
                                      || preg_match('/^[[:space:]]*$/',$data[0]))
                    continue;
                if (empty($data[1])) $data[1] = '';
                $photos[] = array ("name" => dirname($src)."/".trim($data[0]),
                                   "src" => dirname($src)."/".trim($data[0]),
                                   "desc" => trim($data[1]),
                                   "name_tile" => dirname($src)."/".trim($data[0]));
            }
        }
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

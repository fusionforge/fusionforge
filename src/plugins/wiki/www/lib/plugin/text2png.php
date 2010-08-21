<?php // -*-php-*-
// rcs_id('$Id: text2png.php 7639 2010-08-11 12:15:16Z vargenau $');
/*
 * Copyright 1999,2000,2001,2002,2007 $ThePhpWikiProgrammingTeam
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
 * File loading and saving diagnostic messages, to see whether an
 * image was saved to or loaded from the cache and what the path is.
 *
 * Convert text into a png image using GD without using [WikiPluginCached|Help:WikiPlugin].
 * The images are stored in a private <PHPWIKI_DIR>/images/<LANG> subdirectory instead,
 * which are not timestamp checked at all. Delete the .png file(s) if you change anything.
 *
 * This is a really simple and stupid plugin, which needs some work.
 * No size and color options, no change check.
 *
 * We'd need a ButtonCreator for the MacOSX theme buttons also.
 * Via svg => png, or is gd2 good enough?
 *
 * PHP must be compiled with support for the GD library version 1.6 or
 * later to create PNG image files:
 *
 * ./configure --with-gd
 *
 * See <http://www.php.net/manual/pl/ref.image.php> for more info.
 */
if (!defined('text2png_debug'))
    define('text2png_debug', DEBUG & _DEBUG_VERBOSE);


class WikiPlugin_text2png
extends WikiPlugin
{
    function getName () {
        return "text2png";
    }

    function getDescription() {
        return _("Convert text into a png image using GD.");
    }

    function getDefaultArguments() {
        global $LANG;
        // TODO: add fixed size and center.
        return array('text'    => "text2png testtext",
                     'lang'    => $LANG,
                     '_force'      => 0,
                     'fontsize'    => 18, // with GD1 it's the pixelsize, with GD2 the pointsize
                     'with_shadow' => 1,
                     'fontcolor'   => '#000000',
                     'shadowcolor' => '#AFAFAF',
                     'backcolor'   => '#ffffff');
        }

    function run($dbi, $argstr, &$request, $basepage) {
        if (ImageTypes() & IMG_PNG) {
            // we have gd & png so go ahead.
            $args = $this->getArgs($argstr, $request);
            return $this->text2png($args);
        } else {
            // we don't have png and/or gd.
            $error_html = _("Sorry, this version of PHP cannot create PNG image files.");
            $link = "http://www.php.net/manual/pl/ref.image.php";
            $error_html .= sprintf(_("See %s"), $link) .".";
            trigger_error($error_html, E_USER_NOTICE);
            return;
        }
    }

   /**
    * Parse hexcolor into ordinal rgb array.
    * '#000'    => array(0,0,0)
    * '#000000' => array(0,0,0)
    */
    function hexcolor($h, $default=false) {
        if ($h[0] != '#') return $default;
        $rgb = substr($h,1);
        if (strlen($rgb) == 3)
            return array(hexdec($rgb{0}),hexdec($rgb{1}),hexdec($rgb{2}));
        elseif (strlen($rgb) == 6)
            return array(hexdec(substr($rgb,0,2)),hexdec(substr($rgb,2,2)),hexdec(substr($rgb,4,2)));
        return $default;
    }

    function text2png($args) {
        extract ($args);
        /**
         * Basic image creation and caching
         *
         * You MUST delete the image cache yourself in /images if you
         * change the drawing routines!
         */

        $filename = urlencode($text) . ".png"; // protect by urlencode!!!

        /**
         * FIXME: need something more elegant, and a way to gettext a
         *        different language depending on any individual
         *        user's locale preferences.
         */

        if ($l == "C") {
            $l = "en"; //english=C
        } else {
            $l = urlencode ($l); // who on earth forgot his?
        }
        $basedir = "text2png-image";
        $filepath = getUploadFilePath() . "$basedir/$l";
        if ($_force or !file_exists($filepath.$filename)) {
            if (!file_exists($filepath)) {
                $oldumask = umask(0);
                // permissions affected by user the www server is running as
                mkdir(getUploadFilePath() . $basedir, 0777);
                mkdir($filepath, 0777);
                umask($oldumask);
            }
            $filepath .= "/";
            /**
             * prepare a new image
             *
             * FIXME: needs a dynamic image size depending on text
             *        width and height
             */

            // got this logic from GraphViz
            if (defined('TTFONT'))
                $ttfont = TTFONT;
            elseif (PHP_OS == "Darwin") // Mac OS X
                $ttfont   = "/System/Library/Frameworks/JavaVM.framework/Versions/1.3.1/Home/lib/fonts/LucidaSansRegular.ttf";
            elseif (isWindows()) {
                $ttfont = $_ENV['windir'].'\Fonts\Arial.ttf';
            } else {
                $ttfont = 'luximr'; // This is the only what sourceforge offered.
                //$ttfont = 'Helvetica';
            }

            /* http://download.php.net/manual/en/function.imagettftext.php
             * array imagettftext (int im, int size, int angle, int x, int y,
             *                      int col, string fontfile, string text)
             */

            // get ready to draw
            $s = ImageTTFBBox($fontsize, 0, $ttfont, $text);
            $im = @ImageCreate(abs($s[4])+20, abs($s[7])+10);
            if (empty($im)) {
                $error_html = _("PHP was unable to create a new GD image stream. Read 'lib/plugin/text2png.php' for details.");
                // FIXME: Error manager does not transform URLs passed
                //        through it.
                $link = "http://www.php.net/manual/en/function.imagecreate.php";
                $error_html .= sprintf(_("See %s"), $link) .".";
                trigger_error( $error_html, E_USER_NOTICE );
                return;
            }
            $rgb = $this->hexcolor($backcolor, array(255,255,255));
            $bg_color = ImageColorAllocate($im, $rgb[0], $rgb[1], $rgb[2]);
            if ($with_shadow) {
                $rgb = $this->hexcolor($shadowcolor, array(175,175,175));
                $text_color = ImageColorAllocate($im, $rgb[0], $rgb[1], $rgb[2]);
                // shadow is 1 pixel down and 2 pixels right
                ImageTTFText($im, $fontsize, 0, 12, abs($s[7])+6, $text_color, $ttfont, $text);
            }
            // draw text
            $rgb = $this->hexcolor($fontcolor, array(0,0,0));
            $text_color = ImageColorAllocate($im, $rgb[0], $rgb[1], $rgb[2]);
            ImageTTFText($im, $fontsize, 0, 10, abs($s[7])+5, $text_color, $ttfont, $text);

            /**
             * An alternate text drawing method in case ImageTTFText
             * doesn't work.
             **/
            //ImageString($im, 2, 10, 40, $text, $text_color);

            // To dump directly to browser:
            //header("Content-type: image/png");
            //ImagePng($im);

            // to save to file:
            $success = ImagePng($im, $filepath . $filename);

        } else {
            $filepath .= "/";
            $success = 2;
        }

        // create an <img src= tag to show the image!
        $html = HTML();
        if ($success > 0) {
            if (defined('text2png_debug')) {
                switch($success) {
                case 1:
                    trigger_error(sprintf(_("Image saved to cache file: %s"),
                                          $filepath . $filename),
                                  E_USER_NOTICE);
                case 2:
                    trigger_error(sprintf(_("Image loaded from cache file: %s"),
                                          $filepath . $filename),
                                  E_USER_NOTICE);
                }
            }
            $url =  getUploadDataPath()."$basedir/".urlencode($l)."/".urlencode($filename);
            $html->pushContent(HTML::img(array('src' => $url,
                                               'alt' => $text,
                                               'title' => '"'.$text.'"'. _(" produced by "). $this->getName())));
        } else {
            trigger_error(sprintf(_("couldn't open file '%s' for writing"),
                                  $filepath . $filename), E_USER_NOTICE);
        }
        return $html;
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

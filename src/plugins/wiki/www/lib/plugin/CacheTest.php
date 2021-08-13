<?php
/**
 * Copyright © 2002 Johannes Große
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

// +---------------------------------------------------------------------+
// | simple test of the WikiPluginCached class which provides a          |
// | text to image conversion.                                           |
// | This is a usage example of WikiPluginCached.                        |
// +---------------------------------------------------------------------+

/*------------------------------------------------------------------------
 |
 | You may call this plugin as follows:
 |
 |        <<CacheTest text="What a wonderful test!" >>
 |
 *----------------------------------------------------------------------*/

require_once 'lib/WikiPluginCached.php';

class WikiPlugin_CacheTest
    extends WikiPluginCached
{
    /* --------- overwrite abstract methods ---------------- */

    function getPluginType()
    {
        return PLUGIN_CACHED_IMG_ONDEMAND;
    }

    function getDescription()
    {
        return _('This is a simple example using WikiPluginCached.');
    }

    function getDefaultArguments()
    {
        return array('text' => $this->getDescription(),
            'font' => '3',
            'type' => 'png');
    }

    // should return image handle
    //
    // if an error occurs you MUST call
    // $this->complain('aboutwhichidocomplain') you may produce an
    // image handle to an error image if you do not,
    // WikiPluginImageCache will do so.

    protected function getImage($dbi, $argarray, $request)
    {
        extract($argarray);
        return $this->produceGraphics($text, $font);

        // This should also work
        // return $this->lazy_produceGraphics($text,$font);
    } // getImage

    protected function getMap($dbi, $argarray, $request)
    {
        trigger_error('pure virtual', E_USER_ERROR);
    }

    protected function getHtml($dbi, $argarray, $request, $basepage)
    {
        trigger_error('pure virtual', E_USER_ERROR);
    }

    function getImageType($dbi, $argarray, $request)
    {
        extract($argarray);
        if (in_array($type, array('png', 'gif', 'jpg'))) {
            return $type;
        }
        return 'png';
    }

    function getAlt($dbi, $argarray, $request)
    {
        // ALT-text for <img> tag
        extract($argarray);
        return $text;
    }

    function getExpire($dbi, $argarray, $request)
    {
        return '+600'; // 600 seconds life time
    }

    /* -------------------- extremely simple converter -------------------- */

    function produceGraphics($text, $font)
    {
        // The idea (and some code) is stolen from the text2png plugin
        // but I did not want to use TTF. Imagestring is quite ugly
        // and quite compatible. It's only a usage example.

        if ($font < 1 || $font > 5) {
            $text = "Fontnr. (font=\"$font\") should be in range 1-5";
            $this->complain($text);
            $font = 3;
        }

        $ok = ($im = @imagecreate(400, 40));
        $bg_color = imagecolorallocate($im, 240, 240, 240);
        $text_color1 = imagecolorallocate($im, 120, 120, 120);
        $text_color2 = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 149, 49, $bg_color);
        imagestring($im, $font, 11, 12, $text, $text_color1);
        imagestring($im, $font, 10, 10, $text, $text_color2);

        if (!$ok) {
            // simple error handling by WikiPluginImageCache
            $this->complain("Could not create image");
            return false;
        }

        // image creation takes really _much_ time :-)
        // so caching is very useful!
        sleep(4);

        return $im;
    } // produce_Graphics

    /* -------------------------------------------------------------------- */

    // we could have used the simple built-in text2img function
    // instead of writing our own:

    function lazy_produceGraphics($text, $font)
    {
        if ($font < 1 || $font > 5) {
            $text = "Fontnr. (font=\"$font\") should be in range 1-5";
            $this->complain($text);
            $font = 3;

        }

        return $this->text2img($text, $font, array(0, 0, 0),
            array(255, 255, 255));
    } // lazy_produceGraphics

}

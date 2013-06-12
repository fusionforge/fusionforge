<?php

/*
 * Copyright (C) 2002 Johannes Große
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

// +---------------------------------------------------------------------+
// | CacheTest.php                                                       |
// +---------------------------------------------------------------------+
// | simple test of the WikiPluginCached class which provides a          |
// | text to image conversion.                                           |
// | This is a usage example of WikiPluginCached.                        |
// |                                                                     |
// | You may copy this code freely under the conditions of the GPL       |
// +---------------------------------------------------------------------+

/*------------------------------------------------------------------------
 | CacheTest
 *------------------------------------------------------------------------
 |
 | You may call this plugin as follows:
 |
 |        <<CacheTest text="What a wonderful test!" >>
 |

/*-----------------------------------------------------------------------
 |
 |  Source
 |
 *----------------------------------------------------------------------*/

/*-----------------------------------------------------------------------
 | WikiPlugin_CacheTest
 *----------------------------------------------------------------------*/

require_once 'lib/WikiPluginCached.php';

class WikiPlugin_CacheTest
    extends WikiPluginCached
{
    /* --------- overwrite virtual or abstract methods ---------------- */

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

    function getImage($dbi, $argarray, $request)
    {
        extract($argarray);
        return $this->produceGraphics($text, $font);

        // This should also work
        // return $this->lazy_produceGraphics($text,$font);
    } // getImage

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
        // but I did not want to use TTF. ImageString is quite ugly
        // and quite compatible. It's only a usage example.

        if ($font < 1 || $font > 5) {
            $text = "Fontnr. (font=\"$font\") should be in range 1-5";
            $this->complain($text);
            $font = 3;
        }

        $ok = ($im = @ImageCreate(400, 40));
        $bg_color = ImageColorAllocate($im, 240, 240, 240);
        $text_color1 = ImageColorAllocate($im, 120, 120, 120);
        $text_color2 = ImageColorAllocate($im, 0, 0, 0);

        ImageFilledRectangle($im, 0, 0, 149, 49, $bg_color);
        ImageString($im, $font, 11, 12, $text, $text_color1);
        ImageString($im, $font, 10, 10, $text, $text_color2);

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

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:

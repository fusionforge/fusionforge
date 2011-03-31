<?php // -*-php-*-
// $Id: FacebookLike.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2010 Reini Urban
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
 Optional opengraph page meta data to be added to head.tmpl:
  og:title - The title of your page; if not specified, the title element will be used.
  og:site_name - The name of your web site, e.g., "CNN" or "IMDb".
  og:image - The URL of the best picture for this page. The image must be at least
             50px by 50px and have a maximum aspect ratio of 3:1.
*/

class WikiPlugin_FacebookLike
extends WikiPlugin
{
    function getDescription() {
        return _("Display a Facebook Like button. See http://developers.facebook.com/docs/reference/plugins/like");
    }

    function getDefaultArguments() {
        return array('width'       => 450,
                     'height'      => 35,
                     //'title'       => '',    // override $TITLE (i.e. pagename)
                     'colorscheme' => 'light', // or "dark"
                     'show_faces'  => "false",
                     'layout'      => "standard", // or "button_count"
                     'action'      => "like",   // or "recommend"
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        
        //$iframe = "<iframe src=\"http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fexample.com%2Fpage%2Fto%2Flike&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:35px;\" allowTransparency=\"true\"></iframe>";
        $urlargs = array(
                         "layout"     => $layout,
                         "show_faces" => $show_faces,
                         "width"      => $width,
                         "action"     => "like", // or "recommend"
                         "colorscheme"=> $colorscheme,
                         "height"     => $height
                         );
        $pagename = $request->getArg('pagename');
        $url = "http://www.facebook.com/plugins/like.php?"
             . "href=" . urlencode(WikiUrl($pagename,$urlargs,true));
        $url = str_replace("%3D","=",$url);
        $params = array("src"               => $url,
                        "scrolling"         => 'no',
                        "frameborder"       => '0',
                        "style"             => "border:none; overflow:hidden; "
                                             . "width:$width"."px; height:$height"."px;",
                        "allowtransparency" => "true");
        return HTML::iframe($params);
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

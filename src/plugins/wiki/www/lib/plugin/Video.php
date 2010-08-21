<?php // -*-php-*-
// rcs_id('$Id: Video.php 7639 2010-08-11 12:15:16Z vargenau $');
/*
 * Copyright 2009 Roger Guignard and Marc-Etienne Vargenau, Alcatel-Lucent
 * Copyright 2009 Reini Urban
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The VideoPlugin ("Contribution") has not been tested and/or
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

class WikiPlugin_Video
extends WikiPlugin
{
    function getName() {
        return _("Video");
    }

    function getDescription() {
        return _("Display video in Flash");
    }

    function getDefaultArguments() {
        return array('width'    => 460,
                     'height'   => 320,
                     'url'      => '',
                     'file'     => '',
                     'autoplay' => 'false',
                     'image'    => ''
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {

        global $WikiTheme;
        $args = $this->getArgs($argstr, $request);
        extract($args);

        if (! $url && ! $file) {
            return $this->error(_("Both 'url' or 'file' parameters missing."));
        } elseif ($url && $file) {
            return $this->error(_("Choose only one of 'url' or 'file' parameters."));
        } elseif ($file) {
            $url = getUploadDataPath() . $file;
        }

        // TODO: Check HTML5 browser capabilities
        if (string_ends_with($url, ".ogg")) {
            return HTML::video(array('autoplay' => 'true', 'controls' => 'true', 'src' => $url),
                               _("Your browser does not understand the HTML 5 video tag."));
        }
        if (!$image) $image = $url;
        if ($autoplay != 'true' and $autoplay != 'false')
            return $this->error(fmt("Invalid argument %s", "autoplay"));
        if (!is_numeric($width))
            return $this->error(fmt("Invalid argument %s", "width"));
        if (!is_numeric($height))
            return $this->error(fmt("Invalid argument %s", "height"));
        if (preg_match("/'/", $url))
            return $this->error(fmt("Invalid argument %s", "url"));

        $params = array("data" => SERVER_URL . $WikiTheme->_findData('flowplayer-3.1.4.swf'),
                        "type"              => "application/x-shockwave-flash",
                        "width"             => $width,
                        "height"            => $height,
                        "allowfullscreen"   => "true",
                        "allowscriptaccess" => "false",
                        "flashvars"=>
                          "config={'clip':{'url':'" . $url . "','autoPlay':" . $autoplay . "}}'");
        return ImgObject(HTML::img(array('src' => $image)), $params);
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

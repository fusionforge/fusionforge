<?php // -*-php-*-
// $Id: Transclude.php 7805 2011-01-04 17:50:32Z vargenau $
/**
 * Copyright 1999,2000,2001,2002,2006 $ThePhpWikiProgrammingTeam
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
 * Transclude:  Include an external web page within the body of a wiki page.
 *
 * Usage:
 *  <<Transclude
 *           src=http://www.internet-technology.de/fourwins_de.htm
 *  >>
 *
 * @author Geoffrey T. Dairiki
 *
 * @see http://www.cs.tut.fi/~jkorpela/html/iframe.html
 *
 * KNOWN ISSUES
 *  Will only work if the browser supports <iframe>s (which is a recent,
 *  but standard tag)
 *
 *  The auto-vertical resize javascript code only works if the transcluded
 *  page comes from the PhpWiki server.  Otherwise (due to "tainting"
 *  security checks in JavaScript) I can't figure out how to deduce the
 *  height of the transcluded page via JavaScript... :-/
 *
 *  Sometimes the auto-vertical resize code doesn't seem to make the iframe
 *  quite big enough --- the scroll bars remain.  Not sure why.
 */
class WikiPlugin_Transclude
extends WikiPlugin
{
    function getName() {
        return _("Transclude");
    }

    function getDescription() {
      return _("Include an external web page within the body of a wiki page.");
    }

    function getDefaultArguments() {
        return array( 'src'     => false, // the src url to include
                      'title'   =>  _("Transcluded page"), // title of the iframe
                      'height'  => 450, // height of the iframe
                      'quiet'   => false // if set, iframe appears as normal content
                    );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;

        $args = ($this->getArgs($argstr, $request));
        extract($args);

        if (!$src) {
            return $this->error(fmt("%s parameter missing", "'src'"));
        }
        // Expand possible interwiki link for src
        if (strstr($src,':')
            and (!strstr($src,'://'))
            and ($intermap = getInterwikiMap())
            and preg_match("/^" . $intermap->getRegexp() . ":/", $src))
        {
            $link = $intermap->link($src);
            $src = $link->getAttr('href');
        }

        // FIXME: Better recursion detection.
        // FIXME: Currently this doesnt work at all.
        if ($src == $request->getURLtoSelf() ) {
            return $this->error(fmt("Recursive inclusion of url %s", $src));
        }
        if (! IsSafeURL($src)) {
            return $this->error(_("Bad url in src: remove all of <, >, \""));
        }

        $params = array('title' => $title,
                        'src' => $src,
                        'width' => "100%",
                        'height' => $height,
                        'marginwidth' => 0,
                        'marginheight' => 0,
                        'class' => 'transclude',
                        "onload" => "adjust_iframe_height(this);");

        $noframe_msg[] = fmt("See: %s", HTML::a(array('href' => $src), $src));

        $noframe_msg = HTML::div(array('class' => 'transclusion'),
                                 HTML::p(array(), $noframe_msg));

        $iframe = HTML::iframe($params, $noframe_msg);

        /* This doesn't work very well...  maybe because CSS screws up NS4 anyway...
        $iframe = new HtmlElement('ilayer', array('src' => $src), $iframe);
        */

        if ($quiet) {
            return HTML($this->_js(), $iframe);
        } else {
            return HTML(HTML::p(array('class' => 'transclusion-title'),
                                fmt("Transcluded from %s", LinkURL($src))),
                        $this->_js(), $iframe);
        }
    }

    /**
     * Produce our javascript.
     *
     * This is used to resize the iframe to fit the content.
     * Currently it only works if the transcluded document comes
     * from the same server as the wiki server.
     *
     * @access private
     */
    function _js() {
        static $seen = false;

        if ($seen)
            return '';
        $seen = true;

        return JavaScript('
          function adjust_iframe_height(frame) {
            var content = frame.contentDocument;
            try {
                frame.height = content.height + 2 * frame.marginHeight;
            }
            catch (e) {
              // Cannot get content.height unless transcluded doc
              // is from the same server...
              return;
            }
          }

          window.addEventListener("resize", function() {
            f = this.document.body.getElementsByTagName("iframe");
            for (var i = 0; i < f.length; i++)
              adjust_iframe_height(f[i]);
          }, false);
          ');
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

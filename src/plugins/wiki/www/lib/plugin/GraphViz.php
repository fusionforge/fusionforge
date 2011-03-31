<?php // -*-php-*-
// $Id: GraphViz.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * The GraphViz plugin passes all its arguments to the grapviz dot
 * binary and displays the result as cached image (PNG,GIF,SVG) or imagemap.
 *
 * @Author: Reini Urban
 *
 * Note:
 * - We support only images supported by GD so far (PNG most likely).
 *   EPS, PS, SWF, SVG or SVGZ and imagemaps need to be tested.
 *
 * Usage:
<<GraphViz [options...]
   multiline dot script ...
>>

 * See also: VisualWiki, which depends on GraphViz and WikiPluginCached.
 *
 * TODO:
 * - neato binary ?
 * - expand embedded <!plugin-list pagelist !> within the digraph script.
 */

if (PHP_OS == "Darwin") { // Mac OS X
    if (!defined("GRAPHVIZ_EXE"))
        define('GRAPHVIZ_EXE', '/sw/bin/dot'); // graphviz via Fink
    // Name of the Truetypefont - at least LucidaSansRegular.ttf is always present on OS X
    if (!defined('VISUALWIKIFONT'))
        define('VISUALWIKIFONT', 'LucidaSansRegular');
    // The default font paths do not find your fonts, set the path here:
    $fontpath = "/System/Library/Frameworks/JavaVM.framework/Versions/1.3.1/Home/lib/fonts/";
    //$fontpath = "/usr/X11R6/lib/X11/fonts/TTF/";
}
elseif (isWindows()) {
    if (!defined("GRAPHVIZ_EXE"))
        define('GRAPHVIZ_EXE','dot.exe');
    if (!defined('VISUALWIKIFONT'))
        define('VISUALWIKIFONT', 'Arial');
} elseif ($_SERVER["SERVER_NAME"] == 'phpwiki.sourceforge.net') { // sf.net hack
    if (!defined("GRAPHVIZ_EXE"))
        define('GRAPHVIZ_EXE','/home/groups/p/ph/phpwiki/bin/dot');
    if (!defined('VISUALWIKIFONT'))
        define('VISUALWIKIFONT', 'luximr');
} else { // other os
    if (!defined("GRAPHVIZ_EXE"))
        define('GRAPHVIZ_EXE','/usr/local/bin/dot');
    // Name of the Truetypefont - Helvetica is probably easier to read
    if (!defined('VISUALWIKIFONT'))
        define('VISUALWIKIFONT', 'Helvetica');
    //define('VISUALWIKIFONT', 'Times');
    //define('VISUALWIKIFONT', 'Arial');
    // The default font paths do not find your fonts, set the path here:
    //$fontpath = "/usr/X11R6/lib/X11/fonts/TTF/";
    //$fontpath = "/usr/share/fonts/default/TrueType/";
}

require_once "lib/WikiPluginCached.php";

class WikiPlugin_GraphViz
extends WikiPluginCached
{

    function _mapTypes() {
            return array("imap", "cmapx", "ismap", "cmap");
    }

    /**
     * Sets plugin type to MAP
     * or HTML if the imagetype is not supported by GD (EPS, SVG, SVGZ) (not yet)
     * or IMG_INLINE if device = png, gif or jpeg
     */
    function getPluginType() {
        $type = $this->decideImgType($this->_args['imgtype']);
        if ($type == $this->_args['imgtype'])
            return PLUGIN_CACHED_IMG_INLINE;
        $device = strtolower($this->_args['imgtype']);
            if (in_array($device, $this->_mapTypes()))
                return PLUGIN_CACHED_MAP;
            if (in_array($device, array('svg','swf','svgz','eps','ps'))) {
            switch ($this->_args['imgtype']) {
                    case 'svg':
                    case 'svgz':
                   return PLUGIN_CACHED_STATIC | PLUGIN_CACHED_SVG_PNG;
                    case 'swf':
                   return PLUGIN_CACHED_STATIC | PLUGIN_CACHED_SWF;
                default:
                   return PLUGIN_CACHED_STATIC | PLUGIN_CACHED_HTML;
            }
        }
            else
            return PLUGIN_CACHED_IMG_INLINE; // normal cached libgd image handles
    }
    function getName () {
        return _("GraphViz");
    }
    function getDescription () {
        return _("GraphViz image or imagemap creation of directed graphs");
    }
    function managesValidators() {
        return true;
    }
    function getDefaultArguments() {
        return array(
                     'imgtype' => 'png', // png,gif,svgz,svg,...
                     'alt'     => false,
                     'pages'   => false,  // <!plugin-list !> support
                     'exclude' => false,
                     'help'    => false,
                     'debug'    => false,
                     );
    }
    function handle_plugin_args_cruft(&$argstr, &$args) {
        $this->source = $argstr;
    }
    /**
     * Sets the expire time to one day (so the image producing
     * functions are called seldomly) or to about two minutes
     * if a help screen is created.
     */
    function getExpire($dbi, $argarray, $request) {
        if (!empty($argarray['help']))
            return '+120'; // 2 minutes
        return sprintf('+%d', 3*86000); // approx 3 days
    }

    /**
     * Sets the imagetype according to user wishes and
     * relies on WikiPluginCached to catch illegal image
     * formats.
     * @return string 'png', 'jpeg', 'gif'
     */
    function getImageType($dbi, $argarray, $request) {
        return $argarray['imgtype'];
    }

    /**
     * This gives an alternative text description of
     * the image.
     */
    function getAlt($dbi, $argstr, $request) {
        return (!empty($this->_args['alt'])) ? $this->_args['alt']
                                             : $this->getDescription();
    }

    /**
     * Returns an image containing a usage description of the plugin.
     *
     * TODO: *map features.
     * @return string image handle
     */
    function helpImage() {
        $def = $this->defaultArguments();
        //$other_imgtypes = $GLOBALS['PLUGIN_CACHED_IMGTYPES'];
        //unset ($other_imgtypes[$def['imgtype']]);
        $imgtypes = $GLOBALS['PLUGIN_CACHED_IMGTYPES'];
        $imgtypes = array_merge($imgtypes, array("svg", "svgz", "ps"), $this->_mapTypes());
        $helparr = array(
            '<<GraphViz ' .
            'imgtype'          => ' = "' . $def['imgtype'] . "(default)|" . join('|',$imgtypes).'"',
            'alt'              => ' = "alternate image text"',
            'pages'            => ' = "pagenames,*" or <!plugin-list !> pagelist as input',
            'exclude'          => ' = "pagenames,*" or <!plugin-list !> pagelist as input',
            'help'             => ' bool: displays this screen',
            '...'              => ' all further lines below the first plugin line ',
            ''                 => ' and inside the tags are the dot script.',
            "\n  ?>"
            );
        $length = 0;
        foreach($helparr as $alignright => $alignleft) {
            $length = max($length, strlen($alignright));
        }
        $helptext ='';
        foreach($helparr as $alignright => $alignleft) {
            $helptext .= substr('                                                        '
                                . $alignright, -$length).$alignleft."\n";
        }
        return $this->text2img($helptext, 4, array(1, 0, 0),
                               array(255, 255, 255));
    }

    function processSource($argarray=false) {
        if (empty($this->source)) {
            // create digraph from pages
            if (empty($argarray['pages'])) {
                trigger_error(sprintf(_("%s is empty"), 'GraphViz argument source'), E_USER_WARNING);
                return '';
            }
            $source = "digraph GraphViz {\n";  // }
            foreach ($argarray['pages'] as $name) { // support <!plugin-list !> pagelists
                // allow Page/SubPage
                $url = str_replace(urlencode(SUBPAGE_SEPARATOR), SUBPAGE_SEPARATOR,
                                   rawurlencode($name));
                $source .= "  \"$name\" [URL=\"$url\"];\n";
            }
            // {
            $source .= "\n  }";
        } else {
            $source = $this->source;
        }
        /* //TODO: expand inlined plugin-list arg
         $i = 0;
         foreach ($source as $data) {
             // hash or array?
             if (is_array($data))
                 $src .= ("\t" . join(" ", $data) . "\n");
             else
                 $src .= ("\t" . '"' . $data . '" ' . $i++ . "\n");
             $src .= $source;
             $source = $src;
        }
        */
        return $source;
    }

    function createDotFile($tempfile='', $argarray=false) {
        $this->source = $this->processSource($argarray);
        if (!$this->source)
            return false;
        if (!$tempfile) {
            $tempfile = $this->tempnam($this->getName().".dot");
            @unlink($tempfile);
        }
        if (!$fp = fopen($tempfile, 'w'))
            return false;
        $ok = fwrite($fp, $this->source);
        $ok = fclose($fp) && $ok;  // close anyway
        return $ok ? $tempfile : false;
    }

    function getImage($dbi, $argarray, $request) {
        $dotbin = GRAPHVIZ_EXE;
        $tempfiles = $this->tempnam($this->getName());
        $gif = $argarray['imgtype'];
        if (in_array($gif, array("imap", "cmapx", "ismap", "cmap"))) {
            $this->_mapfile = "$tempfiles.map";
            $gif = $this->decideImgType($argarray['imgtype']);
            if ($gif == $argarray['imgtype']) $gif = 'png';
        }

        $ImageCreateFromFunc = "ImageCreateFrom$gif";
        $outfile = $tempfiles.".".$gif;
        $debug = $request->getArg('debug');
        if ($debug) {
            $tempdir = dirname($tempfiles);
            $tempout = $tempdir . "/.debug";
        }
        $source = $this->processSource($argarray);
        if (empty($source))
            return $this->error(fmt("No dot graph given"));
        if (isWindows()) {
          $ok = $tempfiles;
          $dotfile = $this->createDotFile($tempfiles.'.dot', $argarray);
          $args = "-T$gif $dotfile -o $outfile";
          $cmdline = "$dotbin $args";
          $code = $this->execute($cmdline, $outfile);
          if (!$code)
            $this->complain(sprintf(_("Couldn't start commandline '%s'"), $cmdline));
        } else {
          $args = "-T$gif -o $outfile";
          $cmdline = "$dotbin $args";
          if ($debug) $cmdline .= " > $tempout";
          //if (!isWindows()) $cmdline .= " 2>&1";
          $code = $this->filterThroughCmd($source, $cmdline);
          if ($code)
            $this->complain(sprintf(_("Couldn't start commandline '%s'"), $cmdline));
          sleep(0.1);
        }
        if (! file_exists($outfile) ) {
            $this->complain(sprintf(_("%s error: outputfile '%s' not created"),
                                    "GraphViz", $outfile));
            $this->complain("\ncmd-line: $cmdline");
            return false;
        }
        if (function_exists($ImageCreateFromFunc)) {
            $img = $ImageCreateFromFunc( $outfile );
            // clean up tempfiles
            @unlink($tempfiles);
            if (empty($argarray['debug']))
                foreach (array(".$gif",'.dot') as $ext) {
                    //if (file_exists($tempfiles.$ext))
                    @unlink($tempfiles.$ext);
                }
            return $img;
        }
        return $outfile;
    }

    // which argument must be set to 'png', for the fallback image when svg will fail on the client.
    // type: SVG_PNG
    function pngArg() {
            return 'imgtype';
    }

    function getMap($dbi, $argarray, $request) {
            $result = $this->invokeDot($argarray);
        if (isa($result, 'HtmlElement'))
            return array(false, $result);
        else
            return $result;
        // $img = $this->getImage($dbi, $argarray, $request);
            //return array($this->_mapfile, $img);
    }

    /**
     * Produces a dot file, calls dot twice to obtain an image and a
     * text description of active areas for hyperlinking and returns
     * an image and an html map.
     *
     * @param width     float   width of the output graph in inches
     * @param height    float   height of the graph in inches
     * @param colorby   string  color sceme beeing used ('age', 'revtime',
     *                                                   'none')
     * @param shape     string  node shape; 'ellipse', 'box', 'circle', 'point'
     * @param label     string  not used anymore
     */
    function invokeDot($argarray) {
        $dotbin = GRAPHVIZ_EXE;
        $tempfiles = $this->tempnam($this->getName());
        $gif = $argarray['imgtype'];
        $ImageCreateFromFunc = "ImageCreateFrom$gif";
        $outfile = $tempfiles.".".$gif;
        $debug = $GLOBALS['request']->getArg('debug');
        if ($debug) {
            $tempdir = dirname($tempfiles);
            $tempout = $tempdir . "/.debug";
        }
        $ok = $tempfiles;
        $source = $this->processSource($argarray);
        if (empty($source)) {
            $this->complain("No dot graph given");
            return array(false, $this->GetError());
        }
        //$ok = $ok and $this->createDotFile($tempfiles.'.dot', $argarray);

        $args = "-T$gif $tempfiles.dot -o $outfile";
        $cmdline1 = "$dotbin $args";
        if ($debug) $cmdline1 .= " > $tempout";
        $ok = $ok and $this->filterThroughCmd($source, $cmdline1);
        //$ok = $this->execute("$dotbin -T$gif $tempfiles.dot -o $outfile" .
        //                     ($debug ? " > $tempout 2>&1" : " 2>&1"), $outfile)

        $args = "-Timap $tempfiles.dot -o $tempfiles.map";
        $cmdline2 = "$dotbin $args";
        if ($debug) $cmdline2 .= " > $tempout";
        $ok = $ok and $this->filterThroughCmd($source, $cmdline2);
        // $this->execute("$dotbin -Timap $tempfiles.dot -o ".$tempfiles.".map" .
        //                    ($debug ? " > $tempout 2>&1" : " 2>&1"), $tempfiles.".map")
        $ok = $ok and file_exists( $outfile );
        $ok = $ok and file_exists( $tempfiles.'.map' );
        $ok = $ok and ($img = $ImageCreateFromFunc($outfile));
        $ok = $ok and ($fp = fopen($tempfiles.'.map', 'r'));

        $map = HTML();
        if ($debug == 'static') {
            // workaround for misconfigured WikiPluginCached (sf.net) or dot.
            // present a static png and map file.
            if (file_exists($outfile) and filesize($outfile) > 900)
                $img = $outfile;
            else
                $img = $tempdir . "/".$this->getName().".".$gif;
            if (file_exists( $tempfiles.".map") and filesize($tempfiles.".map") > 20)
                $map = $tempfiles.".map";
            else
                $map = $tempdir . "/".$this->getName().".map";
            $img = $ImageCreateFromFunc($img);
            $fp = fopen($map, 'r');
            $map = HTML();
            $ok = true;
        }
        if ($ok and $fp) {
            while (!feof($fp)) {
                $line = fgets($fp, 1000);
                if (substr($line, 0, 1) == '#')
                    continue;
                list($shape, $url, $e1, $e2, $e3, $e4) = sscanf($line,
                                                                "%s %s %d,%d %d,%d");
                if ($shape != 'rect')
                    continue;

                // dot sometimes gives not always the right order so
                // so we have to sort a bit
                $x1 = min($e1, $e3);
                $x2 = max($e1, $e3);
                $y1 = min($e2, $e4);
                $y2 = max($e2, $e4);
                $map->pushContent(HTML::area(array(
                            'shape'  => 'rect',
                            'coords' => "$x1,$y1,$x2,$y2",
                            'href'   => $url,
                            'title'  => rawurldecode($url),
                            'alt' => $url)));
            }
            fclose($fp);
            //trigger_error("url=".$url);
        } else {
            $this->complain("$outfile: "
                            . (file_exists($outfile) ? filesize($outfile):'missing')
                            ."\n"
                            . "$tempfiles.map: "
                            . (file_exists("$tempfiles.map") ? filesize("$tempfiles.map"):'missing'));
            $this->complain("\ncmd-line: $cmdline1");
            $this->complain("\ncmd-line: $cmdline2");
            //trigger_error($this->GetError(), E_USER_WARNING);
            return array(false, $this->GetError());
        }

        // clean up tempfiles
        @unlink($tempfiles);
        if ($ok and !$argarray['debug'])
            foreach (array('',".$gif",'.map','.dot') as $ext) {
                @unlink($tempfiles.$ext);
            }

        if ($ok)
            return array($img, $map);
        else
            return array(false, $this->GetError());
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

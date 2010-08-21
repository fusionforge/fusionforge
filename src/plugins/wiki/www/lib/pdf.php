<?php // -*-php-*-
// rcs_id('$Id: pdf.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright (C) 2003 Olivier PLATHEY
 * Copyright (C) 200? Don Sebà
 * Copyright (C) 2004,2006,2007 Reini Urban
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

//define("USE_EXTERNAL_HTML2PDF", "htmldoc --quiet --format pdf14 --jpeg --webpage --no-toc --no-title %s");
/**
 * handler for format=pdf
 * http://phpwiki.sourceforge.net/phpwiki/PhpWikiToDocBookAndPDF
 * htmldoc or ghostscript + html2ps or docbook (dbdoclet, xsltproc, fop)
 * http://www.easysw.com/htmldoc
*/
function ConvertAndDisplayPdfPageList (&$request, $pagelist, $args = array()) {
    global $WikiTheme;
    if (empty($request->_is_buffering_output))
        $request->buffer_output(false/*'nocompress'*/);
    $pagename = $request->getArg('pagename');
    $dest = $request->getArg('dest');
    $request->setArg('dest',false);
    $request->setArg('format',false);
    include_once("lib/display.php");
    include_once("lib/loadsave.php");

    array_unshift($pagelist->_pages, $request->_dbi->getPage($pagename));
    require_once("lib/WikiPluginCached.php");
    $cache = new WikiPluginCached;
    $cache->newCache();
    $tmpfile = $cache->tempnam();
    $tmpdir = dirname($tmpfile); 
    unlink ($tmpfile);

    $WikiTheme->DUMP_MODE = 'PDFHTML';
    _DumpHtmlToDir($tmpdir, 
    		   new WikiDB_Array_generic_iter($pagelist->_pages),
    		   $request->getArg('exclude'));
    $WikiTheme->DUMP_MODE = false;
    return;
}

/*
 * Main action handler: action=pdf
 * TODO: inline cached content: /getimg.php? => image.png
 * Just use an external exe.
 */
function ConvertAndDisplayPdf (&$request) {
    global $WikiTheme;
    if (empty($request->_is_buffering_output))
        $request->buffer_output(false/*'nocompress'*/);
    $pagename = $request->getArg('pagename');
    $dest = $request->getArg('dest');
    // Disable CACHE

    $WikiTheme->DUMP_MODE = true;
    include_once("lib/display.php");
    // TODO: urldecode pagename to get rid of %20 in filename.pdf
    displayPage($request, new Template('htmldump', $request));
    $html = ob_get_contents();
    $WikiTheme->DUMP_MODE = false;
    
    // check hook for external converters
    if (defined('USE_EXTERNAL_HTML2PDF')
        and USE_EXTERNAL_HTML2PDF)
    {   // See http://phpwiki.sourceforge.net/phpwiki/PhpWikiToDocBookAndPDF
        // htmldoc or ghostscript + html2ps or docbook (dbdoclet, xsltproc, fop)
        Header('Content-Type: application/pdf');
        $request->discardOutput();
        $request->buffer_output(false/*'nocompress'*/);
        require_once("lib/WikiPluginCached.php");
        $cache = new WikiPluginCached;
        $cache->newCache();
        $tmpfile = $cache->tempnam('pdf.html');
        $fp = fopen($tmpfile, "wb");
        fwrite($fp, $html);
        fclose($fp);
        passthru(sprintf(USE_EXTERNAL_HTML2PDF, $tmpfile));
        unlink($tmpfile);
    }
    // clean the hints errors
    global $ErrorManager;
    $ErrorManager->destroyPostponedErrors();
    
    if (!empty($errormsg)) {
        $request->discardOutput();
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

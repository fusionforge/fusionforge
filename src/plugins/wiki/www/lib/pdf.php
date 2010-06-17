<?php // -*-php-*-
rcs_id('$Id: pdf.php 6184 2008-08-22 10:33:41Z vargenau $');
/*
 Copyright (C) 2003 Olivier PLATHEY
 Copyright (C) 200? Don Sebà
 Copyright (C) 2004,2006,2007 Reini Urban

 This file is part of PhpWiki.

 PhpWiki is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 PhpWiki is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with PhpWiki; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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

// $Log: not supported by cvs2svn $
// Revision 1.13  2007/09/15 12:28:46  rurban
// Improve multi-page format handling: abstract _DumpHtmlToDir. get rid of non-external pdf, non-global VALID_LINKS
//
// Revision 1.12  2007/09/12 19:41:38  rurban
// Enable format=pdf for pagelists (not yet finished)
//
// Revision 1.11  2007/02/17 14:14:55  rurban
// fix pagename for lists
//
// Revision 1.10  2007/01/07 18:44:39  rurban
// Add ConvertAndDisplayPdfPageList
//
// Revision 1.9  2006/09/06 06:02:05  rurban
// omit actionbar from pdf
//
// Revision 1.8  2006/08/25 22:09:00  rurban
// print pdf header earlier
//
// Revision 1.7  2004/09/22 13:46:26  rurban
// centralize upload paths.
// major WikiPluginCached feature enhancement:
//   support _STATIC pages in uploads/ instead of dynamic getimg.php? subrequests.
//   mainly for debugging, cache problems and action=pdf
//
// Revision 1.6  2004/09/20 13:40:19  rurban
// define all config.ini settings, only the supported will be taken from -default.
// support USE_EXTERNAL_HTML2PDF renderer (htmldoc tested)
//
// Revision 1.5  2004/09/17 14:19:02  rurban
// default pdf dest: browser
//
// Revision 1.4  2004/06/14 11:31:37  rurban
// renamed global $Theme to $WikiTheme (gforge nameclash)
// inherit PageList default options from PageList
//   default sortby=pagename
// use options in PageList_Selectable (limit, sortby, ...)
// added action revert, with button at action=diff
// added option regex to WikiAdminSearchReplace
//
// Revision 1.3  2004/05/15 19:49:09  rurban
// moved action_pdf to lib/pdf.php
//

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

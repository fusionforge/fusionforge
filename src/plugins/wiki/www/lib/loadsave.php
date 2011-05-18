<?php //-*-php-*-
// $Id: loadsave.php 8071 2011-05-18 14:56:14Z vargenau $

/*
 * Copyright 1999,2000,2001,2002,2004,2005,2006,2007 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2010 Marc-Etienne Vargenau, Alcatel-Lucent
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

require_once("lib/ziplib.php");
require_once("lib/Template.php");

/**
 * ignore fatal errors during dump
 */
function _dump_error_handler($error) {
    if ($error->isFatal()) {
        $error->errno = E_USER_WARNING;
        return true;
    }
    return true;         // Ignore error
    /*
    if (preg_match('/Plugin/', $error->errstr))
        return true;
    */
    // let the message come through: call the remaining handlers:
    // return false;
}

function StartLoadDump(&$request, $title, $html = '')
{
    // MockRequest is from the unit testsuite, a faked request. (may be cmd-line)
    // We are silent on unittests.
    if (isa($request,'MockRequest'))
        return;
    // FIXME: This is a hack. This really is the worst overall hack in phpwiki.
    if ($html)
        $html->pushContent('%BODY%');
    $tmpl = Template('html', array('TITLE' => $title,
                                   'HEADER' => $title,
                                   'CONTENT' => $html ? $html : '%BODY%'));
    echo ereg_replace('%BODY%.*', '', $tmpl->getExpansion($html));
    $request->chunkOutput();

    // set marker for sendPageChangeNotification()
    $request->_deferredPageChangeNotification = array();
}

function EndLoadDump(&$request)
{
    global $WikiTheme;

    if (isa($request,'MockRequest'))
        return;
    $action = $request->getArg('action');
    $label = '';
    switch ($action) {
    case 'zip':        $label = _("ZIP files of database"); break;
    case 'dumpserial': $label = _("Dump to directory"); break;
    case 'upload':     $label = _("Upload File"); break;
    case 'loadfile':   $label = _("Load File"); break;
    case 'upgrade':    $label = _("Upgrade"); break;
    case 'dumphtml':
    case 'ziphtml':    $label = _("Dump pages as XHTML"); break;
    }
    if ($label) $label = str_replace(" ","_",$label);
    if ($action == 'browse') // loading virgin
        $pagelink = WikiLink(HOME_PAGE);
    else
        $pagelink = WikiLink(new WikiPageName(_("PhpWikiAdministration"),false,$label));

    // do deferred sendPageChangeNotification()
    if (!empty($request->_deferredPageChangeNotification)) {
        $pages = $all_emails = $all_users = array();
        foreach ($request->_deferredPageChangeNotification as $p) {
            list($pagename, $emails, $userids) = $p;
            $pages[] = $pagename;
            $all_emails = array_unique(array_merge($all_emails, $emails));
            $all_users = array_unique(array_merge($all_users, $userids));
        }
        $editedby = sprintf(_("Edited by: %s"), $request->_user->getId());
        $content = "Loaded the following pages:\n" . join("\n", $pages);
        if (mail(join(',',$all_emails),"[".WIKI_NAME."] "._("LoadDump"),
                 _("LoadDump")."\n".
                 $editedby."\n\n".
                 $content))
            trigger_error(sprintf(_("PageChange Notification of %s sent to %s"),
                                  join("\n",$pages), join(',',$all_users)), E_USER_NOTICE);
        else
            trigger_error(sprintf(_("PageChange Notification Error: Couldn't send %s to %s"),
                                  join("\n",$pages), join(',',$all_users)), E_USER_WARNING);
        unset($pages);
        unset($all_emails);
        unset($all_users);
    }
    unset($request->_deferredPageChangeNotification);

    PrintXML(HTML::p(HTML::strong(_("Complete."))),
             HTML::p(fmt("Return to %s", $pagelink)));
    // Ugly hack to get valid XHTML code
    if (isa($WikiTheme, 'WikiTheme_fusionforge')) {
        echo "</div>\n";
        echo "</td></tr>\n";
        echo "</table>\n";
        echo "</div>\n";
        echo "</td></tr>\n";
        echo "</table>\n";
    } else if (isa($WikiTheme, 'WikiTheme_Sidebar')
           or isa($WikiTheme, 'WikiTheme_MonoBook')) {
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";
    } else if (isa($WikiTheme, 'WikiTheme_wikilens')) {
        echo "</div>\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";
    } else if (isa($WikiTheme, 'WikiTheme_blog')) {
        echo "</div>\n";
        echo "</div>\n";
    } else if (isa($WikiTheme, 'WikiTheme_Crao')
           or isa($WikiTheme, 'WikiTheme_Hawaiian')
           or isa($WikiTheme, 'WikiTheme_MacOSX')
           or isa($WikiTheme, 'WikiTheme_shamino_com')
           or isa($WikiTheme, 'WikiTheme_smaller')) {
        echo "</div>\n";
    }
    echo "</body></html>\n";
}

////////////////////////////////////////////////////////////////
//
//  Functions for dumping.
//
////////////////////////////////////////////////////////////////

/**
 * For reference see:
 * http://www.nacs.uci.edu/indiv/ehood/MIME/2045/rfc2045.html
 * http://www.faqs.org/rfcs/rfc2045.html
 * (RFC 1521 has been superceeded by RFC 2045 & others).
 *
 * Also see http://www.faqs.org/rfcs/rfc2822.html
 */
function MailifyPage ($page, $nversions = 1)
{
    $current = $page->getCurrentRevision(false);
    $head = '';

    if (STRICT_MAILABLE_PAGEDUMPS) {
        $from = defined('SERVER_ADMIN') ? SERVER_ADMIN : 'foo@bar';
        //This is for unix mailbox format: (not RFC (2)822)
        // $head .= "From $from  " . CTime(time()) . "\r\n";
        $head .= "Subject: " . rawurlencode($page->getName()) . "\r\n";
        $head .= "From: $from (PhpWiki)\r\n";
        // RFC 2822 requires only a Date: and originator (From:)
        // field, however the obsolete standard RFC 822 also
        // requires a destination field.
        $head .= "To: $from (PhpWiki)\r\n";
    }
    $head .= "Date: " . Rfc2822DateTime($current->get('mtime')) . "\r\n";
    $head .= sprintf("Mime-Version: 1.0 (Produced by PhpWiki %s)\r\n",
                     PHPWIKI_VERSION);

    // This should just be entered by hand (or by script?)
    // in the actual pgsrc files, since only they should have
    // RCS ids.
    //$head .= "X-Rcs-Id: \$Id\$\r\n";

    $iter = $page->getAllRevisions();
    $parts = array();
    while ($revision = $iter->next()) {
        $parts[] = MimeifyPageRevision($page, $revision);
        if ($nversions > 0 && count($parts) >= $nversions)
            break;
    }
    if (count($parts) > 1)
        return $head . MimeMultipart($parts);
    assert($parts);
    return $head . $parts[0];
}

/***
 * Compute filename to used for storing contents of a wiki page.
 *
 * Basically we do a rawurlencode() which encodes everything except
 * ASCII alphanumerics and '.', '-', and '_'.
 *
 * But we also want to encode leading dots to avoid filenames like
 * '.', and '..'. (Also, there's no point in generating "hidden" file
 * names, like '.foo'.)
 *
 * We have to apply a different "/" logic for dumpserial, htmldump and zipdump.
 * dirs are allowed for zipdump and htmldump, not for dumpserial
 *
 *
 * @param $pagename string Pagename.
 * @return string Filename for page.
 */
function FilenameForPage ($pagename, $action = false)
{
    $enc = rawurlencode($pagename);
    if (!$action) {
        global $request;
    $action = $request->getArg('action');
    }
    if ($action != 'dumpserial') { // zip, ziphtml, dumphtml
    // For every %2F we will need to mkdir -p dirname($pagename)
    $enc = preg_replace('/%2F/', '/', $enc);
    }
    $enc = preg_replace('/^\./', '%2E', $enc);
    $enc = preg_replace('/%20/', ' ',   $enc);
    $enc = preg_replace('/\.$/', '%2E', $enc);
    return $enc;
}

/**
 * The main() function which generates a zip archive of a PhpWiki.
 *
 * If $include_archive is false, only the current version of each page
 * is included in the zip file; otherwise all archived versions are
 * included as well.
 */
function MakeWikiZip (&$request)
{
    global $ErrorManager;
    if ($request->getArg('include') == 'all') {
        $zipname         = WIKI_NAME . _("FullDump") . date('Ymd-Hi') . '.zip';
        $include_archive = true;
    }
    else {
        $zipname         = WIKI_NAME . _("LatestSnapshot") . date('Ymd-Hi') . '.zip';
        $include_archive = false;
    }
    $include_empty = false;
    if ($request->getArg('include') == 'empty') {
    $include_empty = true;
    }

    $zip = new ZipWriter("Created by PhpWiki " . PHPWIKI_VERSION, $zipname);

    /* ignore fatals in plugins */
    $ErrorManager->pushErrorHandler(new WikiFunctionCb('_dump_error_handler'));

    $dbi =& $request->_dbi;
    $thispage = $request->getArg('pagename'); // for "Return to ..."
    if ($exclude = $request->getArg('exclude')) {   // exclude which pagenames
        $excludeList = explodePageList($exclude);
    } else {
        $excludeList = array();
    }
    if ($pages = $request->getArg('pages')) {  // which pagenames
        if ($pages == '[]') // current page
            $pages = $thispage;
        $page_iter = new WikiDB_Array_PageIterator(explodePageList($pages));
    } else {
        $page_iter = $dbi->getAllPages(false,false,false,$excludeList);
    }
    $request_args = $request->args;
    $timeout = (! $request->getArg('start_debug')) ? 30 : 240;

    while ($page = $page_iter->next()) {
    $request->args = $request_args; // some plugins might change them (esp. on POST)
        longer_timeout($timeout);     // Reset watchdog

        $current = $page->getCurrentRevision();
        if ($current->getVersion() == 0)
            continue;

        $pagename = $page->getName();
        $wpn = new WikiPageName($pagename);
        if (!$wpn->isValid())
            continue;
        if (in_array($page->getName(), $excludeList)) {
            continue;
        }

        $attrib = array('mtime'    => $current->get('mtime'),
                        'is_ascii' => 1);
        if ($page->get('locked'))
            $attrib['write_protected'] = 1;

        if ($include_archive)
            $content = MailifyPage($page, 0);
        else
            $content = MailifyPage($page);

        $zip->addRegularFile( FilenameForPage($pagename),
                              $content, $attrib);
    }
    $zip->finish();

    $ErrorManager->popErrorHandler();
}

function DumpToDir (&$request)
{
    $directory = $request->getArg('directory');
    if (empty($directory))
        $directory = DEFAULT_DUMP_DIR; // See lib/plugin/WikiForm.php:87
    if (empty($directory))
        $request->finish(_("You must specify a directory to dump to"));

    // see if we can access the directory the user wants us to use
    if (! file_exists($directory)) {
        if (! mkdir($directory, 0755))
            $request->finish(fmt("Cannot create directory '%s'", $directory));
        else
            $html = HTML::p(fmt("Created directory '%s' for the page dump...",
                                $directory));
    } else {
        $html = HTML::p(fmt("Using directory '%s'", $directory));
    }

    StartLoadDump($request, _("Dumping Pages"), $html);

    $dbi =& $request->_dbi;
    $thispage = $request->getArg('pagename'); // for "Return to ..."
    if ($exclude = $request->getArg('exclude')) {   // exclude which pagenames
        $excludeList = explodePageList($exclude);
    } else {
        $excludeList = array();
    }
    $include_empty = false;
    if ($request->getArg('include') == 'empty') {
    $include_empty = true;
    }
    if ($pages = $request->getArg('pages')) {  // which pagenames
        if ($pages == '[]') // current page
            $pages = $thispage;
        $page_iter = new WikiDB_Array_PageIterator(explodePageList($pages));
    } else {
        $page_iter = $dbi->getAllPages($include_empty,false,false,$excludeList);
    }

    $request_args = $request->args;
    $timeout = (! $request->getArg('start_debug')) ? 30 : 240;

    while ($page = $page_iter->next()) {
    $request->args = $request_args; // some plugins might change them (esp. on POST)
        longer_timeout($timeout);     // Reset watchdog

        $pagename = $page->getName();
        if (!isa($request,'MockRequest')) {
            PrintXML(HTML::br(), $pagename, ' ... ');
            flush();
        }

        if (in_array($pagename, $excludeList)) {
            if (!isa($request, 'MockRequest')) {
                PrintXML(_("Skipped."));
                flush();
            }
            continue;
        }
        $filename = FilenameForPage($pagename);
        $msg = HTML();
        if($page->getName() != $filename) {
            $msg->pushContent(HTML::small(fmt("saved as %s", $filename)),
                              " ... ");
        }

        if ($request->getArg('include') == 'all')
            $data = MailifyPage($page, 0);
        else
            $data = MailifyPage($page);

        if ( !($fd = fopen($directory."/".$filename, "wb")) ) {
            $msg->pushContent(HTML::strong(fmt("couldn't open file '%s' for writing",
                                               "$directory/$filename")));
            $request->finish($msg);
        }

        $num = fwrite($fd, $data, strlen($data));
        $msg->pushContent(HTML::small(fmt("%s bytes written", $num)));
        if (!isa($request, 'MockRequest')) {
            PrintXML($msg);
            flush();
        }
        assert($num == strlen($data));
        fclose($fd);
    }

    EndLoadDump($request);
}

function _copyMsg($page, $smallmsg) {
    if (!isa($GLOBALS['request'], 'MockRequest')) {
        if ($page) $msg = HTML(HTML::br(), HTML($page), HTML::small($smallmsg));
        else $msg = HTML::small($smallmsg);
        PrintXML($msg);
        flush();
    }
}

function mkdir_p($pathname, $permission = 0777) {
    $arr = explode("/", $pathname);
    if (empty($arr)) {
    return mkdir($pathname, $permission);
    }
    $s = array_shift($arr);
    $ok = TRUE;
    foreach ($arr as $p) {
    $curr = "$s/$p";
    if (!is_dir($curr))
        $ok = mkdir($curr, $permission);
    $s = $curr;
    if (!$ok) return FALSE;
    }
    return TRUE;
}

/**
 * Dump all pages as XHTML to a directory, as pagename.html.
 * Copies all used css files to the directory, all used images to a
 * "images" subdirectory, and all used buttons to a "images/buttons" subdirectory.
 * The webserver must have write permissions to these directories.
 *   chown httpd HTML_DUMP_DIR; chmod u+rwx HTML_DUMP_DIR
 * should be enough.
 *
 * @param string directory (optional) path to dump to. Default: HTML_DUMP_DIR
 * @param string pages     (optional) Comma-seperated of glob-style pagenames to dump.
 *                                    Also array of pagenames allowed.
 * @param string exclude   (optional) Comma-seperated of glob-style pagenames to exclude
 */
function DumpHtmlToDir (&$request)
{
    global $WikiTheme;
    $directory = $request->getArg('directory');
    if (empty($directory))
        $directory = HTML_DUMP_DIR; // See lib/plugin/WikiForm.php:87
    if (empty($directory))
        $request->finish(_("You must specify a directory to dump to"));

    // See if we can access the directory the user wants us to use
    if (! file_exists($directory)) {
        if (! mkdir($directory, 0755))
            $request->finish(fmt("Cannot create directory '%s'", $directory));
        else
            $html = HTML::p(fmt("Created directory '%s' for the page dump...",
                                $directory));
    } else {
        $html = HTML::p(fmt("Using directory '%s'", $directory));
    }
    StartLoadDump($request, _("Dumping Pages"), $html);
    $thispage = $request->getArg('pagename'); // for "Return to ..."

    $dbi =& $request->_dbi;
    if ($exclude = $request->getArg('exclude')) {   // exclude which pagenames
        $excludeList = explodePageList($exclude);
    } else {
        $excludeList = array('DebugAuthInfo', 'DebugGroupInfo', 'AuthInfo');
    }
    if ($pages = $request->getArg('pages')) {  // which pagenames
        if ($pages == '[]') // current page
            $pages = $thispage;
        $page_iter = new WikiDB_Array_generic_iter(explodePageList($pages));
    // not at admin page: dump only the current page
    } elseif ($thispage != _("PhpWikiAdministration")) {
        $page_iter = new WikiDB_Array_generic_iter(array($thispage));
    } else {
        $page_iter = $dbi->getAllPages(false,false,false,$excludeList);
    }

    $WikiTheme->DUMP_MODE = 'HTML';
    _DumpHtmlToDir($directory, $page_iter, $request->getArg('exclude'));
    $WikiTheme->DUMP_MODE = false;

    $request->setArg('pagename',$thispage); // Template::_basepage fix
    EndLoadDump($request);
}

/* Known problem: any plugins or other code which echo()s text will
 * lead to a corrupted html zip file which may produce the following
 * errors upon unzipping:
 *
 * warning [wikihtml.zip]:  2401 extra bytes at beginning or within zipfile
 * file #58:  bad zipfile offset (local header sig):  177561
 *  (attempting to re-compensate)
 *
 * However, the actual wiki page data should be unaffected.
 */
function MakeWikiZipHtml (&$request)
{
    global $WikiTheme;
    if ($request->getArg('zipname')) {
        $zipname = basename($request->getArg('zipname'));
        if (!preg_match("/\.zip$/i", $zipname))
            $zipname .= ".zip";
        $request->setArg('zipname', false);
    } else {
        $zipname = "wikihtml.zip";
    }
    $zip = new ZipWriter("Created by PhpWiki " . PHPWIKI_VERSION, $zipname);
    $dbi =& $request->_dbi;
    $thispage = $request->getArg('pagename'); // for "Return to ..."
    if ($pages = $request->getArg('pages')) {  // which pagenames
        if ($pages == '[]') // current page
            $pages = $thispage;
        $page_iter = new WikiDB_Array_generic_iter(explodePageList($pages));
    } else {
        $page_iter = $dbi->getAllPages(false,false,false,$request->getArg('exclude'));
    }

    $WikiTheme->DUMP_MODE = 'ZIPHTML';
    _DumpHtmlToDir($zip, $page_iter, $request->getArg('exclude'));
    $WikiTheme->DUMP_MODE = false;
}

/*
 * Internal html dumper. Used for dumphtml, ziphtml and pdf
 */
function _DumpHtmlToDir ($target, $page_iter, $exclude = false)
{
    global $WikiTheme, $request, $ErrorManager;
    $silent = true; $zip = false; $directory = false;
    if ($WikiTheme->DUMP_MODE == 'HTML') {
        $directory = $target;
        $silent = false;
    } elseif ($WikiTheme->DUMP_MODE == 'PDFHTML') {
        $directory = $target;
    } elseif (is_object($target)) { // $WikiTheme->DUMP_MODE == 'ZIPHTML'
        $zip = $target;
    }

    $request->_TemplatesProcessed = array();
    if ($exclude) {   // exclude which pagenames
        $excludeList = explodePageList($exclude);
    } else {
        $excludeList = array('DebugAuthInfo', 'DebugGroupInfo', 'AuthInfo');
    }
    $WikiTheme->VALID_LINKS = array();
    if ($request->getArg('format')) { // pagelist
        $page_iter_sav = $page_iter;
        foreach ($page_iter_sav->asArray() as $handle) {
            $WikiTheme->VALID_LINKS[] = is_string($handle) ? $handle : $handle->getName();
        }
        $page_iter_sav->reset();
    }

    if (defined('HTML_DUMP_SUFFIX')) {
        $WikiTheme->HTML_DUMP_SUFFIX = HTML_DUMP_SUFFIX;
    }
    if (isset($WikiTheme->_MoreAttr['body'])) {
        $_bodyAttr = $WikiTheme->_MoreAttr['body'];
        unset($WikiTheme->_MoreAttr['body']);
    }

    $ErrorManager->pushErrorHandler(new WikiFunctionCb('_dump_error_handler'));

    // check if the dumped file will be accessible from outside
    $doc_root = $request->get("DOCUMENT_ROOT");
    if ($WikiTheme->DUMP_MODE == 'HTML') {
        $ldir = NormalizeLocalFileName($directory);
        $wikiroot = NormalizeLocalFileName('');
        if (string_starts_with($ldir, $doc_root)) {
            $link_prefix = substr($directory, strlen($doc_root))."/";
        } elseif (string_starts_with($ldir, $wikiroot)) {
            $link_prefix = NormalizeWebFileName(substr($directory, strlen($wikiroot)))."/";
        } else {
            $prefix = '';
            if (isWindows()) {
                $prefix = '/'; // . substr($doc_root,0,2); // add drive where apache is installed
            }
            $link_prefix = "file://".$prefix.$directory."/";
        }
    } else {
        $link_prefix = "";
    }

    $request_args = $request->args;
    $timeout = (! $request->getArg('start_debug')) ? 60 : 240;
    if ($directory) {
        if (isWindows())
            $directory = str_replace("\\", "/", $directory); // no Win95 support.
        if (!is_dir("$directory/images"))
            mkdir("$directory/images");
    }
    $already = array();
    $outfiles = array();
    $already_images = array();

    while ($page = $page_iter->next()) {
        if (is_string($page)) {
            $pagename = $page;
            $page = $request->_dbi->getPage($pagename);
        } else {
            $pagename = $page->getName();
        }
        if (empty($firstpage)) $firstpage = $pagename;
        if (array_key_exists($pagename, $already))
            continue;
        $already[$pagename] = 1;
        $current = $page->getCurrentRevision();
        //if ($current->getVersion() == 0)
        //    continue;

        $request->args = $request_args; // some plugins might change them (esp. on POST)
        longer_timeout($timeout);     // Reset watchdog

        if ($zip) {
            $attrib = array('mtime'    => $current->get('mtime'),
                            'is_ascii' => 1);
            if ($page->get('locked'))
                $attrib['write_protected'] = 1;
        } elseif (!$silent) {
            if (!isa($request,'MockRequest')) {
                PrintXML(HTML::br(), $pagename, ' ... ');
                flush();
            }
        }
        if (in_array($pagename, $excludeList)) {
            if (!$silent and !isa($request,'MockRequest')) {
                PrintXML(_("Skipped."));
                flush();
            }
            continue;
        }
        $relative_base = '';
        if ($WikiTheme->DUMP_MODE == 'PDFHTML')
            $request->setArg('action', 'pdf');   // to omit cache headers
        $request->setArg('pagename', $pagename); // Template::_basepage fix
        $filename = FilenameForPage($pagename) . $WikiTheme->HTML_DUMP_SUFFIX;
        $args = array('revision'      => $current,
                      'CONTENT'       => $current->getTransformedContent(),
                      'relative_base' => $relative_base);
        // For every %2F will need to mkdir -p dirname($pagename)
        if (preg_match("/(%2F|\/)/", $filename)) {
            // mkdir -p and set relative base for subdir pages
            $filename = preg_replace("/%2F/", "/", $filename);
            $count = substr_count($filename, "/");
            $dirname = dirname($filename);
            if ($directory)
                mkdir_p($directory."/".$dirname);
            // Fails with "XX / YY", "XX" is created, "XX / YY" cannot be written
            // if (isWindows()) // interesting Windows bug: cannot mkdir "bla "
            // Since dumps needs to be copied, we have to disallow this for all platforms.
            $filename = preg_replace("/ \//", "/", $filename);
            $relative_base = "../";
            while ($count > 1) {
                $relative_base .= "../";
                $count--;
            }
            $args['relative_base'] = $relative_base;
        }
        $msg = HTML();

        $DUMP_MODE = $WikiTheme->DUMP_MODE;
        $data = GeneratePageasXML(new Template('browse', $request, $args),
                     $pagename, $current, $args);
        $WikiTheme->DUMP_MODE = $DUMP_MODE;

        if (preg_match_all("/<img .*?src=\"(\/.+?)\"/", $data, $m)) {
            // fix to local relative path for uploaded images, so that pdf will work
            foreach ($m[1] as $img_file) {
                $base = basename($img_file);
                $data = str_replace('src="'.$img_file.'"','src="images/'.$base.'"', $data);
                if (array_key_exists($img_file, $already_images))
                    continue;
                $already_images[$img_file] = 1;
                // resolve src from webdata to file
                $src = $doc_root . $img_file;
                if (file_exists($src) and $base) {
                    if ($directory) {
                        $target = "$directory/images/$base";
                        if (copy($src, $target)) {
                            if (!$silent)
                                _copyMsg($img_file, fmt("... copied to %s", $target));
                        } else {
                            if (!$silent)
                                _copyMsg($img_file, fmt("... not copied to %s", $target));
                        }
                    } else {
                        $target = "images/$base";
                        $zip->addSrcFile($target, $src);
                    }
                }
            }
        }

    if ($directory) {
        $outfile = $directory."/".$filename;
        if ( !($fd = fopen($outfile, "wb")) ) {
        $msg->pushContent(HTML::strong(fmt("couldn't open file '%s' for writing",
                           $outfile)));
        $request->finish($msg);
        }
        $len = strlen($data);
        $num = fwrite($fd, $data, $len);
        if ($pagename != $filename) {
        $link = LinkURL($link_prefix.$filename, $filename);
        $msg->pushContent(HTML::small(_("saved as "), $link, " ... "));
        }
        $msg->pushContent(HTML::small(fmt("%s bytes written", $num), "\n"));
        if (!$silent) {
        if (!isa($request, 'MockRequest')) {
            PrintXML($msg);
        }
        flush();
        $request->chunkOutput();
        }
        assert($num == $len);
        fclose($fd);
        $outfiles[] = $outfile;
    } else {
        $zip->addRegularFile($filename, $data, $attrib);
    }

        if (USECACHE) {
            $request->_dbi->_cache->invalidate_cache($pagename);
            unset ($request->_dbi->_cache->_pagedata_cache);
            unset ($request->_dbi->_cache->_versiondata_cache);
            unset ($request->_dbi->_cache->_glv_cache);
        }
        unset ($request->_dbi->_cache->_backend->_page_data);

        unset($msg);
        unset($current->_transformedContent);
        unset($current);
    if (!empty($template)) {
        unset($template->_request);
        unset($template);
    }
        unset($data);
    }
    $page_iter->free();

    $attrib = false; //array('is_ascii' => 0);
    if (!empty($WikiTheme->dumped_images) and is_array($WikiTheme->dumped_images)) {
        // @mkdir("$directory/images");
        foreach ($WikiTheme->dumped_images as $img_file) {
        if (array_key_exists($img_file, $already_images))
            continue;
        $already_images[$img_file] = 1;
            if ($img_file
                and ($from = $WikiTheme->_findFile($img_file, true))
                and basename($from))
            {
        if ($directory) {
            $target = "$directory/images/".basename($from);
            if ($silent)
            copy($WikiTheme->_path . $from, $target);
            else {
            if (copy($WikiTheme->_path . $from, $target)) {
                _copyMsg($from, fmt("... copied to %s", $target));
            } else {
                _copyMsg($from, fmt("... not copied to %s", $target));
            }
            }
        } else {
            $target = "images/".basename($from);
            $zip->addSrcFile($target, $WikiTheme->_path . $from);
        }
            } elseif (!$silent) {
                _copyMsg($from, _("... not found"));
            }
        }
    }

    if (!empty($WikiTheme->dumped_buttons)
         and is_array($WikiTheme->dumped_buttons))
    {
        // Buttons also
        if ($directory && !is_dir("$directory/images/buttons"))
            mkdir("$directory/images/buttons");
        foreach ($WikiTheme->dumped_buttons as $text => $img_file) {
            if (array_key_exists($img_file, $already_images))
                continue;
        $already_images[$img_file] = 1;
            if ($img_file
                and ($from = $WikiTheme->_findFile($img_file, true))
                and basename($from))
            {
        if ($directory) {
            $target = "$directory/images/buttons/".basename($from);
            if ($silent)
            copy($WikiTheme->_path . $from, $target);
            else {
                if (copy($WikiTheme->_path . $from, $target)) {
                    _copyMsg($from, fmt("... copied to %s", $target));
                } else {
                    _copyMsg($from, fmt("... not copied to %s", $target));
                }
            }
        } else {
            $target = "images/buttons/".basename($from);
            $zip->addSrcFile($target, $WikiTheme->_path . $from);
        }
            } elseif (!$silent) {
                _copyMsg($from, _("... not found"));
            }
        }
    }
    if (!empty($WikiTheme->dumped_css) and is_array($WikiTheme->dumped_css)) {
        foreach ($WikiTheme->dumped_css as $css_file) {
            if (array_key_exists($css_file, $already_images))
                continue;
        $already_images[$css_file] = 1;
            if ($css_file
                and ($from = $WikiTheme->_findFile(basename($css_file), true))
                and basename($from))
            {
        // TODO: fix @import url(main.css);
        if ($directory) {
            $target = "$directory/" . basename($css_file);
            if ($silent)
            copy($WikiTheme->_path . $from, $target);
            else {
            if (copy($WikiTheme->_path . $from, $target)) {
                _copyMsg($from, fmt("... copied to %s", $target));
            } else {
                _copyMsg($from, fmt("... not copied to %s", $target));
            }
            }
        } else {
            //$attrib = array('is_ascii' => 0);
            $target = basename($css_file);
            $zip->addSrcFile($target, $WikiTheme->_path . $from);
        }
            } elseif (!$silent) {
                _copyMsg($from, _("... not found"));
            }
        }
    }

    if ($zip)
    $zip->finish();

    if ($WikiTheme->DUMP_MODE == 'PDFHTML') {
    if (USE_EXTERNAL_HTML2PDF and $outfiles) {
        $cmd = EXTERNAL_HTML2PDF_PAGELIST.' "'.join('" "', $outfiles).'"';
        $filename = FilenameForPage($firstpage);
        if (DEBUG) {
        $tmpfile = $directory . "/createpdf.bat";
        $fp = fopen($tmpfile, "wb");
        fwrite($fp, $cmd . " > $filename.pdf");
        fclose($fp);
        }
        if (!headers_sent()) {
        Header('Content-Type: application/pdf');
        passthru($cmd);
        }
        else {
        $tmpdir = getUploadFilePath();
        $s = passthru($cmd . " > $tmpdir/$filename.pdf");
        $errormsg = "<br />\nGenerated <a href=\"".getUploadDataPath()."$filename.pdf\">Upload:$filename.pdf</a>\n";
        $errormsg .= $s;
        echo $errormsg;
        }
        if (!DEBUG) {
        foreach($outfiles as $f) unlink($f);
        }
    }
    if (!empty($errormsg)) {
        $request->discardOutput();
        $GLOBALS['ErrorManager']->_postponed_errors = array();
    }
    }

    $ErrorManager->popErrorHandler();

    $WikiTheme->HTML_DUMP_SUFFIX = '';
    $WikiTheme->DUMP_MODE = false;
    $WikiTheme->_MoreAttr['body'] = isset($_bodyAttr) ? $_bodyAttr : '';
}


////////////////////////////////////////////////////////////////
//
//  Functions for restoring.
//
////////////////////////////////////////////////////////////////

function SavePage (&$request, &$pageinfo, $source, $filename)
{
    static $overwite_all = false;
    $pagedata    = $pageinfo['pagedata'];    // Page level meta-data.
    $versiondata = $pageinfo['versiondata']; // Revision level meta-data.

    if (empty($pageinfo['pagename'])) {
        PrintXML(HTML::p(HTML::strong(_("Empty pagename!"))));
        return;
    }

    if (empty($versiondata['author_id']))
        $versiondata['author_id'] = $versiondata['author'];

    // remove invalid backend specific chars. utf8 issues mostly
    $pagename_check = new WikiPagename($pageinfo['pagename']);
    if (!$pagename_check->isValid()) {
        PrintXML(HTML::p(HTML::strong(_("Invalid pagename!")." ".$pageinfo['pagename'])));
        return;
    }
    $pagename = $pagename_check->getName();
    $content  = $pageinfo['content'];

    if ($pagename == _("InterWikiMap"))
        $content = _tryinsertInterWikiMap($content);

    $dbi =& $request->_dbi;
    $page = $dbi->getPage($pagename);

    // Try to merge if updated pgsrc contents are different. This
    // whole thing is hackish
    //
    // TODO: try merge unless:
    // if (current contents = default contents && pgsrc_version >=
    // pgsrc_version) then just upgrade this pgsrc
    $needs_merge = false;
    $merging = false;
    $overwrite = false;

    if ($request->getArg('merge')) {
        $merging = true;
    }
    else if ($request->getArg('overwrite')) {
        $overwrite = true;
    }

    $current = $page->getCurrentRevision();
    $skip = false;
    $edit = $request->getArg('edit');
    if ($merging) {
        if (isset($edit['keep_old'])) {
            $merging = false;
            $skip = true;
        }
        elseif (isset($edit['overwrite'])) {
            $merging = false;
            $overwrite = true;
        }
        elseif ( $current and (! $current->hasDefaultContents())
         && ($current->getPackedContent() != $content) )
        {
            include_once('lib/editpage.php');
            $request->setArg('pagename', $pagename);
            $v = $current->getVersion();
            $request->setArg('revision', $current->getVersion());
            $p = new LoadFileConflictPageEditor($request);
            $p->_content = $content;
            $p->_currentVersion = $v - 1;
            $p->editPage($saveFailed = true);
            return; //early return
       }
    }
    if (!$skip)
      foreach ($pagedata as $key => $value) {
        if (!empty($value))
            $page->set($key, $value);
      }

    $mesg = HTML::p(array('style' => 'text-indent: 3em;'));
    if ($source)
        $mesg->pushContent(' ', fmt("from %s", $source));

    if (!$current) {
        //FIXME: This should not happen! (empty vdata, corrupt cache or db)
        $current = $page->getCurrentRevision();
    }
    if ($current->getVersion() == 0) {
        $versiondata['author'] = ADMIN_USER;
        $versiondata['author_id'] = ADMIN_USER;
        $mesg->pushContent(' - ', _("New page"));
        $isnew = true;
    }
    else {
        if ( (! $current->hasDefaultContents())
             && ($current->getPackedContent() != $content) ) {
            if ($overwrite) {
                $mesg->pushContent(' ',
                                   fmt("has edit conflicts - overwriting anyway"));
                $skip = false;
                if (substr_count($source, 'pgsrc')) {
                    $versiondata['author'] = ADMIN_USER;
                    // but leave authorid as userid who loaded the file
                }
            }
            else {
        if (isset($edit['keep_old'])) {
            $mesg->pushContent(' ', fmt("keep old"));
        } else {
            $mesg->pushContent(' ', fmt("has edit conflicts - skipped"));
            $needs_merge = true; // hackish, to display the buttons
        }
                $skip = true;
            }
        }
        else if ($current->getPackedContent() == $content) {
            // The page content is the same, we don't need a new revision.
            $mesg->pushContent(' ',
                               fmt("content is identical to current version %d - no new revision created",
                                   $current->getVersion()));
            $skip = true;
        }
        $isnew = false;
    }

    if (! $skip ) {
        // in case of failures print the culprit:
        if (!isa($request,'MockRequest')) {
            PrintXML(HTML::p(WikiLink($pagename))); flush();
        }
        $new = $page->save($content, WIKIDB_FORCE_CREATE, $versiondata);
        $dbi->touch();
        $mesg->pushContent(' ', fmt("- saved to database as version %d",
                                    $new->getVersion()));
    }
    if ($needs_merge) {
        $f = $source;
        // hackish, $source contains needed path+filename
        $f = str_replace(sprintf(_("MIME file %s"), ''), '', $f);
        $f = str_replace(sprintf(_("Serialized file %s"), ''), '', $f);
        $f = str_replace(sprintf(_("plain file %s"), ''), '', $f);
        //check if uploaded file? they pass just the content, but the file is gone
        if (@stat($f)) {
            global $WikiTheme;
            $meb = Button(array('action' => 'loadfile',
                                'merge'=> true,
                                'source'=> $f),
                          _("Merge Edit"),
                          _("PhpWikiAdministration"),
                          'wikiadmin');
            $owb = Button(array('action' => 'loadfile',
                                'overwrite'=> true,
                                'source'=> $f),
                          _("Restore Anyway"),
                          _("PhpWikiAdministration"),
                          'wikiunsafe');
            $mesg->pushContent(' ', $meb, " ", $owb);
            if (!$overwite_all) {
                $args = $request->getArgs();
                $args['overwrite'] = 1;
                $owb = Button($args,
                              _("Overwrite All"),
                              _("PhpWikiAdministration"),
                              'wikiunsafe');
                $mesg->pushContent(HTML::span(array('class' => 'hint'), $owb));
                $overwite_all = true;
            }
        } else {
            $mesg->pushContent(HTML::em(_(" Sorry, cannot merge.")));
        }
    }

    if (!isa($request,'MockRequest')) {
      if ($skip)
        PrintXML(HTML::p(HTML::em(WikiLink($pagename))), $mesg);
      else
        PrintXML($mesg);
      flush();
    }
}

// action=revert (by diff)
function RevertPage (&$request)
{
    $mesg = HTML::div();
    $pagename = $request->getArg('pagename');
    $version = $request->getArg('version');
    if (!$version) {
        PrintXML(HTML::p(fmt("Revert")," ",WikiLink($pagename)),
                 HTML::p(_("missing required version argument")));
        return;
    }
    $dbi =& $request->_dbi;
    $page = $dbi->getPage($pagename);
    $current = $page->getCurrentRevision();
    $currversion = $current->getVersion();
    if ($currversion == 0) {
        $mesg->pushContent(' ', _("no page content"));
        PrintXML(HTML::p(fmt("Revert")," ",WikiLink($pagename)),
                 $mesg);
        flush();
        return;
    }
    if ($currversion == $version) {
        $mesg->pushContent(' ', _("same version page"));
        PrintXML(HTML::p(fmt("Revert")," ",WikiLink($pagename)),
                 $mesg);
        flush();
        return;
    }
    if ($request->getArg('cancel')) {
        $mesg->pushContent(' ', _("Cancelled"));
        PrintXML(HTML::p(fmt("Revert")," ",WikiLink($pagename)),
                 $mesg);
        flush();
        return;
    }
    if (!$request->getArg('verify')) {
        $mesg->pushContent(HTML::p(fmt("Are you sure to revert %s to version $version?", WikiLink($pagename))),
                           HTML::form(array('action' => $request->getPostURL(),
                                            'method' => 'post'),
                                      HiddenInputs($request->getArgs(), false, array('verify')),
                                      HiddenInputs(array('verify' => 1)),
                                      Button('submit:verify', _("Yes"), 'button'),
                                      HTML::Raw('&nbsp;'),
                                      Button('submit:cancel', _("Cancel"), 'button'))
                           );
        $rev = $page->getRevision($version);
        $html = HTML(HTML::fieldset($mesg), HTML::hr(), $rev->getTransformedContent());
        $template = Template('browse',
                             array('CONTENT' => $html));
        GeneratePage($template, $pagename, $rev);
        $request->checkValidators();
        flush();
        return;
    }
    $rev = $page->getRevision($version);
    $content = $rev->getPackedContent();
    $versiondata = $rev->_data;
    $versiondata['summary'] = sprintf(_("revert to version %d"), $version);
    $new = $page->save($content, $currversion + 1, $versiondata);
    $dbi->touch();

    $mesg = HTML::span();
    $pagelink = WikiLink($pagename);
    $mesg->pushContent(fmt("Revert: %s", $pagelink),
                       fmt("- version %d saved to database as version %d",
                           $version, $new->getVersion()));
    // Force browse of current page version.
    $request->setArg('version', false);
    $template = Template('savepage', array());
    $template->replace('CONTENT', $new->getTransformedContent());

    GeneratePage($template, $mesg, $new);
    flush();
}

function _tryinsertInterWikiMap($content) {
    $goback = false;
    if (strpos($content, "<verbatim>")) {
        //$error_html = " The newly loaded pgsrc already contains a verbatim block.";
        $goback = true;
    }
    if (!$goback && !defined('INTERWIKI_MAP_FILE')) {
        $error_html = sprintf(" "._("%s: not defined"), "INTERWIKI_MAP_FILE");
        $goback = true;
    }
    $mapfile = FindFile(INTERWIKI_MAP_FILE,1);
    if (!$goback && !file_exists($mapfile)) {
        $error_html = sprintf(" "._("%s: file not found"), INTERWIKI_MAP_FILE);
        $goback = true;
    }

    if (!empty($error_html))
        trigger_error(_("Default InterWiki map file not loaded.")
                      . $error_html, E_USER_NOTICE);
    if ($goback)
        return $content;

    // if loading from virgin setup do echo, otherwise trigger_error E_USER_NOTICE
    if (!isa($GLOBALS['request'], 'MockRequest'))
        echo sprintf(_("Loading InterWikiMap from external file %s."), $mapfile),"<br />";

    $fd = fopen ($mapfile, "rb");
    $data = fread ($fd, filesize($mapfile));
    fclose ($fd);
    $content = $content . "\n<verbatim>\n$data</verbatim>\n";
    return $content;
}

function ParseSerializedPage($text, $default_pagename, $user)
{
    if (!preg_match('/^a:\d+:{[si]:\d+/', $text))
        return false;

    $pagehash = unserialize($text);

    // Split up pagehash into four parts:
    //   pagename
    //   content
    //   page-level meta-data
    //   revision-level meta-data

    if (!defined('FLAG_PAGE_LOCKED'))
        define('FLAG_PAGE_LOCKED', 1);
    if (!defined('FLAG_PAGE_EXTERNAL'))
        define('FLAG_PAGE_EXTERNAL', 1);
    $pageinfo = array('pagedata'    => array(),
                      'versiondata' => array());

    $pagedata = &$pageinfo['pagedata'];
    $versiondata = &$pageinfo['versiondata'];

    // Fill in defaults.
    if (empty($pagehash['pagename']))
        $pagehash['pagename'] = $default_pagename;
    if (empty($pagehash['author'])) {
        $pagehash['author'] = $user->getId();
    }

    foreach ($pagehash as $key => $value) {
        switch($key) {
            case 'pagename':
            case 'version':
            case 'hits':
                $pageinfo[$key] = $value;
                break;
            case 'content':
                $pageinfo[$key] = join("\n", $value);
                break;
            case 'flags':
                if (($value & FLAG_PAGE_LOCKED) != 0)
                    $pagedata['locked'] = 'yes';
                if (($value & FLAG_PAGE_EXTERNAL) != 0)
                    $pagedata['external'] = 'yes';
                break;
            case 'owner':
            case 'created':
                $pagedata[$key] = $value;
                break;
            case 'acl':
            case 'perm':
                $pagedata['perm'] = ParseMimeifiedPerm($value);
                break;
            case 'lastmodified':
                $versiondata['mtime'] = $value;
                break;
            case 'author':
            case 'author_id':
            case 'summary':
                $versiondata[$key] = $value;
                break;
        }
    }
    if (empty($pagehash['charset']))
        $pagehash['charset'] = 'utf-8';
    // compare to target charset
    if (strtolower($pagehash['charset']) != strtolower($GLOBALS['charset'])) {
        $pageinfo['content'] = charset_convert($params['charset'], $GLOBALS['charset'], $pageinfo['content']);
        $pageinfo['pagename'] = charset_convert($params['charset'], $GLOBALS['charset'], $pageinfo['pagename']);
    }
    return $pageinfo;
}

function SortByPageVersion ($a, $b) {
    return $a['version'] - $b['version'];
}

/**
 * Security alert! We should not allow to import config.ini into our wiki (or from a sister wiki?)
 * because the sql passwords are in plaintext there. And the webserver must be able to read it.
 * Detected by Santtu Jarvi.
 */
function LoadFile (&$request, $filename, $text = false, $mtime = false)
{
    if (preg_match("/config$/", dirname($filename))             // our or other config
        and preg_match("/config.*\.ini/", basename($filename))) // backups and other versions also
    {
        trigger_error(sprintf("Refused to load %s", $filename), E_USER_WARNING);
        return;
    }
    if (!is_string($text)) {
        // Read the file.
        $stat  = stat($filename);
        $mtime = $stat[9];
        $text  = implode("", file($filename));
    }

    if (! $request->getArg('start_debug')) @set_time_limit(30); // Reset watchdog
    else @set_time_limit(240);

    // FIXME: basename("filewithnoslashes") seems to return garbage sometimes.
    $basename = basename("/dummy/" . $filename);

    if (!$mtime)
        $mtime = time();    // Last resort.

    // DONE: check source - target charset for content and pagename
    // but only for pgsrc'ed content, not from the browser.

    $default_pagename = rawurldecode($basename);
    if ( ($parts = ParseMimeifiedPages($text)) ) {
        if (count($parts) > 1)
            $overwrite = $request->getArg('overwrite');
        usort($parts, 'SortByPageVersion');
        foreach ($parts as $pageinfo) {
            // force overwrite
            if (count($parts) > 1)
                $request->setArg('overwrite', 1);
            SavePage($request, $pageinfo, sprintf(_("MIME file %s"),
                                                  $filename), $basename);
    }
        if (count($parts) > 1)
            if ($overwrite)
                $request->setArg('overwrite', $overwrite);
            else
            unset($request->_args['overwrite']);
    }
    else if ( ($pageinfo = ParseSerializedPage($text, $default_pagename,
                                               $request->getUser())) ) {
        SavePage($request, $pageinfo, sprintf(_("Serialized file %s"),
                                              $filename), $basename);
    }
    else {
        // plain old file
        $user = $request->getUser();

        $file_charset = 'utf-8';
        // compare to target charset
        if ($file_charset != strtolower($GLOBALS['charset'])) {
            $text = charset_convert($file_charset, $GLOBALS['charset'], $text);
            $default_pagename = charset_convert($file_charset, $GLOBALS['charset'], $default_pagename);
        }

        // Assume plain text file.
        $pageinfo = array('pagename' => $default_pagename,
                          'pagedata' => array(),
                          'versiondata'
                          => array('author' => $user->getId()),
                          'content'  => preg_replace('/[ \t\r]*\n/', "\n",
                                                     chop($text))
                          );
        SavePage($request, $pageinfo, sprintf(_("plain file %s"), $filename),
                 $basename);
    }
}

function LoadZip (&$request, $zipfile, $files = false, $exclude = false) {
    $zip = new ZipReader($zipfile);
    $timeout = (! $request->getArg('start_debug')) ? 20 : 120;
    while (list ($fn, $data, $attrib) = $zip->readFile()) {
        // FIXME: basename("filewithnoslashes") seems to return
        // garbage sometimes.
        $fn = basename("/dummy/" . $fn);
        if ( ($files && !in_array($fn, $files))
             || ($exclude && in_array($fn, $exclude)) ) {
            PrintXML(HTML::p(WikiLink($fn)),
                     HTML::p(_("Skipping")));
            flush();
            continue;
        }
        longer_timeout($timeout);     // longer timeout per page
        LoadFile($request, $fn, $data, $attrib['mtime']);
    }
}

function LoadDir (&$request, $dirname, $files = false, $exclude = false) {
    $fileset = new LimitedFileSet($dirname, $files, $exclude);

    if (!$files and ($skiplist = $fileset->getSkippedFiles())) {
        PrintXML(HTML::p(HTML::strong(_("Skipping"))));
        $list = HTML::ul();
        foreach ($skiplist as $file)
            $list->pushContent(HTML::li(WikiLink($file)));
        PrintXML(HTML::p($list));
    }

    // Defer HomePage loading until the end. If anything goes wrong
    // the pages can still be loaded again.
    $files = $fileset->getFiles();
    if (in_array(HOME_PAGE, $files)) {
        $files = array_diff($files, array(HOME_PAGE));
        $files[] = HOME_PAGE;
    }
    $timeout = (! $request->getArg('start_debug')) ? 20 : 120;
    foreach ($files as $file) {
        longer_timeout($timeout);     // longer timeout per page
        if (substr($file,-1,1) != '~')  // refuse to load backup files
            LoadFile($request, "$dirname/$file");
    }
}

class LimitedFileSet extends FileSet {
    function LimitedFileSet($dirname, $_include, $exclude) {
        $this->_includefiles = $_include;
        $this->_exclude = $exclude;
        $this->_skiplist = array();
        parent::FileSet($dirname);
    }

    function _filenameSelector($fn) {
        $incl = &$this->_includefiles;
        $excl = &$this->_exclude;

        if ( ($incl && !in_array($fn, $incl))
             || ($excl && in_array($fn, $excl)) ) {
            $this->_skiplist[] = $fn;
            return false;
        } else {
            return true;
        }
    }

    function getSkippedFiles () {
        return $this->_skiplist;
    }
}


function IsZipFile ($filename_or_fd)
{
    // See if it looks like zip file
    if (is_string($filename_or_fd))
    {
        $fd    = fopen($filename_or_fd, "rb");
        $magic = fread($fd, 4);
        fclose($fd);
    }
    else
    {
        $fpos  = ftell($filename_or_fd);
        $magic = fread($filename_or_fd, 4);
        fseek($filename_or_fd, $fpos);
    }

    return $magic == ZIP_LOCHEAD_MAGIC || $magic == ZIP_CENTHEAD_MAGIC;
}


function LoadAny (&$request, $file_or_dir, $files = false, $exclude = false)
{
    // Try urlencoded filename for accented characters.
    if (!file_exists($file_or_dir)) {
        // Make sure there are slashes first to avoid confusing phps
        // with broken dirname or basename functions.
        // FIXME: windows uses \ and :
        if (is_integer(strpos($file_or_dir, "/"))) {
            $newfile = FindFile($file_or_dir, true);
            // Panic. urlencoded by the browser (e.g. San%20Diego => San Diego)
            if (!$newfile)
                $file_or_dir = dirname($file_or_dir) . "/"
                    . rawurlencode(basename($file_or_dir));
        } else {
            // This is probably just a file.
            $file_or_dir = rawurlencode($file_or_dir);
        }
    }

    $type = filetype($file_or_dir);
    if ($type == 'link') {
        // For symbolic links, use stat() to determine
        // the type of the underlying file.
        list(,,$mode) = stat($file_or_dir);
        $type = ($mode >> 12) & 017;
        if ($type == 010)
            $type = 'file';
        elseif ($type == 004)
            $type = 'dir';
    }

    if (! $type) {
        $request->finish(fmt("Empty or not existing source. Unable to load: %s", $file_or_dir));
    }
    else if ($type == 'dir') {
        LoadDir($request, $file_or_dir, $files, $exclude);
    }
    else if ($type != 'file' && !preg_match('/^(http|ftp):/', $file_or_dir))
    {
        $request->finish(fmt("Bad file type: %s", $type));
    }
    else if (IsZipFile($file_or_dir)) {
        LoadZip($request, $file_or_dir, $files, $exclude);
    }
    else /* if (!$files || in_array(basename($file_or_dir), $files)) */
    {
        LoadFile($request, $file_or_dir);
    }
}

function LoadFileOrDir (&$request)
{
    $source = $request->getArg('source');
    $finder = new FileFinder;
    $source = $finder->slashifyPath($source);
    StartLoadDump($request,
        sprintf(_("Loading '%s'"), $source));
    LoadAny($request, $source);
    EndLoadDump($request);
}

/**
 * HomePage was not found so first-time install is supposed to run.
 * - import all pgsrc pages.
 * - Todo: installer interface to edit config/config.ini settings
 * - Todo: ask for existing old index.php to convert to config/config.ini
 * - Todo: theme-specific pages:
 *   blog - HomePage, ADMIN_USER/Blogs
 */
function SetupWiki (&$request)
{
    global $GenericPages, $LANG;

    //FIXME: This is a hack (err, "interim solution")
    // This is a bogo-bogo-login:  Login without
    // saving login information in session state.
    // This avoids logging in the unsuspecting
    // visitor as ADMIN_USER
    //
    // This really needs to be cleaned up...
    // (I'm working on it.)
    $real_user = $request->_user;
    if (ENABLE_USER_NEW)
        $request->_user = new _BogoUser(ADMIN_USER);

    else
        $request->_user = new WikiUser($request, ADMIN_USER, WIKIAUTH_BOGO);

    StartLoadDump($request, _("Loading up virgin wiki"));

    $pgsrc = FindLocalizedFile(WIKI_PGSRC);
    $default_pgsrc = FindFile(DEFAULT_WIKI_PGSRC);

    $request->setArg('overwrite', true);
    if ($default_pgsrc != $pgsrc) {
        LoadAny($request, $default_pgsrc, $GenericPages);
    }
    $request->setArg('overwrite', false);
    LoadAny($request, $pgsrc);
    $dbi =& $request->_dbi;

    // Ensure that all mandatory pages are loaded
    $finder = new FileFinder;

    if (!FUSIONFORGE) {
        $mandatory = explode(':','SandBox:Template/Category:Template/Talk:SpecialPages:CategoryCategory:CategoryActionPage:Help/OldTextFormattingRules:Help/TextFormattingRules:PhpWikiAdministration');
    } else if (WIKI_NAME == "help") {
        $mandatory = explode(':','SandBox:Template/Category:Template/Talk:SpecialPages:CategoryCategory:CategoryActionPage:Help/TextFormattingRules:PhpWikiAdministration');
    } else {
        $mandatory = explode(':','SandBox:Template/UserPage:Template/Category:Template/Talk:SpecialPages:CategoryCategory:CategoryActionPage:TextFormattingRules:PhpWikiAdministration');
    }
    foreach (array_merge($mandatory,
                         $GLOBALS['AllActionPages'],
                         array(constant('HOME_PAGE'))) as $f)
    {
        $page = gettext($f);
        $epage = urlencode($page);
        if (! $dbi->isWikiPage($page) ) {
            // translated version provided?
            if ($lf = FindLocalizedFile($pgsrc . $finder->_pathsep . $epage, 1)) {
                LoadAny($request, $lf);
            } else { // load english version of required action page
                LoadAny($request, FindFile(DEFAULT_WIKI_PGSRC . $finder->_pathsep . urlencode($f)));
                $page = $f;
            }
        }
        if (! $dbi->isWikiPage($page)) {
            trigger_error(sprintf("Mandatory file %s couldn't be loaded!", $page),
                          E_USER_WARNING);
        }
    }

    $pagename = _("InterWikiMap");
    $map = $dbi->getPage($pagename);
    $map->set('locked', true);
    PrintXML(HTML::p(HTML::em(WikiLink($pagename)), HTML::strong(" locked")));
    EndLoadDump($request);
}

function LoadPostFile (&$request)
{
    $upload = $request->getUploadedFile('file');

    if (!$upload)
        $request->finish(_("No uploaded file to upload?")); // FIXME: more concise message

    // Dump http headers.
    StartLoadDump($request, sprintf(_("Uploading %s"), $upload->getName()));

    $fd = $upload->open();
    if (IsZipFile($fd))
        LoadZip($request, $fd, false, array(_("RecentChanges")));
    else
        LoadFile($request, $upload->getName(), $upload->getContents());

    EndLoadDump($request);
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

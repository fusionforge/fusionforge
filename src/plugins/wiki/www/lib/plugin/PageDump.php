<?php // -*-php-*-
// rcs_id('$Id: PageDump.php 7780 2010-12-16 12:52:11Z vargenau $');
/*
 * Copyright (C) 2003 $ThePhpWikiProgrammingTeam
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
 * PhpWikiPlugin for PhpWiki developers to generate single page dumps
 * for checking into Subversion, or for users or the admin to produce a
 * downloadable page dump of a single page.
 *
 * This plugin will also be useful to (semi-)automatically sync pages
 * directly between two wikis. First the LoadFile function of
 * PhpWikiAdministration needs to be updated to handle URLs again, and
 * add loading capability from InterWiki addresses.
 *
 * Multiple revisions in one file handled by format=backup
 *
 * TODO: What about comments/summary field? quoted-printable?
 *
 * Usage:
 *  Direct URL access:
 *   http://...phpwiki/PageDump?page=HomePage?format=forsvn
 *   http://...phpwiki/index.php?PageDump&page=HomePage
 *   http://...phpwiki/index.php?PageDump&page=HomePage&download=1
 *  Static:
 *   <<PageDump page=HomePage>>
 *  Dynamic form (put both on the page):
 *   <<PageDump>>
 *   <?plugin-form PageDump?>
 *  Typical usage: as actionbar button
 */

class WikiPlugin_PageDump
extends WikiPlugin
{
    var $MessageId;

    function getName() {
        return _("PageDump");
    }
    function getDescription() {
        return _("View a single page dump online.");
    }

    function getDefaultArguments() {
        return array('s'    => false,
                     'page' => '[pagename]',
                     //'encoding' => 'binary', // 'binary', 'quoted-printable'
                     'format' => false, // 'normal', 'forsvn', 'forcvs', 'backup'
                     // display within WikiPage or give a downloadable
                     // raw pgsrc?
                     'download' => false);
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        // allow plugin-form
        if (!empty($s))
            $page = $s;
        if (!$page)
            return '';
        if (! $dbi->isWikiPage($page) )
            return fmt("Page %s not found.",
                       WikiLink($page, 'unknown'));

        // Check if user is allowed to get the Page.
        if (!mayAccessPage ('view', $page)) {
                return $this->error(sprintf(_("Illegal access to page %s: no read access"),
                                        $page));
        }

        $p = $dbi->getPage($page);
        include_once("lib/loadsave.php");
        $mailified = MailifyPage($p, ($format == 'backup') ? 99 : 1);

        // fixup_headers massages the page dump headers depending on
        // the 'format' argument, 'normal'(default) or 'forsvn'.
        //
        // Normal: Don't add X-Rcs-Id, add unique Message-Id, don't
        // strip any fields from Content-Type.
        //
        // ForCVS: Add empty X-Rcs-Id, strip attributes from
        // Content-Type field: "author", "version", "lastmodified",
        // "author_id", "hits".

        $this->pagename = $page;
        $this->generateMessageId($mailified);
        if (($format == 'forsvn') || ($format == 'forcvs'))
            $this->fixup_headers_forsvn($mailified);
        else // backup or normal
            $this->fixup_headers($mailified);

        if ($download) {
            // TODO: we need a way to hook into the generated headers, to override
            // Content-Type, Set-Cookie, Cache-control, ...
            $request->discardOutput(); // Hijack the http request from PhpWiki.
            ob_end_clean();            // clean up after hijacking $request
            //while (@ob_end_flush()); //debugging
            $filename = FilenameForPage($page);
            Header("Content-disposition: attachment; filename=\""
                   . $filename . "\"");
            // Read charset from generated page itself.
            // Inconsequential at the moment, since loadsave.php
            // always generates headers.
            $charset = $p->get('charset');
            if (!$charset) $charset = $GLOBALS['charset'];
            // We generate 3 Content-Type headers! first in loadsave,
            // then here and the mimified string $mailified also has it!
            // This one is correct and overwrites the others.
            Header("Content-Type: application/octet-stream; name=\""
                   . $filename . "\"; charset=\"" . $charset
                   . "\"");
            $request->checkValidators();
            // let $request provide last modified & etag
            Header("Content-Id: <" . $this->MessageId . ">");
            // be nice to http keepalive~s
            Header("Content-Length: " . strlen($mailified));

            // Here comes our prepared mime file
            echo $mailified;
            exit; // noreturn! php exits.
            return;
        }
        // We are displaing inline preview in a WikiPage, so wrap the
        // text if it is too long--unless quoted-printable (TODO).
        $mailified = wordwrap($mailified, 70);

        $dlsvn = Button(array(//'page' => $page,
                              'action' => $this->getName(),
                              'format'=> 'forsvn',
                              'download'=> true),
                        _("Download for Subversion"),
                        $page);
        $dl = Button(array(//'page' => $page,
                           'action' => $this->getName(),
                           'download'=> true),
                     _("Download for backup"),
                     $page);
        $dlall = Button(array(//'page' => $page,
                           'action' => $this->getName(),
                           'format'=> 'backup',
                           'download'=> true),
                     _("Download all revisions for backup"),
                     $page);

        $h2 = HTML::h2(fmt("Preview: Page dump of %s",
                           WikiLink($page, 'auto')));
        global $WikiTheme;
        if (!$Sep = $WikiTheme->getButtonSeparator())
            $Sep = " ";

        if ($format == 'forsvn') {
            $desc = _("(formatted for PhpWiki developers as pgsrc template, not for backing up)");
            $altpreviewbuttons = HTML(
                                      Button(array('action' => $this->getName()),
                                             _("Preview as normal format"),
                                             $page),
                                      $Sep,
                                      Button(array(
                                                   'action' => $this->getName(),
                                                   'format'=> 'backup'),
                                             _("Preview as backup format"),
                                             $page));
        }
        elseif ($format == 'backup') {
            $desc = _("(formatted for backing up: all revisions)"); // all revisions
            $altpreviewbuttons = HTML(
                                      Button(array('action' => $this->getName(),
                                                   'format'=> 'forsvn'),
                                             _("Preview as developer format"),
                                             $page),
                                      $Sep,
                                      Button(array(
                                                   'action' => $this->getName(),
                                                   'format'=> ''),
                                             _("Preview as normal format"),
                                             $page));
        } else {
            $desc = _("(normal formatting: latest revision only)");
            $altpreviewbuttons = HTML(
                                      Button(array('action' => $this->getName(),
                                                   'format'=> 'forsvn'),
                                             _("Preview as developer format"),
                                             $page),
                                      $Sep,
                                      Button(array(
                                                   'action' => $this->getName(),
                                                   'format'=> 'backup'),
                                             _("Preview as backup format"),
                                             $page));
        }
        $warning = HTML(
_("Please use one of the downloadable versions rather than copying and pasting from the above preview.")
. " " .
_("The wordwrap of the preview doesn't take nested markup or list indentation into consideration!")
. " ",
HTML::em(
_("PhpWiki developers should manually inspect the downloaded file for nested markup before rewrapping with emacs and checking into Subversion.")
         )
                        );

        return HTML($h2, HTML::em($desc),
                    HTML::pre($mailified),
                    $altpreviewbuttons,
                    HTML::div(array('class' => 'errors'),
                              HTML::strong(_("Warning:")),
                              " ", $warning),
                    $dl, $Sep, $dlall, $Sep, $dlsvn
                    );
    }

    // function handle_plugin_args_cruft(&$argstr, &$args) {
    // }

    function generateMessageId($mailified) {
        $array = explode("\n", $mailified);
        // Extract lastmodifed from mailified document for Content-Id
        // and/or Message-Id header, NOT from DB (page could have been
        // edited by someone else since we started).
        $m1 = preg_grep("/^\s+lastmodified\=(.*);/", $array);
        $m1 = array_values($m1); //reset resulting keys
        unset($array);
        $m2 = preg_split("/(^\s+lastmodified\=)|(;)/", $m1[0], 2,
                         PREG_SPLIT_NO_EMPTY);

        // insert message id into actual message when appropriate, NOT
        // into http header should be part of fixup_headers, in the
        // format:
        // <abbrphpwikiversion.mtimeepochTZ%InterWikiLinktothispage@hostname>
        // Hopefully this provides a unique enough identifier without
        // using md5. Even though this particular wiki may not
        // actually be part of InterWiki, including this info provides
        // the wiki name and name of the page which is being
        // represented as a text message.
        $this->MessageId = implode('', explode('.', PHPWIKI_VERSION))
            . "-" . $m2[0] . date("O")
            //. "-". rawurlencode(WIKI_NAME.":" . $request->getURLtoSelf())
            . "-". rawurlencode(WIKI_NAME.":" . $this->pagename)
            . "@". rawurlencode(SERVER_NAME);
    }

    function fixup_headers(&$mailified) {
        $return = explode("\n", $mailified);

        // Leave message intact for backing up, just add Message-Id header before transmitting.
        $item_to_insert = "Message-Id: <" . $this->MessageId .">";
        $insert_into_key_position = 2;
        $returnval_ignored = array_splice($return,
                                          $insert_into_key_position,
                                          0, $item_to_insert);

        $mailified = implode("\n", array_values($return));
    }

    function fixup_headers_forsvn(&$mailified) {
        $array = explode("\n", $mailified);

        // Massage headers to prepare for developer checkin to Subversion.
        $item_to_insert = "X-Rcs-Id: \$Id\$";
        $insert_into_key_position = 2;
        $returnval_ignored = array_splice($array,
                                          $insert_into_key_position,
                                          0, $item_to_insert);

        $item_to_insert = "  pgsrc_version=\"2 \$Revision\$\";";
        $insert_into_key_position = 5;
        $returnval_ignored = array_splice($array,
                                          $insert_into_key_position,
                                          0, $item_to_insert);
        /*
            Strip out all this junk:
            author=MeMe;
            version=74;
            lastmodified=1041561552;
            author_id=127.0.0.1;
            hits=146;
        */
        $killme = array("author", "version", "lastmodified",
                        "author_id", "hits", "owner", "acl");
        // UltraNasty, fixme:
        foreach ($killme as $pattern) {
            $array = preg_replace("/^\s\s$pattern\=.*;/",
                                  /*$replacement =*/"zzzjunk", $array);
        }
        // remove deleted values from array
        for ($i = 0; $i < count($array); $i++ ) {
            if(trim($array[$i]) != "zzzjunk") { //nasty, fixme
            //trigger_error("'$array[$i]'");//debugging
                $return[] = $array[$i];
            }
        }

        $mailified = implode("\n", $return);
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

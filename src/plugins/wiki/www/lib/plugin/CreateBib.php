<?php

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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * CreateBib:  Automatically create a BibTex file from page
 * Based on CreateTOC
 *
 * Usage:
 *  <<CreateBib pagename||=whatever >>
 *
 * @author:  Lea Viljanen
 */

class WikiPlugin_CreateBib
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Automatically create a Bibtex file from linked pages.");
    }

    function getDefaultArguments()
    {
        return array('pagename' => '[pagename]'); // The page from which the BibTex file is generated
    }

    /**
     * @param array $content
     * @return array
     */
    private function extractBibTeX($content)
    {
        $bib = array();

        $start = false;
        $stop = false;
        for ($i = 0; $i < count($content); $i++) {
            if (preg_match('/^@/', $content[$i], $match)) {
                $start = true;
            }
            else if (preg_match('/^\}/', $content[$i], $match)) {
                $stop = true;
            }
            if ($start) {
                $bib[] = $content[$i];
                if ($stop) $start = false;
            }
        }
        return $bib;
    }

    /**
     * Extract article links. Current markup is by * characters...
     * Assume straight list
     *
     * @param array $content
     * @return array
     */
    private function extractArticles($content)
    {
        $articles = array();
        for ($i = 0; $i < count($content); $i++) {
            // Should match "* [[WikiPageName]] whatever"
            if (preg_match('/^\s*\*\s+\[\[(.+)\]\]/', $content[$i], $match)) {
                $articles[] = $match[1];
            // Should match "* [WikiPageName] whatever"
            } elseif (preg_match('/^\s*\*\s+\[(.+)\]/', $content[$i], $match)) {
                $articles[] = $match[1];
            }
        }
        return $articles;
    }

    /**
     * @param WikiDB_Page $thispage
     * @param string $filename
     */
    private function dumpFile($thispage, $filename)
    {
        include_once 'lib/loadsave.php';
        $mailified = MailifyPage($thispage);

        $zip = new ZipArchive();
        $tmp_filename = "/tmp/" . $filename;
        if (file_exists($tmp_filename)) {
            unlink ($tmp_filename);
        }
        if ($zip->open($tmp_filename, ZipArchive::CREATE) !== true) {
            trigger_error(_("Cannot create ZIP archive"), E_USER_ERROR);
            return;
        }
        $zip->setArchiveComment(sprintf(_("Created by PhpWiki %s"), PHPWIKI_VERSION));
        $zip->addFromString(FilenameForPage($thispage->getName()), $mailified);
        $zip->close();
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.filesize($tmp_filename));
        readfile($tmp_filename);
        exit;
    }

    /**
     * @param WikiDB $dbi
     * @param string $argstr
     * @param WikiRequest $request
     * @param string $basepage
     * @return mixed
     */
    function run($dbi, $argstr, &$request, $basepage)
    {
        extract($this->getArgs($argstr, $request));
        if (!empty($pagename)) {
            // Expand relative page names.
            $page = new WikiPageName($pagename, $basepage);
            $pagename = $page->name;
        } else {
            return $this->error(sprintf(_("A required argument “%s” is missing."), 'pagename'));
        }

        // Get the links page contents
        $page = $dbi->getPage($pagename);
        $current = $page->getCurrentRevision();
        $content = $current->getContent();

        // Prepare the button to trigger dumping
        $dump_url = $request->getURLtoSelf(array("file" => $pagename.".bib"));
        global $WikiTheme;
        $dump_button = $WikiTheme->makeButton(_("Save to File"), $dump_url);

        $html = HTML::div(array('class' => 'bib align-left'));
        $html->pushContent($dump_button, ' ');
        $list = HTML::pre(array('id' => 'biblist', 'class' => 'bib'));

        // Let's find the subpages
        if ($articles = $this->extractArticles($content)) {
            foreach ($articles as $h) {

                // Now let's get the bibtex information from that subpage
                $subpage = $dbi->getPage($h);
                $subversion = $subpage->getCurrentRevision();
                $subcontent = $subversion->getContent();

                $bib = $this->extractBibTeX($subcontent);

                // ...and finally just push the bibtex data to page
                $foo = implode("\n", $bib);
                $bar = $foo . "\n\n";
                $list->pushContent(HTML::raw($bar));
            }
        }
        $html->pushContent($list);

        if ($request->getArg('file')) {
            // Yes, we want to dump this somewhere
            // Get the contents of this page
            $p = $dbi->getPage($pagename);
            $this->dumpFile($p, $request->getArg('file'));
            // No return
        }

        return $html;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:

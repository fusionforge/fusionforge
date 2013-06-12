<?php

/*
 * Copyright 2005 Wincor Nixdorf International GmbH
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
 * HtmlConverter:  Convert HTML tags as far to Wiki markup as possible
 *          and eliminate all other HTML markup, so the output can be
 *          copied and pasted into a wiki page.
 *          Credit to an unknown programmer, who has provided the first
 *          version 0.01 on http://www.gpgstudy.com/striphtml.phps
 * Usage:   <<HtmlConverter >>
 * Author:  HendrikScheider <hendrik.scheider@wincor-nixdorf.com>
 */

class WikiPlugin_HtmlConverter extends WikiPlugin
{
    function getDescription()
    {
        return _("Convert HTML markup into wiki markup.");
    }

    function getDefaultArguments()
    {
        return array();
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $form = HTML::form(array('action' => $request->getPostURL(),
            'enctype' => 'multipart/form-data',
            'method' => 'post'));
        $contents = HTML::div(array('class' => 'wikiaction'));
        $contents->pushContent(HTML::input(array('type' => 'hidden',
            'name' => 'MAX_FILE_SIZE',
            'value' => MAX_UPLOAD_SIZE)));
        $contents->pushContent(HTML::input(array('name' => 'userfile',
            'type' => 'file',
            'size' => '50')));
        $contents->pushContent(HTML::raw(" "));
        $contents->pushContent(HTML::input(array('value' => _("Convert"),
            'type' => 'submit')));
        $form->pushContent($contents);

        $message = HTML();
        $userfile = $request->getUploadedFile('userfile');
        if ($userfile) {
            $userfile_name = $userfile->getName();
            $userfile_name = basename($userfile_name);
            $userfile_tmpname = $userfile->getTmpName();

            if (!preg_match("/(\.html|\.htm)$/i", $userfile_name)) {
                $message->pushContent(_("Only files with extension HTML are allowed"), HTML::br(), HTML::br());
            } else {
                $message->pushContent(_("Processed $userfile_name"), HTML::br(), HTML::br());
                $message->pushContent(_("Copy the output below and paste it into your Wiki page."), HTML::br());
                $message->pushContent($this->process($userfile_tmpname));
            }
        } else {
            $message->pushContent(HTML::br(), HTML::br());
        }

        $result = HTML();
        $result->pushContent($form);
        $result->pushContent($message);
        return $result;
    }

    private function processA(&$file)
    {

        $file = eregi_replace(
            "<a([[:space:]]+)href([[:space:]]*)=([[:space:]]*)\"([-/.a-zA-Z0-9_~#@%$?&=:\200-\377\(\)[:space:]]+)\"([^>]*)>", "{{\\4}}", $file);

        $file = eregi_replace("{{([-/a-zA-Z0-9._~#@%$?&=:\200-\377\(\)[:space:]]+)}}([^<]+)</a>", "[ \\2 | \\1 ]", $file);
    }

    private function processIMG(&$file)
    {

        $img_regexp = "_<img\s+src\s*=\s*\"([-/.a-zA-Z0-9\_~#@%$?&=:\200-\377\(\)\s]+)\"[^>]*>_";

        $file = preg_replace($img_regexp, "\n\n[Upload:\\1]", $file);
    }

    private function processUL(&$file)
    {

        // put any <li>-Tag in a new line to indent correctly and strip trailing white space (including new-lines)
        $file = str_replace("<li", "\n<li", $file);
        $file = preg_replace("/<li>\s*/", "<li>", $file);

        $enclosing_regexp = "_(.*)<ul\s?[^>]*>((?U).*)</ul>(.*)_is";
        $indent_tag = "<li";
        $embedded_fragment_array = array();
        $found = preg_match($enclosing_regexp, $file, $embedded_fragment_array);
        while ($found) {
            $indented = str_replace($indent_tag, "\t" . $indent_tag, $embedded_fragment_array[2]);
            // string the file together again with the indented part in the middle.
            // a <p> is inserted instead of the erased <ul> tags to have a paragraph generated at the end of the script
            $file = $embedded_fragment_array[1] . "<p>" . $indented . $embedded_fragment_array[3];
            $found = preg_match($enclosing_regexp, $file, $embedded_fragment_array);
        }
    }

    private function process($file_name)
    {
        $result = HTML();
        $file = file_get_contents($file_name);
        $file = html_entity_decode($file);

        $this->processA($file);
        $this->processIMG($file);
        $this->processUL($file);

        $file = str_replace("\r\n", "\n", $file);

        $file = eregi_replace("<h1[[:space:]]?[^>]*>", "\n\n!!!!", $file);

        $file = eregi_replace("<h2[[:space:]]?[^>]*>", "\n\n!!!", $file);

        $file = eregi_replace("<h3[[:space:]]?[^>]*>", "\n\n!!", $file);

        $file = eregi_replace("<h4[[:space:]]?[^>]*>", "\n\n!", $file);

        $file = eregi_replace("<h5[[:space:]]?[^>]*>", "\n\n__", $file);

        $file = eregi_replace("</h1>", "\n\n", $file);

        $file = eregi_replace("</h2>", "\n\n", $file);

        $file = eregi_replace("</h3>", "\n\n", $file);

        $file = eregi_replace("</h4>", "\n\n", $file);

        $file = eregi_replace("</h5>", "__\n\n", $file);

        $file = eregi_replace("<hr[[:space:]]?[^>]*>", "\n----\n", $file);

        $file = eregi_replace("<li[[:space:]]?[^>]*>", "* ", $file);

        // strip all tags, except for <pre>, which is supported by wiki
        // and <p>'s which will be converted after compression.
        $file = strip_tags($file, "<pre><p>");
        // strip </p> end tags with trailing white space
        $file = preg_replace("_</p>\s*_i", "", $file);

        // get rid of all blank lines
        $file = preg_replace("/\n\s*\n/", "\n", $file);

        // finally only add paragraphs where defined by inserting double new-lines
        // be sure to only catch <p> or <p[space]...> and not <pre>!
        // Actually <p> tags with all white space and one new-line before
        // and after around are replaced
        $file = preg_replace("_\n?[^\S\n]*<p(\s[^>]*|)>[^\S\n]*\n?_i", "\n\n", $file);

        // strip attributes from <pre>-Tags and add a new-line before
        $file = preg_replace("_<pre(\s[^>]*|)>_iU", "\n<pre>", $file);

        $outputArea = HTML::textarea(array('rows' => '30', 'cols' => '80'));

        $outputArea->pushContent(_($file));
        $result->pushContent($outputArea);
        return $result;
    }
}

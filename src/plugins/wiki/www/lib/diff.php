<?php
/**
 * Copyright © 2000, 2001 Geoffrey T. Dairiki <dairiki@dairiki.org>
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
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

// diff.php
//
// PhpWiki diff output code.
//

require_once 'lib/difflib.php';

class HWLDF_WordAccumulator
{
    function __construct()
    {
        $this->lines = array();
        $this->line = false;
        $this->group = false;
        $this->tag = '~begin';
    }

    private function flushGroup($new_tag)
    {
        if ($this->group !== false) {
            if (!$this->line)
                $this->line = HTML();
            $this->line->pushContent($this->tag
                ? new HtmlElement($this->tag,
                    $this->group)
                : $this->group);
        }
        $this->group = '';
        $this->tag = $new_tag;
    }

    private function flushLine($new_tag)
    {
        $this->flushGroup($new_tag);
        if ($this->line)
            $this->lines[] = $this->line;
        $this->line = HTML();
    }

    public function addWords($words, $tag = '')
    {
        if ($tag != $this->tag)
            $this->flushGroup($tag);

        foreach ($words as $word) {
            // new-line should only come as first char of word.
            if ($word === "")
                continue;
            if ($word[0] == "\n") {
                $this->group .= " ";
                $this->flushLine($tag);
                $word = substr($word, 1);
            }
            assert(!strstr($word, "\n"));
            $this->group .= $word;
        }
    }

    public function getLines()
    {
        $this->flushLine('~done');
        return $this->lines;
    }
}

class WordLevelDiff extends MappedDiff
{
    function __construct($orig_lines, $final_lines)
    {
        list ($orig_words, $orig_stripped) = $this->split_lines($orig_lines);
        list ($final_words, $final_stripped) = $this->split_lines($final_lines);

        parent::__construct($orig_words, $final_words, $orig_stripped, $final_stripped);
    }

    private function split_lines($lines)
    {
        // FIXME: fix POSIX char class.
        if (!preg_match_all('/ ( [^\S\n]+ | [[:alnum:]]+ | . ) (?: (?!< \n) [^\S\n])? /xs',
            implode("\n", $lines),
            $m)
        ) {
            return array(array(''), array(''));
        }
        return array($m[0], $m[1]);
    }

    public function orig()
    {
        $orig = new HWLDF_WordAccumulator();

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $orig->addWords($edit->orig);
            elseif ($edit->orig)
                $orig->addWords($edit->orig, 'del');
        }
        return $orig->getLines();
    }

    public function finalize()
    {
        $final = new HWLDF_WordAccumulator();

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $final->addWords($edit->final);
            elseif ($edit->final)
                $final->addWords($edit->final, 'ins');
        }
        return $final->getLines();
    }
}

/**
 * HTML unified diff formatter.
 *
 * This class formats a diff into a CSS-based
 * unified diff format.
 *
 * Within groups of changed lines, diffs are highlit
 * at the character-diff level.
 */
class HtmlUnifiedDiffFormatter extends UnifiedDiffFormatter
{
    /**
     * @var HtmlElement $top
     */
    public $top;
    /**
     * @var HtmlElement $block
     */
    public $block;

    function __construct($context_lines = 4)
    {
        parent::__construct($context_lines);
    }

    protected function start_diff()
    {
        $this->top = HTML::div(array('class' => 'diff'));
    }

    protected function end_diff()
    {
        $val = $this->top;
        unset($this->top);
        return $val;
    }

    protected function start_block($header)
    {
        $this->block = HTML::div(array('class' => 'block'),
            HTML::samp($header));
    }

    protected function end_block()
    {
        $this->top->pushContent($this->block);
        unset($this->block);
    }

    protected function html_lines($lines, $class, $prefix = false, $elem = false)
    {
        if (!$prefix)
            $prefix = HTML::raw('&nbsp;');
        $div = HTML::div(array('class' => 'difftext'));
        foreach ($lines as $line) {
            if ($elem)
                $line = new HtmlElement($elem, $line);
            $div->pushContent(HTML::div(array('class' => $class),
                HTML::samp(array('class' => 'prefix'),
                    $prefix),
                $line, HTML::raw('&nbsp;')));
        }
        $this->block->pushContent($div);
    }

    protected function context($lines)
    {
        $this->html_lines($lines, 'context');
    }

    protected function deleted($lines)
    {
        $this->html_lines($lines, 'deleted', '-', 'del');
    }

    protected function added($lines)
    {
        $this->html_lines($lines, 'added', '+', 'ins');
    }

    protected function changed($orig, $final)
    {
        $diff = new WordLevelDiff($orig, $final);
        $this->html_lines($diff->orig(), 'original', '-');
        $this->html_lines($diff->finalize(), 'final', '+');
    }
}

/////////////////////////////////////////////////////////////////

/**
 * @param string $label
 * @param WikiDB_PageRevision $rev
 * @param WikiRequest $request
 * @param bool $is_current
 * @return $this|HtmlElement
 */
function PageInfoRow($label, $rev, &$request, $is_current = false)
{
    global $WikiTheme;

    $row = HTML::tr(HTML::td(array('class' => 'align-right'), $label));
    if ($rev) {
        $author = $rev->get('author');
        $dbi = $request->getDbh();

        $iswikipage = (isWikiWord($author) && $dbi->isWikiPage($author));
        $authorlink = $iswikipage ? WikiLink($author) : $author;
        $version = $rev->getVersion();
        $linked_version = WikiLink($rev, 'existing', $version);
        if ($is_current)
            $revertbutton = HTML();
        else
            $revertbutton = $WikiTheme->makeActionButton(array('action' => 'revert',
                    'version' => $version),
                false, $rev);
        $row->pushContent(HTML::td(fmt("version %s", $linked_version)),
            HTML::td($WikiTheme->getLastModifiedMessage($rev,
                false)),
            HTML::td(fmt("by %s", $authorlink)),
            HTML::td($revertbutton)
        );
    } else {
        $row->pushContent(HTML::td(array('colspan' => '4'), _("None")));
    }
    return $row;
}

/**
 * @param WikiRequest $request
 */
function showDiff(&$request)
{
    $pagename = $request->getArg('pagename');
    if (is_array($versions = $request->getArg('versions'))) {
        // Version selection from pageinfo.php display:
        rsort($versions);
        list ($version, $previous) = $versions;
    } else {
        $version = $request->getArg('version');
        $previous = $request->getArg('previous');
    }

    // abort if page doesn't exist
    $page = $request->getPage();
    $current = $page->getCurrentRevision(false);
    if ($current->getVersion() < 1) {
        $html = HTML::div(array('class' => 'wikitext', 'id' => 'difftext'),
            HTML::p(fmt("Page “%s” does not exist.", WikiLink($pagename, 'unknown'))));
        require_once 'lib/Template.php';
        GeneratePage($html, sprintf(_("Diff: %s"), $pagename), false);
        return; //early return
    }

    if ($version) {
        if (!($new = $page->getRevision($version)))
            NoSuchRevision($request, $page, $version);
        $new_version = fmt("version %d", $version);
    } else {
        $new = $current;
        $new_version = _("current version");
    }

    if (preg_match('/^\d+$/', $previous)) {
        if (!($old = $page->getRevision($previous)))
            NoSuchRevision($request, $page, $previous);
        $old_version = fmt("version %d", $previous);
        $others = array('major', 'minor', 'author');
    } else {
        switch ($previous) {
            case 'author':
                $old = $new;
                while ($old = $page->getRevisionBefore($old)) {
                    if ($old->get('author') != $new->get('author'))
                        break;
                }
                $old_version = _("revision by previous author");
                $others = array('major', 'minor');
                break;
            case 'minor':
                $old = $page->getRevisionBefore($new);
                $old_version = _("previous revision");
                $others = array('major', 'author');
                break;
            default:
                $old = $new;
                while ($old && $old->get('is_minor_edit'))
                    $old = $page->getRevisionBefore($old);
                if ($old)
                    $old = $page->getRevisionBefore($old);
                $old_version = _("predecessor to the previous major change");
                $others = array('minor', 'author');
                break;
        }
    }

    $new_link = WikiLink($new, '', $new_version);
    $old_link = $old ? WikiLink($old, '', $old_version) : $old_version;
    $page_link = WikiLink($page);

    $html = HTML::div(array('class' => 'wikitext', 'id' => 'difftext'),
        HTML::p(fmt("Differences between %s and %s of %s.",
            $new_link, $old_link, $page_link)));

    $otherdiffs = HTML::p(_("Other diffs:"));
    $label = array('major' => _("Previous Major Revision"),
        'minor' => _("Previous Revision"),
        'author' => _("Previous Author"));
    foreach ($others as $other) {
        $args = array('action' => 'diff', 'previous' => $other);
        if ($version)
            $args['version'] = $version;
        if (count($otherdiffs->getContent()) > 1)
            $otherdiffs->pushContent(", ");
        else
            $otherdiffs->pushContent(" ");
        $otherdiffs->pushContent(Button($args, $label[$other]));
    }
    $html->pushContent($otherdiffs);

    if ($old and $old->getVersion() == 0)
        $old = false;

    $html->pushContent(HTML::table(PageInfoRow(_("Newer page:"), $new,
            $request, empty($version)),
        PageInfoRow(_("Older page:"), $old,
            $request, false)));

    if ($new && $old) {
        $diff = new Diff($old->getContent(), $new->getContent());

        if ($diff->isEmpty()) {
            $html->pushContent(HTML::hr(),
                HTML::p(sprintf(_('Content of versions %1$s and %2$s is identical.'),
                    $old->getVersion(),
                    $new->getVersion())));
            // If two consecutive versions have the same content, it is because the page was
            // renamed, or metadata changed: ACL, owner, markup.
            // We give the reason by printing the summary.
            if (($new->getVersion() - $old->getVersion()) == 1) {
                $html->pushContent(HTML::p(sprintf(_('Version %1$s was created because: %2$s'),
                    $new->getVersion(),
                    $new->get('summary'))));
            }
        } else {
            $fmt = new HtmlUnifiedDiffFormatter();
            $html->pushContent($fmt->format($diff));
        }

        $html->pushContent(HTML::hr(), HTML::h2($new_version));
        require_once 'lib/BlockParser.php';
        $html->pushContent(TransformText($new, $pagename));
    }

    require_once 'lib/Template.php';
    GeneratePage($html, sprintf(_("Diff: %s"), $pagename), $new);
}

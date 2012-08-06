<?php // -*-php-*-
// $Id: diff.php 8085 2011-05-20 10:53:54Z vargenau $
// diff.php
//
// PhpWiki diff output code.
//
// Copyright (C) 2000, 2001 Geoffrey T. Dairiki <dairiki@dairiki.org>
// You may copy this code freely under the conditions of the GPL.
//

require_once('lib/difflib.php');

class _HWLDF_WordAccumulator {
    function _HWLDF_WordAccumulator () {
        $this->_lines = array();
        $this->_line = false;
        $this->_group = false;
        $this->_tag = '~begin';
    }

    function _flushGroup ($new_tag) {
        if ($this->_group !== false) {
            if (!$this->_line)
                $this->_line = HTML();
            $this->_line->pushContent($this->_tag
                                      ? new HtmlElement($this->_tag,
                                                        $this->_group)
                                      : $this->_group);
        }
        $this->_group = '';
        $this->_tag = $new_tag;
    }

    function _flushLine ($new_tag) {
        $this->_flushGroup($new_tag);
        if ($this->_line)
            $this->_lines[] = $this->_line;
        $this->_line = HTML();
    }

    function addWords ($words, $tag = '') {
        if ($tag != $this->_tag)
            $this->_flushGroup($tag);

        foreach ($words as $word) {
            // new-line should only come as first char of word.
            if ($word === "")
                continue;
            if ($word[0] == "\n") {
                $this->_group .= " ";
                $this->_flushLine($tag);
                $word = substr($word, 1);
            }
            assert(!strstr($word, "\n"));
            $this->_group .= $word;
        }
    }

    function getLines() {
        $this->_flushLine('~done');
        return $this->_lines;
    }
}

class WordLevelDiff extends MappedDiff
{
    function WordLevelDiff ($orig_lines, $final_lines) {
        list ($orig_words, $orig_stripped) = $this->_split($orig_lines);
        list ($final_words, $final_stripped) = $this->_split($final_lines);


        $this->MappedDiff($orig_words, $final_words,
                          $orig_stripped, $final_stripped);
    }

    function _split($lines) {
        // FIXME: fix POSIX char class.
        if (!preg_match_all('/ ( [^\S\n]+ | [[:alnum:]]+ | . ) (?: (?!< \n) [^\S\n])? /xs',
                            implode("\n", $lines),
                            $m)) {
            return array(array(''), array(''));
        }
        return array($m[0], $m[1]);
    }

    function orig () {
        $orig = new _HWLDF_WordAccumulator;

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $orig->addWords($edit->orig);
            elseif ($edit->orig)
                $orig->addWords($edit->orig, 'del');
        }
        return $orig->getLines();
    }

    function _final () {
        $final = new _HWLDF_WordAccumulator;

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
    function HtmlUnifiedDiffFormatter($context_lines = 4) {
        $this->UnifiedDiffFormatter($context_lines);
    }

    function _start_diff() {
        $this->_top = HTML::div(array('class' => 'diff'));
    }
    function _end_diff() {
        $val = $this->_top;
        unset($this->_top);
        return $val;
    }

    function _start_block($header) {
        $this->_block = HTML::div(array('class' => 'block'),
                                  HTML::tt($header));
    }

    function _end_block() {
        $this->_top->pushContent($this->_block);
        unset($this->_block);
    }

    function _lines($lines, $class, $prefix = false, $elem = false) {
        if (!$prefix)
            $prefix = HTML::raw('&nbsp;');
        $div = HTML::div(array('class' => 'difftext'));
        foreach ($lines as $line) {
            if ($elem)
                $line = new HtmlElement($elem, $line);
            $div->pushContent(HTML::div(array('class' => $class),
                                        HTML::tt(array('class' => 'prefix'),
                                                 $prefix),
                                        $line, HTML::raw('&nbsp;')));
        }
        $this->_block->pushContent($div);
    }

    function _context($lines) {
        $this->_lines($lines, 'context');
    }
    function _deleted($lines) {
        $this->_lines($lines, 'deleted', '-', 'del');
    }

    function _added($lines) {
        $this->_lines($lines, 'added', '+', 'ins');
    }

    function _changed($orig, $final) {
        $diff = new WordLevelDiff($orig, $final);
        $this->_lines($diff->orig(), 'original', '-');
        $this->_lines($diff->_final(), 'final', '+');
    }
}

/////////////////////////////////////////////////////////////////

function PageInfoRow ($label, $rev, &$request, $is_current = false)
{
    global $WikiTheme;

    $row = HTML::tr(HTML::td(array('align' => 'right'), $label));
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

function showDiff (&$request) {
    $pagename = $request->getArg('pagename');
    if (is_array($versions = $request->getArg('versions'))) {
        // Version selection from pageinfo.php display:
        rsort($versions);
        list ($version, $previous) = $versions;
    }
    else {
        $version = $request->getArg('version');
        $previous = $request->getArg('previous');
    }

    // abort if page doesn't exist
    $dbi = $request->getDbh();
    $page = $request->getPage();
    $current = $page->getCurrentRevision(false);
    if ($current->getVersion() < 1) {
        $html = HTML::div(array('class'=>'wikitext','id'=>'difftext'),
                          HTML::p(fmt("I'm sorry, there is no such page as %s.",
                                      WikiLink($pagename, 'unknown'))));
        require_once('lib/Template.php');
        GeneratePage($html, sprintf(_("Diff: %s"), $pagename), false);
        return; //early return
    }

    if ($version) {
        if (!($new = $page->getRevision($version)))
            NoSuchRevision($request, $page, $version);
        $new_version = fmt("version %d", $version);
    }
    else {
        $new = $current;
        $new_version = _("current version");
    }

    if (preg_match('/^\d+$/', $previous)) {
        if ( !($old = $page->getRevision($previous)) )
            NoSuchRevision($request, $page, $previous);
        $old_version = fmt("version %d", $previous);
        $others = array('major', 'minor', 'author');
    }
    else {
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
            $previous='minor';
            $old = $page->getRevisionBefore($new);
            $old_version = _("previous revision");
            $others = array('major', 'author');
            break;
        case 'major':
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

    $html = HTML::div(array('class'=>'wikitext','id'=>'difftext'),
                     HTML::p(fmt("Differences between %s and %s of %s.",
                                 $new_link, $old_link, $page_link)));

    $otherdiffs = HTML::p(_("Other diffs:"));
    $label = array('major' => _("Previous Major Revision"),
                   'minor' => _("Previous Revision"),
                   'author'=> _("Previous Author"));
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

    $html->pushContent(HTML::Table(PageInfoRow(_("Newer page:"), $new,
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
            $fmt = new HtmlUnifiedDiffFormatter;
            $html->pushContent($fmt->format($diff));
        }

        $html->pushContent(HTML::hr(), HTML::h2($new_version));
        require_once("lib/BlockParser.php");
        $html->pushContent(TransformText($new,$new->get('markup'),$pagename));
    }

    require_once('lib/Template.php');
    GeneratePage($html, sprintf(_("Diff: %s"), $pagename), $new);
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

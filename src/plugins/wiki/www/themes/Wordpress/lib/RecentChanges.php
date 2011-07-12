<?php // $Id: RecentChanges.php 7956 2011-03-03 17:08:31Z vargenau $
/*
 * Extensions/modifications to the stock RecentChanges (and PageHistory) format.
 */


require_once('lib/plugin/RecentChanges.php');
require_once('lib/plugin/PageHistory.php');

function Wordpress_RC_revision_formatter (&$fmt, &$rev) {
        $class = 'rc-' . $fmt->importance($rev);
        $time = $fmt->time($rev);
        if ($rev->get('is_minor_edit')) {
            $minor_flag = HTML::small("(" . _("minor edit") . ")");
        }
        else {
            $time = HTML::strong($time);
            $minor_flag = '';
        }

        return HTML::li(array('class' => $class),
                        $fmt->diffLink($rev), ' ',
                        $fmt->pageLink($rev), ' ',
                        $time, ' ',
                        $minor_flag, ' ',
                        " . . . ", $fmt->summaryAsHTML($rev), ' ',
                        " . . . ",
                        $fmt->authorLink($rev)
                       );
    }


class _Wordpress_RecentChanges_Formatter
extends _RecentChanges_HtmlFormatter
{
    function format_revision (&$rev) {
        return Wordpress_RC_revision_formatter($this, $rev);
    }
    function summaryAsHTML ($rev) {
        if ( !($summary = $this->summary($rev)) )
            return '';
        return  HTML::strong( array('class' => 'wiki-summary'),
                              "(",
                              TransformLinks($summary, $rev->get('markup'), $rev->getPageName()),
                              ")");
    }
}

class _Wordpress_PageHistory_Formatter
extends _PageHistory_HtmlFormatter
{
    function format_revision (&$rev) {
        return Wordpress_RC_revision_formatter($this, $rev);
    }
    function summaryAsHTML ($rev) {
        if ( !($summary = $this->summary($rev)) )
            return '';
        return  HTML::strong( array('class' => 'wiki-summary'),
                              "(",
                              TransformLinks($summary, $rev->get('markup'), $rev->getPageName()),
                              ")");
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

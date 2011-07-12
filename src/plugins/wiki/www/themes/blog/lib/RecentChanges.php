<?php // $Id: RecentChanges.php 7956 2011-03-03 17:08:31Z vargenau $
/*
 * Extensions/modifications to the stock RecentChanges (and PageHistory) format.
 */

require_once('lib/plugin/RecentChanges.php');
//require_once('lib/plugin/PageHistory.php');

class _blog_RecentChanges_BoxFormatter
extends _RecentChanges_BoxFormatter
{
    function pageLink (&$rev, $link_text=false) {
        if (!$link_text and $rev->get('pagetype') == 'wikiblog')
            $link_text = $rev->get('summary');
        elseif (preg_match("/\/Blog\b/", $rev->_pagename))
            return '';
        if ($link_text and strlen($link_text) > 20)
            $link_text = substr($link_text,0,20)."...";
        return WikiLink($rev->getPage(),'auto',$link_text);
    }
}

class _blog_RecentChanges_Formatter
extends _RecentChanges_HtmlFormatter
{
    function pageLink (&$rev, $link_text=false) {
        if (!$link_text and $rev->get('pagetype') == 'wikiblog')
            $link_text = $rev->get('summary');
        return WikiLink($rev,'auto',$link_text);
    }
}
/*
class _blog_PageHistory_Formatter
extends _PageHistory_HtmlFormatter
{
    function pageLink (&$rev, $link_text=false) {
        if (!$link_text and $rev->get('pagetype') == 'wikiblog')
            $link_text = $rev->get('summary');
        return WikiLink($rev,'auto',$link_text);
    }
}
*/

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

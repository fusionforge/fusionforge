<?php // -*-php-*-
// $Id: RecentReferrers.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Analyze our ACCESS_LOG
 * Check HTTP_REFERER
 *
 */
include_once("lib/PageList.php");

class WikiPlugin_RecentReferrers extends WikiPlugin
{
    function getName () {
        return _("RecentReferrers");
    }

    function getDescription () {
        return _("Analyse access log.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array(
                   'limit'            => 15,
                   'noheader'      => false,
                   ));
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if (!ACCESS_LOG) {
            return HTML::div(array('class' => "error"), "Error: no ACCESS_LOG");
        }
        $args = $this->getArgs($argstr, $request);
        $table = HTML::table(array('cellpadding' => 1,
                                   'cellspacing' => 2,
                                   'border'      => 0,
                                   'class'       => 'pagelist'));
        if (!$args['noheader'] and !empty($args['caption']))
            $table->pushContent(HTML::caption(array('align'=>'top'), $args['caption']));
        $logs = array();
        $limit = $args['limit'];
        $accesslog =& $request->_accesslog;
        if ($logiter = $accesslog->get_referer($limit, "external_only")
            and $logiter->count()) {
            $table->pushContent(HTML::tr(HTML::th("Target"),HTML::th("Referrer"),
                                         HTML::th("Host"),HTML::th("Date")));
            while($logentry = $logiter->next()) {
                $table->pushContent(HTML::tr(HTML::td($logentry['request']),
                                             HTML::td($logentry['referer']),
                                             HTML::td($logentry['host']),
                                             HTML::td($logentry['time'])
                                             ));
            }
            return $table;
        }
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

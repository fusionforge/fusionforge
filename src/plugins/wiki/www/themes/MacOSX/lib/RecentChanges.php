<?php
/**
 * Copyright © 2002 Jeff Dairiki
 * Copyright © 2002 Carsten Klapp
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

/*
 * Extensions/modifications to the stock RecentChanges (and PageHistory) format.
 */

require_once 'lib/plugin/RecentChanges.php';
require_once 'lib/plugin/PageHistory.php';

function MacOSX_RC_revision_formatter(&$fmt, &$rev)
{
    $class = 'rc-' . $fmt->importance($rev);

    return HTML::li(array('class' => $class),
        $fmt->diffLink($rev), ' ',
        $fmt->pageLink($rev), ' ',
        $rev->get('is_minor_edit') ? $fmt->time($rev) : HTML::strong($fmt->time($rev)),
        ' . . . ',
        $fmt->summaryAsHTML($rev),
        ' -- ',
        $fmt->authorLink($rev));
}

function MacOSX_PH_revision_formatter(&$fmt, &$rev)
{
    $class = 'rc-' . $fmt->importance($rev);

    return HTML::li(array('class' => $class),
        $fmt->diffLink($rev), ' ',
        $fmt->pageLink($rev), ' ',
        $rev->get('is_minor_edit') ? $fmt->time($rev) : HTML::strong($fmt->time($rev)),
        ' . . . ',
        $fmt->summaryAsHTML($rev),
        ' -- ',
        $fmt->authorLink($rev),
        $rev->get('is_minor_edit') ? HTML::em(" (" . _("minor edit") . ")") : '');
}

class _MacOSX_RecentChanges_Formatter
    extends _RecentChanges_HtmlFormatter
{
    function format_revision($rev)
    {
        return MacOSX_RC_revision_formatter($this, $rev);
    }
}

class _MacOSX_PageHistory_Formatter
    extends _PageHistory_HtmlFormatter
{
    function format_revision($rev)
    {
        return MacOSX_PH_revision_formatter($this, $rev);
    }
}

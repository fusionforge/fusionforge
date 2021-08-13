<?php
/**
 * Copyright © 2009 Reini Urban
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

/* Download hyperwiki.jar and GraphXML.dtd from
 *   http://hypergraph.sourceforge.net/download.html
 *   and place it into your theme directory.
 * Include this php file and adjust the width/height.
 */
global $WikiTheme;
// via the RPC interface it goes like this...
?>
<applet code="hypergraph.applications.hwiki.HWikiApplet.class"
        archive="<?php echo $WikiTheme->_findData("hyperwiki.jar") ?>"
        width="162" height="240">
    <param name="startPage" value="<?php echo $page->getName() ?>"/>
    <param name="properties" value="<?php echo $WikiTheme->_findData("hwiki.prop") ?>"/>
    <param name="wikiURL" value="<?php echo PHPWIKI_BASE_URL ?>"/>
</applet>

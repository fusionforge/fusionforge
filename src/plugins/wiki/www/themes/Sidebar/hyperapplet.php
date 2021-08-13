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

/* Download hyperapplet.jar (or hyperwiki.jar) and GraphXML.dtd from
 *   http://hypergraph.sourceforge.net/download.html
 *   and place it into your theme directory.
 * Include this php file and adjust the width/height.
 * The static version requires a dumped "LinkDatabase.xml" via
 *   cd themes/default; wget http://localhost/wiki/index.php/LinkDatabase?format=xml -O LinkDatabase.xml
 * into the same dir as hyperapplet.jar
 */
global $WikiTheme;
?>
<applet code="hypergraph.applications.hexplorer.HExplorerApplet.class" align="baseline"
        archive="<?php echo $WikiTheme->_findData("hyperapplet.jar") ?>"
        width="160" height="360">
    <?php // the dynamic version: ?>
    <!--param name="file" value="<?php echo WikiURL("LinkDatabase", array('format' => 'xml')) ?>" /-->
    <?php // The faster static version: dump it periodically ?>
    <param name="file" value="<?php echo $WikiTheme->_findData("LinkDatabase.xml") ?>"/>
    <!--param name="properties" value="<?php echo $WikiTheme->_findData("hwiki.prop") ?>" /-->
    <param name="center" value="<?php echo $page->getName() ?>"/>
</applet>

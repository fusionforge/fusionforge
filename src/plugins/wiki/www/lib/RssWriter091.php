<?php
/**
 * Copyright © 2002 Lawrence Akka
 * Copyright © 2002-2003 Jeff Dairiki
 * Copyright © 2004 Reini Urban
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

// ----------------------------------------------------------------------
// Original Author of file: Lawrence Akka
// Purpose of file: Plugin and associated classes
// for outputting RecentChanges in RSS 0.91 format
// ----------------------------------------------------------------------

include_once 'lib/RssWriter.php';
class RssWriter091 extends RssWriter
{
    function __construct()
    {
        $this->XmlElement('rss', array('version' => "0.91"));
        $this->_items = array();
    }

    /**
     * Finish construction of RSS.
     */
    function finish()
    {
        if (isset($this->_finished))
            return;

        $channel = &$this->_channel;
        $items = &$this->_items;

        if ($items) {
            foreach ($items as $i)
                $channel->pushContent($i);
        }
        $this->pushContent($channel);
        $this->__spew();
        $this->_finished = true;
    }

    /**
     * Create a new RDF <em>typedNode</em>.
     */
    function __node($type, $properties, $uri = false)
    {
        return new XmlElement($type, '',
            $this->__elementize($properties));
    }

    /**
     * Write output to HTTP client.
     */
    function __spew()
    {
        header("Content-Type: application/xml; charset=UTF-8");
        print("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");
        print("<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\"\n");
        print("\"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n\n");
        $this->printXML();
    }

}

class _RecentChanges_RssFormatter091
    extends _RecentChanges_RssFormatter
// This class should probably go at then of RecentChanges.php
{
    function format($changes)
    {
        //    include_once('lib/RssWriter.php');
        $rss = new RssWriter091();

        $rss->channel($this->channel_properties());

        if (($props = $this->image_properties()))
            $rss->image($props);
        if (($props = $this->textinput_properties()))
            $rss->textinput($props);

        while ($rev = $changes->next()) {
            $rss->addItem($this->item_properties($rev),
                $this->pageURI($rev));
        }

        global $request;
        $request->discardOutput();
        $rss->finish();
        $request->finish(); // NORETURN!!!!
    }

    function channel_properties()
    {
        global $request;

        $rc_url = WikiURL($request->getArg('pagename'), array(), 'absurl');

        return array('title' => WIKI_NAME,
            'description' => _("RecentChanges"),
            'link' => $rc_url,
            'language' => 'en-US');

        /* FIXME: language should come from $LANG (or other config variable). */

        /* FIXME: other things one might like in <channel>:
         * managingEditor
         * webmaster
         * lastBuildDate
         * copyright
         */
    }

    function item_properties($rev)
    {
        $page = $rev->getPage();
        $pagename = $page->getName();

        return array('title' => SplitPagename($pagename),
            'description' => $this->summary($rev),
            'link' => $this->pageURL($rev)
        );
    }
}

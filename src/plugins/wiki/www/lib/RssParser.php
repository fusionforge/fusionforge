<?php // -*-php-*-
// rcs_id('$Id: RssParser.php 7641 2010-08-11 13:00:46Z vargenau $');
/**
 * Simple RSSParser Class
 * Based on Duncan Gough RSSParser class
 * Copyleft Arnaud Fontaine
 * Licence : GPL
 * See lib/plugin/RssFeed.php and lib/XmlParser.php
 *
 * The myth of RSS compatibility:
 *   http://diveintomark.org/archives/2004/02/04/incompatible-rss
 */

/*
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * 2004-04-09 16:30:50 rurban:
 *   added fsockopen allow_url_fopen = Off workaround
 * 2004-04-12 20:04:12 rurban:
 *   fixes for IMAGE element (sf.net)
 * 2005-04-10 11:17:35 rurban
 *   certain RSS dont contain <item> tags to describe the list of <items>
 *     http://ws.audioscrobbler.com/rdf/ for example
 */

require_once('lib/XmlParser.php');

class RSSParser
extends XmlParser {

    var $title = "";
    var $author = "";
    var $pubDate = "";
    var $link  = "";
    var $description = "";
    var $inside_item = false;
    var $list_items = false;
    var $item  = array();
    var $items;
    var $channel;
    var $divers = "";
    var $date = "";

    function tag_open($parser, $name, $attrs=''){
        global $current_tag, $current_attrs;

        $current_tag = $name;
        $current_attrs = $attrs;
        if ($name == "ITEM")
            $this->inside_item = true;
        elseif ($name == "ITEMS")
            $this->list_items = true;
        elseif ($name == "IMAGE")
            $this->inside_item = true;
    }

    function tag_close($parser, $tagName, $attrs=''){
        global $current_tag;

        if ($tagName == "ITEM") {
            if (empty($this->items)) {
                $this->items = array();
                $GLOBALS['rss_parser_items'] =& $this->items;
            } elseif (!empty($this->items[0]['link']) and $this->items[0]['title'] == '') {
            	// override the initial <items> list with detailed <item>'s
                $this->items = array();
                $GLOBALS['rss_parser_items'] =& $this->items;
            }
            $this->items[] = array("title"       => $this->item['TITLE'],
                                   "author"      => $this->item['AUTHOR'],
                                   "pubDate"     => $this->item['PUBDATE'],
                                   "description" => @$this->item['DESCRIPTION'],
                                   "link"        => $this->item['LINK']);
            $this->item = array("TITLE"       => "",
                                "DESCRIPTION" => "",
                                "LINK"        => "");
            $this->inside_item = false;
        } elseif ($tagName == "IMAGE") {
            $this->item = array("TITLE"       => "",
                                "DESCRIPTION" => "",
                                "LINK"        => "");
            $this->inside_item = false;
        } elseif ($tagName == "CHANNEL") {
            $this->channel = array("title" => $this->title,
                                   "description" => $this->description,
                                   "link" => $this->link,
                                   "date" => $this->date,
                                   "divers" => $this->divers);
            $GLOBALS['rss_parser_channel'] =& $this->channel;
            $this->title       = "";
            $this->description = "";
            $this->link        = "";
            $this->divers      = "";
            $this->date        = "";
        } elseif ($tagName == "ITEMS") {
            $GLOBALS['rss_parser_items'] =& $this->items;
            $this->item = array("TITLE"       => "",
                                "DESCRIPTION" => "",
                                "LINK"        => "");
            $this->list_items = false;
        }
    }

    function cdata($parser, $data){
        global $current_tag, $current_attrs;

        if ($this->inside_item) {
            if (empty($this->item[$current_tag]))
                $this->item[$current_tag] = '';
            if ($current_tag == 'LINK') {
            	if (trim($data))
            	    $this->item[$current_tag] = trim($data);
            } else {
                $this->item[$current_tag] .= trim($data);
            }
        } elseif ($this->list_items) {
            if ($current_tag == 'RDF:LI') {
            	// FIXME: avoid duplicates. cdata called back 4x per RDF:LI
            	if ($this->items[count($this->items)-1]['link'] != @$current_attrs['RDF:RESOURCE'])
                    $this->items[] = array('link' => @$current_attrs['RDF:RESOURCE'],
                                           'title' => '');
            }
        } else {
            switch ($current_tag) {
            case "TITLE":
                if (trim($data))
                    $this->title .= " " . trim($data);
                break;
            case "DESCRIPTION":
                if (trim($data))
                    $this->description .= trim($data);
                break;
            case "LINK":
                if (trim($data))
                    $this->link = trim($data);
                break;
            case "DC:DATE":
                if (trim($data))
                    $this->date .= " " . trim($data);
            default:
                if (trim($data))
                    $this->divers .= " " . $current_tag."/".$data;
                break;
            }
        }
    }

    function parse($content, $is_final = true) {
        xml_parse($this->_parser, $content, $is_final) or
            trigger_error(sprintf("XML error: %s at line %d",
                                  xml_error_string(xml_get_error_code($this->_parser)),
                                  xml_get_current_line_number($this->_parser)),
                          E_USER_WARNING);
        //OO workaround: parser object looses its params. we have to store them in globals
        if ($is_final) {
    	    if (empty($this->items)) {
                $this->items   = @$GLOBALS['rss_parser_items'];
                $this->channel = @$GLOBALS['rss_parser_channel'];
    	    }
    	    unset($GLOBALS['rss_parser_items']);
    	    unset($GLOBALS['rss_parser_channel']);
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

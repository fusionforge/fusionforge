<?php // -*-php-*-
// $Id: AtomParser.php 7509 2010-06-09 11:35:44Z rurban $
/*
 * Copyright 2010 Sébastien Le Callonnec
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/**
 * This class is a poor-man Atom parser, it does no validation of the feed.
 * The content of an entry ("payload") is not parsed but rather returned "as-is",
 * as its format can be text, html or xhtml.
 * 
 * @author: Sébastien Le Callonnec
 */
require_once('lib/XmlParser.php');

class AtomParser
extends XmlParser
{
    // Feed
    var $feed = array();
    var $feed_title = '';
    var $feed_links = array();
    var $feed_subtitle = '';
    var $feed_id = '';
    var $feed_updated = '';
    var $feed_authors = array();
    var $feed_contributors = array();
    var $generator = '';
    var $icon = '';
    var $rights = '';
    var $logo = '';
    
    var $categories = array();

    var $authors = array();
    var $contributors = array();

    // Author, Contributor
    var $name = '';
    var $email = '';
    var $uri = '';

    // Entries
    var $entries = array();
    var $inside_entry = false;
    var $title = '';
    var $updated = '';
    var $published = '';
    var $id = '';
    var $links = array();
    var $summary = '';
    
    var $inside_content = false;
    var $content = '';

    function tag_open($parser, $name, $attrs='') {
        global $current_tag, $current_attrs;

        $current_tag = $name;
        $current_attrs = $attrs;

        if ($name == "ENTRY") {
            $this->inside_entry = true;
        } elseif ($this->inside_content) {
            $this->content .= $this->serialize_tag(strtolower($name), $attrs);
        } elseif ($name == "CONTENT") {
            $this->inside_content = true;
        }
    }

    function tag_close($parser, $name, $attrs='') {
        if ($name == "AUTHOR") {
            $an_author = $this->trim_data(array(
                "name" => $this->name,
                "email" => $this->email,
                "uri" => $this->uri
            ));
            if ($this->inside_entry) {
                $this->authors[] = $an_author;
            } else {
                $this->feed_authors[] = $an_author;
            }
            $this->name = '';
            $this->email = '';
            $this->uri = '';
        } elseif ($name == "FEED") {
            $this->feed[] = $this->trim_data(array(
                "id" => $this->feed_id,
                "title" => $this->feed_title,
                "links" => $this->feed_links,
                "subtitle" => $this->feed_subtitle,
                "updated" => $this->feed_updated,
                "generator" => $this->generator,
                "icon" => $this->icon,
                "rights" => $this->rights,
                "logo" => $this->logo,
                "authors" => $this->feed_authors,
                "contributors" => $this->feed_contributors
            ));
            $this->feed_title = '';
            $this->feed_id = '';
            $this->feed_links = array();
            $this->feed_subtitle = '';
            $this->feed_updated = '';
            $this->feed_authors = array();
            $this->feed_contributors = array();
            $this->generator = '';
            $this->icon = '';
            $this->rights = '';
            $this->logo = '';
        } elseif ($name == "ENTRY") {
            $this->entries[] = $this->trim_data(array(
                "id" => $this->id,
                "title" => $this->title,
                "updated" => $this->updated,
                "links" => $this->links,
                "published" => $this->published,
                "content" => $this->content,
                "summary" => $this->summary,
                "authors" => $this->authors,
                "contributors" => $this->contributors
            ));
            $this->id = '';
            $this->title = '';
            $this->updated = '';
            $this->links = '';
            $this->published = '';
            $this->content = '';
            $this->authors = array();
            $this->contributors = array();
            $this->inside_entry = false;
        } elseif ($name == "CONTENT") {
            $this->inside_content = false;
        } elseif ($name == "CONTRIBUTOR") {
            $a_contributor = $this->trim_data(array(
                "name" => $this->name,
                "email" => $this->email
            ));
            if ($this->inside_entry) {
                $this->contributors[] = $a_contributor;
            } else {
                $this->feed_contributors[] = $a_contributor;
            }
            $this->name = '';
            $this->email = '';
        } elseif ($this->inside_content) {
            $this->content .= "</" . strtolower($name) . ">";
        }
    }

    function cdata($parser, $data) {
        global $current_tag, $current_attrs;
        
        if ($this->inside_content) {
            $this->content .= $data;
        } else {
            switch ($current_tag) {
                case "ID":
                    if ($this->inside_entry)
                        $this->id .= $data;
                    else
                        $this->feed_id .= $data;
                    break;
                case "LINK":
                    $a_link = array();
                    foreach ($current_attrs as $k => $v) {
                        $a_link[strtolower($k)] = $v;
                    }
                    if ($this->inside_entry) {
                        $this->links[] = $a_link;
                    } else {
                        $this->feed_links[] = $a_link;
                    }
                    break;
                case "NAME":
                    $this->name .= $data;
                    break;
                case "EMAIL":
                    $this->email .= $data;
                    break;
                case "TITLE" :
                    if ($this->inside_entry)
                        $this->title .= $data;
                    else
                        $this->feed_title .= $data;
                    break;
                case "UPDATED":
                    if ($this->inside_entry)
                        $this->updated .= $data;
                    else
                        $this->feed_updated .= $data;
                    break;
                case "SUBTITLE":
                    $this->feed_subtitle .= $data;
                    break;
                case "PUBLISHED":
                    $this->published .= $data;
                    break;
                case "SUMMARY":
                    $this->summary .= $data;
                    break;
                case "URI":
                    $this->uri .= $data;
                    break;
                case "GENERATOR":
                    $this->generator .= $data;
                    break;
                case "ICON":
                    $this->icon .= $data;
                    break;
                case "LOGO":
                    $this->logo .= $data;
                    break;
                case "RIGHTS":
                    $this->rights .= $data;
                    break;
            }
        }
    }

    function trim_data($array) {
        return array_map(array("self", "trim_element"), $array);
    }

    function trim_element($element) {
        if (is_array($element)) {
            return $this->trim_data($element);
        } elseif (is_string($element)) {
            return trim($element);
        }
    }
    
    function serialize_tag($tag_name, $attributes) {
        $tag = "<" . $tag_name;
        foreach ($attributes as $k => $v) {
            $tag .= " " . strtolower($k). "=\"$v\"";
        }
        $tag .= ">";
        return $tag;
    }
}
?>

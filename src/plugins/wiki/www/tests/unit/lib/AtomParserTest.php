<?php
// $Id: AtomParserTest.php 7837 2011-01-14 11:12:00Z vargenau $
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
/**
 * @author: Sébastien Le Callonnec
 */

require_once('lib/AtomParser.php');
require_once('PHPUnit/Autoload.php');

class AtomParserTest
extends phpwiki_TestCase
{
    function testSimpleAtomFileParsing() {
        $fake_atom_file = <<<ATOM
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:georss="http://www.georss.org/georss" >
    <link href="http://www.phpwiki.org/fakeurl" rel="self" type="application/atom+xml" />
    <title>This is a fake feed</title>
    <updated>2010-05-15T01:00:00Z</updated>
    <id>http://www.phpwiki.org/fakeurl</id>
    <subtitle>Cool feed</subtitle>
    <author>
        <name>Sébastien Le Callonnec</name>
        <email>slc_ie@yahoo.ie</email>
    </author>

    <entry>
      <title>Foobar Éire</title>
      <link href="http://maps.google.com/maps?f=q&amp;sll=53.125728,-6.068907&amp;ie=UTF8"/>
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">Millenium Spire, Dublin
          <div class="geo">Geo coordinates: 
            <abbr class="latitude" title="53.349441">53.349441</abbr>
            <abbr class="longitude" title="-6.260282">-6.260282</abbr>
          </div>
        </div>
      </content>
      <updated>2010-05-15T01:00:00Z</updated>
      <published>2010-05-15T01:00:00Z</published>
      <georss:box>53.349441 -6.26234 53.35078 -6.260282</georss:box>
      <id>tag:www.phpwiki.org,2010-05-15:/fakeurl/20100515223621</id>
    </entry>

</feed>
ATOM;
        $parser = new AtomParser();
        $parser->parse($fake_atom_file);

        $this->assertFalse(count($parser->feed) == 0);
        
        $current_feed = $parser->feed[0];
        $this->assertEquals("This is a fake feed", $current_feed["title"]);
        
        $this->assertFalse(count($current_feed["links"]) == 0);
        $this->assertEquals("http://www.phpwiki.org/fakeurl", $current_feed["links"][0]["href"]);
        $this->assertEquals("Cool feed", $current_feed["subtitle"]);
        $this->assertEquals("2010-05-15T01:00:00Z", $current_feed["updated"]);
        
        $this->assertFalse(count($current_feed["authors"]) == 0);
        
        $current_author = $current_feed["authors"][0];
        $this->assertEquals("Sébastien Le Callonnec", $current_author["name"]);
        $this->assertEquals("slc_ie@yahoo.ie", $current_author["email"]);
        
        $this->assertFalse(count($parser->entries) == 0);
        
        $current_entry = $parser->entries[0];
        $this->assertEquals("Foobar Éire", $current_entry["title"]);
        $this->assertEquals("http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8", $current_entry["links"][0]["href"]);
        $this->assertEquals("2010-05-15T01:00:00Z", $current_entry["updated"]);
        $this->assertEquals("2010-05-15T01:00:00Z", $current_entry["published"]);
        $this->assertEquals("tag:www.phpwiki.org,2010-05-15:/fakeurl/20100515223621", $current_entry["id"]);
        
        $payload =<<<CONTENT
<div xmlns="http://www.w3.org/1999/xhtml">Millenium Spire, Dublin
          <div class="geo">Geo coordinates: 
            <abbr class="latitude" title="53.349441">53.349441</abbr>
            <abbr class="longitude" title="-6.260282">-6.260282</abbr>
          </div>
        </div>
CONTENT;
        $this->assertEquals($payload, $current_entry["content"]);
    }
    
    function testExtensiveAtomExampleFromRFC4287() {
        $fake_atom_file = <<<ATOM
<?xml version="1.0" encoding="utf-8"?>

<feed xmlns="http://www.w3.org/2005/Atom">
  <title type="text">dive into mark</title>
  <subtitle type="html">
    A &lt;em&gt;lot&lt;/em&gt; of effort
    went into making this effortless
  </subtitle>

  <updated>2005-07-31T12:29:29Z</updated>
  <id>tag:example.org,2003:3</id>
  <link rel="alternate" type="text/html" 
   hreflang="en" href="http://example.org/"/>
  <link rel="self" type="application/atom+xml" 
   href="http://example.org/feed.atom"/>
  <rights>Copyright (c) 2003, Mark Pilgrim</rights>

  <generator uri="http://www.example.com/" version="1.0">
    Example Toolkit
  </generator>
  <entry>
    <title>Atom draft-07 snapshot</title>
    <link rel="alternate" type="text/html" 
     href="http://example.org/2005/04/02/atom"/>

    <link rel="enclosure" type="audio/mpeg" length="1337"
     href="http://example.org/audio/ph34r_my_podcast.mp3"/>
    <id>tag:example.org,2003:3.2397</id>
    <updated>2005-07-31T12:29:29Z</updated>
    <published>2003-12-13T08:29:29-04:00</published>

    <author>
      <name>Mark Pilgrim</name>
      <uri>http://example.org/</uri>
      <email>f8dy@example.com</email>

    </author>
    <contributor>
      <name>Sam Ruby</name>
    </contributor>
    <contributor>

      <name>Joe Gregorio</name>
    </contributor>
    <content type="xhtml" xml:lang="en" 
     xml:base="http://diveintomark.org/">
      <div xmlns="http://www.w3.org/1999/xhtml">
        <p><i>[Update: The Atom draft is finished.]</i></p>

      </div>
    </content>
  </entry>
</feed>
ATOM;
        $parser = new AtomParser();
        $parser->parse($fake_atom_file);

        $this->assertFalse(count($parser->feed) == 0);
        
        $current_feed = $parser->feed[0];
        $this->assertEquals("dive into mark", $current_feed["title"]);
        $this->assertEquals("Copyright (c) 2003, Mark Pilgrim", $current_feed["rights"]);
        $this->assertEquals("A <em>lot</em> of effort\n    went into making this effortless", $current_feed["subtitle"]);
        $this->assertEquals("2005-07-31T12:29:29Z", $current_feed["updated"]);
        $this->assertEquals("tag:example.org,2003:3", $current_feed["id"]);
        $this->assertEquals("Example Toolkit", $current_feed["generator"]);
        
        $this->assertTrue(count($current_feed["authors"]) == 0);
        $this->assertTrue(count($current_feed["contributors"]) == 0);

        
        $this->assertFalse(count($parser->entries) == 0);
        
        $current_entry = $parser->entries[0];
        $this->assertEquals("Atom draft-07 snapshot", $current_entry["title"]);
        $this->assertEquals("2005-07-31T12:29:29Z", $current_entry["updated"]);
        $this->assertEquals("2003-12-13T08:29:29-04:00", $current_entry["published"]);
        $this->assertEquals("tag:example.org,2003:3.2397", $current_entry["id"]);
        $this->assertEquals(2, count($current_entry["links"]));
        
        $this->assertTrue(count($current_entry["authors"]) == 1);
        $this->assertTrue(count($current_entry["contributors"]) == 2);
        
        $current_author = $current_entry["authors"][0];
        $this->assertEquals("Mark Pilgrim", $current_author["name"]);
        $this->assertEquals("f8dy@example.com", $current_author["email"]);
        
        $first_contributor = $current_entry["contributors"][0];
        $second_contributor = $current_entry["contributors"][1];
        
        $this->assertEquals("Sam Ruby", $first_contributor["name"]);
        $this->assertEquals("Joe Gregorio", $second_contributor["name"]);
        
        $first_link = $current_entry["links"][0];
        $this->assertEquals("alternate", $first_link["rel"]);
        $this->assertEquals("text/html", $first_link["type"]);
        $this->assertEquals("http://example.org/2005/04/02/atom", $first_link["href"]);
        
        $second_link = $current_entry["links"][1];
        $this->assertEquals("enclosure", $second_link["rel"]);
        $this->assertEquals("audio/mpeg", $second_link["type"]);
        $this->assertEquals("1337", $second_link["length"]);
        $this->assertEquals("http://example.org/audio/ph34r_my_podcast.mp3", $second_link["href"]);
        
        $payload = <<<CONTENT
<div xmlns="http://www.w3.org/1999/xhtml">
        <p><i>[Update: The Atom draft is finished.]</i></p>

      </div>
CONTENT;

        $this->assertEquals($payload, $current_entry["content"]);
    }
}
?>

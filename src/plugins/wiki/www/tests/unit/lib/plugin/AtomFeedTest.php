<?php
// $Id: AtomFeedTest.php 7466 2010-06-07 08:12:29Z rurban $
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

require_once('lib/plugin/AtomFeed.php');
require_once('lib/AtomParser.php');
require_once('lib/HtmlElement.php');

class AtomFeedTest
extends phpwiki_TestCase
{
    var $atom_feed_plugin;
    
    public function setUp() {
        parent::setUp();
        $this->atom_feed_plugin = new WikiPlugin_AtomFeed();
    }
    
    public function testRunMaxItem() {
        global $request;
        $expected_html = <<<EXPECTED
<div class="rss"><h3><a href="http://www.phpwiki.org/fakeurl">This is a fake feed</a></h3>
<dl>
<dt><a href="http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8">Foobar Éire</a></dt>
<dd><div xmlns="http://www.w3.org/1999/xhtml">Millenium Spire, Dublin
          <div class="geo">Geo coordinates: 
            <abbr class="latitude" title="53.349441">53.349441</abbr>
            <abbr class="longitude" title="-6.260282">-6.260282</abbr>
          </div>
        </div></dd>
</dl>
</div>
EXPECTED;
        $html = $this->atom_feed_plugin->run(null, 'url=file://' . dirname(__FILE__) . '/atom-example.xml maxitem=1', $request, '.');
        $this->assertEquals($expected_html, trim(html_entity_decode($html->asXML())));
    }
    
    public function testRunTitleOnly() {
        global $request;
        $expected_html = <<<EXPECTED
<div class="rss"><h3><a href="http://www.phpwiki.org/fakeurl">This is a fake feed</a></h3>
<dl>
<dt><a href="http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8">Foobar Éire</a></dt>
<dd></dd>
<dt><a href="http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8">Foobar Éire 2</a></dt>
<dd></dd>
<dt><a href="http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8">Foobar Éire 3</a></dt>
<dd></dd>
<dt><a href="http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8">Foobar Éire 4</a></dt>
<dd></dd>
<dt><a href="http://maps.google.com/maps?f=q&sll=53.125728,-6.068907&ie=UTF8">Foobar Éire 5</a></dt>
<dd></dd>
</dl>
</div>
EXPECTED;
        $html = $this->atom_feed_plugin->run(null, 'url=file://' . dirname(__FILE__) . '/atom-example.xml titleonly=true', $request, '.');
        $this->assertEquals($expected_html, trim(html_entity_decode($html->asXML())));
    }
}
?>
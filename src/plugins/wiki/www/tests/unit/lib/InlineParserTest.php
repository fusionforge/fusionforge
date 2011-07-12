<?php
// $Id: InlineParserTest.php 7956 2011-03-03 17:08:31Z vargenau $

/* Copyright (C) 2004 Dan Frankowski <dfrankow@cs.umn.edu>
 *           (C) 2006, 2007 Reini Urban <rurban@x-ray.at>
 */

require_once 'lib/InlineParser.php';
require_once 'PHPUnit.php';

class InlineParserTest extends phpwiki_TestCase {

    function _tests() {
        $uplink = getUploadDataPath().'/image.jpg';
        // last update: 1.3.13
    	return array(
	"[label|link]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":3:{s:5:"_page";s:4:"link";s:6:"_label";s:5:"label";s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"[ label | link.jpg ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":3:{s:5:"_page";s:8:"link.jpg";s:6:"_label";s:5:"label";s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"[ image.jpg | link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":3:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:4:"_tag";s:6:"object";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:8:"_content";a:1:{i:0;O:11:"HtmlElement":4:{s:4:"_tag";s:5:"embed";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:8:"_content";a:0:{}s:11:"_properties";i:4;}}s:11:"_properties";i:6;}s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"[ Upload:image.jpg | link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":3:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:'.strlen($uplink).':"'.$uplink.'";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:8:"_content";a:0:{}s:11:"_properties";i:7;}s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"[ http://server/image.jpg | link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":3:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:23:"http://server/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:8:"_content";a:0:{}s:11:"_properties";i:7;}s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"[ label | http://server/link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":2:{s:4:"_url";s:18:"http://server/link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
	"[ label | Upload:link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:20:"Cached_InterwikiLink":2:{s:5:"_link";s:11:"Upload:link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
	"[ label | phpwiki:action=link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:17:"Cached_PhpwikiURL":2:{s:4:"_url";s:19:"phpwiki:action=link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
	"Upload:image.jpg" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:20:"Cached_InterwikiLink":1:{s:5:"_link";s:16:"Upload:image.jpg";}i:2;s:0:"";}}',
	"http://server/image.jpg" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:23:"http://server/image.jpg";}i:2;s:0:"";}}',
	"http://server/link" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:18:"http://server/link";}i:2;s:0:"";}}',
	"[http:/server/~name/]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:18:"http:/server/name/";}i:2;s:0:"";}}',
	"http:/server/~name/" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:18:"http:/server/name/";}i:2;s:0:"";}}',
	"[label|:link]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":4:{s:5:"_page";s:4:"link";s:7:"_nolink";b:1;s:6:"_label";s:5:"label";s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"[:link]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":3:{s:5:"_page";s:4:"link";s:7:"_nolink";b:1;s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"relation::link" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":3:{s:4:"_url";s:14:"relation::link";s:9:"_relation";s:8:"relation";s:5:"_page";s:4:"link";}i:2;s:0:"";}}',
	"[label|relation::link]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":4:{s:4:"_url";s:14:"relation::link";s:6:"_label";s:5:"label";s:9:"_relation";s:8:"relation";s:5:"_page";s:4:"link";}i:2;s:0:"";}}',
	"attribute:=1000" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":5:{s:4:"_url";s:15:"attribute:=1000";s:9:"_relation";s:9:"attribute";s:10:"_attribute";s:4:"1000";s:15:"_attribute_base";s:4:"1000";s:5:"_unit";s:0:"";}i:2;s:0:"";}}',
	"attribute:=1,000km" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":5:{s:4:"_url";s:18:"attribute:=1,000km";s:9:"_relation";s:9:"attribute";s:10:"_attribute";s:7:"1,000km";s:15:"_attribute_base";s:9:"1000000 m";s:5:"_unit";s:1:"m";}i:2;s:0:"";}}',
	"attribute:=1,000 km" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":5:{s:4:"_url";s:16:"attribute:=1,000";s:9:"_relation";s:9:"attribute";s:10:"_attribute";s:5:"1,000";s:15:"_attribute_base";s:4:"1000";s:5:"_unit";s:0:"";}i:2;s:3:" km";}}',
	"[attribute:=1,000 km]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":5:{s:4:"_url";s:19:"attribute:=1,000 km";s:9:"_relation";s:9:"attribute";s:10:"_attribute";s:8:"1,000 km";s:15:"_attribute_base";s:9:"1000000 m";s:5:"_unit";s:1:"m";}i:2;s:0:"";}}',
	"[label|attribute:=1,000 km]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_SemanticLink":6:{s:4:"_url";s:19:"attribute:=1,000 km";s:6:"_label";s:5:"label";s:9:"_relation";s:9:"attribute";s:10:"_attribute";s:8:"1,000 km";s:15:"_attribute_base";s:9:"1000000 m";s:5:"_unit";s:1:"m";}i:2;s:0:"";}}',
	"This is a :PageLink" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:11:"This is a :";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:8:"PageLink";s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"This is a ::PageLink" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:12:"This is a ::";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:8:"PageLink";s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"This is a :=PageAttr" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:12:"This is a :=";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:8:"PageAttr";s:9:"_basepage";b:0;}i:2;s:0:"";}}',
	"This is :~NoLink" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:9:"This is :";i:1;s:6:"NoLink";i:2;s:0:"";}}',
	"This is ::~NoLink" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:10:"This is ::";i:1;s:6:"NoLink";i:2;s:0:"";}}',
	"This is :=~NoLink" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:10:"This is :=";i:1;s:6:"NoLink";i:2;s:0:"";}}');
    }

    function runTest() {
	if (substr($this->_name,0,5) == "_test") {
	    $name = rawurldecode(substr($this->_name,5));
	    $this->_testLink($name);
	} else {
	    call_user_func(array(&$this, $this->_name));
	}
    }

    function testNoWikiWords() {
        $str1 = 'This has no wiki words, and is all text.';
        $xmlc1 = TransformInline($str1);
        $this->assertTrue(isa($xmlc1, 'XmlContent'));
        $c1 = $xmlc1->getContent();
        $this->assertEquals(1, count($c1));
        $this->assertEquals($str1, $c1[0]);
    }

    function testWikiWord() {
        $ww = 'WikiWord';
        $str1 = "This has 1 $ww.";
        $xml = TransformInline($str1);
        $this->assertTrue(isa($xml, 'XmlContent'));
        $c1 = $xml->getContent();
        $this->assertEquals(3, count($c1));
        $this->assertTrue(isa($c1[1], 'Cached_WikiLink'));

        $this->assertEquals('This has 1 ', $c1[0]);
        $this->assertEquals($ww, $c1[1]->asString());
        $this->assertEquals('.', $c1[2]);
    }

    function _testLink($wiki, $expected = null) {
        if (is_null($expected)) {
            $ta = $this->_tests();
            $expected = $ta[$wiki];
        }
        $xml = TransformInline($wiki);
        $this->assertTrue(isa($xml, 'XmlContent'));
        $expectobj = unserialize($expected);
        /* if (DEBUG & _DEBUG_VERBOSE)
	   echo "\t\"",$wiki,'" => \'',serialize($xml),"',\n"; flush(); */
        $this->assertEquals($expectobj, $xml);
    }
}

foreach (InlineParserTest::_tests() as $wiki => $expected) {
    $name = "_test".rawurlencode($wiki);
    $GLOBALS['suite']->addTest(new InlineParserTest($name));
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

<?php
rcs_id('$Id: InlineParserTest.php,v 1.8 2005/11/21 22:16:46 rurban Exp $');

/* Copyright (C) 2004, Dan Frankowski <dfrankow@cs.umn.edu>
 * testLinks: Reini Urban
 */

require_once 'lib/InlineParser.php';
require_once 'PHPUnit.php';

class InlineParserTest extends phpwiki_TestCase {

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
    
    function testLinks() {
        $uplink = 'http://'.(defined('SERVER_NAME')?SERVER_NAME:'').DATA_PATH.'/uploads/image.jpg';
        $tests = array("[label|link]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:4:"link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
                       "[ label | link.jpg ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:8:"link.jpg";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
                       "[ image.jpg | link ]" => check_php_version(5)
				// php5 does not have _content as first HtmlElement property
/* php4
TestCase inlineparsertest->testlinks() failed: expected 
  o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:3:{s:3:"src";b:0;s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}
actual 
  o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:1:{i:0;o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:5:"embed";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:11:"_properties";i:4;}}s:4:"_tag";s:6:"object";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:11:"_properties";i:6;}}i:2;s:0:"";}}
TestCase inlineparsertest->testlinks() failed: expected 
  o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:3:{s:3:"src";s:42:"http://reini/phpwiki-dev/uploads/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}
actual 
  o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:42:"http://reini/phpwiki-dev/uploads/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}
TestCase inlineparsertest->testlinks() failed: expected 
  o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:3:{s:3:"src";s:23:"http://server/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}
actual 
  o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:23:"http://server/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}
*/
				? 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:4:"_tag";s:6:"object";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:8:"_content";a:1:{i:0;O:11:"HtmlElement":4:{s:4:"_tag";s:5:"embed";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:8:"_content";a:0:{}s:11:"_properties";i:4;}}s:11:"_properties";i:6;}}i:2;s:0:"";}}'
                       		: 'o:10:"xmlcontent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;o:15:"cached_wikilink":2:{s:5:"_page";s:4:"link";s:6:"_label";o:11:"htmlelement":4:{s:8:"_content";a:1:{i:0;o:11:"htmlelement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:5:"embed";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:11:"_properties";i:4;}}s:4:"_tag";s:6:"object";s:5:"_attr";a:5:{s:3:"src";s:0:"";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:12:"inlineobject";s:4:"type";b:0;}s:11:"_properties";i:6;}}i:2;s:0:"";}}',
                       "[ Upload:image.jpg | link ]" => !check_php_version(5) 
                       	? 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:'.strlen($uplink).':"'.$uplink.'";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}' 
                       	: 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:'.strlen($uplink).':"'.$uplink.'";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:8:"_content";a:0:{}s:11:"_properties";i:7;}}i:2;s:0:"";}}',
                       "[ http://server/image.jpg | link ]" => !check_php_version(5) 
                        ? 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:8:"_content";a:0:{}s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:23:"http://server/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:11:"_properties";i:7;}}i:2;s:0:"";}}' 
                        : 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:15:"Cached_WikiLink":2:{s:5:"_page";s:4:"link";s:6:"_label";O:11:"HtmlElement":4:{s:4:"_tag";s:3:"img";s:5:"_attr";a:4:{s:3:"src";s:23:"http://server/image.jpg";s:3:"alt";s:4:"link";s:5:"title";s:4:"link";s:5:"class";s:11:"inlineimage";}s:8:"_content";a:0:{}s:11:"_properties";i:7;}}i:2;s:0:"";}}',
                       "[ label | http://server/link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":2:{s:4:"_url";s:18:"http://server/link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
                       "[ label | Upload:link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:20:"Cached_InterwikiLink":2:{s:5:"_link";s:11:"Upload:link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
                       "[ label | phpwiki:action=link ]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:17:"Cached_PhpwikiURL":2:{s:4:"_url";s:19:"phpwiki:action=link";s:6:"_label";s:5:"label";}i:2;s:0:"";}}',
                       "Upload:image.jpg" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:20:"Cached_InterwikiLink":1:{s:5:"_link";s:16:"Upload:image.jpg";}i:2;s:0:"";}}',
                       "http://server/image.jpg" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:23:"http://server/image.jpg";}i:2;s:0:"";}}',
                       "http://server/link" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:18:"http://server/link";}i:2;s:0:"";}}',
                       "[http:/server/~name/]" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:18:"http:/server/name/";}i:2;s:0:"";}}',
                       "http:/server/~name/" => 'O:10:"XmlContent":1:{s:8:"_content";a:3:{i:0;s:0:"";i:1;O:19:"Cached_ExternalLink":1:{s:4:"_url";s:18:"http:/server/name/";}i:2;s:0:"";}}'
                       );
        //$i = 0;
        foreach ($tests as $wiki => $expected) {
            //print $i++ . " .. ";
            $xml = TransformInline($wiki);
            $this->assertTrue(isa($xml, 'XmlContent'));
            $actual = serialize($xml);
            if (!check_php_version(5))  {
                $expected = strtolower($expected);
                $actual = strtolower($actual);
            }
            $this->assertEquals($expected, $actual);
        }
    }
   
}

// (c-file-style: "gnu")
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:   
?>
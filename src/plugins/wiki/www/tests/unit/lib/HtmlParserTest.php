<?php
// $Id: HtmlParserTest.php 7956 2011-03-03 17:08:31Z vargenau $

/* Copyright (C) 2004, Reini Urban <rurban@x-ray.at>
 */

require_once 'lib/HtmlParser.php';
require_once 'PHPUnit.php';

define('USE_GLOBAL_SAX',false); // this seems to be a xml bug

class HtmlParserTest extends phpwiki_TestCase {

    function testSimple() {
        $html2wiki = array(
                           "<B>bold</B>"              => "*bold*",
                           "<STRONG>strong</STRONG>"  => "*strong*",
                           "<I>italic</I>"	     => "_italic_",
                           "<EM>emphasized</EM>"     => "_emphasized_",
                           "<HR>"		     => "----",
                           "<DT><DD>Indent</DD></DT>" => ";:Indent",
                           "<NOWIKI>nowiki</NOWIKI>"  => "<verbatim>\nnowiki\n</verbatim>",
                           "<DL><DT> Def </DT><DD> List</DD></DL>" => "; Def : List",
                           );
        if (USE_GLOBAL_SAX)
            $parser = new HtmlParser("PhpWiki2"); // will not work!
        foreach ($html2wiki as $html => $wiki) {
            if (!USE_GLOBAL_SAX) // redefine it for every run.
                $parser = new HtmlParser("PhpWiki2");
            if (USE_GLOBAL_SAX)
                $parser->parse($html,false); // is_final is false
            else
                $parser->parse($html);
            $this->assertEquals($wiki, trim($parser->output()));
            if (USE_GLOBAL_SAX)
                unset($GLOBALS['xml_parser_root']);
            else
                $parser->__destruct();
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

<?php
// $Id: XmlRpcTest.php 7956 2011-03-03 17:08:31Z vargenau $

/* Copyright (C) 2007 Reini Urban
 */

require_once 'lib/XmlRpcClient.php';
require_once 'PHPUnit.php';

class XmlRpcTest extends phpwiki_TestCase {

    function testSelfVersion() {
        $v = wiki_xmlrpc_post("wiki.getRPCVersionSupported");
        $this->assertTrue(gettype($v), 'int');
        $this->assertEquals($v, WIKI_XMLRPC_VERSION);
    }

    function testSelfAPI_v1() {
        $v = wiki_xmlrpc_post("wiki.getRPCVersionSupported");
        $this->assertTrue($v >= 1);
    }

    /*
    function testSelfAPI_v2() {
        $v = wiki_xmlrpc_post("wiki.getRPCVersionSupported");
        $this->assertTrue($v >= 2);
	// struct getAttachment( string localpath )
    }
    */

    /*
    function testSelfAPI_private() {
	// struct getUploadedFileInfo( string localpath )
	// boolean wiki.mailPasswordToUser ( username )
	// array wiki.titleSearch(String substring [, String option = "0"])
	// array wiki.listPlugins()
	// String wiki.getPluginSynopsis(String plugin)
        // array wiki.listRelations([ Integer option = 1 ])
        // array wiki.callPlugin(String name, String args)

        $this->assertTrue(true);
    }

    function testSelfAPI_planned() {
	// String pingback.ping(String sourceURI, String targetURI)
        // boolean wiki.rssPleaseNotify ( notifyProcedure, port, path, protocol, urlList )
        $this->assertTrue(true);
    }
    */
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>

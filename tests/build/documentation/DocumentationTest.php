<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Documentation tests (html, pdf).
 *
 * @package   DocumentationTests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   http://www.opensource.org/licenses/gpl-license.php  GPL License
 */
class Documentation_Tests extends PHPUnit_Framework_TestCase
{
    /**
     * Build HTML documentation from docbook
     */
    public function testBuildHTMLDocumentation()
    {
	    $tests = dirname( dirname( dirname( dirname (__FILE__))));
	    $base = dirname( $tests );
	    system("cd ../src/docs/docbook; make TARGET=$base/build/documentation/ html", $retval);
	    $this->assertEquals(0, $retval);
    }

    /**
     * Build PDF documentation from docbook
     */
    public function testBuildPDFDocumentation()
    {
	    $tests = dirname( dirname( dirname( dirname (__FILE__))));
	    $base = dirname( $tests );
	    system("cd ../src/docs/docbook; make TARGET=$base/build/documentation/ pdf", $retval);
	    $this->assertEquals(0, $retval);
    }
}

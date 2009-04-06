<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Syntax test class.
 *
 * @package   SyntaxTests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   http://www.opensource.org/licenses/gpl-license.php  GPL License
 */
class Tarball_Tests extends PHPUnit_Framework_TestCase
{
    /**
     * Build tarballs 
     */
    public function testBuildTarball()
    {
	    $tests = dirname( dirname( dirname( dirname (__FILE__)))); 
	    $base = dirname( $tests );
	    system("cd ..; make BUILDRESULT=$base/build/packages buildtar", $retval);
	    $this->assertEquals(0, $retval);
    }
}

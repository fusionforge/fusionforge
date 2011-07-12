<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Packaging tests (tarball, debian, rpm).
 *
 * @package   PackagesTests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   http://www.opensource.org/licenses/gpl-license.php  GPL License
 */
class Packages_Tests extends PHPUnit_Framework_TestCase
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

    public function testBuildDeb()
    {
	    $tests = dirname( dirname( dirname( dirname (__FILE__))));
	    $base = dirname( $tests );
	    system("cd ..; make -f Makefile.debian BUILDRESULT=$base/build/packages clean all", $retval);
	    $this->assertEquals(0, $retval);
    }

    public function testBuildRPM()
    {
	    $tests = dirname( dirname( dirname( dirname (__FILE__))));
	    $base = dirname( $tests );
	    system("cd ..; make -f Makefile.rh BUILDRESULT=$base/build/packages all", $retval);
	    $this->assertEquals(0, $retval);
    }
}

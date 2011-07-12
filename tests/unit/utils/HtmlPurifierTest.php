<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Simple test to check that htmlpurifier is installed
 *
 * @package   Tests
 * @author    Olivier Berger <olivier.berger@it-sudparis.eu>
 * @copyright 2009 Olivier Berger & Institut TELECOM
 * @license   GPL License
 */
class HtmlPurifier_Tests extends PHPUnit_Framework_TestCase
{
	/**
	 * Test that include of lib doesn't fail, otherwise give some hint on missing package
	 */
	public function testHtmlPurifier()
	{
	  try {
	    // cannot test this as fails hard
	    //require_once('HTMLPurifier.auto.php');
	    // so include instead :
	    include 'HTMLPurifier.auto.php';
	  }

	  catch (PHPUnit_Framework_Error $expected) {
	    $this->fail('You probably need to install htmlpurifier : '.$expected->getMessage());
            return;
	  }

	}

}

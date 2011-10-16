<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../src/common/include/utils.php';

/**
 * Simple tests for the utils library.
 *
 * @package   Tests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   GPL License
 */
class Utils_Tests extends PHPUnit_Framework_TestCase
{
	/**
	 * test the validate_email function.
	 */
	public function testEmail()
	{
		$this->assertTrue(validate_email('al@fx.fr'), 'al@fx.fr is a valid email address');

		$this->assertFalse(validate_email('al @fx.fr'), 'al @fx.fr is not a valid email address');

		$this->assertFalse(validate_email('al'), 'al is not a valid email address');
	}

	/**
	 * test the validate_hostname function.
	 */
	public function testHostname()
	{
		$this->assertTrue(valid_hostname('myhost.com'), 'myhost.com is a valid hostname.');

		$this->assertTrue(valid_hostname('myhost.com.'), 'myhost.com. is a valid hostname.');

		$this->assertFalse(valid_hostname('my host.com'), 'my host.com is not a valid hostname');

		$this->assertFalse(valid_hostname('O@O'), 'O@O is not a valid hostname');
	}

	/**
	 * test the util_strip_accents() function.
	 */
	public function testStripAccents()
	{
		$this->assertEquals(util_strip_accents('aléiât'), 'aleiat');

		$this->assertEquals(util_strip_accents('ààéééïï'), 'aaeeeii');

		$this->assertEquals(util_strip_accents('alain'), 'alain');
	}

	/**
	 * test the human_readable_bytes() function.
	 */
	public function testHumanReadableBytes()
	{
		$this->assertEquals('0', human_readable_bytes(0));
		$this->assertEquals('12 bytes', human_readable_bytes(12));
		$this->assertEquals('-12 bytes', human_readable_bytes(-12));
		$this->assertEquals('1 KiB', human_readable_bytes(1024));
		$this->assertEquals('1 kB', human_readable_bytes(1000, true));
		$this->assertEquals('2 KiB', human_readable_bytes(2*1024));
		$this->assertEquals('2 kB', human_readable_bytes(2000, true));
		$this->assertEquals('2 kB', human_readable_bytes(2012, true));
		$this->assertEquals('1 MiB', human_readable_bytes(1024*1024));
		$this->assertEquals('1 MB', human_readable_bytes(1000000, true));
		$this->assertEquals('1 GiB', human_readable_bytes(1024*1024*1024));
		$this->assertEquals('1 GB', human_readable_bytes(1000000000, true));
		$this->assertEquals('1 TiB', human_readable_bytes(1024*1024*1024*1024));
		$this->assertEquals('1 TB', human_readable_bytes(1000000000000, true));
	}
}

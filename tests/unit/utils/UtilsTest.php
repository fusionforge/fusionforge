<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(dirname(__FILE__)) . '/../../src/common/include/utils.php';
require_once dirname(dirname(__FILE__)) . '/../../src/common/include/escapingUtils.php';

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
	 * test the util_check_url function.
	 */
	public function testUtilCheckUrl()
	{
		$this->assertTrue(util_check_url('http://fusionforge.org/'), 'http://fusionforge.org/ is a valid URL.');

		$this->assertTrue(util_check_url('https://fusionforge.org/'), 'https://fusionforge.org/ is a valid URL.');

		$this->assertTrue(util_check_url('ftp://fusionforge.org/'), 'ftp://fusionforge.org/ is a valid URL.');

		$this->assertFalse(util_check_url('webdav://toto'), 'webdav://toto is not a valid URL.');

		$this->assertFalse(util_check_url('fusionforge.org'), 'fusionforge.org is not a valid URL');
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
	 * test the util_make_links() function.
	 */
	public function testUtilMakeLinks()
	{
		$this->assertEquals(
			'a <a href="http://fusionforge.org/" target="_new">http://fusionforge.org/</a> b',
			util_make_links('a http://fusionforge.org/ b')
		);

		$this->assertEquals(
			'a <a href="https://fusionforge.org/" target="_new">https://fusionforge.org/</a> b',
			util_make_links('a https://fusionforge.org/ b')
		);

		$this->assertEquals(
			'a <img src="http://ff.org/i.png" /> b',
			util_make_links('a <img src="http://ff.org/i.png" /> b')
		);
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

	public function testGetFilteredStringFromRequest()
	{
		$_REQUEST=array('arg' => 'good');
		$this->assertEquals(getFilteredStringFromRequest('arg', '/^[a-z]+$/', 'default'), 'good');

		$_REQUEST=array('arg' => 'BaD');
		$this->assertEquals(getFilteredStringFromRequest('arg', '/^[a-z]+$/', 'default'), 'default');

		$_REQUEST=array('no_arg' => 'BaD');
		$this->assertEquals(getFilteredStringFromRequest('arg', '/^[a-z]+$/', 'default'), 'default');
	}
}

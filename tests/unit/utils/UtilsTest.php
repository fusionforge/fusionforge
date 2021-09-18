<?php
/**
 * Copyright FusionForge Team
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

@include_once '/usr/local/share/php/vendor/autoload.php';
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
class utilsTest extends PHPUnit\Framework\TestCase {
	/**
	 * test the validate_email function.
	 */
	public function testEmail() {
		$this->assertTrue(validate_email('al@fx.fr'), 'al@fx.fr is a valid email address');

		$this->assertFalse(validate_email('al @fx.fr'), 'al @fx.fr is not a valid email address');

		$this->assertFalse(validate_email('al'), 'al is not a valid email address');
	}

	/**
	 * test the validate_hostname function.
	 */
	public function testHostname() {
		$this->assertTrue(valid_hostname('myhost.com'), 'myhost.com is a valid hostname.');

		$this->assertTrue(valid_hostname('myhost.com.'), 'myhost.com. is a valid hostname.');

		$this->assertFalse(valid_hostname('my host.com'), 'my host.com is not a valid hostname');

		$this->assertFalse(valid_hostname('O@O'), 'O@O is not a valid hostname');
	}

	/**
	 * test the util_check_url function.
	 */
	public function testUtilCheckUrl() {
		$this->assertTrue(util_check_url('http://fusionforge.org/'), 'http://fusionforge.org/ is a valid URL.');

		$this->assertTrue(util_check_url('https://fusionforge.org/'), 'https://fusionforge.org/ is a valid URL.');

		$this->assertTrue(util_check_url('ftp://fusionforge.org/'), 'ftp://fusionforge.org/ is a valid URL.');

		$this->assertFalse(util_check_url('webdav://toto'), 'webdav://toto is not a valid URL.');

		$this->assertFalse(util_check_url('fusionforge.org'), 'fusionforge.org is not a valid URL');
	}

	/**
	 * test the util_strip_accents() function.
	 */
	public function testStripAccents() {
		$this->assertEquals('aleiat', util_strip_accents('aléiât'));

		$this->assertEquals('aaeeeii', util_strip_accents('ààéééïï'));

		$this->assertEquals('alain', util_strip_accents('alain'));
	}

	/**
	 * test the util_make_links() function.
	 */
	public function testUtilMakeLinks() {
		$this->assertEquals(
			'a <a href="http://fusionforge.org/" target="_blank">http://fusionforge.org/</a> b',
			util_make_links('a http://fusionforge.org/ b')
		);

		$this->assertEquals(
			'a <a href="https://fusionforge.org/" target="_blank">https://fusionforge.org/</a> b',
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
	public function testHumanReadableBytes() {
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

	public function testGetFilteredStringFromRequest() {
		$_REQUEST=array('arg' => 'good');
		$this->assertEquals('good', getFilteredStringFromRequest('arg', '/^[a-z]+$/', 'default'));

		$_REQUEST=array('arg' => 'BaD');
		$this->assertEquals('default', getFilteredStringFromRequest('arg', '/^[a-z]+$/', 'default'));

		$_REQUEST=array('no_arg' => 'BaD');
		$this->assertEquals('default', getFilteredStringFromRequest('arg', '/^[a-z]+$/', 'default'));
	}

	public function testUtilIsHtml() {
		$this->assertFalse(util_is_html(''));
		$this->assertFalse(util_is_html('This is a text.'));
		$this->assertTrue(util_is_html('This is <strong>html</strong>'));
		$this->assertFalse(util_is_html('Math: 4 > 3'));
		$this->assertFalse(util_is_html('Math: "4" > 3'));
		$this->assertTrue(util_is_html('Math: 4 &gt; 3'));
		$this->assertTrue(util_is_html('Math&eacute;tiques'));
		$this->assertTrue(util_is_html('Math: &quot;4&quot; > 3'));
	}
}

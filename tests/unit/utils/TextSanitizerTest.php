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
require_once dirname(__FILE__) . '/../../../src/common/include/FFError.class.php';
require_once dirname(__FILE__) . '/../../../src/common/include/TextSanitizer.class.php';

/**
 * Simple tests for the text sanitizer class.
 *
 * @package   Tests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   GPL License
 */
class TextSanitizerTests extends PHPUnit\Framework\TestCase
{
	protected $s;

	function setUp()
	{
		$this->s = new TextSanitizer();
	}

	/**
	 * test purify on good code.
	 */
	public function testPurifyOnValidHtmlCode()
	{
		$this->assertEquals('<h1>A valid message</h1>', $this->s->purify('<h1>A valid message</h1>'));
		$this->assertEquals('<h1>A <b>valid</b> message</h1>', $this->s->purify('<h1>A <B>valid</B> message</h1>'));
	}

	/**
	 * test purify on repairing damaged code.
	 */
	public function testPurifyOnInvalidHtmlCode()
	{
		$this->assertEquals('<h1>Missing ending tag</h1>', $this->s->purify('<h1>Missing ending tag'));
		$this->assertEquals('Invalid  tag', $this->s->purify('Invalid <toto> tag'));
	}

	/**
	 * test purify on malicious code.
	 */
	public function testPurifyOnMaliciousHtmlCode()
	{
		$this->assertEquals('Hacker ', $this->s->purify('Hacker <script>hello</script>'));
	}

	/**
	 * test purify on other html piece of code.
	 */
	public function testPurifyOnMiscCode()
	{
		$in  = "</div>\n<div>&gt; rep &gt; rep</div>\n<div>";
		$out = "\n<div>&gt; rep &gt; rep</div>\n";
		$this->assertEquals($out, $this->s->purify($in));
	}

	/**
	 * test purify on other html piece of code.
	 */
	public function testPurifyOnMiscCode2()
	{
		$text = '<b>ceci</b> <i>est</i> <u>une</u> <font color="#cc0000">reponse</font>';
		$this->assertEquals($text, $this->s->purify($text));
	}
}

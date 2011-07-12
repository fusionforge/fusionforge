<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../src/common/include/Error.class.php';
require_once dirname(__FILE__) . '/../../../src/common/include/TextSanitizer.class.php';

/**
 * Simple tests for the text sanitizer class.
 *
 * @package   Tests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   GPL License
 */
class TextSanitizerTests extends PHPUnit_Framework_TestCase
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
}

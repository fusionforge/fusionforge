<?php
/*
 * Copyright (C) 2008 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The test suite ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once 'config.php';
require_once 'Testing/SeleniumGforge.php';
require_once 'PHPUnit/Framework/TestCase.php';

class CreateNews extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
		// Reload a fresh database before running this test suite.
		system("php ".dirname(dirname(__FILE__))."/db_reload.php");

		$this->verificationErrors = array();
		$this->selenium = new Testing_SeleniumGforge($this, "*firefox", URL, SELENIUM_RC_HOST);
		$result = $this->selenium->start();
	}

	function tearDown()
	{
		$this->selenium->stop();
	}

	function testMyTestCase()
	{
		$this->selenium->createProject($this, 'ProjectA');

		// Create a simple news.
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "First news");
		$this->selenium->type("details", "This is a simple news.");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("First news"));
		$this->selenium->click("link=First news");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("First news"));
		$this->assertTrue($this->selenium->isTextPresent("This is a simple news."));

		// Create a second news.
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "Second news");
		$this->selenium->type("details", "This is another text");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Second news");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Second news"));
		$this->assertTrue($this->selenium->isTextPresent("This is another text"));
		
		// Check that news are visible in the activity
		// TODO: Not implemented in gforge-4.6
//		$this->selenium->click("link=Activity");
//		$this->selenium->waitForPageToLoad("30000");
//		$this->assertTrue($this->selenium->isTextPresent("First news"));
//		$this->assertTrue($this->selenium->isTextPresent("Second news"));
		
		// Check modification of a news.
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("//a[contains(@href, '" . BASE . "/news/admin/?group_id=6')]");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Second news");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("details", "This is another text (corrected)");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Second news");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Second news");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("This is another text (corrected)"));
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "Test3");
		$this->selenium->type("details", "Special ' chars \"");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Test3");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Special ' chars \""));
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("//a[contains(@href, '". BASE . "/news/admin/?group_id=6')]");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Test3");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("document.forms[2].status[1]");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");

	}

	/*
	 * Test multilines news formated in HTML.
	 */
	function testAcBug4100()
	{
		$this->selenium->createProject($this, 'ProjectA');

		// Create a simple news.
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "Multi line news");
		$this->selenium->type("details", "<p>line1</p><p>line2</p><p>line3</p><br />hello<p>line5</p>\n");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Multi line news"));
		$this->assertTrue($this->selenium->isTextPresent("line1"));
		$this->assertTrue($this->selenium->isTextPresent("line2"));
		$this->assertTrue($this->selenium->isTextPresent("line3"));
		$this->assertTrue($this->selenium->isTextPresent("hello"));
		// $this->assertFalse($this->selenium->isTextPresent("line5"));
		$this->selenium->click("link=Multi line news");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Multi line news"));
		$this->assertTrue($this->selenium->isTextPresent("line1"));
		$this->assertTrue($this->selenium->isTextPresent("line2"));
		$this->assertTrue($this->selenium->isTextPresent("line3"));
		$this->assertTrue($this->selenium->isTextPresent("hello"));
		$this->assertTrue($this->selenium->isTextPresent("line5"));
	}
	
	/*
	 * Test multiple post of the news (reload).
	 * Test skipped due to manual intervention required.
	 */
	function skiptestPreventMultiplePost()
	{
		$this->selenium->createProject($this, 'ProjectA');

		// Create a simple news.
		$this->selenium->click("link=News");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "My ABC news");
		$this->selenium->type("details", "hello DEF with a long detail.\n");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("News Added.")); 
		$this->selenium->chooseOkOnNextConfirmation();
		// Problem, a confirmation window is displayed and I didn't found
		// the way to automatically click on the Ok button.
		$this->selenium->refresh();
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Error - double submit")); 
	}
	
}
?>

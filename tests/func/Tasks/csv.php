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

class ExportCSV extends PHPUnit_Framework_TestCase
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

	function testexportCSV()
	{
		$this->selenium->createProject($this, 'ProjectA');

		// Test: Create a simple bug report (Message1/Text1).
		$this->selenium->open( BASE );
		$this->selenium->click("link=ProjectA");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Task Manager");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("To Do"));
		$this->assertTrue($this->selenium->isTextPresent("Next Release"));
		$this->selenium->click("link=To Do");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("No Matching Tasks found"));

		$this->selenium->click("link=Add task");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "Task1: Hello Paris");
		$this->selenium->type("details", "Hello Paris");
		$this->selenium->type("hours", "10");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Task Created Successfully"));

		$this->selenium->click("link=Add task");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "Task2: Hello France");
		$this->selenium->type("details", "Hello France");
		$this->selenium->type("hours", "15");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Task Created Successfully"));

		$this->selenium->click("link=Add task");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("summary", "Task3: Hello World");
		$this->selenium->type("details", "Hello World");
		$this->selenium->type("hours", "20");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Task Created Successfully"));
	}
}
?>

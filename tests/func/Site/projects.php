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

class CreateProject extends PHPUnit_Framework_TestCase
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

	// Simple creation of a project by the admin user and
	// approval of the creation just after.
	// After creation, project is visible on the main page.
	function testSimpleCreate()
	{
		$this->selenium->open( BASE );
		$this->selenium->click("link=Log In");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("form_loginname", "admin");
		$this->selenium->type("form_pw", "myadmin");
		$this->selenium->click("login");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=My Page");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Register Project");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("full_name", "ProjectA");
		$this->selenium->type("purpose", "This is a simple description for project A");
		$this->selenium->type("description", "This is the public description for project A.");
		$this->selenium->type("unix_name", "projecta");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Your project has been submitted"));
		$this->assertTrue($this->selenium->isTextPresent("you will receive notification of their decision and further instructions"));
		$this->selenium->click("link=Admin");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Pending (P) (New Project Approval)");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("document.forms['approve.projecta'].submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Home");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("ProjectA"));
		$this->selenium->click("link=ProjectA");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("This is the public description for project A."));
		$this->assertTrue($this->selenium->isTextPresent("This project has not yet categorized itself"));
	}

	function testCharsCreateTestCase()
	{
		$this->selenium->open( BASE );
		$this->selenium->click("link=Log In");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("form_loginname", "admin");
		$this->selenium->type("form_pw", "myadmin");
		$this->selenium->click("login");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=My Page");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Register Project");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->type("full_name", "Project ' & B");
		$this->selenium->type("purpose", "This is a & été simple description for project B");
		$this->selenium->type("description", "This is & été the public description for project B.");
		$this->selenium->type("unix_name", "projectb");
		$this->selenium->click("submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Your project has been submitted"));
		$this->assertTrue($this->selenium->isTextPresent("you will receive notification of their decision and further instructions"));
		$this->selenium->click("link=Admin");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Pending (P) (New Project Approval)");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("document.forms['approve.projectb'].submit");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Home");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("Project ' & B"));
		$this->selenium->click("link=Project ' & B");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertTrue($this->selenium->isTextPresent("This is & été the public description for project B."));
	}

	// Test removal of project.
	// TODO: Test not finished as removal does not work.
	function testRemoveProject()
	{
		$this->selenium->createProject($this, 'testal1');
		
		$this->selenium->click("link=Admin");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Display Full Project List/Edit Projects");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=testal1");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("link=Permanently Delete Project");
		$this->selenium->waitForPageToLoad("30000");
		$this->selenium->click("sure");
		$this->selenium->click("reallysure");
		$this->selenium->click("reallyreallysure");
		$this->selenium->click("submit");
	}
}
?>

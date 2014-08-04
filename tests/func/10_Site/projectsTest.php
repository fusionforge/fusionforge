<?php
/**
 * Copyright (C) 2008 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class CreateProject extends FForge_SeleniumTestCase
{
	// Simple creation of a project by the admin user.
	// approval is automatic since project is created bu admin user.
	// After creation, project is visible on the main page.
	function testSimpleCreate()
	{
		// "Manual" procedure
		$this->login (FORGE_ADMIN_USERNAME);
		$this->click("link=My Page");
		$this->waitForPageToLoad("30000");
		$this->click("link=Register Project");
		$this->waitForPageToLoad("30000");
		$this->type("full_name", "ProjectA");
		$this->type("purpose", "This is a simple description for ProjectA");
		$this->type("//textarea[@name='description']", "This is the public description for ProjectA.");
		$this->type("unix_name", "projecta");
		$this->click("//input[@name='scm' and @value='scmsvn']");
		$this->assertTrue($this->isElementPresent("//select[@name='built_from_template']"));
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Your project has been automatically approved"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("ProjectA"));
		$this->click("link=ProjectA");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectA."));
		$this->assertTrue($this->isTextPresent("This project has not yet categorized itself"));
	}

	function testCharsCreateTestCase()
	{
		$this->login(FORGE_ADMIN_USERNAME);
		$this->click("link=My Page");
		$this->waitForPageToLoad("30000");
		$this->click("link=Register Project");
		$this->waitForPageToLoad("30000");
		$this->type("full_name", "Project ' & B");
		$this->type("purpose", "This is a & été simple description for project B");
		$this->type("//textarea[@name='description']", "This is & été the public description for project B.");
		$this->type("unix_name", "projectb");
		$this->click("//input[@name='scm' and @value='scmsvn']");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Your project has been automatically approved"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Project ' & B"));
		$this->click("link=Project ' & B");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is & été the public description for project B."));

		$this->click("link=Projects");
		$this->waitForPageToLoad("30000");
		$this->click("link=Project Tree");
		$this->waitForPageToLoad("30000");
		$this->click("link=Project List");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Project ' & B - This is & été the public description for project B."));
		$this->click("link=My Page");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("Project ' &amp; B"));
	}

	function testHighLevelFunctions()
	{
		// Test our high-level functions (testing the test-suite)
		$this->createProject ('ProjectB');
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("ProjectB"));
		$this->click("link=ProjectB");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectB."));
		$this->assertTrue($this->isTextPresent("This project has not yet categorized itself"));
		$this->gotoProject ('ProjectB');
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectB."));
		$this->createAndGoto ('ProjectC');
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectC."));
		$this->init ();
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectA."));
	}

	function testTemplateProject()
	{
		$this->populateStandardTemplate('trackers');

		$this->open( ROOT . '/projects/tmpl') ;
		$this->waitForPageToLoad("30000");

		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Tools");
		$this->waitForPageToLoad("30000");
		$this->click("link=Trackers Administration");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Local tracker for UNIXNAME");
		$this->type("//input[@name='description']", "Tracker for PUBLICNAME (UNIXNAME)");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Tracker created successfully"));

		$this->init();
		$this->assertTrue($this->isElementPresent("//a//*[normalize-space(.)='Tracker']"));
		$this->assertTrue($this->isElementPresent("//a//*[normalize-space(.)='Forums']"));
		$this->assertTrue($this->isElementPresent("//a//*[normalize-space(.)='Tasks']"));
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Tracker for ProjectA (projecta)"));

		// Test for fusionforge.org bug #245
		$this->open( ROOT . '/projects/tmpl') ;
		$this->waitForPageToLoad("30000");

		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Tools");
		$this->waitForPageToLoad("30000");
		$this->uncheck("//input[@name='use_tracker']") ;
		$this->click("submit");
		$this->waitForPageToLoad("30000");

		$this->createAndGoto('ProjectB');
	}

	function testEmptyProject()
	{
		// Create an empty project despite the template being full
		$this->populateStandardTemplate('all');

		$this->click("link=My Page");
		$this->waitForPageToLoad("30000");
		$this->click("link=Register Project");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//select[@name='built_from_template']"));
		$this->type("full_name", "ProjectA");
		$this->type("purpose", "This is a simple description for ProjectA");
		$this->type("//textarea[@name='description']", "This is the public description for ProjectA.");
		$this->type("unix_name", "projecta");
		$this->select("//select[@name='built_from_template']", "label=Start from empty project");
		$this->click("//input[@name='scm' and @value='scmsvn']");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Your project has been automatically approved"));
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("ProjectA"));
		$this->click("link=ProjectA");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectA."));
		$this->assertTrue($this->isTextPresent("This project has not yet categorized itself"));
		$this->assertFalse($this->isElementPresent("//a//*[normalize-space(.)='Tracker']"));
		$this->assertFalse($this->isElementPresent("//a//*[normalize-space(.)='Forums']"));
		$this->assertFalse($this->isElementPresent("//a//*[normalize-space(.)='Tasks']"));
	}

	// Test removal of project.
	function testRemoveProject()
	{
		$this->login(FORGE_ADMIN_USERNAME);

		// Create project as a different user
		// Non-regression test for Adacore ticket K720-005
		$this->createUser('toto');

		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->select ("//form[contains(@action,'globalroleedit.php')]//select[@name='role_id']", "label=Forge administrators") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Edit Role']") ;
		$this->waitForPageToLoad("30000");
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "toto") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("toto Lastname"));

		$this->registerProject('testal1','toto');

		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Display Full Project List/Edit Projects");
		$this->waitForPageToLoad("30000");
		$this->click("link=testal1");
		$this->waitForPageToLoad("30000");
		$this->click("link=Permanently Delete Project");
		$this->waitForPageToLoad("30000");
		$this->click("sure");
		$this->click("reallysure");
		$this->click("reallyreallysure");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("testal1"));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

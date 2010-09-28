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

require_once 'func/Testing/SeleniumGforge.php';

class CreateProject extends FForge_SeleniumTestCase
{
	// Simple creation of a project by the admin user and
	// approval of the creation just after.
	// After creation, project is visible on the main page.
	function testSimpleCreate()
	{
		$this->open( ROOT );
		$this->click("link=Log In");
		$this->waitForPageToLoad("30000");
		$this->type("form_loginname", "admin");
		$this->type("form_pw", "myadmin");
		$this->click("login");
		$this->waitForPageToLoad("30000");
		$this->click("link=My Page");
		$this->waitForPageToLoad("30000");
		$this->click("link=Register Project");
		$this->waitForPageToLoad("30000");
		$this->type("full_name", "ProjectA");
		$this->type("purpose", "This is a simple description for project A");
		$this->type("description", "This is the public description for project A.");
		$this->type("unix_name", "projecta");
		$this->click("//input[@name='scm' and @value='scmsvn']");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Your project has been submitted"));
		$this->assertTrue($this->isTextPresent("you will receive notification of their decision and further instructions"));
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Pending projects (new project approval)");
		$this->waitForPageToLoad("30000");
		$this->click("document.forms['approve.projecta'].submit");
		$this->waitForPageToLoad("30000");
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("ProjectA"));
		$this->click("link=ProjectA");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for project A."));
		$this->assertTrue($this->isTextPresent("This project has not yet categorized itself"));
	}

	function testCharsCreateTestCase()
	{
		$this->open( ROOT );
		$this->click("link=Log In");
		$this->waitForPageToLoad("30000");
		$this->type("form_loginname", "admin");
		$this->type("form_pw", "myadmin");
		$this->click("login");
		$this->waitForPageToLoad("30000");
		$this->click("link=My Page");
		$this->waitForPageToLoad("30000");
		$this->click("link=Register Project");
		$this->waitForPageToLoad("30000");
		$this->type("full_name", "Project ' & B");
		$this->type("purpose", "This is a & été simple description for project B");
		$this->type("description", "This is & été the public description for project B.");
		$this->type("unix_name", "projectb");
		$this->click("//input[@name='scm' and @value='scmsvn']");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Your project has been submitted"));
		$this->assertTrue($this->isTextPresent("you will receive notification of their decision and further instructions"));
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Pending projects (new project approval)");
		$this->waitForPageToLoad("30000");
		$this->click("document.forms['approve.projectb'].submit");
		$this->waitForPageToLoad("30000");
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
		$this->click("//div[@id='tabber']/ul/li[5]/a");
		$this->assertFalse($this->isTextPresent("Project ' &amp; B"));
	}

	// Test removal of project.
	// TODO: Test not finished as removal does not work.
	function testRemoveProject()
	{
		$this->createProject('testal1');

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
	}
}
?>

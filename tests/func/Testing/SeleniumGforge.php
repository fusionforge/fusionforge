<?php
/*
 * Copyright (C) 2007-2008 Alain Peyrat <aljeux at free dot fr>
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

$config = getenv('CONFIG_PHP') ? getenv('CONFIG_PHP'): dirname(dirname(__FILE__)).'/config.php';
require_once $config;

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class FForge_SeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
	protected $logged_in = false ;

	protected function setUp()
	{
		if (getenv('SELENIUM_RC_DIR') && getenv('SELENIUM_RC_URL')) {
			$this->captureScreenshotOnFailure = true;
			$this->screenshotPath = getenv('SELENIUM_RC_DIR');
			$this->screenshotUrl = getenv('SELENIUM_RC_URL');
		}
		if (defined('DB_INIT_CMD')) {
			// Reload a fresh database before running this test suite.
			system(DB_INIT_CMD);
		}
		
		$this->setBrowser('*firefox');
		$this->setBrowserUrl(URL);
		$this->setHost(SELENIUM_RC_HOST);
	}

//	protected function waitForPageToLoad($timeout)
//	{
//		parent::waitForPageToLoad($timeout);
//		$this->test->assertFalse($this->isTextPresent("Notice: Undefined variable:"));
//		$this->test->assertFalse($this->isTextPresent("Notice: Undefined index:"));
//		$this->test->assertFalse($this->isTextPresent("Warning: Missing argument"));
//	}

	protected function init() {
		$this->createProject('ProjectA');

		$this->open( ROOT );
		$this->click("link=ProjectA");
		$this->waitForPageToLoad("30000");
	}

	protected function login($username)
	{
		$this->open( ROOT );
		$this->clickAndWait("link=Log In");
		$this->triggeredLogin($username);
	}

	protected function triggeredLogin($username)
	{
		if ($username == 'admin') {
			$password = 'myadmin';
		} else {
			$password = 'password';
		}
		
		$this->type("form_loginname", $username);
		$this->type("form_pw", $password);
		$this->clickAndWait("login");

		$this->logged_in = $username ;
	}

	protected function logout()
	{
//		$this->click("link=Log Out");
		$this->open( ROOT ."/account/logout.php" );
		$this->waitForPageToLoad("30000");

		$this->logged_in = false ;
	}
	
	protected function switchUser($username)
	{
		if ($this->logged_in != $username) {
			$this->logout();
			$this->login($username);
		}
	}

	protected function isLoginRequired()
	{
		return $this->isTextPresent("You've been redirected to this login page") ;
	}

	protected function isPermissionDenied()
	{
		return $this->isTextPresent("Permission denied") ;
	}

	protected function registerProject ($name, $user) {
		$unix_name = strtolower($name);

		$saved_user = $this->logged_in ;
		$this->switchUser ($user) ;

		$this->clickAndWait("link=My Page");
		$this->clickAndWait("link=Register Project");
		$this->type("full_name", $name);
		$this->type("purpose", "This is a simple description for $name");
		$this->type("description", "This is the public description for $name.");
		$this->type("unix_name", $unix_name);
		$this->click("//input[@name='scm' and @value='scmsvn']");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Your project has been submitted"));
		$this->assertTrue($this->isTextPresent("you will receive notification of their decision and further instructions"));

		$this->switchUser ($saved_user) ;
	}	

	protected function approveProject ($name, $user) {
		$unix_name = strtolower($name);

		$saved_user = $this->logged_in ;
		$this->switchUser ($user) ;

		if ($user == 'admin') {
			$this->click("link=Site Admin");
			$this->waitForPageToLoad("30000");
			$this->click("link=Pending projects (new project approval)");
			$this->waitForPageToLoad("30000");
		} else {
			$this->open( ROOT . '/admin/approve-pending.php') ;
			$this->waitForPageToLoad("30000");
		}
		$this->click("document.forms['approve.$unix_name'].submit");
		$this->waitForPageToLoad("60000");
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent($name));
		$this->click("link=$name");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for $name."));
		$this->assertTrue($this->isTextPresent("This project has not yet categorized itself"));

		$this->switchUser ($saved_user) ;
	}

	protected function createProject ($name) {
		$unix_name = strtolower($name);

		$this->switchUser ('admin') ;
		
		// Create a simple project.
		if ((!defined('PROJECTA')) || ($unix_name != "projecta")) {
			$this->registerProject ($name, 'admin') ;
			$this->approveProject ($name, 'admin') ;
		}
		$this->click("link=Home");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent($name));
		$this->click("link=$name");
		$this->waitForPageToLoad("30000");
	}
	
	protected function createUser ($login)
	{
		$this->open( ROOT );
		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Register a New User");
		$this->type("unix_name", $login);
		$this->type("password1", "password");
		$this->type("password2", "password");
		$this->type("firstname", $login);
		$this->type("lastname", "Lastname");
		$this->type("email", $login."@debug.log");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Display Full User List/Edit Users");
		$this->click("//table/tbody/tr/td/a[contains(@href,'useredit.php') and contains(.,'($login)')]/../..//a[contains(@href, 'userlist.php?action=activate&user_id=')]");
		$this->waitForPageToLoad("30000");
	}

	protected function activatePlugin($pluginName) {
		$this->open( ROOT . '/admin/pluginman.php?update='.$pluginName.'&action=deactivate');
		$this->waitForPageToLoad("30000");
		$this->open( ROOT );
		$this->waitForPageToLoad("30000");
		$this->login('admin');
		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Plugin Manager");
		$this->click($pluginName);
		$this->click("//a[contains(@href, \"javascript:change('".ROOT."/admin/pluginman.php?update=$pluginName&action=activate','$pluginName');\")]");
		$this->waitForPageToLoad("30000");
		$this->logout();
	}

	protected function gotoProject($project) {
		$unix_name = strtolower($project);
		
		$this->open( ROOT . '/projects/' . $unix_name) ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for $project."));
	}
}

?>

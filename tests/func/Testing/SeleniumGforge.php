<?php
/*
 * Copyright (C) 2007-2008 Alain Peyrat <aljeux at free dot fr>
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
		$this->reload_nscd();

		$this->setBrowser('*firefox');
		$this->setBrowserUrl(URL);
		$this->setHost(SELENIUM_RC_HOST);
	}

	/**
	 * Method that is called after Selenium actions.
	 *
	 * @param  string $action
	 */
	protected function defaultAssertions($action)
	{
		if ($action == 'waitForPageToLoad') {
			$this->assertElementPresent("//h1");
//			$this->assertFalse($this->isElementPresent("//div[@id='ffErrors']"));
//			$this->assertFalse($this->isTextPresent("PhpWiki Warning:"));
		}
	}

	protected function clickAndWait($link)
	{
		$this->click($link);
		$this->waitForPageToLoad();
	}

	protected function waitForTextPresent($text)
	{
		for ($second = 0; ; $second++) {
			if ($second >= 30) $this->fail("timeout");
			try {
				if ($this->isTextPresent($text)) break;
			} catch (Exception $e) {}
			sleep(1);
		}
	}

	protected function runCommand($cmd)
	{
		system(RUN_COMMAND_PREFIX.$cmd, $ret);
                $this->assertEquals($ret, 0);
	}

	protected function db($sql)
	{
		system("echo \"$sql\" | psql -q -Upostgres ".DB_NAME);
	}

	protected function cron($cmd)
	{
		$this->runCommand(RUN_JOB_PATH."/forge_run_job $cmd");
	}

	protected function cron_for_plugin($cmd, $plugin)
	{
		$this->runCommand(RUN_JOB_PATH."/forge_run_plugin_job $plugin $cmd");
	}

	protected function reload_apache()
	{
		$this->runCommand("service apache2 reload > /dev/null 2>&1 || service httpd reload > /dev/null 2>&1");
		sleep (3); // Give it some time to become available again
	}

	protected function reload_nscd()
	{
		$this->runCommand("service unscd restart > /dev/null 2>&1 || service nscd restart > /dev/null 2>&1 || true");
		sleep (1); // Give it some time to wake up
	}

	protected function init() {
		$this->createAndGoto('ProjectA');
	}

	protected function populateStandardTemplate($what='all')
	{
		if ($what == 'all') {
			$what = array('trackers','tasks','forums');
		} elseif ($what == 'empty') {
			$what = array();
		} elseif (!is_array($what)) {
			$what = array($what) ;
		}
		$this->switchUser (FORGE_ADMIN_USERNAME) ;

		$this->createProject ('Tmpl');

		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Display Full Project List/Edit Projects");
		$this->waitForPageToLoad("30000");
		$this->click("link=Tmpl");
		$this->waitForPageToLoad("30000");
		$this->select ("//select[@name='form_template']", "label=Yes") ;
		$this->click("submit");
		$this->waitForPageToLoad("30000");

		$this->open( ROOT . '/projects/tmpl') ;
		$this->waitForPageToLoad("30000");

		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Tools");
		$this->waitForPageToLoad("30000");
		$this->check("//input[@name='use_forum']") ;
		$this->check("//input[@name='use_tracker']") ;
		$this->check("//input[@name='use_mail']") ;
		$this->check("//input[@name='use_pm']") ;
		$this->check("//input[@name='use_docman']") ;
		$this->check("//input[@name='use_news']") ;
		$this->check("//input[@name='use_frs']") ;
		$this->click("submit");
		$this->waitForPageToLoad("30000");

		if (in_array ('trackers', $what)) {
			$this->click("link=Trackers Administration");
			$this->waitForPageToLoad("30000");
			$this->type("name", "Bugs");
			$this->type("//input[@name='description']", "Tracker for bug reports");
			$this->click("post_changes");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));
			$this->click("link=Bugs");
			$this->waitForPageToLoad("30000");
			$this->click("link=Manage Custom Fields");
			$this->waitForPageToLoad("30000");
			$this->type("name", "URL");
			$this->type("alias", "url");
			$this->click("//input[@name='field_type' and @value=4]");
			$this->click("post_changes");
			$this->waitForPageToLoad("30000");

			$this->click("link=Admin");
			$this->waitForPageToLoad("30000");
			$this->click("link=Tools");
			$this->waitForPageToLoad("30000");
			$this->click("link=Trackers Administration");
			$this->waitForPageToLoad("30000");
			$this->type("name", "Support Requests");
			$this->type("//input[@name='description']", "Tracker for support requests");
			$this->click("post_changes");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));

			$this->type("name", "Patches");
			$this->type("//input[@name='description']", "Proposed changes to code");
			$this->click("post_changes");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));

			$this->type("name", "Feature Requests");
			$this->type("//input[@name='description']", "New features that people want");
			$this->click("post_changes");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));
		}

		if (in_array ('tasks', $what)) {
			$this->click("link=Admin");
			$this->waitForPageToLoad("30000");
			$this->click("link=Tools");
			$this->waitForPageToLoad("30000");
			$this->click("link=Tasks Administration");
			$this->waitForPageToLoad("30000");
			$this->click("link=Add a Subproject");
			$this->waitForPageToLoad("30000");
			$this->type("project_name", "To Do");
			$this->type("//input[@name='description']", "Things we have to do");
			$this->click("submit");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Subproject Inserted"));

			$this->type("project_name", "Next Release");
			$this->type("//input[@name='description']", "Items for our next release");
			$this->click("submit");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Subproject Inserted"));
		}

		if (in_array ('forums', $what)) {
			$this->click("link=Admin");
			$this->waitForPageToLoad("30000");
			$this->click("link=Tools");
			$this->waitForPageToLoad("30000");
			$this->click("link=Forums Admin");
			$this->waitForPageToLoad("30000");

			$this->click("link=Add Forum");
			$this->waitForPageToLoad("30000");
			$this->type("forum_name", "Open-Discussion");
			$this->type("//input[@name='description']", "General Discussion");
			$this->click("submit");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Forum added successfully"));

			$this->type("forum_name", "Help");
			$this->type("//input[@name='description']", "Get Public Help");
			$this->click("submit");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Forum added successfully"));

			$this->type("forum_name", "Developers-Discussion");
			$this->type("//input[@name='description']", "Project Developer Discussion");
			$this->click("submit");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("Forum added successfully"));

			$this->click("link=Forums");
			$this->waitForPageToLoad("30000");
			$this->assertTrue($this->isTextPresent("open-discussion"));
			$this->assertTrue($this->isTextPresent("Get Public Help"));
			$this->assertTrue($this->isTextPresent("Project Developer Discussion"));
		}
	}

	protected function initSvn($project='ProjectA', $user=FORGE_ADMIN_USERNAME)
	{
		// Remove svnroot directory before creating the project.
		$repo = '/var/lib/gforge/chroot/scmrepos/svn/'.strtolower($project);
		if (is_dir($repo)) {
			system("rm -fr $repo");
		}

		$this->init($project, $user);

		// Run manually the cron for creating the svn structure.
		$this->cron("create_scm_repos.php");
	}

	protected function login($username)
	{
		$this->open( ROOT );
		if ($this->isTextPresent('Log Out')) {
			$this->logout();
		}
		$this->clickAndWait("link=Log In");
		$this->triggeredLogin($username);
	}

	protected function triggeredLogin($username)
	{
		if ($username == FORGE_ADMIN_USERNAME) {
			$password = FORGE_ADMIN_PASSWORD;
		} else {
			$password = FORGE_OTHER_PASSWORD;
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

	protected function registerProject ($name, $user, $scm='scmsvn') {
		$unix_name = strtolower($name);

		$saved_user = $this->logged_in ;
		$this->switchUser ($user) ;

		$this->clickAndWait("link=My Page");
		$this->clickAndWait("link=Register Project");
		$this->type("full_name", $name);
		$this->type("purpose", "This is a simple description for $name");
		$this->type("//textarea[@name='description']", "This is the public description for $name.");
		$this->type("unix_name", $unix_name);
		$this->click("//input[@name='scm' and @value='$scm']");

		if ($this->isElementPresent("//select[@name='built_from_template']/option[.='Tmpl']")) {
			$this->select("//select[@name='built_from_template']", "label=Tmpl");
		}

		$this->clickAndWait("submit");
		$this->assertTextPresent("Your project has been automatically approved");

		$this->switchUser ($saved_user) ;
	}

	protected function approveProject ($name, $user) {
		$unix_name = strtolower($name);

		$saved_user = $this->logged_in ;
		$this->switchUser ($user) ;

		$this->open( ROOT . '/admin/approve-pending.php') ;
		$this->waitForPageToLoad("30000");
		$this->click("document.forms['approve.$unix_name'].submit");
		$this->waitForPageToLoad("60000");

		$this->assertTrue($this->isTextPresent("Approving Project: $unix_name"));

		$this->switchUser ($saved_user) ;
	}

	protected function createProject ($name, $scm='scmsvn') {
		$unix_name = strtolower($name);

		$this->switchUser (FORGE_ADMIN_USERNAME) ;

		// Create a simple project.
		if ((!defined('PROJECTA')) || ($unix_name != "projecta")) {
			$this->registerProject ($name, FORGE_ADMIN_USERNAME, $scm) ;
		}
	}

	protected function createAndGoto($project) {
		$this->createProject($project);
		$this->gotoProject($project);
	}

	protected function createUser ($login)
	{
		$this->open( ROOT );
		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Register a New User");
		$this->type("unix_name", $login);
		$this->type("password1", FORGE_OTHER_PASSWORD);
		$this->type("password2", FORGE_OTHER_PASSWORD);
		$this->type("firstname", $login);
		$this->type("lastname", "Lastname");
		$this->type("email", $login."@debug.log");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Display Full User List/Edit Users");
		$this->clickAndWait("//table/tbody/tr/td/a[contains(@href,'useredit.php') and contains(.,'($login)')]/../..//a[contains(@href, 'userlist.php?action=activate&user_id=')]");
	}

	protected function activatePlugin($pluginName) {
		$this->switchUser(FORGE_ADMIN_USERNAME);
		$this->open( ROOT . '/admin/pluginman.php?update='.$pluginName.'&action=deactivate');
		$this->waitForPageToLoad("30000");
		$this->open( ROOT . '/admin/pluginman.php?update='.$pluginName.'&action=activate');
		$this->waitForPageToLoad("30000");
		$this->logout();
	}

	protected function gotoProject($project) {
		$unix_name = strtolower($project);

		$this->open( ROOT . '/projects/' . $unix_name) ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("This is the public description for $project."));
	}

	protected function uploadSshKey () {
		$keys = file(getenv('HOME').'/.ssh/id_rsa.pub');
		$k = $keys[0];
		$this->assertEquals(count($keys), 1);

		$this->clickAndWait("link=My Account");
		$this->clickAndWait("link=Edit Keys");
		$this->type("authorized_key", $k);
		$this->clickAndWait("submit");
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

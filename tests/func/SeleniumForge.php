<?php
/**
 * Copyright (C) 2007-2008 Alain Peyrat <aljeux at free dot fr>
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2013,2019, Franck Villaume - TrivialDev
 * Copyright (C) 2015  Inria (Sylvain Beucler)
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

/**
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

/**
 *Copyright (c) 2010-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

define('FORGE_ADMIN_USERNAME', 'admin');
define('FORGE_ADMIN_PASSWORD', 'my_Admin7');
define('FORGE_OTHER_PASSWORD', 'toto_Tata8');

$config = dirname(__FILE__).'/config.php';
require_once $config;

if (@include_once '/usr/local/share/php/vendor/autoload.php') {
        class PHPUnit_Extensions_SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {}
} else {
        require_once 'PHPUnit/Extensions/Selenium2TestCase.php';
}


class FForge_SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase
{
	public $logged_in = false ;
	public $fixture = 'base';
	public $fixture_loaded = false;
	
	public function setUp() {
		$this->configureSelenium();
		$this->loadCachedFixture();
	}

	public function configureSelenium() {
		if (getenv('SELENIUM_RC_DIR') && getenv('SELENIUM_RC_URL')) {
			$this->captureScreenshotOnFailure = true;
			$this->screenshotPath = getenv('SELENIUM_RC_DIR');
			$this->screenshotUrl = getenv('SELENIUM_RC_URL');
		}
		
		$this->setBrowser('firefox');
		$capabilities = array('acceptInsecureCerts' => true);
		$this->setDesiredCapabilities($capabilities);
		$this->setBrowserUrl(URL);
		$this->setHost(SELENIUM_RC_HOST);

		// Use a sensible default background (instead of Selenium's criminal default to black)
		// (future-proof - https://github.com/giorgiosironi/phpunit-selenium/commit/07e50f74f3782ce8781527653e6c79aeefd94ada)
		$this->screenshotBgColor = '#CCFFDD';
	}

	/**
	 * Load existing fixture.
	 * Mainly used to load the 'base' fixture in tests that don't call loadAndCacheFixture() yet
	 */
	public function loadCachedFixture() {
		$this->fixture_loaded = false;
		$base_cmd = dirname(__FILE__)."/fixtures.sh";
		$ret = 0;
		passthru("$base_cmd --exists {$this->fixture}", $ret); ob_flush();
		if ($ret != 0) {
			# wait until the test starts (with a valid Selenium session) to generate the fixture
		} else {
			passthru("$base_cmd {$this->fixture}", $ret); ob_flush();
			if ($ret != 0)
				die("Error running: $base_cmd {$this->fixture}");
			$this->fixture_loaded = true;
		}
	}

	/**
	 * We can't run the fixture in setUp nor even in assertPreConditions
	 * because the Selenium session isn't started yet >(
	 *
	 * Postponing fixture caching after the test is run.
	 * Call this first in your test.
	 *
	 * Alternatively we could use SQL-based fixtures (rather than
	 * Selenium-based fixtures)
	 */
	public function loadAndCacheFixture() {
		if (!$this->fixture_loaded) {
			$base_cmd = dirname(__FILE__)."/fixtures.sh";
			$ret = 0;
			passthru("$base_cmd base", $ret); ob_flush();

			require(dirname(__FILE__)."/fixtures/{$this->fixture}.php");
			$this->logout();
			$this->fixture_loaded = true;

			passthru("$base_cmd --backup {$this->fixture}", $ret); ob_flush();
		}
	}

	
	public function changeConfig($config) {
		$config_path = rtrim(`forge_get_config config_path`);
		$classname = get_class($this);

		$contents = "";
		foreach ($config as $section => $sv) {
			$contents .= "[$section]\n";
			foreach ($sv as $variable => $value) {
				$contents .= "$variable = $value\n";
			}
		}
		
		file_put_contents("$config_path/config.ini.d/zzz-buildbot-$classname.ini",
				$contents);
	}

	public function openWithOneRetry($url) {
		try {
			$this->open($url);
		}
		catch (Exception $e) {
			$this->open($url);
		}
	}

	public function clickAndWait($link) {
		if (preg_match('/^jquery#/', $link)) {
			$elementid = substr($link, 7);
			$this->execute(array(
					'script' => "jQuery('a[href=\"#$elementid\"').click()",
					'args' => array(),
				));
		} else {
			for ($second = 0; ; $second++) {
				if ($second >= 30) $this->fail("timeout");
				try {
					if (preg_match('/^link=/', $link)) {
						$text = substr($link, 5);
						$myelement = $this->byLinkText($text);
					} else if (preg_match('/^id=/', $link)) {
						$id = substr($link, 3);
						$myelement = $this->byId($id);
					} else if (preg_match('/^css=/', $link)) {
						$css = substr($link, 4);
						$myelement = $this->byCssSelector($css);
					} else if (preg_match('/^\/\/[a-z]/', $link)) {
						$myelement = $this->byXPath($link);
					} else {
						//default case
						$myelement = $this->byName($link);
					}
					if ($myelement->displayed()) break;
				} catch (Exception $e) {}
				sleep(1);
			}
			sleep(1);
			try {
				$myelement->click();
			} catch (Exception $e) {
				$this->url($myelement->attribute('href'));
			}
			sleep(1);
		}
	}

	public function waitForTextPresent($text) {
		for ($second = 0; ; $second++) {
			if ($second >= 30) $this->fail("timeout");
			try {
				if ($this->isTextPresent($text)) break;
			} catch (Exception $e) {}
			sleep(1);
		}
		return true;
	}

	public function runCommand($cmd) {
		system($cmd, $ret);
		$this->assertEquals(0, $ret);
		ob_flush();
	}

	function runCommandTimeout($dir, $command, $env='') {
		# Disable timeout so we have a chance to gdb the stalled process:
		#$cmd = "cd $dir && $env timeout 15s $command";
		$cmd = "cd $dir && $env $command";
		system($cmd, $ret);
		if ($ret == 124) {	# retry once if we get a timeout
			system($cmd, $ret);
		}
		if ($ret == 124) {	# retry a second time if we get a timeout again
			system($cmd, $ret);
		}
		$this->assertEquals(0, $ret);  # Give up
		ob_flush();
	}

	public function cron($cmd) {
		$this->runCommand("forge_run_job $cmd");
	}

	public function cron_for_plugin($cmd, $plugin) {
		$this->runCommand("forge_run_plugin_job $plugin $cmd");
	}

	/**
	 * Execute pending system tasks
	 */
	public function waitSystasks() {
		$this->runCommand(dirname(__FILE__).'/../../src/bin/systasks_wait_until_empty.php');
	}

	public function init() {
		$this->createAndGoto('ProjectA');
	}

	public function populateStandardTemplate($what='all') {
		if ($what == 'all') {
			$what = array('trackers','tasks','forums');
		} elseif ($what == 'empty') {
			$what = array();
		} elseif (!is_array($what)) {
			$what = array($what) ;
		}
		$this->switchUser (FORGE_ADMIN_USERNAME) ;

		$this->createProject ('Tmpl');

		$this->url(ROOT."/admin/");
		$this->clickAndWait("link=Display Full Project List/Edit Projects");
		$this->clickAndWait("link=Tmpl");
		$this->select($this->byXPath("//select[@name='form_template']"))->selectOptionByLabel("Yes");
		$this->clickAndWait("submit");

		$this->open( ROOT . '/projects/tmpl') ;
		$this->waitForPageToLoad();

		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->check("//input[@name='use_forum']") ;
		$this->check("//input[@name='use_tracker']") ;
		$this->check("//input[@name='use_mail']") ;
		$this->check("//input[@name='use_pm']") ;
		$this->check("//input[@name='use_docman']") ;
		$this->check("//input[@name='use_news']") ;
		$this->check("//input[@name='use_frs']") ;
		$this->clickAndWait("submit");

		if (in_array ('trackers', $what)) {
			$this->clickAndWait("link=Trackers Administration");
			$this->type("name", "Bugs");
			$this->type("//input[@name='description']", "Tracker for bug reports");
			$this->clickAndWait("post_changes");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));
			$this->clickAndWait("link=Bugs");
			$this->clickAndWait("link=Manage Custom Fields");
			$this->type("name", "URL");
			$this->type("alias", "url");
			$this->clickAndWait("//input[@name='field_type' and @value=4]");
			$this->clickAndWait("post_changes");

			$this->clickAndWait("link=Admin");
			$this->clickAndWait("link=Tools");
			$this->clickAndWait("link=Trackers Administration");
			$this->type("name", "Support Requests");
			$this->type("//input[@name='description']", "Tracker for support requests");
			$this->clickAndWait("post_changes");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));

			$this->type("name", "Patches");
			$this->type("//input[@name='description']", "Proposed changes to code");
			$this->clickAndWait("post_changes");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));

			$this->type("name", "Feature Requests");
			$this->type("//input[@name='description']", "New features that people want");
			$this->clickAndWait("post_changes");
			$this->assertTrue($this->isTextPresent("Tracker created successfully"));
		}

		if (in_array ('tasks', $what)) {
			$this->clickAndWait("link=Admin");
			$this->clickAndWait("link=Tools");
			$this->clickAndWait("link=Tasks Administration");
			$this->clickAndWait("link=Add a Subproject");
			$this->type("project_name", "To Do");
			$this->type("//input[@name='description']", "Things we have to do");
			$this->clickAndWait("submit");
			$this->assertTrue($this->isTextPresent("Subproject Inserted"));

			$this->type("project_name", "Next Release");
			$this->type("//input[@name='description']", "Items for our next release");
			$this->clickAndWait("submit");
			$this->assertTrue($this->isTextPresent("Subproject Inserted"));
		}

		if (in_array ('forums', $what)) {
			$this->clickAndWait("link=Admin");
			$this->clickAndWait("link=Tools");
			$this->clickAndWait("link=Forums Administration");

			$this->clickAndWait("link=Add Forum");
			$this->type("forum_name", "Open-Discussion");
			$this->type("//input[@name='description']", "General Discussion");
			$this->clickAndWait("submit");
			$this->assertTrue($this->isTextPresent("Forum added successfully"));

			$this->type("forum_name", "Help");
			$this->type("//input[@name='description']", "Get Public Help");
			$this->clickAndWait("submit");
			$this->assertTrue($this->isTextPresent("Forum added successfully"));

			$this->type("forum_name", "Developers-Discussion");
			$this->type("//input[@name='description']", "Project Developer Discussion");
			$this->clickAndWait("submit");
			$this->assertTrue($this->isTextPresent("Forum added successfully"));

			$this->clickAndWait("link=Forums");
			$this->assertTrue($this->isTextPresent("open-discussion"));
			$this->assertTrue($this->isTextPresent("Get Public Help"));
			$this->assertTrue($this->isTextPresent("Project Developer Discussion"));
		}
	}

	public function login($username) {
		$this->open( ROOT );
		if ($this->isTextPresent('Log Out')) {
			$this->logout();
		}
		$this->clickAndWait("link=Log In");
		$this->triggeredLogin($username);
	}

	public function triggeredLogin($username) {
		if ($username == FORGE_ADMIN_USERNAME) {
			$password = FORGE_ADMIN_PASSWORD;
		} else {
			$password = FORGE_OTHER_PASSWORD;
		}

		$this->type("form_loginname", $username);
		$this->type("form_pw", $password);
		$this->clickAndWait("login");

		$this->logged_in = $username;
	}

	public function logout() {
		$this->open( ROOT ."/account/logout.php" );
		$this->logged_in = false;
	}

	public function switchUser($username) {
		if ($this->logged_in != $username) {
			$this->logout();
			$this->login($username);
		}
	}

	public function isLoginRequired() {
		return $this->isTextPresent("You've been redirected to this login page") ;
	}

	public function isPermissionDenied() {
		return $this->isTextPresent("Permission denied") ;
	}

	public function registerProject ($name, $user, $scm='scmsvn') {
		$unix_name = strtolower($name);

		$saved_user = $this->logged_in ;
		$this->switchUser ($user) ;

		$this->url(ROOT."/my/");
		$this->clickAndWait("link=Register Project");
		$this->type("full_name", $name);
		$this->type("purpose", "This is a simple description for $name");
		$this->type("//textarea[@name='description']", "This is the public description for $name.");
		$this->type("unix_name", $unix_name);
		$this->clickAndWait("//input[@name='scm' and @value='$scm']");

		try {
			$this->select($this->byXPath("//select[@name='built_from_template']"))->selectOptionByLabel("Tmpl");
		} catch (Exception $e) {}

		$this->clickAndWait("submit");
		$this->assertTextPresent("Your project has been automatically approved");

		$this->switchUser ($saved_user) ;
	}

	public function approveProject ($name, $user) {
		$unix_name = strtolower($name);

		$saved_user = $this->logged_in ;
		$this->switchUser ($user) ;

		$this->open( ROOT . '/admin/approve-pending.php') ;
		$this->waitForPageToLoad();
		$this->clickAndWait("document.forms['approve.$unix_name'].submit");

		$this->assertTrue($this->isTextPresent("Approving Project: $unix_name"));

		$this->switchUser ($saved_user) ;
	}

	public function createProject ($name, $scm='scmsvn') {
		$unix_name = strtolower($name);

		$this->switchUser (FORGE_ADMIN_USERNAME) ;

		// Create a simple project.
		$this->registerProject($name, FORGE_ADMIN_USERNAME, $scm);
	}

	public function createAndGoto($project) {
		$this->createProject($project);
		$this->gotoProject($project);
	}

	public function createUser ($login) {
		$this->switchUser(FORGE_ADMIN_USERNAME);
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

	public function activatePlugin($pluginName) {
		$this->switchUser(FORGE_ADMIN_USERNAME);
		$this->open( ROOT . '/admin/pluginman.php?update='.$pluginName.'&action=deactivate');
		$this->waitForPageToLoad();
		$this->open( ROOT . '/admin/pluginman.php?update='.$pluginName.'&action=activate');
		$this->waitForPageToLoad();
		//$this->logout();
	}

	public function gotoProject($project) {
		$unix_name = strtolower($project);

		$this->open( ROOT . '/projects/' . $unix_name) ;
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("This is the public description for $project."));
	}

	public function uploadSshKey () {
		// Prepare client config
		$sshdir = getenv('HOME') . '/.ssh';
		if (!file_exists($sshdir)) {
			mkdir($sshdir);
			chmod($sshdir, 0700);
		}
		$config = $sshdir . '/config';
		if (!file_exists($config) or
		    // Avoid OpenSSH host fingerprint prompt
		    count(preg_grep('/StrictHostKeyChecking/', file($config))) == 0) {
			$f = fopen($config, 'a');
			fwrite($f, 'StrictHostKeyChecking no');
			fclose($f);
		}
		chmod($sshdir . '/config', 0600);

		// Generate user keys
		$privkey = $sshdir . '/id_rsa';
		$pubkey  = $sshdir . '/id_rsa.pub';
		if (!file_exists($pubkey)) {
			system("ssh-keygen -N '' -f $privkey");
		}

		// Upload keys to the web interface
		$keys = file($pubkey);
		$k = $keys[0];
		$this->assertEquals(1, count($keys));
		$this->clickAndWait("link=My Account");
		$this->clickAndWait("link=Edit Keys");
		$this->type("authorized_key", $k);
		$this->clickAndWait("submit");
	}

	public function skip_test($msg) {
		$this->captureScreenshotOnFailure = false;
		$this->markTestSkipped($msg);
	}

	public function skip_on_rpm_installs($msg='Skipping on installations from RPM') {
		if (INSTALL_METHOD == 'rpm') {
			$this->skip_test($msg);
		}
	}

	public function skip_on_deb_installs($msg='Skipping on installations from *.deb') {
		if (INSTALL_METHOD == 'deb') {
			$this->skip_test($msg);
		}
	}

	public function skip_on_src_installs($msg='Skipping on installations from source') {
		if (INSTALL_METHOD == 'src') {
			$this->skip_test($msg);
		}
	}

	public function skip_on_centos($msg='Skipping on CentOS platforms') {
		if (INSTALL_OS == 'centos') {
			$this->skip_test($msg);
		}
	}

	public function skip_on_debian($msg='Skipping on Debian platforms') {
		if (INSTALL_OS == 'debian') {
			$this->skip_test($msg);
		}
	}

	/**
	 * add PHP wrappers for SeleniumTestCase compatibility
	 */

	function open($url) {
		$this->url($url);
	}

	function isTextPresent($text) {
		$elementArray = $this->execute(array(
				'script' => 'return document.body;',
				'args' => array(),
			));
		$element = $this->elementFromResponseValue($elementArray);
		if (strpos($element->text(), $text) === false) {
			return false;
		}
		return true;
	}

	function isElementPresent($element) {
		try {
			if (preg_match('/^\/\/[a-z]/', $element)) {
				if ($this->byXPath($element) instanceof PHPUnit_Extensions_Selenium2TestCase_Element) {
					return true;
				}
			} elseif (preg_match('/^link=/', $element)) {
				if ($this->byLinkText(substr($element, 5)) instanceof PHPUnit_Extensions_Selenium2TestCase_Element) {
					return true;
				}
			}
		} catch (Exception $e) {}
		return false;
	}

	function getLocation() {
		return $this->execute(array(
				'script' => 'return window.location.href;',
				'args' => array(),
			));
	}

	function type($name, $value) {
		if (preg_match('/^\/\/[a-z]/', $name)) {
			$this->byXPath($name)->clear();
			$this->byXPath($name)->value($value);
		} else if (preg_match('/^id=/', $name)) {
			$this->byId(substr($name, 3))->clear();
			$this->byId(substr($name, 3))->value($value);
		} else {
			$this->byName($name)->clear();
			$this->byName($name)->value($value);
		}
	}

	function waitForPageToLoad($integer = 30000) {
		//do we need to do something???
		$this->pause($integer);
	}

	function pause($integer = 10000) {
		usleep($integer);
	}

	function assertTextPresent($string) {
		return $this->assertTrue($this->waitForTextPresent($string));
	}

	function check($string) {
		$myelement = $this->byXPath($string);
		if (!$myelement->attribute('checked')) {
			$myelement->click();
		}
	}

	function uncheck($string) {
		$myelement = $this->byXPath($string);
		if ($myelement->attribute('checked')) {
			$myelement->click();
		}
	}

	function goBack() {
		$this->execute(array(
				'script' => 'window.history.back();',
				'args' => array(),
			));
	}

	function getText($string) {
		if (preg_match('/^\/\/[a-z]/', $string)) {
			return $this->byXPath($string)->text();
		}
	}

	function getValue($string) {
		if (preg_match('/^\/\/[a-z]/', $string)) {
			return $this->byXPath($string)->attribute('value');
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

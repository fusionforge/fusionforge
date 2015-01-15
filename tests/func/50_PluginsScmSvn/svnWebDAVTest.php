<?php
/**
 * Copyright (C) 2012 Roland Mas
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class ScmSvnWebDAVTest extends FForge_SeleniumTestCase
{
	// Retry Git/SVN commands over HTTP if they timeout at first (?)
	function runCommandTimeout($dir, $command) {
		system("cd $dir && timeout 15s $command", $ret);
		if ($ret == 124) {	# retry once if we get a timeout
			system("cd $dir && timeout 15s $command", $ret);
		}
		ob_flush();
		$this->assertEquals($ret, 0);
	}

	function testScmSvnWebDAV()
	{
        $this->changeConfig("[scmsvn]\nuse_ssl = no\n");

		$this->activatePlugin('scmsvn');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmengine[]' and @value='scmsvn']");
		$this->clickAndWait("submit");

		// Run the cronjob to create repositories
		$this->cron("scm/create_scm_repos.php");
		$this->cron("shell/homedirs.php");
		$this->reload_apache();
		$this->reload_nscd();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'svn checkout --username ".FORGE_ADMIN_USERNAME." http')]");
		$p = preg_replace(",^svn checkout --username ".FORGE_ADMIN_USERNAME." ,", "", $p);

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/svnTest.XXXXXX");
		$auth = "--username ".FORGE_ADMIN_USERNAME." --password ".FORGE_ADMIN_PASSWORD;
		$globalopts = "--trust-server-cert --non-interactive";
		$this->runCommandTimeout($t, "svn checkout $globalopts $auth $p projecta");
		sleep(2);
		$this->runCommand("echo 'this is a simple text' > $t/projecta/mytext.txt");
		$this->runCommandTimeout("$t/projecta", "svn add mytext.txt");
		$this->runCommandTimeout("$t/projecta", "svn commit $globalopts $auth -m'Adding file'");
		sleep(2);
		$this->runCommand("echo 'another simple text' >> $t/projecta/mytext.txt");
		$this->runCommandTimeout("$t/projecta", "svn commit $globalopts $auth -m'Modifying file'");
		sleep(2);

		// Check that the changes appear in svnweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Subversion Repository");
		$this->selectFrame("id=scmsvn_iframe");
		$this->assertTextPresent("Modifying file");
		$this->assertTextNotPresent("Adding file");

		system("rm -fr $t");
	}

	/**
	 * Method that is called after Selenium actions.
	 *
	 * @param  string $action
	 */
	protected function defaultAssertions($action)
	{
		if ($action == 'waitForPageToLoad') {
			$this->assertTrue($this->isElementPresent("//h1")
					  || $this->isElementPresent("//.[@class='page_footer']"));
		}
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

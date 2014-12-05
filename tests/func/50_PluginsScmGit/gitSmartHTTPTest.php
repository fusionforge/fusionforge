<?php
/*
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

class ScmGitSmartHTTPTest extends FForge_SeleniumTestCase
{
	function testScmGitSmartHTTP()
	{

        $this->changeConfig("[core]\nuse_ssl = no\n");

		$this->activatePlugin('scmgit');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmengine[]' and @value='scmgit']");
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
		$p = $this->getText("//tt[contains(.,'git clone http') and contains(.,'".FORGE_ADMIN_USERNAME."@')]");
		$p = preg_replace(",^git clone ,", "", $p);
		$p = preg_replace(",@,", ":".FORGE_ADMIN_PASSWORD."@", $p);
        $log = "2> /var/log/git.stderr > /var/log/git.stdout";
        $timeout = "timeout 15s";

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		system("cd $t && GIT_SSL_NO_VERIFY=true $timeout git clone --quiet $p $log", $ret);
		$this->assertEquals($ret, 0);

		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && $timeout git add mytext.txt $log && $timeout git commit --quiet -a -m'Adding file' $log", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file' $log", $ret);
		$this->assertEquals($ret, 0);

		system("cd $t/projecta && GIT_SSL_NO_VERIFY=true $timeout git push --quiet --all $log", $ret);
		$this->assertEquals($ret, 0);

		// Check that the changes appear in gitweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Git Repository");
        $this->selectFrame("id=scmgit_iframe");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");

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
?>

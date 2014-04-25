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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumGforge.php';

class ScmGitSSHTest extends FForge_SeleniumTestCase
{
	function testScmGitSSH()
	{
		$this->activatePlugin('scmgit');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmradio' and @value='scmgit']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();
	    
		// Run the cronjob to create repositories
		$this->cron("create_scm_repos.php");
		$this->cron("homedirs.php");
		$this->cron("ssh_create.php");
		$this->reload_nscd();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'git clone git+ssh')]");
		$p = preg_replace(",^git clone ,", "", $p);

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		system("cd $t && git clone --quiet $p", $ret);
		$this->assertEquals($ret, 0);

		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && git add mytext.txt && git commit --quiet -a -m'Adding file'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file'", $ret);
		$this->assertEquals($ret, 0);

		system("cd $t/projecta && git push --quiet --all", $ret);
		$this->assertEquals($ret, 0);

		// Check that the changes appear in gitweb
		$this->open(ROOT.'/plugins/scmgit/cgi-bin/gitweb.cgi?a=project_list;pf=projecta');
		$this->waitForPageToLoad();
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->click("link=projecta/projecta.git");
		$this->waitForPageToLoad();
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

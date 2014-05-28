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

class ScmSvnWebDAVTest extends FForge_SeleniumTestCase
{
	function testScmSvnWebDAV()
	{
		$this->activatePlugin('scmsvn');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmradio' and @value='scmsvn']");
		$this->clickAndWait("submit");
	    
		// Run the cronjob to create repositories
		$this->cron("create_scm_repos.php");
		$this->cron("homedirs.php");
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
		system("cd $t && svn checkout $auth $p projecta", $ret);
		$this->assertEquals($ret, 0);
		sleep(2);
		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && svn add mytext.txt && svn commit $auth -m'Adding file'", $ret);
		$this->assertEquals($ret, 0);
		sleep(2);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && svn commit $auth -m'Modifying file'", $ret);
		$this->assertEquals($ret, 0);
		sleep(2);

		// Check that the changes appear in svnweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Subversion Repository");
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
?>

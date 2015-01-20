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

class ScmSvnSSHTest extends FForge_SeleniumTestCase
{
	function testScmSvnSSH()
	{
		$this->changeConfig("[scmsvn]\nuse_ssh = yes\nuse_dav = no\n");

		$this->activatePlugin('scmsvn');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmsvn']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();

		// Run the cronjob to create repositories
		$this->waitSystasks();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'svn checkout svn+ssh')]");
		$p = preg_replace(",^svn checkout ,", "", $p);

		// Create a local checkout, commit stuff
		$t = exec("mktemp -d /tmp/svnTest.XXXXXX");
		system("cd $t && svn checkout $p projecta", $ret);
		$this->assertEquals(0, $ret);

		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && svn add mytext.txt && svn commit -m'Adding file'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && svn commit -m'Modifying file'", $ret);
		$this->assertEquals(0, $ret);

		// Check that the changes appear in svnweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Subversion Repository");
		$this->selectFrame("id=scmsvn_iframe");
		$this->assertTextPresent("Modifying file");
		$this->assertTextNotPresent("Adding file");
		$this->selectFrame("relative=top");

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

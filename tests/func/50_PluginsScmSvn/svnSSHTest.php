<?php
/**
 * Copyright (C) 2012 Roland Mas
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

require_once dirname(dirname(__FILE__)).'/SeleniumForge.php';

class ScmSvnSSHTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testScmSvnSSH()
	{
		$this->changeConfig(array("scmsvn" => array("use_ssh" => "yes",
		                                            "use_dav" => "no")));

		$this->loadAndCacheFixture();

		$this->activatePlugin('scmsvn');

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
		$p = $this->getText("//span[contains(.,'svn checkout svn+ssh')]");
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
}

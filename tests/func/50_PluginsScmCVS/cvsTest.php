<?php
/**
 * Copyright (C) 2012,2016 Roland Mas
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

class ScmCvsSSHTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';
	
	function testScmCvs()
	{
		$this->loadAndCacheFixture();

		$this->activatePlugin('scmcvs');

		$this->createProject('ProjectB','scmcvs');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectB");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmcvs']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();

		// Run the cronjob to create repositories
		$this->waitSystasks();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectB");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'cvs -d :ext:')]");
		$p = preg_replace(",^cvs -d ,", "", $p);
		$p = preg_replace(", checkout.*,", "", $p);

		// Create a local checkout, commit stuff
		$t = exec("mktemp -d /tmp/cvsTest.XXXXXX");
		system("echo cvs -d $p checkout .", $ret);
		system("cd $t && cvs -d $p checkout .", $ret);
		$this->assertEquals(0, $ret);

		system("echo 'this is a simple text' > $t/mytext.txt");
		system("cd $t && cvs add mytext.txt && cvs commit -m'Adding file'", $ret);
		system("echo 'another simple text' >> $t/mytext.txt");
		system("cd $t && cvs commit -m'Modifying file'", $ret);
		$this->assertEquals(0, $ret);

		// Check that the changes appear in cvsweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectB");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse CVS Repository");
		$this->selectFrame("id=scmcvs_iframe");
		$this->assertTextPresent("Modifying file");
		$this->assertTextNotPresent("Adding file");
		$this->selectFrame("relative=top");

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectB");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,' login')]");
		$p = preg_replace(",^cvs -d ,", "", $p);
		$p = preg_replace(", login.*,", "", $p);

		// Create a local checkout, commit stuff
		$t = exec("mktemp -d /tmp/cvsTest.XXXXXX");
		system("echo cvs -d $p checkout .", $ret);
		system("cd $t && cvs -d $p checkout .", $ret);
		$this->assertEquals(0, $ret);

		system("grep 'simple text' $t/mytext.txt");
		$this->assertEquals(0, $ret);

		system("rm -fr $t");
	}
}

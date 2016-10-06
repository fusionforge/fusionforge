<?php
/*
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

class ScmGitSSHTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testScmGitSSH()
	{
		$this->loadAndCacheFixture();

		$this->activatePlugin('scmgit');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();

		// Run the cronjob to create repositories
		$this->waitSystasks();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'git clone git+ssh')]");
		$p = preg_replace(",^git clone ,", "", $p);

		// Create a local clone, add stuff, push it to the repo
		system("git config --global core.askpass ''", $ret);
		$this->assertEquals(0, $ret);
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		system("cd $t && git clone --quiet $p", $ret);
		$this->assertEquals(0, $ret);

		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && git add mytext.txt && git commit --quiet -a -m'Adding file'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file'", $ret);
		$this->assertEquals(0, $ret);

		system("cd $t/projecta && git push --quiet --all", $ret);
		$this->assertEquals(0, $ret);

		// Check that the changes appear in gitweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse main git repository");
		$this->selectFrame("id=scmgit_iframe");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");
		$this->selectFrame("relative=top");

		// Check that the changes appear in the global activity page

		$this->activatePlugin('globalactivity');

		$this->open(ROOT.'/plugins/globalactivity/');
		$this->select("//select[@name='show[]']","label=Git Commits");
		$this->clickAndWait("submit");
		$this->assertTextPresent("scm commit: Modifying file");
		$this->assertTextPresent("scm commit: Adding file");

		system("rm -fr $t");
	}
}

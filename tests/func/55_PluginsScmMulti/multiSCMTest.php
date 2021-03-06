<?php
/**
 * Copyright (C) 2012, Roland Mas
 * Copyright (C) 2015, Inria (Sylvain Beucler)
 * Copyright 2019, Franck Villaume -TrivialDev
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

class multiSCMTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testMultiSCM()
	{
		$this->loadAndCacheFixture();

		$this->changeConfig(array("core" => array("allow_multiple_scm" => "yes")));

		$this->activatePlugin('scmsvn');
		$this->activatePlugin('scmgit');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmsvn']");
		$this->check("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();

		// Run the cronjob to create repositories
		$this->waitSystasks();

		// Check Subversion checkout/commit
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("jquery#tabber-scmsvn");
		$p = $this->getText("//kbd[contains(.,'svn checkout svn+ssh')]");
		$p = preg_replace(",^svn checkout ,", "", $p);
		$t = exec("mktemp -d /tmp/svnTest.XXXXXX");
		system("cd $t && svn checkout $p projecta", $ret);
		$this->assertEquals(0, $ret);
		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && svn add mytext.txt && svn commit -m'Adding file in Subversion'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && svn commit -m'Modifying file in Subversion'", $ret);
		$this->assertEquals(0, $ret);
		system("rm -fr $t");

		// Check Git clone/commit/push
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("jquery#tabber-scmgit");
		$p = $this->getText("//kbd[contains(.,'git clone git+ssh')]");
		$p = preg_replace(",^git clone ,", "", $p);
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		system("cd $t && git clone --quiet $p", $ret);
		$this->assertEquals(0, $ret);
		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && git add mytext.txt && git commit --quiet -a -m'Adding file in Git'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file in Git'", $ret);
		$this->assertEquals(0, $ret);
		system("cd $t/projecta && git push --quiet --all", $ret);
		$this->assertEquals(0, $ret);
		system("rm -fr $t");

		// Check Subversion browse
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("jquery#tabber-scmsvn");
		$this->clickAndWait("link=Browse Subversion Repository");
		$this->selectFrame("id=scmsvn_iframe");
		$this->assertTextPresent("trunk");
		$this->assertTextPresent("Init");
		$this->assertTextPresent("Modifying file in Subversion");
		$this->selectFrame("relative=top");

		// Check Git browse
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("jquery#tabber-scmgit");
		$this->clickAndWait("link=Browse main git repository");
		$this->selectFrame("id=scmgit_iframe");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file in Git");
		$this->assertTextPresent("Adding file in Git");
		$this->selectFrame("relative=top");
	}
}

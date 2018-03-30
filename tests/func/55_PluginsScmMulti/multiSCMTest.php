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

class multiSCMTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testMultiSCM()
	{
		$this->skip_on_src_installs();

		$this->loadAndCacheFixture();

		$this->changeConfig(array("core" => array("allow_multiple_scm" => "yes")));

		$this->activatePlugin('scmsvn');
		$this->activatePlugin('scmgit');
		$this->activatePlugin('scmbzr');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmsvn']");
		$this->check("//input[@name='scmengine[]' and @value='scmgit']");
		$this->check("//input[@name='scmengine[]' and @value='scmbzr']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();

		// Run the cronjob to create repositories
		$this->waitSystasks();

        // Check Bazaar commit/push
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//kbd[contains(.,'bzr checkout bzr+ssh')]");
		$p = preg_replace(",^bzr checkout ,", "", $p);
		$p = preg_replace(",/branchname$,", "", $p);
		$t = exec("mktemp -d /tmp/bzrTest.XXXXXX");
		system("bzr whoami 'admin <admin@localhost.localdomain>'");
		system("cd $t && bzr init --quiet trunk >/dev/null", $ret);
		$this->assertEquals(0, $ret);
		system("echo 'this is a simple text' > $t/trunk/mytext.txt");
		system("cd $t/trunk && bzr add --quiet && bzr commit -m'Adding file in Bazaar' --quiet", $ret);
		system("echo 'another simple text' >> $t/trunk/mytext.txt");
		system("cd $t/trunk && bzr add --quiet && bzr commit -m'Modifying file in Bazaar' --quiet", $ret);
		$this->assertEquals(0, $ret);
		system("cd $t/trunk && bzr push --quiet $p/trunk", $ret);
		$this->assertEquals(0, $ret);
		system("rm -fr $t");

        // Check Subversion checkout/commit
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
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

        // Check Bazaar browse
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Bazaar Repository");
		$this->selectFrame("id=scmbzr_iframe");
		$this->assertTextPresent("Browsing (root)");
		$this->clickAndWait("link=projecta");
		$this->assertTextPresent("Browsing (root)/projecta");
		$this->assertTextPresent("trunk");
		$this->clickAndWait("link=trunk");
		$this->assertTextPresent("Modifying file in Bazaar");
		$this->assertTextNotPresent("Adding file in Bazaar");
		$this->clickAndWait("link=Changes");
		$this->assertTextPresent("Modifying file in Bazaar");
		$this->assertTextPresent("Adding file in Bazaar");
		$this->selectFrame("relative=top");

        // Check Subversion browse
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
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

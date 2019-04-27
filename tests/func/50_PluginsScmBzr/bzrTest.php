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

function mysystem($cmd, &$ret=null) {
	print "Running: $cmd\n";
	ob_flush();
	system($cmd, $ret);
}

class ScmBzrTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testScmBzr()
	{
		$this->skip_on_src_installs();
		$this->skip_on_deb_installs();

		$this->loadAndCacheFixture();

		$this->activatePlugin('scmbzr');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmbzr']");
		$this->clickAndWait("submit");

		$this->uploadSshKey();

		// Run the cronjob to create repositories
		$this->waitSystasks();
		// Give some time to WSGI/loggerhead to start-up
		// (avoids "Service Unavailable" error page)
		sleep(5);

		// Check that the repo is present and Loggerhead shows it (even if empty)
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Bazaar Repository");
		$this->selectFrame("id=scmbzr_iframe");
		$this->assertTextPresent("Browsing (root)");
		$this->clickAndWait("link=projecta");
		$this->assertTextPresent("Browsing (root)/projecta");
		$this->selectFrame("relative=top");

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//kbd[contains(.,'bzr checkout bzr+ssh')]");
		$p = preg_replace(",^bzr checkout ,", "", $p);
		$p = preg_replace(",/branchname$,", "", $p);

		// Create a local branch, push it to the repo
		mysystem("bzr whoami 'admin <admin@admin.tld>'");
		$t = exec("mktemp -d /tmp/bzrTest.XXXXXX");
		mysystem("cd $t && bzr init --quiet trunk >/dev/null", $ret);
		$this->assertEquals(0, $ret);

		mysystem("echo 'this is a simple text' > $t/trunk/mytext.txt");
		mysystem("cd $t/trunk && bzr add --quiet && bzr commit -m'Adding file' --quiet", $ret);
		mysystem("echo 'another simple text' >> $t/trunk/mytext.txt");
		mysystem("cd $t/trunk && bzr add --quiet && bzr commit -m'Modifying file' --quiet", $ret);
		$this->assertEquals(0, $ret);

		mysystem("cd $t/trunk && bzr push --quiet $p/trunk", $ret);
		$this->assertEquals(0, $ret);

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
		$this->assertTextPresent("Modifying file");
		$this->assertTextNotPresent("Adding file");
		$this->clickAndWait("link=Changes");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");
		$this->selectFrame("relative=top");

		mysystem("rm -fr $t");
	}
}

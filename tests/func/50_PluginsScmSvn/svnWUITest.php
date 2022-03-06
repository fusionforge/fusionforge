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

class ScmSvnWUITest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testScmSvnWUI()
	{
		$this->skip_on_debian_11();
		$this->skip_on_centos_8();
		$this->loadAndCacheFixture();

		$this->changeConfig(array("scmsvn" => array("use_ssh" => "no",
		                                            "use_dav" => "yes")));

		$this->activatePlugin('scmsvn');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmsvn']");
		$this->clickAndWait("submit");

		// Run the cronjob to create repositories
		$this->waitSystasks();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse Subversion Repository");
		$this->selectFrame("id=scmsvn_iframe");
		$this->assertTextPresent("trunk");
		$this->assertTextPresent("Init");
		$this->selectFrame("relative=top");
	}
}

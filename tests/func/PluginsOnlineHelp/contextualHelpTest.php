<?php
/*
 * Copyright (C) 2010 Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The test suite ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumGforge.php';

class ContextualHelp extends FForge_SeleniumTestCase
{
	function testContextualHelp()
	{
		$this->init();
		$this->activatePlugin('online_help');
		$this->login ('admin');

		$this->checkWindow("link=Home",    "User Guide");

		$this->gotoProject ("ProjectA");
		$this->checkWindow("link=Forums",  "Creating a new forum");
		$this->checkWindow("link=Tracker", "What is the Tracker?");
		$this->checkWindow("link=Lists",   "Mailing Lists");
		$this->checkWindow("link=Tasks",   "Inserting a new Task");
		$this->checkWindow("link=Docs",    "Submit new documentation");
		$this->checkWindow("link=Surveys", "Administering survey questions");
		$this->checkWindow("link=News",    "Inserting a news item");
		//      $this->checkWindow("link=SCM",     "Source Code menu");
		$this->checkWindow("link=Files",   "Managing packages and releases via CLI");
	}

	function checkWindow($action, $text)
	{
		$this->clickAndWait($action);
		$this->click("link=Get Help");
		sleep(1);
		$this->waitForPopUp("HelpWindow", "30000");
		$this->selectWindow("name=HelpWindow");
		$this->assertTextPresent($text);
		$this->close();
		$this->selectWindow("");
	}
}
?>

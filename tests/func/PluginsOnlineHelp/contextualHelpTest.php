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

        $this->clickAndWait("link=Home");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("User Guide");

        $this->close();
        $this->selectWindow("");
        $this->gotoProject ("ProjectA") ;
        $this->clickAndWait("link=Forums");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Creating a new forum");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=Tracker");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("What is the Tracker?");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=Lists");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Mailing Lists");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=Tasks");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Inserting a new Task");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=Docs");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Submit new documentation");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=Surveys");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Administering survey questions");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=News");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Inserting a news item");

//        $this->close();
//        $this->selectWindow("");
//        $this->clickAndWait("link=SCM");
//        $this->click("link=Get Help");
//        $this->waitForPopUp("HelpWindow", "30000");
//        $this->selectWindow("name=HelpWindow");
//        $this->assertTextPresent("Source Code menu");

        $this->close();
        $this->selectWindow("");
        $this->clickAndWait("link=Files");
        $this->click("link=Get Help");
        $this->waitForPopUp("HelpWindow", "30000");
        $this->selectWindow("name=HelpWindow");
        $this->assertTextPresent("Managing packages and releases via CLI");
        $this->close();
        $this->selectWindow("");
    }
}
?>

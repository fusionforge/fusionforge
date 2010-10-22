<?php
/*
 * Copyright (C) 2008 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2009 - 2010 Alain Peyrat, Alcatel-Lucent
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
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

class CreateTrackerWorkflow extends FForge_SeleniumTestCase
{
	function testWorkflow()
	{
		$this->createProject('ProjectA');
			
		// Testing extra-fields
		$this->open( ROOT );
		$this->click("link=ProjectA");
		$this->waitForPageToLoad("30000");
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(@href, '".ROOT. "/tracker/admin/?group_id=6&atid=101')]");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Custom Fields");
		$this->waitForPageToLoad("30000");
		$this->type("name", "MyStatus");
		$this->type("alias", "mystatus");
		$this->click("document.forms[2].field_type[6]");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("//tr[@id='field-mystatus']/td[3]/a[1]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "New");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Custom Fields");
		$this->waitForPageToLoad("30000");
		$this->click("//tr[@id='field-mystatus']/td[3]/a[3]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Analyse");
		$this->select("status_id", "label=Open");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Workflow");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Custom Fields");
		$this->waitForPageToLoad("30000");
$this->click("//tr[@id='field-mystatus']/td[4]/a[1]");
		//$this->click("//a[contains(@href, '/tracker/admin/index.php?add_opt=1&boxid=22&group_id=6&atid=101')]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Candidate");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Custom Fields");
		$this->waitForPageToLoad("30000");
$this->click("//tr[@id='field-mystatus']/td[4]/a[1]");
		//$this->click("//a[contains(@href, '/tracker/admin/index.php?add_opt=1&boxid=22&group_id=6&atid=101')]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Open");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Resolved");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Validated");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Verified");
		$this->select("status_id", "label=Closed");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Duplicated");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Postponed");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Closed");
		$this->select("status_id", "label=Closed");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Workflow");
		$this->waitForPageToLoad("30000");
// TODO have to find a less data dependant way to do this
	if (defined('DB_INIT_CMD')) {
		$this->click("wk[157][159]");
		$this->click("wk[157][160]");
		$this->click("wk[157][161]");
		$this->click("wk[157][162]");
		$this->click("wk[157][163]");
		$this->click("wk[157][164]");
		$this->click("wk[157][165]");
		$this->click("wk[157][166]");
		$this->click("wk[158][157]");
		$this->click("wk[159][160]");
		$this->click("wk[159][161]");
		$this->click("wk[159][162]");
		$this->click("wk[159][163]");
		$this->click("wk[159][157]");
		$this->click("wk[159][158]");
		$this->click("wk[159][158]");
		$this->click("wk[159][157]");
		$this->click("wk[158][160]");
		$this->click("wk[158][161]");
		$this->click("wk[158][162]");
		$this->click("wk[158][163]");
		$this->click("wk[158][164]");
		$this->click("wk[158][165]");
		$this->click("wk[158][166]");
		$this->click("wk[158][166]");
		$this->click("wk[158][165]");
		$this->click("wk[159][157]");
		$this->click("wk[159][158]");
		$this->click("wk[159][164]");
		$this->click("wk[159][165]");
		$this->click("wk[159][166]");
		$this->click("wk[159][160]");
		$this->click("wk[159][166]");
		$this->click("wk[159][165]");
		$this->click("wk[160][157]");
		$this->click("wk[160][158]");
		$this->click("wk[160][159]");
		$this->click("wk[160][161]");
		$this->click("wk[160][162]");
		$this->click("wk[160][163]");
		$this->click("wk[160][164]");
		$this->click("wk[160][165]");
		$this->click("wk[160][166]");
		$this->click("wk[160][158]");
		$this->click("wk[160][161]");
		$this->click("wk[161][157]");
		$this->click("wk[161][158]");
		$this->click("wk[161][159]");
		$this->click("wk[161][160]");
		$this->click("wk[161][162]");
		$this->click("wk[161][163]");
		$this->click("wk[161][164]");
		$this->click("wk[161][165]");
		$this->click("wk[161][166]");
		$this->click("wk[161][162]");
		$this->click("wk[161][158]");
		$this->click("wk[162][157]");
		$this->click("wk[162][158]");
		$this->click("wk[162][159]");
		$this->click("wk[162][160]");
		$this->click("wk[162][161]");
		$this->click("wk[162][164]");
		$this->click("wk[162][163]");
		$this->click("wk[162][165]");
		$this->click("wk[162][166]");
		$this->click("wk[162][158]");
		$this->click("wk[162][158]");
		$this->click("wk[162][158]");
		$this->click("wk[162][163]");
		$this->click("wk[163][157]");
		$this->click("wk[163][158]");
		$this->click("wk[163][159]");
		$this->click("wk[163][160]");
		$this->click("wk[163][161]");
		$this->click("wk[163][162]");
		$this->click("wk[163][164]");
		$this->click("wk[163][165]");
		$this->click("wk[163][166]");
		$this->click("wk[164][157]");
		$this->click("wk[164][158]");
		$this->click("wk[164][159]");
		$this->click("wk[164][160]");
		$this->click("wk[164][161]");
		$this->click("wk[164][162]");
		$this->click("wk[164][163]");
		$this->click("wk[164][165]");
		$this->click("wk[164][166]");
		$this->click("wk[165][157]");
		$this->click("wk[165][158]");
		$this->click("wk[165][159]");
		$this->click("wk[165][160]");
		$this->click("wk[165][161]");
		$this->click("wk[165][162]");
		$this->click("wk[165][163]");
		$this->click("wk[165][164]");
		$this->click("wk[165][166]");
		$this->click("wk[165][158]");
		$this->click("wk[166][157]");
		$this->click("wk[166][158]");
		$this->click("wk[166][159]");
		$this->click("wk[166][160]");
		$this->click("wk[166][161]");
		$this->click("wk[166][162]");
		$this->click("wk[166][163]");
		$this->click("wk[166][164]");
		$this->click("wk[166][165]");
	}
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Workflow saved"));
		
		// Ensure that it is not possible to configure the workflow without initial state.
// TODO have to find a less data dependant way to do this
	if (defined('DB_INIT_CMD')) {
		$this->click("wk[100][157]");
		$this->click("wk[100][158]");
		$this->click("wk[100][159]");
		$this->click("wk[100][160]");
		$this->click("wk[100][161]");
		$this->click("wk[100][162]");
		$this->click("wk[100][163]");
		$this->click("wk[100][164]");
		$this->click("wk[100][165]");
		$this->click("wk[100][166]");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("ERROR: Initial values not saved"));
		$this->assertTrue($this->isTextPresent("Workflow saved"));
	}
	}
}
?>

<?php
/*
 * Copyright (C) 2008 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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

class LoginProcess extends FForge_SeleniumTestCase
{
	function testLogin()
	{
		// Test with a normal login.
		$this->open( ROOT );
		if (!$this->isTextPresent("Log In")) {
			$this->logout();
		}
		$this->clickAndWait("link=Log In");

		// Check that current URL's base is the same as ROOT
		// If the forge redirects to other URL than the one
		// used to access it, then logout doesn't work (bug or
		// feature ?)
		$location=$this->getLocation();
		$url_regexp = str_replace('.', '\.', HOST);
		$url_regexp = '/https?:\/\/'. $url_regexp .'\//';
		$this->assertRegExp($url_regexp, $location, 
				    "You may need to set 'HOST' setting in test suite's config file to something compatible with 'web_host' defined in ini file");

		$this->type("form_loginname", "admin");
		$this->type("form_pw", "myadmin");
		$this->click("login");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Forge Admin"));
		$this->assertTrue($this->isTextPresent("Log Out"));
		$this->logout();
		// Verify that logout is succesfull
		$this->assertTrue($this->isTextPresent("Log In"));
	
		// Test with an empty password.
		$this->open( ROOT );
		$this->click("link=Log In");
		$this->waitForPageToLoad("30000");
		$this->type("form_loginname", "admin");
		$this->type("form_pw", "");
		$this->click("login");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Missing Password Or Users Name"));
		$this->assertFalse($this->isTextPresent("Forge Admin"));
		$this->assertTrue($this->isTextPresent("Log In"));
		
		// Test with a wrong password.
		$this->open( ROOT );
		$this->click("link=Log In");
		$this->waitForPageToLoad("30000");
		$this->type("form_loginname", "admin");
		$this->type("form_pw", "awrongpassword");
		$this->click("login");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Invalid Password Or User Name"));
		$this->assertFalse($this->isTextPresent("Forge Admin"));
		$this->assertTrue($this->isTextPresent("Log In"));
		
		// Test factored code.
		$this->login('admin');
		$this->assertTrue($this->isTextPresent("Forge Admin"));
		$this->assertTrue($this->isTextPresent("Log Out"));

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Display Full User List/Edit Users");
		$this->click("//table/tbody/tr/td/a[contains(@href,'useredit.php') and contains(.,'(admin)')]/../..//a[contains(@href, 'passedit.php?user_id=')]");
		$this->waitForPageToLoad("30000");
		$this->type("passwd","tototata");
		$this->type("passwd2","tototata");
		$this->clickAndWait("submit");
		$this->logout();

		$this->open( ROOT );
		$this->click("link=Log In");
		$this->waitForPageToLoad("30000");
		$this->type("form_loginname", "admin");
		$this->type("form_pw", "tototata");
		$this->click("login");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Forge Admin"));
		$this->assertTrue($this->isTextPresent("Log Out"));
		$this->assertTrue($this->isTextPresent("Log Out"));
	}

	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

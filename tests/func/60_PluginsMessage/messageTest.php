<?php
/**
 * Copyright (C) 2015 Roland Mas
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

/**
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class MessageTest extends FForge_SeleniumTestCase
{
	protected $alreadyActive = 0;

	function testMessage()
	{
		$this->_activateMessagePlugin();

		$this->init();

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Configure Global Message");
		$this->type("//textarea[@name='body']", "Forge under maintenance, please bear with us.");
		$this->clickAndWait("//input[@value='Save']");

		$this->open( ROOT );
		$this->assertTrue($this->isTextPresent("Forge under maintenance"));

		$this->open( ROOT . '/projects/projecta') ;
		$this->assertTrue($this->isTextPresent("Forge under maintenance"));

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Configure Global Message");
		$this->type("//textarea[@name='body']", "Forge recently upgraded, please report problems.");
		$this->clickAndWait("//input[@value='Save']");

		$this->open( ROOT );
		$this->assertFalse($this->isTextPresent("Forge under maintenance"));
		$this->assertTrue($this->isTextPresent("Forge recently upgraded"));

		$this->open( ROOT . '/projects/projecta') ;
		$this->assertFalse($this->isTextPresent("Forge under maintenance"));
		$this->assertTrue($this->isTextPresent("Forge recently upgraded"));

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Configure Global Message");
		$this->type("//textarea[@name='body']", "");
		$this->clickAndWait("//input[@value='Save']");

		$this->open( ROOT );
		$this->assertFalse($this->isTextPresent("Forge under maintenance"));
		$this->assertFalse($this->isTextPresent("Forge recently upgraded"));

		$this->open( ROOT . '/projects/projecta') ;
		$this->assertFalse($this->isTextPresent("Forge under maintenance"));
		$this->assertFalse($this->isTextPresent("Forge recently upgraded"));
	}

	private function _activateMessagePlugin() {
		if (! $this->alreadyActive) {
			$this->activatePlugin('message');
			$this->alreadyActive = 1;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

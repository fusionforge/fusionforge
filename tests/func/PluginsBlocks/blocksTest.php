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

class UserBlocks extends FForge_SeleniumTestCase
{
  protected $alreadyActive = 0;
	
  function testUserBlocks()
  {
  	$this->_activateBlocksPlugin();
	
    $this->populateStandardTemplate('empty');
    $this->init();

    $this->click("link=Admin");
    $this->waitForPageToLoad("30000");
    $this->click("link=Tools");
    $this->waitForPageToLoad("30000");
    $this->click("use_blocks");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Project information updated"));
    $this->click("link=Blocks Admin");
    $this->waitForPageToLoad("30000");
    $this->click("activate[summary_description]");
//    $this->click("activate[summary_right]");
    $this->click("//input[@value='Save Blocks']");
    $this->waitForPageToLoad("30000");

    $this->open("/plugins/blocks/index.php?id=6&type=admin&pluginname=blocks");
    $this->click("link=configure");
    $this->waitForPageToLoad("30000");
    $this->type("body", "This is my nice block.");
    $this->click("//input[@value='Save']");
    $this->waitForPageToLoad("30000");
//    $this->click("//div[@id='maindiv']/form/table/tbody/tr[2]/td[4]/a");
//    $this->waitForPageToLoad("30000");
//    $this->type("body", "{boxTop Project}\nThis is the summary block.\n{boxBottom}");
//    $this->click("//input[@value='Save']");
//    $this->waitForPageToLoad("30000");
    $this->click("link=Summary");
    $this->waitForPageToLoad("30000");
//    $this->assertText("//td[@id='main']/table[1]/tbody/tr/td[1]", "This is my nice block.");
//    $this->assertEquals("This is the summary block.", $this->getText("//td[@id='main']/table[1]/tbody/tr/td[2]/table[1]/tbody/tr[2]/td"));
	$this->assertTrue($this->isTextPresent("This is my nice block."));
//	$this->assertTrue($this->isTextPresent("This is the summary block."));
  }
  
  private function _activateBlocksPlugin() {
  	if (! $this->alreadyActive) {
  		$this->activatePlugin('blocks');
		$this->alreadyActive = 1;
  	}
  }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

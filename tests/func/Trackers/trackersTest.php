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

class CreateTracker extends FForge_SeleniumTestCase
{
	function testSimpleCreate()
	{
		$this->populateStandardTemplate('trackers');
		$this->init();

		// Test: Create a simple bug report (Message1/Text1).
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Bugs"));
		$this->assertTrue($this->isTextPresent("Support"));
		$this->assertTrue($this->isTextPresent("Patches"));
		$this->assertTrue($this->isTextPresent("Feature Requests"));
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit New");
		$this->waitForPageToLoad("30000");
		$this->type("summary", "Summary1");
		$this->type("details", "Description1");
		$this->click("//form[@id='trackeraddform']//input[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Summary1"));
		$this->click("link=Summary1");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent(""));
		$this->assertTrue($this->isTextPresent("Description1"));

		// Test: Adding a comment and checking that it is recorded.
		$this->type("details", 'This is comment 1');
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Summary1");
		$this->assertTextPresent('This is comment 1');

		// Test: Adding a second comment and checking that it is recorded.
		$this->type("details", 'Comment 2 \n added');
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Summary1");
		$this->assertTextPresent('Comment 2 \n added');
		$this->assertTextPresent("This is comment 1");

		// Test: Adding another comment (chars) and checking that it is recorded.
		$this->type("details", "This & été");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Summary1");
		$this->assertTextPresent("This & été");

		// Test: Updating the URL extra field and checking that it is recorded.
		$this->type("//form[@id='trackermodform']//input[@type='text']", "http://google.com/");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->click("link=Summary1");
		$this->waitForPageToLoad("30000");
		try {
			$this->assertEquals("http://google.com/", $this->getValue("//form[@id='trackermodform']//input[@type='text']"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}

		// Test: Updating the priority and checking that it is recorded.
		$this->select("priority", "label=5 - Highest");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("5"));
		$this->click("link=Summary1");
		$this->waitForPageToLoad("30000");
	}

	function testExtraFields()
	{
		$this->populateStandardTemplate('trackers');
		$this->init();

		// Testing extra-fields
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(@href, '".ROOT. "/tracker/admin/')]");
		$this->waitForPageToLoad("30000");
		$this->click("link=Manage Custom Fields");
		$this->waitForPageToLoad("30000");
		$this->type("name", "Number");
		$this->type("alias", "number");
		$this->click("field_type");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("//tr[@id='field-number']/td[4]/a[1]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "1");
		$this->clickAndWait("post_changes");
		$this->assertTextPresent("Element inserted");
		$this->type("name", "2");
		$this->clickAndWait("post_changes");
		$this->assertTextPresent("Element inserted");

		// Testing [#3609]: Select Box does not accept 0 as choice
		$this->type("name", "0");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Element inserted"));

		// Testing [#3649]: 0 not accepted when modifying a select list value
		$this->click("link=Manage Custom Fields");
		$this->waitForPageToLoad("30000");
		$this->click("//tr[@id='field-number']/td[3]/a[5]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "10");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->click("//tr[@id='field-number']/td[3]/a[5]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "0");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Element updated"));
	}

	function testCreateAndDeleteNewTracker()
	{
		$this->populateStandardTemplate('trackers');
		$this->init();

		// Create a new tracker and delete it after.
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(@href,'".ROOT."/tracker/admin/')]");
		$this->waitForPageToLoad("30000");
		$this->type("name", "newTracker");
		$this->type("description", "This is a new tracker");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Tracker created successfully"));
		$this->assertTrue($this->isTextPresent("newTracker"));
		$this->assertTrue($this->isTextPresent("This is a new tracker"));
		$this->click("link=newTracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Delete");
		$this->waitForPageToLoad("30000");
		$this->click("sure");
		$this->click("really_sure");
		$this->click("post_changes");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Successfully Deleted."));
		$this->assertFalse($this->isTextPresent("newTracker"));
		$this->assertFalse($this->isTextPresent("This is a new tracker"));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

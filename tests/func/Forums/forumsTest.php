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

class CreateForum extends FForge_SeleniumTestCase
{
	function testSimplePost()
	{
		// Create the first message (Message1/Text1).
		$this->init();
		$this->click("link=Forums");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("Permission denied."));
		$this->assertTrue($this->isTextPresent("open-discussion"));
		$this->click("link=open-discussion");
		$this->waitForPageToLoad("30000");
		$this->click("link=Start New Thread");
		$this->waitForPageToLoad("30000");
		$this->type("subject", "Message1");
		$this->type("body", "Text1");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Message Posted Successfully"));
		$this->click("link=Forums");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("open-discussion"));
		$this->click("link=open-discussion");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Message1"));
	}

	/*
	 * Simulate a click on the link from a mail.
	 * As the forum is private, the users should be
	 * redirected to the login prompt saying that he has
	 * to login to get access to the message. Once logged,
	 * he should be redirected to the given forum.
	 */
	function testSimpleAccessWhenPrivate()
	{
		$this->init();

		$this->open( ROOT.'/forum/message.php?msg_id=3' );
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Welcome to Developers"));

		$this->logout();
		$this->open( ROOT.'/forum/message.php?msg_id=3' );
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isLoginRequired());
		$this->triggeredLogin('admin');
		$this->assertTrue($this->isTextPresent("Welcome to Developers"));
	}

	/*
	 * Simulate a user non logged that will reply
	 * to a message in a forum. He will be redirected
	 * to the login page, then will reply and then
	 * we check that his reply is present in the thread.
	 */
	function testReplyToMessage()
	{
		$this->init();
		$this->logout();

		$this->open("/projects/projecta/");
		$this->click("link=Forums");
		$this->waitForPageToLoad("30000");
		$this->click("link=open-discussion");
		$this->waitForPageToLoad("30000");
		$this->click("link=Welcome to Open-Discussion");
		$this->waitForPageToLoad("30000");
		$this->click("link=[ reply ]");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isLoginRequired());
		$this->triggeredLogin('admin');
		$this->type("body", "Here is my 19823 reply");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Message Posted Successfully"));
		$this->click("link=Welcome to Open-Discussion");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Here is my 19823 reply"));

	}
	
	/*
	 * Verify that it is imposible to use name already used by a mailing list
	 */
	function testEmailAddressNotAlreadyUsed() {
		$this->init();
		$this->click("link=Mailing Lists");
		$this->waitForPageToLoad("30000");
		$this->click("//body/div[@id='maindiv']/p[1]/strong/a");
		$this->waitForPageToLoad("30000");
		$this->click("link=Add Mailing List");
		$this->waitForPageToLoad("30000");
		$this->type("list_name", "toto");
		$this->type("description", "Toto mailing list");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("List Added"));
		$this->click("link=Forums");
		$this->waitForPageToLoad("30000");
		$this->click("link=open-discussion");
		$this->waitForPageToLoad("30000");
		$this->click("//body/div[@id='maindiv']/p[1]/strong/a[2]");
		$this->waitForPageToLoad("30000");
		$this->click("link=Add forum");
		$this->waitForPageToLoad("30000");
		$this->type("forum_name", "toto");
		$this->type("description", "Toto forum");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Error: a mailing list with the same email address already exists"));
	}
}
?>

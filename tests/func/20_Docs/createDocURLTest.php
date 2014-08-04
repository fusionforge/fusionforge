<?php
/*
 * Copyright (C) 2010-2013 Alain Peyrat - Alcatel-Lucent
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class CreateDocURL extends FForge_SeleniumTestCase
{
	function testCreateDocURL()
	{
		$this->init();
		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Docs");
		$this->clickAndWait("addItemDocmanMenu");
		// ugly hack until we fix behavior in docman when no folders exist. We need to click twice on the link
		$this->clickAndWait("addItemDocmanMenu");
		$this->click("id=tab-new-folder");
		$this->type("groupname", "docdirectory");
		$this->clickAndWait("submitaddsubgroup");
		$this->clickAndWait("addItemDocmanMenu");
		$this->click("id=tab-new-document");
		$this->type("title", "My document");
		$this->type("//input[@name='description']", "L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");
		$this->click("//input[@name='type' and @value='pasteurl']");
		$this->type("//input[@name='file_url']", URL."/terms.php");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Document ".URL."/terms.php submitted successfully");
		$this->assertTextPresent("My document");
		$this->assertTextPresent("L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");
		//$this->clickAndWait("link=".URL."/terms.php");
		//$this->assertTextPresent("These are the terms and conditions under");

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Docs");
		$this->clickAndWait("listFileDocmanMenu");
		$this->clickAndWait("link=docdirectory");
		$this->clickAndWait("//img[@alt='trashdir']");
		$this->assertTextPresent("Documents folder docdirectory moved to trash successfully");
	}

	function testCreateMultipleDocs()
	{
		$this->init();
		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Docs");
		$this->clickAndWait("addItemDocmanMenu");
		// ugly hack until we fix behavior in docman when no folders exist. We need to click twice on the link
		$this->clickAndWait("addItemDocmanMenu");
		$this->click("id=tab-new-document");
		$this->type("title", "My document");
		$this->type("//input[@name='description']", "My Description");
		$this->click("//input[@name='type' and @value='pasteurl']");
		$this->type("file_url", URL."/terms.php");
		$this->clickAndWait("//input[@name='submit' and @value='Submit Information']");
		$this->assertTextPresent("Document ".URL."/terms.php submitted successfully");

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Docs");
		$this->clickAndWait("addItemDocmanMenu");
		$this->type("title", " My document ");
		$this->type("//input[@name='description']", "My Description");
		$this->click("//input[@name='type' and @value='pasteurl']");
		$this->type("file_url", URL."/terms.php");
		$this->clickAndWait("//input[@name='submit' and @value='Submit Information']");
		$this->assertTextPresent("Document already published in this folder");
	}
}

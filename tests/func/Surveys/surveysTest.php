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

class Surveys extends FForge_SeleniumTestCase
{
	function testSimpleSurvey()
	{
		// Test: Create a simple survey.
		$this->init();

		$this->clickAndWait("link=Surveys");
		$this->clickAndWait("link=Administration");

		// Create some questions
		$this->clickAndWait("link=Add Question");
		$this->type("question", "This is my first question (radio) ?");
		$this->clickAndWait("submit");
		$this->type("question", "This is my second question (text area) ?");
		$this->select("question_type", "label=Text Area");
		$this->clickAndWait("submit");
		$this->type("question", "This is my third question (yes/no) ?");
		$this->select("question_type", "label=Radio Buttons Yes/No");
		$this->clickAndWait("submit");
		$this->type("question", "This is a comment line of text");
		$this->select("question_type", "label=Comment Only");
		$this->clickAndWait("submit");
		$this->type("question", "This is a my fifth question (text field) ?");
		$this->select("question_type", "label=Text Field");
		$this->clickAndWait("submit");
		$this->type("question", "L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");
		$this->select("question_type", "label=Text Field");
		$this->clickAndWait("submit");

		// Create survey
		$this->clickAndWait("link=Add Survey");
		$this->type("survey_title", "My first survey: L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");
		$this->click("to_add[]");
		$this->click("//input[@name='to_add[]' and @value='4']");
		$this->click("//input[@name='to_add[]' and @value='2']");
		$this->click("//input[@name='to_add[]' and @value='5']");
		$this->click("//input[@name='to_add[]' and @value='3']");
		$this->clickAndWait("submit");
		$this->click("link=My first survey: L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");
		$this->waitForPageToLoad("30000");
		$this->assertTextPresent("This is a my fifth question (text field) ?");
		$this->assertTextPresent("This is a comment line of text");
		$this->assertTextPresent("This is my third question (yes/no) ?");
		$this->assertTextPresent("This is my second question (text area) ?");
		$this->assertTextPresent("This is my first question (radio) ?");
		$this->click("//input[@name='_1' and @value='3']");
		$this->type("_2", "hello");
		$this->click("_3");
		$this->click("_5");
		$this->type("_5", "text");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Administration");
		$this->clickAndWait("link=Show Results");
		$this->click("link=My first survey: L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");
		$this->waitForPageToLoad("30000");
		$this->assertTextPresent("Warning - you are about to vote a second time on this survey.");
		$this->clickAndWait("link=Administration");
		$this->clickAndWait("link=Show Results");
		$this->clickAndWait("link=Result");
		$this->assertTextPresent("YES (1)");
		$this->assertTextPresent("3 (1)");
		$this->assertTextPresent("1, 2, 3, 4, 5");
		// Check that the number of votes is 1
		$this->assertEquals("1", $this->getText("//div[@id='maindiv']/table/tbody/tr/td[5]"));

		// Now testing by adding new questions to the survey.
		$this->clickAndWait("link=Surveys");
		$this->clickAndWait("link=Administration");
		$this->clickAndWait("link=Add Survey");
		$this->clickAndWait("link=Add Question");
		$this->type("question", "Another added question ?");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Add Survey");
		$this->clickAndWait("link=Edit");
		$this->click("to_add[]");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Add Survey");
		$this->type("survey_title", "Q10 ?");
		$this->clickAndWait("link=Add Question");
		$this->type("question", "Q8 ?");
		$this->clickAndWait("submit");
		$this->type("question", "Q9 ?");
		$this->clickAndWait("submit");
		$this->type("question", "Q10 ?");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Add Survey");
		$this->clickAndWait("link=Edit");
		$this->click("to_add[]");
		$this->click("//input[@name='to_add[]' and @value='8']");
		$this->click("//input[@name='to_add[]' and @value='9']");
		$this->clickAndWait("submit");
		$this->assertTextPresent("1, 2, 3, 4, 5, 6, 7, 8, 9");

		// Check that survey is public.
		$this->logout();
		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Surveys");
		$this->assertTextPresent("My first survey: L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils");

//		// Set survey to private
//		$this->login(FORGE_ADMIN_USERNAME);
//
//		$this->open("/survey/?group_id=6");
//		$this->clickAndWait("link=Surveys");
//		$this->clickAndWait("link=Administration");
//		$this->clickAndWait("link=Add Survey");
//		$this->clickAndWait("link=Edit");
//		$this->click("//input[@name='is_public' and @value='0']");
//		$this->clickAndWait("submit");
//		// Log out and check no survey is visible
//		$this->clickAndWait("link=Log Out");
//		$this->select("none", "label=projecta");
//		$this->waitForPageToLoad("30000");
//		$this->clickAndWait("link=Surveys");
//		$this->assertTextPresent("No Survey is found");
//
//		// Check direct access to a survey.
//		$this->open("/survey/survey.php?group_id=6&survey_id=1");
//		$this->waitForPageToLoad("30000");
//		$this->assertFalse($this->isTextPresent("My first survey: L'année dernière à Noël, 3 < 4, 中国 \" <em>, père & fils"));
	}
}
?>

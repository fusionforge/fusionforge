<?php
/*
 * Copyright 2011, Roland Mas
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumGforge.php';

class Search extends FForge_SeleniumTestCase
{
	function testSearch()
	{
		/*
		 * Search for projects
		 */

		$this->populateStandardTemplate();
		$this->createProject('projecta');
		$this->createProject('projectb');

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "XXXXXXXXXXXXXXXXXXXXXXXXXX");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("No matches found for"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "projecta");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("public description for ProjectA"));
		$this->assertFalse($this->isTextPresent("public description for projectb"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "description public projecta");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("public description for ProjectA"));
		$this->assertFalse($this->isTextPresent("public description for projectb"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "description 'public projecta'");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("public description for ProjectA"));
		$this->assertFalse($this->isTextPresent("public description for projectb"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "description public");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("public description for ProjectA"));
		$this->assertTrue($this->isTextPresent("public description for projectb"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "'description public'");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("public description for ProjectA"));
		$this->assertFalse($this->isTextPresent("public description for projectb"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "'public description'");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("public description for ProjectA"));
		$this->assertTrue($this->isTextPresent("public description for projectb"));

		/*
		 * Search for people
		 */

		$this->createUser('ratatouille');
		$this->createUser('tartiflette');

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->select("type_of_search", "label=People");
		$this->type("//input[@name='words']", "tartempion");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("ratatouille Lastname"));
		$this->assertFalse($this->isTextPresent("tartiflette Lastname"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->select("type_of_search", "label=People");
		$this->type("//input[@name='words']", "ratatouille");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("ratatouille Lastname"));
		$this->assertFalse($this->isTextPresent("tartiflette Lastname"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->select("type_of_search", "label=People");
		$this->type("//input[@name='words']", "lastname ratatouille");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("ratatouille Lastname"));
		$this->assertFalse($this->isTextPresent("tartiflette Lastname"));

		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->select("type_of_search", "label=People");
		$this->type("//input[@name='words']", "Lastname");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("ratatouille Lastname"));
		$this->assertTrue($this->isTextPresent("tartiflette Lastname"));

		/*
		 * Search inside a project
		 */

		// Prepare some tracker items

		$this->gotoProject('projecta');
		$this->clickAndWait("link=Tracker");
		$this->clickAndWait("link=Bugs");
		$this->clickAndWait("link=Submit New");
		$this->type("summary", "Bug1 boustrophédon");
		$this->type("details", "brebis outremanchienne");
		$this->clickAndWait("//form[@id='trackeraddform']//input[@type='submit']");
		$this->clickAndWait("link=Bug1 boustrophédon");
		$this->type("details", 'Ceci était une référence au « Génie des Alpages », rien à voir avec Charlie');
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Bug1 boustrophédon");
		$this->type("details", 'This is the needle');
		$this->clickAndWait("submit");

		$this->clickAndWait("link=Tracker");
		$this->clickAndWait("link=Patches");
		$this->clickAndWait("link=Submit New");
		$this->type("summary", "Bug2 gratapouêt");
		$this->type("details", "cthulhu was here");
		$this->clickAndWait("//form[@id='trackeraddform']//input[@type='submit']");
		$this->clickAndWait("link=Bug2 gratapouêt");
		$this->type("details", 'Charlie was here too');
		$this->clickAndWait("submit");

		// Search in trackers

		$this->select("type_of_search", "label=This project's trackers");
		$this->type("//input[@name='words']", "brebis");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Bug1"));

		$this->select("type_of_search", "label=This project's trackers");
		$this->type("//input[@name='words']", "alpages");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Bug1"));

		$this->select("type_of_search", "label=This project's trackers");
		$this->type("//input[@name='words']", "boustrophédon brebis alpages");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Bug1"));

		$this->select("type_of_search", "label=This project's trackers");
		$this->type("//input[@name='words']", "'boustrophédon brebis'");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("Bug1"));

		$this->select("type_of_search", "label=This project's trackers");
		$this->type("//input[@name='words']", "boustrophédon cthulhu");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("Bug1"));
		$this->assertFalse($this->isTextPresent("Bug2"));

		// Search in one particular tracker
		
		$this->select("type_of_search", "label=This project's trackers");
		$this->type("//input[@name='words']", "charlie");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertTrue($this->isTextPresent("Bug2"));

		$this->clickAndWait("link=Tracker");
		$this->clickAndWait("link=Bugs");
		$this->select("type_of_search", "label=Bugs");
		$this->type("//input[@name='words']", "charlie");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertFalse($this->isTextPresent("Bug2"));

		$this->clickAndWait("link=Bugs");
		$this->select("type_of_search", "label=Bugs");
		$this->type("//input[@name='words']", "charlie boustrophédon");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertFalse($this->isTextPresent("Bug2"));

		// Create some tasks

		$this->gotoProject('projecta');
		$this->clickAndWait("link=Tasks");
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task1 the brain");
		$this->type("details", "The same thing we do every night, Pinky - try to take over the world!");
		$this->type("hours", "199");
		$this->clickAndWait("submit");
		
		$this->clickAndWait("link=Task1 the brain");
		$this->type("details", 'This is the needle for tasks');
		$this->clickAndWait("submit");

		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task2 world peace");
		$this->type("details", "Otherwise WW4 will be fought with sticks");
		$this->type("hours", "199");
		$this->clickAndWait("submit");
			      
		// Search in Tasks

		$this->select("type_of_search", "label=This project's tasks");
		$this->type("//input[@name='words']", "pinky");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Task1"));

		$this->select("type_of_search", "label=This project's tasks");
		$this->type("//input[@name='words']", "cortex");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("Task1"));

		$this->select("type_of_search", "label=This project's tasks");
		$this->type("//input[@name='words']", "brain pinky needle");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Task1"));

		// Post some messages in a forum
		
		$this->gotoProject('projecta');
		$this->clickAndWait("link=Forums");
		$this->clickAndWait("link=open-discussion");
		$this->click("link=Start New Thread");
		$this->waitForPageToLoad("30000");
		$this->type("subject", "Message1 in a bottle");
		$this->type("body", "ninetynine of them on Charlie's wall");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Message1 in a bottle");
		$this->clickAndWait("link=[ reply ]");
		$this->type("subject", "Message2 in a bottle");
		$this->type("body", "ninetyeight of them in Charlie's fridge");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Message1 in a bottle");
		$this->clickAndWait("link=[ reply ]");
		$this->type("subject", "Message3 in a bottle");
		$this->type("body", "and yet another needle for the forums");
		$this->clickAndWait("submit");

		$this->clickAndWait("link=Forums");
		$this->clickAndWait("link=developers-discussion");
		$this->click("link=Start New Thread");
		$this->waitForPageToLoad("30000");
		$this->type("subject", "Message4 in an envelope");
		$this->type("body", "not the same thing as an antilope (and different thread anyway) (but still related to Charlie)");
		$this->clickAndWait("submit");

		// Search in Forums

		$this->select("type_of_search", "label=This project's forums");
		$this->type("//input[@name='words']", "bottle");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Message1"));
		$this->assertTrue($this->isTextPresent("Message2"));
		$this->assertTrue($this->isTextPresent("Message3"));
		$this->assertFalse($this->isTextPresent("Message4"));

		$this->select("type_of_search", "label=This project's forums");
		$this->type("//input[@name='words']", "bottle fridge");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertFalse($this->isTextPresent("Message1"));
		$this->assertTrue($this->isTextPresent("Message2"));
		$this->assertFalse($this->isTextPresent("Message3"));
		$this->assertFalse($this->isTextPresent("Message4"));

		// Search in one particular forum

		$this->select("type_of_search", "label=This project's forums");
		$this->type("//input[@name='words']", "charlie");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Message1"));
		$this->assertTrue($this->isTextPresent("Message2"));
		$this->assertFalse($this->isTextPresent("Message3"));
		$this->assertTrue($this->isTextPresent("Message4"));

		$this->clickAndWait("link=Forums");
		$this->clickAndWait("link=open-discussion");
		$this->select("type_of_search", "label=This forum");
		$this->type("//input[@name='words']", "charlie");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Message1"));
		$this->assertTrue($this->isTextPresent("Message2"));
		$this->assertFalse($this->isTextPresent("Message3"));
		$this->assertFalse($this->isTextPresent("Message4"));

		$this->clickAndWait("link=Forums");
		$this->clickAndWait("link=open-discussion");
		$this->select("type_of_search", "label=This forum");
		$this->type("//input[@name='words']", "charlie fridge");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		// Only one result => threaded view => need to check on bodies, not subjects
		$this->assertFalse($this->isTextPresent("wall"));
		$this->assertTrue($this->isTextPresent("fridge"));
		$this->assertFalse($this->isTextPresent("needle"));
		$this->assertFalse($this->isTextPresent("Message4"));

		// Create some documents

		$this->gotoProject('projecta');
		$this->clickAndWait("link=Docs");
		$this->clickAndWait("addItemDocmanMenu");
		$this->click("buttonDoc");
		$this->type("title", "Doc1 Vladimir");
		$this->type("description", "Jenkins buildbot");
		$this->click("//input[@name='type' and @value='pasteurl']");
		$this->type("file_url", "http://buildbot3.fusionforge.org/");
		$this->clickAndWait("submit");

		$this->clickAndWait("addItemDocmanMenu");
		$this->click("buttonDoc");
		$this->type("title", "Doc2 Astromir");
		$this->type("description", "Hudson (the needle)");
		$this->click("//input[@name='type' and @value='pasteurl']");
		$this->type("file_url", "http://buildbot.fusionforge.org/");
		$this->clickAndWait("submit");

		// Search in Documents

		$this->select("type_of_search", "label=This project's documents");
		$this->type("//input[@name='words']", "jenkins");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Doc1"));
		$this->assertFalse($this->isTextPresent("Doc2"));

		$this->select("type_of_search", "label=This project's documents");
		$this->type("//input[@name='words']", "vladimir jenkins");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("Doc1"));
		$this->assertFalse($this->isTextPresent("Doc2"));

		// Create some news

		$this->gotoProject('projecta');
		$this->clickAndWait("link=News");
		$this->clickAndWait("link=Submit");
		$this->type("summary", "News1 daily planet");
		$this->type("details", "Clark Kent's newspaper");
		$this->clickAndWait("submit");

		$this->clickAndWait("link=Submit");
		$this->type("summary", "News2 usenet");
		$this->type("details", "alt sysadmin recovery (needle)");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=News");

		// Search in news

		$this->select("type_of_search", "label=This project's news");
		$this->type("//input[@name='words']", "sysadmin");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("News2"));

		$this->select("type_of_search", "label=This project's news");
		$this->type("//input[@name='words']", "daily newspaper");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertFalse($this->isTextPresent("No matches found for"));
		$this->assertTrue($this->isTextPresent("News1"));

		// Search in entire project

		$this->gotoProject('projecta');
		$this->select("type_of_search", "label=Search the entire project");
		$this->type("//input[@name='words']", "needle");
		$this->clickAndWait("//input[@name='Search']");
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertFalse($this->isTextPresent("Bug2"));
		$this->assertTrue($this->isTextPresent("Task1"));
		$this->assertFalse($this->isTextPresent("Task2"));
		$this->assertFalse($this->isTextPresent("Message1"));
		$this->assertFalse($this->isTextPresent("Message2"));
		$this->assertTrue($this->isTextPresent("Message3"));
		$this->assertFalse($this->isTextPresent("Message4"));
		$this->assertFalse($this->isTextPresent("Doc1"));
		$this->assertTrue($this->isTextPresent("Doc2"));
		$this->assertFalse($this->isTextPresent("News1"));
		$this->assertTrue($this->isTextPresent("News2"));

		// Advanced search

		$this->gotoProject('projecta');
		$this->clickAndWait('Link=Advanced search');
		$this->click("//a[contains(@href,'short_forum') and .='all']");
		$this->click("//a[contains(@href,'short_tracker') and .='all']");
		$this->click("//a[contains(@href,'short_pm') and .='all']");
		$this->click("//a[contains(@href,'short_docman') and .='all']");
		$this->click("//a[contains(@href,'short_news') and .='all']");
		$this->type("//div[@id='maindiv']//input[@name='words']", "needle");
		$this->clickAndWait("//input[@name='submitbutton']");
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertFalse($this->isTextPresent("Bug2"));
		$this->assertTrue($this->isTextPresent("Task1"));
		$this->assertFalse($this->isTextPresent("Task2"));
		$this->assertFalse($this->isTextPresent("Message1"));
		$this->assertFalse($this->isTextPresent("Message2"));
		$this->assertTrue($this->isTextPresent("Message3"));
		$this->assertFalse($this->isTextPresent("Message4"));
		$this->assertFalse($this->isTextPresent("Doc1"));
		$this->assertTrue($this->isTextPresent("Doc2"));
		$this->assertFalse($this->isTextPresent("News1"));
		$this->assertTrue($this->isTextPresent("News2"));
	}

}
?>

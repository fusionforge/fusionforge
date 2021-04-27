<?php
/**
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
 * Copyright (C) 2015  Inria (Sylvain Beucler)
 * Copyright 2019, Franck Villaume - TrivialDev
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

require_once dirname(dirname(__FILE__)).'/SeleniumForge.php';

class CreateTask extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testcreateTask()
	{
		$this->setUpTasks();
		$this->createSomeTasks();
		// $this->browseTasks();
		$this->setTaskPriority();
		$this->completeTask();
		$this->closeTask();
		$this->deleteTask();
		// $this->assignTask();
		$this->orderTask();
		$this->registerEmailAddressForNotification();
		// $this->createSubproject();
		// $this->createPrivateSubproject();
		// $this->displayGANTT();
		// $this->displayActivityReportByDeveloper();
		// $this->displayActivityReportBySubproject();
		$this->exportCSV();
	}


	function setUpTasks()
	{
		$this->loadAndCacheFixture();
		$this->switchUser(FORGE_ADMIN_USERNAME);
		$this->gotoProject('ProjectA');

		// Initialize "rep_time_tracking" table
		$this->clickAndWait("link=Reporting");
		$this->waitForPageToLoad();

		$this->clickAndWait("link=Initialize / Rebuild Reporting Tables");
		$this->waitForPageToLoad();
		$this->clickAndWait("im_sure");
		$this->clickAndWait("submit");
		$this->waitForPageToLoad();

	//        $this->switchUser('uadmin');
		$this->gotoProject('ProjectA');

		$this->clickAndWait("link=Tasks");
		$this->assertTextPresent("To Do");
		$this->assertTextPresent("Next Release");
		$this->clickAndWait("link=To Do");
		$this->assertTextPresent("No Matching Tasks found");
	}

	function createSomeTasks()
	{
		// Create a first task
		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task1: Hello Paris");
		$this->type("details", "Details: Hello Paris");
		$this->type("hours", "10");
		$this->clickAndWait("//body//main[@id='maindiv']/form/table/tbody/tr[9]/td/input");
		$this->waitForPageToLoad();
		$this->assertTextPresent("Task Created Successfully");

		// Create a second task
		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task2: Hello France");
		$this->type("details", "Details: Hello France");
		$this->type("hours", "15");
		$this->clickAndWait("//body//main[@id='maindiv']/form/table/tbody/tr[9]/td/input");
		$this->waitForPageToLoad();
		$this->assertTextPresent("Task Created Successfully");

		// Create a third task
		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task3: Hello World");
		$this->type("details", "Details: Hello World");
		$this->type("hours", "20");
		$this->clickAndWait("//body//main[@id='maindiv']/form/table/tbody/tr[9]/td/input");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("Task Created Successfully"));
	}

	function browseTasks()
	{
		// Let us check that the 3 tasks appear on the list of tasks
		$this->clickAndWait("link=Tasks");
		$this->clickAndWait("link=To Do");
		$this->assertTextPresent("Task1: Hello Paris");
		$this->assertTextPresent("Task2: Hello France");
		$this->assertTextPresent("Task3: Hello World");

		// Let us assign tasks to users so that we can filter by assignee
		$this->clickAndWait("link=Task1: Hello Paris");
		$this->removeSelection("assigned_to[]", "label=None");
		$this->addSelection("assigned_to[]", "label=ucoredev Lastname");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Task2: Hello France");
		$this->removeSelection("assigned_to[]", "label=None");
		$this->addSelection("assigned_to[]", "label=ucontrib Lastname");
		$this->clickAndWait("submit");
		$this->clickAndWait("link=Task3: Hello World");
		$this->removeSelection("assigned_to[]", "label=None");
		$this->addSelection("assigned_to[]", "label=ucontrib Lastname");
		$this->clickAndWait("submit");

		// There should not be unassigned tasks
		$this->select($this->byName("_assigned_to"))->selectOptionByLabel("Unassigned");
		$this->clickAndWait("submit");
		$this->assertTextPresent("No Matching Tasks found");

		// Tasks 2 and 3 should be assigned to ucontrib
		$this->select($this->byName("_assigned_to"))->selectOptionByLabel("ucontrib Lastname");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task2:");
		$this->assertTextPresent("Task3:");

		// Task 1 should be assigned to ucoredev
		$this->select($this->byName("_assigned_to"))->selectOptionByLabel("ucoredev Lastname");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task1:");

		// "Any" should show the 3 tasks
		$this->select($this->byName("_assigned_to"))->selectOptionByLabel("Any");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task1:");
		$this->assertTextPresent("Task2:");
		$this->assertTextPresent("Task3:");

		// Let use close a task to sort by status
		$this->clickAndWait("link=Task1: Hello Paris");
		$this->clickAndWait("status_id");
		$this->select($this->byName("status_id"))->selectOptionByLabel("Closed");
		$this->clickAndWait("submit");

		// Select open tasks
		$this->select($this->byName("_status"))->selectOptionByLabel("Open");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task2:");
		$this->assertTextPresent("Task3:");

		// Select closed tasks
		$this->select($this->byName("_status"))->selectOptionByLabel("Closed");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task1:");

		// Select "Any" status
		$this->select($this->byName("_status"))->selectOptionByLabel("Any");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task1:");
		$this->assertTextPresent("Task2:");
		$this->assertTextPresent("Task3:");

		// Let us add categories to sort by category
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Add/Edit Categories");
		$this->type("name", "mycategory");
		$this->clickAndWait("post_changes");
		$this->assertTextPresent("Category Inserted");
		$this->type("name", "yourcategory");
		$this->clickAndWait("post_changes");
		$this->assertTextPresent("Category Inserted");
		$this->assertTextPresent("mycategory");
		$this->assertTextPresent("yourcategory");

		// Set Task1 to mycategory
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Task1: Hello Paris");
		$this->select($this->byName("category_id"))->selectOptionByLabel("mycategory");
		$this->clickAndWait("submit");

		// Set Task2 to yourcategory
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Task2: Hello France");
		$this->select($this->byName("category_id"))->selectOptionByLabel("yourcategory");
		$this->clickAndWait("submit");

		// Select "Any" category
		$this->select($this->byName("_category_id"))->selectOptionByLabel("Any");
		$this->clickAndWait("submit");
		$this->select($this->byName("_order"))->selectOptionByLabel("Task Summary");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task1:");
		$this->assertTextPresent("Task2:");
		$this->assertTextPresent("Task3:");

		// Select "mycategory" category
		$this->select($this->byName("_category_id"))->selectOptionByLabel("mycategory");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task1:");
		$this->assertFalse($this->isTextPresent("Task2:"));
		$this->assertFalse($this->isTextPresent("Task3:"));

		// Select "yourcategory" category
		$this->select($this->byName("_category_id"))->selectOptionByLabel("yourcategory");
		$this->clickAndWait("submit");
		$this->assertFalse($this->isTextPresent("Task1:"));
		$this->assertTextPresent("Task2:");
		$this->assertFalse($this->isTextPresent("Task3:"));

		// Set Detail view to Detailed
		$this->clickAndWait("link=To Do");
		$this->select($this->byName("_category_id"))->selectOptionByLabel("Any");
		$this->select($this->byName("_view"))->selectOptionByLabel("Detailed");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Details: Hello Paris");
		$this->assertTextPresent("Details: Hello France");
		$this->assertTextPresent("Details: Hello World");

		// Set Detail view to Summary
		$this->select($this->byName("_view"))->selectOptionByLabel("Summary");
		$this->clickAndWait("submit");
		$this->assertFalse($this->isTextPresent("Details: Hello"));
	}

	function setTaskPriority()
	{
		// Set the priority of a task
		$this->clickAndWait("link=Task2: Hello France");
		$this->select($this->byName("priority"))->selectOptionByLabel("5 - Highest");
		$this->clickAndWait("submit");

		// Check the priority is OK
		$this->clickAndWait("link=Task2: Hello France");
		$this->assertTextPresent("Highest");
	}

	function completeTask()
	{
		// Set the completing value of a task
		$this->select($this->byName("percent_complete"))->selectOptionByLabel("45%");
		$this->clickAndWait("//option[@value='45']");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task Updated Successfully");

		// Check the percentage is OK
		$this->clickAndWait("link=Task2: Hello France");
		$this->assertTextPresent("45%");
	}

	function closeTask()
	{
		// Done in browseTasks()
	}

	function deleteTask()
	{
		// Delete a task
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Task3: Hello World");
		$this->clickAndWait("link=Delete this task");
		$this->clickAndWait("confirm_delete");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Task Successfully Deleted");

		// Let us check that Task3 no longer appears on the list of tasks
		$this->clickAndWait("link=Tasks");
		$this->clickAndWait("link=To Do");
		$this->assertTextPresent("Task1: Hello Paris");
		$this->assertTextPresent("Task2: Hello France");
		$this->assertFalse($this->isTextPresent("Task3: Hello World"));
	}

	function assignTask()
	{
		$this->gotoProject("ProjectA");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=Tasks");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=To Do");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=Task1: Hello Paris");
		$this->addSelection("assigned_to[]", "label=ucontrib Lastname");
		$this->removeSelection("assigned_to[]", "label=ucoredev Lastname");
		$this->clickAndWait("submit");

		$this->switchUser('ucontrib');
		$this->open(ROOT);
		$this->waitForPageToLoad();
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=My Page");
		// You cannot click on "Assigned Tasks" tabs,
		// but the text is present in the page anyway.
		// $this->clickAndWait("link=Assigned Tasks");
		$this->assertTextPresent("Task2: Hello France");
		$this->switchUser('uadmin');
		$this->open(ROOT);
		$this->waitForPageToLoad();
	}

	function orderTask()
	{
	}

	function registerEmailAddressForNotification()
	{
	}

	function createSubproject()
	{
		$this->gotoProject("ProjectA");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=Project Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Tasks Administration");
		$this->clickAndWait("link=Add a Subproject");
		$this->type("project_name", "public");
		$this->type("//input[@name='description']", "This is a public subproject");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Subproject Inserted");
		$this->clickAndWait("link=Tasks");
		$this->assertTextPresent("This is a public subproject");
	}

	function createPrivateSubproject()
	{
		$this->gotoProject("ProjectA");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=Project Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Tasks Administration");
		$this->clickAndWait("link=Add a Subproject");
		$this->clickAndWait("//input[@name='is_public' and @value='0']");
		$this->type("project_name", "private");
		$this->type("//input[@name='description']", "This is a private subproject");
		$this->clickAndWait("submit");
		$this->assertTextPresent("Subproject Inserted");
		$this->clickAndWait("link=Tasks");
		$this->assertTextPresent("This is a private subproject");
	}

	function displayGANTT()
	{
		// Display GANTT diagram
		$this->open("/pm/reporting/index.php?what=tech&span=&period=lifespan&group_id=6#b");
		$this->clickAndWait("link=Tasks");
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Gantt Chart");
		$this->waitForPopUp("Gantt_Chart", );
		$this->selectWindow("name=Gantt_Chart");
		$this->assertTextPresent("Assignee");

		$this->close();
		$this->selectWindow("");
	}

	function displayActivityReportByDeveloper()
	{
		// Display activity report by developer
		$this->clickAndWait("link=Reporting");
		$this->select($this->byName("what"))->selectOptionByLabel("Report by Assignee");
		$this->clickAndWait("//input[@value='Show']");
		$this->assertTextPresent("Tasks By Assignee");
		$this->assertTextPresent("ucontrib");
		$this->assertFalse($this->isTextPresent("ucoredev"));
	}

	function displayActivityReportBySubproject()
	{
		// Display activity report by subproject
		$this->select($this->byName("what"))->selectOptionByLabel("Report by Subproject");
		$this->clickAndWait("//input[@value='Show']");
		$this->assertTextPresent("Tasks By Category");
		$this->assertTextPresent("To Do");
	}

	function exportCSV()
	{
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/*
 * Copyright (C) 2010 Alain Peyrat, Alcatel-Lucent
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

class CreateTask extends FForge_SeleniumTestCase
{
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
        $this->displayGANTT();
        //$this->displayActivityReportByDeveloper();
        //$this->displayActivityReportBySubproject();
        $this->exportCSV();
    }

    function setUpTasks()
    {
        $this->init();

        // Initialize "rep_time_tracking" table
        $this->click("link=Reporting");
        $this->waitForPageToLoad("30000");

        $this->click("link=Initialize / Rebuild Reporting Tables");
        $this->waitForPageToLoad("30000");
        $this->click("im_sure");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

//        $this->switchUser('uadmin');
        $this->gotoProject('ProjectA');

        $this->click("link=Tasks");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("To Do"));
        $this->assertTrue($this->isTextPresent("Next Release"));
        $this->click("link=To Do");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("No Matching Tasks found"));
    }

    function createSomeTasks()
    {
        // Create a first task
        $this->click("link=Add Task");
        $this->waitForPageToLoad("30000");
        $this->type("summary", "Task1: Hello Paris");
        $this->type("details", "Details: Hello Paris");
        $this->type("hours", "10");
        $this->click("//body/div[@id='maindiv']/form/table/tbody/tr[9]/td/input");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task Created Successfully"));

        // Create a second task
        $this->click("link=Add Task");
        $this->waitForPageToLoad("30000");
        $this->type("summary", "Task2: Hello France");
        $this->type("details", "Details: Hello France");
        $this->type("hours", "15");
        $this->click("//body/div[@id='maindiv']/form/table/tbody/tr[9]/td/input");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task Created Successfully"));

        // Create a third task
        $this->click("link=Add Task");
        $this->waitForPageToLoad("30000");
        $this->type("summary", "Task3: Hello World");
        $this->type("details", "Details: Hello World");
        $this->type("hours", "20");
        $this->click("//body/div[@id='maindiv']/form/table/tbody/tr[9]/td/input");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task Created Successfully"));
    }

    function browseTasks()
    {
        // Let us check that the 3 tasks appear on the list of tasks
        $this->click("link=Tasks");
        $this->waitForPageToLoad("30000");
        $this->click("link=To Do");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1: Hello Paris"));
        $this->assertTrue($this->isTextPresent("Task2: Hello France"));
        $this->assertTrue($this->isTextPresent("Task3: Hello World"));

        // Let us assign tasks to users so that we can filter by assignee
        $this->click("link=exact:Task1: Hello Paris");
        $this->waitForPageToLoad("30000");
        $this->removeSelection("assigned_to[]", "label=None");
        $this->addSelection("assigned_to[]", "label=ucoredev Lastname");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->click("link=exact:Task2: Hello France");
        $this->waitForPageToLoad("30000");
        $this->removeSelection("assigned_to[]", "label=None");
        $this->addSelection("assigned_to[]", "label=ucontrib Lastname");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->click("link=exact:Task3: Hello World");
        $this->waitForPageToLoad("30000");
        $this->removeSelection("assigned_to[]", "label=None");
        $this->addSelection("assigned_to[]", "label=ucontrib Lastname");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

        // There should not be unassigned tasks
        $this->select("_assigned_to", "label=Unassigned");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("No Matching Tasks found"));

        // Tasks 2 and 3 should be assigned to ucontrib
        $this->select("_assigned_to", "label=ucontrib Lastname");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task2:"));
        $this->assertTrue($this->isTextPresent("Task3:"));

        // Task 1 should be assigned to ucoredev
        $this->select("_assigned_to", "label=ucoredev Lastname");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1:"));

        // "Any" should show the 3 tasks
        $this->select("_assigned_to", "label=Any");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1:"));
        $this->assertTrue($this->isTextPresent("Task2:"));
        $this->assertTrue($this->isTextPresent("Task3:"));

        // Let use close a task to sort by status
        $this->click("link=exact:Task1: Hello Paris");
        $this->waitForPageToLoad("30000");
        $this->click("status_id");
        $this->select("status_id", "label=Closed");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

        // Select open tasks
        $this->select("_status", "label=Open");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task2:"));
        $this->assertTrue($this->isTextPresent("Task3:"));

        // Select closed tasks
        $this->select("_status", "label=Closed");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1:"));

        // Select "Any" status
        $this->select("_status", "label=Any");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1:"));
        $this->assertTrue($this->isTextPresent("Task2:"));
        $this->assertTrue($this->isTextPresent("Task3:"));

        // Let us add categories to sort by category
        $this->click("link=Admin");
        $this->waitForPageToLoad("30000");
        $this->click("link=Add/Edit Categories");
        $this->waitForPageToLoad("30000");
        $this->type("name", "mycategory");
        $this->click("post_changes");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Category Inserted"));
        $this->type("name", "yourcategory");
        $this->click("post_changes");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Category Inserted"));
        $this->assertTrue($this->isTextPresent("mycategory"));
        $this->assertTrue($this->isTextPresent("yourcategory"));

        // Set Task1 to mycategory
        $this->click("link=To Do: Browse tasks");
        $this->waitForPageToLoad("30000");
        $this->click("link=exact:Task1: Hello Paris");
        $this->waitForPageToLoad("30000");
        $this->select("category_id", "label=mycategory");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

        // Set Task2 to yourcategory
        $this->click("link=To Do: Browse tasks");
        $this->waitForPageToLoad("30000");
        $this->click("link=exact:Task2: Hello France");
        $this->waitForPageToLoad("30000");
        $this->select("category_id", "label=yourcategory");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

        // Select "Any" category
        $this->select("_category_id", "label=Any");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->select("_order", "label=Task Summary");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1:"));
        $this->assertTrue($this->isTextPresent("Task2:"));
        $this->assertTrue($this->isTextPresent("Task3:"));

        // Select "mycategory" category
        $this->select("_category_id", "label=mycategory");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1:"));
        $this->assertFalse($this->isTextPresent("Task2:"));
        $this->assertFalse($this->isTextPresent("Task3:"));

        // Select "yourcategory" category
        $this->select("_category_id", "label=yourcategory");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertFalse($this->isTextPresent("Task1:"));
        $this->assertTrue($this->isTextPresent("Task2:"));
        $this->assertFalse($this->isTextPresent("Task3:"));

        // Set Detail view to Detailed
        $this->click("link=To Do: Browse tasks");
        $this->waitForPageToLoad("30000");
        $this->select("_category_id", "label=Any");
        $this->select("_view", "label=Detailed");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Details: Hello Paris"));
        $this->assertTrue($this->isTextPresent("Details: Hello France"));
        $this->assertTrue($this->isTextPresent("Details: Hello World"));

        // Set Detail view to Summary
        $this->select("_view", "label=Summary");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertFalse($this->isTextPresent("Details: Hello"));

    }

    function setTaskPriority()
    {
        // Set the priority of a task
        $this->click("link=exact:Task2: Hello France");
        $this->waitForPageToLoad("30000");
        $this->select("priority", "label=5 - Highest");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

        // Check the priority is OK
        $this->click("link=exact:Task2: Hello France");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Highest"));
    }

    function completeTask()
    {
        // Set the completing value of a task
        $this->select("percent_complete", "label=45%");
        $this->click("//option[@value='45']");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task Updated Successfully"));

        // Check the percentage is OK
        $this->click("link=exact:Task2: Hello France");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("45%"));
    }

    function closeTask()
    {
        // Done in browseTasks()
    }

    function deleteTask()
    {
        // Delete a task
        $this->click("link=To Do: Browse tasks");
        $this->waitForPageToLoad("30000");
        $this->click("link=exact:Task3: Hello World");
        $this->waitForPageToLoad("30000");
        $this->click("link=Delete this task");
        $this->waitForPageToLoad("30000");
        $this->click("confirm_delete");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task Successfully Deleted"));

        // Let us check that Task3 no longer appears on the list of tasks
        $this->click("link=Tasks");
        $this->waitForPageToLoad("30000");
        $this->click("link=To Do");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Task1: Hello Paris"));
        $this->assertTrue($this->isTextPresent("Task2: Hello France"));
        $this->assertFalse($this->isTextPresent("Task3: Hello World"));
    }

    function assignTask()
    {
        $this->open("/pm/task.php?group_id=6&group_project_id=2");
        $this->click("link=exact:Task1: Hello Paris");
        $this->waitForPageToLoad("30000");
        $this->addSelection("assigned_to[]", "label=ucontrib Lastname");
        $this->removeSelection("assigned_to[]", "label=ucoredev Lastname");
        $this->click("submit");
        $this->waitForPageToLoad("30000");

        $this->switchUser('ucontrib');
        $this->open( ROOT );
        $this->waitForPageToLoad("30000");
        $this->click("link=ProjectA");
        $this->waitForPageToLoad("30000");
        $this->click("link=My Page");
        $this->waitForPageToLoad("30000");
        // You cannot click on "Assigned Tasks" tabs,
        // but the text is present in the page anyway.
        // $this->click("link=Assigned Tasks");
        $this->assertTrue($this->isTextPresent("Task2: Hello France"));
        $this->switchUser('uadmin');
        $this->open( ROOT );
        $this->waitForPageToLoad("30000");
    }

    function orderTask()
    {
    }

    function registerEmailAddressForNotification()
    {
    }

    function createSubproject()
    {
        $this->open("/pm/task.php?group_id=6");
        $this->click("link=Project Admin");
        $this->waitForPageToLoad("30000");
        $this->click("link=Tools");
        $this->waitForPageToLoad("30000");
        $this->click("link=Task Manager Admin");
        $this->waitForPageToLoad("30000");
        $this->click("link=Add a Subproject");
        $this->waitForPageToLoad("30000");
        $this->type("project_name", "public");
        $this->type("description", "This is a public subproject");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Subproject Inserted"));
        $this->click("link=Tasks");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("This is a public subproject"));
    }

    function createPrivateSubproject()
    {
        $this->open("/pm/task.php?group_id=6");
        $this->click("link=Project Admin");
        $this->waitForPageToLoad("30000");
        $this->click("link=Tools");
        $this->waitForPageToLoad("30000");
        $this->click("link=Task Manager Admin");
        $this->waitForPageToLoad("30000");
        $this->click("link=Add a Subproject");
        $this->waitForPageToLoad("30000");
        $this->click("//input[@name='is_public' and @value='0']");
        $this->type("project_name", "private");
        $this->type("description", "This is a private subproject");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Subproject Inserted"));
        $this->click("link=Tasks");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("This is a private subproject"));
    }

    function displayGANTT()
    {
        // Display GANTT diagram
        $this->click("link=Tasks");
        $this->waitForPageToLoad("30000");
        $this->click("link=To Do");
        $this->waitForPageToLoad("30000");
        $this->click("link=Gantt Chart");
        $this->waitForPopUp("Gantt_Chart", "30000");
    }

    function displayActivityReportByDeveloper()
    {
        // Display activity report by developer
        $this->click("link=Reporting");
        $this->waitForPageToLoad("30000");
        $this->select("what", "label=Report by Assignee");
        $this->click("//input[@value='Show']");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Tasks By Assignee"));
        $this->assertTrue($this->isTextPresent("ucontrib"));
        $this->assertFalse($this->isTextPresent("ucoredev"));
    }

    function displayActivityReportBySubproject()
    {
        // Display activity report by subproject
        $this->select("what", "label=Report by Subproject");
        $this->click("//input[@value='Show']");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("Tasks By Category"));
        $this->assertTrue($this->isTextPresent("To Do"));
    }

    function exportCSV()
    {
    }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<h2>Welcome to the GForge 3.0 Project!</h2>
<p>
GForge is an Open Source collaborative software development tool, which allows you to organize 
and manage any number of software development projects. It's perfect for managing 
large teams of software engineers and/or engineers scattered among multiple locations.
<p>
<b>Track Bugs, Patches, Feature Requests, and Support Requests</b>
<p>
<a href="/tracker/?atid=101&group_id=5&func=browse"><img src="/images/gforge.jpg" width="450" height="268" border="0"></a>
<p>
<b>New Project Management Tools:</b>
<p>
The new Project Manager allows you to create task lists, constraints, and track the 
progress of your project. The data can then be plotted in a standard Gantt chart.
<p>
<a href="http://dev.gforge.org/pm/task.php?group_id=5&group_project_id=2&func=ganttpage"><img src="/images/gantt.png" width="447" height="230" border="0"></a>
<p>

<?php
echo $HTML->boxTop($Language->getText('group','long_news'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->boxBottom();
?>

</td>

<td width="35%" valign="top">

<?php
echo $HTML->boxTop('Getting GForge');
?>
<b>Download:</b><br>
<a href="/project/showfiles.php?group_id=1">GForge3.0pre8</a><br>
<a href="http://postgresql.org/">PostgreSQL</a><br>
<a href="http://www.php.net/">PHP 4.x</a><br>
<a href="http://www.apache.org/">Apache</a><br>
<a href="http://www.gnu.org/software/mailman/">Mailman *</a><br>
<a href="http://www.python.org/">Python *</a><br>
<a href="http://jabberd.jabberstudio.org/">Jabber Server *</a><br>
* optional
<p>
<b>Contribute!</b><br>
<a href="http://gforge.org/projects/gforge/"><b>GForge Project Page</b></a><br>
<a href="http://gforge.org/forum/forum.php?forum_id=6"><b>Help Board</b></a><br>
<a href="http://gforge.org/forum/forum.php?forum_id=5"><b>Developer Board</b></a><br>
<a href="http://gforge.org/forum/forum.php?forum_id=29"><b>Oracle Board</b></a><br>
<a href="http://gforge.org/tracker/?atid=105&group_id=1&func=browse"><b>Bug Tracker</b></a><br>
<a href="http://gforge.org/tracker/?func=browse&group_id=1&atid=106"><b>Patch Submissions</b></a>
<p>
<a href="http://www.debian.org/"><b>Debian Users</b></a> can simply add 
"http://people.debian.org/~bayle/" to /etc/apt/sources.list and type 
"apt-get install gforge" to 
install a working GForge-3.0pre7 system, thanks to Christian Bayle and 
the Debian-SF project.
<p>
<?php
echo $HTML->boxBottom();
echo show_features_boxes();
?>

</td></tr></table>

<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<h3>Welcome to the GForge Project!</h3>
<p>
GForge is an Open Source collaborative software development tool, which allows you to organize
and manage any number of software development projects. It's perfect for managing
large teams of software engineers and/or engineers scattered among multiple locations.
</p>
<p>
GForge is complete with Source Code Management integration (CVS, Subversion), Forums, 
Mailing Lists, Trackers for Bugs, Patches, Feature Requests, and end-user Support as well as
task management with Gantt charting.
</p>
<h3>Professional Services</h3>
<p>
You can support the GForge project by: 
<ul>
	<li>Having us professionally install GForge on your server.</li>
	<li>Having us customize GForge to suit your needs.</li>
	<li>Having us support, upgrade, and maintain your installation.</li>
	<li>Sponsoring development of new features.</li>
	<li>See the <a href="http://gforgegroup.com/"><strong>GForge Group Professional Services</strong></a> for more info.</li>
</ul>
<p>
<strong>Track Bugs, Patches, Feature Requests, and Support Requests</strong>
</p>
<p>
<a href="http://gforge.org/tracker/?atid=101&amp;group_id=5&amp;func=browse"><img src="/images/gforge.jpg" width="450" height="268" border="0" alt="" /></a>
</p>
<p>
<strong>New Project Management Tools:</strong>
</p>
<p>
The new Project Manager allows you to create task lists, constraints, and track the
progress of your project. The data can then be plotted in a standard Gantt chart.
</p>
<p>
<a href="http://gforge.org/pm/task.php?group_id=5&amp;group_project_id=2&amp;func=ganttpage"><img src="/images/gantt.png" width="447" height="230" border="0" alt="" /></a>
</p>

<?php
echo $HTML->boxTop($Language->getText('group','long_news'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->boxBottom();
?>

</td>

<td width="35%" valign="top">
<?php
//echo $HTML->boxTop('Ad');
//echo '<center><a target="_blank" href="/redir_ad.php"><img src="bugopolis.gif" border="0"></a></center>';
echo $HTML->boxTop('Getting GForge');

?>
<strong>Download:</strong><br />
<a href="http://gforge.org/project/showfiles.php?group_id=1">GForge3.3</a><br />
<a href="http://postgresql.org/">PostgreSQL</a><br />
<a href="http://www.php.net/">PHP 4.x</a><br />
<a href="http://www.apache.org/">Apache</a><br />
<a href="http://www.gnu.org/software/mailman/">Mailman *</a><br />
<a href="http://www.python.org/">Python *</a><br />
<a href="http://jabberd.jabberstudio.org/">Jabber Server *</a><br />
* optional
<p />
<strong>Get Help</strong><br />
<a href="http://gforgegroup.com/"><strong>GForge Group Professional Services</strong></a><br />
<a href="http://gforge.org/forum/forum.php?forum_id=6"><strong>Help Board</strong></a><br />
<a href="http://gforge.org/docman/?group_id=1"><strong>Online Docs</strong></a><br />
<p />
<strong>Contribute!</strong><br />
<a href="http://gforge.org/projects/gforge/"><strong>GForge Project Page</strong></a><br />
<a href="http://gforge.org/forum/forum.php?forum_id=5"><strong>Developer Board</strong></a><br />
<a href="http://gforge.org/forum/forum.php?forum_id=29"><strong>Oracle Board</strong></a><br />
<a href="http://gforge.org/forum/forum.php?forum_id=44"><strong>SOAP API</strong></a><br />
<a href="http://gforge.org/tracker/?atid=105&amp;group_id=1&amp;func=browse"><strong>Bug Tracker</strong></a><br />
<a href="http://gforge.org/tracker/?func=browse&amp;group_id=1&amp;atid=106"><strong>Patch Submissions</strong></a><br />
<a href="http://gforge.org/tracker/?func=browse&amp;group_id=1&amp;atid=119"><strong>Feature Requests</strong></a>
<p />
<a href="http://www.debian.org/"><strong>Debian "unstable" Users</strong></a>
can simply "apt-get install gforge" to get a complete gforge system.
Other debian users, can add some lines to /etc/apt/sources.list found at
<a href="http://people.debian.org/~bayle/">"http://people.debian.org/~bayle/"</a>
or
<a href="http://people.debian.org/~lolando/">"http://people.debian.org/~lolando/"</a>
and type "apt-get install gforge" to
install a working GForge-3.0 system, thanks to Christian Bayle, Roland Mas and
the Debian-SF project.
<p />
<?php
echo $HTML->boxBottom();
echo show_features_boxes();
?>

</td></tr></table>

<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<h3>GForge helps you manage the entire development life cycle</h3>
<p>
GForge has tools to help your team collaborate, like message forums 
and mailing lists; tools to create and control access to Source Code 
Management repositories like CVS and Subversion. GForge automatically 
creates a repository and controls access to it depending on the role 
settings of the project.
</p>
<p>
Additional Tools:show_features_boshow_features_boxesshow_features_boxesxes
</p>
<ul>
	<li>Manage File Releases</li>
	<li>Document Management</li>
	<li>News announcements</li>
	<li>Surveys for users and admins</li>
	<li>Issue tracking with "unlimited" numbers of categories, text fields, etc</li>
	<li>Task management</li>
</ul>
<h3>Professional Services</h3>
<p>
The GForge project is supported and maintained by the GForgeGroup:
</p>
<ul>
	<li>Installation</li>
	<li>Support</li>
	<li>Online Training</li>
	<li>Integration into your network, with LDAP authentication</li>
	<li>See <a href="http://gforgegroup.com/"><strong>GForge Group Professional Services</strong></a> for more info.</li>
</ul>
<h3>Major New Features</h3>
<p>
<strong>Command Line Interface</strong> The unix-style interface to 
GForge uses the SOAP API to let you access, add, and update Bugs, 
Tasks, and File Releases. The <a href="http://gforge.org/projects/cli/">CLI Project</a>
is always looking for enhancements, fixes, and feedback.
</p>
<p>
<strong>Tinderbox</strong> This widely-known build tool has been 
integrated with a GForge plugin, allowing project admins to 
setup and activate tinderbox building for their projects and 
view the results through the tinderbox plugin in GForge. The 
<a href="http://gforge.org/projects/tinderbox/">Tinderbox Project</a>
is always looking for volunteers.
</p>
<p>
<strong>More Powerful Tracker</strong> Major enhancements in the 
tracker include powerful new querying capabilities, new bugzilla-like 
fields by default, a configurable template project, and tracker cloning.
</p>
<p>
<strong>MS Project Integration</strong> A plugin has been developed 
for MS Project that allows it to synchronize tasks with GForge 
task manager subprojects. The 
<a href="http://gforge.org/projects/msproject/">MS Project Plugin</a> 
is currently only available under a non-free license.
</p>
<?php
echo $HTML->boxTop(_('Latest News'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->boxBottom();
?>

</td>

<td width="35%" valign="top">
<?php
echo $HTML->boxTop('Ad');
echo '<center><h3>GForge Enterprise CDE 4.5</h3>';
echo '
<a href="http://gforgegroup.com/products/">GForge Enterprise CDE 4.5</a> from the GForge Group includes commercial-grade 
documentation, online-training, updates, and support.<br/>
<a target="_blank" href="http://gforgegroup.com/products/">
<img src="http://gforge.org/gfginstaller.png" border="0" height="164" width="250" alt="GForge Training" /></a></center>';
echo $HTML->boxMiddle('Getting GForge');

?>
<strong>Download:</strong><br />
<a href="http://gforge.org/project/showfiles.php?group_id=1">GForge4.5</a><br />
<a href="http://postgresql.org/">PostgreSQL</a><br />
<a href="http://www.php.net/">PHP 4.x</a><br />
<a href="http://www.apache.org/">Apache</a><br />
<a href="http://www.gnu.org/software/mailman/">Mailman *</a><br />
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

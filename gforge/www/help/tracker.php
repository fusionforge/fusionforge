<?php
/**
  *
  * SourceForge Help Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

help_header('Tracker Help - ' . ucwords(str_replace('_',' ',$helpname)));
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td>
<?php
	switch( $helpname ) {
		case 'assignee':
			print('This drop-down box represents the project administrator to which a tracker item is assigned.');
			break;
		case 'status':
			print('This drop-down box represents the current status of a tracker item.<br><br>
				   You can set the status to \'Pending\' if you are waiting for a response from the tracker item author.  When
				   the author responds the status is automatically reset to that of \'Open\'.  Otherwise, if the author doesn\'t 
				   respond with an admin-defined amount of time (default is 14 days) then the item is given a status of \'Deleted\'.');
			break;
		case 'category':
			print('This drop-down box represents the Category of the tracker items which is a particular section of a project.<br><br>
				   Select \'Any\' for a broader result set.');
			break;
		case 'group':
			print('This drop-down box represents the Group of the tracker items which is a list of project admin-defined options.<br><br>
				   If you are a project admin you can click the \'(admin)\' link to define your own groups.');
			break;
		case 'sort_by':
			print('The Sort By option allows you to determine how the browse results are sorted.<br><br>  You can sort by
				  ID, Priority, Summary, Open Date, Close Date, Submitter, or Assignee.  You can also have the 
				  results sorted in Ascending or Descending order.');
			break;
		case 'data_type':
			print('The Data Type option determines the type of tracker item this is.  Since the tracker rolls into one the 
				   bug, patch, support, etc... managers you need to be able to determine which one of these an item should belong.
				   <br><br>This has the added benefit of enabling an admin to turn a support request into a bug.');
			break;
		case 'priority':
			print('The priority option allows a user to define a tracker item priority (ranging from 1-Lowest to 9-Highest).<br><br>
				   This is especially helpful for bugs and support requests where a user might find a critical problem with a project.');
			break;
		case 'resolution':
			print('The resolution option represents a tracker items resolution if any.');
			break;
		case 'summary':
			print('The summary text-box represents a short tracker item summary.  Useful when browsing through several tracker items.');
			break;
		case 'canned_response':
			print('The canned response drop-down represents a list of project admin-defined canned responses to common support or bug 
				   submission.<br><br> If you are a project admin you can click the \'(admin)\' link to define your own canned responses');
			break;
		case 'comment':
			print('The comment textarea allows you to attach a comment to a tracker item when a canned response isn\'t appropriate.');
			break;
		case 'attach_file':
			print('When you wish to attach a file to a tracker item you must check this checkbox before submitting changes.');
			break;
		case 'monitor':
			print('You can monitor or un-monitor this item by clicking the "Monitor" button. <br><br><b>Note!</b> this will send you additional email. If you add comments to this item, or submitted, or are assigned this item, you will also get emails for those reasons as well!');
			break;
		default:
			print("UNKNOWN HELP REQUEST: $helpname");
			break;
	}
?>
	</td>
</tr>
<tr>
	<td align="right">
		<br><br>
		<form>
			<input type="button" value="Close Window" onClick="window.close()">
		</form>
	</td>
</tr>
</table>

<?
help_footer();
?>

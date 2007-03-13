<?php
/**
 * GForge Help Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once('pre.php');

$helpname = getStringFromRequest('helpname');

help_header('Tracker Help - ' . ucwords(str_replace('_',' ',$helpname)));
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td>
<?php
	switch( $helpname ) {
		case 'assignee':
			print( _('This drop-down box represents the person to which a tracker item is assigned.'));
			break;
		case 'status':
			print( _('This drop-down box represents the current status of a tracker item.<br /><br />You can set the status to \'Pending\' if you are waiting for a response from the tracker item author.  When the author responds the status is automatically reset to that of \'Open\'. Otherwise, if the author doesn\'t respond with an admin-defined amount of time (default is 14 days) then the item is given a status of \'Deleted\'.'));
			break;
		case 'category':
			print( _('MISSINGTEXT:help_tracker/category:TEXTMISSING'));
			break;
		case 'group':
			print(  _('MISSINGTEXT:help_tracker/group:TEXTMISSING'));
			break;
		case 'sort_by':
			print( _('The Sort By option allows you to determine how the browse results are sorted.<br /><br />  You can sort by ID, Priority, Summary, Open Date, Close Date, Submitter, or Assignee.  You can also have the results sorted in Ascending or Descending order.'));
			break;
		case 'data_type':
			print( _('The Data Type option determines the type of tracker item this is.  Since the tracker rolls into one the bug, patch, support, etc... managers you need to be able to determine which one of these an item should belong.<br /><br />This has the added benefit of enabling an admin to turn a support request into a bug.'));
			break;
		case 'priority':
			print( _('The priority option allows a user to define a tracker item priority (ranging from 1-Lowest to 5-Highest).<br /><br />This is especially helpful for bugs and support requests where a user might find a critical problem with a project.'));
			break;
		case 'resolution':
			print( _('MISSINGTEXT:help_tracker/resolution:TEXTMISSING'));
			break;
		case 'summary':
			print( _('The summary text-box represents a short tracker item summary. Useful when browsing through several tracker items.'));
			break;
		case 'canned_response':
			print( _('The canned response drop-down represents a list of project admin-defined canned responses to common support or bug submission.<br /><br /> If you are a project admin you can click the \'(admin)\' link to define your own canned responses'));
			break;
		case 'comment':
			print( _('MISSINGTEXT:help_tracker/comment:TEXTMISSING'));
			break;
		case 'attach_file':
			print( _('When you wish to attach a file to a tracker item you must check this checkbox before submitting changes.'));
			break;
		case 'monitor':
			print( _('You can monitor or un-monitor this item by clicking the "Monitor" button. <br /><br /><strong>Note!</strong> this will send you additional email. If you add comments to this item, or submitted, or are assigned this item, you will also get emails for those reasons as well!'));
			break;
		default:
			print( _('UNKNOWN HELP REQUEST:'). $helpname);
			break;
	}
?>
	</td>
</tr>
<tr>
	<td align="right">
		<br /><br />
		<form>
			<input type="button" value="<?php echo _('Close Window'); ?>" onClick="window.close()" />
		</form>
	</td>
</tr>
</table>

<?php
help_footer();
?>

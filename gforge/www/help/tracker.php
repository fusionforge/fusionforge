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
			print( $Language->getText('help_tracker','assignee'));
			break;
		case 'status':
			print( $Language->getText('help_tracker','status'));
			break;
		case 'category':
			print( $Language->getText('help_tracker','category'));
			break;
		case 'group':
			print(  $Language->getText('help_tracker','group'));
			break;
		case 'sort_by':
			print( $Language->getText('help_tracker','sort_by'));
			break;
		case 'data_type':
			print( $Language->getText('help_tracker','data_type'));
			break;
		case 'priority':
			print( $Language->getText('help_tracker','priority'));
			break;
		case 'resolution':
			print( $Language->getText('help_tracker','resolution'));
			break;
		case 'summary':
			print( $Language->getText('help_tracker','summary'));
			break;
		case 'canned_response':
			print( $Language->getText('help_tracker','canned_response'));
			break;
		case 'comment':
			print( $Language->getText('help_tracker','comment'));
			break;
		case 'attach_file':
			print( $Language->getText('help_tracker','attach_file'));
			break;
		case 'monitor':
			print( $Language->getText('help_tracker','monitor'));
			break;
		default:
			print( $Language->getText('help_tracker','unknown_help_request'). $helpname);
			break;
	}
?>
	</td>
</tr>
<tr>
	<td align="right">
		<br /><br />
		<form>
			<input type="button" value="<?php echo $Language->getText('help_tracker','close_window'); ?>" onClick="window.close()" />
		</form>
	</td>
</tr>
</table>

<?php
help_footer();
?>

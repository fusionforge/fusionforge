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
			print( $Language->getText('help_tracker','data_type'));
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

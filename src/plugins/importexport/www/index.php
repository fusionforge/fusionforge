<?php

/*
 * importexport plugin
 *
 * Daniel Perez <danielperez.arg@gmail.com>
 *
 * This is an example to watch things in action. You can obviously modify things and logic as you see fit
 */

require_once '../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

site_admin_header(array('title'=>_('Import/Export')));

//$plugin = plugin_get_object('importexport');
//$plugin_id = $plugin->getID();
$func = getStringFromRequest('func');

//$type = getStringFromRequest('type');
//$id = getStringFromRequest('id');

   /**
	* defaultView - Renders when plugin is accessed with no arguments.
	*
	*/
	function defaultView()
	{
		// TODO: Add the actions in a class to automate this.
		echo '<br />';
		echo '<h2>'._('Available Actions').'</h2>';

		echo '<h3>'._('Import').'</h3>';
		echo '<ul>';
		echo '<li>';
		echo util_make_link ('/plugins/importexport/?func=import_from_platform&',
			      _('[Import Data from a different platform.]')) ;
		echo '</li>';
		echo '<li>';
		echo util_make_link ('/plugins/importexport/?func=show_import_options',
			      _('[Show available data for import, from a specific project]')) ;
		echo '</li>';
		echo '<li>';
		echo util_make_link ('/plugins/importexport/?func=select_project_to_import_into',
			      _('[Select the projects to import into]')) ;
		echo '</li>';
		echo '</ul>';
		echo '<br />';
		
		echo '<h3>'._('Export').'</h3>';
		echo '<ul>';
		echo '<li>';
		echo util_make_link ('/plugins/importexport/?func=importData&',
			      _('[Export Data from a different platform.]')) ;
		echo '</li>';
		echo '<li>';
		echo util_make_link ('/plugins/importexport/?func=show_export_options',
			      _('[Show available data for export, from all projects]')) ;
		echo '</li>';
		echo '<li>';
		echo util_make_link ('/plugins/importexport/?func=select_export',
			      _('[Select a project to export its data]')) ;
		echo '</li>';
		echo '</ul>';
		
	}

	function importData()
	{
		echo util_make_link ('/plugins/importexport/?func=import_project&label_id=',
			      _('[Import Data]')) ;
		echo util_make_link ('/plugins/importexport/?func=export_project&project_id=',
			      _('[Export Data from a specific project]')) ;
	}

	function import_from_platform()
	{
		echo '<h2>'._('Import Data From Another Platform').'</h2>';
		?>
		<br />
		<form name="importFromPlatform" method="post"  enctype="multipart/form-data" action="<?php echo util_make_url ('/plugins/importexport/?import_from_paltform', _('Upload file')) ; ?>">
		<p>
				 <?php echo _('Import from')._(': '); ?>
		
		<select type=select name="import_from">
			<option value="Platform1">
				Platform 1
			</option>
			<option value="Platform2">
				Platform 2
			</option>
			<option value="Platform3">
				Platform 3
			</option>
		</select>
		</p><p>
		<?php echo _('Import into project')._(': '); ?>
		<select type=select name="import_into">
			<option value="Project1">
				Project 1
			</option>
			<option value="Project2">
				Project 2
			</option>
			<option value="Project3">
				Project 3
			</option>
		</select>
	</p><p>
		<?php echo _('Select file to import from')._(': '); ?>
		<input type="file" name="importfrom" size="chars"> 
	</p>
	<?php echo util_make_link ('/plugins/importexport/',
			      _('[Cancel]')) ; ?>
		<input type="hidden" name="func" value="import">
		<input type="submit" value="<?php echo _('Import') ?>">
		<input type="hidden" value="<?php echo ''; ?>" name=label_id>
		</form>
		<?php
	}

	switch ($func) {
		case 'import_from_platform':{
			import_from_platform();
			break;
		}
		case 'show_import_options':{
			
			break;
		}
		case 'select_import':{
			
			break;
		}
		case 'select_import':{
			
			break;
		}
		case 'import_project':{
			
			break;
		}
		default:{
			defaultView();
			break;
		}
	}
	site_project_footer();
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/**
 *
 * Project Registration: Project Information.
 *
 * This page is used to request data required for project registration:
 *	 o Project Public Name
 *	 o Project Registartion Purpose
 *	 o Project License
 *	 o Project Public Description
 *	 o Project Unix Name
 * All these data are more or less strictly validated.
 *
 * This is last page in registartion sequence. Its successful subsmission
 * leads to creation of new group with Pending status, suitable for approval.
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * Portions Copyright 2002-2004 (c) GForge Team
 * Portions Copyright 2002-2009 (c) Roland Mas
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'scm/SCMFactory.class.php';
//
//	Test if restricted project registration
//
if (forge_get_config('project_registration_restricted')) {
	session_require_global_perm ('approve_projects', '', 
				     sprintf (_('Project registration is restricted on %s, and only administrators can create new projects.'),
					      forge_get_config ('forge_name')));
} elseif (!session_loggedin()) {
	exit_not_logged_in();
}

$template_projects = group_get_template_projects() ;
sortProjectList ($template_projects) ;

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$full_name = trim(getStringFromRequest('full_name'));
	$purpose = trim(getStringFromRequest('purpose'));
	$description = trim(getStringFromRequest('description'));
	$unix_name = trim(strtolower(getStringFromRequest('unix_name')));
	$scm = getStringFromRequest('scm');
	$is_public = getIntFromRequest('is_public');
	$built_from_template = getIntFromRequest('built_from_template');
	$feedback = "";
	$error_msg = "";

	if (!$scm) {
		$scm = 'noscm' ;
	}

	$template_project = group_get_object ($built_from_template) ;
	if ($template_project
	    && !$template_project->isError()
	    && $template_project->isTemplate()) {
		// Valid template selected, nothing to do
	} elseif (forge_get_config('allow_project_without_template')) {
		// Empty projects allowed
		$built_from_template = 0 ;
	} elseif (count($template_projects) == 0) {
		// No empty projects allowed, but no template available
		$built_from_template = 0 ;
	} else {
		// No empty projects allowed, picking the first available template
		$built_from_template = $template_projects[0]->getID() ;
	}

	if (forge_get_config('use_scm')) {
		$scm_host = '';
		$plugin = false ;
		if (forge_get_config('use_scm') && $scm && $scm != 'noscm') {
			$plugin = plugin_get_object($scm);
			if ($plugin) {
				$scm_host = $plugin->getDefaultServer();
			}
		}

		if ( !$purpose && forge_get_config ('project_auto_approval') ) {
			$purpose = 'No purpose given, autoapprove was on';
		}

		$send_mail = ! forge_get_config ('project_auto_approval') ;

		$group = new Group();
		$u = session_get_user();
		$res = $group->create(
			$u,
			$full_name,
			$unix_name,
			$description,
			$purpose,
			'shell1',
			$scm_host,
			$is_public,
			$send_mail,
			$built_from_template
		);
		if ($res && forge_get_config('use_scm') && $plugin) {
			$group->setUsesSCM (true) ;
			$res = $group->setPluginUse ($scm, true);
		} else {
			$group->setUsesSCM (false) ;
		}

		if (!$res) {
			form_release_key(getStringFromRequest("form_key"));
			$error_msg .= $group->getErrorMessage();
		} else {
			$HTML->header(array('title'=>_('Registration complete')));

			if ( ! forge_get_config ('project_auto_approval') ) {
				printf(_('<p>Your project has been submitted to the %1$s administrators. Within 72 hours, you will receive notification of their decision and further instructions.<p/>Thank you for choosing %1$s</p>'), forge_get_config ('forge_name'));
			} else if ($group->isError()) {
				printf(_('<div class="error">ERROR: %1$s</div>'), $group->getErrorMessage() );
			} else {
				printf(_('Approving Project: %1$s'), $group->getUnixName()).'<br />';

				if (!$group->approve( user_get_object_by_name ( forge_get_config ('project_auto_approval_user') ) ) ) {
					printf(_('<div class="error">Approval ERROR: %1$s</div>'), $group->getErrorMessage() );
				} else {
					printf(_('<p>Your project has been automatically approved.  You should receive an email containing further information shortly.<p/>Thank you for choosing %1$s</p>'), forge_get_config ('forge_name'));
				}
			}

			$HTML->footer(array());
			exit();
		}
	}
} else if (getStringFromRequest('i_disagree')) {
	session_redirect("/");
} else {
	$full_name = '';
	$purpose = '';
	$description = '';
	$unix_name = '';
	$scm = '';
	$feedback = '';
}

site_header(array('title'=>_('Register Project')));
?>

<p>
<?php echo _('To apply for project registration, you should fill in basic information about it. Please read descriptions below carefully and provide complete and comprehensive data. All fields below are mandatory.') ?>
</p>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<?php
	$index=1;
	echo '<h3>'.$index.'. '._('Project full name').'</h3>';
	echo _('You should start with specifying the name of your project. The "Full Name" is descriptive, and has no arbitrary restrictions (except a 40 character limit).<p/>Full Name:<br/>'); ?>

<input size="40" maxlength="40" type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>"/>

<?php
// Don't display Project purpose if auto approval is on, because it won't be used.
if ( !forge_get_config ('project_auto_approval') ) {
	$index++;
	echo '<h3>'.$index.'. '._('Project Purpose And Summarization').'</h3>';
	echo '<p>';
	printf(_('Please provide detailed, accurate description of your project and what %1$s resources and in which way you plan to use. This description will be the basis for the approval or rejection of your project\'s hosting on %1$s, and later, to ensure that you are using the services in the intended way. This description will not be used as a public description of your project. It must be written in English.'), forge_get_config ('forge_name'));
	echo '</p>';
	echo '<textarea name="purpose" cols="70" rows="10">';
	echo htmlspecialchars($purpose);
	echo '</textarea>';
}
?>

<?php
	$index++;
	echo '<h3>'.$index.'. '. _('Project Public Description').'</h3>';
	echo '<p>';
	echo _('This is the description of your project which will be shown on the Project Summary page, in search results, etc. Maximum length is 255 chars.');
	echo '</p>';
	?>
<textarea name="description" cols="70" rows="5">
<?php echo htmlspecialchars($description); ?>
</textarea>

<?php
	$index++;
	echo '<h3>'.$index.'. '._('Project Unix Name').'</h3>';
	printf(_('In addition to full project name, you will need to choose short,"Unix" name for your project.<p/> The "Unix Name" has several restrictions because it is used in so many places around the site. They are:<ul><li>Cannot match the unix name of any other project</li><li>Must be between 3 and 15 characters in length</li><li>Must be in lower case</li><li>Can only contain characters, numbers, and dashes</li><li>Must be a valid unix username</li><li>Cannot match one of our reserved domains</li><li>Unix name will never change for this project</li></ul><p/>Your unix name is important, however, because it will be used for many things, including:<ul><li>A web site at <tt>unixname.%1$s</tt></li><li>A CVS Repository root of <tt>/cvsroot/unixname</tt> at <tt>cvs.unixname.%1$s</tt></li><li>Shell access to <tt>unixname.%1$s</tt></li><li>Search engines throughout the site</li></ul><p/>Unix Name:<br/>'), forge_get_config('web_host')) ?>

<input type="text" maxlength="15" size="15" name="unix_name" value="<?php echo htmlspecialchars($unix_name); ?>"/>

<?php
	$SCMFactory = new SCMFactory() ;
$scm_plugins=$SCMFactory->getSCMs() ;
if (forge_get_config('use_scm') && count($scm_plugins) > 0) {	
	$index++;
	echo '<h3>'.$index.'. '._('Source Code').'</h3>';
	echo _('<p>You can choose among different SCM for your project, but just one (or none at all). Please select the SCM system you want to use.</p>')."\n";
	echo '<table><tbody><tr><td><strong>'._('SCM Repository').':</strong></td>';
	echo '<td><input type="radio" name="scm" value="noscm" checked="checked">'._('No SCM').'</td>';
	foreach($scm_plugins as $plugin) {
		$myPlugin= plugin_get_object($plugin);
		echo '<td><input type="radio" name="scm" value="'.$myPlugin->name.'">'.$myPlugin->text.'</td>';
	}
	echo '</tr></tbody></table>'."\n";
}

$index++;
echo '<h3>'.$index.'. '._('Project template'). '</h3>';

if (count ($template_projects) > 1) {
	$tpv_arr = array () ;
	$tpn_arr = array () ;
	echo '<p>';
	if (forge_get_config('allow_project_without_template')) {
		printf(_('You can either start from an empty project, or pick a project that will act as a template for yours.  Your project will initially have the same configuration as the template (same roles and permissions, same trackers, same set of enabled plugins, and so on).')) ;
		$tpv_arr[] = 0 ;
		$tpn_arr[] = _('Start from empty project') ;
	} else {
		printf(_('Please pick a project that will act as a template for yours.  Your project will initially have the same configuration as the template (same roles and permissions, same trackers, same set of enabled plugins, and so on).')) ;
	}
	echo '</p>' ;
	foreach ($template_projects as $tp) {
		$tpv_arr[] = $tp->getID() ;
		$tpn_arr[] = $tp->getPublicName() ;
	}
	echo html_build_select_box_from_arrays ($tpv_arr, $tpn_arr, 'built_from_template', $template_projects[0]->getID(),
						false, '', false, '') ;
} elseif (count ($template_projects) == 1) {
	echo '<p>';
	if (forge_get_config('allow_project_without_template')) {
		printf(_('You can either start from an empty project, or use the %s project as a template for yours.  Your project will initially have the same configuration as the template (same roles and permissions, same trackers, same set of enabled plugins, and so on).'),
		       $template_projects[0]->getPublicName()) ;
		echo '</p>' ;
		$tpv_arr = array () ;
		$tpn_arr = array () ;
		$tpv_arr[] = 0 ;
		$tpn_arr[] = _('Start from empty project') ;
		$tpv_arr[] = $template_projects[0]->getID() ;
		$tpn_arr[] = $template_projects[0]->getPublicName() ;
		echo html_build_select_box_from_arrays ($tpv_arr, $tpn_arr, 'built_from_template', $template_projects[0]->getID(),
							false, '', false, '') ;
	} else {
		printf(_('Your project will initially have the same configuration as the %s project (same roles and permissions, same trackers, same set of enabled plugins, and so on).'),
		       $template_projects[0]->getPublicName()) ;
		echo '<input type="hidden" name="built_from_template" value="'.$template_projects[0]->getID().'" />' ;
	echo '</p>' ;
	}
} else {
	echo '<p>';
	printf(_('Since no template project is available, your project will start empty.')) ;
	echo '<input type="hidden" name="built_from_template" value="0" />' ;
	echo '</p>';
}

if ($sys_use_private_project) {
	$index++;
	echo '<h3>'.$index.'. '._('Visibility'). '</h3>';
	echo '<p><input type="radio" name="is_public" value="1" ';
	if (!isset($is_public) || $is_public) {
		echo 'checked="checked" ';
	}
	echo '/>'. _('Public').'</p>';

	echo '<p><input type="radio" name="is_public" value="0" ';
	if (isset ($is_public) && !$is_public) {
		echo 'checked="checked"';
	}
	echo '/>'. _('Private').'</p> ';
} else {
	echo '<input type="hidden" name="is_public" value="1" />';
}
?>

<p style="text-align: center">
<input type="submit" name="submit" value="<?php echo _('Submit') ?>"/>
<input type="submit" name="i_disagree" value="<?php echo _('Cancel') ?>"/>
</p>

</form>

<?php

site_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

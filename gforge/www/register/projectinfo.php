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
 * http://gforge.org/
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
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'scm/SCMFactory.class.php';
//
//	Test if restricted project registration
//
if ($sys_project_reg_restricted) {
	session_require(array('group'=>'1','admin_flags'=>'A'),
			sprintf (_('Project registration is restricted on %s, and only administrators can create new projects.'),
				 $sys_name));
} elseif (!session_loggedin()) {
	exit_not_logged_in();
}

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$full_name = trim(getStringFromRequest('full_name'));
	$purpose = trim(getStringFromRequest('purpose'));
	$description = trim(getStringFromRequest('description'));
	$unix_name = trim(strtolower(getStringFromRequest('unix_name')));
	$scm = getStringFromRequest('scm');
	$is_public = getIntFromRequest('is_public');
	$feedback = "";

	if (!$scm) {
		$scm = 'noscm' ;
	}

	if ($sys_use_scm) {
		$scm_host = '';
		$plugin = false ;
		if ($sys_use_scm && $scm && $scm != 'noscm') {
			$plugin = plugin_get_object($scm);
			if ($plugin) {
				$scm_host = $plugin->getDefaultServer();
			}
		}

		$group = new Group();
		$u =& session_get_user();
		$res = $group->create(
			$u,
			$full_name,
			$unix_name,
			$description,
			$purpose,
			'shell1',
			$scm_host,
			$is_public
		);
		if ($res && $sys_use_scm && $plugin) {
			$group->setUsesSCM (true) ;
			$res = $group->setPluginUse ($scm, true);
		} else {
			$group->setUsesSCM (false) ;
		}

		if (!$res) {
			form_release_key(getStringFromRequest("form_key"));
			$feedback .= $group->getErrorMessage();
		} else {
			$HTML->header(array('title'=>_('Registration complete')));
	
			?>
	
			<p><?php printf(_('Your project has been submitted to the %1$s administrators. Within 72 hours, you will receive notification of their decision and further instructions.<p/>Thank you for choosing %1$s'), $GLOBALS['sys_name'])?>
			</p>
	
			<?php
	
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

site_header(array('title'=>_('Project Information')));
?>

<p><?php echo _('To apply for project registration, you should fill in basic information about it. Please read descriptions below carefully and provide complete and comprehensive data. All fields below are mandatory.') ?>
</p>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<?php echo _('<h3>1. Project full name</h3>You should start with specifying the name of your project. The "Full Name" is descriptive, and has no arbitrary restrictions (except a 40 character limit).<p/>Full Name:<br/>') ?>

<input size="40" maxlength="40" type="text" name="full_name" value="<?php echo htmlspecialchars(stripslashes($full_name)); ?>"/>

<?php printf(_('<h3>2. Project Purpose And Summarization</h3><strong> Please provide detailed, accurate description of your project and what %1$s resources and in which way you plan to use. This description will be the basis for the approval or rejection of your project\'s hosting on %1$s, and later, to ensure that you are using the services in the intended way. This description will not be used as a public description of your project. It must be written in English.</strong>'), $GLOBALS['sys_name'])?>
<p/>
<textarea name="purpose" cols="70" rows="10">
<?php echo htmlspecialchars(stripslashes($purpose)); ?>
</textarea>

<?php echo _('<h3>3. Project Public Description</h3><p>This is the description of your project which will be shown on the Project Summary page, in search results, etc. It should not be as comprehensive and formal as Project Purpose description (step 2), so feel free to use concise and catchy wording. Maximum length is 255 chars.</p>')?>
<br />
<br />
<textarea name="description" cols="70" rows="5">
<?php echo htmlspecialchars(stripslashes($description)); ?>
</textarea>

<?php printf(_('<h3>4. Project Unix Name</h3>In addition to full project name, you will need to choose short,"Unix" name for your project.<p/> The "Unix Name" has several restrictions because it is used in so many places around the site. They are:<ul><li>Cannot match the unix name of any other project</li><li>Must be between 3 and 15 characters in length</li><li>Must be in lower case</li><li>Can only contain characters, numbers, and dashes</li><li>Must be a valid unix username</li><li>Cannot match one of our reserved domains</li><li>Unix name will never change for this project</li></ul><p/>Your unix name is important, however, because it will be used for many things, including:<ul><li>A web site at <tt>unixname.%1$s</tt></li><li>A CVS Repository root of <tt>/cvsroot/unixname</tt> at <tt>cvs.unixname.%1$s</tt></li><li>Shell access to <tt>unixname.%1$s</tt></li><li>Search engines throughout the site</li></ul><p/>Unix Name:<br/>'), $GLOBALS['sys_default_domain']) ?>

<input type="text" maxlength="15" size="15" name="unix_name" value="<?php echo htmlspecialchars(stripslashes($unix_name)); ?>"/>

<?php
	$SCMFactory = new SCMFactory() ;
$scm_plugins=$SCMFactory->getSCMs() ;
if ($sys_use_scm && count($scm_plugins) > 0) {	
	echo _('<h3>5. SCM</h3><p>You can choose among different SCM for your project, but just one (or none at all). Please select the SCM system you want to use.</p>')."\n";
	echo '<table><tbody><tr><td><strong>'._('SCM Repository').':</strong></td>';
	echo '<td><input type="radio" name="scm" value="noscm" checked="checked">'._('No SCM').'</td>';
	foreach($scm_plugins as $plugin) {
		$myPlugin= plugin_get_object($plugin);
		echo '<td><input type="radio" name="scm" value="'.$myPlugin->name.'">'.$myPlugin->text.'</td>';
	}
	echo '</tr></tbody></table>'."\n";
}

?>
<?php
	if ($sys_use_private_project) {
		echo "<p><input type=\"radio\" name=\"is_public\" value=\"1\" ";
		if (!isset($is_public) || $is_public) {
			echo ' checked';
		}
		echo ">". _('Public')."</p>";

		echo "<p><input type=\"radio\" name=\"is_public\" value=\"0\" ";
		if (isset ($is_public) && !$is_public) {
			echo ' checked';
		}
		echo ">". _('Private')."</p>";
	} else {
		echo "<input type=\"hidden\" name=\"is_public\" value=\"1\">";
	}
?>

<div align="center">
<input type="submit" name="submit" value="<?php echo _('Submit') ?>"/> <input type="submit" name="i_disagree" value="<?php echo _('Cancel') ?>"/>
</div>

</form>

<?php

site_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

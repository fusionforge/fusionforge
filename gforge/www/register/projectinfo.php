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
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/license.php');
require_once('common/scm/SCMFactory.class');
//
//	Test if restricted project registration
//
if ($sys_project_reg_restricted) {
	session_require(array('group'=>'1','admin_flags'=>'A'));
}

session_require(array('isloggedin'=>'1'));

if ($submit) {
	$full_name = trim($full_name);
	$purpose = trim($purpose);
	$license_other = trim($license_other);
	$description = trim($description);
	$unix_name = strtolower($unix_name);

	if ($sys_use_scm && !$scm) {
		$feedback .= $Language->getText('register','scm_not_selected');
	} else {
		$group = new Group();
		$u =& session_get_user();
		$res = $group->create(
			$u,
			$full_name,
			$unix_name,
			$description,
			$license,
			$license_other,
			$purpose
		);
		$res = $res && $group->setPluginUse($scm,true);
		if (!$res) {
			$feedback .= $group->getErrorMessage();
		} else {
			$HTML->header(array('title'=>$Language->getText('register','registration_complete'),'pagename'=>'register_complete'));
	
			?>
	
			<p><?php echo $Language->getText('register','project_submitted',array($GLOBALS['sys_name']))?>
			</p>
	
			<?php
	
			$HTML->footer(array());
			exit();
		}
	}
} else if ($i_disagree) {
	session_redirect("/");
}

site_header(array('title'=>$Language->getText('register','project_information'),'pagename'=>'register_projectinfo'));
?>

<p><?php echo $Language->getText('register','apply_for_registration') ?>
</p>

<form action="<?php echo $PHP_SELF; ?>" method="post">

<?php echo $Language->getText('register','project_full_name') ?>

<input size="40" maxlength="40" type=text name="full_name" value="<?php echo stripslashes($full_name); ?>">

<?php echo $Language->getText('register','purpose_and_summarization', array($GLOBALS['sys_name']))?>
<p>
<font size="-1">
<textarea name="purpose" wrap="virtual" cols="70" rows="10">
<?php echo stripslashes($purpose); ?>
</textarea>
</font>

<?php echo $Language->getText('register','project_license', array($GLOBALS['sys_name'])) ?>

<?php
echo license_selectbox('license',$license);
?>
<p>
<?php echo $Language->getText('register','other_license') ?>
<br />
<textarea name="license_other" wrap=virtual cols=60 rows=5>
<?php echo stripslashes($license_other); ?>
</textarea>
<p>

<?php echo $Language->getText('register','project_description')?>
</p>
<font size="-1">
<textarea name="description" wrap="virtual" cols="70" rows="5">
<?php echo stripslashes($description); ?>
</textarea>
</font>

<?php echo $Language->getText('register','project_unix_name',array($GLOBALS['sys_default_domain'])) ?>

<input type=text maxlength="15" SIZE="15" name="unix_name" value="<?php echo $unix_name; ?>">

<?php
	$SCMFactory=new SCMFactory();
	if ($sys_use_scm) {
		$scm_plugins=$SCMFactory->getSCMs();
		if(count($scm_plugins)!=0) {	
			echo $Language->getText('register','choose_scm');
			if(count($scm_plugins)==1) {
				echo $Language->getText('register','one_scm',$scm_plugins[0]).'<br /><br />';
				echo '<input type=\'hidden\' name=\'scm\' value=\''. $scm_plugins[0].'\'/>';
			} else {
				$checked=true;
				foreach($scm_plugins as $scm) {
					$myPlugin= plugin_get_object($scm);
					echo '<p><input type=\'radio\' name=\'scm\' ';
					if ($checked) {
						echo 'CHECKED ';
					}
					echo 'value='.$myPlugin->name;
					echo '>'.$myPlugin->text.'</input></p>';
					$checked=false;
				}
			}
		} else {
			echo "Error - Site has SCM but no plugins registered";
		}
	}

?>


<div align="center">
<input type=submit name="submit" value="<?php echo $Language->getText('register','i_agree') ?>"> <input type=submit name="i_disagree" value="<?php echo $Language->getText('register','i_disagree') ?>">
</div>

</form>

<?php

site_footer(array());

?>


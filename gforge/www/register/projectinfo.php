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
require_once('common/include/vars.php');

session_require(array('isloggedin'=>'1'));

if ($submit) {
	$full_name = trim($full_name);
	$purpose = trim($purpose);
	$license_other = trim($license_other);
	$description = trim($description);
	$unix_name = strtolower($unix_name);

	/*
		Fierce validation
	*/

	if (strlen($full_name)<3) {
		$feedback .= "Invalid Full Name";
	} else if (!account_groupnamevalid($unix_name)) {
		$feedback .= "Invalid Unix Name";
	} else if (db_numrows(db_query("SELECT group_id FROM groups WHERE unix_group_name='$unix_name'")) > 0) {
		$feedback .= "Unix group name already taken";
	} else if (strlen($purpose)<20) {
		$feedback .= "Please describe your
			Registration Purpose in a more comprehensive manner";
	} else if (strlen($description)<10) {
		$feedback .= "Please use more comprehensive Project Description";
	} else if (!$license) {
		$feedback .= "You have not chosen a license";
	} else if ($license!="other" && $license_other) {
		$feedback .= "Conflicting licenses choice";
	} else if ($license=="other" && strlen($license_other)<50) {
		$feedback .= "Please give more comprehensive licensing description";
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

		if (!$res) {
			$feedback .= $group->getErrorMessage();
		} else {
			$HTML->header(array('title'=>'Registration Complete','pagename'=>'register_complete'));

			?>

			<p>Your project has been submitted to the <?php echo $GLOBALS['sys_name']; ?> admininstrators.
			Within 72 hours, you will receive decision notification and further
			instructions.
			</p>
			<p>
			Thank you for choosing <?php echo $GLOBALS['sys_name']; ?>.
			</p>

			<?php

			$HTML->footer(array());
			exit();
		}

	}
} else if ($i_disagree) {
	session_redirect("/");
}

site_header(array('title'=>'Project Information','pagename'=>'register_projectinfo'));
?>

<p>
To apply for project registration, you should fill in basic information
about it. Please read descriptions below carefully and provide complete
and comprehensive data. All fields below are mandatory.
</p>

<form action="<?php echo $PHP_SELF; ?>" method="POST">

<H3>1. Project Full Name</H3>


<p>
You should start with specifying the name of your project.
The "Full Name" is descriptive, and has no arbitrary restrictions (except
a 40 character limit).
</p>


Full Name:
<BR>
<INPUT size="40" maxlength="40" type=text name="full_name" value="<?php echo $full_name; ?>">

<H3>2. Project Purpose and Summarization</H3>
<P>
<B></B>
<P>
<b>
Please provide detailed, accurate description of your project and
what <?php echo $GLOBALS['sys_name']; ?> resources and in which way you plan to use. This
description will be the basis for the approval or rejection of
your project's hosting on <?php echo $GLOBALS['sys_name']; ?>, and later, to ensure that
you are using the services in the intended way. This description
will not be used as a public description of your project. It must
be written in English.
</b>
<P>
<font size="-1">
<TEXTAREA name="purpose" wrap="virtual" cols="70" rows="10">
<?php echo $purpose; ?>
</TEXTAREA>
</font>

<h3>3. License</h3>

<P><B><I>If you are applying for a website-only project, please
select "website-only" from the choices below and proceed.</I></B>

<P><?php echo $GLOBALS['sys_name']; ?> was created to advance Open Source software development.
To keep things simple, we are relying on the outstanding work
of the <A href="http://www.opensource.org">Open Source Initiative</A>
for our licensing choices.

<P>We realize, however that there may be other licenses out there
that may better fit your needs. If you wish to use a license that is
not OSI Certified, please let us know why you wish to use another
license.

<P>Choosing a license is a serious decision. Please take some time
to read the text (and our explanations) of several licenses before
making a choice about your project.

<P>You may change the license for your project at a
later date, so long as you have a legal capability to do so, your file
release clearly relates this change, and your filemap categorization is
updated appropriately. <i>Please note that license changes are not
retroactive (i.e. do not apply to products already released under
OpenSource license).</i>

<P><?php echo $GLOBALS['sys_name']; ?> is not responsible for legal discrepencies regarding
your license.

<P><B>Licenses</B>

<UL>
<LI><A href="http://www.opensource.org/licenses/gpl-license.html" target="_blank">GNU General Public License (GPL)</A>
<LI><A href="http://www.opensource.org/licenses/lgpl-license.html" target="_blank">GNU Library Public License (LGPL)</A>
<LI><A href="http://www.opensource.org/licenses/bsd-license.html" target="_blank">BSD License</A>
<LI><A href="http://www.opensource.org/licenses/mit-license.html" target="_blank">MIT License</A>
<LI><A href="http://www.opensource.org/licenses/artistic-license.html" target="_blank">Artistic License</A>
<LI><A href="http://www.opensource.org/licenses/mozilla1.0.html" target="_blank">Mozilla Public License 1.0 (MPL)</A>
<LI><A href="http://www.opensource.org/licenses/qtpl.html" target="_blank">Q Public License (QPL)</A>
<LI><A href="http://www.opensource.org/licenses/ibmpl.html" target="_blank">IBM Public License 1.0</A>
<LI><A href="http://www.opensource.org/licenses/mitrepl.html" target="_blank">MITRE Collaborative Virtual Workspace License (CVW License)</A>
<LI><A href="http://www.opensource.org/licenses/ricohpl.html" target="_blank">Ricoh Source Code Public License 1.0</A>
<LI><A href="http://www.opensource.org/licenses/pythonpl.html" target="_blank">Python License</A>
<LI><A href="http://www.opensource.org/licenses/zlib-license.html" target="_blank">zlib/libpng License</A>
<LI><A href="http://www.opensource.org/licenses/apachepl.html" target="_blank">Apache Software License</A>
<LI><A href="http://www.opensource.org/licenses/vovidapl.html" target="_blank">Vovida Software License 1.0</A>
<LI><A href="http://www.opensource.org/licenses/sisslpl.html" target="_blank">Sun Internet Standards Source License (SISSL)</A>
<LI><A href="http://www.opensource.org/licenses/intel-open-source-license.html" target="_blank">Intel Open Source License</A>
<LI><A href="http://www.opensource.org/licenses/mozilla1.1.html" target="_blank">Mozilla Public License 1.1 (MPL 1.1)</A>
<LI><A href="http://www.opensource.org/licenses/jabberpl.html" target="_blank">Jabber Open Source License</A>
<LI><A href="http://www.opensource.org/licenses/nokia.html" target="_blank">Nokia Open Source License</A>
<LI><A href="http://www.opensource.org/licenses/sleepycat.html" target="_blank">Sleepycat License</A>
<LI><A href="http://www.opensource.org/licenses/nethack.html" target="_blank">Nethack General Public License</A>
<LI><A href="http://oss.software.ibm.com/developerworks/opensource/license-cpl.html" target="_blank">IBM Common Public License</A>
<LI><A href="http://www.opensource.apple.com/apsl/" target="_blank">Apple Public Source License</A>
<LI><A href="http://www.sourceforge.net/register/publicdomain.txt" target="_blank">Public Domain</A>
<LI>Website Only
<LI>Other/Proprietary License

</UL>

<P><B>License for This Project</B>

<B>Your License:</B><BR>
<?php

// create SELECT based on $LICENSE array in common/include/vars.php
//
	echo '<SELECT NAME="license">';
	echo '<OPTION value="">(select)'."\n";
	while (list($k,$v) = each($LICENSE)) {
		print "<OPTION value=\"$k\"";
		if ($license == $k) {
			print " SELECTED";
		}
		print ">$v\n";
	}
	echo '</SELECT>';

?>
<P>
If you selected "other", please provide an explanation along
with a description of your license. Realize that other licenses may
not be approved. Also, it may take additional time to make a decision
for such project, since we will need to check that license is compatible
with the OpenSource definition.
<BR>
<TEXTAREA name="license_other" wrap=virtual cols=60 rows=5>
<?php echo $license_other; ?>
</TEXTAREA>
<P>


<h3>4. Project Public Description</h3>
<p>
This is the description of your project which will be shown on
the Project Summary page, in search results, etc. It should not
be as comprehensive and formal as Project Purpose description
(step 2), so feel free to use concise and catchy wording. Maximum
length is 255 chars.
</p>
<font size="-1">
<TEXTAREA name="description" wrap="virtual" cols="70" rows="5">
<?php echo $description; ?>
</TEXTAREA>
</font>

<H3>5. Project Unix Name</H3>
<p>
In addition to full project name, you will need to choose short,
"Unix" name for your project.
</p>

<P> The "Unix Name" has several restrictions because it is
used in so many places around the site. They are:

<UL>
<LI>Cannot match the unix name of any other project
<LI>Must be between 3 and 15 characters in length
<LI>Must be in lower case
<LI>Can only contain characters, numbers, and dashes
<LI>Must be a valid unix username
<LI>Cannot match one of our reserved domains
<LI>Unix name will never change for this project
</UL>

<P>Your unix name is important, however, because it will be used for
many things, including:

<UL>
<LI>A web site at <tt>unixname.<?php echo $GLOBALS['sys_default_domain']; ?></tt>
<LI>A CVS Repository root of <tt>/cvsroot/unixname</tt> at <tt>cvs.unixname.<?php echo $GLOBALS['sys_default_domain']; ?></tt>
<LI>Shell access to <tt>unixname.<?php echo $GLOBALS['sys_default_domain']; ?></tt>
<LI>Search engines throughout the site
</UL>

<P>Unix Name:
<BR>
<input type=text maxlength="15" SIZE="15" name="unix_name" value="<?php echo $unix_name; ?>">

<div align="center">
<input type=submit name="submit" value="I AGREE"> <INPUT type=submit name="i_disagree" value="I DISAGREE">
</div>

</form>

<?php

site_footer(array());

?>


<?php
/**
  *
  * Project Registration: Services&Requirements (informative)
  *
  * This page presents informative description of prerequisites and
  * requirements for hosting on SourceForge. This page doesn't require
  * any actions.
  *
  * Next in sequence: tos.php
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: requirements.php,v 1.20 2001/05/13 17:57:29 pfalcon Exp $
  *
  */


require_once('pre.php');

session_require(array(isloggedin=>1));

$HTML->header(array(title=>"Project Requirements",'pagename'=>'register_requirements'));
?>

<p>
We are now offering a full suite of services for Sourceforge projects. If
you haven't already, please be sure to browse the most recent revision of
the Sourceforge Services.
</p>

<p>
<b>Use of Project Account</b>
</p>

<p>
The space given to you on the original Sourceforge servers is given
for the expressed purpose of Open Source development or, in the case
of web sites, the advancement of Open Source.  The space given to you
on this server, on the other hand, is given for the expressed purpose
of whatever the local Sourceforge maintainer has decided.
</p>

<p>
<b>Creative Freedom</b>
</p>

<p>
It is the intent of the Sourceforge software to allow you creative
freedom on your project.  However, for the legal protection of this
site and yours there might be limits imposed by the local Sourceforge
administrator.  Details about these restrictions are described in the
Terms of Service.
</p>

<p>
<b>Advertisements</b>
</p>

<p>
You may not place any revenue-generating advertisements on a site
hosted at sourceforge.net.  Contact the local Sourceforge
administrator about the present site.
</p>

<p>
<b>Sourceforge Link</b>
</p>

<p>
If you host a web site at this Sourceforge site, you might want to
place one of our approved graphic images on your site with a link back
to the Sourceforge site.  The graphic may either link to the main
Sourceforge site or to your project page on the site.  For information
about how to insert a SourceForge logo which will track your
pageviews, please read the Sourceforge documentation.  Again, this
condition is subject to the will of the local Sourceforge
administrator.
</p>

<p>
<b>Open Source/Rights to Code</b>
</p>

<p>
You will be presented with a choice of licenses for your project.
Some of them are Open Source approved, some are not.  The ones that
are Open Source approved also allow us to make your code available to
the general public: although you may choose to stop hosting your
project with us, the nature of these licenses will allow us to
continue to make your code available.  It is therefore advised to
think twice before you choose a license, especially if you choose one
the local Sourceforge administrator might have added to the default
ones.
</p>

<p>
If you wish to use another license that is not currently listed, let
the local Sourceforge administrator know and he will review these
requests on a case-by-case basis.
</p>

<p>
It is the intent of the software to provide a permanent home for all
versions of your code.  The Sourceforge administrator does reserve the
right, however, to terminate your project if there is due cause, in
accordance with the Terms of Service.
</p>

<BR><H3 align=center><a href="tos.php">Step 2: Terms of Service Agreement</a></H
3>

<?php
$HTML->footer(array());
?>


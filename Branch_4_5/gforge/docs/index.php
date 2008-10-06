<?php include("doc_utils.php"); ?>

<!-- ------------------------ HEADER ------------------------ -->
<html>
<head>
  <title>SourceForge: Engine Team Documentation</title>
</head>
<body bgcolor="#FFFFFF">

<table width="100%">
  <tr>
    <td>
      <strong><font size=+1>SourceForge: Engine Team Documentation</font></strong><br />
      <font size="-1">$Id$<br />
      SourceForge Engine Team [<a href="mailto:alexandria-devel@sourceforge.net">email</a>]</font>
    </td>
    <td>
      <a href="http://sourceforge.net"><img src="images/sflogo2-105a.png" width="108" height="53" border="0"></a>
    </td>
  </tr>
</table>

<!-- ------------------------ MENU ------------------------ -->
<p><hr /><p>

<dd>
  <a href="#0">0. Quick Links</a>
</dd>

<dd>
  <a href="#1">1. Background</a>
</dd>

<dd>
  <a href="#2">2. Project Management</a>
</dd>

<dd>
  <a href="#3">3. Requirements, Design, Implementation</a>
</dd>

<dd>
  <a href="#4">4. Projects</a>
</dd>

<p>
  <strong>Note:</strong> revision histories in bold denote changes in the last week.
</p>

<!-- ------------------------ SECTION 0 ------------------------ -->
<p><hr /><p>

<a name="0"></a><strong>0. Quick Links</strong>

<p>
  <strong>List Archives:</strong>&nbsp;|&nbsp;alexandria-devel&nbsp;|&nbsp;alexandria-cvs&nbsp;|&nbsp;<a href="https://lists.valinux.com/archives/sf-engine/">sf-engine</a>&nbsp;|&nbsp;<a href="https://lists.valinux.com/archives/sf-onsite/">sf-onsite</a>|<br />
  <strong>Sandboxes:</strong>&nbsp;|&nbsp;<a href="http://webdev.tperdue.sourceforge.net">tperdue</a>&nbsp;|&nbsp;<a href="http://webdev.dbrogdon.sourceforge.net">dbrogdon</a>&nbsp;|&nbsp;<a href="http://webdev.pfalcon.sourceforge.net">pfalcon</a>&nbsp;|&nbsp;<a href="http://webdev.jbyers.sourceforge.net">jbyers</a>&nbsp;|<br />
  <strong>Reference:</strong>&nbsp;|&nbsp;<a href="http://webdev.sourceforge.net/cgi-bin/viewcvs.cgi/">ViewCVS</a>&nbsp;|&nbsp;<a href="https://vaweb.valinux.com/Marketing/SourceForge/">product marketing</a>&nbsp;|<br />
</p>

</table>

<!-- ------------------------ SECTION 1 ------------------------ newer than-->
<p><hr /><p>

<a name="1"></a><strong>1. Background</strong>

<ul>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>Engine Team Charter, Vision</td>
    <td><font size="-1">[<a href="background/charter.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/background/charter.html"); ?></td>
  </tr>

  <tr>
    <td>Engine Team Org Chart</td>
    <td><font size="-1">[<a href="background/org_chart.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/background/org_chart.html"); ?></td>
  </tr>

  <tr>
    <td>Who's Who</td>
    <td><font size="-1">[<a href="background/whos_who.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/background/whos_who.html"); ?></td>
  </tr>
</table>
</ul>

<!-- ------------------------ SECTION 2 ------------------------ -->
<p><hr /><p>

<a name="1"></a><strong>2. Project Management</strong>

<ul>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>MRD, ERD, PRD: Requirements Discovery</td>
    <td><font size="-1">[<a href="project_management/requirements.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/requirements.html"); ?></td>
  </tr>

  <tr>
    <td>Change Control and the Change Czar</td>
    <td><font size="-1">[<a href="project_management/change_control.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/change_control.html"); ?></td>
  </tr>  

  <tr>
    <td>Source Control</td>
    <td><font size="-1">[<a href="project_management/source_control.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/source_control.html"); ?></td>
  </tr>

  <tr>
    <td>Source Control: Branching Diagram</td>
    <td><font size="-1">[<a href="project_management/source_branching.sda">SDA</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/source_branching.sda"); ?></td>
  </tr>

  <tr>
    <td>Community Involvement</td>
    <td><font size="-1">[<a href="project_management/community.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/community.html"); ?></td>
  </tr>

  <tr>
    <td>Technical and Document Review</td>
    <td><font size="-1">[<a href="project_management/review.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/review.html"); ?></td>
  </tr>

  <tr>
    <td>Development Process</td>
    <td><font size="-1">[<a href="project_management/development_process.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/development_process.html"); ?></td>
  </tr>

  <tr>
    <td>PS Engineers on the Engine Team</td>
    <td><font size="-1">[<a href="project_management/ps_engineers.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/project_management/ps_engineers.html"); ?></td>
  </tr>
</table>
</ul>

<!-- ------------------------ SECTION 3 ------------------------ -->
<p><hr /><p>

<a name="1"></a><strong>3. Requirements, Design, Implementation</strong>

<ul>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>Architecture Tour (slides)</td>
    <td><font size="-1">[<a href="architecture/tour/standards.htm">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/architecture/tour/standards.htm"); ?></td>
  </tr>

  <tr>
    <td>PHP Template Example: Logic and Presentation</td>
    <td><font size="-1">[<a href="architecture/templating.php">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/architecture/templating.php"); ?></td>
  </tr>

  <tr>
    <td>Coding Standards</td>
    <td><font size="-1">[<a href="architecture/coding_standards.html">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/architecture/coding_standards.html"); ?></td>
  </tr>

  <tr>
    <td>Site-Wide Structure and Use Cases</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>
</table>
</ul>

<!-- ------------------------ SECTION 4 ------------------------ -->
<p><hr /><p>

<a name="1"></a><strong>4. Projects</strong>

<ul>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>Code Cleanup (March Madness and April Fool's)</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>XML Interface</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>Site Searching</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>Geocrawler II</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>Reporting and Statistics Engine</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>UI Redesign</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>Doc Manager II</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>Content Manager</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>

  <tr>
    <td>Foundry II</td>
    <td><font size="-1">[<a href="">HTML</a>]<font></td>
    <td><?php echo util_cvs_status("docs/"); ?></td>
  </tr>
</table>
</ul>

<!-- ------------------------ FOOTER ------------------------ -->
<p><hr /><p>

<p align="right">
  <font size="-1">
    Copyright &copy; 1999, 2000, 2001 <a href="http://www.valinux.com/">VA Linux Systems, Inc.</a>
  </font>
</body>
</html>
<br />




<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

function snippet_mainpage() {
	global $SCRIPT_LANGUAGE,$SCRIPT_CATEGORY;

	$return .= 
	'<FONT face="arial, helvetica">
	<P>
	The purpose of this archive is to let you share your code snippets, scripts, 
	and functions with the Open Source Software Community.
	<P>
	You can create a "new snippet", then post additional versions of that 
	snippet quickly and easily.
	<P>
	Once you have snippets posted, you can then create a "Package" of snippets. 
	That package can contain multiple, specific versions of other snippets.
	<P>
	<H3>Browse Snippets</H3>
	<P>
	You can browse the snippet library quickly:
	<BR>
	<P>
	<TABLE WIDTH="100%" BORDER="0">
	<TR><TD>

	</TD></TR>

	<TR><TD>
	<B>Browse by Language:</B>
	<P>';

	$count=count($SCRIPT_LANGUAGE);
	for ($i=1; $i<$count; $i++) {
		$sql="SELECT count(*) FROM snippet WHERE language=$i";
		$result = db_query ($sql);

		$return .= '
		<LI><A HREF="/snippet/browse.php?by=lang&lang='.$i.'">'.$SCRIPT_LANGUAGE[$i].'</A> ('.db_result($result,0,0).')<BR>';
	}

	$return .= 	
	'</TD>
	<TD>
	<B>Browse by Category:</B>
	<P>';

	$count=count($SCRIPT_CATEGORY);
	for ($i=1; $i<$count; $i++) {
		$sql="SELECT count(*) FROM snippet WHERE category=$i";
		$result = db_query ($sql);

		$return .= '
		<LI><A HREF="/snippet/browse.php?by=cat&cat='.$i.'">'.$SCRIPT_CATEGORY[$i].'</A> ('.db_result($result,0,0).')<BR>';
	}


	$return .=
	'</TD>
	</TR>
	</TABLE>';

	return $return;

}

?>

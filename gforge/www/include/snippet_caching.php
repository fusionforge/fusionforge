<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

function snippet_mainpage() {
	global $SCRIPT_LANGUAGE,$SCRIPT_CATEGORY,$Language;

	$return .= 
	'<FONT face="arial, helvetica">
	'.$Language->SNIPPETFRONT.'
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

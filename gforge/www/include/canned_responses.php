<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

function add_canned_response($title, $text)
{
		global $feedback;
		if( !db_query("INSERT INTO canned_responses (response_title, response_text) VALUES('$title','$text')") ) {
			$feedback .= db_error();
		}
}

function get_canned_responses()
{
	$result = db_query("SELECT response_id, response_title FROM canned_responses");
	while( $res_array = db_fetch_array($result) ) {
		$ids[] = $res_array["response_id"];
		$texts[] = $res_array["response_title"];
	}

	return html_build_select_box_from_arrays($ids, $texts, "response_id");
}

?>

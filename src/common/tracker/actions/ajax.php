<?php

$function = getStringFromRequest('function');

switch ($function) {
	case 'get_canned_response':
		//$atid = getIntFromRequest('atid');
		$canned_response_id = getIntFromRequest('canned_response_id');
		echo get_canned_response($canned_response_id);
		break;
	
	default:
		echo '';
		break;
}

function get_canned_response($id) {
	$result = db_query_params('SELECT body FROM artifact_canned_responses WHERE id=$1',
		array ($id));
	if (! $result || db_numrows($result) < 1) {
		return '';
	}
	else {
		return db_result($result, 0, 'body');
	}
}

?>
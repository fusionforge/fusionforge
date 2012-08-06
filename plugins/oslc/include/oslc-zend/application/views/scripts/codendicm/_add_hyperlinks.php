<?php


function get_url() {
	 $url = 'http';
	 if ($_SERVER["HTTPS"] == "on") {$url .= "s";}
	 $url .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  	$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	 } else {
	  	$url .= $_SERVER["SERVER_NAME"];
	 }
	 return $url;
}

function add_href($match){
	return '<a href="'.$match[0].'">'.$match[0].'</a>';
}

function add_links($text, $url)	{
	$preg_url = "/".str_replace("/", "\/", $url)."[^&\"]*"."/";
	$replacement = preg_replace_callback($preg_url, "add_href", $text);
	return $replacement;
}



?>

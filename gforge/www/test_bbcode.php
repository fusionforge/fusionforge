<?php
require_once('www/include/pre.php');    // Initial db and session library, opens session
// bbcode, smilies support
require_once('common/text/TextSupport.class');


$HTML->header(array('title'=>'Test BBCODE'));

$text_support = new TextSupport();


if (GetStringFromRequest('doit')){
	$make_clickable=1; 
	$smilie_on=1; 
	$bbcode_on=1; 
	$strip_html=0;
	// we prepare the text for db insert
	$text = GetStringFromRequest('text');
	$bbcode_uid = $text_support->prepareText($text,make_clickable,$strip_html,$smilie_on,$bbcode_on);
	
	echo "<h4>Text to insert into db:</h4> $text <br><br>";
	// we display the text
	echo "<h4>How text will be displayed:</h4> ";
	echo $text_support->displayText($text, $make_clickable, $smilie_on, $bbcode_on, $bbcode_uid);
	
	$HTML->footer(array());
	exit;
}



echo "<H4>Exemple of bbcode text field enable</H4>";


$text_support->displayForm('form', 'text','test_bbcode.php');
echo '<table><tr><td valign="top">';
echo $text_support->displaySmiliesList();
echo '</td><td valign="top">';
$text_support->displayBBCodeHelpTools();
echo '<br>';
$text_support->displayTextField('text');
echo '</td></tr></table>';
echo '<input name="doit" type="hidden" value="1">  ';
echo '<input type="submit" name="Submit" value="Test it!">  
</form>';




$HTML->footer(array());

?>
<?php
require(dirname(__FILE__) . '/commons.php');
printPageHeader('&Lang.HistoryTitle;',true,'&Lang.HistoryTitle; ');

printPageContent('');
?>

<form method="post" action="">
<div>
<select name="Date" id="Date" size="10">
&Page.History;
</select>
</div>
<p id="PPreviewSave"><input type="submit" id="Preview" name="Preview" value="&Lang.Preview;" accesskey="p"/><input type="submit" id="Save" name="Save" value="&Lang.Restore;" accesskey="s"/></p>
</form>

<?php
printPageFooter(true,false);
?>

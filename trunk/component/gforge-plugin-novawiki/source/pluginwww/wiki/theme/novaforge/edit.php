<?php
require(dirname(__FILE__) . '/commons.php');
printPageHeader('&Lang.EditTitle;',true,'&Lang.EditTitle; ');

printPageContent(' class="Preview"');
?>

<form method="post" action="">
<div>
<textarea id="Wiki" name="Wiki" cols="80" rows="30">&Page.Wiki;</textarea>
</div>
<p id="PPreviewSave"><input type="submit" id="Preview" name="Preview" value="&Lang.Preview;" accesskey="p"/><input type="submit" id="Save" name="Save" value="&Lang.Save;" accesskey="s"/></p>
</form>

<div id="Rules">
&Lang.Rules;
</div>

<?php
printPageFooter(false,true);
?>

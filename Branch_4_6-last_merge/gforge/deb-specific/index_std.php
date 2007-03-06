<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
<?php
echo $Language->getText('home','about_blurb', array($GLOBALS['sys_name'], $GLOBALS['sys_default_domain'])) ;

echo $HTML->boxTop($Language->getText('group','long_news'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->boxBottom();
?>

</td>

<td width="35%" valign="top">

<?php
echo show_features_boxes();
?>

</td></tr></table>

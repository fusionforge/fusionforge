<?php

$title = sprintf(_('Add/Update template for %s'), $ath->getName()) ;

$ath->adminHeader(array('title'=>$title));

echo '<h1>'.$title.'</h1>';
?>
<table>
<tr>
<td>
<fieldset>
<legend><?php echo _('Select Template'); ?></legend>
<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post" enctype="multipart/form-data">
<p>
    <input type="hidden" name="uploadtemplate" value="1" />
    <input type="file" name="input_file" size="30" />
    <input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
</p>
</form>
</fieldset>
</td>
</tr>
</table>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

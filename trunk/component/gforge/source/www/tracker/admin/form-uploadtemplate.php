<?php

$title = sprintf(_('Add/Update template for %s'), $ath->getName()) ;

$ath->adminHeader(array('title'=>$title));

echo '<h3>'.$title.'</h3>';
?>
<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="uploadtemplate" value="1">

<input type="file" name="input_file" size="30" /></p>

<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
</form></p>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

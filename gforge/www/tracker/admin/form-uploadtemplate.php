<?php

$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_build_boxes','box_update_title',$ath->getName())));

echo '<h3>'.$Language->getText('tracker_admin_build_boxes','upload_template').'</h3>';
?>
<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="uploadtemplate" value="1">

<input type="file" name="input_file" size="30" /></p>

<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
</form></p>
<?php

$ath->footer(array());

?>

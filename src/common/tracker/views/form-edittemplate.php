<?php

$title = sprintf(_('Edit Layout Template for %s'), $ath->getName()) ;
$ath->adminHeader(array('title'=>$title, 'modal'=>1));

$params = array() ;
$params['body'] = isset($body)? $body : '<table>'.$ath->getRenderHTML(array(),'DETAIL').'</table>';
$params['height'] = "500";
$params['group'] = $group_id;
$params['content'] = '<textarea name="body"  rows="30" cols="80">' . $params['body'] . '</textarea>';
plugin_hook_by_reference("text_editor",$params);

?>
<h2>Important</h2>
<ul>
    <li>Keep the one table format with two columns table layout, do not add strings before or after the table.</li>
	<li>All template variables (named like {$...}) should be left untouched.</li>
	<li>Once a template model in use, if you add/remove custom fields, you'll have to update the template yourself.</li>
</ul>

<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
<input type="hidden" name="update_template" value="y" />
<p><?php echo $params['content']; ?></p>
<p><input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
</form>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php
if($from == 'admin'){
	$urlPrefix = 'admin/';
	$groupIdAdding = '&group_id='.$group_id;
	$viewUrl = 'optionprivateinstance';
}else{
	$urlPrefix = 'siteAdmin/';
	$groupIdAdding = '';
	$viewUrl = 'optioninstance';
}

$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view='.$viewUrl.'&instanceid='.$instanceid.$groupIdAdding;
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<?php 
if($view=='addschedule'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_schedule"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_schedule"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="view" value="<?php echo $viewUrl;?>">
	<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<?php
		if(isset($scheduleToEdit)){?>
	<input type="hidden" name="scheduleid" value="<?php echo $scheduleToEdit->id;?>">
	<?php
		}
	?>
	<?php
		if($from == 'admin'){?>
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">	
	<?php
		}
	?>
	
	<table>
		<tr valign="top">
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_name");?></td>
			<td><input size="40" maxlength="128" type="text" name="name" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->name.'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td colspan="2" align="right" style="border-bottom : thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_description");?></td>
			<td><input size="40" maxlength="128" type="text" name="description" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->description.'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td rowspan="8"><u><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression");?></u>
				<br /><a href="http://www.opensymphony.com/quartz/api/org/quartz/CronTrigger.html"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_syntaxe");?></a>
			</td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_seconde");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionSeconde" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getSeconde().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_minute");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionMinute" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getMinute().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_hour");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionHour" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getHour().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_day_of_month");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionDayOfMonth" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getDayOfMonth().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_month");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionMonth" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getMonth().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_day_of_week");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionDayOfWeek" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getDayOfWeek().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_cronExpression_year");?></td>
			<td><input size="10" maxlength="10" type="text" name="cronExpressionYear" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->getYear().'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td colspan="2" align="right" style="border-top : thin solid black;"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_max_time");?></td>
			<td><input size="10" maxlength="20" type="text" name="maxJobExecutionTime" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->maxJobExecutionTime.'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_wait_time");?></td>
			<td><input size="10" maxlength="20" type="text" name="delay" <?php if(isset($scheduleToEdit)){echo 'value="'.$scheduleToEdit->delay.'"';}?>/></td>
		</tr>
		<tr valign="top">
			<td colspan="2"></td>
			<td><input type="checkbox" name="active" <?php if(isset($scheduleToEdit)&& $scheduleToEdit->active==true){echo 'checked="true"';}?>/><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_schedule_activated");?></td>
		</tr>
		<tr valign="top">
			<td colspan="2"></td>
			<td><input type="submit" name="addInstance" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addschedule'?"submit_add_schedule":"submit_edit_schedule")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
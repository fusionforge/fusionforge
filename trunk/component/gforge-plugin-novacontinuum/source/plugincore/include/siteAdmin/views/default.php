<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "global_configuration")); ?>
<?php
$configuration = $serviceManager->getConfiguration();
?>
<table>
	<tr>
		<td><?php echo dgettext ("gforge-plugin-novacontinuum", "form_configuration_allow_private_instance");?></td>
		<td>
			<?php 
			if($configuration->values['allowPrivateInstance'] == '1'){ ?>
				<a href="/plugins/novacontinuum/siteAdmin/index.php?action=disallowPrivateInstance" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "disallow_private_instance_tooltip")?>"><img src="imgs/lock_open.png" alt="Disable" border='none'/></a>
			<?php } else {?>
				<a href="/plugins/novacontinuum/siteAdmin/index.php?action=allowPrivateInstance" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "allow_private_instance_tooltip")?>"><img src="imgs/lock.png" alt="Enable" border='none'/></a>
			<?php }?>
		</td>
	</tr>	
	
</table>
<?php echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "instances_list")); ?>

<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_instance');
$menu_links [] = '/plugins/novacontinuum/siteAdmin/index.php?view=addinstance';
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<table width="97%" align="center" border="1" cellspacing="0" cellpadding="7" style="border-collapse: collapse">
   <tr>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_name')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_url')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_user')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_proxy')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_working')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_nb_group')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'instance_col_actions')?>
    </th>
   </tr>
  	<?php
		$instances = $serviceManager->getAllContinuumInstances();

		foreach ($instances as $instance) {
		?>
		<tr>
			<td><?php if($instance->groupId!=-1){echo '[';} echo $instance->name; if($instance->groupId!=-1){echo ']'; }?></td>
			<td><?php echo $instance->url;?></td>
			<td><?php echo $instance->user;?></td>
			<td><?php echo (isset($instance->httpProxy)?$instance->httpProxy->name:'');?></td>
			<td width="50px" align="center">
				<?php
				$pingRet = $instance->ping(); 
				if($pingRet===true){ ?>
					<img src="imgs/icon_success_sml.gif" alt="status" border='none'/>
				<?php }else{?>
					<a href="javascript:alert('<?php echo $pingRet;?>');"><img src="imgs/icon_error_sml.gif" alt="status" border='none'/></a>
				<?php } ?>
			</td>
			<td width="50px" align="center">
			<?php
			
			if($instance->groupId==-1){
				$projects = $serviceManager->getProjectsForInstance($instance->id);
				$strProject ="";
				foreach ($projects as $key=>$value) {
	   			$strProject.=$value.'\n';
	   		}
	   		$nbProject = count($projects);
	   		$diff = $instance->maxUse - $nbProject;
	   		$point = max(1,$instance->maxUse*0.2);
   		
			?>
			<a href="javascript:alert('<?php echo $strProject;?>');"><?php echo $nbProject.($instance->maxUse>'0'?"/".$instance->maxUse:'');?></a><?php if($instance->maxUse>'0'&&$point>=$diff){?> <img src="imgs/icon_warning_sml.gif" alt="Warning" border='none'/><?php }}else{?><img src="imgs/rosette.png" alt="label" border='none'/><?php }?></td>
			<td width="80px" align="center">
			<?php
				$pingRet = $instance->ping(); 
				if($pingRet===true){ ?>
					<a href="/plugins/novacontinuum/siteAdmin/index.php?view=optioninstance&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "option_instance_tooltip")?>"><img src="imgs/buildhistory.gif" alt="Option" border='none'/></a>	
			<?php }?>	
			<a href="/plugins/novacontinuum/siteAdmin/index.php?view=editinstance&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_instance_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
			<a href="/plugins/novacontinuum/siteAdmin/index.php?view=deleteinstance&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_instance_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
			<?php 
			if($instance->isEnabled == 1){ ?>
				<a href="/plugins/novacontinuum/siteAdmin/index.php?action=disableinstance&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "disable_instance_tooltip")?>"><img src="imgs/lock_open.png" alt="Disable" border='none'/></a>
			<?php } else {?>
				<a href="/plugins/novacontinuum/siteAdmin/index.php?action=enableinstance&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "enable_instance_tooltip")?>"><img src="imgs/lock.png" alt="Enable" border='none'/></a>
			<?php }?>
			
			</td>
		<?php
		}
		?>
  </table>
<?php echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "proxy_list")); ?>
<?php
$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'add_proxy');
$menu_links [] = '/plugins/novacontinuum/siteAdmin/index.php?view=addproxy';
	
echo $HTML->subMenu ($menu_text, $menu_links);
?>

<table width="97%" align="center" border="1" cellspacing="0" cellpadding="7" style="border-collapse: collapse">
   <tr>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'proxy_col_name')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'proxy_col_host')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'proxy_col_port')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'proxy_col_user')?>
    </th>
    <th valign="top">
    <?php echo dgettext ('gforge-plugin-novacontinuum', 'proxy_col_actions')?>
    </th>
   </tr>
  	<?php
		$instances = $serviceManager->getAllHttpProxies();


		foreach ($instances as $instance) {
		?>
		<tr>
			<td><?php echo $instance->name;?></td>
			<td><?php echo $instance->host;?></td>
			<td width="40px" align="center"><?php echo $instance->port;?></td>
			<td><?php echo $instance->userName;?></td>
			<td width="60px" align="center">
			<a href="/plugins/novacontinuum/siteAdmin/index.php?view=editproxy&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "edit_proxy_tooltip")?>"><img src="imgs/edit.gif" alt="Edit" border='none'/></a>
			<a href="/plugins/novacontinuum/siteAdmin/index.php?view=deleteproxy&instanceid=<?php echo $instance->id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_proxy_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
			</td>
		<?php
		}
		?>
  </table>
<?php
echo $HTML->boxBottom ();
?>
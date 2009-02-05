<?php
if($from == 'admin'){
	$urlPrefix = 'admin/';
}else{
	$urlPrefix = '';
}
if($serviceManager->hasRoleForGroup($group_id,'read_maven_site')){
	echo $HTML->boxMiddle (dgettext ("gforge-plugin-novacontinuum", "consult_maven_site"));

	$projectRoot = $serviceManager->getContinuumDataDir().$group->getUnixName();
	$availableHisto = $serviceManager->getAvailableHisto($projectRoot);
	if(sizeof($availableHisto)==0){
		echo "---";
	}
	?>
	<ul>
	<?php
	foreach ($availableHisto as $value) {
		$siteDate = $serviceManager->formatSiteDate($value);
 		?>
 			
 			<li>
 				<?php
					if($serviceManager->hasRoleForGroup($group_id,'write_maven_site')){
					?>
						<a href="/plugins/novacontinuum/<?php echo $urlPrefix;?>index.php?view=deletesite&siteid=<?php echo $value;?>&group_id=<?php echo $group_id;?>" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "delete_maven_site_tooltip")?>"><img src="imgs/delete.gif" alt="Delete" border='none'/></a>
					<?php
					} 
				?>
			 <?php
			if(file_exists('/var/lib/gforge/novacontinuum/'.$group->getUnixName().'/'.$value.'/index.html')){
			?>
			 <a href="/plugins/novacontinuum/view.php/<?php echo $group->getUnixName().'/'.$value.'/index.html';?>" target="_blank" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "consult_maven_site_tooltip")?>"><?php echo $siteDate;?></a>
			<?php
			} else {
				$found = false;
				$foundFile = "";
				$dir = '/var/lib/gforge/novacontinuum/'.$group->getUnixName().'/'.$value.'/';

				if ($handle = opendir($dir)) {
      					while (false !== ($file = readdir($handle))) {
					      if(is_dir($dir.$file) && $file!='.' && $file!='..'){
							if(file_exists($dir.$file.'/index.html')){
								$found = true;
								$foundFile = $file;
							} 
						}
	
					}
					closedir($handle);
				}
				if($found) {
				?>
					 <a href="/plugins/novacontinuum/view.php/<?php echo $group->getUnixName().'/'.$value.'/'.$foundFile.'/index.html';?>" target="_blank" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "consult_maven_site_tooltip")?>"><?php echo $siteDate;?></a>
				<?php
				}else{
					echo "Not a valid site '".$value."'";
				}
			}
			?></li>

 		<?php
 	}
 	?>
 	</ul>
 	<?php
}
?>
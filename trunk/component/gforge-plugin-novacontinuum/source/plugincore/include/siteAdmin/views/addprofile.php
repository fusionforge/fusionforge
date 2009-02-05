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
if($view=='addprofile'){
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "add_profile"));
}else{
	echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "edit_profile"));
} ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="<?php echo $view;?>">
	<input type="hidden" name="view" value="<?php echo $viewUrl;?>">
	<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<?php
		if(isset($profileToEdit)){?>
	<input type="hidden" name="profileid" value="<?php echo $profileToEdit->id;?>">
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
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_name");?></td>
			<td colspan="3"><input size="40" maxlength="128" type="text" name="name" <?php if(isset($profileToEdit)){echo 'value="'.$profileToEdit->name.'"';}?>/></td>
		</tr>
		<?php
		  $selectedvarenvs = array();
			if((isset($profileToEdit))&&(isset($profileToEdit->environmentVariables))){
				foreach ($profileToEdit->environmentVariables as $key=>$varenv) {
					$selectedvarenvs[$varenv->installationId]=$varenv;
				}
			}
			$jdks = array();
			$builders = array();
			$varenvs = array();
			$instance = $serviceManager->getContinuumInstance($instanceid);
			$installations = $serviceManager->getInstallationsForInstance($instance);
	
			foreach ($installations as $key=>$installation) {
 				if($installation->type == 'jdk'){
				 	$jdks[] = $installation;
				}else if($installation->type == 'maven2'){
				 	$builders[] = $installation;
				}else{
					if(!array_key_exists($installation->installationId,$selectedvarenvs)){
						$varenvs[] = $installation;
					}
				}
 			}
		?>
		<tr>
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_jdk");?></td>
			<td colspan="3">
				<select name="jdk">
					<option value="-1"<?php if((!isset($profileToEdit))||(!isset($profileToEdit->jdk))){echo ' selected="selected" ';}?>><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_jdk_none");?></option>
					<?php
						foreach ($jdks as $key=>$jdk) {
      		?>
					<option value="<?php echo $jdk->installationId;?>"<?php if((isset($profileToEdit))&&(isset($profileToEdit->jdk))&&($profileToEdit->jdk->installationId==$jdk->installationId)){echo ' selected="selected" ';}?>><?php echo $jdk->name;?></option>
					<?php
						}
					?>			
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_builder");?></td>
			<td colspan="3">
				<select name="builder">
					<option value="-1"<?php if((!isset($profileToEdit))||(!isset($profileToEdit->builder))){echo ' selected="selected" ';}?>><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_builder_none");?></option>
					<?php
						foreach ($builders as $key=>$builder) {
      		?>
					<option value="<?php echo $builder->installationId;?>"<?php if((isset($profileToEdit))&&(isset($profileToEdit->builder))&&($profileToEdit->builder->installationId==$builder->installationId)){echo ' selected="selected" ';}?>><?php echo $builder->name;?></option>
					<?php
						}
					?>			
				</select>
			</td>
		</tr>
		<tr>
			<td rowspan="4" colspan="2" align="right"><?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_var_envs");?></td>
			<td rowspan="4">
				<select name="availableenvs" size="5" multiple="true" id="availableenvs">
					<?php
						foreach ($varenvs as $key=>$varenv) {
      		?>
					<option value="<?php echo $varenv->installationId;?>"><?php echo $varenv->name;?></option>
					<?php
						}
					?>			
				</select>
			</td>
			<td>
				<script type="text/javascript">
					<!--
						function remove(){
							var availvarenvs = document.getElementById("availableenvs");
							var varenvs = document.getElementById("varenvs");
							for (var i = varenvs.options.length - 1; i>=0 ; i--){
								if (varenvs.options[ i ].selected){
									var selected = varenvs.options[ i ];
									varenvs.options[i] = null;
									availvarenvs.options[availvarenvs.length] = selected;
								}
							}
						}
						
						function add(){
							var varenvs = document.getElementById("varenvs");
							var availvarenvs = document.getElementById("availableenvs");
							for (var i = availvarenvs.options.length - 1; i>=0 ; i--){
								if (availvarenvs.options[ i ].selected){
									var selected = availvarenvs.options[ i ];
									availvarenvs.options[i] = null;
									varenvs.options[varenvs.length] = selected;
								}
							}
						}
						
						function selectAll(){
							var varenvs = document.getElementById("varenvs");
							for (var i = varenvs.options.length - 1; i>=0 ; i--){
								varenvs.options[ i ].selected = true;
							}
						}
					-->
				</script>
			</td>
			<td rowspan="4">
				<select name="varenvs[]" size="5" multiple="true" id="varenvs">
					<?php
						if((isset($selectedvarenvs))){
							foreach ($selectedvarenvs as $key=>$varenv) {
      		?>
					<option value="<?php echo $varenv->installationId;?>"><?php echo $varenv->name;?></option>
					<?php
							}
						}
					?>			
				</select>
			</td>
		</tr>
		<tr>
			<td><button type="button" style="background:white; cursor:hand; border:solid 1px black;"
        		onclick="JavaScript: add()" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_add_env_tooltip")?>">
				  <img src="imgs/control_fastforward.png">
				</button>
			</td>
		</tr>
		<tr>
			<td><button type="button" style="background:white; cursor:hand; border:solid 1px black;"
        		onclick="JavaScript: remove()" title="<?php echo dgettext ("gforge-plugin-novacontinuum", "form_add_profile_remove_env_tooltip")?>">
				  <img src="imgs/control_rewind.png">
				</button>
			</td>
		</tr>
		<tr>
			<td colspan="5">
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2"></td>
			<td colspan="3"><input type="submit" onclick="selectAll();" name="addProfile" value="<? echo dgettext ("gforge-plugin-novacontinuum", ($view=='addprofile'?"submit_add_profile":"submit_edit_profile")); ?>" /></td>
		</tr>
	</table>
		
</form>
<?php
echo $HTML->boxBottom ();
?>
<?php
//HOWTO:
/*
php import_mailman.php OLD_PROJECT_NAME NEW_PROJECT_NAME OLD_DOMAIN_NAME NEW_DOMAIN_NAME
	OLD_PROJECT_NAME : Name of the project when it was exported (should be the name of the archive)
	NEW_PROJECT_NAME : Name of the project where the lists should be imported
	OLD_DOMAIN_NAME : Old mailman server address in the original forge, it can be found by reading an archive file. Cannot be automatically retrieved for now, if nothing provided, no change will be done
	NEW_DOMAIN_NAME : New mailman server address on the target forge, this param should never be used if the preceding one was not used
*/
	
class Mailman {

	function __construct($mailingDir, $oldlistname, $oldprojectname, $gid, $olddomain="", $newdomain=""){
		$this->dir = $mailingDir; //Directory where the mailing list was extracted, probably /tmp/[old_project_name]/mailings/[old_list_name_full]
		$this->oldlistname = $oldlistname; //full name of the list (ie, name of the last directory of this->dir), for instance [old_project_name]-[list_name]
		$this->listname = substr($oldlistname, strlen($oldprojectname)+1); //name of the list without "[old_project_name]-"
		$this->newpjct = $gid;//group_get_object($gid)->getUnixName(); // UnixName of the project where the data will be imported
		$this->olddomain = $olddomain; // Old domain (lists.yyy.yyy.fr ...)
		$this->newdomain = $newdomain; // New domain (lists.xxx.xxx.fr ...)
	}
	//USAGE - rename, copy, update
	function rename(){
		//$this->newpjct = $newpjct;
		shell_exec( " mv ".$this->dir."/archives/private/".$this->oldlistname." ".$this->dir."/archives/private/".$this->newpjct."-".$this->listname);
		shell_exec( " mv ".$this->dir."/archives/private/".$this->oldlistname.".mbox ".$this->dir."/archives/private/".$this->newpjct."-".$this->listname.".mbox");
		shell_exec( " mv ".$this->dir."/lists/".$this->oldlistname." ".$this->dir."/lists/".$this->newpjct."-".$this->listname);
		$this->newlistname = $this->newpjct."-".$this->listname;
	}
	
	
	function copy(){
		echo "Copying archives : ".$this->dir."/archives/* ---> /var/lib/mailman/archives/\n\n";
		shell_exec( " cp -r -a ".$this->dir."/archives/* /var/lib/mailman/archives/ 2>&1 " );
		echo "Copying lists : ".$this->dir."/lists/* ---> /var/lib/mailman/lists\n\n";
		shell_exec( " cp -r -a ".$this->dir."/lists/* /var/lib/mailman/lists 2>&1 " );#possible to replace with PHP code like copy(), useful?
	}
	
	function search_replace($startdir, $original, $new){
		if (count($original) != count($new)){
			return false;
		}
		$fh = opendir($startdir);
		while (($file = readdir($fh)) !== false){
			$filepath = $startdir.'/'.$file;
			$fileinfo = pathinfo($file);
			$fileext = $fileinfo['extension'];
			if($fileext == 'html'){
				for($i =0; $i < count($original); $i++){
					$content = str_replace(	$original[$i], $new[$i] , file_get_contents($filepath) );
					file_put_contents($filepath, $content);
				}
			}
		}
		return true;
	}
	
	function update(){
		isset($this->newlistname)? $name=$this->newlistname : $name=$this->oldlistname;
		shell_exec( " /var/lib/mailman/bin/check_perms -f 1>/dev/null 2>/dev/null" );
		shell_exec( " /var/lib/mailman/bin/check_perms -f 1>/dev/null 2>/dev/null" );
		echo "permission update done\n\n";	
	//Updates list domain in config
		shell_exec( " /var/lib/mailman/bin/withlist -l -r fix_url ".$name." -v -u ".$this->newdomain."/".$name." 1>/dev/null 2>/dev/null");
		echo "url fixed to ".$this->newdomain."\n\n";
		if(isset($this->newlistname)){
			$filepath = "/tmp/".$name;
			shell_exec( " /var/lib/mailman/bin/config_list -o ".$filepath." ".$name." 1>/dev/null 2>/dev/null");
			echo "config output to ".$filepath."\n\n";
			
			//updates list name in config
			$pattern = "/real_name = '[^']*'/";
			$replacement = "real_name = '".$name."'";
			$content = preg_replace($pattern, $replacement, file_get_contents($filepath));
			file_put_contents($filepath, $content);
	
			echo "updated real name\n\n";
			shell_exec( " /var/lib/mailman/bin/config_list -i ".$filepath." ".$name);
			echo "new configuration loaded\n\n";
		}
			
		echo "Replacing old name strings and list domain in archives : \n";
		$original = array();
		$new = array();
		if(isset($this->newlistname)){
			echo $this->oldlistname." --> ".$name."\n";
			$original[] = $this->oldlistname;
			$new[] = $name;
			echo ucfirst($this->oldlistname)." --> ".ucfirst($name)."\n";
			$original[] = ucfirst($this->oldlistname);
			$new[] = ucfirst($name);
		}
		if($this->olddomain != ""){
			echo $this->olddomain." --> ".$this->newdomain."\n\n";
			$original[] = $this->olddomain;
			$new[] = $this->newdomain;
		}
		$this->search_replace("/var/lib/mailman/archives/private/".$name, $original, $new);
	}
		
}




	$oldpjctname = $argv[1];
	$newpjctname = $argv[2];
	$olddomainname = $argv[3];
	$newdomainname = $argv[4];
	
	
	/*
	Optional necessitates PHP>5.3.0 not supported by lenny, use $argv in the meantime
	$options = "o:n:d::e::";
	$chosen = getopt($options);
	$oldpjctname = $chosen["o"];
	$newpjctname = $chosen["n"];
	$olddomainname = $chosen["d"];
	$newdomainname = $chosen["e"];
	*/
	
	if($dirs = scandir($mailingspath = '/tmp/'.$oldpjctname.'/mailings')){
		echo "Mailing lists found\n\n";
		foreach ($dirs as $dir){
			if($dir != '.' && $dir != '..'){	
				if (isset($olddomainname) && isset($newdomainname)){
					$mailing = new Mailman($mailingspath.'/'.$dir, $dir, $oldpjctname, $newpjctname, $olddomainname, $newdomainname);
				} else {
					$mailing = new Mailman($mailingspath.'/'.$dir, $dir, $oldpjctname, $newpjctname);
				}
				$mailing->rename();
				$mailing->copy();
				$mailing->update();
			}
		}
	}

?>

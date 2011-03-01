<?php
require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'frs/FRSFile.class.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';

class FRS {
	
	var $frs;
	
	var $group;
	
	var $group_id;
	
	public $pk_vars = array('Active' => 1, 'Hidden' =>3);
	
	public $rel_vars = array('Active' => 1, 'Hidden' =>3);
	
	
    function __construct($frs, $group_id) {
        $this->frs = $frs;
        $this->group_id = $group_id;
    	$group =& group_get_object($group_id);
		if (!$group || !is_object($group)) {
			print "error retrieving group from id";
		} else if ($group->isError()) {
			print "error";
		}
		$this->group = $group;
    }
	
    
	function frs_fill(){
$pkgs = &get_frs_packages($this->group);
foreach($pkgs as $pkg){
$pkg->delete(true,true);
}

//		For each package
		foreach($this->frs as $pk_name => $pk_content){
//	new dBug(array($pk_name,$pk_content));
//			Create package
			$pkg = new FRSPackage($this->group);
//new dBug(textareaSpecialchars($pk_name));
			$pkg->create($pk_name);
			$pkg->update($pk_name, $this->pk_vars[$pk_content['status']]);
$pkg->clearError();
//new dBug($pkg);
			foreach($pk_content['releases'] as $rel_name => $rel_content){
//	new dBug(array($rel_name,$rel_content));
//				Create release
				$rel = new FRSRelease($pkg);
				$rel->create($rel_name, $rel_content['release_notes'], $rel_content['change_log'], 0, strtotime($rel_content['date']));
				$rel->update($this->rel_vars[$rel_content['status']], $rel_name, $rel_content['release_notes'], $rel_content['change_log'], 0, strtotime($rel_content['date']));
				foreach($rel_content['files'] as $fname => $fcontent){
//					Create File
					$file = new FRSFile($rel);
					$res = db_query("SELECT processor_id
						FROM frs_processor
						WHERE name='".$fcontent['processor']."'");
					//TODO:Cleanup, there must be a function to catch only one result
					while ($row=db_fetch_array($res)){
						$processor_id=$row['processor_id'];
					}

					$res = db_query("SELECT type_id
						FROM frs_filetype
						WHERE name='".$fcontent['type']."'");
					//TODO:Cleanup, there must be a function to catch only one result
					while ($row=db_fetch_array($res)){
						$type_id=$row['type_id'];
					}
					$file->create($fname, '/tmp/'.$fcontent['url'], $type_id, $processor_id, strtotime($fcontent['date']));
//new dBug($file);
				}
			}
		}
	}
	
}

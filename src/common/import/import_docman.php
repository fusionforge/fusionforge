<?php

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'docman/Document.class.php';

class Docman {

	public $doc_group_ids = array();

	var $group;

	var $group_id;

	public $language_ids = array("Bulgarian" => 20, "Catalan" => 14, "Dutch" => 12, "English" => 1, "Esperanto" =>13, "French" =>7, "Greek" => 19, "German" =>6, "Hebrew" =>3,
					"Indonesian" =>21, "Italian" => 8, "Japanese" =>2, "Korean" => 22, "Latin" => 25, "Norwegian" =>9, "Polish" =>15, "Portuguese" =>18, "Pt. Brazilian" =>16, "Russian" => 17,
					"Smpl.Chinese" =>23, "Spanish" =>4, "Swedish" =>10, "Thai" =>5, "Trad.Chinese" =>11);

	public $docman_states = array("active"=>1, "deleted"=>2, "pending"=>3, "hidden"=>4, "private"=>5);


    function __construct($docman, $group_id) {
        $this->docman = $docman;
        $this->group_id = $group_id;
    	$group =& group_get_object($group_id);
		if (!$group || !is_object($group)) {
			print "error retrieving group from id";
		} else if ($group->isError()) {
			print "error";
		}
		$this->group = $group;
    }

    function checkDocGrpExistence($doc_group_name,$parent_doc_group_id){
    	if ($id = $this->doc_group_ids[$parent_doc_group_id][$doc_group_name]){
    		//this name already exists in the directory of id $parent_doc_group_id
    		return $id;
    	}
    	return false;
    }

    function addFile($params, $parent_dir_id, $status="active"){
    	//nothing for now
    	echo "Adding file:".$params["given_name"]." at directory:".$parent_dir_id;
    	echo "<br />";
    	$path = '/tmp/'.$params['url'];
		if (is_file($path)){
    		$doc = new Document($this->group);

    		$fn = $params["file_name"];
    		$ftitle = $params["given_name"];
			$fdata = file_get_contents($path);
			$fdocgrp = $parent_dir_id;
			$flanguage = $this->language_ids[$params["language"]];
			$fdesc = $params["description"];
			$finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic"); // Retourne le type mime
			if (!$finfo) {
	   			echo "error opening fileinfo";
	    		exit();
			}
			$ftype = $finfo->file($path);

			$doc->create($fn,$ftype,$fdata,$fdocgrp,$ftitle,$flanguage,$fdesc);

			//now update state
			$fstate_id = $this->docman_states[$status];
			$doc->update($fn, $ftype, $fdata, $fdocgrp, $ftitle, $flanguage, $fdesc,$fstate_id);
		}
    }



    function addDirectory($dirName, $parent_dir_id){
    	if(!$parent_dir_id){
    		//root of the current type
    		echo "ROOT:".$dirName;
    		echo "<br />";
    		//is dir already existing?
    		if(!$dirid = $this->checkDocGrpExistence($dirName, $parent_dir_id)){
    			//dir does not exist : create
    			$doc_group = new DocumentGroup($this->group);
    			$doc_group->create($dirName);
    			$dirid = $doc_group->getID();
    			$this->doc_group_ids[$parent_dir_id][$dirName] = $dirid;
    		} else {
    			//dir exists : return its id
    		}

    	} else {
    		echo $parent_dir.":".$dirName;
    		echo "<br />";
    		//is dir already existing?
    		if(!$dirid = $this->checkDocGrpExistence($dirName, $parent_dir_id)){
    			//dir does not exist : create
    			$doc_group = new DocumentGroup($this->group);
    			$doc_group->create($dirName, $parent_dir_id);
    			$dirid = $doc_group->getID();
    			$this->doc_group_ids[$parent_dir_id][$dirName] = $dirid;
    		} else {
				//dir exists : return its id
    		}
    	}
    	return $dirid;
    }

    function getUncat(){
    	$gr = new DocumentGroupFactory($this->group);
		$dgroups = $gr->getDocumentGroups();
		foreach($dgroups as $dg){
			if($dg->getParentID()==0 && $dg->getName()=='Uncategorized Submissions'){
				return $dg->getID();
			}
		}
		return false;
    }

    function fill_type($content, $status = "", $parent_dir_id = ""){
//    	while (len($content) != 0){
//    		$c = array_pop($content);
		foreach($content as $k => $v){
    		if(array_key_exists("class", $v) && $v["class"] == "FILE"){
    			//$k is a file
    			$this->addFile($v, $parent_dir_id, $status);
    		} else {
    			//$k is a directory
    			if($k!='Uncategorized Submissions'){ //Uncategorized subs is a basic category which should not be duplicated, we need to get its id to add docs to it though
    				$dirid = $this->addDirectory($k, $parent_dir_id);
    			} else {
    				//get Uncategorized Submissions doc_group
    				$dirid = $this->getUncat();
    				if(!$dirid){
    					//error : no Uncategorized subs for this project for unknown reason
    					//create it
    					$dirid = $this->addDirectory($k, $parent_dir_id);
    				}
    			}

    			$this->fill_type($v,$status,$dirid);
    		}
    	}
    }

	function docman_fill(){
		$r1 = db_query_params ('DELETE FROM doc_data WHERE group_id=$1',
					   array ($this->group_id)) ;

		$r2 = db_query_params ('DELETE FROM doc_groups WHERE group_id=$1',
					   array ($this->group_id)) ;


		foreach($this->docman as $status => $content){
			$this->fill_type($content, $status);
		}
	}

}

?>

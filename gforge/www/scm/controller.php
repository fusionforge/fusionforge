<?php
/**
 *
 * CVS repository viewer / parser
 *
 * Controller.php (probably will change names later) creates the view
 * and parses the given requested revision, ultimately will view all repository data
 *
 * @author	Ronald Petty
 *
 */
	require_once('www/include/pre.php');    // Initial db and session library, opens session

	//preferences
	$require_login 		= false;		//true if you want users to have to login
	$cvsroot_stem 		= "/cvsroot/";		//must have trailing slash
	$filecolor 		= "#bababa";		//bgcolor of file directories
	$directorycolor 	= "#ababab";		//bgcolor of directories directories

	//this sees if people have to login or not
	if($require_login && !session_loggedin()) {
		exit_not_logged_in();
	}

	$project =& group_get_object($group_id);

	//$user =& session_get_user();
	//$user_login = $user->getUnixName();
	//$user_pw = $user->getUnixPasswd();

	//make sure we are on a project page
	if(!$project) {
		//fix the getText to be something for cvs
		exit_error($Language->getText('scm_index','error_only_projects_can_use_cvs'), "go to project cvs link");
	}
	
	if (!$project->isProject()) {
		exit_error($Language->getText('scm_index','error_only_projects_can_use_cvs'), "not a project");
	}

	//not 100% sure if this works, haven't tried it out yet
	if (!$project->usesCVS()) {
		exit_error($language->getText('scm_index','error_this_project_has_turned_off'), "project does not use cvs");
	}

	//this gets the correct repository assuming the following
	//the root directory to all repositories is $cvsroot_stem and then you just add the group name
	//until users complain then we will use this
	$group = $project->getUnixName();
	$cvsroot = $cvsroot_stem.$group;

	//this allows gforge theming, in the depths of Layout.class
	site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$group_id,'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($project->getPublicName())));

	//make sure that they are in the directory they should be in, this needs to be better checking,
	//just basic to get started, assumes the path to a file/dir has the group name
	//so this fails if someone is cheating using a partial group name
	if(!strstr($fil,$group) && !strstr($dir,$group)) {
		$fil = null;	//forces back to top directory of project repository
		$dir = null;
	}

	//if the user clicked on a file link this will get the correct revision and display it
	if($fil && $rev) {
		$filename = tail($fil);
		$path = path($fil); 
		print splithref($path);
		print "<TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0 WIDTH=\"100%\"><TR>";
		print "<TD><A HREF=$PHP_SELF?group_id=$group_id&dir=$path>BACK</A></TD>";
		print "<TD ALIGN=CENTER WIDTH=\"100%\">$filename</TD>";
		print "<TD>&nbsp;</TD></TR><TR><TD COLSPAN=\"3\"><HR></TD></TR></TABLE>";
		$p = new parser($fil);
		print $p->getRev($rev);
		site_project_footer(array());
		exit;
	}

	//this checks if the user is coming to the page the first time or not, basically if you haven't 
	//clicked on a link it will set it too cvsroot for the default (which is cvsroot_stem/project
	if(!$dir) {
		$dir = $cvsroot;
		$files = retrieveDir($dir);
		echo $HTML->boxTop($Language->getText('cvs','search')." for $group -- " . splithref($dir));	
		listing($files);
		echo $HTML->boxBottom();
	}
	else {
		$files = retrieveDir($dir);
		echo $HTML->boxTop($Language->getText('cvs','search')." for $group -- " . splithref($dir));
		listing($files);
		echo $HTML->boxBottom();
	}

	//this is the end of the display
	site_project_footer(array());
	exit;	//not sure if you need an exit here, but it works 

	//this function lists the directory/files and color coats them, I have a feeling
	//one day people will want to change this around
	function listing($list) {
		global $group_id;
		global $filecolor;
		global $directorycolor;	

		for($i = 0; $i < count($list); $i++) {
			if(is_dir($list[$i])) {
				if(tail($list[$i]) == ".") {
					print "<TR><TD BGCOLOR=$directorycolor><A HREF=$PHP_SELF?group_id=$group_id&dir=".path($list[$i]).">$list[$i]</A></TD></TR>";
				}
				else if(tail($list[$i]) == ".." && ereg("^$cvsroot",$list[$i])) {
					print "<TR><TD BGCOLOR=$directorycolor><A HREF=$PHP_SELF?group_id=$group_id&dir=".path(path($list[$i])).">$list[$i]</A></TD></TR>";
				}
				else {
					print "<TR><TD BGCOLOR=$directorycolor><A HREF=$PHP_SELF?group_id=$group_id&dir=$list[$i]>$list[$i]</A></TD></TR>";
				}
			}
			//assume it is a file not a dir
			else {
				$p = new parser($list[$i]);
				$t = $p->getPath("-1");
		
				for($k = 0; $k < count($t); $k++) {
					$t[$k] = "<a href=$PHP_SELF?group_id=$group_id&fil=$list[$i]&rev=$t[$k]>$t[$k]</a>&nbsp;&nbsp;";
				}

				$t = implode(" ",$t);

				//this is where you call the parser or not , if file ends in ,v parse it
				print "<TR><TD BGCOLOR=$filecolor><A HREF=$PHP_SELF?group_id=$group_id&fil=$list[$i]&rev=-1>$list[$i]</A>&nbsp;&nbsp;$t</TD></TR>";
			}
		}
	}

	//this function goes from a root directory and then finds all the dir/files and creates paths
	function retrieveDir($rootdirpath) {
		if($dir = @opendir($rootdirpath)) {
			$array[] = $rootdirpath;	
			
			while(($file = readdir($dir)) !== false) {
				$array[] = $rootdirpath."/".$file;
			}
		}

		closedir($dir);

		return $array;
	}

	//return what is following the last / in a path
	function tail($path) {
		$path = explode("/",$path);
		return array_pop($path);
	}

	//return everything in front of the last / the stem of a file path
	function head($path) {
		$path = explode("/",$path);
		array_pop($path);
		$path = implode("/",$path);
		return $path;
	}

	//return everything in front of the last / same as head , don't know which name to use yet for the function
	function path($path) {
		$path = explode("/",$path);
		array_pop($path);
		$path = implode("/",$path);
		return $path;
	}

	//I have not verified if this function works on cvsroot_stem that contains multible directories
	//right now it is checking to see if the first section $i = 0 of a path is contained in the cvsroot_stem
	//if so it doesn't link to it, need more thought, later
	function splithref($path) {
		global $group_id;
		global $cvsroot_stem;
		$path = explode("/",$path);
		array_shift($path); //because explode adds an empty element in the front because a path starts with /	
		$href = null;
		$previous = "/";
		$first = 0;

		for($i = 0; $i < count($path); $i++) {
			if(strlen($previous)>1) {
				$previous = $previous . "/" . $path[$i];
			}
			else {
				$previous = "/".$path[$i];
			}

			if(strpos($cvsroot_stem,$path[$i]) && $i == 0) {
				$href = $href . "$path[$i] / ";
			}
			else	{
				$href = $href . "<A HREF=$PHP_SELF?group_id=$group_id&dir=$previous>$path[$i]</A> / ";
			}
		}

		return $href;
	}

	//function is good for large flat listing, need smaller one
	function retrieveDirs($rootdirpath) {
		if ($dir = @opendir($rootdirpath)) {
			$array[] = $rootdirpath;
			
			while (($file = readdir($dir)) !== false) {
				if (is_dir($rootdirpath."/".$file) && $file != "." && $file != "..") {
					$array = array_merge($array, retrieveDirs($rootdirpath."/".$file));
				}
			}
			
			closedir($dir);
		}

		return $array;
	}

	//function gets the files paths for a given list of directories
	function retrieveFiles($directories) {
		$array = array();

		foreach($directories as $directory) {
			if($dir = @opendir($directory)) {
				while(($file = readdir($dir)) !== false) {
					$array[] = "$directory/$file";
				}
			}
		}

		return $array;
	}

	//this replaces rlog, I bet most of the slowdown is from here, so any optimization time
	//should be spend here.	
	class parser {
		var $rev; //the revision we are finding and doing.

		var $filecontent;	//read in the file,v
		var $filename;		//file,v

		//these are tokens for an rcs grammar man rcsfile
		var $w;			//whitespace
		var $num;		//num
		var $id;		//id
		var $sym;		//sym
		var $idchar;		//idchar
		var $special;		//special
		var $string;		//string

		//admin section of the rcsfile	
		var $a_head;		
		var $a_branch;
		var $a_access;
		var $a_symbols;
		var $a_locks;
		var $a_comment;
		var $a_expand;
		var $admin;
	
		//delta section of the rcsfile	
		var $delta_num;
		var $delta_date;
		var $delta_author;
		var $delta_state;
		var $delta_branches;
		var $delta_next;
		var $delta;
		var $single_delta;

		//desc section of the rcsfile
		var $desc_desc;
		var $desc;

		//deltatext section of the rcsfile
		var $deltatext_num;
		var $deltatext_log;
		var $deltatext_text;
		var $deltatext;
		var $single_deltatext;

		//this is for doing revisions
		var $path;
		var $delta_array;
		var $deltaText_array;

		//gotta love php, have to init it all in the constructor
		function parser($filename) {
			$this->filecontent = null;
			$this->filename = $filename;

			//set up alot or regex to parse the tokens and grammars	
			$this->w	= "(\t|\n|\v|\r| )*";	// \ b and \ f are messing it up look like literals?
			$this->num	= "([0-9]|\.)+";
			$this->id	= '(([0-9]|\.)+)*[^?,.:;@]([^?,.:;@]|([0-9]|\.)+)*';
			$this->sym	= '([0-9])*[^$,.:;@]([^$,.:;@]|([0-9]))*';
			$this->idchar	= '[^$,.:;@]';
			$this->special	= '$,.:;@';
			$this->string	= '@([^@]|[@]{2})*@';

			//admin
			$this->a_head		= "$this->w"."head$this->w($this->num)*;$this->w";
			$this->a_branch		= "(branch$this->w($this->num)*;$this->w)?$this->w";
			$this->a_access		= "access$this->w($this->id)*;$this->w";
			$this->a_symbols	= "symbols$this->w($this->sym$this->w:$this->w$this->num)*;$this->w";
			$this->a_locks		= "locks$this->w($this->id$this->w:$this->w$this->num)*;$this->w(strict$this->w;)?$this->w"; 
			$this->a_comment	= "(comment$this->w($this->string)?;)?$this->w";
			$this->a_expand		= "(expand$this->w($this->string)?;)?$this->w";
			$this->admin		= "$this->a_head$this->a_branch$this->a_access$this->a_symbols$this->a_locks$this->a_comment$this->a_expand";

			//delta
			$this->delta_num	= "$this->w$this->num$this->w";
			$this->delta_date	= "date$this->w$this->num;$this->w";
			$this->delta_author	= "author$this->w$this->id;$this->w";
			$this->delta_state	= "state$this->w($this->id)?;$this->w";
			$this->delta_branches	= "branches$this->w($this->num|$this->w)*;$this->w";
			$this->delta_next	= "next$this->w($this->num)?;$this->w";
			$this->delta		= "($this->delta_num$this->delta_date$this->delta_author$this->delta_state$this->delta_branches$this->delta_next)*";
			$this->single_delta	= "($this->delta_num$this->delta_date$this->delta_author$this->delta_state$this->delta_branches$this->delta_next)";
			
			//desc
			$this->desc_desc	= "$this->w"."desc$this->w$this->string$this->w";
			$this->desc		= "$this->desc_desc";
			
			//deltatext
			$this->deltatext_num		= "$this->w$this->num$this->w";
			$this->deltatext_log		= "log$this->w$this->string$this->w";
			$this->deltatext_text		= "text$this->w$this->string$this->w";
			$this->deltatext		= "($this->deltatext_num$this->deltatext_log$this->deltatext_text)*";
			$this->single_deltatext		= "($this->deltatext_num$this->deltatext_log$this->deltatext_text)";

			$fd = fopen($this->filename, "r");
			$this->filecontent = fread($fd,filesize($this->filename));
			fclose($fd);

			//validate REALLY slows it down, this makes sure it is an RCS file, however
			//we can trust cvs to do its job so only uncomment this if worried
	//		if($this->validate())
			{
				$this->getAdmin();	//parses the admin section, dont use now
				$this->getDelta();	//this gets the deltas to do revisions
				$this->getDescription();	//this gets the description, dont use now
				$this->getDeltaText();	//this gets the deltatex to do revision
			}
		}

		//make sure it is an rcs file
		function validate()
		{
			$temp = array();

			if(ereg("$this->admin$this->delta$this->desc$this->deltatext",$this->filecontent,$temp)) {
				if(strlen($this->$filecontent) == $temp[0]) {
					return true;
				}
			}

			return false;
		}

		//this gets a specific revision, this is really the rlog replacement
		function getRev($rev) {
			$this->path = $this->getPath($rev);
			$t = null;

			//on each path node do the deltas
			for($i = 0; $i < count($this->path); $i++) {
				//this is the head so just get the text
				if($i == 0) {
					$t = explode("\n",($this->deltaText_array[$this->path[$i]]["text"]));
				}
				else {
					//get the text for this node
					$a = explode("\n",($this->deltaText_array[$this->path[$i]]["text"]));
		
					//this pops the empty value because of the newline at the end of the deltas
					array_pop($a);
		
					//this loop looks for deltas backwards since they are applied that way	
					for($k = count($a)-1; $k > -1; $k--) {
						$temp = array();
						//this got the instructions on what to do
						ereg("(a|d)([0-9])+ ([0-9])+",$a[$k],$temp);
			
						//this makes sure we found something, this maybe wrong though as 0 length
						//strings from ereg may be valid I think, don't know
						if(strlen($temp[0]) > 0) {
							//apply the deltas
							//this means add
							if(ereg("a",$temp[0])) {
								$temp = trim($temp[0]);
								$temp = ereg_replace("(\n|\t| )+"," ",$temp);
								list($move,$howmany) = explode(" ",$temp);
								//remove the "a" in the front
								$move = substr($move,1,strlen($move)-1);
						
								for($l = 0; $l < $howmany; $l++) {
									$this->array_insert($t,$a[$k+1+$l],$move+$l);
								}
							}
						
							//this means delete
							if(ereg("d",$temp[0])) {
								$temp = trim($temp[0]);
								$temp = ereg_replace("(\n|\t| )+"," ",$temp);
								list($move,$howmany) = explode(" ",$temp);
								$move = substr($move,1,strlen($move)-1);

								array_splice($t,$move-1,$howmany);
							}	
						}
					}
				}
			}

			//this should be using htmlspecialchars, Ill fix later, other things to test first
			for($j = 0; $j < count($t); $j++) {
				$t[$j] = ereg_replace("<","&lt;",$t[$j]);
				$t[$j] = ereg_replace(">","&gt;",$t[$j]);
			}

			//need more html formatting junk, I dont know enought about htmlspecialchars to see
			//if it will fix things like this
			$t = implode("<BR>",$t);
			$t = ereg_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$t);

			return $t;
		}

		//this lets getRevision add lines when a0 2 for example, since php doesn't have an array_insert
		function array_insert(&$array, $value, $pos) {
			if (!is_array($array)) {
				return FALSE;
			}

			$last = array_splice($array, $pos);
			array_push($array, $value);
			$array = array_merge($array, $last);
		}

		//this function gets the path to a certain revision (including branches)
		function getPath($rev) {
			if($rev == "-1") {
				$rev = $this->getHead();
			}
			else
			{
				$this->rev = $rev;
			}

			$head = $this->getHead();
			$path = array();
			$node = $head;

			$path[] = $node; //add the head to the front of the path
		
			//start down the path in hopes of finding the light	
			while($next = $this->getNext($node,$this->rev)) {
				$path[] = $next;
				$node = $next;
			}

			return $path;
		}

		//getPath calls this to follow branches/trunk to next revision
		function getNext($node,$rev) {
			//we found it
			if($node != $rev) {
				//start looking for the next node
				for($i = 0; $i < count($this->delta_array); $i++) {
					$t = $this->delta_array[$i];

					//we found the next node, see where it is going
					if($t["num"] == $node) {
						//now try to find some branches, if not use next
						if(count($t) > 5) {
							//extract the array of branches
							$t2 = array_slice($t,5);
				
							for($k = 0; $k < count($t2); $k++) {
								$b = explode(".",$t2[$k]);
								$c = array_pop($b);
								$b = implode(".",$b);
	
							//this may not be right, this says to return the first matched 
							//branch, need to double check if this is correct
								$d = strpos($rev,$b);

								if($d == 0 && $d !== false) {
									return $b . "." . $c;
								}
							}

							//means that no branch matched 
							return $t["next"];
						}
						//no branch to follow goto next (if there is a next)
						else {
							return $t["next"];
						}
					}
				}
			}

			return false;
		}

		//this gets the head revision on the trunk so you can start down the path to the
		//revision you want.
		function getHead() {
			$temp = array();
			$one = null;

			if(ereg($this->admin,$this->filecontent,$temp)) {
				$one = $temp[0];
				$start = strlen($one);
				$length = strlen($this->filecontent);
				$file = substr($this->filecontent,$start,$length-$start);
				$one = trim($one);

				if(ereg($this->a_head,$one,$temp)) {
					$one = substr($one,strlen($temp[0]));
					$temp[0] = ereg_replace("(\t| |\n)+"," ",$temp[0]);
					list($head,$head_num) = explode(" ",$temp[0]);
					$head_num = trim($head_num);
					return substr($head_num,0,strlen($head_num)-1);
				}
			}
		}

		//this parses the rcsfile (file,v) and get the path information ready for getPath	
		//we are only doing path/revision now, so until we see what people want for date/author/sym etc
		function getDelta() {
			$temp = array();	//scratch
			$f = $this->filecontent;
			$this->delta_array = array();

			while(ereg($this->single_delta,$f,$temp)) {
				$t = array();	//build up the delta nodes
				
				$two = $temp[0];
				$start = strlen($two) + strpos($f,$two);
				$length = strlen($f);
				$f = substr($f,$start,$length-$start);
				$two = trim($two);

				ereg($this->delta_num,$two,$temp); //title of the delta node
				$t["num"] = trim($temp[0]);

				ereg($this->delta_date,$two,$temp);	//date
				ereg($this->num,$temp[0],$temp);
				$t["date"] = $temp[0];
	
				ereg($this->delta_author,$two,$temp);	//author
				$temp = trim($temp[0]);
				$temp = ereg_replace("(\n| )+"," ", $temp);
				list($author,$id) = explode(" ",$temp);
				$t["author"] = substr($id,0,strlen($id)-1);
			
				ereg($this->delta_state,$two,$temp);	//state value
				$temp = trim($temp[0]);
				$temp = ereg_replace("(\n| |\t)+"," ", $temp);
				list($state,$id) = explode(" ",$temp);
				$t["state"] = substr($id,0,strlen($id)-1);

//the reason next is done before branches is because the grammer states
//there always (num,date,author,state,branches,next), however branches is the only
//value that can have multible values, and thus, thanks to PHP being wierd, allows 
//you use string keys for some and ordinal for non defined keys (in this case the multible
//branches, so later we know all branches start at $t[5], $[6] ... etc
				ereg($this->delta_next,$two,$temp); //next
				$temp = trim($temp[0]);
				$temp = ereg_replace("(\n| |\t)+"," ",$temp);
				list($next,$num) = explode(" ",$temp);
				$num = substr($num,0,strlen($num)-1);
				$t["next"] = $num;

				ereg($this->delta_branches,$two,$temp);	//branches
				$temp = trim($temp[0]);
				$temp = ereg_replace("(\n| |\t)+"," ",$temp);
				$list = explode(" ",$temp);

				for($i = 1; $i < count($list); $i++) {
					//this removes the semicolon at the end
					if($list[$i][strlen($list[$i])-1] == ";") {
						$t[] = substr($list[$i],0,strlen($list[$i])-1);
					}
					else {
						$t[] = $list[$i];
					}
				}

				$this->delta_array[] = $t;
			}

		}

		//parses admin section, for now just getHead
		//users will define where this is going depending on info they want to see
		function getAdmin() {
			$temp = array();
			$one = null;
			$admin = array();

			if(ereg($this->admin,$this->filecontent,$temp)) {
				$one = $temp[0];
				$start = strlen($one);
				$length = strlen($this->filecontent);
				$file = substr($this->filecontent,$start,$length-$start);
				$one = trim($one);
/*

				//this gets the head, for some reason explode doens't take regex, so I had
				//to smash whitespace into a single space, may need to add various
				//whitespace/newline things to the ereg_replace one day
				if(ereg($this->a_head,$one,$temp)) {
					$one = substr($one,strlen($temp[0]));
					$temp[0] = ereg_replace("([\t| ])+"," ",$temp[0]);
					list($head,$head_num) = explode(" ",$temp[0]);
//					list($head,$head_num) = split("(\t| )*",$temp[0]);
					$admin["head"] = $head;
					$admin["head_num"] = $head_num;
				}

				if(ereg($this->a_branch,$one,$temp)) {
					$one = substr($one,strlen($temp[0]));
					$temp[0] = ereg_replace("([\t| ])+"," ",$temp[0]);
					list($branch,$branch_num) = explode(" ",$temp[0]);
					$admin["branch"] = $branch;
					$admin["branch_num"] = $branch_num;
				}
				else {
					$admin["branch"] = "";
					$admin["branch_num"] = "";
				}

				if(ereg($this->a_access,$one,$temp)) {
					$one = substr($one,strlen($temp[0]));
					$temp[0] = ereg_replace("([\t| ])+"," ",$temp[0]);
					$t = explode(" ",$temp[0]);
					$admin["access"] = array_shift($t);
					$admin["access_id"] = $t;	
				}

				//if there is a space between sym : num it will mess up
				//so it expects sym:num not sym: num or sym :num etc...
				if(ereg($this->a_symbols,$one,$temp)) {
					$one = substr($one,strlen($temp[0]));
					$temp[0] = ereg_replace("([\t| ])+"," ",$temp[0]);
					$t = explode(" ",$temp[0]);
					$admin["symbols"] = array_shift($t);
					$admin["sym_num"] = $t;
				}

				if(ereg($this->a_locks,$one,$temp)) {
					$one = substr($one,strlen($temp[0]));
					$temp[0] = ereg_replace("([\t| ])+"," ",$temp[0]);
					$t = explode(" ",$temp[0]);
					$admin["locks"] = array_shift($t);
					$admin["strict"] = array_pop($t);
					$admin["id_num"] = $t;
				}

				if(ereg("(comment$this->w($this->string)?;)?$this->w",$one,$temp)) {
					$temp[0] = trim($temp[0]);
					$t = array();
					ereg("comment$this->w",$temp[0],$t);
					$admin["comment"] = $t[0];
					$a = substr($one,strlen($t[0]));
					$admin["comment_string"] = $a;
					$one = substr($one,strlen($temp[0]));
				}

				if(ereg($this->a_expand,$one,$temp)) {
					$temp[0] = trim($temp[0]);
					$t = array();
					ereg("expand$this->w",$temp[0],$t);
					$admin["expand"] = $t[0];
					$a = substr($one,strlen($t[0]));
					$admin["expand_string"] = $a;
					$one = substr($one,strlen($temp[0]));
				}
*/
			}

		}

		//I believe this is the initial import / add of a file
		function getDescription() {
			$temp = array();

			$f = $this->filecontent;

			if(ereg($this->desc,$f,$temp)) {
				$three = $temp[0];
				$start = strlen($three) + strpos($f,$three);
				$length = strlen($f);
				$f = substr($f,$start,$length-$start);
				$three = trim($three);
				ereg($this->desc_desc,$three,$temp);
			}
		}

		//get the revision info ready to create a revision
		function getDeltaText() {
			$temp = array();
			$f = $this->filecontent;
			$this->deltaText_array = array();

			while(ereg($this->single_deltatext,$f,$temp)) {
				$four = $temp[0];
				$start = strlen($four) + strpos($f,$four);
				$length = strlen($f);
				$f = substr($f,$start,$length-$start);
				$four = trim($four);
		
				$a = array();
				
				ereg($this->deltatext_num,$four,$temp);
				$a["num"] = trim($temp[0]);

				ereg($this->deltatext_log,$four,$temp);
				$temp = trim($temp[0]);
				ereg($this->string,$temp,$temp);

				if(strlen($temp) == 2) {
					$a["log"] = "";
				}
				else {
					ereg_replace("@@","@",$temp[0]);	//replace any inner doubling with single
					$a["log"] = substr($temp[0],1,strlen($temp[0])-2); //remove the surronding @
				}

				ereg($this->deltatext_text,$four,$temp);
				$temp = trim($temp[0]);
				ereg($this->string,$temp,$temp);

				if(strlen($temp) == 2) {
					$a["text"] = "";
				}
				else {
					ereg_replace("@@","@",$temp[0]);	//replace any inner doubling with single
					$a["text"] = substr($temp[0],1,strlen($temp[0])-2); //remove the surronding @
				}

				$this->deltaText_array[$a["num"]] = $a;
			}
		}
	}
?>

<?php
require_once $gfcommon.'import/import_forums.php';
require_once($gfcommon.'include/User.class.php');
class News{
	
	function __construct($news, $group_id, $users){
		$this->news = $news;
		$this->group_id = $group_id;
		//create hash table hashrn{real_name:mail} 
		foreach($users as $user => $infos){
			$this->hashrn[$infos['real_name']] = $infos['mail'];
		}
	}
	
	function news_fill(){
	//	$sys_news_group = 3; Use this var instead.
		$importForum = new Forums($this->news, 3 , true);
		
		
		foreach($importForum->forums as $singleNews){
			$fid = $importForum->create_forum($singleNews);
			$this->addToDB($singleNews, $fid);
		}
	}
	
	function addToDB($news, $fid){
		db_begin();
		$uid = user_get_object_by_mail($this->hashrn[$news['poster_name']])->getID();
		$sql="INSERT INTO news_bytes (group_id,submitted_by,is_approved,post_date,forum_id,summary,details) ".
					" VALUES ($1, $2, $3, $4, $5, $6, $7)";
		$result=db_query_params($sql,
					array($this->group_id, $uid , 0, strtotime($news['date']), $fid, $news['summary'], $news['news_content']));
   		if (!$result) {
			db_rollback();
			echo "\nError adding new to Database\n";
   		} else {
			db_commit();
   		}
	}	
	
//	
//	function create_news($news){
//		$date = $news["date"];
//		$content = $news["news_content"];
//		$postername = $news["poster_name"];
//		$summary = $news["summary"];
//		$forum = $news["forum"];
//		
	
	
	
}

<?php
/*
 * Script to import members of mailman lists in the "plugin_mailman" table
 * must be done before adding extend.py file in /var/lib/mailman/lists
 * but after creating plugin_mailman table in the database
 */
require_once 'pre.php';
require_once 'mailman/include/MailmanListDao.class.php';
$sql = "SELECT * from mail_group_list WHERE status=3";
$result = db_query_params($sql,array());
while($row = db_fetch_array($result)) {
	$members= null;
	$usernames = null;
	$config = null;
	$listname = $row['list_name'];
	$dao = new MailmanListDao(CodendiDataAccess::instance());
	echo "\n".$listname."\n**********************************\n";
	exec('/usr/lib/mailman/bin/dumpdb '.'/var/lib/mailman/lists/'.$listname.'/config.pck', $config, $ret2);
	if ($ret2==0 ) {
		$j=0;
		for ($j=0;$j<count($config);$j++){
			if(preg_match("#\'(usernames|passwords)\'#",$config[$j],$essai)) {
				$end=false;
				$i = $j;
				if (strpos($config[$j],"usernames") !== false)
				{
					$username = true;
					$pwd =false;
				}
				elseif(strpos($config[$j],"passwords") !== false) {
					$username = false;
					$pwd =true;
				}
				while ($end==false) {
					if(preg_match("#([a-zA-Z0-9-_.]*@[a-zA-Z0-9-_.]*).*[u:\s]*\'([a-zA-Z0-9-_.\s]*)\'#",$config[$i],$essai)){
						if($username) {
							$usernames[$essai[1]] = $essai[2];
						}
						elseif($pwd) {
							$members[$essai[1]]=$essai[2];
						}
					}

					if (strpos($config[$i],"}") ===false) {
						$i=$i+1;
					}
					else {
						$end=true;
					}
				}

			}
		}
		if (isset($members)) {
			foreach ($members as $mail => $pwd) {
				$res = $dao->userIsMonitoring($mail,$listname);
				if (!$res) {
					echo "Error on Query :".db_error();
				}
				else {
					$row_count = $res->getRow();
					if ($row_count['count'] == 0){
						if (isset($usernames) && array_key_exists($mail, $usernames)) {
							$name = $usernames[$mail];
							echo "Membre trouvé avec mail=".$mail." et nom=".$name." avec pwd=".$pwd."\n";
							$dao->newSubscriber($mail,$name,md5($pwd),$listname);
						}
						else {
							echo "Membre trouvé avec mail=".$mail." avec pwd=".$pwd."\n";
							$dao->newSubscriber($mail,'',md5($pwd),$listname);
						}
					}
					else {
						echo $mail." est déjà membre de ".$listname."\n ";
					}
				}
			}
		}
		else {
			echo "Aucun membres dans cette liste\n";
		}
	}
	else {
		echo "Erreur";
	}
}
?>

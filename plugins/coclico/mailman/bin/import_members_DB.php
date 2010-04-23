<?php
/*
 * Script to import members of mailman lists in the "plugin_mailman" table
 * must be done before adding extend.py file in /var/lib/mailman/lists
 * but after creating plugin_mailman table in the database
*/

require_once 'pre.php';
require_once 'mailman/include/MailmanListDao.class.php';
$sql = 'SELECT * from mail_group_list WHERE status=3';
$result = db_query($sql);
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
		$foundname=0;
		$foundpw=0;
		for ($j=0;$j<count($config);$j++){
			if ($foundname) {
				if(preg_match("#([a-zA-Z0-9-_.]*@[a-zA-Z0-9-_.]*).*[u:\s]*\'([a-zA-Z0-9-_.\s]*)\'#",$config[$j],$essai)){
					$usernames[$essai[1]] = $essai[2];
				}
				if (strpos($config[$j],"}") !==false) {
					$foundname=0;
				}
				$essai =null;
			}
			if ($foundpw) {
				if(preg_match("#([a-zA-Z0-9-_.]*@[a-zA-Z0-9-_.]*).*\:[\s]*\'([a-zA-Z0-9-_.]*)\'#",$config[$j],$pwd)){
					$members[$pwd[1]] = $pwd[2];
				}
				if (strpos($config[$j],"}") !==false) {
					$foundpw=0;
				}
				$pwd =null;
			}
			if(strpos($config[$j],"usernames") !== false && preg_match("#\{\s*\}#",$config[$j]) == false) {
				$foundname = 1;
				if(preg_match("#([a-zA-Z0-9-_.]*@[a-zA-Z0-9-_.]*).*[u:\s]*\'([a-zA-Z0-9-_.\s]*)\'#",$config[$j],$essai)){
					$usernames[$essai[1]] = $essai[2];
				}
				if (strpos($config[$j],"}") !==false) {
					$foundname=0;
				}
				$essai =null;
			}

			if(strpos($config[$j],"passwords") !== false && strpos($config[$j],"{\s*}") === false) {
				$foundpw = 1;
				if(preg_match("#([a-zA-Z0-9-_.]*@[a-zA-Z0-9-_.]*)\'.*\:[\s]*\'([a-zA-Z0-9-_.]*)\'#",$config[$j],$pwd)){
					$members[$pwd[1]] = $pwd[2];
				}
				if (strpos($config[$j],"}") !==false) {
					$foundpw=0;
				}
				$pwd =null;
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
						if (isset($usernames)) {
							$insert =false;
							foreach ($usernames as $mailu => $name) {
								if( $mailu == $mail) {
									echo "Membre trouvé avec mail=".$mail." et nom=".$name." avec pwd=".$pwd."\n";
									$membersToAdd[$listname] =array($mail,md5($pwd),$name);
									$insert =true;
								}
							}
							if (!$insert) {
								echo "Membre trouvé avec mail=".$mail." avec pwd=".$pwd."\n";
								$membersToAdd[$listname] =array($mail,md5($pwd));
							}
						}
						else {
							echo "Membre trouvé avec mail=".$mail." avec pwd=".$pwd."\n";
							$membersToAdd[$listname] =array($mail,md5($pwd));
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


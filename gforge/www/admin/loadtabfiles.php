<?php
/**
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');
require_once('www/include/BaseLanguage.class');

session_require(array('group'=>'1','admin_flags'=>'A'));

if ($purgeall) {
	db_query("DROP TABLE tmp_lang;");
}

if ($loadall) {
	db_query("DROP TABLE tmp_lang;");
	db_query("CREATE TABLE tmp_lang (tmpid integer, language_id text, seq integer , pagename text, category text, tstring  text);");
	//db_commit();
	$rep= $sys_urlroot . 'include/languages/';
	//chdir($rep);
	$dir = opendir("$rep");
	while($file = readdir($dir)) {
		if(ereg("(.*)\.tab$",$file,$regs)){
			$tmpid=0;
			$language_id=$regs[1];
			$ary = file($rep . $file,1);
			for ($i=0; $i<sizeof($ary); $i++) {
				$seq=$i*10;
				if (substr($ary[$i], 0, 1) == '#') {
					$query="INSERT INTO tmp_lang values(". $seq . ",'" . $language_id . "'," . $seq . ",'#','#','" . $ary[$i] . "')";
					$tmpid++;
					db_query($query);
					continue;
				}
				$line = explode("\t", $ary[$i], 3);
#				//$query="INSERT INTO tmp_lang values(". $seq . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . $line[2] ."')";
				$query="INSERT INTO tmp_lang values(". $seq . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . addslashes(quotemeta(htmlspecialchars($line[2]))) ."')";
				//$query="INSERT INTO tmp_lang values(". $seq . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . base64_encode($line[2]) ."')";
				$tmpid++;
				$res=db_query($query);
				if (!$res){
					echo '<br />'.$query.'<br />'. db_error();
				}
			}
		}
	}
	rewinddir($dir);
	while($file = readdir($dir)) {
		if(ereg("(.*)\.tab$",$file,$regs)){
			$language_id=$regs[1];
			$query="INSERT INTO tmp_lang SELECT -1,'".$language_id."',seq+5,pagename,category,tstring FROM tmp_lang WHERE language_id='Base' AND pagename!='#' AND pagename||category NOT IN (select pagename||category FROM tmp_lang WHERE language_id='" . $language_id . "' ) ORDER BY seq";
			$res=db_query($query);
			if (!$res){
				echo '<br />'.$query.'<br />'. db_error();
			}
		}
	}
	closedir($dir);
	//db_commit();
}

site_admin_header(array('title'=>"Site Admin"));
?>

<form name="mload" method="post" action="<?php echo $PHP_SELF; ?>">

<input type="submit" name="loadall" value="<?php echo "(Re)Load all language files"; ?>" />
<input type="submit" name="purgeall" value="<?php echo "Purge loaded data"; ?>" />

</form>

<p />

<?php
$result=db_query("select language_id, count(language_id) AS count from tmp_lang where pagename!='#' and tmpid!='-1' group by language_id");
$result2=db_query("select language_id, count(language_id) AS count from tmp_lang where pagename!='#' group by language_id");
if (db_numrows($result)>0) {
?>
	<h3 style="color:red">Tables loaded:</h3>
<?php
	echo '
	<table border="0" bgcolor=white width="100%">
		<tr bgcolor=blue>
			<td><b><font color="white">Language</b></font></td>
			<td colspan=2><b><font color="white">Translated</b></font></td>
			<td colspan=2><b><font color="white">Extra</b></font></td>
			<td><b><font color="white">Translation</b></font></td>
			<td><b><font color="white">To Translate</b></font></td>
			<td><b><font color="white">Not in Base</b></font></td>
			<td><b><font color="white">Tab file</b></font></td>
		</tr>';
		
	$maxtrans=0;
	for ($i=0; $i<db_numrows($result) ; $i++) {
		$howmany=db_result($result, $i, 'count');
		//if ($howmany>$maxtrans) $maxtrans=$howmany;
		$lang=db_result($result, $i, 'language_id');
		if ($lang=='Base') $maxtrans=$howmany;
	}
	for ($i=0; $i<db_numrows($result) ; $i++) {
		$howmany=db_result($result,$i,'count');
		$howmany2=db_result($result2,$i,'count')-$maxtrans;
		$rate=$howmany * 100 / $maxtrans;
		$rate2=$howmany2 * 100 / $maxtrans;
		$language_id=db_result($result,$i,'language_id');
		echo "\n<tr bgcolor=lightblue><td>$language_id</td>";
		printf("<td>%d</td><td>%3.2f",$howmany,$rate);
		echo "%</td>";
		if ($rate2!=0) {
			printf("<td>%d</td><td>%3.2f",$howmany2,$rate2);
			echo "%</td>";
		} else {
			printf("<td colspan=2></td>");
		}
?>
<td>
	<a href="/admin/seetranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[see]</a>
	<a href="/admin/edittranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
</td>
<td>
	<a href="/admin/seenotranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[see]</a>
	<a href="/admin/editnotranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
</td>
<td>
	<a href="/admin/seenotinbasetabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">
	<? if ($rate2!=0) echo "[see]";?></a>
	<a href="/admin/editnotinbasetabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">
	<? if ($rate2!=0) echo "[edit]";?></a>
</td>
<td>
	<a href="/admin/seetabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[see]</a>
	<a href="/admin/edittabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
	<a href="/admin/gettabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[get]</a>
</td>
<?php
		echo "</tr>";
	}
	echo "\n</table>";
} else {
?>
	<h3 style="color:red">Available Tables:</h3>
		<table border="0">
<?php
	$rep= $sys_urlroot . 'include/languages/';
	//chdir($rep);
	$dir = opendir("$rep");
	while($file = readdir($dir)) {
		if(ereg("(.*)\.tab$",$file,$regs)){
			$language_id=$regs[1];
			echo "\n<tr><td>$language_id</td>";
			echo "<tr>";
		}
	}
	echo "\n</table>";
}

site_admin_footer(array());

?>

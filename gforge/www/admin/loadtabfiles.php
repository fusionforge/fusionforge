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
	$tmpid=0;
	while($file = readdir($dir)) {
		if(ereg("(.*)\.tab$",$file,$regs)){
			$language_id=$regs[1];
			$ary = file($rep . $file,1);
			for ($i=0; $i<sizeof($ary); $i++) {
				$seq=$i*10;
				if (substr($ary[$i], 0, 1) == '#') {
					$query="INSERT INTO tmp_lang values(". $tmpid . ",'" . $language_id . "'," . $seq . ",'#','#','" . $ary[$i] . "')";
					$tmpid++;
					db_query($query);
					continue;
				}
				$line = explode("\t", $ary[$i], 3);
#				$query="INSERT INTO tmp_lang values(". $tmpid . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . $line[2] ."')";
				$query="INSERT INTO tmp_lang values(". $tmpid . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . addslashes(quotemeta(htmlspecialchars($line[2]))) ."')";
				$tmpid++;
				$res=db_query($query);
				if (!$res){
					echo '<br />'.$query.'<br />'. db_error();
				}
			}
		}
	}
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
$result=db_query("select language_id, count(language_id) AS count from tmp_lang where pagename!='#' group by language_id");
if (db_numrows($result)>0) {
?>
	<h3 style="color:red">Tables loaded:</h3>
<?php
	echo "<table border=\"0\">";
	$maxtrans=0;
	for ($i=0; $i<db_numrows($result) ; $i++) {
		$howmany=db_result($result, $i, 'count');
		if ($howmany>$maxtrans) $maxtrans=$howmany;
	}
	for ($i=0; $i<db_numrows($result) ; $i++) {
		$howmany=db_result($result,$i,'count');
		$rate=$howmany * 100 / $maxtrans;
		$language_id=db_result($result,$i,'language_id');
		echo "\n<tr><td>$language_id</td>";
		printf("<td>(%d)</td><td>[%3.2f",$howmany,$rate);
		echo "%]</td>"
?>
<td><a href="/admin/seetabfiles.php?lang=<?php echo "$language_id"; ?>">[see translations]</a>
</td>
<td><a href="/admin/notranstabfiles.php?lang=<?php echo "$language_id"; ?>">[see untranslated]</a>
</td>
<td><a href="/admin/edittabfiles.php?lang=<?php echo "$language_id"; ?>">[edit(don t work)]</a>
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

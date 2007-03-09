<?php
/**
 * //TODO DESCRIPTION
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../../env.inc.php');
require_once('pre.php');
require_once('common/include/account.php');
require_once('common/include/escapingUtils.php');
require_once('www/admin/admin_utils.php');
require_once('www/include/BaseLanguage.class');

session_require(array('group'=>'1','admin_flags'=>'A'));

if (getStringFromRequest('purgeall')) {
	db_query("DROP TABLE tmp_lang;");
}

if (getStringFromRequest('loadall')) {
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
					$query="INSERT INTO tmp_lang values(". $seq . ",'" . $language_id . "'," . $seq . ",'#','#" . $seq . "','" . $ary[$i] . "')";
					$tmpid++;
					db_query($query);
					continue;
				}
				$line = explode("\t", $ary[$i], 3);
				//$query="INSERT INTO tmp_lang values(". $seq . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . $line[2] ."')";
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
			if ($sys_database_type == "mysql") {
				$query="INSERT INTO tmp_lang SELECT -1,'".$language_id."',seq+5,pagename,category,tstring FROM tmp_lang WHERE language_id='Base' AND pagename!='#' AND concat(pagename,category) NOT IN (select concat(pagename,category) FROM tmp_lang WHERE language_id='" . $language_id . "' ) ORDER BY seq";
			} else {
				$query="INSERT INTO tmp_lang SELECT -1,'".$language_id."',seq+5,pagename,category,tstring FROM tmp_lang WHERE language_id='Base' AND pagename!='#' AND pagename||category NOT IN (select pagename||category FROM tmp_lang WHERE language_id='" . $language_id . "' ) ORDER BY seq";
			}
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

<form name="mload" method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>">

<input type="submit" name="loadall" value="<?php echo "(Re)Load all language files"; ?>" />
<input type="submit" name="purgeall" value="<?php echo "Purge loaded data"; ?>" />

</form>

<p />

<?php
$result1=db_query("select language_id, count(language_id) AS count from tmp_lang where pagename!='#' and tmpid!='-1' group by language_id");
$result2=db_query("select language_id, count(language_id) AS count from tmp_lang where pagename!='#' group by language_id");
if (db_numrows($result1)>0) {
?>
	<span class="important">Tables loaded:</span>
<?php
	echo '
	<table border="0">
		<tr class="tableheading">
			<td>Language</td>
			<td colspan=2>Translated</td>
			<td colspan=2>Extra</td>
			<td>Double</td>
			<td>Translation</td>
			<td>To Translate</td>
			<td>Not in Base</td>
			<td>Tab file</td>
		</tr>';
		
	$maxtrans=0;
	for ($i=0; $i<db_numrows($result1) ; $i++) {
		$howmany1=db_result($result1, $i, 'count');
		//if ($howmany>$maxtrans) $maxtrans=$howmany;
		$lang=db_result($result1, $i, 'language_id');
		if ($lang=='Base') $maxtrans=$howmany1;
	}
	for ($i=0; $i<db_numrows($result1) ; $i++) {
		$howmany1=db_result($result1,$i,'count');
		$howmany2=db_result($result2,$i,'count')-$maxtrans;
		$rate1=$howmany1 * 100 / $maxtrans;
		$rate2=$howmany2 * 100 / $maxtrans;
		$language_id=db_result($result1,$i,'language_id');
		echo "\n<tr ".$HTML->boxGetAltRowStyle($i)."><td>$language_id</td>";
		printf("<td>%d</td><td>%3.2f",$howmany1,$rate1);
		echo "%</td>";
		if ($rate2!=0) {
			printf("<td>%d</td><td>%3.2f",$howmany2,$rate2);
			echo "%</td>";
		} else {
			printf("<td colspan=2></td>");
		}
?>
<td>
	<a href="editdouble.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
</td>
<td>
	<a href="seetranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[see]</a>
	<a href="edittranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
</td>
<td>
	<a href="seenotranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[see]</a>
	<a href="editnotranstabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
</td>
<td>
	<a href="seenotinbasetabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">
	<? if ($rate2!=0) echo "[see]";?></a>
	<a href="editnotinbasetabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">
	<? if ($rate2!=0) echo "[edit]";?></a>
</td>
<td>
	<a href="seetabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[see]</a>
	<a href="edittabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[edit]</a>
	<a href="gettabfiles.php?function=show&lang=<?php echo "$language_id"; ?>">[get]</a>
</td>
<?php
		echo "</tr>";
	}
	echo "\n</table>";
} else {
?>
	<span class="important">Available Tables:</span>
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

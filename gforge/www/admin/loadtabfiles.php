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

if ($load) {
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
				$query="INSERT INTO tmp_lang values(". $tmpid . ",'" . $language_id . "'," . $seq . ",'" . $line[0] . "','" . $line[1] . "','" . $line[2] ."')";
				$tmpid++;
				db_query($query);
			}
		}
	}
	//db_commit();
}

site_admin_header(array('title'=>"Site Admin"));
?>

<h3>Load language tab files</h3>

<form name="mload" method="post" action="<?php echo $PHP_SELF; ?>">

<input type="submit" name="load" value="<? echo "Load all language files"; ?>">

</form>

<p>

<?
if ($load) {
?>
	<H3 color=red>Tables loaded:</H3>
<?
	$result=db_query("select language_id, count(language_id) AS count from tmp_lang where pagename!='#' group by language_id");
	echo "<TABLE border=0>";
	$maxtrans=0;
	for ($i=0; $i<db_numrows($result) ; $i++) {
		$howmany=db_result($result, $i, 'count');
		if ($howmany>$maxtrans) $maxtrans=$howmany;
	}
	for ($i=0; $i<db_numrows($result) ; $i++) {
		$howmany=db_result($result,$i,'count');
		$rate=$howmany * 100 / $maxtrans;
		$language_id=db_result($result,$i,'language_id');
		echo "\n<TR><TD>$language_id</TD>";
		printf("<TD>(%d)</TD><TD>[%3.2f",$howmany,$rate);
		echo "%]</TD>"
?>
<TD><A HREF=/admin/seetabfiles.php?lang=<? echo "$language_id"; ?>>[see translations]</A>
</TD>
<TD><A HREF=/admin/notranstabfiles.php?lang=<? echo "$language_id"; ?>>[see untranslated]</A>
</TD>
<TD><A HREF=/admin/edittabfiles.php?lang=<? echo "$language_id"; ?>>[edit(don t work)]</A>
</TD>
<?
		echo "</TR>";
	}
	echo "\n</TABLE>";
} else {
?>
	<H3 color=red>Available Tables:</H3>
		<TABLE border=0>
<?
	$rep= $sys_urlroot . 'include/languages/';
	//chdir($rep);
	$dir = opendir("$rep");
	while($file = readdir($dir)) {
		if(ereg("(.*)\.tab$",$file,$regs)){
			$language_id=$regs[1];
			echo "\n<TR><TD>$language_id</TD>";
			echo "<TR>";
		}
	}
	echo "\n</TABLE>";
}

site_admin_footer(array());

?>

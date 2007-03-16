<?php
/**
 * GForge Doc Search engine
 *
 * 
 * Fabio Bertagnin November 2005
 *
 * @version   $Id: 04_IMPROVDOC_75_document_specific_search_engine.dpatch,v 1.1 2006/01/11 17:02:45 fabio Exp $
 */


/*
	Document Search Motor

	by Fabio Bertagnin

*/

require_once('pre.php');
require_once('include/vtemplate.class.php');
require_once('include/doc_utils.php');
require_once('common/docman/DocumentFactory.class');
require_once('common/docman/DocumentGroupFactory.class');

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$df = new DocumentFactory($g);
if ($df->isError()) {
	exit_error(_('Error'),$df->getErrorMessage());
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError()) {
	exit_error(_('Error'),$dgf->getErrorMessage());
}

// the "selected language" variable will be used in the links to navigate the
// document groups tree

if (!$language_id) {
	if (session_loggedin()) {
		$language_id = $LUSER->getLanguage();
	} else {
		$language_id = 1;
	}
	
	$selected_language = $language_id;
} else if ($language_id == "*") {
	$language_id = 0 ;
	$selected_language = "*";
} else {
	$selected_language = $language_id;
}
$df->setLanguageID($language_id);


// check if the user is docman's admin
$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isDocEditor()) 
{
	$is_editor = false;
} else 
{
	$is_editor = true;
}


docman_header(_('Document Manager: Display Document'),_('Project: %1$s'),'docman','',$g->getPublicName());

$vtp = new VTemplate;
$handle = $vtp->Open("search.tpl.html");
$vtp->NewSession($handle,"MAIN");

$allchecked = ""; $onechecked = ""; 
if ($_POST["search_type"] == "one") {$onechecked = "checked";}
else {$allchecked = "checked";}
$vtp->AddSession($handle,"FORMSEARCH");
$vtp->SetVar($handle,"FORMSEARCH.TITLE",_('Search in documents'));
$vtp->SetVar($handle,"FORMSEARCH.GROUP_ID",$_GET["group_id"]);
$vtp->SetVar($handle,"FORMSEARCH.TEXTSEARCH",$_POST["textsearch"]);
$vtp->SetVar($handle,"FORMSEARCH.ALLCHECKED",$allchecked);
$vtp->SetVar($handle,"FORMSEARCH.ONECHECKED",$onechecked);
$vtp->SetVar($handle,"FORMSEARCH.SUBMIT_PROMPT",_('Search'));
$vtp->SetVar($handle,"FORMSEARCH.SEARCH_ALL_WORDS",_('With all the words'));
$vtp->SetVar($handle,"FORMSEARCH.SEARCH_ONE_WORD",_('With at least one of words'));
$vtp->CloseSession($handle,"FORMSEARCH");

if ($_POST["cmd"] == "search")
{
	
	$textsearch = $_POST["textsearch"];
	$textsearch = prepare_search_text ($textsearch);
	$mots = preg_split("/[\s,]+/",$textsearch);
	$WHERE = "WHERE TRUE ";
	if ($_POST["search_type"] == "one")
	{
		if (count($mots) > 0)
		{
			$WHERE .= "AND (FALSE ";
			foreach ($mots as $mot)
			{
				$mot = strtolower($mot); 
				$WHERE .= "OR title LIKE '%$mot%' OR description LIKE '%$mot%' OR data_words LIKE '%$mot%' ";
			//	$WHERE .= "OR data_words LIKE '%$mot%' ";
			}
			$WHERE .= " ) ";
		}
	}
	else
	{
		// search_type = all
		if (count($mots) > 0)
		{
			$WHERE .= "AND (TRUE ";
			foreach ($mots as $mot)
			{
				$WHERE .= "AND (title LIKE '%$mot%' OR description LIKE '%$mot%' OR data_words LIKE '%$mot%') ";
				//$WHERE .= "AND data_words LIKE '%$mot%' ";
			}
			$WHERE .= " ) ";
		}
	}
	if (!$is_editor)
	{
		$WHERE .= "AND doc_data.stateid = 1 ";
	}
	
	$sql = "SELECT filename, docid, doc_data.stateid as stateid, doc_states.name as statename, ";
	$sql .= "title, description, createdate, updatedate, ";
	$sql .= "doc_group, language_id, group_id ";
	$sql .= "FROM doc_data \n";
	$sql .= "JOIN doc_states ON doc_data.stateid = doc_states.stateid ";
	$sql .= "$WHERE \n";
	$sql .= "AND group_id = $_GET[group_id] \n";
	$sql .= "ORDER BY updatedate,createdate \n";
	$resarr = array();
	$result=db_query($sql);
	if (!$result)
	{
		$vtp->AddSession($handle,"MESSAGE");
		$vtp->SetVar($handle,"MESSAGE.TEXT",_('Database query error'));
		$vtp->CloseSession($handle,"MESSAGE");
	}
	elseif (db_numrows($result) < 1) 
	{
		$vtp->AddSession($handle,"MESSAGE");
		$vtp->SetVar($handle,"MESSAGE.TEXT",_('Your search did not match any documents'));
		$vtp->CloseSession($handle,"MESSAGE");
	}
	else
	{
		while ($arr = db_fetch_array($result))
		{
			$resarr[] = $arr;
		}
	}
	db_free_result($result);
 	// print_debug ($sql);
	// need groups infos
	$groupsarr = array();
	$sql = "SELECT doc_group, groupname, parent_doc_group FROM doc_groups WHERE group_id = $_GET[group_id] \n";
	$groupsarr = array();
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0)
	{
		while ($arr = db_fetch_array($result))
		{
			$groupsarr[] = $arr;
		}
	}
	db_free_result($result);
	
	$vtp->AddSession($handle,"RESULTSEARCH");
	$count = 0;
	foreach ($resarr as $item)
	{
		$count++;
		$vtp->AddSession($handle,"RESULT");
		$vtp->SetVar($handle,"RESULT.N",$count);
		$vtp->SetVar($handle,"RESULT.SEARCHTITLE",$item["title"]);
		$vtp->SetVar($handle,"RESULT.SEARCHCOMMENT",$item["description"]);
		$s = get_path_document ($groupsarr, $item["doc_group"], "$_GET[group_id]", "$item[language_id]");
		$vtp->SetVar($handle,"RESULT.SEARCHPATH",$s);
		$vtp->SetVar($handle,"RESULT.GROUP_ID",$_GET["group_id"]);
		$vtp->SetVar($handle,"RESULT.DOC_ID",$item["docid"]);
		$vtp->SetVar($handle,"RESULT.FILE_NAME",$item["filename"]);
		if ($is_editor) $vtp->SetVar($handle,"RESULT.STATE",$item["statename"]);
		$vtp->CloseSession($handle,"RESULT");
	}
	$vtp->CloseSession($handle,"RESULTSEARCH");
}

$vtp->CloseSession($handle,"MAIN");
$vtp->Display();

// print_debug (print_r($_POST,true));
// print_debug (print_r($groupsarr,true));
// print_debug ($sql);

docman_footer(array());

function print_debug ($text)
{
	echo "<pre>$text</pre>";
}

function get_path_document ($groupsarr, $doc_group, $group_id, $language_id="1")
{
	$rep = "";
	foreach ($groupsarr as $group)
	{
		if ($group["doc_group"] == $doc_group)
		{
			if ($group["parent_doc_group"] == 0) 
			{
				$href = "/docman/index.php?group_id=$group_id&selected_doc_group_id=$group[doc_group]&language_id=$language_id";
				$rep .= "<a href=\"$href\" style=\"color:#00610A;\">$group[groupname]</a>";
				break;
			}
			$s = get_path_document ($groupsarr,  $group["parent_doc_group"], $group_id, $language_id);
			$href = "/docman/index.php?group_id=$group_id&selected_doc_group_id=$group[doc_group]&language_id=$language_id";
			$rep .= "$s / <a href=\"$href\" style=\"color:#00610A;\">$group[groupname]</a>";
			break;
		}
	}
	return $rep;
}

function prepare_search_text ($text)
{
	
	$rep = $text; 
	$rep = utf8_decode($rep);
	$rep = ereg_replace ("é", "e", $rep);
	$rep = ereg_replace ("è", "e", $rep);
	$rep = ereg_replace ("ê", "e", $rep);
	$rep = ereg_replace ("à", "a", $rep);
	$rep = ereg_replace ("ù", "u", $rep);
	$rep = ereg_replace ("ç", "c", $rep);
	$rep = ereg_replace ("é", "e", $rep);
	$rep = strtolower ($rep);
	return $rep;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

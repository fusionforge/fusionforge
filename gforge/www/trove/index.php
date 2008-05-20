<?php
/**
  *
  * SourceForge Trove Software Map
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: trove_list.php,v 1.160 2001/05/22 16:12:12 pfalcon Exp $
  *
  */

require_once('../env.inc.php');
require_once('pre.php');
require_once('trove.php');

require_once('common/include/escapingUtils.php');
require_once('TroveCategory.class.php');
require_once('TroveCategoryFactory.class.php');
require_once('include/utils.php');

if (!$sys_use_trove) {
	exit_disabled();
}

$categoryId = getIntFromGet('form_cat');

// assign default if not defined
if (!$categoryId) {
	$categoryId = $default_trove_cat;
}

$category = new TroveCategory($categoryId);

	$HTML->header(array('title'=>_('Trove Map')));

echo '<hr />';

// We check current filtering directives and display them

$filter = getStringFromGet('discrim');

if($filter) {

	// check and clean the array
	$filterArray = explode(',', $filter);
	$cleanArray = array();	
	$count = max(6, sizeof($filterArray));
	for ($i = 0; $i < $count; $i++) {
		if(is_numeric($filterArray[$i]) && $filterArray[$i] != 0) {
			$cleanArray[] = (int) $filterArray[$i];
		}
	}
	$filterArray = array_unique($cleanArray);
	if(!empty($filterArray)) {
		$filterCategories = TroveCategoryFactory::getCategories($filterArray);
	
		echo '<p><span style="color:red;">'._('Limiting View').'</span>';
		
		for($i=0, $count = sizeof($filterCategories); $i < $count; $i++) {
			$filterCategory =& $filterCategories[$i];
			echo '<br /> &nbsp; &nbsp; &nbsp; '
				.$filterCategory->getFullPath()
				.' <a href="?form_cat='.$category->getId()
				.getFilterUrl($filterArray, $filterCategory->getId()).'">['._('Remove Filter').']'
				.'</a>';
		}
	
		echo '</p><hr />';
	}
	
	$category->setFilter($filterArray);
}

// We display the trove

?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr valign="top">
		<td width="50%">
		<?php
			// here we print list of root level categories, and use open folder for current
			$rootCategories = TroveCategoryFactory::getRootCategories();
			echo _('Browse By').':';
			for($i = 0, $count = sizeof($rootCategories); $i < $count; $i++) {
				$rootCategory =& $rootCategories[$i];
				
				// print open folder if current, otherwise closed
				// also make anchor if not current

				echo '<br />';
				if (($rootCategory->getId() == $category->getRootParentId())
					|| ($rootCategory->getId() == $category->getId())) {
					
					echo html_image('ic/ofolder15.png','15','13',array());
					echo '&nbsp; <strong>'.$rootCategory->getLocalizedLabel().'</strong>';
				} else {
					echo '<a href="?form_cat='.$rootCategory->getId().@$discrim_url.'">';
					echo html_image('ic/cfolder15.png', '15', '13', array());
					echo '&nbsp; '.$rootCategory->getLocalizedLabel();
					echo '</a>';
				}
			}
		?>
		</td>
		<td width="50%">
		<?php
			$currentIndent='';
			$parentCategories =& $category->getParents();
			for ($i=0, $count = sizeof($parentCategories); $i < $count; $i++) {
				echo str_repeat(' &nbsp; ', $i * 2);

				echo html_image('ic/ofolder15.png', '15', '13', array());
				echo '&nbsp; ';
				if($parentCategories[$i]['id'] != $category->getId()) {
					echo '<a href="?form_cat='.$parentCategories[$i]['id'].$discrim_url.'">';
				} else {
					echo '<strong>';
				}
				echo $parentCategories[$i]['name'];
				if($parentCategories[$i]['id'] != $category->getId()) {
					echo '</a>';
				} else {
					echo '</strong>';
				}
				echo '<br />';
			}
			
			$childrenCategories =& $category->getChildren();
			
			$currentIndent .= str_repeat(' &nbsp; ', sizeof($parentCategories) * 2);
			
			for($i = 0, $count = sizeof($childrenCategories); $i < $count; $i++) {
				$childCategory =& $childrenCategories[$i];
				
				echo $currentIndent;
				echo '<a href="?form_cat='.$childCategory->getId().@$discrim_url.'">';
				echo html_image('ic/cfolder15.png', '15', '13', array());
				echo '&nbsp; '.$childCategory->getLocalizedLabel().'</a>';
				echo ' <em>('
					.sprintf(_('%1$s projects'), $childCategory->getSubProjectsCount())
					.')</em><br />';
			}
		?>
		</td>
	</tr>
</table>
<hr />
<?php

// We display projects

$offset = getIntFromGet('offset');

$projectsResult = $category->getProjects($offset);
/*
$res_grp = db_query("
	SELECT * 
	FROM trove_agg
	$discrim_queryalias
	WHERE trove_agg.trove_cat_id='$form_cat'
	$discrim_queryand
	ORDER BY trove_agg.trove_cat_id ASC, trove_agg.ranking ASC
", $TROVE_HARDQUERYLIMIT, 0, SYS_DB_TROVE);
echo db_error();
$querytotalcount = db_numrows($res_grp);
	
// #################################################################
// limit/offset display

// no funny stuff with get vars

if (!isset($page) || !is_numeric($page)) {
	$page = 1;
}

// store this as a var so it can be printed later as well
$html_limit = '<span style="text-align:center;font-size:smaller">';
if ($querytotalcount == $TROVE_HARDQUERYLIMIT){
	$html_limit .= 'More than ';
	$html_limit .= $Language->getText('trove_list','more_than',array($querytotalcount));
	
	}
$html_limit .= $Language->getText('trove_list','number_of_projects',array($querytotalcount));

// only display pages stuff if there is more to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	$html_limit .= ' Displaying '.$TROVE_BROWSELIMIT.' per page. Projects sorted by activity ranking.<br />';

	// display all the numbers
	for ($i=1;$i<=ceil($querytotalcount/$TROVE_BROWSELIMIT);$i++) {
		$html_limit .= ' ';
		if ($page != $i) {
			$html_limit .= '<a href="/softwaremap/trove_list.php?form_cat='.$form_cat;
			$html_limit .= $discrim_url.'&page='.$i;
			$html_limit .= '">';
		} else $html_limit .= '<strong>';
		$html_limit .= '&lt;'.$i.'&gt;';
		if ($page != $i) {
			$html_limit .= '</a>';
		} else $html_limit .= '</strong>';
		$html_limit .= ' ';
	}
}

$html_limit .= '</span>';

print $html_limit."<hr />\n";
*/
?><table border="0" cellpadding="0" width="100%"><?php
while($project = db_fetch_array($projectsResult)) {
	?>
		<tr valign="top">
			<td colspan="2"><?php
			echo $count.'. <a href="/projects/'.strtolower($project['unix_group_name']).'/"><strong>'
			.htmlspecialchars($project['group_name']).'</strong></a>';
			if ($project['short_description']) {
				echo ' - '.htmlspecialchars($project['short_description']);
			}
			?>
			<br />&nbsp;
			</td>
		</tr>
		<tr valign="top">
			<td><?php
				// list all trove categories
				//print trove_getcatlisting($row_grp['group_id'],1,0);
			?></td>
			<td align="right">
				Activity Percentile: <strong><?php echo number_format($project['percentile'],2) ?></strong>
				<br />Activity Ranking: <strong><?php echo number_format($project['ranking'],2) ?></strong>
				<br />Register Date: <strong><?php echo date($sys_datefmt, $project['register_time']) ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr /></td>
		</tr>
	<?php
} ?>
</table>
<?php
// print bottom navigation if there are more projects to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	print $html_limit;
}

$HTML->footer(array());

?>

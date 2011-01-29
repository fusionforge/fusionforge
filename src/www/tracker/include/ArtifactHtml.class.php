<?php
/*
 *
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://sourceforge.net
 *
 */


require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'include/utils_crossref.php';

class ArtifactHtml extends Artifact {

	/**
	 *  ArtifactHtml() - constructor
	 *
	 *  Use this constructor if you are modifying an existing artifact
	 *
	 *  @param $ArtifactType object
	 *  @param $artifact_id integer (primary key from database)
	 *  @return true/false
	 */
	function ArtifactHtml(&$ArtifactType,$artifact_id=false) {
		return $this->Artifact($ArtifactType,$artifact_id);
	}

	/**
	 * show details preformatted (like followups)
	 */
	function showDetails($editable = false) {
		$result = $this->getDetails();
		$result = util_gen_cross_ref($result, $this->ArtifactType->Group->getID());
		//$result = util_line_wrap( $result, 120,"\n");
		$result = preg_replace('/\r?\n/', '<br />', $result);

		$title_arr = array();
		if ($editable === true) {
			$title_arr[] = '<div style="width:100%;">' .
				'<div style="float:left">' . _('Detailed description') . '</div>' .
				'<div style="float:right">' . html_image('ic/forum_edit.gif','37','15',array('title'=>"Click to edit", 'alt'=>"Click to edit", 'onclick'=>"switch2edit(this, 'show', 'edit')")) . '</div>' .
				'</div>';
		}
		else {
			$title_arr[] = _('Detailed description');
		}
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		echo '<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle(0) .'><td>'. $result. '</td></tr>';

		echo $GLOBALS['HTML']->listTableBottom();
	}


	function showMessages() {
		$result= $this->getMessages();
		$rows=db_numrows($result);

		if ($rows > 0) {
			$title_arr=array();
			$title_arr[]=_('Message');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>';

				$params = array('user_id' => db_result($result,$i,'user_id'), 'size' => 's');
				plugin_hook("user_logo", $params);

				echo _('Date').': '.
					date(_('Y-m-d H:i'),db_result($result, $i, 'adddate')) .'<br />'.
					_('Sender').': ';
				if(db_result($result,$i,'user_id') == 100) {
					echo db_result($result,$i,'realname');
				} else {
					echo util_make_link_u (db_result($result,$i,'user_name'),db_result($result,$i,'user_id'),db_result($result,$i,'realname'));
				}

				$text = db_result($result, $i, 'body');
				$text = util_gen_cross_ref($text, $this->ArtifactType->Group->getID());
				//$text = util_line_wrap( $text, 120,"\n");
				$text = preg_replace('/\r?\n/', '<br />', $text);
				echo "<br /><br />".$text.'</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
				<p>'._('No Followups Have Been Posted').'</p>';
		}
	}

	function showHistory() {
		global $artifact_cat_arr,$artifact_grp_arr,$artifact_res_arr;
		$result=$this->getHistory();
		$rows= db_numrows($result);

		if ($rows > 0) {

			$title_arr=array();
			$title_arr[]=_('Field');
			$title_arr[]=_('Old Value');
			$title_arr[]=_('Date');
			$title_arr[]=_('By');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			$artifactType =& $this->getArtifactType();

			for ($i=0; $i < $rows; $i++) {
				$field=db_result($result, $i, 'field_name');
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.$field.'</td><td>';

				if ($field == 'status_id') {

					echo $artifactType->getStatusName(db_result($result, $i, 'old_value'));

				} else if ($field == 'assigned_to') {

					echo user_getname(db_result($result, $i, 'old_value'));

				} else if ($field == 'close_date') {
					if (db_result($result, $i, 'old_value'))
						echo date(_('Y-m-d H:i'),db_result($result, $i, 'old_value'));
					else 
						echo '<i>None</i>';
				} else {

					echo db_result($result, $i, 'old_value');

				}
				echo '</td>'.
					'<td>'. date(_('Y-m-d H:i'),db_result($result, $i, 'entrydate')) .'</td>'.
					'<td>'. db_result($result, $i, 'user_name'). '</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
			<p>'._('No Changes Have Been Made to This Item').'</p>';
		}

	}

	function showRelations() {
		$aid = $this->getID();

		// Search for all relations pointing to this record.

		$res = db_query_params ('SELECT *
		FROM artifact_extra_field_list, artifact_extra_field_data, artifact_group_list, artifact, groups
		WHERE field_type=9
		AND artifact_extra_field_list.extra_field_id=artifact_extra_field_data.extra_field_id
		AND artifact_group_list.group_artifact_id = artifact_extra_field_list.group_artifact_id
		AND artifact.artifact_id = artifact_extra_field_data.artifact_id
		AND groups.group_id = artifact_group_list.group_id
		AND (field_data = $1 OR field_data LIKE $2 OR field_data LIKE $3 OR field_data LIKE $4)
		ORDER BY artifact_group_list.group_id ASC, name ASC, artifact.artifact_id ASC',
					array($aid,
					      "$aid %",
					      "% $aid %",
					      "% $aid"));
		if (db_numrows($res)>0) {
			?>
<div class="tabbertab" title="<?php echo _('Backward Relations'); ?>">
<table border="0" width="80%">
	<tr>
		<td colspan="2">
		<h2><?php echo _('Changes') ?>:</h2>
		<?php
		$current = '';
		$end = '';
		while ($arr = db_fetch_array($res)) {
			$title = $arr['group_name'].': '.$arr['name'];
			if ($title != $current) {
				echo $end.'<strong>'.$title.'</strong>';
				$current = $title;
				$end = '<br /><br />';
			}
			$text = '[#'.$arr['artifact_id'].']';
			$url = '/tracker/?func=detail&amp;aid='.$arr['artifact_id'].'&amp;group_id='.$arr['group_id'].'&amp;atid='.$arr['group_artifact_id'];
			$arg = 'title="'.$arr['summary'].'"' ;
			if ($arr['status_id'] == 2) {
				$arg .= 'class="artifact_closed"';
			}
			print '<br/>&nbsp;&nbsp;&nbsp;<a href="'.$url.'" '.$arg.'>'.$text.'</a>'.' <a href="'.$url.'">'.$arr['summary'].'</a> <i>(Relation: '.$arr['field_name'].')</i>';
		}
		?></td>
	</tr>
</table>
</div>
<?php
}	
	}

	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

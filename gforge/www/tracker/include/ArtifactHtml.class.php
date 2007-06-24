<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('common/tracker/Artifact.class.php');

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
	function showDetails() {
		global $Language;
		$result = $this->getDetails();

		$title_arr = array();
		$title_arr[] = _('Detailed description');
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		echo '<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><pre>'. util_line_wrap ( $result, 120,"\n"). '</pre></td></tr>';

		echo $GLOBALS['HTML']->listTableBottom();
	}


	function showMessages() {
		global $sys_datefmt;
		global $Language;
		$result= $this->getMessages();
		$rows=db_numrows($result);

		if ($rows > 0) {
			$title_arr=array();
			$title_arr[]=_('Message');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><pre>
'._('Date').': '. date($sys_datefmt,db_result($result, $i, 'adddate')) .'
'._('Sender').': ';
				if(db_result($result,$i,'user_id') == 100) {
					echo db_result($result,$i,'realname');
				} else {
					echo '<a href="'.$GLOBALS['sys_urlprefix'].'/users/'.db_result($result,$i,'user_name').'/">'.db_result($result,$i,'realname').'</a>';
				}
				echo "\n\n". util_line_wrap ( db_result($result, $i, 'body'),65,"\n"). '</pre></td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
				<h3>'._('No Followups Have Been Posted').'</h3>';
		}
	}

	function showHistory() {
		global $sys_datefmt,$artifact_cat_arr,$artifact_grp_arr,$artifact_res_arr, $Language;
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

					echo date($sys_datefmt,db_result($result, $i, 'old_value'));

				} else {

					echo db_result($result, $i, 'old_value');

				}
				echo '</td>'.
					'<td>'. date($sys_datefmt,db_result($result, $i, 'entrydate')) .'</td>'.
					'<td>'. db_result($result, $i, 'user_name'). '</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
			<h3>'._('No Changes Have Been Made to This Item').'</h3>';
		}

	}

}

?>

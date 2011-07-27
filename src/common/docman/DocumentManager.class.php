<?php
/**
 * FusionForge document manager
 *
 * Copyright 2011, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';

class DocumentManager extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array	$data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * Constructor.
	 *
	 * @param	object	The Group object to which this document is associated.
	 * @return	boolean	success.
	 */
	function DocumentManager(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setNotValidGroupObjectError();
			return false;
		}
		if ($Group->isError()) {
			$this->setError('DocumentManager:: '. $Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 * getGroup - get the Group object this Document is associated with.
	 *
	 * @return	Object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}


	/**
	 * getTrashID - the trash doc_group id for this DocumentManager.
	 *
	 * @return	integer	The trash doc_group id.
	 */
	function getTrashID() {
		if (isset($this->data_array['trashid']))
			return $this->data_array['trashid'];

		$res = db_query_params('SELECT doc_group from doc_groups
					WHERE groupname = $1
					AND group_id = $2
					AND stateid = $3',
					array('.trash', $this->Group->getID(), '2'));
		if (db_numrows($res) == 1) {
			$arr = db_fetch_array($res);
			$this->data_array['trashid'] = $arr['doc_group'];
			return $this->data_array['trashid'];
		} else {
			$dg = new DocumentGroup($this->Group);
			$dg->create('.trash');
			$dg->setStateID('2');
			return $dg->getID();
		}
		return false;
	}

	/**
	 * cleanTrash - delete all items in trash for this DocumentManager
	 *
	 * @return	boolean	true on success
	 */
	function cleanTrash() {
		$trashId = $this->getTrashID();
		if ($trashId !== -1) {
			db_begin();
			$emptyFile = db_query_params('DELETE FROM doc_data WHERE stateid=$1 and group_id=$2', array('2', $this->Group->getID()));
			if (!$emptyFile)	{
				db_rollback();
				return false;
			}
			$emptyDir = db_query_params('DELETE FROM doc_groups WHERE stateid=$1 and group_id=$2 and groupname !=$3', array('2', $this->Group->getID(), '.trash'));
			if (!$emptyDir) {
				db_rollback();
				return false;
			}
			db_commit();
			return true;
		}
		return false;
	}

	/**
	 *  getTree - display recursively the content of the doc_group. Only doc_groups within doc_groups.
	 *
	 * @param	string	the type of link in the menu
	 * @param	int		the doc_group to start: default 0
	 */
	function getTree($linkmenu, $docGroupId = 0) {
		$dg = new DocumentGroup($this->Group);
		switch ($linkmenu) {
			case "listtrashfile": {
				$stateId = 2;
				break;
			}
			default: {
				$stateId = 1;
				break;
			}
		}
		$subGroupIdArr = $dg->getSubgroup($docGroupId, $stateId);
		echo '<ul>';
		echo '<li><a href="?group_id='.$this->Group->getID().'&amp;view='.$linkmenu.'">/</a></il>';
		echo '<ul>';
		foreach ($subGroupIdArr as $subGroupIdValue) {
			$localDg = new DocumentGroup($this->Group, $subGroupIdValue);
			echo '<li><a href="?group_id='.$this->Group->getID().'&amp;view='.$linkmenu.'&amp;dirid='.$localDg->getID().'">'.$localDg->getName().'</a></il>';
			$this->getTree($linkmenu, $subGroupIdValue);
		}
		echo '</ul>';
		echo '</ul>';
	}

	function getJSTree($linkmenu) {
		global $idExposeTreeIndex;
		?>
		<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
	var myThemeXPBase = "<?php echo util_make_uri('/jscook/ThemeXP/'); ?>";
/* ]]> */</script>
<script type="text/javascript" src="<?php echo util_make_uri('/jscook/JSCookTree.js'); ?>"></script>
<script src="<?php echo util_make_uri('/jscook/ThemeXP/theme.js'); ?>" type="text/javascript"></script>

<div id="myMenuID" style="overflow:auto;"></div>

<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
	var myMenu =
		[
			['<span class="JSCookTreeFolderClosed"><i><img alt="" src="' + myThemeXPBase + 'folder1.gif" /></i></span><span id="ctItemID0" class="JSCookTreeFolderOpen"><i><img alt="" src="' + myThemeXPBase + 'folderopen1.gif" /></i></span>', '/', '<?php echo '?group_id='.$this->Group->getID().'&view='.$linkmenu ?>', '', '', <?php docman_recursive_display(0); ?>
			]
		];

	var treeIndex = ctDraw('myMenuID', myMenu, ctThemeXP1, 'ThemeXP', 0, 1);
	ctExposeTreeIndex(treeIndex, <?php echo $idExposeTreeIndex ?>);
	var openItem = ctGetSelectedItem(treeIndex);
	ctOpenFolder(openItem);
/* ]]> */</script>
		<?php
	}
}

?>

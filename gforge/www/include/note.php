<?php

/**
 * GForge Notepad Facility
 *
 * Copyright 2003 FUJITSU PRIME SOFTWARE TECHNOLOGIES LIMITED
 * http://www.pst.fujitsu.com/
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
/**
 * GForge Notepad Window
 * By Hidenari Miwa, FUJITSU PRIME SOFTWARE TECHNOLOGIES LIMITED, 09/2003
 */
require_once('html.php');

/**
 * editor_button_raw() - Open an Editor dialog window
 *
 * @param               str     Anchor string       
 * @param		form	Form name
 * @param               int     wrap
 * @param		int     rows
 * @param               int     cols
 */
function notepad_button_raw($str, $form, $wrap, $rows, $cols) {
       $anchor = '<a href="javascript:notepad_window_param(\''.$form.'\',\''.
		 $wrap.'\',' . $rows . ',' . $cols . ')">'. $str . '</a>';
       return $anchor;
}

function notepad_button($form) {
        $icon = html_image('ic/msg.png','12','14',array('alt'=>'Notepad'));
        return notepad_button_raw($icon, $form, "SOFT", "45", "80");
}

function notepad_anchor($str, $form) {
        return notepad_button_raw($str, $form, "SOFT", "45", "80");
}

function notepad_func() {
      $js = "\n" .
'<script language="JavaScript" type="text/javascript">'.
"\n<!--\n".
'function notepad_window_param(form, wrap, rows, cols) {'.
'   notepad_php = "/notepad.php";'.
'   notepad_url = notepad_php + "?form="+form + "&wrap=" + wrap +'.
'                "&rows=" + rows + "&cols=" + cols;'.
'     notepad_title = \'GForgeNotepad\';'.
'     notepad_height = 700;'.
'     notepad_width = 580;'.
'     notepad_winopt = "scrollbars=yes,resizable=yes,toolbar=no,height="+'.
'                       notepad_height + ",width=" +  notepad_width;'.
'    NotepadWin = window.open(notepad_url, notepad_title,'.
'                         notepad_winopt);'.
'}'.
"\n-->\n".
"</script>\n";
      return $js;
}

?>

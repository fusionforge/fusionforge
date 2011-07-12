<?php

/**
 * FusionForge Notepad Facility
 *
 * Copyright 2003 FUJITSU PRIME SOFTWARE TECHNOLOGIES LIMITED
 * http://www.pst.fujitsu.com/
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
/**
 * FusionForge Notepad Window
 * By Hidenari Miwa, FUJITSU PRIME SOFTWARE TECHNOLOGIES LIMITED, 09/2003
 */
require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';

/*
 *  form:       Input form name
 *  wrap:       Wrap attribute
 *  rows:       Rows
 *  cols:       Colums
 */

$form = getStringFromRequest('form');
$wrap = getStringFromRequest('wrap');
$rows = getIntFromRequest('rows');
$cols = getIntFromRequest('cols');

if (!$wrap) {
        $wrap = htmlspecialchars("SOFT");
}
if (!is_int($rows)) {
        $rows = 30;
}
if (!is_int($cols)) {
        $cols = 75;
}
$pform = '';
if ($form) {
        $pform = "opener.".$form;
}
?>

<html>
  <head>
    <title>FusionForge Notepad</title>
	<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
    function load_initial_value() {
        try {
            aform = <?php echo $pform ?>;
            document.forms[0].details.value = aform.value;

        } catch (e) {
        }
    }

    function set_value() {
         try {
             aform = eval("<?php echo $pform ?>");
             aform.value = document.forms[0].details.value;
         } catch (e) {
         }
    }

    function set_and_close() {
         set_value();
         window.close();
    }

	/* ]]> */</script>
  </head>
  <body onLoad="load_initial_value();" >
    <form name="form1" action="">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td><input type="button" name="ok" value="OK"
                     onClick="set_and_close()"/>
          <input type="reset" value="Clear" />
          <input type="button" value="Cancel"
                     onClick="window.close()" />
          </td>
        </tr>
        <tr>
          <td>
            <textarea name="details" ROWS="<?php echo $rows ?>"
                      COLS="<?php echo $cols ?>"
                      WRAP="<?php echo $wrap ?>"></textarea>
          </td>
        </tr>
        <tr>
          <td><input type="button" name="ok" value="OK"
                     onClick="set_and_close()"/>
          <input type="reset" value="Clear" />
          <input type="button" value="Cancel"
                     onClick="window.close()" />
          </td>
        </tr>
      </table>
    </form>
  </body>
</html>


<?php

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

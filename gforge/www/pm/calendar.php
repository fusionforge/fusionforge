<?php
/**
 *
 * Display a calendar.
 * This file displays various sorts of calendars.
 *
 * Copyright 2002 (c) GForge Development Team
 *
 * @version   $Id$
 *
 * @todo Remove hardcoded colours etc. and move into style sheet.
 * @todo some locales start the week with "Monday", and not "Sunday".
 */

require_once('pre.php');

// Some sanity checks first.
if (isset($year) && ($year < 1990 || $year > 2020)) {
	exit_error($Language->getText("calendar", "invalidyear"),
		   $Language->getText("calendar", "invalidyearexplain"));
}

if (isset($month) && ($month < 1 || $month > 12)) {
	exit_error($Language->getText("calendar", "invalidmonth"),
		   $Language->getText("calendar", "invalidmonthexplain"));
}

if (isset($day) && ($day < 1 || $day > 31)) {
	exit_error($Language->getText("calendar", "invalidday"),
		   $Language->getText("calendar", "invaliddayexplain"));
}

if (isset($year) && isset($month) && isset($day)) {
	if (!checkdate($month, $day, $year)) {
		exit_error($Language->getText("calendar", "invaliddate"),
			   $Language->getText("calendar", "invaliddateexplain", "$year-$month-$day"));
	}
}

if (isset($type) && $type != 'onemonth' && $type != 'threemonth' && $type != 'currentyear' && $type != 'comingyear') {
	exit_error($Language->getText("calendar", "invalidtype"),
		   $Language->getText("calendar", "invalidtypeexplain"));
}

$HTML->header(array(title=>$Language->getText("calendar", "title")));


// Fill in defaults
if (!isset($type)) {
	$type = 'threemonth';
}


$today = getdate(time());

if (!isset($year)) {
	$year = $today['year'];
}

if (!isset($month)) {
	$month = $today['mon'];
}

if (!isset($day)) {
	$day = $today['mday'];
}


$months = array(1 => 'january', 'february', 'march', 'april', 'may', 'june',
		'july', 'august', 'september', 'october', 'november', 'december');

/**
 * Display one month.
 * This displays one month.  m may be less than 0 and greater than 12: display_month
 * uses mktime() to readjust it and the year in such cases.
 *
 * @author    Ryan T. Sammartino <ryants at shaw dot ca>
 * @param     m  month
 * @param     y  year
 * @date      2002-12-29
 *
 */
function display_month($m, $y) {
	global $months, $today, $month, $day, $year, $Language;
	$dow = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

	$date = getdate(mktime(0, 0, 0, $m + 1, 0, $y));
	$days_in_month = $date['mday'];

	$date = getdate(mktime(0, 0, 0, $m, 1, $y));
	$first_dow = $date['wday'];

	$m = $date['mon'];
	$y = $date['year'];
?>
	<TABLE ALIGN="CENTER" CELLPADDING=1 CELLSPACING=1 BORDER=1 WIDTH=100%>
		<TR>
			<TH COLSPAN=7><?php echo $Language->getText("calendar", $months[$m]) . " $y"; ?></TH>
		</TR>
		<TR>
<?php
	reset($dow);
	while (list ($key, $val) = each ($dow)) {
		print "\t\t\t<TH WIDTH=14%>" . $Language->getText("calendar", $val) . "</TH>\n";
	}
?>
		</TR>
<?php
	$curr_dow = 0;
	$curr_date = 1;
	print "\t\t<TR>\n";
	while ($curr_dow != $first_dow) {
		print "\t\t\t<TD></TD>\n";
		$curr_dow++;
	}
	while ($curr_date <= $days_in_month) {
		while ($curr_dow < 7) {
			if ($curr_date <= $days_in_month) {
				$colour = "";
				if ($curr_date == $today['mday']
				    && $y == $today['year']
				    && $m == $today['mon']) {
					$colour = " BGCOLOR=\"RED\"";
				} elseif ($curr_date == $day
					  && $y == $year
					  && $m == $month) {
					$colour = " BGCOLOR=\"GRAY\"";
				}
				print "\t\t\t<TD" . $colour . ">$curr_date<BR><BR><BR></TD>\n";
			} else {
				print "\t\t\t<TD></TD>\n";
			}
			$curr_dow++;
			$curr_date++;
		}
		print "\t\t</TR>\n";
		$curr_dow = 0;
	}
?>

	</TABLE>

<?php
}

?>
	<FORM ACTION="/pm/calendar.php" METHOD="GET">
	<TABLE WIDTH=100%>
		<TR>
			<TD><?php echo $Language->getText("calendar", "view"); ?><BR>
				<SELECT NAME="type">
<?php
	print '
				<OPTION VALUE="onemonth"' . ($type == 'onemonth' ? ' SELECTED' : '') . '>'. $Language->getText("calendar", "onemonth") . '</OPTION>';
	print '
				<OPTION VALUE="threemonth"' . ($type == 'threemonth' ? ' SELECTED' : '') . '>'. $Language->getText("calendar", "threemonth") . '</OPTION>';
	print '
				<OPTION VALUE="currentyear"' . ($type == 'currentyear' ? ' SELECTED' : '') . '>' . $Language->getText("calendar", "currentyear") . '</OPTION>';
	print '
				<OPTION VALUE="comingyear"' . ($type == 'comingyear' ? ' SELECTED' : '') . '>' . $Language->getText("calendar", "comingyear") . '</OPTION>';
?>
				</SELECT>
			</TD>
			<TD><?php echo $Language->getText("calendar", "fordate"); ?><BR>
				<SELECT NAME="year">
<?php

	for ($i = 1990; $i < 2020; $i++) {
		print "\t\t\t\t<OPTION VALUE=\"$i\"" . ($year == $i ? ' SELECTED' : '') . ">$i</OPTION>\n";
	}
?>
				</SELECT>
				<SELECT NAME="month">
<?php
	for ($i = 1; $i <= 12; $i++) {
		print "\t\t\t\t<OPTION VALUE=\"$i\"" . ($month == $i ? ' SELECTED' : '') . ">" . $Language->getText("calendar", $months[$i]) . "</OPTION>\n";
	}
?>
				</SELECT>
				<SELECT NAME="day">
<?php
	for ($i = 1; $i <= 31; $i++) {
		print "\t\t\t\t<OPTION VALUE=\"$i\"" . ($day == $i ? ' SELECTED' : '') . ">$i</OPTION>\n";
	}
?>
				</SELECT>
			</TD>
			<TD>
				<INPUT TYPE="submit" VALUE="<?php echo $Language->getText("calendar", "update") ?>">
			</TD>
		</TR>
	</TABLE>
	<TABLE WIDTH=100%>
		<TR>
			<TD WIDTH=20px BGCOLOR="RED"></TD>
			<TD><?php echo $Language->getText("calendar", "todaysdate") ?></TD>
		</TR>
		<TR>
			<TD WIDTH=20px BGCOLOR="GRAY"</TD>
			<TD><?php echo $Language->getText("calendar", "selecteddate") ?></TD>
		</TR>
	</TABLE>
<?php

if ($type == 'onemonth') {
	display_month($month, $year);
} elseif ($type == 'threemonth') {
	display_month($month - 1, $year);
	print "\t<BR>\n\n";
	display_month($month, $year);
	print "\t<BR>\n\n";
	display_month($month + 1, $year);
} elseif ($type == 'currentyear') {
	for ($i = 1; $i <= 12; $i++) {
		display_month($i, $year);
		print "\t<BR>\n\n";
	}
} elseif ($type == 'comingyear') {
	for ($i = 0; $i < 12; $i++) {
		display_month($month + $i, $year);
		print "\t<BR>\n\n";
	}
}

$HTML->footer(array());

?>

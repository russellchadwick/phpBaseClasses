<?php
	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Date Select Popup');
	echo tab ();

	if (empty ($_GET['current_date'])) {
		$_GET['current_date'] = date ('Y-m-d');
	}

	$month = substr ($_GET['current_date'], 0, 7);
	$monthMktime = mktime (0, 0, 0, substr ($month, 5, 2), 1, substr ($month, 0, 4));

	echo '
		<table>
			<tr class="table_header">
				<td>&nbsp;</td>
				<td align="center"><a href="date_select_popup.php?name=' . $_GET['name'] . '&current_date=' . date ('Y-m', mktime (0, 0, 0, date ('m', $monthMktime) - 1, 1, date ('Y', $monthMktime))) . '-' . substr ($_GET['current_date'], 8) . '" class="table_header_link">&lt;&lt;</a></td>
				<td colspan="3" align="center">' . date ('F', $monthMktime) . '</td>
				<td align="center"><a href="date_select_popup.php?name=' . $_GET['name'] . '&current_date=' . date ('Y-m', mktime (0, 0, 0, date ('m', $monthMktime) + 1, 1, date ('Y', $monthMktime))) . '-' . substr ($_GET['current_date'], 8) . '" class="table_header_link">&gt;&gt;</a></td>
				<td>&nbsp;</td>
			</tr>
			<tr class="table_header">
				<td>&nbsp;</td>
				<td align="center"><a href="date_select_popup.php?name=' . $_GET['name'] . '&current_date=' . date ('Y-m', mktime (0, 0, 0, date ('m', $monthMktime), 1, date ('Y', $monthMktime) - 1)) . '-' . substr ($_GET['current_date'], 8) . '" class="table_header_link">&lt;&lt;</a></td>
				<td colspan="3" align="center">' . date ('Y', $monthMktime) . '</td>
				<td align="center"><a href="date_select_popup.php?name=' . $_GET['name'] . '&current_date=' . date ('Y-m', mktime (0, 0, 0, date ('m', $monthMktime), 1, date ('Y', $monthMktime) + 1)) . '-' . substr ($_GET['current_date'], 8) . '" class="table_header_link">&gt;&gt;</a></td>
				<td>&nbsp;</td>
			</tr>
			<tr class="table_even_row">
				<td>SUN</td>
				<td>MON</td>
				<td>TUE</td>
				<td>WED</td>
				<td>THU</td>
				<td>FRI</td>
				<td>SAT</td>
			</tr>
			<tr class="table_odd_row">';

	list ($dayOfWeek, $totalDays) = explode (',', date ('w,t', mktime (0, 0, 0, date ('m', $monthMktime), 1, date ('Y', $monthMktime))));

	for ($index = 1; $index <= $dayOfWeek; $index++) {
		echo '
				<td>&nbsp;</td>';
	}

	for ($index = 1; $index <= $totalDays; $index++) {
		$dayOfWeek++;

		if ($dayOfWeek > 7) {
			$dayOfWeek -= 7;

			echo '
			</tr>
			<tr class="table_odd_row">';
		}

		if ($_GET['current_date'] == ($month . '-' . $index)) {
			echo '
				<td class="table_even_row"><a href="javascript:void(0);" onClick="' . "setParentValueAndClose ('" . $_GET['name'] . "', '" . substr ($_GET['current_date'], 0, 8) . $index . "')" . '"><b>' . $index . '</b></a></td>';
		} else {
			echo '
				<td><a href="javascript:void(0);" onClick="' . "setParentValueAndClose ('" . $_GET['name'] . "', '" . substr ($_GET['current_date'], 0, 8) . $index . "')" . '">' . $index . '</a></td>';
		}
	}

	echo '
			</tr>
			<tr class="table_header">
				<td colspan="7">&nbsp;</td>
			</tr>
		</table>';

	echo $display->foot ();
	include 'end.php';
?>
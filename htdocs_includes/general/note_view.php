<?php
	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('View Note');
	echo tab ($_GET['tab']);

	if (!empty ($_GET['note_id'])) {
		$note = $db->doQuery1Row ("SELECT n.note_id, n.when_noted, n.title, n.note, u.first_name||' '||u.last_name AS name, n.security_object_reference_id, so.name AS security_object, so.foreign_table, so.foreign_id_column, so.foreign_name_column 
						FROM notes n, security.users u, security.security_objects so
						WHERE n.user_id = u.user_id 
							AND n.security_object_id = so.security_object_id 
							AND n.note_id = " . $_GET['note_id']);

		$soinfo = $db->doQuery1Row ("SELECT " . $note['foreign_name_column'] . " FROM " . $note['foreign_table'] . " WHERE " . $note['foreign_id_column'] . " = " . $note['security_object_reference_id']);
	}

	if ((isset ($note['title'])) && (!empty ($note['title']))) {
		echo '
<table align="center" cellspacing="3" cellpadding="3">
	<tr class="table_header">
		<td colspan="2" class="table_header_large" align="center">Note', ((isset ($_GET['header'])) && (!empty ($_GET['header']))) ? '<br>' . $_GET['header'] : '', '</td>
	</tr>
	<tr class="table_odd_row">
		<td>Task</td>
		<td>' . $note['title'] . '</td>
	</tr>
	<tr class="table_even_row">
		<td>Note Opened</td>
		<td>' . $note['when_noted'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td>Note</td>
		<td>' . $note['note'] . '</td>
	</tr>
	<tr class="table_header">
		<td colspan="2" class="table_header_large">&nbsp;</td>
	</tr>
</table>';
	} else {
		echo 'Could not find the specified note';
	}

	include 'end.php';
?>

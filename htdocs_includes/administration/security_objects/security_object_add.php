<?php
/*
 * $RCSfile
 *
 * phpBaseClasses - Foundation for any application in php
 * Copyright (C) 2002-2003 Russell Chadwick
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * @version $Revision: 1.7 $ $Date: 2004/01/15 00:13:16 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Security Objects -- Security Object Add');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Add this Security Object')) {
		$error = '';

		if (empty ($_POST['name'])) 
			$error .= 'Error: Name cannot be empty<br>';

		$security_object_count = $db->doQuery ("SELECT security_object_id FROM security.security_objects WHERE name = '" . $_POST['name'] . "'");

		if ($security_object_count > 0) 
			$error .= 'Error: Name already taken<br>';

		if (empty ($error)) {
			$foreign_data = explode ('/', $_POST['referenced_columns']);
			$insert_query = "INSERT INTO security.security_objects (name, foreign_table, foreign_id_column, foreign_name_column)
					VALUES ('" . $_POST['name'] . "', '" . $_POST['referenced_table'] . "', 
						'" . $foreign_data[0] . "', '" . $foreign_data[1] . "')";

			if ($db->doUpdate ($insert_query) == 0) {
				$db->commit ();
				echo 'Redirecting to <a href="security_objects_list.php">security_objects_list.php</a><meta http-equiv="refresh" content="0; URL=security_objects_list.php">';
			} else {
				$db->rollback ();
				debug ('Save failed, please notify administrator', 1);
			}
		}
	}

	$ol->set ('offsety', 20);

	$subselect = $display->makeTableColumnSelect ('referenced_table', 'referenced_columns', 'form');
	echo $subselect['javascript_declaration'];

	echo '
<br>
<center>
	<span class="large_bold_black">Security Object Add</span> 
	<a href="security_objects_list.php">[ Cancel ]</a>
</center>
<span class="error">' . $error . '</span>
<br><br>
<form name="form" method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">New Security Object Information</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Name' . $datainput->popupHelp ('Name Help', 'Help') . '</td>
		<td><input type="text" name="name" size="16" maxlength="50" value="' . $_POST['name'] . '"></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Referenced Table' . $datainput->popupHelp ('Referenced Table Help', 'Help') . '</td>
		<td>' . $subselect['select_html'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Referenced Table ID (Data Type) / <br>Name Column (Data Type)' . $datainput->popupHelp ('Referenced Columns Help', 'Help') . '</td>
		<td>' . $subselect['sub_select_html'] . '</td>
	</tr>
	<tr class="table_even_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Add this Security Object"></td>
	</tr>
	<tr class="table_header">
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>';

	echo $subselect['javascript'];

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>

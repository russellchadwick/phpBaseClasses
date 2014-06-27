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
	echo $display->head ('Administration -- Security Objects -- Security Object Details');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	$select_query = "SELECT name, foreign_table, foreign_id_column, foreign_name_column, display 
					FROM security.security_objects 
					WHERE security_object_id = " . $_GET['security_object_id'];

	$security_object = $db->doQuery1Row ($select_query);

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Save New Security Object Data')) {
		$error = '';

		if (empty ($_POST['name'])) 
			$error .= 'Error: Name cannot be empty<br>';

		if ($_POST['name'] != $security_object['name']) {
			$security_object_count = $db->doQuery ("SELECT security_object_id FROM security.security_objects WHERE name = '" . $_POST['name'] . "'");

			if ($security_object_count > 0) 
				$error .= 'Error: Name already taken<br>';
		}

		if (empty ($error)) {
			$foreign_data = explode ('/', $_POST['referenced_columns']);

			$update_query = "UPDATE security.security_objects SET name = '" . $_POST['name'] . "', 
						foreign_table = '" . $_POST['referenced_table'] . "', foreign_id_column = '" . $foreign_data[0] . "', 
						foreign_name_column = '" . $foreign_data[1] . "', display = '" . $_POST['display'] . "'
					WHERE security_object_id = " . $_GET['security_object_id'];

			if ($db->doUpdate ($update_query) == 0) {
				$db->commit ();
			} else {
				$db->rollback ();
				debug ('Save failed, please notify administrator', 1);
			}
		}

		$security_object = $db->doQuery1Row ($select_query);
	}

	if (empty($security_object)) {
		debug ('Invalid security object id', 1);
	}

	$ol->set ('offsety', 20);

	$subselect = $display->makeTableColumnSelect ('referenced_table', 'referenced_columns', 'form', $security_object['foreign_table'], $security_object['foreign_id_column'] . '/' . $security_object['foreign_name_column']);
	echo $subselect['javascript_declaration'];

	echo '
<br>
<center>
	<span class="large_bold_black">Security Object Details</span> 
	<a href="security_objects_list.php">[ Back to List ]</a>

</center>
<span class="error">' . $error . '</span>
<br><br>
<form method="post" name="form" action="' . $_SERVER['PHP_SELF'] . '?security_object_id=' . $_GET['security_object_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">Security Object Details for ' . $security_object['name'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Name' . $datainput->popupHelp ('Name Help', 'Help') . '</td>
		<td><input type="text" name="name" size="16" maxlength="50" value="' . $security_object['name'] . '"></td>
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
		<td align="right">Display' . $datainput->popupHelp ('Display Help', 'Help') . '</td>
		<td>' . $datainput->selectYesNo ('display', false, $security_object['display']) . '</td>
	</tr>
	<tr class="table_odd_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Save New Security Object Data"></td>
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

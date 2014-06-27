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
 * @version $Revision: 1.8 $ $Date: 2004/02/19 23:29:40 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Roles -- Roles List');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Add this Role')) {
		$error = '';

		if (empty ($_POST['title'])) 
			$error .= 'Error: Title cannot be empty<br>';

		$role_count = $db->doQuery ("SELECT role_id FROM security.roles WHERE title = '" . $_POST['title'] . "'");

		if ($role_count > 0) 
			$error .= 'Error: Title already taken<br>';

		if (empty ($error)) {
			$insert_query = "INSERT INTO security.roles (title) VALUES ('" . $_POST['title'] . "')";

			if ($db->doUpdate ($insert_query) == 0) {
				$db->commit ();
			} else {
				$db->rollback ();
				debug ('Save failed, please notify administrator', 1);
			}
		}
	} else if ((isset ($_GET['submit'])) && ($_GET['submit'] == 'Delete')) {
		$delete_query = "DELETE FROM security.roles WHERE role_id = " . $_GET['role_id'];

		if ($db->doUpdate ($delete_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	}

	$ol->set ('offsety', 20);

	echo '
<br>
<center>
	<span class="large_bold_black">Roles</span> 
</center>
<span class="error">' . $error . '</span>
<br><br>
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td>Title</td>
		<td>List of Users with this Role</td>
		<td>User Count</td>
		<td>&nbsp;</td>
	</tr>';

	$role_count = $db->doQuery ("SELECT r.role_id, r.title
				FROM security.roles r ORDER BY r.title", $roles);

	for ($index = 1; $index <= $role_count; $index++) {
		$users = $security->getRolesUsers ($roles[$index]['title']);

		$user_list = '';
		foreach ($users as $count=>$user) $user_list .= $user['username'] . ', ';
		$user_list = substr ($user_list, 0, -2);

		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $roles[$index]['title'] . '</td>
		<td>' . $user_list . '</td>
		<td>' . $user_count . '</td>
		<td><a href="roles_list.php?submit=Delete&role_id=' . $roles[$index]['role_id'] . '">Delete</a></td>
	</tr>';
	}

	echo '
	<tr class="table_header">
		<td colspan="4">&nbsp;</td>
	</tr>
</table>

<form name="form" method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">New Role Information</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Title' . $datainput->popupHelp ('Title Help', 'Help') . '</td>
		<td><input type="text" name="title" size="16" maxlength="50"></td>
	</tr>
	<tr class="table_even_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Add this Role"></td>
	</tr>
	<tr class="table_header">
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>';

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>

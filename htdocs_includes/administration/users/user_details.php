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
 * @version $Revision: 1.7 $ $Date: 2004/01/15 00:13:17 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Users -- User Details');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	$select_query = "SELECT username, first_name, last_name, email_address, last_login, 
					last_login_from, CASE enabled 
						WHEN 't' THEN 'Yes'
						ELSE 'No'
					END AS enabled
					FROM security.users 
					WHERE user_id = " . $_GET['user_id'];

	$user = $db->doQuery1Row ($select_query);

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Save New User Data')) {
		$error = '';

		if (empty ($_POST['username'])) 
			$error .= 'Error: Username cannot be empty<br>';
		if (empty ($_POST['first_name'])) 
			$error .= 'Error: First Name cannot be empty<br>';
		if (empty ($_POST['last_name'])) 
			$error .= 'Error: Last Name cannot be empty<br>';

		if (!empty($_POST['password'])) {
			if ($_POST['password'] != $_POST['password2']) 
				$error .= 'Error: Passwords dont match<br>';
			if (strlen ($_POST['password']) < 5) 
				$error .= 'Error: Password is too short<br>';
		}

		if ($_POST['username'] != $user['username']) {
			$user_count = $db->doQuery ("SELECT user_id FROM security.users WHERE username = '" . $_POST['username'] . "' AND user_id != " . $_GET['user_id']);

			if ($user_count > 0) 
				$error .= 'Error: Username already taken<br>';
		}

		if (($_POST['first_name'] != $user['first_name']) || ($_POST['last_name'] != $user['last_name'])) {
			$user_count = $db->doQuery ("SELECT user_id FROM security.users WHERE first_name = '" . $_POST['first_name'] . "' AND last_name = '" . $_POST['last_name'] . "' AND user_id != " . $_GET['user_id']);

			if ($user_count > 0) 
				$error .= 'Error: First name and Last name combined already taken<br>';
		}

		if (!$datainput->validateEmail ($_POST['email_address'])) 
			$error .= 'Error: Invalid Email Address<br>';

		if (empty ($error)) {
			$update_query = "UPDATE security.users SET username = '" . $_POST['username'] . "', ";

			if (!empty($_POST['password'])) 
				$update_query .= "password = '" . md5($_POST['password']) . "', ";

			$update_query .= "
						first_name = '" . $_POST['first_name'] . "', last_name = '" . $_POST['last_name'] . "', 
						email_address = '" . $_POST['email_address'] . "', enabled = '" . $_POST['enabled'] . "'
					WHERE user_id = " . $_GET['user_id'];

			if ($db->doUpdate ($update_query) == 0) {
				$db->commit ();
			} else {
				$db->rollback ();
				debug ('Save failed, please notify administrator', 1);
			}
		}

		$user = $db->doQuery1Row ($select_query);
	}

	if (empty($user)) {
		debug ('Invalid user id', 1);
	}

	$ol->set ('offsety', 20);

	echo '
<br>
<center>
	<span class="large_bold_black">User Details</span> 
	<a href="users_list.php">[ Back to List ]</a>
</center>';

	include ('user_details_header.php');

	echo '
<span class="error">' . $error . '</span>
<br><br>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '?user_id=' . $_GET['user_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">User Details for ' . $user['username'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Username' . $datainput->popupHelp ('Username Help', 'Help') . '</td>
		<td><input type="text" name="username" size="16" maxlength="50" value="' . $user['username'] . '"></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Password' . $datainput->popupHelp ('Password Help', 'Help') . '</td>
		<td><input type="password" name="password" size="16" maxlength="16" value=""></td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Password Again' . $datainput->popupHelp ('Password Again Help', 'Help') . '</td>
		<td><input type="password" name="password2" size="16" maxlength="16" value=""></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">First Name' . $datainput->popupHelp ('First Name Help', 'Help') . '</td>
		<td><input type="text" name="first_name" size="40" maxlength="50" value="' . $user['first_name'] . '"></td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Last Name' . $datainput->popupHelp ('Last Name Help', 'Help') . '</td>
		<td><input type="text" name="last_name" size="40" maxlength="50" value="' . $user['last_name'] . '"></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Email Address' . $datainput->popupHelp ('Email Address Help', 'Help') . '</td>
		<td><input type="text" name="email_address" size="50" maxlength="100" value="' . $user['email_address'] . '"></td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Last Login' . $datainput->popupHelp ('Last Login Help', 'Help') . '</td>
		<td>' . $user['last_login'] . '</td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Last Login From' . $datainput->popupHelp ('Last Login From Help', 'Help') . '</td>
		<td>' . $user['last_login_from'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Enabled' . $datainput->popupHelp ('Enabled Help', 'Help') . '</td>
		<td>' . $datainput->selectYesNo ('enabled', false, $user['enabled']) . '</td>
	</tr>
	<tr class="table_even_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Save New User Data"></td>
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

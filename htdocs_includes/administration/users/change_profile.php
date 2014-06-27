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
 * @version $Revision: 1.6 $ $Date: 2004/06/03 08:01:39 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';

	if ((isset ($_GET['action'])) && ($_GET['action'] == 'delete')) {
		$security->deletePreference ($_GET['preference']);
	}

	if (isset ($_POST['dateformat'])) {
		if ($_POST['dateformat'] == 'yyyy-mm-dd') {
			$security->deletePreference ('global.dateformat');
		} else {
			$security->setPreference ('global.dateformat', $_POST['dateformat']);
		}
	}

	echo $display->head ('Administration -- Users -- Change Profile');
	echo menu ();

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Save Profile')) {
		$error = '';

		if (!empty($_POST['password'])) {
			if ($_POST['password'] != $_POST['password2']) 
				$error .= 'Error: Passwords dont match<br>';
			if (strlen ($_POST['password']) < 5) 
				$error .= 'Error: Password is too short<br>';
		}

		if (!$datainput->validateEmail ($_POST['email_address'])) 
			$error .= 'Error: Invalid Email Address<br>';

		if (empty ($error)) {
			$update_query = "UPDATE security.users SET ";

			if (!empty($_POST['password'])) 
				$update_query .= "password = '" . md5($_POST['password']) . "', ";

			$update_query .= "
						email_address = '" . $_POST['email_address'] . "' 
					WHERE user_id = " . $_SESSION['user_id'];

			if ($db->doUpdate ($update_query) == 0) {
				$db->commit ();
			} else {
				$db->rollback ();
				debug ('Save failed, please notify administrator', 1);
			}
		}
	}

	$ol->set ('offsety', 20);

	$user = $db->doQuery1Row ("SELECT username, email_address
					FROM security.users 
					WHERE user_id = " . $_SESSION['user_id']);

	$preferences = $security->getUsersPreferences ($_SESSION['user_id']);

	echo '
<br>
<span class="error">' . $error . '</span>
<br><br>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '?user_id=' . $_SESSION['user_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">Profile for ' . $user['username'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Email Address' . $datainput->popupHelp ('Email Address Help', 'Enter email address where the website can reach you') . '</td>
		<td><input type="text" name="email_address" size="50" maxlength="100" value="' . $user['email_address'] . '"></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">New Password' . $datainput->popupHelp ('Password Help', 'Enter your new password') . '</td>
		<td><input type="password" name="password" size="16" maxlength="16" value=""></td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Password Again' . $datainput->popupHelp ('Password Again Help', 'Enter your new password again') . '</td>
		<td><input type="password" name="password2" size="16" maxlength="16" value=""></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Date Format' . $datainput->popupHelp ('Date Format Help', 'Format of all dates on the website') . '</td>
		<td>
			<select name="dateformat">
				<option value="yyyy-mm-dd"', ((!isset ($preferences['global.dateformat'])) || ($preferences['global.dateformat'] == 'yyyy-mm-dd')) ? ' selected' : '', '>yyyy-mm-dd</option>
				<option value="mm/dd/yyyy"', ($preferences['global.dateformat'] == 'mm/dd/yyyy') ? ' selected' : '', '>mm/dd/yyyy</option>
				<option value="mm/dd/yy"', ($preferences['global.dateformat'] == 'mm/dd/yy') ? ' selected' : '', '>mm/dd/yy</option>
			</select>
		</td>
	</tr>
	<tr class="table_odd_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Save Profile"></td>
	</tr>
	<tr class="table_header">
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>

<br>

<form method="post" action="' . $_SERVER['PHP_SELF'] . '?user_id=' . $_SESSION['user_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="3" class="medium_bold_black" align="center">Preferences for ' . $user['username'] . '</td>
	</tr>
	<tr class="table_header">
		<td>Preference Name</td>
		<td>Value</td>
		<td>&nbsp;</td>
	</tr>';

	foreach ($preferences as $key => $value) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $key . '</td>
		<td>' . str_replace ('    ', '&nbsp;&nbsp;&nbsp;', nl2br (print_r ($value, true))) . '</td>
		<td><a href="' . $_SERVER['PHP_SELF'] . '?user_id=' . $_SESSION['user_id'] . '&action=delete&preference=' . $key . '">Delete</a></td>
	</tr>';
	}

	echo '
	<tr class="table_header">
		<td colspan="3">&nbsp;</td>
	</tr>
</table>
</form>';

	echo menu_end ();
	include 'end.php';
?>

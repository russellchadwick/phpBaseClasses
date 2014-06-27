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
	echo $display->head ('Administration -- Users -- User Add');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Add this User')) {
		$user_id = $security->addUser ($_POST['username'], $_POST['password'], $_POST['password2'], 
						$_POST['first_name'], $_POST['last_name'], $_POST['email_address'], $error);

		if ($user_id > 0) {
			echo 'Redirecting to <a href="user_details.php?user_id=' . $user_id . '">user_details.php?user_id=' . $user_id . '</a><meta http-equiv="refresh" content="0; URL=user_details.php?user_id=' . $user_id . '">';
		}
	}

	$ol->set ('offsety', 20);

	echo '
<br>
<center>
	<span class="large_bold_black">User Add</span> 
	<a href="users_list.php">[ Cancel ]</a>
</center>
<span class="error">' . $error . '</span>
<br><br>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">New User Information</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Username' . $datainput->popupHelp ('Username Help', 'Help') . '</td>
		<td><input type="text" name="username" size="16" maxlength="50" value="' . $_POST['username'] . '"></td>
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
		<td><input type="text" name="first_name" size="40" maxlength="50" value="' . $_POST['first_name'] . '"></td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Last Name' . $datainput->popupHelp ('Last Name Help', 'Help') . '</td>
		<td><input type="text" name="last_name" size="40" maxlength="50" value="' . $_POST['last_name'] . '"></td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Email Address' . $datainput->popupHelp ('Email Address Help', 'Help') . '</td>
		<td><input type="text" name="email_address" size="50" maxlength="100" value="' . $_POST['email_address'] . '"></td>
	</tr>
	<tr class="table_odd_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Add this User"></td>
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

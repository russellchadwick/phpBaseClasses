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
 * @version $Revision: 1.6 $ $Date: 2004/02/19 23:29:43 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Users -- Users List');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	echo '
<br>
<center>
	<span class="large_bold_black">Users</span> 
	<a href="user_add.php">[ Add User ]</a>
</center>
<br><br>
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td>Username</td>
		<td>Full Name</td>
		<td>Email Address</td>
		<td>Last Login Date</td>
		<td>Last Login From</td>
		<td>Enabled</td>
	</tr>';

	$user_count = $db->doQuery ("SELECT user_id, first_name||' '||last_name AS name, username, 
				email_address, last_login, last_login_from, enabled
			FROM security.users ORDER BY username", $users);

	for ($index = 1; $index <= $user_count; $index++) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td><a href="user_details.php?user_id=' . $users[$index]['user_id'] . '">' . $users[$index]['username'] . '</a></td>
		<td>' . $users[$index]['name'] . '</td>
		<td><a href="mailto:' . $users[$index]['email_address'] . '">' . $users[$index]['email_address'] . '</a></td>
		<td>' . $users[$index]['last_login'] . '</td>
		<td>' . $users[$index]['last_login_from'] . '</td>
		<td>' . $display->displayTF ($users[$index]['enabled']) . '</td>
	</tr>';
	}

	echo '
	<tr class="table_header">
		<td colspan="6">&nbsp;</td>
	</tr>
</table>';

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>








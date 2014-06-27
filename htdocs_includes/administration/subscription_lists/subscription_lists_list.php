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
 * @version $Revision: 1.5 $ $Date: 2003/09/04 18:53:52 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Subscription Lists -- Subscription Lists List');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	if (isset ($_GET['action'])) {
		if ($_GET['action'] == 'Add') {
			$update_success = $db->doUpdate ("INSERT INTO security.subscription_list_members (
						subscription_list_id, user_id 
					) VALUES (
						" . $_GET['id'] . ", " . $_GET['user_id'] . "
					)");
		} else if ($_GET['action'] == 'remove') {
			$update_success = $db->doUpdate ("DELETE FROM security.subscription_list_members 
						WHERE subscription_list_id = " . $_GET['id'] . "
						AND user_id = " . $_GET['user_id']);
		} else if ($_GET['action'] == 'Add New List') {
			$update_success = $db->doUpdate ("INSERT INTO security.subscription_lists (
						name
					) VALUES (
						'" . $_GET['name'] . "'
					)");
			$_GET['id'] = $db->getAutoID ('security.subscription_lists_subscription_list_id_seq');
		}

		$_GET['action'] = 'edit';

		if ($update_success == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
		}
	}

	echo '
<br>
<center>
	<span class="large_bold_black">Subscription Lists</span> 
</center>
<br><br>
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td>Subscription List</td>
		<td># of Members</td>
		<td>Add / Remove</td>
	</tr>';

	$subscription_list_count = $db->doQuery ("SELECT sl.subscription_list_id, sl.name, 
				(SELECT count (slm.user_id) 
					FROM security.subscription_list_members slm 
					WHERE slm.subscription_list_id = sl.subscription_list_id
				) AS count 
			FROM security.subscription_lists sl ORDER BY sl.name", $subscription_lists);

	for ($index = 1; $index <= $subscription_list_count; $index++) {
		if ((isset ($_GET['action'])) && ($_GET['action'] == 'edit') && ($_GET['id'] == $subscription_lists[$index]['subscription_list_id'])) {
			echo '
	<form method="get" action="subscription_lists_list.php">
	<tr class="', ($index % 2 == 0) ? 'table_even_row' : 'table_odd_row', '">
		<td>' . $subscription_lists[$index]['name'] . '</td>
		<td>' . $subscription_lists[$index]['count'] . '</td>
		<td>&nbsp;</td>
	</tr>';

			$subscription_list_member_count = $db->doQuery ("SELECT u.user_id, u.first_name||' '||u.last_name AS name, u.email_address 
							FROM security.subscription_list_members slm, 
								security.users u 
							WHERE slm.subscription_list_id = " . $subscription_lists[$index]['subscription_list_id'] . "
								AND slm.user_id = u.user_id
							ORDER BY u.first_name||' '||u.last_name", $subscription_list_members);

			for ($index2 = 1; $index2 <= $subscription_list_member_count; $index2++) {
				echo '
	<tr class="', ($index % 2 == 0) ? 'table_even_row' : 'table_odd_row', '">
		<td>&nbsp;</td>
		<td>"' . $subscription_list_members[$index2]['name'] . '" &lt;' . $subscription_list_members[$index2]['email_address'] . '&gt;</td>
		<td><a href="subscription_lists_list.php?action=remove&id=' . $subscription_lists[$index]['subscription_list_id'] . '&user_id=' . $subscription_list_members[$index2]['user_id'] . '">Remove</a></td>
	</tr>';
			}

			echo '
	<tr class="', ($index % 2 == 0) ? 'table_even_row' : 'table_odd_row', '">
		<td>&nbsp;</td>
		<td>' . $datainput->selectDBSimple ('user_id', 'security.users', 'user_id', "first_name||' '||last_name", '') . '
			<input type="hidden" name="id" value="' . $subscription_lists[$index]['subscription_list_id'] . '">
			<input type="submit" name="action" value="Add"></td>
		<td>&nbsp;</td>
	</tr>
	</form>';
		} else {	
			echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $subscription_lists[$index]['name'] . '</td>
		<td>' . $subscription_lists[$index]['count'] . '</td>
		<td><a href="subscription_lists_list.php?action=edit&id=' . $subscription_lists[$index]['subscription_list_id'] . '">Edit</a></td>
	</tr>';
		}
	}

	echo '
	<form method="get" action="subscription_lists_list.php">
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td><input type="text" name="name" size="20" maxlength="100"></td>
		<td><input type="submit" name="action" value="Add New List"></td>
		<td>&nbsp;</td>
	</tr>
	</form>
	<tr class="table_header">
		<td colspan="3">&nbsp;</td>
	</tr>
</table>';

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>
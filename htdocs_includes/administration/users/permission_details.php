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
	echo $display->head ('Administration -- Users -- Permissions Details');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Add New Permission')) {
		$security_object = $db->doQuery1Row ("SELECT security_object_id 
					FROM security.security_objects 
					WHERE name = '" . $_POST['security_object_id'] . "'");
		$_POST['security_object_id'] = $security_object['security_object_id'];

		$insert_query = "INSERT INTO security.users2permissions (user_id, permission_type_id, security_object_id, security_object_reference_id)
				VALUES (" . $_GET['user_id'] . ", " . $_POST['permission_type_id'] . ", 
					" . $_POST['security_object_id'] . ", " . $db->orNull ($_POST['security_object_reference_id']) . ")";

		if ($db->doUpdate ($insert_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	} else if ((isset ($_GET['submit'])) && ($_GET['submit'] == 'Delete')) {
		$delete_query = "DELETE FROM security.users2permissions WHERE users2permissions_id = " . $_GET['users2permissions_id'];

		if ($db->doUpdate ($delete_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	}

	$subselect = $display->makeSecurityObjectSelect ('security_object_id', 'security_object_reference_id', 'addpermissionform', '', '', false, true);
	echo $subselect['javascript_declaration'];

	$user = $db->doQuery1Row ("SELECT username 
					FROM security.users 
					WHERE user_id = " . $_GET['user_id']);

	$permission_count = $db->doQuery ("SELECT u2p.users2permissions_id, pt.type, u2p.security_object_id, u2p.security_object_reference_id 
					FROM security.permission_types pt, security.users2permissions u2p 
					WHERE u2p.user_id = " . $_GET['user_id'] . "
						AND pt.permission_type_id = u2p.permission_type_id
					ORDER BY u2p.security_object_id, u2p.security_object_reference_id, pt.type", $permissions);

	$ol->set ('offsety', 20);

	echo '
<br>
<center>
	<span class="large_bold_black">Permission Details</span> 
	<a href="users_list.php">[ Back to List ]</a>
</center>';

	include ('user_details_header.php');

	echo '
<span class="error">' . $error . '</span>
<br><br>
<form name="addpermissionform" method="post" action="' . $_SERVER['PHP_SELF'] . '?user_id=' . $_GET['user_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">Add Permission for ' . $user['username'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Permission Type' . $datainput->popupHelp ('Permission Help', 'Help') . '</td>
		<td>' . $datainput->selectDB ('permission_type_id', 1, 'security.permission_types', 'permission_type_id', false) . '</td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Security Object ' . $datainput->popupHelp ('Security Object Help', 'Help') . '</td>
		<td>' . $subselect['select_html'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Security Object Refers To (Optional)' . $datainput->popupHelp ('Security Object Refers To Help', 'Help') . '</td>
		<td>' . $subselect['sub_select_html'] . '</td>
	</tr>
	<tr class="table_even_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Add New Permission"></td>
	</tr>
	<tr class="table_header">
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>

<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="4" class="medium_bold_black" align="center">Current Permissions for ' . $user['username'] . '</td>
	</tr>
	<tr class="table_header">
		<td>Permission Type</td>
		<td>Security Object</td>
		<td>Referenced Object</td>
		<td>&nbsp;</td>
	</tr>';

	for ($index = 1; $index <= $permission_count; $index++) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $permissions[$index]['type'] . '</td>
		<td>' . $security->getSecurityObjectName ($permissions[$index]['security_object_id'], true) . '</td>
		<td>' . $security->getReferencedSecurityObject ($permissions[$index]['security_object_id'], $permissions[$index]['security_object_reference_id']) . '</td>
		<td><a href="permission_details.php?user_id=' . $_GET['user_id'] . '&submit=Delete&users2permissions_id=' . $permissions[$index]['users2permissions_id'] . '">Revoke</a>
	</tr>';
	}

	echo '
	<tr class="table_header">
		<td colspan="4">&nbsp;</td>
	</tr>
</table>
';

	echo $subselect['javascript'];

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>

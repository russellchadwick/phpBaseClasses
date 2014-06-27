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
 * @version $Revision: 1.7 $ $Date: 2004/02/19 23:28:40 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Security Inheritances -- Permission Inheritance Add');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Add New Permission Inheritance')) {
		if ($_POST['security_object_id'] == 'All') {
			$_POST['security_object_id'] = '';
		} else {
			$security_object = $db->doQuery1Row ("SELECT security_object_id 
						FROM security.security_objects 
						WHERE name = '" . $_POST['security_object_id'] . "'");
			$_POST['security_object_id'] = $security_object['security_object_id'];
		}

		$insert_query = "INSERT INTO security.roles2permissions_inheritances (role_id, permission_type_id, security_object_id, security_object_reference_id)
				VALUES (" . $_GET['role_id'] . ", " . $_POST['permission_type_id'] . ", 
					" . $db->orNull ($_POST['security_object_id']) . ", " . $db->orNull ($_POST['security_object_reference_id']) . ")";

		if ($db->doUpdate ($insert_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	} else if ((isset ($_GET['submit'])) && ($_GET['submit'] == 'Delete')) {
		$delete_query = "DELETE FROM security.roles2permissions_inheritances WHERE roles2permissions_inheritance_id = " . $_GET['roles2permissions_inheritance_id'];

		if ($db->doUpdate ($delete_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	}

	$subselect = $display->makeSecurityObjectSelect ('security_object_id', 'security_object_reference_id', 'addpermissionform', '', '', false, true);
	echo $subselect['javascript_declaration'];

	$role = $db->doQuery1Row ("SELECT title 
					FROM security.roles 
					WHERE role_id = " . $_GET['role_id']);

	$permission_count = $db->doQuery ("SELECT r2pi.roles2permissions_inheritance_id, pt.type, 
								r2pi.security_object_id, r2pi.security_object_reference_id 
							FROM security.roles2permissions_inheritances r2pi, security.permission_types pt
							WHERE r2pi.permission_type_id = pt.permission_type_id 
								AND r2pi.role_id = " . $_GET['role_id'] . "
							ORDER BY pt.type", $permissions);

	$ol->set ('offsety', 20);

	echo '
<br>
<center>
	<span class="large_bold_black">Permission Inheritances</span> 
	<a href="role_inheritances_list.php">[ Back to List ]</a>
</center>

<span class="error">' . $error . '</span>
<br><br>
<form name="addpermissionform" method="post" action="' . $_SERVER['PHP_SELF'] . '?role_id=' . $_GET['role_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">Add Permission Inheritance for ' . $role['title'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Permission Type' . $datainput->popupHelp ('Permission Type Help', 'Help') . '</td>
		<td>' . $datainput->selectDB ('permission_type_id', '', 'security.permission_types', 'permission_type_id', false) . '</td>
	</tr>
	<tr class="table_even_row">
		<td align="right">Security Object (Optional)' . $datainput->popupHelp ('Security Object Help', 'Help') . '</td>
		<td>' . $subselect['select_html'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Security Object Refers To (Optional)' . $datainput->popupHelp ('Security Object Refers To Help', 'Help') . '</td>
		<td>' . $subselect['sub_select_html'] . '</td>
	</tr>
	<tr class="table_even_row">
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Add New Permission Inheritance"></td>
	</tr>
	<tr class="table_header">
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>

<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="4" class="medium_bold_black" align="center">Current Inherited Permissions for ' . $role['title'] . '</td>
	</tr>
	<tr class="table_header">
		<td>Permission Types</td>
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
		<td><a href="roles2permissions_inheritance_add.php?role_id=' . $_GET['role_id'] . '&submit=Delete&roles2permissions_inheritance_id=' . $permissions[$index]['roles2permissions_inheritance_id'] . '">Revoke</a>
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
	include 'end.php';
?>

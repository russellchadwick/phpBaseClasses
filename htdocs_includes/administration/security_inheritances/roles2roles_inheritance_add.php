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
 * @version $Revision: 1.8 $ $Date: 2004/02/19 23:28:40 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Security Inheritances -- Role Inheritance Add');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Add New Role Inheritance')) {
		$security_object = $db->doQuery1Row ("SELECT security_object_id 
					FROM security.security_objects 
					WHERE name = '" . $_POST['security_object_id'] . "'");
		$_POST['security_object_id'] = $security_object['security_object_id'];


		$insert_query = "INSERT INTO security.roles2roles_inheritances (role_id, inherited_role_id, security_object_id, security_object_reference_id)
				VALUES (" . $_GET['role_id'] . ", " . $_POST['inherited_role_id'] . ", 
					" . $db->orNull ($_POST['security_object_id']) . ", " . $db->orNull ($_POST['security_object_reference_id']) . ")";

		if ($db->doUpdate ($insert_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	} else if ((isset ($_GET['submit'])) && ($_GET['submit'] == 'Delete')) {
		$delete_query = "DELETE FROM security.roles2roles_inheritances WHERE roles2roles_inheritance_id = " . $_GET['roles2roles_inheritance_id'];

		if ($db->doUpdate ($delete_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
			debug ('Save failed, please notify administrator', 1);
		}
	}

	$subselect = $display->makeSecurityObjectSelect ('security_object_id', 'security_object_reference_id', 'addroleform', '', '', true, true);
	echo $subselect['javascript_declaration'];

	$role = $db->doQuery1Row ("SELECT title
					FROM security.roles 
					WHERE role_id = " . $_GET['role_id']);

	$role_count = $db->doQuery ("SELECT r2ri.roles2roles_inheritance_id, r.title, 
								r2ri.security_object_id, r2ri.security_object_reference_id 
							FROM security.roles2roles_inheritances r2ri, security.roles r
							WHERE r2ri.inherited_role_id = r.role_id 
								AND r2ri.role_id = " . $_GET['role_id'] . "
							ORDER BY r.title", $roles);

	$ol->set ('offsety', 20);

	echo '
<br>
<center>
	<span class="large_bold_black">Role Inheritances</span> 
	<a href="role_inheritances_list.php">[ Back to List ]</a>
</center>

<span class="error">' . $error . '</span>
<br><br>
<form name="addroleform" method="post" action="' . $_SERVER['PHP_SELF'] . '?role_id=' . $_GET['role_id'] . '">
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="2" class="medium_bold_black" align="center">Add Role Inheritance for ' . $role['title'] . '</td>
	</tr>
	<tr class="table_odd_row">
		<td align="right">Inherited Role' . $datainput->popupHelp ('Inherited Role Help', 'Help') . '</td>
		<td>' . $datainput->selectDB ('inherited_role_id', '', 'security.roles', 'role_id', false) . '</td>
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
		<td><input type="submit" name="submit" value="Add New Role Inheritance"></td>
	</tr>
	<tr class="table_header">
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>

<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="4" class="medium_bold_black" align="center">Current Inherited Roles for ' . $role['title'] . '</td>
	</tr>
	<tr class="table_header">
		<td>Role</td>
		<td>Security Object</td>
		<td>Referenced Object</td>
		<td>&nbsp;</td>
	</tr>';

	for ($index = 1; $index <= $role_count; $index++) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $roles[$index]['title'] . '</td>
		<td>' . $security->getSecurityObjectName ($roles[$index]['security_object_id'], true) . '</td>
		<td>' . $security->getReferencedSecurityObject ($roles[$index]['security_object_id'], $roles[$index]['security_object_reference_id']) . '</td>
		<td><a href="roles2roles_inheritance_add.php?role_id=' . $_GET['role_id'] . '&submit=Delete&roles2roles_inheritance_id=' . $roles[$index]['roles2roles_inheritance_id'] . '">Revoke</a>
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

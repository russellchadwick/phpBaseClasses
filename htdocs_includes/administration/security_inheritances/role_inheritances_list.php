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
 * @version $Revision: 1.6 $ $Date: 2004/02/19 23:29:41 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Security Inheritances -- Role Inheritances List');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	echo '
<br>
<center>
	<span class="large_bold_black">Role Inheritances</span> 
</center>
<br><br>
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td>Role</td>
		<td>Inherits Role</td>
		<td>Inherits Permission</td>
		<td>Admin Links</td>
	</tr>';

	$role_count = $db->doQuery ("SELECT role_id, title 
			FROM security.roles ORDER BY title", $roles);

	for ($index = 1; $index <= $role_count; $index++) {
		$inheritance_count = $db->doQuery ("SELECT r2ri.roles2roles_inheritance_id, r.title, 
								r2ri.security_object_id, r2ri.security_object_reference_id 
							FROM security.roles2roles_inheritances r2ri, security.roles r
							WHERE r2ri.inherited_role_id = r.role_id 
								AND r2ri.role_id = " . $roles[$index]['role_id'] . "
							ORDER BY r.title", $inheritances);

		$role_inheritances = '';
		foreach ($inheritances as $inheritance) {
			$role_inheritances .= $inheritance['title'] . $security->getReferencedSecurityObject ($inheritance['security_object_id'], $inheritance['security_object_reference_id'], true) . '<br>';
		}

		$inheritance_count = $db->doQuery ("SELECT r2pi.roles2permissions_inheritance_id, pt.type, 
									r2pi.security_object_id, r2pi.security_object_reference_id 
								FROM security.roles2permissions_inheritances r2pi, security.permission_types pt
								WHERE r2pi.permission_type_id = pt.permission_type_id 
									AND r2pi.role_id = " . $roles[$index]['role_id'] . "
								ORDER BY pt.type", $inheritances);

		$permission_inheritances = '';
		foreach ($inheritances as $inheritance) {
			$permission_inheritances .= $inheritance['type'] . $security->getReferencedSecurityObject ($inheritance['security_object_id'], $inheritance['security_object_reference_id'], true) . '<br>';
		}

		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $roles[$index]['title'] . '</a></td>
		<td>' . $role_inheritances . '</td>
		<td>' . $permission_inheritances . '</td>
		<td><a href="roles2roles_inheritance_add.php?role_id=' . $roles[$index]['role_id'] . '">[ Role Inheritance ]</a><br>
			<a href="roles2permissions_inheritance_add.php?role_id=' . $roles[$index]['role_id'] . '">[ Permission Inheritance ]</a></td>
	</tr>';
	}

	echo '
	<tr class="table_header">
		<td colspan="4">&nbsp;</td>
	</tr>
</table>';

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>

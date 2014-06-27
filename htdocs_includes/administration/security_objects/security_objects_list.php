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
 * @version $Revision: 1.6 $ $Date: 2004/02/19 23:29:42 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Security Objects -- Security Objects List');
	echo menu ();

	if (!$security->checkPermissions ('Uber Admin')) {
		die ('You must be Uber Admin to access this page');
	}

	echo '
<br>
<center>
	<span class="large_bold_black">Security Objects</span> 
	<a href="security_object_add.php">[ Add Security Object ]</a>
</center>
<br><br>
<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td>Security Object Name</td>
		<td>Referenced Table</td>
		<td>Referenced Table ID Column</td>
		<td>Referenced Table Name Column</td>
		<td>Display</td>
	</tr>';

	$security_object_count = $db->doQuery ("SELECT security_object_id, name, 
				foreign_table, foreign_id_column, foreign_name_column, 
				display 
			FROM security.security_objects ORDER BY name", $security_objects);

	for ($index = 1; $index <= $security_object_count; $index++) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td><a href="security_object_details.php?security_object_id=' . $security_objects[$index]['security_object_id'] . '">' . $security_objects[$index]['name'] . '</a></td>
		<td>' . $security_objects[$index]['foreign_table'] . '</td>
		<td>' . $security_objects[$index]['foreign_id_column'] . '</td>
		<td>' . $security_objects[$index]['foreign_name_column'] . '</td>
		<td>' . $display->displayTF ($security_objects[$index]['display']) . '</td>
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

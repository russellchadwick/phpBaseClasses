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
 * @version $Revision: 1.5 $ $Date: 2003/09/04 18:53:54 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Users -- Preferences Details');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	$user = $db->doQuery1Row ("SELECT username 
					FROM security.users 
					WHERE user_id = " . $_GET['user_id']);

	$preference_count = $db->doQuery ("SELECT up.user_preference_id, up.key, up.value 
					FROM security.user_preferences up 
					WHERE up.user_id = " . $_GET['user_id'] . "
					ORDER BY up.key", $preferences);

	echo '
<br>
<center>
	<span class="large_bold_black">Preference Details</span> 
	<a href="users_list.php">[ Back to List ]</a>
</center>';

	include ('user_details_header.php');

	echo '
<br><br>

<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="4" class="medium_bold_black" align="center">Current Preferences for ' . $user['username'] . '</td>
	</tr>
	<tr class="table_header">
		<td>Preference Name</td>
		<td>Value</td>
	</tr>';

	for ($index = 1; $index <= $preference_count; $index++) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $preferences[$index]['key'] . '</td>
		<td>' . $preferences[$index]['value'] . '</td>
	</tr>';
	}

	echo '
	<tr class="table_header">
		<td colspan="4">&nbsp;</td>
	</tr>
</table>
';

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>

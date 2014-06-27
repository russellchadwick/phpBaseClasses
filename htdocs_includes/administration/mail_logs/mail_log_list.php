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
 * @version $Revision: 1.7 $ $Date: 2004/02/19 23:28:38 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Mail -- Mail Log List');
	echo menu ();

	if (!$security->checkPermissions ('Admin')) {
		die ('You must be Admin to access this page');
	}

	$select_query = "SELECT u.username, u.first_name||' '||u.last_name AS full_name, 
						ml.when_mailed, ml.template
					FROM security.mail_logs ml, security.users u 
					WHERE ml.user_id = u.user_id";

	if ((isset($_GET['user_id'])) && (!empty($_GET['user_id']))) {
		$select_query .= "
						AND ml.user_id = " . $_GET['user_id'];
	}

	$select_query .= "
					ORDER BY ml.when_mailed DESC";

	$mail_log_count = $db->doQuery ($select_query, $mails);

	echo '
<br>
<center>
	<span class="large_bold_black">Mail Log List</span> 
</center>';

	echo '
<br>
<form method="get" action="' . $_SERVER['PHP_SELF'] . '">
<center>
	Filter by User: ' . $datainput->selectDB ('user_id', $_GET['user_id'], 'security.users', 'user_id', true) . '
	<input type="submit" name="submit" value="Filter">
</center>
</form>

<table width="80%" align="center" cellpadding="2" cellspacing="2">
	<tr class="table_header">
		<td colspan="4" class="medium_bold_black" align="center">Sorted by When Mailed, Most recent First</td>
	</tr>
	<tr class="table_header">
		<td>User Mailed</td>
		<td>When Mailed</td>
		<td>What Mailed</td>
	</tr>';

	for ($index = 1; $index <= $mail_log_count; $index++) {
		echo '
	<tr class="table_', ($index % 2 == 0) ? 'even' : 'odd', '_row">
		<td>' . $mails[$index]['username'] . ' (' . $mails[$index]['full_name'] . ')</td>
		<td>' . $mails[$index]['when_mailed'] . '</td>
		<td>' . $mails[$index]['template'] . '</td>
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

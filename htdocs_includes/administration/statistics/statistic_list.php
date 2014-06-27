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
 * @version $Revision: 1.1 $ $Date: 2004/06/03 07:59:38 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Statistics -- Statistics List');
	echo menu ();

	if (!isset ($_GET['interval'])) {
		$_GET['interval'] = 'Day';
	}

	echo '
	<form method="get" action="statistic_list.php">
		Interval
		<select name="interval">
			<option value="Hour"', ($_GET['interval'] == 'Hour') ? ' selected' : '', '>Hour</option>
			<option value="Day"', ($_GET['interval'] == 'Day') ? ' selected' : '', '>Day</option>
			<option value="Month"', ($_GET['interval'] == 'Month') ? ' selected' : '', '>Month</option>
		</select>
		<input type="submit" name="submit" value="Change">
	</form>';

	echo '<img src="statistic_graph.php?type=Login&interval=' . $_GET['interval'] . '"><br><br>';

	echo menu_end ();
	echo $display->foot ();
	include 'end.php';
?>
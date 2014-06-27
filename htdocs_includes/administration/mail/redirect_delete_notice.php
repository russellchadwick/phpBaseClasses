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
 * @version $Revision: 1.2 $ $Date: 2003/09/04 18:53:46 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';

	$notification = $db->doQuery1Row ("SELECT n.link 
					FROM security.notifications n 
					WHERE n.notification_id = " . $_GET['notification_id']);

	if (empty ($notification)) {
		echo $display->minimalHead ('Administration -- Mail -- Redirect Delete Notice');

		echo "Invalid notification";

		echo $display->minimalFoot ();
	} else {
		header ('Location: ' . $notification['link']);

		echo '<meta http-equiv="refresh" content="0; URL=' . $notification['link'] . '">';

		$update_query = "DELETE FROM security.notifications 
					WHERE notification_id = " . $_GET['notification_id'];

		if ($db->doUpdate ($update_query) == 0) {
			$db->commit ();
		} else {
			$db->rollback ();
		}
	}

	include 'end.php';
?>

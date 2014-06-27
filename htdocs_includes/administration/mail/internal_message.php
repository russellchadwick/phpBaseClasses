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
	echo $display->head ('Administration -- Mail -- Internal Message');

	$internal_message = $db->doQuery1Row ("SELECT to_char (im.when_sent, 'YYYY-MM-DD HH24:MI:SS') as when_sent, im.subject, im.body, 
							(SELECT username 
								FROM security.users u 
								WHERE im.sent_by = u.user_id
							) as sent_by
						FROM security.internal_messages im 
						WHERE im.internal_message_id = " . $_GET['internal_message']);

	$update_query = "UPDATE security.internal_messages 
				SET when_viewed = CURRENT_TIMESTAMP 
				WHERE internal_message_id = " . $_GET['internal_message'];

	if ($db->doUpdate ($update_query) == 0) {
		$db->commit ();
	} else {
		$db->rollback ();
	}

	if (empty ($internal_message['sent_by'])) 
		$internal_message['sent_by'] = 'Admin';

	echo '
		<h4>Message</h4>
		From: ' . $internal_message['sent_by'] . '<br>
		When: ' . $internal_message['when_sent'] . '<br>
		Subject: ' . $internal_message['subject'] . '<br>
		Body: ' . $internal_message['body'] . '<br>';

	echo $display->foot ();
	include 'end.php';
?>

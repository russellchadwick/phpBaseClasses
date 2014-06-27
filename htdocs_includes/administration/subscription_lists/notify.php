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
 * @version $Revision: 1.5 $ $Date: 2003/09/04 18:53:52 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration -- Subscription Lists -- Notify');

	if ((!empty ($_GET['subject'])) && (!empty ($_GET['body']))) {
		$subscription_list_member_count = $db->doQuery ("SELECT u.user_id, u.username, u.first_name, u.last_name, 
							u.first_name||' '||u.last_name AS name, u.email_address 
						FROM security.subscription_list_members slm, 
							security.users u 
						WHERE slm.subscription_list_id = " . $_GET['subscription_list_id'] . "
							AND slm.user_id = u.user_id
						ORDER BY u.first_name||' '||u.last_name", $subscription_list_members);

		for ($index = 1; $index <= $subscription_list_member_count; $index++) {
			$subject = $_GET['subject'];
			$subject = str_replace ('{$FIRST_NAME}', $subscription_list_members[$index]['first_name'], $subject);
			$subject = str_replace ('{$LAST_NAME}', $subscription_list_members[$index]['last_name'], $subject);
			$subject = str_replace ('{$NAME}', $subscription_list_members[$index]['name'], $subject);
			$subject = str_replace ('{$USERNAME}', $subscription_list_members[$index]['username'], $subject);
			$subject = str_replace ('{$EMAIL_ADDRESS}', $subscription_list_members[$index]['email_address'], $subject);
			$subject = str_replace ('<br>', "\n", $subject);

			$body = $_GET['body'];
			$body = str_replace ('{$FIRST_NAME}', $subscription_list_members[$index]['first_name'], $body);
			$body = str_replace ('{$LAST_NAME}', $subscription_list_members[$index]['last_name'], $body);
			$body = str_replace ('{$NAME}', $subscription_list_members[$index]['name'], $body);
			$body = str_replace ('{$USERNAME}', $subscription_list_members[$index]['username'], $body);
			$body = str_replace ('{$EMAIL_ADDRESS}', $subscription_list_members[$index]['email_address'], $body);
			$body = str_replace ('<br>', "\n", $body);

			if (mail ($subscription_list_members[$index]['email_address'], $subject, $body, $_GET['headers'])) {
				echo 'OK ' . $subscription_list_members[$index]['name'] . '<br>';
			} else {
				echo 'Fail ' . $subscription_list_members[$index]['name'] . '<br>';
			}
		}
	}

	echo $display->foot ();
	include 'end.php';
?>
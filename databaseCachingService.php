#! /usr/bin/env php
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
 * @version $Revision: 1.3 $ $Date: 2003/09/04 20:43:37 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 3;
	$override_config['ERRORLOG'] = '/web/log/toolshed51.com/gateway/dbcs.log';
	$override_config['DB'][1]['TYPE']  = '';
	include 'begin.php';

	function processMessage () {
		global $command, $queries, $msg, $args;

		if ($command == 'Clear') 
			$queries = array ();

		if ($command == 'Query') {
			list ($QRYKEY, $dbname, $query) = @explode ('¡', $args);

			$datas = 'None';

			foreach ($queries as $query_count=>$query_data) {
				debug ("QUERIES :: " . $query_data['Query'] . " == $query :: " . $query_data['DBName'] . " == $dbname :: " . $query_data['Expires'] . " > " . mktime (), 3);
				if ($query_data['Expires'] < mktime ()) {
					unset ($queries[$query_count]);
				} else if (($query_data['Query'] == $query) && ($query_data['DBName'] == $dbname)) {
					if ($query_data['Data'] == 'None') {
						unset ($queries[$query_count]);
					} else {
						$datas = unserialize ($query_data['Data']);
					}
				}
			}

			$qry_id = msg_get_queue ($QRYKEY, 0600);
			msg_set_queue ($qry_id, array ('msg_perm.uid'=>'80'));

			if (is_array ($datas)) {
				if (strlen (serialize ($datas)) <= 2000) {
					msg_send ($qry_id, 1, serialize ($datas), true, true, $msg_err);
				} else {	
					msg_send ($qry_id, 1, 'Begin', true, true, $msg_err);
					foreach ($datas as $count => $data) {
						msg_send ($qry_id, 1, serialize ($data), true, true, $msg_err);
					}
					msg_send ($qry_id, 1, 'End', true, true, $msg_err);
				}
			} else {
				msg_send ($qry_id, 1, $datas, true, true, $msg_err);
			}
		}

		if ($command == 'Store') {
			list ($QRYKEY, $dbname, $query, $ttl) = @explode ('¡', $args);

			$qry_id = msg_get_queue ($QRYKEY, 0600);
			msg_set_queue ($qry_id, array ('msg_perm.uid'=>'80'));

			if (msg_receive ($qry_id, 1, $msg_type, 16384, $msg, true, 0, $msg_error)) {
				debug ("STORE :: Query == $query :: DBName == $dbname :: Expires == " . (mktime () + $ttl), 3);

				if ($msg == 'Begin') {
					$data = array (0 => '');

					while ($msg != 'End') {
						if (msg_receive ($qry_id, 1, $msg_type, 16384, $msg, true, 0, $msg_error)) {
							if ($msg != 'End') {
								array_push ($data, unserialize ($msg));
							}
						}
					}

					unset ($data[0]);

					$data = serialize ($data);
				} else {
					$data = $msg;
				}

				array_push ($queries, array (
							'Query'=>$query, 
							'DBName'=>$dbname, 
							'Expires'=>(mktime () + $ttl), 
							'Data'=>$data
						));
			}

			msg_remove_queue ($qry_id);
		}

		if ($command == 'AllQueries') {
			list ($QRYKEY) = @explode ('¡', $args);

			$retval = array ();

			foreach ($queries as $count=>$query) {
				$retval[$count]['Query'] = $query['Query'];
				$retval[$count]['DBName'] = $query['DBName'];
				$retval[$count]['Expires'] = date ('Y-m-d H:i:s', $query['Expires']);
				$retval[$count]['Data'] = $query['Data'];
			}

			$qry_id = msg_get_queue ($QRYKEY, 0600);
			msg_set_queue ($qry_id, array ('msg_perm.uid'=>'80'));

			msg_send ($qry_id, 1, serialize ($retval), true, true, $msg_err);
		}

		if (!empty ($msg_err)) 
			debug ("ERROR = $msg_err", 3);
	}

	$MSGKEY = 71540000;
	$msg_id = msg_get_queue ($MSGKEY, 0600);
	msg_set_queue ($msg_id, array ('msg_perm.uid'=>'80'));

	$queries = array ();

	debug ('Database Caching Server Ready', 3);

	while (1) {
		if (msg_receive ($msg_id, 1, $msg_type, 16384, $msg, true, 0, $msg_error)) {
			if (substr ($msg, 0, 4) == 'Quit') 
				break;

			debug ("Msg = $msg", 3);
			list ($command, $args) = @explode ('¡', $msg, 2);

			debug ("Command = $command", 3);

			register_tick_function ('processMessage');

			declare (ticks=2);

			unregister_tick_function ('processMessage');
		}
	}

	msg_remove_queue ($msg_id);

	include 'end.php';
?>
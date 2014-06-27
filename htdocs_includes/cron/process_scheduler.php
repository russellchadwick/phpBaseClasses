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

	$DEBUG = 2;
	$override_config['ERRORLOG'] = '/web/log/toolshed51.com/gateway/scheduler.log';
	include 'begin.php';

	$query = "SELECT process_schedule_id, currently_running 
					FROM software.process_schedules 
					WHERE path = '" . $_SERVER['PHP_SELF'] . "'";
	$currently_running = $db->doQuery1Row ($query);

	if (empty ($currently_running['currently_running'])) {
		$db->doUpdate ("INSERT INTO software.process_schedules 
					(process_schedule_id, path, currently_running, monitor)
				VALUES 
					(DEFAULT, '" . $_SERVER['PHP_SELF'] . "', 'f', 'f')");
		$db->commit ();
		$currently_running = $db->doQuery1Row ($query);
	}

	if ($currently_running['currently_running'] == 'f') {
		$begin = mktime ();

		$db->doUpdate ("UPDATE software.process_schedules 
				SET currently_running = 't' 
				WHERE process_schedule_id = " . $currently_running['process_schedule_id']);
		$db->commit ();

		$schedules = array (	'monitor'=>array (), 
					'only_once'=>array (), 
					'interval'=>array ()
				);

		$db->doQuery ("SELECT ps.process_schedule_id, ps.path, ps.currently_running 
				FROM software.process_schedules ps 
				WHERE ps.monitor = 't'", $schedules['monitor']);

		$db->doQuery ("SELECT ps.process_schedule_id, ps.path, ps.currently_running, ps.only_once, psl.when_run 
				FROM software.process_schedules ps 
					LEFT JOIN software.process_schedule_logs psl 
						ON ps.process_schedule_id = psl.process_schedule_id
				WHERE ps.only_once IS NOT NULL
					AND psl.when_run IS NULL
					AND ps.only_once <= 'now'::timestamp(0) without time zone", $schedules['only_once']);

		$db->doQuery ("SELECT ps.process_schedule_id, ps.path, ps.currently_running, ps.only_once, ('now'::timestamp(0) without time zone - psl.when_run) 
				FROM software.process_schedules ps 
					LEFT JOIN software.process_schedule_logs psl 
						ON ps.process_schedule_id = psl.process_schedule_id
				WHERE ps.interval IS NOT NULL
					AND (ps.interval < ('now'::timestamp(0) without time zone - psl.when_run)
						OR psl.when_run IS NULL)", $schedules['interval']);

		foreach ($schedules as $index => $schedule) {
		}

		$end = mktime ();
		$db->doUpdate ("INSERT INTO software.process_schedule_logs 
					(process_schedule_log_id, process_schedule_id, when_run, duration, output)
				VALUES 
					(DEFAULT, " . $currently_running['process_schedule_id'] . ", 'now'::timestamp(0) without time zone, interval '" . ($end - $begin) . " seconds', NULL)");
		$db->doUpdate ("UPDATE software.process_schedules 
				SET currently_running = 'f' 
				WHERE process_schedule_id = " . $currently_running['process_schedule_id']);
		$db->commit ();
	}

	include 'end.php';
?>
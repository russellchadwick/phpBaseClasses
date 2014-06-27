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
	$GRAPH = array ('line');
	include 'begin.php';

	echo $display->poseAsDocument ('image/png', $_GET['type'] . '.png');

	if ($_GET['interval'] == 'Month') {
		$current_timestamp = $datetime->date ('Y-m-d 00:00:00');
		$current_timestamp_data = substr ($current_timestamp, 8, 2);

		$datefunction = 'addDays';
		$interval = 2;

		$labels = array ();
		$timestamps = array ();
		for ($index = 30; $index >= 0; $index--) {
			array_push ($labels, $datetime->date ('m-d', $datetime->{$datefunction} ($current_timestamp, (0 - $index))));
			array_unshift ($timestamps, $datetime->{$datefunction} ($current_timestamp, (0 - $index)));
		}
	}

	if ($_GET['interval'] == 'Day') {
		$current_timestamp = $datetime->date ('Y-m-d H:00:00');
		$current_timestamp_data = substr ($current_timestamp, 11, 2);

		$datefunction = 'addHours';
		$interval = 2;

		$labels = array ();
		$timestamps = array ();
		for ($index = 24; $index >= 0; $index--) {
			array_unshift ($labels, (($current_timestamp_data + 24 + $index) % 24) . ':00:00');
			array_unshift ($timestamps, $datetime->{$datefunction} ($current_timestamp, (0 - $index)));
		}
	}

	if ($_GET['interval'] == 'Hour') {
		$current_timestamp = $datetime->date ('Y-m-d H:i:00');
		$current_timestamp_data = substr ($current_timestamp, 14, 2);

		$datefunction = 'addMinutes';
		$interval = 4;

		$labels = array ();
		$timestamps = array ();
		for ($index = 60; $index >= 0; $index--) {
			array_unshift ($labels, substr ($current_timestamp, 11, 2) . ':' . str_pad (($current_timestamp_data + 60 + $index) % 60, 2, '0', STR_PAD_LEFT) . ':00');
			array_unshift ($timestamps, $datetime->{$datefunction} ($current_timestamp, (0 - $index)));
		}
	}

	$ydata = array ();
	foreach ($timestamps as $index => $timestamp) {
		$data = or0 ($db->doQuery1Row1Column ("SELECT sum (s.data) FROM software.statistics s
					WHERE s.statistic_category_id = (SELECT
							sc.statistic_category_id
						FROM software.statistic_categories sc 
						WHERE sc.name = '" . $_GET['type'] . "')
					AND when_logged > '" . $datetime->{$datefunction} ($timestamp, -1) . "'
					AND when_logged <= '" . $timestamp . "'"));
		 array_unshift ($ydata, $data);
	}

	$graph = new Graph (640, 240, 'auto');
	$graph->SetScale ('textlin');

	$lineplot = new LinePlot ($ydata);

	$graph->Add ($lineplot);

	$graph->img->SetMargin (40, 20, 20, 55);
	$graph->title->Set ($_GET['type'] . ' Statistics per ' . $_GET['interval'] . ' for ' . $current_timestamp);
	$graph->title->SetFont (FF_ARIAL, FS_NORMAL, 11);

	$graph->xaxis->SetFont (FF_ARIAL, FS_NORMAL, 11);
	$graph->xaxis->SetLabelAngle (55);
	$graph->xaxis->SetTickLabels ($labels);
	$graph->xaxis->SetTextLabelInterval ($interval);

	$graph->yaxis->title->Set ('# of ' . $_GET['type'] . 's');
	$graph->yaxis->title->SetFont (FF_ARIAL, FS_NORMAL, 11);

	$lineplot->SetColor ('blue');

	$graph->SetShadow ();

//	$lineplot2 = new LinePlot ($ydata2);
//	$graph->Add ($lineplot2);
//	$lineplot2->SetColor ('orange');

	$graph->Stroke ();

	include 'end.php';
?>

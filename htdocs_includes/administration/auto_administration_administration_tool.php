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
 * @version $Revision: 1.19 $ $Date: 2003/07/03 10:36:56 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

/*
Debug Array -- Edits
   validation => clearError ('level')validateEmpty ('level')validateNumber ('level', -2147483648, 2147483647)
   empty => 
   save => saveEmpty (saveData (#))
End Debug Array -- Edits
Debug Array -- Description
   foreign_table => 
   foreign_column => 
   sequenced => false
End Debug Array -- Description
*/

	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Administration Administration');

	$getvars = explode ('/', substr ($_SERVER["PATH_INFO"], 1));

	$ol->set ('align', 1);

	if ((isset ($_POST['submit'])) && ($_POST['submit'] == 'Save')) {
		if ($_POST['type'] == 'auto_administration_administration_column') {
			$description = $db->doDescribe ($getvars[0]);
			$datainput->loadDescription ($description);

			$defaultEdits = $datainput->makeEditAll (array (), array ($getvars[1]=>array ('LOAD_COMMENTS'=>false)));

			$defaultEdits = $defaultEdits[searchArrayForIndexByKeyVal ($defaultEdits, 'column_name', $getvars[1])];

			$override = array ();

			foreach ($_POST['edits'] as $key=>$value) {
				debug ('Edits = ' . $defaultEdits[$key] . ' :: Post = ' . $_POST['edits'][$key], 2);
				if ($defaultEdits[$key] != $_POST['edits'][$key]) {
					debug ('Override: ' . $key, 2);
					$override[$key] = $value;
				}
			}

			if (empty ($override)) {
				$db->setComment ('COLUMN', $getvars[0] . '.' . $getvars[1], '');
			} else {
				$db->setComment ('COLUMN', $getvars[0] . '.' . $getvars[1], serialize ($override));
			}
		} else if ($_POST['type'] == 'auto_administration_administration_table') {
			
		}
	}

	if (empty ($getvars[0])) {
		echo '
				<h3>Choose a table to administer</h3><br>';

		foreach ($db->getTableList () as $table) {
			echo '
				<a href="' . $_SERVER['PHP_SELF'] . '/' . $table . '">' . $table . '</a><br>';
		}
	} else if (empty ($getvars[1])) {
		$description = $db->doDescribe ($getvars[0]);
		$datainput->loadDescription ($description);

		$edits = $datainput->makeEditAll ();
		$defaultEdits = $datainput->makeEditAll (array (), array ($getvars[1]=>array ('LOAD_COMMENTS'=>false)));

		echo '
	<center><h4>Administration Administration Tool</h4></center>

	<center><a href="' . substr ($_SERVER['PHP_SELF'], 0, strrpos ($_SERVER['PHP_SELF'], '/')) . '">[ Back to List ]</a></center><br>

	<center><span>Choose a column of table <b>' . $getvars[0] . '</b> to change</span>
	<br><br>';

		debugarray ('Edits', $edits, 3);

		foreach ($edits as $edit) {
			echo '
				<a href="' . $_SERVER['PHP_SELF'] . '/' . $edit['column_name'] . '">' . $edit['column_name'] . '</a><br>';
		}

		echo '
	</center>

	<center><h4>Sample of Administration Tool</h4></center>';

		$smarty->assign ('edits', $edits);
		$smarty->assign ('validation', $datainput->getValidateAll ($edits, 'JS'));
		$smarty->assign ('action', $_SERVER['PHP_SELF']);
		$smarty->display ('auto_administration_tool_edit.tpl');
	} else {
		$description = $db->doDescribe ($getvars[0]);
		$datainput->loadDescription ($description);

		$edits = $datainput->makeEditAll ();
		$defaultEdits = $datainput->makeEditAll (array (), array ($getvars[1]=>array ('LOAD_COMMENTS'=>false)));

		echo '
	<center><h4>Administration Administration Tool</h4></center>

	<center><span>Customizing column <b>' . $getvars[1] . '</b> of table <b>' . $getvars[0] . '</b></span>
	<br><br>
	<center><a href="' . substr ($_SERVER['PHP_SELF'], 0, strrpos ($_SERVER['PHP_SELF'], '/')) . '">[ Back to Table ]</a></center><br>';

		$edits = $edits[searchArrayForIndexByKeyVal ($edits, 'column_name', $getvars[1])];
		$defaultEdits = $defaultEdits[searchArrayForIndexByKeyVal ($defaultEdits, 'column_name', $getvars[1])];

		debugarray ('Edits', $edits, 2);
		debugarray ('Default Edits', $defaultEdits, 2);

		$smarty->assign ('edits', $edits);
		$smarty->assign ('defaultEdits', $defaultEdits);
		$smarty->assign ('action', $_SERVER['PHP_SELF']);
		$smarty->display ('auto_administration_administration_column.tpl');
	}

	echo $display->foot ();
	include 'end.php';
?>

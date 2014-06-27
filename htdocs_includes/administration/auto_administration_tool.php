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
 * @version $Revision: 1.1 $ $Date: 2004/01/13 18:56:39 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	$DEBUG = 2;
	include 'begin.php';
	echo $display->head ('Administration');

	$getvars = explode ('/', substr ($_SERVER["PATH_INFO"], 1));

	$ol->set ('align', 1);

	$description = $db->doDescribe ($getvars[0]);
	$datainput->loadDescription ($description);

	$edits = $datainput->makeEditAll ();

	if ((isset($_POST['submit'])) && ($_POST['submit'] == 'Submit')) {
		eval ($datainput->getValidateAll ($edits, 'PHP'));

		if (empty ($validate)) {
			$query = $datainput->makeSQL ($edits, $_POST['save_type']);
			$update_success = $db->doUpdate ($query);
			if ($update_success == 0) 
				$db->commit ();
			else 
				$db->rollback ();
		} else {
			$edits = $datainput->makeEditAll ($datainput->getCurrentValues ($edits));
			$datainput->addErrorMessages ($edits, $validate);
		}
	}

	if (((isset($_POST['submit'])) && ($_POST['submit'] == 'Submit') && (!empty ($validate))) || ((!isset($_POST['submit'])) || ($_POST['submit'] != 'Submit'))) {
		$smarty->assign ('edits', $edits);
		$smarty->assign ('validation', $datainput->getValidateAll ($edits, 'JS'));
		$smarty->assign ('action', $_SERVER['PHP_SELF']);
		$smarty->display ('auto_administration_tool_edit.tpl');
	}

	echo $display->foot ();
	include 'end.php';
?>
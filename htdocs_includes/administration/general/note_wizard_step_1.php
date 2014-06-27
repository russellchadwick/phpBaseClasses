<?php
	$DEBUG = 1;
	include 'begin.php';

	$wizard->begin (2, 'notes');
	if (isset ($_GET['security_object_id'])) {
		$wizard->setValue ('security_object_id', $_GET['security_object_id']);
		$wizard->setValue ('security_object_reference_id', $_GET['security_object_reference_id']);
		$wizard->setValue ('user_id', $_SESSION['user_id']);
	}

	if (isset ($_GET['note_id'])) {
		$wizard->setValue ('note_id', $_GET['note_id']);
	} else {
		$wizard->setValue ('display', 't');
	}

	if ($wizard->getValue ('note_id') > 0) {
		$data = $db->doQuery1Row ('SELECT note_id, user_id, security_object_id, security_object_reference_id, when_noted, title, note, display 
						FROM notes
						WHERE note_id = ' . $wizard->getValue ('note_id'));
		$wizard->setValues ($data, true);
	}

	echo $display->head ('Administration - General - Note Wizard Step 1');
	echo tab ();

	$nav = $wizard->getNavigation ();

	$ol->ol_offsety = 20;

	echo '
<center>' . $wizard->_errorMessage . '</center>
<form method="post" name="form" action="">
<table align="center" cellspacing="3" cellpadding="3">
	<tr class="table_header">
		<td colspan="3" class="table_header_large" align="center">Note Information</td>
	</tr>
	' . $datainput->displayEditByName (1, 'note_id', $wizard->getValue ('note_id'), array ('error'=>$wizard->getError ('note_id'))) . '
	' . $datainput->displayEditByName (2, 'title', $wizard->getValue ('title'), array ('error'=>$wizard->getError ('title'), 'help_message'=>'Title for this Note')) . '
	' . $datainput->displayEditByName (3, 'note', $wizard->getValue ('note'), array ('error'=>$wizard->getError ('note'), 'help_message'=>'Body for this Note'));

	if ($wizard->getValue ('note_id') > 0) {
		echo '
	' . $datainput->displayEditByName (4, 'display', $wizard->getValue ('display'), array ('error'=>$wizard->getError ('display'), 'help_message'=>'Whether to display this note or not'));
	}

	echo '
	</tr>
	<tr class="table_even_row">
		<td colspan="3" align="center">' . $nav['back'] . ' &nbsp;&nbsp;&nbsp;&nbsp; ' . $nav['next'] . '</td>
	</tr>
	<tr class="table_header">
		<td colspan="3"><input type="hidden" name="security_object_id" value="' . $wizard->getValue ('security_object_id') . '">
				<input type="hidden" name="security_object_reference_id" value="' . $wizard->getValue ('security_object_reference_id') . '">
				<input type="hidden" name="user_id" value="' . $wizard->getValue ('user_id') . '">&nbsp;</td>
	</tr>
</table>
<form>';

	include 'end.php';
?>

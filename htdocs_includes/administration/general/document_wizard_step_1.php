<?php
	$DEBUG = 1;
	include 'begin.php';

	if (isset ($_GET['wizard_errorMessage'])) {
		$_GET['wizard_errorMessage'] = str_replace ('filename', 'document', $_GET['wizard_errorMessage']);
	}

	$wizard->begin (2, 'documents');
	if (isset ($_GET['security_object_id'])) {
		$wizard->setValue ('security_object_id', $_GET['security_object_id']);
		$wizard->setValue ('security_object_reference_id', $_GET['security_object_reference_id']);
	}

	if (isset ($_GET['parent_document_id'])) {
		$wizard->setValue ('parent_document_id', $_GET['parent_document_id']);
	}

	if (isset ($_GET['attributes'])) {
		$wizard->setValue ('attributes', $_GET['attributes']);
	}

	if (isset ($_GET['document_id'])) {
		$wizard->setValue ('document_id', $_GET['document_id']);
	}

	$wizard->setValue ('most_recent', 'Yes');

	if ($wizard->getValue ('document_id') > 0) {
		$data = $db->doQuery1Row ('SELECT document_id, name, security_object_id, security_object_reference_id, 
							parent_document_id, version, attributes, most_recent, filename 
						FROM documents
						WHERE document_id = ' . $wizard->getValue ('document_id'));
		$wizard->setValues ($data, true);
	}

	echo $display->head ('Administration - General - Document Wizard Step 1');
	echo tab ();

	$nav = $wizard->getNavigation ();

	$ol->ol_offsety = 20;

	echo '
<center>' . $wizard->_errorMessage . '</center>
<form method="post" enctype="multipart/form-data" name="form" action="">
<table align="center" cellspacing="3" cellpadding="3">
	<tr class="table_header">
		<td colspan="3" class="table_header_large" align="center">Document Information</td>
	</tr>
	' . $datainput->displayEditByName (1, 'document_id', $wizard->getValue ('document_id'), array ('error'=>$wizard->getError ('document_id'))) . '
	' . $datainput->displayEditByName (2, 'name', $wizard->getValue ('name'), array ('error'=>$wizard->getError ('name'), 'help_message'=>'Name for this Document')) . '
	' . $datainput->displayEditByName (3, 'version', $wizard->getValue ('version'), array ('error'=>$wizard->getError ('version'), 'help_message'=>'Version of this document')) . '
	' . $datainput->displayEditByName (4, 'document', $wizard->getValue ('document'), array ('error'=>$wizard->getError ('document'), 'help_message'=>'Please choose a document to upload')) . '
	</tr>
	<tr class="table_odd_row">
		<td colspan="3" align="center">' . $nav['back'] . ' &nbsp;&nbsp;&nbsp;&nbsp; ' . $nav['next'] . '</td>
	</tr>
	<tr class="table_header">
		<td colspan="3">&nbsp;</td>
	</tr>
</table>
<form>';

	include 'end.php';
?>

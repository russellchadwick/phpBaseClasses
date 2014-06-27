<?php
	$DEBUG = 1;
	include 'begin.php';

	if (!isset ($_GET['document_id'])) {
		$pieces = explode ('/', $_SERVER['PHP_SELF']);
		$_GET['document_id'] = (int) urldecode ($pieces[count($pieces) - 2]);
		$expected_filename = urldecode ($pieces[count($pieces) - 1]);
	}

	if (!empty ($_GET['document_id'])) {
		$document = $db->doQuery1Row ("SELECT document, filename 
						FROM documents 
						WHERE document_id = " . $_GET['document_id']);

		if ((isset ($expected_filename)) && ($expected_filename != $document['filename'])) {
			$document = array ();
		}
	}

	if ((isset ($document['filename'])) && (!empty ($document['filename']))) {
		echo $display->poseAsDocument ('application/octet-stream', $document['filename']);

		echo pg_unescape_bytea ($document['document']);
	} else {
		echo $display->poseAsDocument ('application/octet-stream', 'File Not Found.txt');

		echo 'Could not find the specified document';
	}

	include 'end.php';
?>
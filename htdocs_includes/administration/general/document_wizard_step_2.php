<?php
	$DEBUG = 1;
	include 'begin.php';

	$wizard->next (2, 'documents');

	$wizard->validateAll (array ('filename'));

	$wizard->backOnError ();

	echo $display->head ('Administration - General - Document Wizard Step 2');
	echo tab ();

	if ($wizard->issetValue ('document_id')) {
		function INSERT_callback (&$queries) {
			global $wizard;

			$queries[2] = "UPDATE documents SET most_recent = 'f' WHERE document_id = " . $wizard->getValue ('document_id');

			return $queries;
		}

		$wizard->_action = 'INSERT';

		$wizard->save ();
	} else {
		$wizard->save ();
	}

	echo '
<table align="center" cellspacing="3" cellpadding="3">
	<tr class="table_header">
		<td class="table_header_large" align="center">Document Save</td>
	</tr>
	<tr class="table_even_row">
		<td align="center">' . $wizard->done () . '</td>
	</tr>
	<tr class="table_header">
		<td>&nbsp;</td>
	</tr>
</table>';

	include 'end.php';
?>

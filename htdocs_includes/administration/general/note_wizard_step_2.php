<?php
	$DEBUG = 1;
	include 'begin.php';

	$wizard->next (2, 'notes');

	$wizard->validateAll ();

	$wizard->backOnError ();

	echo $display->head ('Administration - General - Note Wizard Step 2');
	echo tab ();

	$wizard->save ();

	echo '
<table align="center" cellspacing="3" cellpadding="3">
	<tr class="table_header">
		<td class="table_header_large" align="center">Note Save</td>
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
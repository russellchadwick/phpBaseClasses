<form name="form1" method="post" action="{$action}">
	<table width="640" align="center" cellspacing="3" cellpadding="3">
		<tr class="table_header" align="center">
			<td><b>Field</b></td>
			<td>&nbsp;</td>
			<td><b>Value</b></td>
			<td>&nbsp;</td>
			<td><b>Default Value</b></td>
		</tr>

		<tr class="table_odd_row">
			<td>Title</td>
			<td align="center"><span class="error">*</span></td>
			<td><input type="text" name="edits[title]" value="{$edits.title}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[title]', '{$defaultEdits.title}')"><--</a>
			<td>{$defaultEdits.title}</td>
		</tr>

		<tr class="table_header">
			<td colspan="2" align="center"><span class="error"> * Required Field</span></td>
			<td colspan="2" align="center">Click <-- to Copy Default</td>
			<td><input type="hidden" name="type" value="auto_administration_administration_table"><input type="submit" name="submit" value="Save"></td>
		</tr>
	</table>
</form>
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
		<tr class="table_even_row">
			<td>Data Length</td>
			<td></td>
			<td><input type="text" name="edits[data_length]" value="{$edits.data_length}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[data_length]', '{$defaultEdits.data_length}')"><--</a>
			<td>{$defaultEdits.data_length}</td>
		</tr>
		<tr class="table_odd_row">
			<td>Data Type</td>
			<td align="center"><span class="error">*</span></td>
			<td><input type="text" name="edits[data_type]" value="{$edits.data_type}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[data_type]', '{$defaultEdits.data_type}')"><--</a>
			<td>{$defaultEdits.data_type}</td>
		</tr>
		<tr class="table_even_row">
			<td>Is Nullable</td>
			<td></td>
			<td><input type="text" name="edits[is_nullable]" value="{$edits.is_nullable}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[is_nullable]', '{$defaultEdits.is_nullable}')"><--</a>
			<td>{$defaultEdits.is_nullable}</td>
		</tr>
		<tr class="table_odd_row">
			<td>Default Value</td>
			<td></td>
			<td><input type="text" name="edits[default_value]" value="{$edits.default_value}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[default_value]', '{$defaultEdits.default_value}')"><--</a>
			<td>{$defaultEdits.default_value}</td>
		</tr>
		<tr class="table_even_row">
			<td>Minimum Value</td>
			<td></td>
			<td><input type="text" name="edits[min]" value="{$edits.min}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[min]', '{$defaultEdits.min}')"><--</a>
			<td>{$defaultEdits.min}</td>
		</tr>
		<tr class="table_odd_row">
			<td>Maximum Value</td>
			<td></td>
			<td><input type="text" name="edits[max]" value="{$edits.max}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[max]', '{$defaultEdits.max}')"><--</a>
			<td>{$defaultEdits.max}</td>
		</tr>
		<tr class="table_even_row">
			<td>Precision of Value</td>
			<td></td>
			<td><input type="text" name="edits[precision]" value="{$edits.precision}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[precision]', '{$defaultEdits.precision}')"><--</a>
			<td>{$defaultEdits.precision}</td>
		</tr>
		<tr class="table_odd_row">
			<td>Help Message</td>
			<td></td>
			<td><input type="text" name="edits[help_message]" value="{$edits.help_message|escape:"html"}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[help_message]', '{$defaultEdits.title}')"><--</a>
			<td>{$defaultEdits.help_message|escape:"html"}</td>
		</tr>
		<tr class="table_even_row">
			<td>Help Example</td>
			<td></td>
			<td><input type="text" name="edits[example]" value="{$edits.example|escape:"html"}" size="70"></td>
			<td nowrap><a href="#" onClick="setValue ('edits[example]', '{$defaultEdits.title}')"><--</a>
			<td>{$defaultEdits.example|escape:"html"}</td>
		</tr>

		<tr class="table_header">
			<td colspan="2" align="center"><span class="error"> * Required Field</span></td>
			<td colspan="2" align="center">Click <-- to Copy Default</td>
			<td><input type="hidden" name="type" value="auto_administration_administration_column"><input type="submit" name="submit" value="Save"></td>
		</tr>
	</table>
</form>
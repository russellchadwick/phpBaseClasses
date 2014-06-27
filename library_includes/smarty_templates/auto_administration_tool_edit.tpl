<form name="form1" method="post" action="{$action}">
	<table width="80%" align="center" cellspacing="3" cellpadding="3">
		<tr align="center">
			<td colspan="5"><h4>Edit </h4></td>
		</tr>
		<tr class="table_header" align="center">
			<td>&nbsp;</td>
			<td><b>Title</b></td>
			<td><b>Help</b></td>
			<td>&nbsp;</td>
			<td><b>Value</b></td>
		</tr>
{foreach from=$edits item="edit"}
		<tr class="table_{cycle values="odd,even"}_row">
			<td><div id="{$edit.column_name}_error_image" style="visibility: hidden;"><img src="/includes/images/general/redcheck.gif" border="0" alt="redcheck"></div></td>
			<td><span id="{$edit.column_name}_title">{$edit.title}</span></td>
			<td align="center">{$edit.help}</td>
			<td align="center" class="errorcell">{$edit.required}{$edit.na}</td>
			<td>{$edit.input}<span id="{$edit.column_name}_error_message" class="error">{$edit.error}</span></td>
		</tr>
{/foreach}
		<tr class="table_header">
			<td colspan="4" align="center"><span class="error"> * Required Field</span></td>
			<td><input type="button" name="validate" value="Save" onClick="{$validation}"></td>
		</tr>
	</table>
	<div id="real_submit" style="visibility: hidden;"><input type="submit" name="submit" value="Submit"></div>
</form>
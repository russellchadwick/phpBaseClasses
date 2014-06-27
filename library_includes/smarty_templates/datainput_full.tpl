	<tr{if $class} class="{$class}"{/if}>
		<td nowrap>
			<div id="{$edit.column_name}_error_image"{if $edit.error > ''} style="visibility: hidden;"{/if}>
				<img src="/includes/images/general/redcheck.gif" border="0" alt="redcheck" align="left" width="{if $edit.error > ''}18{else}0{/if}>
			</div>
			<span id="{$edit.column_name}_title">{$edit.title} {$edit.help}</span>
		</td>
		<td align="center"{if $edit.required} class="errorcell"{/if} width="30">
			&nbsp;{$edit.na}
		</td>
		<td>
			{$edit.input}
			<span id="{$edit.column_name}_error_message" class="error">
				{$edit.error}
			</span>
		</td>
	</tr>

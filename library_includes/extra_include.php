<?php
	function menu () {
		global $db, $security;

		$retval = '
<table width="100%">
	<tr class="menu_header">
		<td width="33%" class="menu_header_item">Title 1</td>
		<td width="33%" class="menu_header_item">Title 2</td>
		<td width="33%" class="menu_header_item">Title 3</td>
	</tr>
</table>
<table width="100%">
	<tr class="menu">
		<td class="menu_item">';

		return $retval;
	}

	function menu_end () {
		$retval = '
		</td>
	</tr>
</table>
<center><small>Copyright ' . date ('Y') . '</small></center>';

		return $retval;
	}

?>

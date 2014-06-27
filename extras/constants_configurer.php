<?php
	$file = $_GET['file'];
	if (empty($file)) $file = $_POST['file'];
	if (empty($file)) $file = $LIBRARY_PATH . '/constants/constants.php';

	$master_vars = array ('SKIN_URI', 'SKIN_PATH', 'INCLUDE_URI', 'INCLUDE_PATH', 'SKIN_INCLUDE_PATH', 'LIBRARY_INCLUDE_PATH');

	function fix_variables ($text) {
		$pos = strpos ($text, '#');
		if ($pos !== false) { 
			$pos2 = strpos ($text, '#', $pos + 1);
			$var_name = substr ($text, $pos + 1, $pos2 - 1);
			$text = "' . " . substr ($text, 0, $pos) . '$' . $var_name . " . '" . substr ($text, $pos2 + 1);
		}

		return $text;
	}

	function fix_display ($current_item, $value) {
		if ($current_item['type'] == 'boolean') {
			if ($value == 1) {
				$value = 'true';
			} else {
				$value = 'false';
			}

			$html = '<select name="' . $current_item['name'] . '">';
			if ($value == 'true') {
				$html .= '<option value="true" selected>true</option><option value="false">false</option>';
			} else {
				$html .= '<option value="true">true</option><option value="false" selected>false</option>';
			}
			$html .= '</select>';
		} else if ($current_item['type'] == 'number') {
			$html = '<input type="text" name="' . $current_item['name'] . '" size="6" maxlength="8" value="' . $value . '">';
		} else if ($current_item['type'] == 'text') {
			$html = '<input type="text" name="' . $current_item['name'] . '" size="50" maxlength="200" value="' . $value . '">';
		} else if ($current_item['type'] == 'array') {
			$value = @implode ("', '", $value);
			$html = '<input type="text" name="' . $current_item['name'] . '" size="50" maxlength="200" value="\'' . $value . '\'">';
		}

		return $html;
	}

	$values = array (
		1=>array (
			'name'=>'Globals',
			'values'=>array (
			)
		),
		2=>array (
			'name'=>'Sessions',
			'values'=>array (
				1=>array (
					'name'=>'SESSIONS_ENABLED',
					'type'=>'boolean',
					'default_value'=>'false',
					'description'=>''
				),
				2=>array (
					'name'=>'SESSION_VARS',
					'type'=>'array',
					'default_value'=>"'user_id', 'user_last_access'",
					'description'=>'user_last_access needs to be last, and is for timeout feature'
				),
				3=>array (
					'name'=>'SESSION_TIMEOUT',
					'type'=>'number',
					'default_value'=>'120',
					'description'=>'In minutes, requires user_last_access in line above'
				),
				4=>array (
					'name'=>'SESSION_REQUIRED_VAR',
					'type'=>'text',
					'default_value'=>'user_id',
					'description'=>'Only timeout if this is set'
				),
				5=>array (
					'name'=>'SESSION_LOGIN_URI',
					'type'=>'text',
					'default_value'=>'/login.php',
					'description'=>'Where to send people if session expires'
				),
				6=>array (
					'name'=>'SESSION_REQUIRED_URIS',
					'type'=>'array',
					'default_value'=>"'/protected', '/protected2'",
					'description'=>'Which folders to timeout people in/check for login var'
				)
			)
		),
		3=>array (
			'name'=>'Caching',
			'values'=>array (
				1=>array (
					'name'=>'CACHING_ENABLED',
					'type'=>'boolean',
					'default_value'=>'false',
					'description'=>''
				),
				2=>array (
					'name'=>'CACHE_TIMEOUT',
					'type'=>'number',
					'default_value'=>'60',
					'description'=>'In minutes, how often to update cached pages.'
				),
				3=>array (
					'name'=>'CACHE_URIS',
					'type'=>'array',
					'default_value'=>"'/'",
					'description'=>'Which folders to enable caching in'
				),
				4=>array (
					'name'=>'CACHE_DIRECTORY',
					'type'=>'text',
					'default_value'=>'/usr/local/www/cache',
					'description'=>'Where to place cached tree'
				)
			)
		),
		4=>array (
			'name'=>'Database',
			'values'=>array (
				1=>array (
					'name'=>'TYPE',
					'type'=>'text',
					'default_value'=>'PgSQL',
					'description'=>'Empty to not connect to database'
				),
				2=>array (
					'name'=>'HOST',
					'type'=>'text',
					'default_value'=>'',
					'description'=>'Empty to connect with domain socket'
				),
				3=>array (
					'name'=>'USER',
					'type'=>'text',
					'default_value'=>'pgsql',
					'description'=>''
				),
				4=>array (
					'name'=>'PASS',
					'type'=>'text',
					'default_value'=>'',
					'description'=>''
				),
				5=>array (
					'name'=>'NAME',
					'type'=>'text',
					'default_value'=>'db',
					'description'=>''
				),
				6=>array (
					'name'=>'QUERY_DIRECTORY',
					'type'=>'text',
					'default_value'=>'/usr/local/www/data/queries',
					'description'=>'Directory to load queries from'
				),
				7=>array (
					'name'=>'QUERY_FILES',
					'type'=>'array',
					'default_value'=>"'general'",
					'description'=>'Filenames to load'
				),
				8=>array (
					'name'=>'DBERRORLOG',
					'type'=>'text',
					'default_value'=>'/usr/local/www/log/db_error.log',
					'description'=>'Where to log database errors, empty will disable logging'
				),
				9=>array (
					'name'=>'DBQUERYLOG',
					'type'=>'text',
					'default_value'=>'',
					'description'=>'Where to log all queries, empty will disable logging'
				)
			)
		),
		5=>array (
			'name'=>'HTML',
			'values'=>array (
				1=>array (
					'name'=>'LANGUAGE',
					'type'=>'text',
					'default_value'=>'en-US',
					'description'=>''
				),
				2=>array (
					'name'=>'CONTENT_TYPE',
					'type'=>'text',
					'default_value'=>'text/html; charset=iso-8859-1',
					'description'=>''
				),
				3=>array (
					'name'=>'KEYWORDS',
					'type'=>'text',
					'default_value'=>'Toolshed phpHostingClasses',
					'description'=>''
				),
				4=>array (
					'name'=>'DESCRIPTION',
					'type'=>'text',
					'default_value'=>'Toolshed phpHostingClasses from Toolshed51.com',
					'description'=>''
				),
				5=>array (
					'name'=>'HOSTED_BY',
					'type'=>'text',
					'default_value'=>'Toolshed Computer Productions',
					'description'=>''
				),
				6=>array (
					'name'=>'STYLESHEET',
					'type'=>'text',
					'default_value'=>'/includes/stylesheet.css',
					'description'=>''
				),
				7=>array (
					'name'=>'TITLE_PREFIX',
					'type'=>'text',
					'default_value'=>'Toolshed51.com -- ',
					'description'=>''
				),
				8=>array (
					'name'=>'JAVASCRIPTS',
					'type'=>'array',
					'default_value'=>"'/includes/main.js'",
					'description'=>''
				),
				9=>array (
					'name'=>'HEADERS',
					'type'=>'array',
					'default_value'=>"",
					'description'=>''
				),
				10=>array (
					'name'=>'EXTRA_INCLUDE_PATH',
					'type'=>'text',
					'default_value'=>'#LIBRARY_INCLUDE_PATH#extra_include.php',
					'description'=>''
				),
				11=>array (
					'name'=>'EXTRA_HEADER_PATH',
					'type'=>'text',
					'default_value'=>'#LIBRARY_INCLUDE_PATH#header.php',
					'description'=>''
				),
				12=>array (
					'name'=>'EXTRA_FOOTER_PATH',
					'type'=>'text',
					'default_value'=>'#LIBRARY_INCLUDE_PATH#footer.php',
					'description'=>''
				),
				13=>array (
					'name'=>'EXTRA_INCLUDE_PRE_PATH',
					'type'=>'array',
					'default_value'=>"''",
					'description'=>''
				),
				14=>array (
					'name'=>'EXTRA_INCLUDE_POST_PATH',
					'type'=>'array',
					'default_value'=>"''",
					'description'=>''
				)
			)
		),
		6=>array (
			'name'=>'Extras',
			'values'=>array (
				1=>array (
					'name'=>'TREEMENU_ENABLED',
					'type'=>'boolean',
					'default_value'=>'false',
					'description'=>''
				),
				2=>array (
					'name'=>'OVERLIB_ENABLED',
					'type'=>'boolean',
					'default_value'=>'false',
					'description'=>''
				)
			)
		)
	);

	if (is_file ($file)) include ($file);

	echo '
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table border="1" cellspacing="2" cellpadding="2">
	<tr>
		<td>Variable</td>
		<td>Data Type</td>
		<td>Default Value</td>
		<td>Current Value</td>
	</tr>';

	foreach ($values as $section_count=>$section_data) {
		for ($index = 1; $index <= count($section_data['values']); $index++) {
			$current_item = $section_data['values'][$index];

			echo '
	<tr>
		<td>' . $current_item['name'] . '</td>
		<td>' . $current_item['type'] . '</td>
		<td>' . $current_item['default_value'] . '</td>
		<td>' . fix_display ($current_item, ${$current_item['name']}) . '</td>
	</tr>';
		}

		echo '
	<tr>
		<td colspan="4">&nbsp;</td>
	</tr>';
	}

	echo '
	<tr>
		<td colspan="4" align="center"><input type="submit" name="submit" value="Save">
	</tr>
</table>
</form>';

/*
	foreach ($values as $section_count=>$section_data) {
		$constants_file = '
// Section: ' . $section_data['name'] . '
';

		if ($section_data['name'] == 'Globals') 
			$constants_file .= '	$LIBRARY_INCLUDE_PATH = $LIBRARY_PATH . \'library_includes/\';
	$SKIN_INCLUDE_PATH = $LIBRARY_INCLUDE_PATH . $_SERVER[\'SERVER_NAME\'] . \'/\';
	$INCLUDE_URI = \'/includes\';
	$INCLUDE_PATH = substr ($_SERVER[\'DOCUMENT_ROOT\'], 0, -1) . $INCLUDE_URI;
	$SKIN_URI = \'/skin/\' . $_SERVER[\'SERVER_NAME\'];
	$SKIN_PATH = substr ($_SERVER[\'DOCUMENT_ROOT\'], 0, -1) . $SKIN_URI;
';

		for ($index = 1; $index <= count($section_data['values']); $index++) {
			$current_item = $section_data['values'][$index];
			$constants_file .= '	' . $current_item['name'] . ' = ';

			$current_item['default_value'] = fix_variables ($current_item['default_value']);

			switch ($current_item['type']) {
				case 'boolean':
				case 'number':
					$constants_file .= $current_item['default_value'];
					break;
				case 'text':
					$constants_file .= "'" . $current_item['default_value'] . "'";
					break;
				case 'array':
					$constants_file .= 'array (' . $current_item['default_value'] . ')';
					break;
			}

			$constants_file .= ';';

			if (!empty($current_item['description'])) $constants_file .= '			// ' . $current_item['description'];

			$constants_file .= '
';
		}			
	}
*/
?>
<?php
	if (!isset ($_GET['step'])) {
		$_GET['step'] = 1;
	}

	function expandFolder ($folder, $original_call=0) {
		$retval = array ();

		if (is_dir ($folder)) {
			if ($dh = opendir ($folder)) {
				while (($file = readdir ($dh)) !== false) {
					if (($file != '.') && ($file != '..') && ($file != 'CVS')) {
						if (is_dir ($folder . '/' . $file)) {
							$retval = array_merge ($retval, expandFolder ($folder . '/' . $file, 1));
						} else {
							array_push ($retval, $folder . '/' . $file);
						}
					}
				}

				closedir($dh);
			}

			if ($original_call == 0) {
				foreach ($retval as $index => $piece) {
					$retval[$index] = substr ($piece, strlen ($folder));
				}
				sort ($retval);
			}

			return $retval;
		} else {
			return array ($folder);
		}
	}

	function calculateSize () {
		global $all_files;
		global $optional_components;

		$all_files = expandFolder ($_POST['install_from']);
		foreach ($all_files as $index => $all_file) {
			foreach ($optional_components as $name => $optional_component) {
				foreach ($optional_component['files'] as $component_file) {
					if (substr ($all_file, 0, strlen ($component_file)) == $component_file) {
						$stat = stat ($_POST['install_from'] . $all_file);
						$optional_components[$name]['size'] += $stat['size'];
						$optional_components[$name]['size_on_disk'] += ($stat['blksize'] * ceil ($stat['size'] / $stat['blksize']));
						array_push ($optional_components[$name]['all_files'], $all_file);
						unset ($all_files[$index]);
					}
				}
			}
		}

		$_POST['size'] = 0;
		$_POST['size_on_disk'] = 0;

		foreach ($all_files as $index => $all_file) {
			$stat = stat ($_POST['install_from'] . $all_file);
			$_POST['size'] += $stat['size'];
			$_POST['size_on_disk'] += ($stat['blksize'] * ceil ($stat['size'] / $stat['blksize']));
		}
	}

	$optional_components = array (
		'Documentation'=>array (
			'site'=>'http://gateway.toolshed51.com/docs/',
			'version'=>'',
			'size'=>0,
			'size_on_disk'=>0,
			'all_files'=>array (),
			'files'=>array (
					'/documentation'
				)
			), 
		'Smarty'=>array (
			'site'=>'http://smarty.php.net/', 
			'version'=>'2.5.0', 
			'size'=>0, 
			'size_on_disk'=>0, 
			'all_files'=>array (), 
			'files'=>array (
					'/addons/smarty', 
					'/htdocs_includes/smarty_examples', 
					'/library_includes/smarty_conf', 
					'/library_includes/smarty_templates'
				)
			), 
		'Treemenu'=>array (
			'site'=>'http://www.phpguru.org/treemenu.php', 
			'version'=>'2.0.2', 
			'size'=>0, 
			'size_on_disk'=>0, 
			'all_files'=>array (), 
			'files'=>array (
					'/addons/treemenu', 
					'/htdocs_includes/javascripts/TreeMenu.js', 
					'/htdocs_includes/images/treemenu_images'
				)
			), 
		'Overlib'=>array (
			'site'=>'http://www.bosrup.com/web/overlib/', 
			'version'=>'3.5.1',
			'size'=>0, 
			'size_on_disk'=>0, 
			'all_files'=>array (), 
			'files'=>array (
					'/addons/overlib', 
					'/htdocs_includes/javascripts/overlib_mini.js', 
					'/htdocs_includes/images/overlib'
				)
			), 
		'JPGraph'=>array (
			'site'=>'http://www.aditus.nu/jpgraph/', 
			'version'=>'1.12.2', 
			'size'=>0, 
			'size_on_disk'=>0, 
			'all_files'=>array (), 
			'files'=>array (
					'/addons/jpgraph', 
					'/htdocs_includes/jpgraph_examples'
				)
			), 
		'JPGraph TTF Fonts'=>array (
			'site'=>'', 
			'version'=>'', 
			'size'=>0, 
			'size_on_disk'=>0, 
			'all_files'=>array (), 
			'files'=>array (
					'/extras/jpgraph_fonts' 
				)
			), 
		'Browser Capability File'=>array (
			'site'=>'', 
			'version'=>'', 
			'size'=>0,
			'size_on_disk'=>0, 
			'all_files'=>array (),
			'files'=>array (
					'/addons/browscap.ini'
				)
			),
		'Original Source for Addons and Diffs'=>array (
			'site'=>'',
			'version'=>'',
			'size'=>0,
			'size_on_disk'=>0,
			'all_files'=>array (),
			'files'=>array (
					'/extras/original_addons'
				)
			)
		);

	// Verify input
	$error = '';

	if ($_GET['step'] == 1) {
		// Nothing to verify step 1
	} else if ($_GET['step'] == 2) {
		if (isset ($_POST['install_from'])) {
			if (is_dir ($_POST['install_from'])) {
				if (is_file ($_POST['install_from'] . '/VERSION')) {
					$version = file_get_contents ($_POST['install_from'] . '/VERSION');

					if (is_file ($_POST['install_from'] . '/LICENSE')) {
						$license = file_get_contents ($_POST['install_from'] . '/LICENSE');
					} else {
						$error .= 'LICENSE file missing! Maybe install_from is an invalid source<br>';
					}

				} else {
					$error .= 'VERSION file missing! Maybe install_from is an invalid source<br>';
				}
			} else {
				$error .= $_POST['install_from'] . ' is not a directory! install_from is an invalid source<br>';
			}
		} else {
			$error .= 'install_from not set!<br>';
		}

		if (isset ($_POST['install_to'])) {
			if (is_dir ($_POST['install_to'])) {
				if (is_writable ($_POST['install_to'])) {
				} else {
					$error .= $_POST['install_to'] . ' is not writable! install_to is an invalid destination<br>';
				}
			} else {
				$error .= $_POST['install_to'] . ' is not a directory! install_to is an invalid destination<br>';
			}
		} else {
			$error .= 'install_to not set!<br>';
		}
	} else if ($_GET['step'] == 3) {
		$version = file_get_contents ($_POST['install_from'] . '/VERSION');
		$license = file_get_contents ($_POST['install_from'] . '/LICENSE');

		if ((!isset ($_POST['license'])) || ($_POST['license'] != 'on')) {
			$error .= 'You must agree to the license.  If you do not, close this browser window and remove the source.<br>';
		}
	} else if ($_GET['step'] == 4) {
		calculateSize ();

		$_POST['install_size'] = $_POST['size'];
		$_POST['install_size_on_disk'] = $_POST['size_on_disk'];
		$_POST['install_file_count'] = count ($all_files);

		foreach ($optional_components as $name => $optional_component) {
			if ((isset ($_POST['component_' . $name])) && ($_POST['component_' . $name] == 'on')) {
				$_POST['install_size'] += $optional_component['size'];
				$_POST['install_size_on_disk'] += $optional_component['size_on_disk'];
				$_POST['install_file_count'] += count ($optional_component['all_files']);
			}
		}

		$freespace = disk_free_space ($_POST['install_to']);

		if ($freespace < $_POST['install_size_on_disk']) {
			$error .= 'Not enough free space to install chosen components to ' . $_POST['install_to'] . '<br>Free Space Left: ' . $freespace . '<br>Space Required: ' . $_POST['install_size_on_disk'] . '<br>';	
		}
	} else if ($_GET['step'] == 5) {
		calculateSize ();

		$_POST['install_size'] = $_POST['size'];
		$_POST['install_size_on_disk'] = $_POST['size_on_disk'];
		$_POST['install_file_count'] = count ($all_files);

		foreach ($optional_components as $name => $optional_component) {
			if ((isset ($_POST['component_' . $name])) && ($_POST['component_' . $name] == 'on')) {
				$_POST['install_size'] += $optional_component['size'];
				$_POST['install_size_on_disk'] += $optional_component['size_on_disk'];
				$_POST['install_file_count'] += count ($optional_component['all_files']);
			}
		}

		$phpversion = `php -v`;
	}

	if (!empty ($error)) {
		$_GET['step']--;
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en-US">
	<head>
		<title>phpBaseClasses - Setup</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

		<script type="text/javascript">
		function findObject (n, d) {
			var p, i, x;

			if (!d)
				d=document;

			if (!(x = d[n]) && d.all)
				x = d.all[n];

			for (i = 0; !x && i < d.forms.length; i++)
				x = d.forms[i][n];

			for (i = 0; !x && d.layers && i < d.layers.length; i++)
				x = findObject (n, d.layers[i].document);

			if (!x && d.getElementById)
				x = d.getElementById(n);

			return x;
		}
		</script>
	</head>
	<body bgcolor="#CCCCCC">
		<form method="post" action="setup.php?step=' . ($_GET['step'] + 1) . '">
		<table width="640" align="center" cellspacing="3" cellpadding="3" border="1">';

	if (!empty ($error)) {
		echo '
			<tr>
				<td colspan="2"><font color="red">ERROR: </font>' . $error . '</td>
			</tr>';
	}

	// Display new input
	if ($_GET['step'] == 1) {
		$install_from = $_SERVER['SCRIPT_FILENAME'];
		$install_from_pieces = explode ('/', $install_from);
		$install_from = implode ('/', array_slice ($install_from_pieces, 0, count ($install_from_pieces) - 2));

		$paths = explode (':', get_include_path ());

		echo '
			<tr>
				<td>Install From: </td>
				<td>' . $install_from . '<input type="hidden" name="install_from" value="' . $install_from . '"></td>
			</tr>
			<tr>
				<td>Install To: </td>
				<td>
					<select name="install_to">';

		foreach ($paths as $path) {
			if (substr ($path, 0, 1) == '/') {
				if ($_POST['install_to'] == $path) {
					echo '
						<option value="' . $path . '" selected>' . $path . '</option>';
				} else {
					echo '
						<option value="' . $path . '">' . $path . '</option>';
				}
			}
		}

		echo '
					</select>
					<br>
					These come from the php value include_path, if you would like to see more options, add the folder to your include_path
				</td>
			</tr>';
	} else if ($_GET['step'] == 2) {
		echo '
			<tr>
				<td colspan="2">Installing Version ' . $version . ' from ' . $_POST['install_from'] . ' to ' . $_POST['install_to'] . '</td>
			</tr>
			<tr>
				<td colspan="2">' . nl2br ($license) . '</td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="license"> I Agree to terms of the license</td>
			</tr>';
	} else if ($_GET['step'] == 3) {
		calculateSize ();

		echo '
			<tr>
				<td colspan="2" align="center">What to install</td>
			</tr>
			<tr>
				<td>Required Files</td>
				<td>' . round ($_POST['size'] / 1024) . 'K (' . round ($_POST['size_on_disk'] / 1024) . 'K on Disk)</td>
			</tr>';

		foreach ($optional_components as $name => $optional_component) {
			echo '
			<tr>
				<td><input type="checkbox" name="component_' . $name . '">' . $name . ' ' . $optional_component['version'] . ' ', (!empty ($optional_component['site'])) ? '<a href="' . $optional_component['site'] . '" target="_blank"><font size="-1">[ More Info ]</font></a>' : '', '</td>
				<td>' . round ($optional_component['size'] / 1024) . 'K (' . round ($optional_component['size_on_disk'] / 1024) . 'K on Disk)</td>
			</tr>';
		}
	} else if ($_GET['step'] == 4) {
		echo '
			<tr>
				<td colspan="2">Installation size ' . round ($_POST['install_size'] / 1024) . 'K (' . round ($_POST['install_size_on_disk'] / 1024) . 'K on Disk)</td>
			</tr>
			<tr>
				<td colspan="2">
					' . str_repeat ('<nobr>', 800) . '<br>
					Current File: <span id="currentFile" class="copy2">&nbsp;</span><br>
					<br>
					Progress: <span id="percentDone" class="copy2">&nbsp;</span><br>
				</td>
			</tr>';

		flush ();
	} else  if ($_GET['step'] == 5) {
		echo '
			<tr>
				<td colspan="2">Running Tests on the Installation</td>
			</tr>
			<tr>
				<td colspan="2">
					' . str_repeat ('<nobr>', 800) . '<br>
					Current Test: <span id="currentTest" class="copy2">&nbsp;</span><br>
					<br>
					Progress: <span id="percentDone" class="copy2">';

		if (substr ($phpversion, 0, 3) == 'PHP') {
			echo 'Please wait...';
		} else {
			echo 'PHP Executable not found!  Tests will not be run.';
		}

		echo '</span><br>
				</td>
			</tr>';

		flush ();
	}

	foreach ($_POST as $key => $value) {
		if ($key != 'submit') {
			echo '
			<input type="hidden" name="' . $key . '" value="' . $value . '">';
		}
	}

	echo '
			<tr>
				<td colspan="2" align="center"><input type="submit" name="submit" value="Next"></td>
			</td>
		</table>
		</form>';

	flush ();

	if ($_GET['step'] == 4) {
		$cmd = 'mkdir -p ' . $_SERVER['DOCUMENT_ROOT'] . '/includes/';
		`$cmd`;

		$current_file_count = 0;
		foreach ($all_files as $index => $all_file) {
			usleep (100);
			$current_file_count++;

			echo '
		<script type="text/javascript">
			x = findObject (\'currentFile\');
			x.innerHTML = \'' . $all_file . '\';
			x = findObject (\'percentDone\');
			x.innerHTML = \'' . round (($current_file_count / $_POST['install_file_count']) * 100) . '%\';
		</script>';

			flush ();

			$cmd = 'mkdir -p ' . substr ($_POST['install_to'] . $all_file, 0, strrpos ($_POST['install_to'] . $all_file, '/'));
			`$cmd`;

			clearstatcache ();
			if (!file_exists ($_POST['install_to'] . $all_file)) {
				$cmd = 'cp ' . $_POST['install_from'] . $all_file . ' ' . $_POST['install_to'] . $all_file;
				`$cmd`;
			} else {
				echo 'Refusing to Overwrite: ' . $_POST['install_to'] . $all_file . '<br>';
			}

			if (substr ($all_file, 0, 17) == '/library_includes') {
				if (!file_exists ($_SERVER['DOCUMENT_ROOT'] . '/includes' . substr ($all_file, 17))) {
					$cmd = 'mkdir -p ' . substr ($_SERVER['DOCUMENT_ROOT'] . '/includes' . substr ($all_file, 17), 0, strrpos ($_SERVER['DOCUMENT_ROOT'] . '/includes' . substr ($all_file, 17), '/'));
					`$cmd`;

					$cmd = 'cp ' . $_POST['install_from'] . $all_file . ' ' . $_SERVER['DOCUMENT_ROOT'] . '/includes' . substr ($all_file, 17);
					`$cmd`;
				} else {
					echo 'Refusing to Overwrite: ' . $_SERVER['DOCUMENT_ROOT'] . '/includes' . substr ($all_file, 17) . '<br>';
				}
			}

			if (substr ($all_file, 0, 16) == '/htdocs_includes') {
				if (!file_exists ($_SERVER['DOCUMENT_ROOT'] . substr ($all_file, 16))) {
					$cmd = 'mkdir -p ' . substr ($_SERVER['DOCUMENT_ROOT'] . substr ($all_file, 16), 0, strrpos ($_SERVER['DOCUMENT_ROOT'] . substr ($all_file, 16), '/'));
					`$cmd`;

					$cmd = 'cp ' . $_POST['install_from'] . $all_file . ' ' . $_SERVER['DOCUMENT_ROOT'] . substr ($all_file, 16);
					`$cmd`;
				} else {
					echo 'Refusing to Overwrite: ' . $_SERVER['DOCUMENT_ROOT'] . substr ($all_file, 16) . '<br>';
				}
			}
		}

		foreach ($optional_components as $name => $optional_component) {
			if ((isset ($_POST['component_' . $name])) && ($_POST['component_' . $name] == 'on')) {
				foreach ($optional_component['all_files'] as $all_file) {
					usleep (100);
					$current_file_count++;

					echo '
		<script type="text/javascript">
			x = findObject (\'currentFile\');
			x.innerHTML = \'' . $all_file . '\';
			x = findObject (\'percentDone\');
			x.innerHTML = \'' . round (($current_file_count / $_POST['install_file_count']) * 100) . '%\';
		</script>';

					flush ();

					$cmd = 'mkdir -p ' . substr ($_POST['install_to'] . $all_file, 0, strrpos ($_POST['install_to'] . $all_file, '/'));
					`$cmd`;
					$cmd = 'cp ' . $_POST['install_from'] . $all_file . ' ' . $_POST['install_to'] . $all_file;
					`$cmd`;
				}
			}
		}

		echo '
		<script type="text/javascript">
			x = findObject (\'percentDone\');
			x.innerHTML = \'100% DONE\';
		</script>';
	}

	if ($_GET['step'] == 5) {
		$fail_test_count = 0;
		foreach ($all_files as $index => $all_file) {
			if (substr ($all_file, 0, 13) == '/setup/tests/') {
				usleep (100);

				echo '
		<script type="text/javascript">
			x = findObject (\'currentTest\');
			x.innerHTML = \'' . $all_file . '\';
		</script>';

				flush ();

				$cmd = 'export DOCUMENT_ROOT=NA;';
				$cmd .= 'export SERVER_NAME=NA;';
				$cmd .= 'php -d include_path=' . $_POST['install_to'] . ' -f ' . $_POST['install_to'] . '/setup/test.php ' . $_POST['install_to'] . $all_file;
				$return = `$cmd`;

				if (substr ($return, strpos ($return, ' '), 4) == 'fail') {
					$fail_test_count++;
				}
			}
		}

		foreach ($optional_components as $name => $optional_component) {
			if ((isset ($_POST['component_' . $name])) && ($_POST['component_' . $name] == 'on')) {
				foreach ($optional_component['all_files'] as $all_file) {
					if (substr ($all_file, 0, 13) == '/setup/tests/') {
						usleep (100);

						echo '
		<script type="text/javascript">
			x = findObject (\'currentTest\');
			x.innerHTML = \'' . $all_file . '\';
		</script>';

						flush ();

						$cmd = 'export DOCUMENT_ROOT=NA;';
						$cmd .= 'export SERVER_NAME=NA;';
						$cmd .= 'php -d include_path=' . $_POST['install_to'] . ' -f ' . $_POST['install_to'] . '/setup/test.php ' . $_POST['install_to'] . $all_file;
						$return = `$cmd`;

						if (substr ($return, strpos ($return, ' '), 4) == 'fail') {
							$fail_test_count++;
						}
					}
				}
			}
		}

		echo '
		<script type="text/javascript">
			x = findObject (\'percentDone\');
			x.innerHTML = \'', ($fail_test_count == 0) ? 'All Tests Passed!' : $fail_test_count . ' Tests Failed', '\';
		</script>';
	}

	echo '
	</body>
</html>';

?>
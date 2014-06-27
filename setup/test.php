<?php
	$current_section = '';
	$sections = array ('--TEST--', '--SKIPIF--', '--GET--', '--POST--', '--FILE--', '--EXPECT--');
	$data = array ();
	$skip = false;

	if (file_exists ($argv[1])) {
		$handle = fopen ($argv[1], 'r');

		while (!feof ($handle)) {
			$buffer = fgets ($handle);

			if (in_array (substr ($buffer, 0, -1), $sections)) {
				$current_section = substr ($buffer, 0, -1);
				$data[$current_section] = '';
			} else {
				if (!empty ($current_section)) {
					$data[$current_section] .= $buffer;
				}
			}
		}

		fclose($handle);

		$data['--TEST--'] = substr ($data['--TEST--'], 0, -1);

		if (isset ($data['--TEST--'])) {
			echo $data['--TEST--'] . ': ';

			if (isset ($data['--SKIPIF--'])) {
				ob_start ();
				eval ($data['--SKIPIF--']);
				$output = ob_get_contents ();
				ob_end_clean ();

				if (substr ($output, 0, 4) == 'skip') {
					$skip = true;
					echo $output;
				}

				unset ($output);
			}

			if (isset ($data['--GET--'])) {
				foreach (explode ('&', $data['--GET--']) as $key => $val) {
					$_GET[$key] = $val;
				}
			}

			if (isset ($data['--POST--'])) {
				foreach (explode ('&', $data['--POST--']) as $key => $val) {
					$_POST[$key] = $val;
				}
			}

			if ((isset ($data['--FILE--'])) && (!$skip)) {
				ob_start ();
				eval ($data['--FILE--']);
				$output = ob_get_contents ();
				ob_end_clean ();

				if ($output == $data['--EXPECT--']) {
					echo 'pass';
				} else {
					echo "fail\n--OUTPUT--\n$output\n--EXPECTED--\n" . $data['--EXPECT--'];
				}
			} else {
				echo 'Test Code does not exist';
			}
		} else {
			echo 'Test name needs to be specified in --TEST-- section';
		}
	} else {
		echo 'Test file does not exist';
	}

	echo "\n";
?>
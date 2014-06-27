<?php
/*
 * $RCSfile
 *
 * phpBaseClasses - Foundation for any application in php
 * Copyright (C) 2002-2003 Russell Chadwick
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * @version $Revision: 1.13 $ $Date: 2004/06/03 08:12:27 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('FUNCTIONS')) {
        return;
} else {
	define('FUNCTIONS', 1);

	$mime_types = array (
		'ai'=>'application/postscript',
		'aif'=>'audio/x-aiff',
		'aifc'=>'audio/x-aiff',
		'aiff'=>'audio/x-aiff',
		'asc'=>'text/plain',
		'au'=>'audio/basic',
		'avi'=>'video/x-msvideo',
		'bcpio'=>'application/x-bcpio',
		'bin'=>'application/octet-stream',
		'bmp'=>'image/bmp',
		'class'=>'application/octet-stream',
		'chm'=>'application/octet-stream',
		'cpio'=>'application/x-cpio',
		'cpt'=>'application/mac-compactpro',
		'css'=>'text/css',
		'dcr'=>'application/x-director',
		'dir'=>'application/x-director',
		'dms'=>'application/octet-stream',
		'doc'=>'application/msword',
		'dvi'=>'application/x-dvi',
		'dxr'=>'application/x-director',
		'eps'=>'application/postscript',
		'exe'=>'application/octet-stream',
		'gif'=>'image/gif',
		'gtar'=>'application/x-gtar',
		'hdf'=>'application/x-hdf',
		'hqx'=>'application/mac-binhex40',
		'htm'=>'text/html',
		'html'=>'text/html',
		'ief'=>'image/ief',
		'jpe'=>'image/jpeg',
		'jpeg'=>'image/jpeg',
		'jpg'=>'image/jpeg',
		'js'=>'application/x-javascript',
		'kar'=>'audio/midi',
		'lha'=>'application/octet-stream',
		'lzh'=>'application/octet-stream',
		'mid'=>'audio/midi',
		'midi'=>'audio/midi',
		'mov'=>'video/quicktime',
		'movie'=>'video/x-sgi-movie',
		'mp2'=>'audio/mpeg',
		'mp3'=>'audio/mpeg',
		'mpeg'=>'video/mpeg',
		'mpe'=>'video/mpeg',
		'mpg'=>'video/mpeg',
		'mpga'=>'audio/mpeg',
		'pdf'=>'application/pdf',
		'pgn'=>'application/x-chess-pgn',
		'png'=>'image/png',
		'pps'=>'application/vnd.ms-powerpoint',
		'ppt'=>'application/vnd.ms-powerpoint',
		'ps'=>'application/postscript',
		'qt'=>'video/quicktime',
		'ra'=>'audio/x-realaudio',
		'ram'=>'audio/x-pn-realaudio',
		'rm'=>'audio/x-pn-realaudio',
		'rpm'=>'audio/x-pn-realaudio-plugin',
		'rtf'=>'text/rtf',
		'rtx'=>'text/richtext',
		'sit'=>'application/x-stuffit',
		'snd'=>'audio/basic',
		'spl'=>'application/x-futuresplash',
		'swf'=>'application/x-shockwave-flash',
		'tar'=>'application/x-tar',
		'texinfo'=>'application/x-texinfo',
		'texi'=>'application/x-texinfo',
		'tiff'=>'image/tiff',
		'tif'=>'image/tiff',
		'txt'=>'text/plain',
		'vcd'=>'application/x-cdlink',
		'vsd'=>'application/vnd.visio',
		'xls'=>'application/vnd.ms-excel',
		'wav'=>'audio/x-wav',
		'wbxml'=>'application/vnd.wap.wbxml',
		'wmlc'=>'application/vnd.wap.wmlc',
		'wmlsc'=>'application/vnd.wap.wmlscriptc',
		'zip'=>'application/zip'
	);

	function getAllMatches ($data, $begin, $end) {
		$count = 0;
		$retval = array ();

		$begin_pos = strpos ($data, $begin);
		$end_pos = strlen ($data);
		while (($begin_pos !== false) && ($end_pos !== false)) {
			if (empty ($end)) {
				$end_pos = strlen ($data);
			} else {
				$end_pos = strpos ($data, $end, $begin_pos + strlen ($begin));
			}

			if ($end_pos !== false) {
				$retval[$count] = substr ($data, ($begin_pos + strlen ($begin)), ($end_pos - $begin_pos - strlen ($begin)));
				$data = substr ($data, ($end_pos + strlen ($end)));
				$count++;
			}

			$begin_pos = strpos ($data, $begin);
		}

		return $retval;
	}

	function removeAllMatches ($data, $begin, $end) {
		$count = 0;

		$begin_pos = strpos ($data, $begin);
		$end_pos = 1;
		while (($begin_pos !== false) && ($end_pos !== false)) {
			$end_pos = strpos ($data, $end, $begin_pos + strlen ($begin));
			if ($end_pos !== false) {
				$data = substr ($data, 0, $begin_pos) . substr ($data, $end_pos + 1);
			}
			$begin_pos = strpos ($data, $begin);
		}

		return $data;
	}

	function matchAnyKeywords ($line, $keywords) {
		if (eregi (implode ('|', $keywords), $line)) {
			return true;
		} else {
			return false;
		}
	}

	function trimBetween ($buffer, $start, $end) {
		$begin = strpos ($buffer, $start);
		$buffer = substr ($buffer, $begin, (strpos ($buffer, $end, $begin) - $begin));

		return $buffer;
  	}

	function roundToNearest ($value, $to_nearest) {
		for ($testval = 1; $testval <= $to_nearest; $testval++) {
			if ((($value + $testval) % $to_nearest) == 0) {
				$retval = $value + $testval;
				break;
			} else if ((($value - $testval) % $to_nearest) == 0) {
				$retval = $value - $testval;
				break;
			}
		}

		return $retval;
	}

	function searchArrayByKeyVal ($array, $search_name, $name, $value) {
		$retval = '';

		if (is_array ($array)) {
			reset ($array);

			while (list ($count, $array2) = each ($array)) {
				if ($array2[$name] == $value) {
					$retval = $array2[$search_name];
				}
			}
		}

		return $retval;
	}

	function searchArrayForIndexByKeyVal ($array, $name, $value) {
		$retval = '';

		if (is_array ($array)) {
			reset ($array);

			while (list ($count, $array2) = each ($array)) {
				if ($array2[$name] == $value) {
					$retval = $count;
				}
			}
		}

		return $retval;
	}

	function crypt_random_md5_salt ($password) {
		$salt = '$1$';
		for ($c = 1; $c <= 5; $c++) $salt .= chr (mt_rand (65, 90));
		$salt .= '0';

		return crypt ($password, $salt);
	}

	function randomChars ($length) {
		$passwordChars = '0123456789' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . 'abcdefghijklmnopqrstuvwxyz';
		$retval = '';

		for ($index = 1; $index <= $length; $index++) {
			$randomNumber = rand (1, strlen ($passwordChars));
			$retval .= substr ($passwordChars, rand (1, strlen ($passwordChars)) - 1, 1);
		}

		return $retval;
	}

	function orNull ($data) {
		if (($data == '') && (!(is_numeric ($data) && ($data === 0)))) {
			return 'NULL';
		} else {
			return $data;
		}
	}

	function nullOn ($data, $test, $quote=true) {
		if ($data == $test) {
			return 'NULL';
		} else {
			if ($quote) {
				return "'$data'";
			} else {
				return $data;
			}
		}
	}

	function or0 ($data) {
		if ((empty ($data)) && (!(is_numeric ($data) && ($data === 0)))) {
			return '0';
		} else {
			return $data;
		}
	}

	function orX ($data, $x) {
		if (($data == '') && (!(is_numeric ($data) && ($data === 0)))) {
			return $x;
		} else {
			return $data;
		}
	}

	function scaleImage ($width, $height, $maxwidth, $maxheight) {
		if (($height > $maxheight) || ($width > $maxwidth)) {
			$height_ratio = ($maxheight / $height);
			$width_ratio = ($maxwidth / $width);

			if ($height_ratio < $width_ratio) {
				$height = round ($height * $height_ratio);
				$width = round ($width * $height_ratio);
			} else {
				$height = round ($height * $width_ratio);
				$width = round ($width * $width_ratio);
			}
		}

		return array ($width, $height, 'width="' . $width . '" height="' . $height . '"');
	}

	function stripTableFromColumnName ($column_name) {
		$pos = strpos ($column_name, '.');
 
		if ($pos !== false) 
			$column_name = substr ($column_name, $pos + 1);
 
		return $column_name;
	}

	function elementOf (&$array, $element) {
		return $array[$element];
	}

	function getLogDate () {
		return date ('Y-m-d H:i:s T');
	}

	function getApacheLogDate () {
		return date ('d/M/Y:H:i:s O');
	}
}
?>
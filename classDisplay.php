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
 * @version $Revision: 1.20 $ $Date: 2004/06/03 08:11:27 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSDISPLAY')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSDISPLAY', 1);

	/**
	 * Classes to assist in displaying data. This includes everything from 
	 * simple making text pretty to displaying complicated subselects.  See 
	 * security object admin tool for example of subselects.
	 *
	 * @package phpBaseClasses
	 */
	class Display {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Array of key=>value pairs for known browser safe colors
		 * Where key is the hex, and value is the name
		 *
		 * @var array $_safeColors
		 * @access private
		 */
		var $_safeColors;

		/**
		 * Constructor which populates the class members
		 *
		 * @access public
		 * @return void
		 */
		function Display () {
			$this->_version = 0.1;

			$this->_safeColors = array (
				'000000'=>'Black', '800000'=>'Maroon', '008000'=>'Green', '808000'=>'Olive', 
				'000080'=>'Navy', '800080'=>'Purple', '008080'=>'Teal', '808080'=>'Gray', 
				'C0C0C0'=>'Silver', 'FF0000'=>'Red', '00FF00'=>'Lime', 'FFFF00'=>'Yellow', 
				'0000FF'=>'Blue', 'FF00FF'=>'Fuchsia', '00FFFF'=>'Aqua', 'FFFFFF'=>'White',
				'F0F8FF'=>'Aliceblue', 'FAEBD7'=>'Antiquewhite', '7FFFD4'=>'Aquamarine',
				'F0FFFF'=>'Azure', 'F5F5DC'=>'Beige', '8A2BE2'=>'Blueviolet', 'A52A2A'=>'Brown',
				'DEB887'=>'Burlywood', '5F9EA0'=>'Cadetblue', '7FFF00'=>'Chartreuse',
				'D2691E'=>'Chocolate', 'FF7F50'=>'Coral', '6495ED'=>'Cornflowerblue',
				'FFF8DC'=>'Cornsilk', 'DC143C'=>'Crimson', '00008B'=>'Darkblue', '008B8B'=>'Darkcyan',
				'B8860B'=>'Darkgoldenrod', 'A9A9A9'=>'Darkgray', '006400'=>'Darkgreen',
				'BDB76B'=>'Darkkhaki', '8B008B'=>'Darkmagenta', '556B2F'=>'Darkolivegreen',
				'FF8C00'=>'Darkorange', '9932CC'=>'Darkorchid', '8B0000'=>'Darkred',
				'E9967A'=>'Darksalmon', '8FBC8F'=>'Darkseagreen', '483D8B'=>'Darkslateblue',
				'2F4F4F'=>'Darkslategray', '00CED1'=>'Darkturquoise', '9400D3'=>'Darkviolet',
				'FF1493'=>'Deeppink', '00BFFF'=>'Deepskyblue', '696969'=>'Dimgray',
				'1E90FF'=>'Dodgerblue', 'B22222'=>'Firebrick', 'FFFAF0'=>'Floralwhite',
				'228B22'=>'Forestgreen', 'DCDCDC'=>'Gainsboro', 'F8F8FF'=>'Ghostwhite',
				'FFD700'=>'Gold', 'DAA520'=>'Goldenrod', 'ADFF2F'=>'Greenyellow',
				'F0FFF0'=>'Honeydew', 'FF69B4'=>'Hotpink', 'CD5C5C'=>'Indianred',
				'4B0082'=>'Indigo', 'FFFFF0'=>'Ivory', 'F0E68C'=>'Khaki', 'E6E6FA'=>'Lavender',
				'FFF0F5'=>'Lavenderblush', '7CFC00'=>'Lawngreen', 'FFFACD'=>'Lemonchiffon',
				'ADD8E6'=>'Lightblue', 'F08080'=>'Lightcoral', 'E0FFFF'=>'Lightcyan',
				'FAFAD2'=>'Lightgoldenrodyellow', '90EE90'=>'Lightgreen', 'D3D3D3'=>'Lightgray',
				'FFB6C1'=>'Lightpink', 'FFA07A'=>'Lightsalmon', '20B2AA'=>'Lightseagreen',
				'87CEFA'=>'Lightskyblue', '778899'=>'Lightslategray', 'B0C4DE'=>'Lightsteelblue',
				'006666'=>'Lightteal', 'FFFFE0'=>'Lightyellow', '32CD32'=>'Limegreen', 'FAF0E6'=>'Linen',
				'66CDAA'=>'Mediumaquamarine', '0000CD'=>'Mediumblue', 'BA55D3'=>'Mediumorchid',
				'9370D0'=>'Mediumpurple', '3CB371'=>'Mediumseagreen', '7B68EE'=>'Mediumslateblue',
				'00FA9A'=>'Mediumspringgreen', '48D1CC'=>'Mediumturquoise', 'C71585'=>'Mediumvioletred',
				'191970'=>'Midnightblue', 'F5FFFA'=>'Mintcream', 'FFE4E1'=>'Mistyrose',
				'FFE4B5'=>'Moccasin', 'FFDEAD'=>'Navajowhite', 'FDF5E6'=>'Oldlace', '6B8E23'=>'Olivedrab',
				'FFA500'=>'Orange', 'FF4500'=>'Orangered', 'DA70D6'=>'Orchid', 'EEE8AA'=>'Palegoldenrod',
				'98FB98'=>'Palegreen', 'AFEEEE'=>'Paleturquoise', 'DB7093'=>'Palevioletred',
				'FFEFD5'=>'Papayawhip', 'FFDAB9'=>'Peachpuff', 'CD853F'=>'Peru', 'FFC0CB'=>'Pink',
				'DDA0DD'=>'Plum', 'B0E0E6'=>'Powderblue', 'BC8F8F'=>'Rosybrown', '4169E1'=>'Royalblue',
				'8B4513'=>'Saddlebrown', 'FA8072'=>'Salmon', 'F4A460'=>'Sandybrown', '2E8B57'=>'Seagreen',
				'FFF5EE'=>'Seashell', 'A0522D'=>'Sienna', '87CEEB'=>'Skyblue', '6A5ACD'=>'Slateblue',
				'708090'=>'Slategray', 'FFFAFA'=>'Snow', '00FF7F'=>'Springgreen', '4682B4'=>'Steelblue',
				'D2B48C'=>'Tan', 'D8BFD8'=>'Thistle', 'FF6347'=>'Tomato', '40E0D0'=>'Turquoise',
				'EE82EE'=>'Violet', 'F5DEB3'=>'Wheat', 'F5F5F5'=>'Whitesmoke', '9ACD32'=>'Yellowgreen'
			);
		}

		/**
		 * Makes HTML for everything from <html> to <body>
		 *
		 * @access public
		 * @param string Title of the document, if there is a config setting this is appended to it
		 * @param array Options to apply to the head
		 * @return string HTML for everything from <html> to <body>
		 */
		function head ($title='', $options='') {
			global $config, $SKINS, $GENERATE_CACHE, $CLI;

			$defaults = array (	'CONTENT_TYPE'=>$config['CONTENT_TYPE'], 
							'KEYWORDS'=>$config['KEYWORDS'], 
							'BACKGROUND'=>$config['BACKGROUND'], 
							'DESCRIPTION'=>$config['DESCRIPTION'], 
							'HOSTED_BY'=>$config['HOSTED_BY'], 
							'STYLESHEETS'=>$config['STYLESHEETS'], 
							'JAVASCRIPTS'=>$config['JAVASCRIPTS']
					);
			$options = populate_defaults ($options, $defaults);

			if (@isset ($SKINS[$_SERVER['SERVER_NAME']])) {
				$options = populate_defaults ($SKINS[$_SERVER['SERVER_NAME']], $options);
			}

			$already_seen_headers = array ();
			if (count ($config['HEADERS']) > 0) {
				foreach ($config['HEADERS'] as $header) {
					list ($begin_header) = explode (':', $header);
					$replace = true;
					if (in_array ($begin_header, $already_seen_headers)) {
						$replace = false;
					}
					header ($header, $replace);
					array_push ($already_seen_headers, $begin_header);
				}
			} else {
				debug ('No Headers have been defined in the config file', 3);
			}

			if ((!$config['CACHING_ENABLED']) || (!$GENERATE_CACHE)) {
				ob_end_flush ();
			}

			$retval = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>' . $config['TITLE_PREFIX'] . $title . '</title>
		<meta http-equiv="Content-Type" content="' . $options['CONTENT_TYPE'] . '" />';

			if (!$CLI) {
				$retval .= '
		<meta name="Request-URI" content="' . $_SERVER['REQUEST_URI'] . '" />
		<meta name="Served-By" content="' . $_SERVER['SERVER_NAME'] . '" />';
			}

			$retval .= '
		<meta name="Keywords" content="' . $options['KEYWORDS'] . '" />
		<meta name="Description" content="' . $options['DESCRIPTION'] . '" />
		<meta name="Hosted-By" content="' . $options['HOSTED_BY'] . '" />';

			if (isset ($NO_CACHE)) {
				$retval .= '
		<meta name="expires" content="Fri, Jun 12 1981 08:20:00 GMT" />
		<meta name="pragma" content="no-cache" />
		<meta name="cache-control" content="no-cache" />';
			}

			if (count ($options['STYLESHEETS']) > 0) {
				foreach ($options['STYLESHEETS'] as $stylesheet) {
					$retval .= '
		<link rel="stylesheet" type="text/css" href="' . $stylesheet . '" />';
				}
			}

			if ((isset ($config['TREEMENU_ENABLED'])) && ($config['TREEMENU_ENABLED'])) {
				array_push ($options['JAVASCRIPTS'], '/includes/javascripts/sniffer.js');
				array_push ($options['JAVASCRIPTS'], '/includes/javascripts/TreeMenu.js');
			}

			if (count ($options['JAVASCRIPTS']) > 0) {
				foreach ($options['JAVASCRIPTS'] as $javascript) {
					$retval .= '
		<script type="text/javascript" src="' . $javascript . '"></script>';
				}
			}

			$retval .= '
	</head>
	<body';

			if (!empty ($options['ONLOAD'])) {
				$retval .= ' onLoad="' . $options['ONLOAD'] . '"';
			}

			$retval .= '>';

			if ((isset ($config['OVERLIB_ENABLED'])) && ($config['OVERLIB_ENABLED'])) {
				array_push ($options['JAVASCRIPTS'], '/includes/javascripts/overlib_mini.js');
				$retval .= '
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<script type="text/javascript" src="/includes/javascripts/overlib_mini.js">
		</script>';
			}

			if (!isset ($config['SKIP_EXTRA_HEADER'])) {
				if (!empty ($config['EXTRA_HEADER_PATH'])) {
					if (!@include ($config['EXTRA_HEADER_PATH'])) {
						debug ('Extra Header Not Found. Please unset the variable <b>EXTRA_HEADER_PATH</b> in config.php or create the file: ' . $config['EXTRA_HEADER_PATH'], 2);
					}

					if (isset ($EXTRA_HEADER)) {
						$retval .= $EXTRA_HEADER;
					}
				}
			}

			session_write_close ();

			return $retval;
		}

		/**
		 * Makes HTML for a minimal head so processing can still happen but it
		 * will not generate any display
		 *
		 * @access public
		 * @return void
		 */
		function minimalHead () {
			global $config, $GENERATE_CACHE;

			if ((!$config['CACHING_ENABLED']) || (!$GENERATE_CACHE)) {
				if (ob_get_contents () > '') {
					ob_end_flush ();
				}
			}

			if (!isset($config['SKIP_EXTRA_HEADER'])) {
				if (!empty($config['EXTRA_HEADER_PATH'])) {
					if (!@include ($config['EXTRA_HEADER_PATH'])) {
						debug ('Extra Header Not Found. Please unset the variable <b>EXTRA_HEADER_PATH</b> in config.php or create the file: ' . $config['EXTRA_HEADER_PATH'], 2);
					}
				}
			}

			session_write_close ();
		}

		/**
		 * Replacement for using a head function when your php is creating something 
		 * to be treated as a file 
		 *
		 * @access public
		 * @return void
		 */
		function poseAsDocument ($type, $filename) {
			global $config, $GENERATE_CACHE;

			if ((!$config['CACHING_ENABLED']) || (!$GENERATE_CACHE)) {
				if (ob_get_contents () > '') {
					ob_end_flush ();
				}
			}

			if (!headers_sent ($file, $line)) {
				session_cache_limiter ('public'); 

				header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT'); // always modified
				header ('Accept-Ranges: bytes');
				header ('Content-type: ' . $type);
				header ('Content-Disposition: inline; filename="' . htmlentities (basename ($filename)) . '"');
				header ('Content-Length: ' . filesize ($filename));
				header ('Cache-Control: no-store, no-cache, must-revalidate');	// https/1.1
				header ('Cache-Control: post-check=0, pre-check=0', false);
				header ('Pragma: public');
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');		// Date in the past
				header ('Content-Transfer-Encoding: binary');
			} else {
				debug ('Headers already sent', 1);
			}

			session_write_close ();
		}

		/**
		 * Makes HTML for end of document from </body>
		 *
		 * @access public
		 * @return string HTML for end of document from </body>
		 */
		function foot () {
			global $config;

			$retval = '';

			if (!isset($config['SKIP_EXTRA_HEADER'])) {
				if (!empty($config['EXTRA_FOOTER_PATH'])) {
					if (!@include ($config['EXTRA_FOOTER_PATH'])) {
						debug ('Extra Footer Not Found. Please unset the variable <b>EXTRA_FOOTER_PATH</b> in config.php or create the file: ' . $config['EXTRA_FOOTER_PATH'], 2);
					}

					if (isset ($EXTRA_FOOTER)) {
						$retval .= $EXTRA_FOOTER;
					}
				}
			}

			debug ('Html Check -- <a href="http://validator.w3.org/check/referer" target="w3c_html">Check HTML using W3C Validator</a>', 3);

			$retval .= '
	</body>
</html>';

			return $retval;
		}

		/**
		 * Makes HTML for a minimal foot so processing can still happen but it
		 * will not generate any display
		 *
		 * @access public
		 * @return void
		 */
		function minimalFoot () {
			global $config;

			if (!isset($config['SKIP_EXTRA_HEADER'])) {
				if (!empty($config['EXTRA_FOOTER_PATH'])) {
					if (!@include ($config['EXTRA_FOOTER_PATH'])) {
						debug ('Extra Footer Not Found. Please unset the variable <b>EXTRA_FOOTER_PATH</b> in config.php or create the file: ' . $config['EXTRA_FOOTER_PATH'], 2);
					}
				}
			}
		}

		/**
		 * If text is empty, returns a non breaking space.  Useful in 
		 * table cells so their borders display.  Otherwise returns the 
		 * original text.
		 *
		 * @access public
		 * @param string text to evaluate
		 * @return string new text
		 */
		function orNbsp ($text) {
			if (empty($text) && ($text != '0')) {
				return '&nbsp;';
			} else {
				return $text;
			}
		}

		/**
		 * Escapes variables from a post operation
		 *
		 * @access public
		 * @param string text to evaluate
		 * @return string new text
		 */
		function escapePostVar ($text) {
			$text = str_replace ("\\\"", "\"", $text);
			$text = str_replace ("\\'", "'", $text);
			$text = str_replace ("\\\\\"", "\"", $text);
			$text = str_replace ("\\\\'", "'", $text);

			return $text;
		}

		/**
		 * Makes a table row with a prompt to the left and a input to the right
		 *
		 * @access public
		 * @param string prompt html for left side
		 * @param string input html for right side
		 * @return string HTML for table row
		 */
		function inputFormRow ($prompt, $body) {
			$retval = '
				<tr>
					<td align="right">
						' . $prompt . ' &nbsp;
					</td>
					<td>
						$body
					</td>
				</tr>';

			return $retval;
		}

		/**
		 * Finds the hex value for a named color.  Returns hex for light gray on
		 * failure.
		 *
		 * @access public
		 * @param string name of a color
		 * @return string hex value of a color
		 */
		function getColorHex ($search_name) {
			foreach ($this->_safeColors as $hex=>$name) {
				if ($search_name == $name) {
					return $hex;
				}
			}

			return 'D3D3D3';
		}

		/**
		 * Finds the name of a color for a given hex value.  Returns 
		 * Lightgray failure.
		 *
		 * @access public
		 * @param string hex value of a color
		 * @return string name of a color
		 */
		function getColorName ($search_hex) {
			foreach ($this->_safeColors as $hex=>$name) {
				if ($search_hex == $hex) {
					return $name;
				}
			}

			return 'Lightgray';
		}

		/**
		 * Makes text such as a column name into a displayable name
		 *
		 * @access public
		 * @param string name of a column
		 * @return string displayable name
		 */
		function makeReadable ($text) {
			$text = str_replace ('Id', 'ID', ucwords (str_replace ('_', ' ', $text)));
			if (substr ($text, -3) == ' ID') {
				$text = substr ($text, 0, -3);
			}
			return $text;
		}

		/**
		 * Takes a string to be used inside javascript code and 
		 * escapes anything that will break the syntax of the javascript
		 *
		 * @access public
		 * @param string original text
		 * @return string escaped text
		 */
		function escapeJsString ($text) {
			$text = str_replace ("'", '\x27', $text);
			$text = str_replace ('"', '\x22', $text);
			$text = str_replace (',', '\x2C', $text);
			$text = str_replace (' ', '\x20', $text);
			$text = str_replace ('#', '\x23', $text);
			$text = str_replace ("\r\n", '<br>', $text);
			$text = str_replace ("\r", '<br>', $text);
			$text = str_replace ("\n", '<br>', $text);

			return $text;
		}

		/**
		 * Creates code to call a javascript in place
		 * 
		 * @access public
		 * @param string Name of javascript function
		 * @param string Parameters to pass to javascript function
		 * @return string Code to call a javascript in place
		 */
		function callJS ($function, $parameters='') {
			return '<script type="text/javascript">' . $function . ' (' . $parameters . ')</script>';
		}

		/** 
		 * Creates call to javascript to open a new window
		 * @access public
		 * @param string URL of the new window
		 * @param string name of the new window
		 * @param string features of the new window (ex. toolbar=no,status=no,scrollbars=no,location=no,menubar=no,directories=no,width=640,height=480)
		 * @return Code to call a javascript to open a new window
		 */
		function openNewWindow ($URL, $name, $features) {
			return $this->callJS ('openNewWindow', '"' . $this->escapeJsString ($URL) . '", "' . $this->escapeJsString ($name) . '", "' . $this->escapeJsString ($features) . '"');
		}

		/**
		 * Creates call to javascript to close current browser window
		 * 
		 * @access public
		 * @return string Call to javascript to close current browser window
		 */
		function closeWindow () {
			return $this->callJS ('closeWindow');
		}

		/**
		 * Creates call to javascript to redirect parent of current browser window
		 * 
		 * @access public
		 * @param string Where to redirect parent
		 * @return string Call to javascript to redirect parent of current browser window
		 */
		function redirectParent ($URL) {
			return $this->callJS ('redirectParent', '"' . $this->escapeJsString ($URL) . '"');
		}

		/**
		 * Creates call to javascript to reload parent of current browser window
		 * 
		 * @access public
		 * @return string Call to javascript to reload parent of current browser window
		 */
		function reloadParent () {
			return $this->callJS ('reloadParent');
		}

		/**
		 * Creates call to javascript to reload parent of current browser window and close current browser window
		 * 
		 * @access public
		 * @param string Where to redirect parent
		 * @return string Call to javascript to reload parent of current browser window and close current browser window
		 */
		function redirectParentAndClose ($URL) {
			return $this->callJS ('redirectParentAndClose', '"' . $this->escapeJsString ($URL) . '"');
		}

		/**
		 * Creates call to javascript to reload parent of current browser window and close current browser window
		 * 
		 * @access public
		 * @return string Call to javascript to reload parent of current browser window and close current browser window
		 */
		function reloadParentAndClose () {
			return $this->callJS ('reloadParentAndClose');
		}

		/**
		 * Creates call to javascript to change where a form submits
		 * 
		 * @access public
		 * @param string Name of the form
		 * @param string New URL where form submits
		 * @return string Call to javascript to change where a form submits
		 */
		function setFormAction ($name, $action) {
			return $this->callJS ('setFormAction', '"' . $this->escapeJsString ($name) . '", "' . $this->escapeJsString ($action) . '"');
		}

		/**
		 * Creates call to javascript to submit a form
		 * 
		 * @access public
		 * @param string Name of the form 
		 * @return string Call to javascript to submit a form
		 */
		function submitForm ($name) {
			return $this->callJS ('submitForm', '"' . $this->escapeJsString ($name) . '"');
		}

		/**
		 * Creates html to redirect current window in given amount of time
		 *
		 * @access public
		 * @param integer Number of seconds to redirect in
		 * @param string URL to redirect current window to
		 * @return string HTML to redirect current window in given amount of time
		 */
		function redirectWindow ($url, $seconds=0) {
			return '<meta http-equiv="refresh" content="' . $seconds . '; URL=' . $url . '">';
		}

		/**
		 * Creates html to refresh current window in given amount of time
		 *
		 * @access public
		 * @param integer Number of seconds to refresh in
		 * @return string HTML to refresh current window in given amount of time
		 */
		function refreshWindow ($seconds=0) {
			return $this->redirectWindow ($_SERVER['REQUEST_URI'], $seconds);
		}

		/**
		 * Takes an array from $db->doQuery and automatically draws an HTML
		 * table based on it
		 *
		 * @access public
		 * @param array from $db->doQuery
		 * @return string HTML of a table
		 */
		function makeDisplayAllRows ($data) {
			$table_guts = '';

			$retval = "
		<table cellspacing=\"5\">
			<tr>";

			if (is_array ($data[1])) {
				foreach ($data as $key=>$data_item) {
					$table_header = "";
					$table_guts .= "
			<tr>";

					foreach ($data_item as $name=>$value) {
						$table_header .= "
				<td>
					" . $this->makeReadable ($name) . "
				</td>";

						$table_guts .= "
				<td>
					" . $value . "
				</td>";
					}

					$table_guts .= "
			</tr>";
				}

				$retval .= "$table_header
			</tr>" . $table_guts;
			} else {
				foreach ($data as $name=>$value) {
					$retval .= "
			<tr>
				<td>
					" . $this->makeReadable ($name) . "
				</td>
				<td>
					" . $value . "
				</td>
			</tr>";
				}
			}

			return $retval . "
		</table>";
		}

		/**
		 * Displays a Yes or No based on given data
		 * table based on it
		 *
		 * @access public
		 * @param string given data
		 * @return string coverted data
		 */
		function displayTF ($data) {
			switch ($data) {
				case 'No':
				case 'N':
				case 'f':
					return 'No';
					break;
				case 'Yes':
				case 'Y':
				case 't':
					return 'Yes';
					break;
			}
		}

		/**
		 * Popups new window
		 * 
		 * @access public
		 * @param string URL of the link
		 * @param string Name of window
		 * @param string Features of the new window
		 * @return string Code to popup new window
		 */
		function popupNewWindow ($url, $name, $features='') {
			return $this->openNewWindow ($url, $name, $features);;
		}

		/**
		 * Popups new window that is very basic
		 *
		 * @access public
		 * @param string URL of the link
		 * @param string Name of window
		 * @param integer Width of the new window
		 * @param integer Height of the new window
		 * @return string Code to popup new window
		 */
		function popupNewPlainWindow ($url, $name, $width=640, $height=480) {
			$features = 'toolbar=no,status=no,scrollbars=no,location=no,menubar=no,directories=no,width=' . $width . ',height=' . $height;
			return $this->popupNewWindow ($url, $name, $features);
		}

		/**
		 * Popups new window that is very basic with scrolling
		 *
		 * @access public
		 * @param string URL of the link
		 * @param string Name of window
		 * @param integer Width of the new window
		 * @param integer Height of the new window
		 * @return string Code to popup new window
		 */
		function popupNewScrollingWindow ($url, $name, $width=640, $height=480) {
			$features = 'toolbar=no,status=no,scrollbars=yes,location=no,menubar=no,directories=no,width=' . $width . ',height=' . $height;
			return $this->popupNewWindow ($url, $name, $features);
		}

		/**
		 * Popups new window that is full featured
		 *
		 * @access public
		 * @param string URL of the link
		 * @param string Name of window
		 * @param integer Width of the new window
		 * @param integer Height of the new window
		 * @return string Code to popup new window
		 */
		function popupNewFullWindow ($url, $name, $width=640, $height=480) {
			$features = 'toolbar=yes,status=yes,scrollbars=yes,location=yes,menubar=yes,directories=yes,width=' . $width . ',height=' . $height;
			return $this->popupNewWindow ($url, $name, $features);
		}

		/**
		 * Makes a link to popup a new window with the given parameters
		 *
		 * @access public
		 * @param string Body of text for the link
		 * @param string URL of the link
		 * @param string Name of window
		 * @param string Features of the new window
		 * @param string Name of stylesheet class to use
		 * @return string HTML for link to new window
		 */
		function getNewWindowLink ($text, $url, $name, $features='', $class='') {
			return "<a href=\"javascript:void(0);\" class=\"$class\" onClick=\"openNewWindow ('" . $this->escapeJsString ($url) . "', '$name', '$features')\">$text</a>";
		}

		/**
		 * Makes a link to popup a new window that is very basic
		 *
		 * @access public
		 * @param string Body of text for the link
		 * @param string URL of the link
		 * @param string Name of window
		 * @param integer Width of the new window
		 * @param integer Height of the new window
		 * @param string Name of stylesheet class to use
		 * @return string HTML for link to new window
		 */
		function getNewPlainWindowLink ($text, $url, $name, $width=640, $height=480, $class='') {
			$features = 'toolbar=no,status=no,scrollbars=no,location=no,menubar=no,directories=no,width=' . $width . ',height=' . $height;
			return $this->getNewWindowLink ($text, $url, $name, $features, $class);
		}

		/**
		 * Makes a link to popup a new window that is very basic with scrolling
		 *
		 * @access public
		 * @param string Body of text for the link
		 * @param string URL of the link
		 * @param string Name of window
		 * @param integer Width of the new window
		 * @param integer Height of the new window
		 * @param string Name of stylesheet class to use
		 * @return string HTML for link to new window
		 */
		function getNewScrollingWindowLink ($text, $url, $name, $width=640, $height=480, $class='') {
			$features = 'toolbar=no,status=no,scrollbars=yes,location=no,menubar=no,directories=no,width=' . $width . ',height=' . $height;
			return $this->getNewWindowLink ($text, $url, $name, $features, $class);
		}

		/**
		 * Makes a link to popup a new window that is full featured
		 *
		 * @access public
		 * @param string Body of text for the link
		 * @param string URL of the link
		 * @param string Name of window
		 * @param integer Width of the new window
		 * @param integer Height of the new window
		 * @param string Name of stylesheet class to use
		 * @return string HTML for link to new window
		 */
		function getNewFullWindowLink ($text, $url, $name, $width=640, $height=480, $class='') {
			$features = 'toolbar=yes,status=yes,scrollbars=yes,location=yes,menubar=yes,directories=yes,width=' . $width . ',height=' . $height;
			return $this->getNewWindowLink ($text, $url, $name, $features, $class);
		}

		/**
		 * Makes a link using the given parameters
		 * From php.net
		 *
		 * @access public
		 * @param string destination for the link
		 * @param string text for the link, leave blank for text to be the url
		 * @param string target for the link
		 * @param string extra tags for the link
		 * @return void
		 */
		function make_link ($url, $linktext=false, $target=false, $extras=false) {
			return sprintf ("<a href=\"%s\"%s%s>%s</a>", $url,
				($target ? ' target="' . $target . '"' : ''),
				($extras ? ' ' . $extras : ''),
				($linktext ? $linktext : $url)
			);
		}

		// From php.net
		function make_popup_link ($url, $linktext=false, $target=false, $windowprops="", $extras=false) {
			return sprintf ("<a href=\"%s\" target=\"%s\" onclick=\"window.open('%s','%s','%s');return false;\"%s>%s</a>",
				htmlspecialchars($url),
				($target ? $target : "_new"),
				htmlspecialchars($url),
				($target ? $target : "_new"),
		                $windowprops,
				($extras ? ' '.$extras : ''),
				($linktext ? $linktext : $url)
			);
		}

		// From php.net
		function hdelim ($color="#000000") {
			if (!$color) {
				return '<hr noshade size="1" />';
			}

			return sprintf ('<hr noshade size="1" color="%s" />', $color);
		}

		// From php.net
		function delim ($color=false) {
			if (!$color) {
				return ' | ';
			}

			return sprintf ('<font color="%s"> | </font>', $color);
		}

		function makeSubSelect ($select_values, $name_of_select, $name_of_subselect, $name_of_form, $select_curval = '', $subselect_curval = '', $use_ids = false) {
			$retval['javascript_declaration'] = '
<script type="text/javascript">
<!--
function populate_' . $name_of_subselect . '(curval) {
  var subselect = new Array();
  var selectedItem = new String;
  var populateWith = new Array();
  var selectedOption = 0;';

			reset ($select_values);
			while (list ($key, $val) = each ($select_values)) {
				$retval['javascript_declaration'] .= "
  subselect['" . $key . "'] = '" . $val . "';";
			}

			$retval['javascript_declaration'] .= '
  document.' . $name_of_form . '.' . $name_of_subselect . '.length = 0;
  selectedItem = document.' . $name_of_form . '.' . $name_of_select . '.options[document.' . $name_of_form . '.' . $name_of_select . ".selectedIndex].text;
  populateWith = subselect[selectedItem].split('¡');";

			if ($use_ids == true) {
				$retval['javascript_declaration'] .= '
  for (var i=0; i < populateWith.length; i = i+2) {
    if (populateWith[i] == curval) selectedOption = (i / 2);
    document.' . $name_of_form . '.' . $name_of_subselect . '.options[i/2] = new Option (populateWith[i+1], populateWith[i], false, false);
  }';
			} else {
				$retval['javascript_declaration'] .= '
  for (var i=0; i < populateWith.length; i++) {
    if (populateWith[i] == curval) selectedOption = (i - 1);
    document.' . $name_of_form . '.' . $name_of_subselect . '.options[i] = new Option (populateWith[i], populateWith[i], false, false);
  }';
			}

			$retval['javascript_declaration'] .= '
  document.' . $name_of_form . '.' . $name_of_subselect . '.options[selectedOption].selected = true;
  return 0;
}
//-->
</script>';

			$retval['select_html'] = '
    <select name="' . $name_of_select . '" onChange="populate_' . $name_of_subselect . '()">';

			reset ($select_values);
			while (list ($key, $val) = each ($select_values)) {
				if ($key == $select_curval) {
					$retval['select_html'] .= '
      <option value="' . $key . '" selected>' . $key . '</option>';
				} else {
					$retval['select_html'] .= '
      <option value="' . $key . '">' . $key . '</option>';
				}
			}

			$retval['select_html'] .= '
    </select>';

			$retval['sub_select_html'] = '
    <select name="' . $name_of_subselect . '">
      <option value=""></option>
    </select>';

			$retval['javascript'] = '
<script language="JavaScript">
<!--
  populate_' . $name_of_subselect . "('" . $subselect_curval . "');
//-->
</script>";

			return $retval;
		}

		function makeDBSubSelect ($query_parameters, $name_of_select, $name_of_subselect, $name_of_form, $select_curval = '', $subselect_curval = '', $use_ids = false, $show_all = false) {
			global $db;

			$query = 'SELECT ' . $query_parameters['SELECT_ID'] . ', ' . $query_parameters['SELECT_NAME'] . '
				FROM ' . $query_parameters['SELECT_TABLE'];

			if (!empty ($query_parameters['SELECT_WHERE'])) {
				$query .= '
				WHERE ' . $query_parameters['SELECT_WHERE'];
			}

			$query .= '
				ORDER BY ' . $query_parameters['SELECT_NAME'];

			$select_count = $db->doQuery ($query, $select_data);

			$pass_data = array ();
			for ($index = 1; $index <= $select_count; $index++) {
				$include = true;

				if (function_exists ('makeDBSubSelect_checkSelect')) {
					$include = makeDBSubSelect_checkSelect ($select_data[$index][$query_parameters['SELECT_ID']], $select_data[$index][$query_parameters['SELECT_NAME']]);
				}

				if ($include) {
					$query = 'SELECT ' . $query_parameters['SUB_SELECT_ID'] . ', ' . $query_parameters['SUB_SELECT_NAME'] . '
						FROM ' . $query_parameters['SUB_SELECT_TABLE'];

					if (!empty ($query_parameters['SUB_SELECT_WHERE_NAME'])) {
						$query .= '
						WHERE ' . $query_parameters['SUB_SELECT_WHERE_NAME'] . " = '" . eval ($query_parameters['SUB_SELECT_WHERE_VALUE']) . "'";
					}

					$query .= '
						ORDER BY ' . $query_parameters['SUB_SELECT_NAME'];

					$sub_select_count = $db->doQuery ($query, $sub_select_data);

					if ($show_all) {
						$current_list = '¡Any ' . $select_data[$index][$query_parameters['SELECT_NAME']] . '¡';
					} else {
						$current_list = '';
					}

					for ($index2 = 1; $index2 <= $sub_select_count; $index2++) {
						$include = true;

						if (function_exists ('makeDBSubSelect_checkSubSelect')) {
							$include = makeDBSubSelect_checkSubSelect ($sub_select_data[$index2][stripTableFromColumnName ($query_parameters['SUB_SELECT_ID'])], $sub_select_data[$index2][$query_parameters['SUB_SELECT_NAME']]);
						}

						if ($include) {
							$current_list .= $this->escapeJsString ($sub_select_data[$index2][stripTableFromColumnName ($query_parameters['SUB_SELECT_ID'])]) . '¡' . $this->escapeJsString ($sub_select_data[$index2][$query_parameters['SUB_SELECT_NAME']]) . '¡';
						}
					}

					$current_list = substr ($current_list, 0, -1);
					$pass_data[$select_data[$index][$query_parameters['SELECT_NAME']]] = $current_list;
				}
			}

			debugArray ('Passing Data to makeSubSelect', $pass_data, 3);
			$retval = $this->makeSubSelect ($pass_data, $name_of_select, $name_of_subselect, $name_of_form, $select_curval, $subselect_curval, $use_ids);

			return $retval;
		}

		function makeMultipleSubSelect ($select1_values, $select2_values, $name_of_select, $name_of_subselect1, $name_of_subselect2, $name_of_form, $select_curval = '', $subselect1_curval = '', $subselect2_curval = '', $use_ids = false) {
			$retval['javascript_declaration'] = "
<script type=\"text/javascript\">
<!--
function populate_" . $name_of_subselect1 . "(curval) {
  var subselect1 = new Array();
  var selectedItem = new String;
  var populateWith = new Array();
  var selectedOption = 0;";

			reset ($select1_values);
			while (list ($key, $val) = each ($select1_values)) {
				$retval["javascript_declaration"] .= "
  subselect1['" . $key . "'] = '" . $val . "';";
			}

			$retval["javascript_declaration"] .= "
  document." . $name_of_form . "." . $name_of_subselect1 . ".length = 0;
  selectedItem = document." . $name_of_form . "." . $name_of_select . ".options[document." . $name_of_form . "." . $name_of_select . ".selectedIndex].text;
  populateWith = subselect1[selectedItem].split('¡');";

			if ($use_ids == true) {
				$retval["javascript_declaration"] .= "
  for (var i=0; i < populateWith.length; i = i+2) {
    if (populateWith[i] == curval) selectedOption = (i / 2);
    document." . $name_of_form . "." . $name_of_subselect1 . ".options[i/2] = new Option (populateWith[i+1], populateWith[i], false, false);
  }";
			} else {
				$retval["javascript_declaration"] .= "
  for (var i=0; i < populateWith.length; i++) {
    if (populateWith[i] == curval) selectedOption = (i - 1);
    document." . $name_of_form . "." . $name_of_subselect1 . ".options[i] = new Option (populateWith[i], populateWith[i], false, false);
  }";
			}

			$retval["javascript_declaration"] .= "
  document." . $name_of_form . "." . $name_of_subselect1 . ".options[selectedOption].selected = true;
  return 0;
}

function populate_" . $name_of_subselect2 . "(curval) {
  var subselect2 = new Array();
  var selectedItem = new String;
  var populateWith = new Array();
  var selectedOption = 0;";

			reset ($select2_values);
			while (list ($key, $val) = each ($select2_values)) {
				$retval["javascript_declaration"] .= "
  subselect2['" . $key . "'] = '" . $val . "';";
			}

			$retval["javascript_declaration"] .= "
  document." . $name_of_form . "." . $name_of_subselect2 . ".length = 0;
  selectedItem = document." . $name_of_form . "." . $name_of_select . ".options[document." . $name_of_form . "." . $name_of_select . ".selectedIndex].text;
  populateWith = subselect2[selectedItem].split('¡');";

			if ($use_ids == true) {
				$retval["javascript_declaration"] .= "
  for (var i=0; i < populateWith.length; i = i+2) {
    if (populateWith[i] == curval) selectedOption = (i / 2);
    document." . $name_of_form . "." . $name_of_subselect2 . ".options[i/2] = new Option (populateWith[i+1], populateWith[i], false, false);
  }";
			} else {
				$retval["javascript_declaration"] .= "
  for (var i=0; i < populateWith.length; i++) {
    if (populateWith[i] == curval) selectedOption = (i - 1);
    document." . $name_of_form . "." . $name_of_subselect2 . ".options[i] = new Option (populateWith[i], populateWith[i], false, false);
  }";
			}

			$retval["javascript_declaration"] .= "
  document." . $name_of_form . "." . $name_of_subselect2 . ".options[selectedOption].selected = true;
  return 0;
}
//-->
</script>";

			$retval['select_html'] = '
    <select name="' . $name_of_select . '" onChange="populate_' . $name_of_subselect1 . '(); populate_' . $name_of_subselect2 . '()">';

			reset ($select1_values);
			while (list ($key, $val) = each ($select1_values)) {
				if ($key == $select_curval) {
					$retval['select_html'] .= '
      <option value="' . $key . '" selected>' . $key . '</option>';
				} else {
					$retval['select_html'] .= '
      <option value="' . $key . '">' . $key . '</option>';
				}
			}

			$retval['select_html'] .= '
    </select>';

			$retval['sub_select1_html'] = '
    <select name="' . $name_of_subselect1 . '">
      <option value=""></option>
    </select>';

			$retval['sub_select2_html'] = '
    <select name="' . $name_of_subselect2 . '">
      <option value=""></option>
    </select>';

			$retval['javascript'] = "
<script language=\"JavaScript\">
<!--
  populate_" . $name_of_subselect1 . "('" . $subselect1_curval . "');
  populate_" . $name_of_subselect2 . "('" . $subselect2_curval . "');
//-->
</script>";

			return $retval;
		}

		function makeDBMultipleSubSelect ($query_parameters, $name_of_select, $name_of_subselect1, $name_of_subselect2, $name_of_form, $select_curval = '', $subselect1_curval = '', $subselect2_curval = '', $use_ids = false, $show_all = false) {
			global $db;

			$query = 'SELECT ' . $query_parameters['SELECT_ID'] . ', ' . $query_parameters['SELECT_NAME'] . '
				FROM ' . $query_parameters['SELECT_TABLE'];

			if (!empty ($query_parameters['SELECT_WHERE'])) {
				$query .= '
				WHERE ' . $query_parameters['SELECT_WHERE'];
			}

			$query .= '
				ORDER BY ' . $query_parameters['SELECT_NAME'];

			$select_count = $db->doQuery ($query, $select_data);

			$pass_data1 = array ();
			for ($index = 1; $index <= $select_count; $index++) {
				$include = true;

				if (function_exists ('makeDBMultipleSubSelect_checkSelect')) {
					$include = makeDBMultipleSubSelect_checkSelect ($select_data[$index][$query_parameters['SELECT_ID']], $select_data[$index][$query_parameters['SELECT_NAME']]);
				}

				if ($include) {
					$query = 'SELECT ' . $query_parameters['SUB_SELECT1_ID'] . ', ' . $query_parameters['SUB_SELECT1_NAME'] . '
						FROM ' . $query_parameters['SUB_SELECT1_TABLE'];

					if (!empty ($query_parameters['SUB_SELECT1_WHERE_NAME'])) {
						$query .= '
						WHERE ' . $query_parameters['SUB_SELECT1_WHERE_NAME'] . " = '" . eval ($query_parameters['SUB_SELECT1_WHERE_VALUE']) . "'";
					}

					$query .= '
						ORDER BY ' . $query_parameters['SUB_SELECT1_NAME'];

					$sub_select1_count = $db->doQuery ($query, $sub_select1_data);

					if ($show_all) {
						$current_list = '¡Any ' . $select_data[$index][$query_parameters['SELECT_NAME']] . '¡';
					} else {
						$current_list = '';
					}

					for ($index2 = 1; $index2 <= $sub_select1_count; $index2++) {
						$include = true;

						if (function_exists ('makeDBSubSelect_checkSubSelect')) {
							$include = makeDBSubSelect_checkSubSelect1 ($sub_select1_data[$index2][stripTableFromColumnName ($query_parameters['SUB_SELECT1_ID'])], $sub_select1_data[$index2][$query_parameters['SUB_SELECT1_NAME']]);
						}

						if ($include) {
							$current_list .= $this->escapeJsString ($sub_select1_data[$index2][stripTableFromColumnName ($query_parameters['SUB_SELECT1_ID'])]) . '¡' . $this->escapeJsString ($sub_select1_data[$index2][$query_parameters['SUB_SELECT1_NAME']]) . '¡';
						}
					}

					$current_list = substr ($current_list, 0, -1);
					$pass_data1[$select_data[$index][$query_parameters['SELECT_NAME']]] = $current_list;
				}
			}

			$pass_data2 = array ();
			for ($index = 1; $index <= $select_count; $index++) {
				$include = true;

				if (function_exists ('makeDBMultipleSubSelect_checkSelect')) {
					$include = makeDBMultipleSubSelect_checkSelect ($select_data[$index][$query_parameters['SELECT_ID']], $select_data[$index][$query_parameters['SELECT_NAME']]);
				}

				if ($include) {
					$query = 'SELECT ' . $query_parameters['SUB_SELECT2_ID'] . ', ' . $query_parameters['SUB_SELECT2_NAME'] . '
						FROM ' . $query_parameters['SUB_SELECT2_TABLE'];

					if (!empty ($query_parameters['SUB_SELECT2_WHERE_NAME'])) {
						$query .= '
						WHERE ' . $query_parameters['SUB_SELECT2_WHERE_NAME'] . " = '" . eval ($query_parameters['SUB_SELECT2_WHERE_VALUE']) . "'";
					}

					$query .= '
						ORDER BY ' . $query_parameters['SUB_SELECT2_NAME'];

					$sub_select2_count = $db->doQuery ($query, $sub_select2_data);

					if ($show_all) {
						$current_list = '¡Any ' . $select_data[$index][$query_parameters['SELECT_NAME']] . '¡';
					} else {
						$current_list = '';
					}

					for ($index2 = 1; $index2 <= $sub_select2_count; $index2++) {
						$include = true;

						if (function_exists ('makeDBSubSelect_checkSubSelect2')) {
							$include = makeDBSubSelect_checkSubSelect2 ($sub_select2_data[$index2][stripTableFromColumnName ($query_parameters['SUB_SELECT2_ID'])], $sub_select2_data[$index2][$query_parameters['SUB_SELECT2_NAME']]);
						}

						if ($include) {
							$current_list .= $this->escapeJsString ($sub_select2_data[$index2][stripTableFromColumnName ($query_parameters['SUB_SELECT2_ID'])]) . '¡' . $this->escapeJsString ($sub_select2_data[$index2][$query_parameters['SUB_SELECT2_NAME']]) . '¡';
						}
					}

					$current_list = substr ($current_list, 0, -1);
					$pass_data2[$select_data[$index][$query_parameters['SELECT_NAME']]] = $current_list;
				}
			}

			debugArray ('Passing Data to makeSubSelect', $pass_data1, 3);
			debugArray ('Passing Data to makeSubSelect', $pass_data2, 3);
			$retval = $this->makeMultipleSubSelect ($pass_data1, $pass_data2, $name_of_select, $name_of_subselect1, $name_of_subselect2, $name_of_form, $select_curval, $subselect1_curval, $subselect2_curval, $use_ids);

			return $retval;
		}

		function makeSecurityObjectSelect ($name_of_select, $name_of_subselect, $name_of_form, $select_curval = '', $subselect_curval = '', $show_all_select = false, $show_all_subselect = false) {
			global $db;

			$query = 'SELECT security_object_id, name, foreign_table, foreign_id_column, foreign_name_column
				FROM security.security_objects 
				ORDER BY name';
			$select_count = $db->doQuery ($query, $select_data);

			$pass_data = array ();

			if ($show_all_select) {
				$pass_data['All'] = '¡All';
			}

			for ($index = 1; $index <= $select_count; $index++) {
				$query = 'SELECT ' . $select_data[$index]['foreign_id_column'] . ', ' . $select_data[$index]['foreign_name_column'] . '
					FROM ' . $select_data[$index]['foreign_table'] . '
					ORDER BY ' . $select_data[$index]['foreign_name_column'];

				$sub_select_count = $db->doQuery ($query, $sub_select_data);

				if ($show_all_subselect) {
					$current_list = '¡Any ' . $select_data[$index]['name'] . '¡';
				} else {
					$current_list = '';
				}

				for ($index2 = 1; $index2 <= $sub_select_count; $index2++) {
					$current_list .= $this->escapeJsString ($sub_select_data[$index2][$select_data[$index]['foreign_id_column']]) . '¡' . $this->escapeJsString ($sub_select_data[$index2][$select_data[$index]['foreign_name_column']]) . '¡';
				}

				$current_list = substr ($current_list, 0, -1);
				$pass_data[$select_data[$index]['name']] = $current_list;
			}

			debugArray ('Passing Data to makeSubSelect', $pass_data, 3);
			$retval = $this->makeSubSelect ($pass_data, $name_of_select, $name_of_subselect, $name_of_form, $select_curval, $subselect_curval, true);

			return $retval;
		}

		function makeTableColumnSelect ($name_of_select, $name_of_subselect, $name_of_form, $select_curval = '', $subselect_curval = '', $show_all_select = false, $show_all_subselect = false) {
			global $db;

			$query = "SELECT relname 
					FROM pg_stat_all_tables 
					WHERE relname NOT LIKE 'pg_%' 
						AND relname NOT IN ('mail_logs', 'permission_types', 'roles', 
								'security_logs', 'security_objects', 'user_preferences', 
								'users', 'users2permissions', 'users2roles') 
					ORDER BY relname;";
			$select_count = $db->doQuery ($query, $select_data);

			$pass_data = array ();

			if ($show_all_select) {
				$pass_data['All'] = '¡All';
			}

			for ($index = 1; $index <= $select_count; $index++) {
				$query = "SELECT a.attname AS column_name, 
							format_type (a.atttypid, a.atttypmod) AS data_type 
						FROM pg_class c, pg_attribute a 
						WHERE c.relname = '" . $select_data[$index]['relname'] . "' 
							AND a.attnum > 0 
							AND a.attisdropped = 'f'
							AND a.attrelid = c.oid 
						ORDER BY a.attnum";
				$sub_select_count = $db->doQuery ($query, $sub_select_data);

				if ($show_all_subselect) {
					$current_list = '¡Any ' . $select_data[$index]['relname'] . '¡';
				} else {
					$current_list = '';
				}

				for ($index2 = 1; $index2 <= $sub_select_count; $index2++) {
					if (in_array ($sub_select_data[$index2]['data_type'], $db->_numericDataTypes)) {
						for ($index3 = 1; $index3 <= $sub_select_count; $index3++) {
							if (!in_array ($sub_select_data[$index3]['data_type'], $db->_numericDataTypes)) {
								$current_list .= $this->escapeJsString ($sub_select_data[$index2]['column_name'] . '/' . $sub_select_data[$index3]['column_name']) . '¡' . $this->escapeJsString ($sub_select_data[$index2]['column_name'] . ' (' . $sub_select_data[$index2]['data_type'] . ') / ' . $sub_select_data[$index3]['column_name'] . ' (' . $sub_select_data[$index3]['data_type'] . ')') . '¡';
							}
						}
					}
				}

				$current_list = substr ($current_list, 0, -1);
				if (!empty ($current_list)) {
					$pass_data[$select_data[$index]['relname']] = $current_list;
				}
			}

			debugArray ('Passing Data to makeSubSelect', $pass_data, 3);
			$retval = $this->makeSubSelect ($pass_data, $name_of_select, $name_of_subselect, $name_of_form, $select_curval, $subselect_curval, true);

			return $retval;
		}
	}
}
?>
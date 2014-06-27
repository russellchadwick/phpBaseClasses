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
 * @version $Revision: 1.26 $ $Date: 2004/06/03 08:09:25 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	ob_start ();

// Determine if we are in command line mode
	if (php_sapi_name () == 'cli') {
		$CLI = true;
		if ((getenv ('DOCUMENT_ROOT')) && (getenv ('SERVER_NAME'))) {
			$_SERVER['DOCUMENT_ROOT'] = getenv ('DOCUMENT_ROOT');
			$_SERVER['SERVER_NAME'] = getenv ('SERVER_NAME');
		} else {
			die ('This is CLI mode.  Please set the envoirment variables DOCUMENT_ROOT and SERVER_NAME');
		}

		if (substr ($_SERVER['SCRIPT_NAME'], 0, 1) == '/') {
			$_SERVER['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_NAME'];
		} else {
			$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_NAME'];
		}

		$_SERVER['PHP_SELF'] = substr ($_SERVER['SCRIPT_FILENAME'], strlen ($_SERVER['DOCUMENT_ROOT']));
	} else {
		$CLI = false;
	}

// Determine this libraries own location
	$config['LIBRARY_PATH'] = dirname (__FILE__);
	foreach (explode (':', get_include_path ()) as $test_library_path) 
		if (file_exists ($test_library_path . '/begin.php')) 
			$config['LIBRARY_PATH'] = $test_library_path;

	unset ($test_library_path);

	$config['TEMP_PATH'] = str_replace ('htdocs', 'tmp', $_SERVER['DOCUMENT_ROOT']);
	$config['LOG_PATH'] = str_replace ('htdocs', 'log', $_SERVER['DOCUMENT_ROOT']);
	if (isset ($config['FILE_PATH'])) {
		$config['BASE_PATH'] = $config['FILE_PATH'];
	}

// Might use these later
	$FILE_NAME = basename ($_SERVER['SCRIPT_NAME']);
	$FOLDER_NAME = dirname ($_SERVER['SCRIPT_FILENAME']);
	$URI_FOLDER_NAME = str_replace ($_SERVER['DOCUMENT_ROOT'], '', dirname ($_SERVER['PHP_SELF']));

// Include config variables
	$configinc = include ('setup/default.config.php');
	if (empty($configinc))
		die ('Default config file not found');
	unset ($configinc);

	@include ($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php');

	if (isset ($config['FILE_PATH'])) {
		$config['BASE_PATH'] = $config['FILE_PATH'];
	}

// Needed by other classes
	include ('initialFunctions.php');

// Startup debugging
	include ('classDebug.php');
	$debug = new Debug ();
	debug ('Initializing', -1);

// See if we are in a skin and override some variables with the skin specific
	if (isset($_SERVER['SERVER_NAME'])) {
		if ((isset($SKINS[$_SERVER['SERVER_NAME']])) && (count($SKINS[$_SERVER['SERVER_NAME']]) > 0)) {
			foreach ($SKINS[$_SERVER['SERVER_NAME']] as $key=>$value) {
				${$key} = $value;
			}
		}
	}

// See if script would like any variable overridden
	if ((isset($override_config)) && (count($override_config) > 0)) {
		foreach ($override_config as $key=>$value) {
			$config[$key] = $value;
		}
	}

	unset ($key, $value);
	unset ($override_config);

// Connect to a database, chooses if replication is present
	include ('classDatabase.php');
	if ((isset($config['DB'][1]['TYPE'])) && (!empty($config['DB'][1]['TYPE']))) {
		if (count ($config['DB']) > 1) {
			// We are replicating, choose one for selecting
			$DBREPLICATION = true;
			$DBCONNECTEDTOALL = false;
			$DBCHOICE = rand (1, count ($config['DB']));
			debug ('Replicating Across ' . count ($config['DB']) . ' Servers, Choose Server ' . $DBCHOICE, 3);
		} else {
			$DBREPLICATION = false;
			$DBCHOICE = 1;
		}

		$dbs = array ();
		$dbs[$DBCHOICE] = new Database ($config['DB'][$DBCHOICE]['TYPE'], $config['DB'][$DBCHOICE]['USER'], $config['DB'][$DBCHOICE]['PASS'], $config['DB'][$DBCHOICE]['NAME'], $config['DB'][$DBCHOICE]['HOST'], '', 
					$config['DBERRORLOG'], $config['DBUPDATELOG'], $config['DBQUERYLOG'], $config['DBEXPLAINLOG']);
		$db = &$dbs[$DBCHOICE];
	}

// Startup security
	include ('classSecurity.php');
	$security = new Security ();

// Config require sessions and we are a web page and not a console script?
	if (($config['SESSIONS_ENABLED']) && (isset($_SERVER['REQUEST_URI']))) {
		session_start ();

		if (((isset($_GET['session_command'])) && ($_GET['session_command'] == 'logout')) || ((isset($session_command)) && ($session_command == 'logout'))) {
			session_unset ();
			session_destroy ();
		}

		if ($config['SESSION_LOGIN_URI'] == substr ($_SERVER['SCRIPT_FILENAME'], strlen ($_SERVER['DOCUMENT_ROOT']))) {
			$security->processLogin ($_POST['username'], md5 ($_POST['password']));
		} else if ((isset ($_GET['one_use_token'])) && (!empty ($_GET['one_use_token']))) {
			$security->processLogin ('', '', $_GET['one_use_token'], false);
		} else {
			$security->checkSessionVars ();
		}
	}

	if (($config['CACHING_ENABLED']) && (empty ($_POST))) {
		$GENERATE_CACHE = false;
		foreach ($config['CACHE_URIS'] as $cache_uri) {
			if ((substr ($_SERVER['REQUEST_URI'], 0, strlen ($cache_uri)) == $cache_uri) || ($cache_uri == 'THIS')) {
				$cache_file = $config['CACHE_DIRECTORY'] . $_SERVER['REQUEST_URI'] . '.cache';
				if (is_file ($cache_file)) {
					if (filemtime ($cache_file) + ($config['CACHE_TIMEOUT'] * 60) < mktime ()) {
						debug ($cache_file . ' is old', 3);
						if ($CACHE_FP = fopen ($cache_file, 'w')) {
							$GENERATE_CACHE = true;
						} else {
							debug ('Could not open cache file: ' . $cache_file, 2);
						}
					} else {
						debug ($cache_file . ' is fresh', 3);
						readfile ($cache_file);
						debug ('End Page', -1);
						die ();
					}
				} else {
					debug ($cache_file . ' is not found', 3);
					if ($CACHE_FP = fopen ($cache_file, 'w')) {
						flock ($CACHE_FP, LOCK_EX | LOCK_NB);
						$GENERATE_CACHE = true;
					} else {
						debug ('Could not open cache file: ' . $cache_file, 2);
					}
				}
			}
		}

		unset ($cache_uri, $cache_file);
	}

	include ('classDataInput.php');
	$datainput = new DataInput ();
	include ('classDisplay.php');
	$display = new Display ();
	include ('functions.php');
	include ('classMath.php');
	$math = new Math ();
	include ('classDateTime.php');
	$datetime = new pbcDateTime ($config['BEGINYEAR'], $config['BEGINDAYOFWEEK']);
	include ('classDataFetch.php');
	$datafetch = new DataFetch ();
	include ('classURLFetch.php');
	$urlfetch = new URLFetch ();
	include ('classWizard.php');
	$wizard = new Wizard ();

	$graph_types = array ('bar', 'canvas', 'canvtools', 'error', 'gantt', 'gradient', 'line', 'log', 
				'pie', 'pie3d', 'radar', 'regstat', 'scatter', 'stock');

	if (isset ($GRAPH)) {
		foreach ($GRAPH as $graph_type) {
			if (in_array ($graph_type, $graph_types)) {
				if (!defined('CLASSJPGRAPH')) {
					define('CLASSJPGRAPH', 1);
					include ('addons/jpgraph/jpgraph.php');
				}
				if (!defined('CLASSJPGRAPH_' . $graph_type)) {
					define('CLASSJPGRAPH_' . $graph_type, 1);
					include ('addons/jpgraph/jpgraph_' . $graph_type . '.php');
				}
			} else {
				debug ($GRAPH . ' is not a type of graph', 1);
			}
		}
	}

	unset ($graph_types, $graph_type);

	if ((isset($config['TREEMENU_ENABLED'])) && ($config['TREEMENU_ENABLED'])) {
		include ('addons/treemenu/ccBrowserInfo.php');
		include ('addons/treemenu/TreeMenu.php');
		include ('addons/treemenu/TreeMenuXL.php');
	}

	if ((isset($config['OVERLIB_ENABLED'])) && ($config['OVERLIB_ENABLED'])) {
		include ('addons/overlib/classOverlib.php');
	} else {
		include ('addons/overlib/classOverlib.stubs.php');
	}
	$ol = new Overlib ();

	if ((isset($config['SMARTY_ENABLED'])) && ($config['SMARTY_ENABLED'])) {
		include ('addons/smarty/Smarty.class.php');
		$smarty = new Smarty ();
		$smarty->compile_check = true;
		$smarty->debugging = false;
	}

	include ('classJabber.php');

	if ($config['SESSION_LOGIN_URI'] == substr ($_SERVER['SCRIPT_FILENAME'], strlen ($_SERVER['DOCUMENT_ROOT']))) {
		echo $display->head ('Login');
		echo $LOGIN_TEMPLATE;
		echo $display->foot ();
	}

	if ((isset ($_GET['one_use_token'])) && (!empty ($_GET['one_use_token']))) {
		header ('Location: ' . $config['SESSION_LOGIN_URI'] . '?session_command=logout&message=' . urlencode ('Invalid one use token, perhaps it has already been used.'));
		die ();
	}

	if (!empty($config['EXTRA_INCLUDE_PATH'])) 
		if (!include ($config['EXTRA_INCLUDE_PATH']))
			debug ('Extra Include Not Found. Please unset the variable <b>EXTRA_INCLUDE_PATH</b> in config.php or create the file: ' . $config['EXTRA_INCLUDE_PATH'], 2);

	debug ('Begin Page', -1);
?>

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
 * @version $Revision: 1.19 $ $Date: 2004/06/03 08:07:47 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSDEBUG')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSDEBUG', 1);

	/**
	 * Shorthand for $debug->debugText
	*/
	function debug ($text='', $level=2, $file='', $line='') {
		global $debug;
		$debug->debugText ($text, $level, $file, $line);
	}

	/**
	 * Shorthand for $debug->debugArray
	*/
	function debugArray ($name, &$array, $level=2, $file='', $line='') {
		global $debug;
		$debug->debugArray ($name, $array, $level, $file, $line);
	}

	/**
	 * Log any messages that php error handler would send to screen
	 *
	 * @access private
	 * @param integer Error Number
	 * @param string Error Message
	 * @param string File name error occured in
	 * @param integer Line number error occured on
	 * @return void
	 */
	 function debugErrorHandler ($errno, $errstr, $errfile, $errline) {
	 	global $debug;

		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			 	$debug->debugText ('PHPError -- Fatal: ' . $errstr . ' in ' . $errfile . ' on line ' . $errline, 1);
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
			 	$debug->debugText ('PHPError -- Warning: ' . $errstr . ' in ' . $errfile . ' on line ' . $errline, 2);
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
			 	$debug->debugText ('PHPError -- Notice: ' . $errstr . ' in ' . $errfile . ' on line ' . $errline, 3);
				break;
			default:
				$debug->debugText ('PHPError -- ' . $errno . ': ' . $errstr . ' in ' . $errfile . ' on line ' . $errline, 3);
				break;
		}
	 }

	/**
	 * The library for debugging application and the BaseClasses themselves
	 *
	 * This class defines the API for debugging and supports different
	 * debug levels and handles backtraces and timing
	 *
	 * @package phpBaseClasses
	 */
	class Debug {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Debugging level script is currently in
		 *
		 * @var integer $_debugLevel
		 * @access private
		 */
		var $_debugLevel;

		/**
		 * Array of all available debug levels
		 *
		 * @var array $_debugLevels (level_number, level_name)
		 * @access private
		 */
		var $_debugLevels;

		/**
		 * Total time since timing was started at constuction of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_totalRunningTime;

		/**
		 * Time when last timing operation was run
		 *
		 * @var integer $_lastTimestamp
		 * @access private
		 */
		var $_lastTimestamp;

		/**
		 * Error handler used to determine logging/displaying behavior
		 *
		 * @var string $_handler
		 * @access private
		 */
		var $_handler;

		/**
		 * Constructor begins timing, initializes debug levels, globalizes DEBUG which I recommend is at the top of every script
		 *
		 * @access public
		 * @return void
		 */
		function Debug () {
			global $DEBUG;
			global $CLI;

			$this->_version = 0.1;
			$this->_totalRunningTime = 0;
			$this->_lastTimestamp = get_microtime ();

			$this->_debugLevels = array (	0=>'NONE', 
							1=>'ERROR', 
							2=>'WARNING', 
							3=>'NOTICE', 
							4=>'ALL', 
							-1=>'TIMING' 
						);

			if (!isset($DEBUG) || empty($DEBUG)) {
				$DEBUG = 0;
			}

			if ($CLI) {
				$this->_handler = 'CLI_ERROR_HANDLERS';
			} else {
				$this->_handler = 'ERROR_HANDLERS';
			}

			set_error_handler ('debugErrorHandler');

			$this->changeDebugLevel ($DEBUG);
		}

		/**
		 * Changes the debugging level partway through script, useful for minimizing unwanted debug output
		 *
		 * @access public
		 * @param integer new debug level
		 * @return void
		 */
		function changeDebugLevel ($debugLevel='') {
			global $DEBUG;

			if (empty ($debugLevel)) {
				$debugLevel = $DEBUG;
			}

			$this->_debugLevel = $debugLevel;
			$this->setErrorReporting ();
		}

		/**
		 * Changes the php error_reporting level based on this library error reporting level
		 *
		 * @access private
		 * @return void
		 */
		function setErrorReporting () {
			if ($this->_debugLevel <= 0) {
				error_reporting (0);
			} else if ($this->_debugLevel == 1) {
				error_reporting (E_ERROR | E_PARSE);
			} else if ($this->_debugLevel == 2) {
				error_reporting (E_ERROR | E_PARSE | E_WARNING | E_NOTICE);
			} else {
				error_reporting (E_ALL);
			}

			if (isset ($smarty)) {
				if ($this->_debugLevel == 4) {
					$smarty->debugging = true;
				} else {
					$smarty->debugging = false;
				}
			}
		}

		/**
		 * Returns an id for a debug level
		 *
		 * @access private
		 * @param string name of the debug level
		 * @return int id of the debug level
		 */
		function getDebugID ($debugLevelName) {
			foreach ($this->_debugLevels as $id=>$name) {
				if ($debugLevelName == $name) {
					return $id;
				}
			}
		}

		/**
		 * Returns an name for a debug level
		 *
		 * @access private
		 * @param integer id of the debug level
		 * @return string name of the debug level
		 */
		function getDebugName ($debugLevelID) {
			foreach ($this->_debugLevels as $id=>$name) {
				if ($debugLevelID == $id) {
					return $name;
				}
			}
		}

		/**
		 * Get the text to a bug report
		 *
		 * @access private
		 * @return string text to a bug report
		 */
		function bugReport ($errorMessage) {
			global $db;

			$message = '
HOSTNAME: ' . substr (`hostname`, 0, -1) . '
SERVER: ' . $_SERVER['SERVER_NAME'] . '
SCRIPT: ' . $_SERVER['REQUEST_URI'] . '
REFERER: ' . @$_SERVER['HTTP_REFERER'] . '
REMOTE: ' . $_SERVER['REMOTE_ADDR'] . '
WHEN: ' . date ('m/d/Y h:i:s') . '
SESSION: ' . $_COOKIE['PHPSESSID'] . '
';

			if (!empty ($db->_errorMessage)) {
				$message .= 'QUERY: ' . $db->_lastQuery;
			}

			$message .= '
ERROR: ' . $errorMessage . '

BACKTRACE: ' . $this->getBacktrace (false) . '

GET VARS: ';

			
			foreach ($_GET as $get_key=>$get_value) {
				$message .= '
	' . $get_key . ' => ' . $get_value;
			}

			$message .= '

POST VARS: ';

			foreach ($_POST as $post_key=>$post_value) {
				$message .= '
	' . $post_key . ' => ' . $post_value;
			}

			$message .= '

SESSION VARS: ';

			foreach ($_SESSION as $session_key=>$session_value) {
				$message .= '
	' . $session_key . ' => ' . $session_value;
			}

			return $message;
		}

		/**
		 * Get the backtrace of function calls and includes in a nice readable format
		 *              
		 * @access private
		 * @return string backtrace of function calls and includes
		 */             
		function getBacktrace ($html=true) {
			$debug_backtrace = debug_backtrace ();
			array_shift ($debug_backtrace);
                                
			$backtrace = '';

			if ($html) 
				$backtrace .= '
<span class="error">Backtrace</span><br>';

			foreach ($debug_backtrace as $trace_count=>$trace_item) {
				if ($html)
					$backtrace .= '
<span> &nbsp;&nbsp;&nbsp; ' . ($trace_count + 1) . ') ' . $trace_item['file'] . '|' . $trace_item['line'] . ' -> ';
				else 
					$backtrace .= '
	' . ($trace_count + 1) . ') ' . $trace_item['file'] . '|' . $trace_item['line'] . ' -> ';

				if (!empty ($trace_item['class'])) 
					$backtrace .= $trace_item['class'] . '::';

				$backtrace .= $trace_item['function'] . ' (';

				if (isset ($trace_item['args'])) {
					for ($trace_index = 0; $trace_index < count($trace_item['args']); $trace_index++) {
						$trace_item['args'][$trace_index] = var_export ($trace_item['args'][$trace_index], true);
					}

					$backtrace .= implode (', ', str_replace ("\'", "'", $trace_item['args']));
				}

				if ($html) 
					$backtrace .= ') </span><br>';
				else 
					$backtrace .= ')';
			}

			if ($html) 
				$backtrace .= '
<span class="error">End Backtrace</span><br>';

			return $backtrace;
		}

		/**
		 * Displays a debug output if appropriate, updates timing information, and will display a back trace for a fatal error
		 *
		 * @access public
		 * @param string Debug information to display if appropriate
		 * @param integer Pass debug level id or name, if a debug level name is given the id will be looked up
		 * @param string Name of file this message appears in, pass in __FILE__
		 * @param integer Line of file this message appears in, pass in __LINE__
		 * @return void
		 */
		function debugText ($text='', $level=2, $file='', $line='') {
			global $config;
			global $CLI;

			$debug_string = '';

			if (strlen($level) > 2) {
				$level = $this->getDebugID ($level);
			}

			if (!empty($text)) {
				if ((($this->_debugLevel >= $level) && ($level > 0)) || (($level < 0) && ($this->_debugLevel == $level))) {
					if ($this->_debugLevel == -1) {
						$CURRENT_TIMESTAMP = get_microtime ();
						$this->_totalRunningTime += ($CURRENT_TIMESTAMP - $this->_lastTimestamp);
					}

					$debug_string .= '<span class="error">' . $this->getDebugName ($level);

					if (!empty($file) && !empty($line)) {
						$debug_string .= " ($file:$line)";
					}

					if ($this->_debugLevel == -1) {
						$debug_string .= ' (' . round (($CURRENT_TIMESTAMP - $this->_lastTimestamp), 6) . ' Seconds Since Last, ' . round ($this->_totalRunningTime, 6) . ' Total)';
					}

					if ($CLI) {
						$debug_string = strip_tags ($debug_string) . ' -- ' . $text . "\n";
					} else {
						$debug_string .= ' -- </span><span>' . $text . "</span><br>\n";
					}

					if ($this->_debugLevel == -1) {
						$this->_lastTimestamp = $CURRENT_TIMESTAMP;
					}
				}
			}

			if (!empty ($debug_string)) {
				$this->handleDebug ($debug_string, $level);
			}
		}

		/**
		 * Displays a debug output if appropriate, updates timing information, and will display a back trace for a fatal error
		 *
		 * @access public
		 * @param string Title of array to display
		 * @param array Data to be traversed and displayed
		 * @param integer Pass debug level id or name, if a debug level name is given the id will be looked up
		 * @param string Name of file this message appears in, pass in __FILE__
		 * @param integer Line of file this message appears in, pass in __LINE__
		 * @param integer This function calls itself with increasing depths, dont use this parameter
		 * @return void
		 */
		function debugArray ($name, &$array, $level=2, $file='', $line='', $depth=1) {
			global $config;
			global $CLI;

			$debug_string = '';

			if (strlen ($level) > 2) 
				$level = $this->getDebugID ($level);

			if ($this->_debugLevel >= $level) {
				if (!empty ($name)) {
					if ($this->_debugLevel == -1) {
						$CURRENT_TIMESTAMP = get_microtime ();
						$this->_totalRunningTime += ($CURRENT_TIMESTAMP - $this->_lastTimestamp);
					}

					$debug_string .= '<span class="error">Debug Array';

					if (!empty($file) && !empty($line)) {
						$debug_string .= " ($file:$line)";
					}

					if ($this->_debugLevel == -1) {
						$debug_string .= ' (' . round (($CURRENT_TIMESTAMP - $this->_lastTimestamp), 6) . ' Seconds Since Last, ' . round ($this->_totalRunningTime, 6) . ' Total)';
					}
					
					$debug_string .= ' -- </span><span>' . $name . "</span><br>\n";

					if ($this->_debugLevel == -1) {
						$this->_lastTimestamp = $CURRENT_TIMESTAMP;
					}
				}

				if (is_array($array)) {
					reset ($array);

					while (list ($key1, $key2) = each ($array) ) {
						$debug_string .= '<span>' . str_repeat('&nbsp;', 3 * $depth) . "$key1 => ";

						if (is_bool ($key2)) {
							if ($key2) {
								$debug_string .= 'true';
							} else {
								$debug_string .= 'false';
							}
						} else {
							$debug_string .= $key2;
						}

						$debug_string .= "</span><br>\n";

						if (is_array ($key1)) {
							$debug_string .= $this->debugArray ('', $key1, $level, '', '', $depth+1);
						} elseif (is_array ($key2)) {
							$debug_string .= $this->debugArray ('', $key2, $level, '', '', $depth+1);
						}
					}
				} else {
					$debug_string .= '<span class="error">ERROR: </span>' . $name . ' is not an array!<br>';
				}

				if (!empty($name)) {
					$debug_string .= '<span class="error">End Debug Array -- </span><span>' . $name . "</span><br>\n";
				}
			}

			if ($CLI) {
				$debug_string = strip_tags ($debug_string);
			}

			if ($depth > 1) {
				return $debug_string;
			} else {
				$this->handleDebug ($debug_string, $level);
			}
		}

		/**
		 * Handle sending a prepared debug from the debugText and debugArray 
		 *
		 * @access private
		 * @param string Prepared string of debug information
		 * @param integer Debug level of the string
		 * @return void
		 */

		function handleDebug ($debug_string, $level) {
			global $config;
			global $FATAL_ERROR;

			if (($level == 1) && ($this->_debugLevel == 1)) {
				if (!isset($FATAL_ERROR)) {
					if (in_array ('Show Backtrace', $config['FATAL_ERROR_HANDLERS']))
						$debug_string .= $this->getBacktrace ();

					if (in_array ('Mail Admin', $config['FATAL_ERROR_HANDLERS'])) 
						$this->mailAdmin ('Automated Bug Report', $this->bugReport ($name));

					$FATAL_ERROR = 1;
				}
			}

			if (in_array ('Log', $config[$this->_handler])) {
				if (!empty ($config['ERRORLOG'])) {
					if (is_writable ($config['ERRORLOG'])) {
						error_log ($_SERVER['SCRIPT_FILENAME'] . ' ' . date ('Y-m-d H:i:s') . " $debug_string", 3, $config['ERRORLOG']);
					} else {
						if ($this->_debugLevel >= 2) {
							echo '<span class="error">ERROR: </span>Error Log Not Writable: ' . $config['ERRORLOG'] . '</span><br>';
						}
					}
				} else {
					if ($this->_debugLevel >= 2) {
						echo '<span class="error">ERROR: </span>Error Logging On but No Log Specified</span><br>';
					}
				}
			}

			if (in_array ('Display', $config[$this->_handler])) {
				echo $debug_string;
			}
		}

		/**
		 * Determines who to email as admin for the site and sends them specified mail
		 *
		 * @access public
		 * @param string Subject of the mail
		 * @param string Body of the mail
		 * @return boolean Return value from mail () function
		 */
		function mailAdmin ($subject, $body) {
			if (!empty ($config['ADMIN_EMAIL_ADDRESS'])) {
				return mail ($config['ADMIN_EMAIL_ADDRESS'], $subject, $body);
			} else if (!empty ($_SERVER['SERVER_ADMIN'])) {
				return mail ($_SERVER['SERVER_ADMIN'], $subject, $body);
			}
		}
	}
}
?>
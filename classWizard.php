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
 * @version $Revision: 1.3 $ $Date: 2004/01/13 18:54:58 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSWIZARD')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSWIZARD', 1);

	/**
	 * Classes to assist in creating wizard style data input.
	 * Input can be split across tables and screens. Intended
	 * to be used as a popup.  Back and ext buttons are provided 
	 * to make data entry feel more like a application than a 
	 * website.
	 *
	 * @package phpBaseClasses
	 */
	class Wizard {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/** 
		 * Total number of steps or screens
		 *
		 * @var integer $_totalSteps
		 * @access private
		 */
		var $_totalSteps;

		/** 
		 * Current step or screen
		 *
		 * @var integer $_totalSteps
		 * @access private
		 */
		var $_stepNumber;

		/** 
		 * Current prefix to use when storing and fetching WIZARD variables.
		 *
		 * @var integer $_totalSteps
		 * @access private
		 */
		var $_prefix;

		/** 
		 * Stores error messages
		 *
		 * @var string $_errorMessage
		 * @access private
		 */
		var $_errorMessage;

		/**
		 * Stores errors per field from Next page
		 * 
		 * @var array $_errors
		 * @access private
		 */
		var $_errors;

		/**
		 * Status of running queries
		 *
		 * @var integer $_updateStatus
		 * @access private
		 */
		var $_updateStatus;

		/**
		 * Type of update to perform
		 *
		 * @var string $_action
		 * @access private
		 */
		var $_action;

		/**
		 * Constructor which optionally takes a description.  If a description is not
		 * provided, certain functions will fail.
		 *
		 * @access public
		 * @param integer Total number of steps or screens
		 * @param string Name of table working with.
		 * @return void
		 */
		function Wizard ($_totalSteps=0, $table='') {
			$this->_version = 0.1;
			$this->_stepNumber = 0;

			$this->_updateStatus = 0;
			$this->_action = '';

			if ($_totalSteps > 0) {
				$this->begin ($_totalSteps, $table);
			}
		}

		function begin ($_totalSteps, $table='') {
			if (((isset ($_POST['submit'])) && ($_POST['submit'] == 'Back')) || (!empty ($_GET['fix_errors']))) {
				// Do Nothing
			} else {
				$this->end ();
			}

			$this->setPrefix ($table);
			$this->setDescription ($table);

			$this->_stepNumber = 1;
			$this->_totalSteps = $_totalSteps;

			if (!empty ($_GET['action'])) {
				$this->_action = strtoupper ($_GET['action']);
				$this->setValue ('action', strtoupper ($_GET['action']));
			}

			if (!empty ($_GET['wizard_errorMessage'])) {
				$this->_errorMessage = '<span class="error">Data you entered was invalid. <br> Please correct the fields marked with red check mark.</span>';
			}

			$this->setErrors ();

			$_SESSION['WIZARD_totalSteps'] = $_totalSteps;
			$_SESSION['WIZARD_traceSteps'] = array ($this->_stepNumber);
			$_SESSION['WIZARD_lastPost'] = $this->_stepNumber;
		}

		function next ($_stepNumber, $table='') {
			global $datainput;

			$this->setPrefix ($table);
			$this->setDescription ($table);

			$this->_totalSteps = $_SESSION['WIZARD_totalSteps'];
			$this->_stepNumber = $_stepNumber;

			$datainput->setRealNames ();

			foreach ($_POST as $key=>$value) {
				$this->setValue ($key);
			}

			foreach ($_SESSION as $key=>$value) {
				if (substr ($key, 0, 7) == 'WIZARD_') {
					$key2 = substr ($key, 6 + strlen ($this->_prefix));
					if (substr ($key2, 0, 1) == '.') {
						$key2 = substr ($key2, 1);
					}
					$_POST[$key2] = $value;
				}
			}

			$this->_action = $this->getValue ('action');

			if (($_POST['submit'] == 'Do not Delete') || ($_POST['submit'] == 'Confirm Delete')) {
				$this->_action = 'DELETE';
			}

			if (!empty ($_GET['wizard_errorMessage'])) {
				$this->_errorMessage = '<span class="error">Data you entered was invalid. <br> Please correct the fields marked with red check mark.</span>';
			}

			$this->setErrors ();

			if (($_POST['submit'] == 'Back') || (!empty ($_GET['fix_errors']))) {
				if ($_SESSION['WIZARD_lastPost'] != $this->_stepNumber) {
					array_pop ($_SESSION['WIZARD_traceSteps']);
				}
			} else {
				if ($_SESSION['WIZARD_lastPost'] != $this->_stepNumber) {
					array_push ($_SESSION['WIZARD_traceSteps'], $this->_stepNumber);
				}
			}

			debugarray ('Session::Trace Steps', $_SESSION['WIZARD_traceSteps'], 3);

			$_SESSION['WIZARD_lastPost'] = $this->_stepNumber;
		}

		function done ($options='') {
			global $db;

			$defaults = array (	'href'=>'#',
						'onClick'=>'reloadParentAndClose ();',
						'linkText'=>'Close this Window', 
						'successText'=>'Data successfully saved.',
						'failureText'=>'Error saving data.  Administrator has been notified.'
					);
			$options = populate_defaults ($options, $defaults);

			$link = '<a href="' . $options['href'] . '" onClick="' . $options['onClick'] . '">' . $options['linkText'] . '</a>';

			if ($this->_updateStatus == 0) {
				$db->commit ();
				return $options['successText'] . '<br>' . $link;
			} else {
				$db->rollback ();
				return $options['failureText'] . '<br>' . $link;
			}
		}

		function save () {
			global $datainput;
			global $db;

			if (empty ($this->_errorMessage)) {
				$edits = $datainput->makeEditAll ();
				$sequences = $datainput->getSequenceData ();

				if (empty ($this->_action)) {
					$this->_action = 'INSERT';

					foreach ($sequences as $column_name => $sequence_name) {
						if ($this->issetValue ($column_name)) {
							$this->_action = 'UPDATE';
						}
					}
				}

				$query = $datainput->makeSQL ($edits, $this->_action);
				$queries = array (1 => $query);

				$function_name = $this->_action . '_callback';
				if (function_exists ($function_name)) {
					$queries = call_user_func ($function_name, $queries);
				}

				foreach ($queries as $index => $query) {
					$this->_updateStatus += $db->doUpdate ($query);
				}

				if ($this->_action == 'INSERT') {
					foreach ($sequences as $column_name => $sequence_name) {
						$this->setValue ($column_name, $db->getAutoID ($sequence_name));
					}
				}

				$this->_action = '';
			} else {
				debug ('Wizard::Save -- Not Saving because of Error Message: ' . $this->_errorMessage, 3);
			}
		}

		function validateAll ($ignore='') {
			global $datainput;

			if (empty ($ignore)) {
				$ignore = array ();
			}

			$found_keys = array ();

			if ($this->_action != 'DELETE') {
				foreach ($_POST as $key => $value) {
					if (($key != 'submit') && (!in_array ($key, $ignore))) {
						$key = $datainput->getRealName ($key);

						if (!in_array ($key, $found_keys)) {
							array_push ($found_keys, $key);

							debug ('Wizard::ValidateAll -- Validate = ' . $datainput->validateByName ($key), 3);
							$this->_errorMessage .= $datainput->validateByName ($key);
						}
					}
				}
			}
		}

		function addErrorMessage ($field, $message) {
			$this->_errorMessage .= "$field $message<br>";
		}

		function getError ($name) {
			if ((isset ($this->_errors[$name])) && (!empty ($this->_errors[$name]))) {
				return $this->_errors[$name];
			} else {
				return '';
			}
		}

		function setErrors () {
			if (isset ($_GET['wizard_errorMessage'])) {
				foreach (explode ('<br>', $_GET['wizard_errorMessage']) as $error) {
					if (!empty ($error)) {
						$this->_errors[substr ($error, 0, strpos ($error, ' '))] = '<br>' . $error;
					}
				}

				debugarray ('Wizard::_Errors', $this->_errors, 3);
			}
		}

		function setPrefixAndDescription ($table) {
			$this->setPrefix ($table);
			$this->setDescription ($table);
		}

		function setPrefix ($_prefix) {
			if (!empty ($_prefix)) {
				$_prefix .= '.';
			}

			$this->_prefix = $_prefix;
		}

		function setDescription ($table) {
			global $datainput;

			$datainput->loadDescription ($table);
		}

		function end () {
			debug ('Wizard::end -- Ending Wizard Session', 3);

			foreach ($_SESSION as $key=>$value) {
				if (substr ($key, 0, 7) == 'WIZARD_') {
					unset ($_SESSION[$key]);
				}
			}
		}

		function issetValue ($key) {
			debug ('Wizard::issetValue -- Checking Data = ' . $this->_prefix . $key, 3);

			if (!empty ($key)) {
				if ((isset ($_SESSION['WIZARD_' . $this->_prefix . $key])) && (!empty ($_SESSION['WIZARD_' . $this->_prefix . $key]))) {
					return true;
				} else if ((isset ($_SESSION['WIZARD_' . $key])) && (!empty ($_SESSION['WIZARD_' . $key]))) {
					return true;
				} else {
					return false;
				}
			}
		}

		function getValue ($key) {
			debug ('Wizard::getValue -- Fetching Data = ' . $this->_prefix . $key, 3);

			if (!empty ($key)) {
				if (isset ($_SESSION['WIZARD_' . $this->_prefix . $key])) {
					return $_SESSION['WIZARD_' . $this->_prefix . $key];
				} else if (isset ($_SESSION['WIZARD_' . $key])) {
					return $_SESSION['WIZARD_' . $key];
				} else {
					return '';
				}
			}
		}

		function setValue ($key, $value='') {
			if (!empty ($key)) {
				if ((empty ($value)) && (!(is_numeric ($value) && ($value === 0)))) {
					if (isset ($_POST[$key])) {
						$_SESSION['WIZARD_' . $this->_prefix . $key] = $_POST[$key];
						$_SESSION['WIZARD_' . $key] = $_POST[$key];
						debug ('Wizard::setValue -- Storing Data = ' . $this->_prefix . $key . ' -> ' . $_POST[$key], 3);
					} else {
						$_SESSION['WIZARD_' . $this->_prefix . $key] = '';
						$_SESSION['WIZARD_' . $key] = '';
						debug ('Wizard::setValue -- Clearing Data = ' . $this->_prefix . $key, 3);
					}
				} else {
					$_SESSION['WIZARD_' . $this->_prefix . $key] = $value;
					$_SESSION['WIZARD_' . $key] = $value;
					debug ('Wizard::setValue -- Storing Data: ' . $this->_prefix . $key . ' -> ' . $value, 3);
				}
			}

			$_POST[$this->_prefix . $key] = $_SESSION['WIZARD_' . $this->_prefix . $key];
			$_POST[$key] = $_SESSION['WIZARD_' . $key];
		}

		function setValues ($data, $values=false) {
			if ($values) {
				foreach ($data as $key=>$value) {
					$this->setValue ($key, $value);
				}
			} else {
				foreach ($data as $key) {
					$this->setValue ($key);
				}
			}
		}

		function backOnError () {
			global $FILE_NAME;

			if (($_POST['submit'] == 'Next') || ($_POST['submit'] == 'Confirm Delete') || ($_POST['submit'] == 'Save')) {
				if ((!isset ($_GET['fix_errors'])) || ($_GET['fix_errors'] != 1)) {
					if (!empty ($this->_errorMessage)) {
						$backurl = str_replace ($_SESSION['WIZARD_traceSteps'][count($_SESSION['WIZARD_traceSteps']) - 1], $_SESSION['WIZARD_traceSteps'][count($_SESSION['WIZARD_traceSteps']) - 2], $FILE_NAME);
						$backurl .= '?fix_errors=1&wizard_errorMessage=' . urlencode ($this->_errorMessage);
						header ('Location: ' . $backurl);
						die ('<a href="' . $backurl . '"><font class="error">Please return to the previous page to fix errors</font></a>');
					}
				}
			}
		}

		function getNavigation ($formName='form', $nextStep='') {
			global $FILE_NAME;

			if (empty ($nextStep)) {
				$nextStep = $this->_stepNumber + 1;
			}

			$backurl = str_replace ($_SESSION['WIZARD_traceSteps'][count($_SESSION['WIZARD_traceSteps']) - 1], ($this->_stepNumber - 1), $FILE_NAME);
			$nexturl = str_replace ($_SESSION['WIZARD_traceSteps'][count($_SESSION['WIZARD_traceSteps']) - 1], $nextStep, $FILE_NAME);

			debug ('Wizard::getNavigation -- Total Steps = ' . $this->_totalSteps . ' :: Step Number = ' . $this->_stepNumber . ' :: Next Step = ' . $nextStep, 3);
			debug ('Wizard::getNavigation -- Back = ' . $backurl, 3);
			debug ('Wizard::getNavigation -- Next = ' . $nexturl, 3);

			if ($this->_action == 'DELETE') {
				$retval['back'] = '<input type="submit" name="submit" value="Do not Delete" onClick="closeWindow ();" tabindex=100>';
				$retval['next'] = '<input type="submit" name="submit" value="Confirm Delete" onClick="setFormAction (' . "'$formName', '$nexturl'" . ');" tabindex=100>';
			} else {
				if (count($_SESSION['WIZARD_traceSteps']) > 1) {
					$retval['back'] = '<input type="submit" name="submit" value="Back" onClick="setFormAction (' . "'$formName', '$backurl'" . ');" tabindex=100>';
				} else {
					$retval['back'] = '';
				}

				if (($this->_totalSteps - $this->_stepNumber) > 1) {
					$retval['next'] = '<input type="submit" name="submit" value="Next" onClick="setFormAction (' . "'$formName', '$nexturl'" . ');" tabindex=100>';
				} else {
					$retval['next'] = '';
				}

				if (($this->_totalSteps - $this->_stepNumber) == 1) {
					$retval['next'] = '<input type="submit" name="submit" value="Save" onClick="setFormAction (' . "'$formName', '$nexturl'" . ');" tabindex=100>';
				}
			}

			return $retval;
		}

		function saveMessage ($update_success) {
			global $db;
			global $datasave;

			($update_success == 0) ? $db->commit () : $db->rollback ();

			$javascript = '<script language="javascript">setTimeout(\'reloadParentAndClose ()\', 5000);</script>';
			$wizard_message = '<br><br>This wizard will close and reload its parent automatically in 5 seconds or by <a href="javascript:void(0);" onClick="reloadParentAndClose ();">clicking here</a>';

			return $javascript . $datasave->message . $wizard_message;
		}
	}
}
?>
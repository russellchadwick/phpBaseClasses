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
 * @version $Revision: 1.16 $ $Date: 2004/06/03 08:06:50 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSSECURITY')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSSECURITY', 1);

	/**
	 * The API for all checking and setting security
	 *
	 * This class defines the API for authenticating users, checking their permissions, 
	 * and setting permissions
	 *
	 * @package phpBaseClasses
	 */
	class Security {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Class constructor
		 */
		function Security () {
			$this->_version = 0.1;
		}

		/**
		 * Check if logged in user has permissions to a given object
		 *
		 * @access public
		 * @param string Either the name of a role or the name of a security object that we are looking for
		 * @param integer A specific id to search for permissions on
		 * @param string One of Read, Modify, Delete where delete inherits Modify and Read, and Modify inherits Read
		 * @returns bool Is permission granted?
		 */
		function checkPermissions ($object_type, $object_id=0, $object_reference_id=0, $requested_permission='Read') {
			global $config;

			if (!$config['SESSIONS_ENABLED']) {
				return true;
			}

			reset ($_SESSION['roles']);
			foreach ($_SESSION['roles'] as $role_count=>$role) {
				if ($role['title'] == $object_type) {
					if ((empty ($object_id)) && ($requested_permission == 'Read')) {
						debug ('Granted Access to <i>' . $object_type . '</i> based on role of same name', 3);
						return true;
					} else if ((!empty ($object_id))) {
						if (($object_id == $role['security_object_id']) || (empty ($role['security_object_id']))) {
							if (($object_reference_id == $role['security_object_reference_id']) || (empty ($role['security_object_reference_id']))) {
								debug ('Granted Access to <i>' . $object_type . '</i> based on specific objects of same name', 3);
								return true;
							}
						}
					}
				}
			}

			reset ($_SESSION['permissions']);
			foreach ($_SESSION['permissions'] as $permission_count=>$permission) {
				if ($permission['security_object'] == $object_type) {
					if (($permission['security_object_reference_id'] == $object_id) || (empty($permission['security_object_reference_id']))) {
						switch ($requested_permission) {
							case 'Read':
								if (($permission['type'] == 'Read') || ($permission['type'] == 'Modify') || ($permission['type'] == 'Delete')) return true;
							case 'Modify':
								if (($permission['type'] == 'Modify') || ($permission['type'] == 'Delete')) return true;
							case 'Delete':
								if ($permission['type'] == 'Delete') return true;
						}
					}
				}
			}

			return false;
		}

		/**
		 * Process a submitted login
		 *
		 * @access public
		 * @param string Username to login
		 * @param string Encrypted password of user
		 * @param string One use token for automatic login
		 * @param boolean Whether to redirect the user to the default URI
		 * @returns void
		 */
		function processLogin ($username='', $encrypted_password='', $one_use_token='', $redirect=true) {
			global $db;
			global $config;
			global $LOGIN_TEMPLATE;

			debug ('At login page', 4);

			$span = '<span>';
			if (isset ($_GET['message'])) {
				$message = $_GET['message'];
			} else {
				$message = '';
			}

			// Login button posted from login screen
			if ((!empty ($username)) && (!empty ($encrypted_password))) {
				$user_info = $db->doQuery1Row ("SELECT u.user_id, 
						u.username, u.first_name||' '||u.last_name AS name
					FROM security.users u 
					WHERE u.username = '" . $db->sqlEscape ($username) . "' 
						AND u.password = '" . $encrypted_password . "'
						AND u.enabled = 't'");
			} else if (!empty ($one_use_token)) {
				$user_info = $db->doQuery1Row ("SELECT u.user_id, 
						u.username, u.first_name||' '||u.last_name AS name 
					FROM security.users u, security.one_use_tokens o
					WHERE o.token = '$one_use_token' 
						AND o.user_id = u.user_id
						AND u.enabled = 't'");

				if (isset ($user_info['user_id'])) {
					$this->deleteOneUseToken ($user_info['user_id'], $one_use_token);
					$_GET['one_use_token'] = '';
				}

			}				

			if (isset ($user_info)) {
				if (!empty ($user_info)) {
					debug ('Login Success', 4);

					// Populate data for this session, all this is security related information
					$_SESSION['user_id'] = $user_info['user_id'];
					$_SESSION['username'] = $user_info['username'];
					$_SESSION['users_name'] = $user_info['name'];
					$_SESSION['roles'] = $this->getThisUsersRoles ();
					$_SESSION['permissions'] = $this->getThisUsersPermissions ();
					$_SESSION['preferences'] = $this->getThisUsersPreferences ();
					$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
					$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
					$_SESSION['last_access'] = mktime ();

					$this->logSuccessfulLogin ();

					if ($config['SESSION_REMEMBER_USER']) {
						setcookie ('last_user_id', $user_info['user_id'], mktime (0, 0, 0, 12, 31, 2020));
						setcookie ('last_users_name', $user_info['name'], mktime (0, 0, 0, 12, 31, 2020));
					}

					// Redirect them to main page for users
					if ($redirect) {
						$message .= 'Login Successful. Redirecting to <a href="' . $config['SESSION_SUCCESS_URI'] . '">' . $config['SESSION_SUCCESS_URI'] . '</a><meta http-equiv="refresh" content="0; URL=' . $config['SESSION_SUCCESS_URI'] . '">';
						header ('Location: ' . $config['SESSION_SUCCESS_URI']);
					}
				} else {
					debug ('Login Failure', 4);

					$span = '<span class="error">';
					$message .= 'Username or Password Invalid.  Please try again.';
				}
			}

			if (isset ($LOGIN_TEMPLATE)) {
				$LOGIN_TEMPLATE = str_replace ('#MESSAGE#', $span . $message . '</span>', $LOGIN_TEMPLATE);
				$LOGIN_TEMPLATE = str_replace ('#FORM#', '<form method="post" action="' . $config['SESSION_LOGIN_URI'] . '">', $LOGIN_TEMPLATE);
				$LOGIN_TEMPLATE = str_replace ('#USERNAME#', '<input type="text" name="username" size="16" maxlength="16" value="' . $username . '">', $LOGIN_TEMPLATE);
				$LOGIN_TEMPLATE = str_replace ('#PASSWORD#', '<input type="password" name="password" size="16" maxlength="16">', $LOGIN_TEMPLATE);
				$LOGIN_TEMPLATE = str_replace ('#SUBMIT#', '<input type="submit" name="submit" value="Login">', $LOGIN_TEMPLATE);
			}
		}

		/**
		 * Check whether accessing a protected area
		 * Check whether required session vars are present and values are good
		 * Update last access time to kick someone after inactivity
		 *
		 * @access public
		 * @returns boolean Whether successful
		 */
		function checkSessionVars () {
			global $config;

			foreach ($config['SESSION_VARS'] as $session_var) {
				session_register ($session_var);
				if ($session_var == $config['SESSION_REQUIRED_VAR']) {
					$restricted = false;
					foreach ($config['SESSION_REQUIRED_URIS'] as $session_required_uri) {
						if (substr ($_SERVER['REQUEST_URI'], 0, strlen ($session_required_uri)) == $session_required_uri) {
							$restricted = true;
						}
					}

					unset ($session_required_uri);

					if (($restricted) && ( (!isset ($_SESSION[$config['SESSION_REQUIRED_VAR']])) || ($_SESSION[$config['SESSION_REQUIRED_VAR']] == 0) || ($_SESSION[$config['SESSION_REQUIRED_VAR']] == '') )) {
						if (!empty ($config['SESSION_LOGIN_URI'])) {
							header ('Location: ' . $config['SESSION_LOGIN_URI'] . '?session_command=logout&message=' . urlencode ('Either you tried to access a restricted area without logging in, or you were inactive long enough your session was deleted.'));
							die ();
						}
					}
				}

				if ($session_var == 'last_access') {
					if (isset ($config['SESSION_TIMEOUT']) && ($config['SESSION_TIMEOUT'] > 0)) {
						if ($restricted) {
							if ($config['SESSION_TIMEOUT'] < ((mktime () - $_SESSION['last_access']) / 60)) {
								if (!empty ($config['SESSION_LOGIN_URI'])) {
									header ('Location: ' . $config['SESSION_LOGIN_URI'] . '?session_command=logout&message=' . urlencode ('Idle More Than ' . $config['SESSION_TIMEOUT'] . ' Minutes'));
									die ();
								}
							} else {
								$_SESSION['last_access'] = mktime ();
							}
						} else {
							$_SESSION['last_access'] = mktime ();
						}
					} else {
						$_SESSION['last_access'] = mktime ();
					}
				}
			}
		}

		/**
		 * Get Reference IDs for given role information
		 *
		 * @access public
		 * @param string Title of a role that we are looking for
		 * @param string The type of object we are looking for
		 * @returns array IDs for given role information
		 */
		function getReferencedIDForRole ($role_title, $object_type='') {
			$retval = array ();
 
			reset ($_SESSION['roles']);
			foreach ($_SESSION['roles'] as $role_count=>$role) {
				if ($role['title'] == $role_title) {
					if ($role['security_object'] == $object_type) {
						array_push ($retval, $role['security_object_reference_id']);
					}
				}
			}
 
			return $retval;
		}

		/**
		 * Gets a list of roles which are inherited per specified role
		 *
		 * @access public
		 * @param array ('role_id', 'security_object_id', 'security_object_reference_id') Record of data pulled from security.roles table
		 * @return array (count=>array ('role_id', 'title', 'security_object_id', 'security_object', 'security_object_reference_id'))
		 */
		function getInheritedRoles ($role) {
			global $db;

			$inheritance_count = $db->doQuery ("SELECT r2ri.inherited_role_id AS role_id, r.title, 
								r2ri.security_object_id, r2ri.security_object_reference_id, 
								(SELECT so.name 
									FROM security.security_objects so 
									WHERE r2ri.security_object_id = so.security_object_id 
								) AS security_object 
							FROM security.roles2roles_inheritances r2ri, 
								security.roles r 
							WHERE r2ri.inherited_role_id = r.role_id 
								AND r2ri.role_id = " . $role['role_id'] . "
							ORDER BY r.title", $inheritances);

			foreach ($inheritances as $index=>$inheritance) {
				if (empty ($inheritance['security_object_id'])) $inheritances[$index]['security_object_id'] = $role['security_object_id'];
				if (empty ($inheritance['security_object_reference_id'])) $inheritances[$index]['security_object_reference_id'] = $role['security_object_reference_id'];
			}

			foreach ($inheritances as $index=>$inheritance) {
				$inheritances = array_merge ($inheritances, $this->getInheritedRoles ($inheritance));
			}

			return $inheritances;
		}

		/**
		 * Gets a list of permissions which are inherited per specified role
		 *
		 * @access public
		 * @param integer ID of role to fetch inherited permissions for
		 * @return array (count=>array ('type', 'security_object_id', 'security_object', 'security_object_reference_id'))
		 */
		function getInheritedPermissions ($role_id) {
			global $db;

			$inheritance_count = $db->doQuery ("SELECT pt.type, r2pi.security_object_id, r2pi.security_object_reference_id, 
									(SELECT so.name 
										FROM security.security_objects so 
										WHERE r2pi.security_object_id = so.security_object_id 
									) AS security_object
								FROM security.roles2permissions_inheritances r2pi, 
									security.permission_types pt  
								WHERE r2pi.permission_type_id = pt.permission_type_id 
									AND r2pi.role_id = $role_id
								ORDER BY pt.type", $inheritances);

			return $inheritances;
		}

		/**
		 * Updates where and when a user last logged in
		 *
		 * @access private
		 */
		function logSuccessfulLogin () {
			global $db;

			$db->doUpdate ("UPDATE security.users 
					SET last_login = CURRENT_TIMESTAMP, 
						last_login_from = '" . $_SERVER['REMOTE_ADDR'] . "'
					WHERE user_id = " . $_SESSION['user_id']);

			$db->doUpdate ("INSERT INTO software.statistics (statistic_category_id) VALUES (
						(SELECT statistic_category_id FROM software.statistic_categories 
							WHERE name = 'Login'))");
						
			$db->commit ();
		}

		/**
		 * Retrieves some information about a user
		 *
		 * @access public
		 * @return array information about a user
		 */
		function getUsersData ($user_id) {
			global $db;

			$user_data = $db->doQuery1Row ("SELECT username, first_name, last_name, email_address FROM security.users WHERE user_id = '$user_id'");
			if (!empty ($user_data['username'])) {
				return $user_data;
			} else {
				return false;
			}
		}

		/**
		 * Gets a users roles
		 *
		 * @access public
		 * @param integer Users id from security.users.user_id
		 * @return array (count=>array ('role_id', 'title', 'security_object_id', 'security_object', 'security_object_reference_id'))
		 */
		function getUsersRoles ($user_id) {
			global $db;

			$db->doQuery ("SELECT r.role_id, r.title, 
					u2r.security_object_id, 
					u2r.security_object_reference_id, 
					(SELECT so.name 
						FROM security.security_objects so 
						WHERE u2r.security_object_id = so.security_object_id 
					) AS security_object
				FROM security.users2roles u2r, 
					security.roles r 
				WHERE u2r.user_id = $user_id 
					AND u2r.role_id = r.role_id", $roles);

			foreach ($roles as $index=>$role) {
				$roles = array_merge ($roles, $this->getInheritedRoles ($role));
			}

			return $roles;
		}

		/**
		 * Gets a users permissions
		 *
		 * @access public
		 * @param integer Users id from security.users.user_id
		 * @param array Result from calling getUsersRoles or getThisUsersRoles
		 * @return array (count=>array ('type', 'security_object_id', 'security_object', 'security_object_reference_id'))
		 */
		function getUsersPermissions ($user_id, $roles) {
			global $db;

			$db->doQuery ("SELECT pt.type, 
					u2p.security_object_id, 
					u2p.security_object_reference_id, 
					(SELECT so.name 
						FROM security.security_objects so 
						WHERE u2p.security_object_id = so.security_object_id 
					) AS security_object 
				FROM security.users2permissions u2p,
					security.permission_types pt  
				WHERE u2p.user_id = $user_id
					AND u2p.permission_type_id = pt.permission_type_id", $permissions);

			foreach ($roles as $index=>$role) {
				$permissions = array_merge ($permissions, $this->getInheritedPermissions ($role['role_id']));
			}

			return $permissions;
		}

		/**
		 * Gets a users preferences
		 *
		 * @access public
		 * @param integer Users id from security.users.user_id
		 * @return array (count=>array ('user_preference_id', 'key', 'value'))
		 */
		function getUsersPreferences ($user_id) {
			global $db;

			$preferences = array ();

			$preferences_count = $db->doQuery ("SELECT 
					user_preference_id, key, value 
				FROM security.user_preferences 
				WHERE user_id = $user_id
				ORDER BY key", $temp_preferences);

			for ($index = 1; $index <= $preferences_count; $index++) {
				if (substr ($temp_preferences[$index]['value'], 0, 2) == 'a:') {
					$preferences[$temp_preferences[$index]['key']] = unserialize ($temp_preferences[$index]['value']);
				} else {
					$preferences[$temp_preferences[$index]['key']] = $temp_preferences[$index]['value'];
				}
			}

			return $preferences;
		}

		/**
		 * Gets a currently logged in users roles
		 *
		 * @access public
		 * @return array (count=>array ('role_id', 'title', 'security_object_id', 'security_object', 'security_object_reference_id'))
		 */
		function getThisUsersRoles () {
			return $this->getUsersRoles ($_SESSION['user_id']);
		}

		/**
		 * Gets a currently logged in users permissions
		 *
		 * @access public
		 * @return array (count=>array ('type', 'security_object_id', 'security_object', 'security_object_reference_id'))
		 */
		function getThisUsersPermissions () {
			return $this->getUsersPermissions ($_SESSION['user_id'], $_SESSION['roles']);
		}

		/**
		 * Gets a currently logged in users preferences
		 *
		 * @access public
		 * @return array (count=>array ('user_preference_id', 'key', 'value'))
		 */
		function getThisUsersPreferences () {
			return $this->getUsersPreferences ($_SESSION['user_id']);
		}

		/**
		 * Gets a computer readable or nicely formatted information about given security object
		 *
		 * @access public
		 * @param integer ID of a security_object we need the name of
		 * @param integer ID of the object referenced by security_object we need the name of
		 * @param boolean Whether or not to format the return value nicely
		 * @return string Textual representation of the security object
		 */
		function getReferencedSecurityObject ($security_object_id, $security_object_reference_id=0, $formatted=false) {
			global $db;

			if (empty ($security_object_reference_id)) $rso['name'] = 'Any';

			if (!empty ($security_object_id)) {
				$so = $db->doQuery1Row ("SELECT name, foreign_table, foreign_id_column, foreign_name_column 
							FROM security.security_objects
							WHERE security_object_id = $security_object_id");

				if (!empty ($security_object_reference_id)) {
					$rso = $db->doQuery1Row ("SELECT " . $so['foreign_name_column'] . " AS name
								FROM " . $so['foreign_table'] . "
								WHERE " . $so['foreign_id_column'] . " = $security_object_reference_id");
				}
			}

			if ($formatted) {
				$retval = '';
				if (!empty ($security_object_id)) {
					$retval .= ' of ';
					if (empty ($security_object_reference_id)) {
						$retval .= ' Any ' . $so['name'];
					} else {
						$retval .= $so['name'] . ' ' . $rso['name'];
					}
				}
				return $retval;
			} else {
				return $rso['name'];
			}
		}

		/**
		 * Gets the name for a specificed security object ID
		 *
		 * @access public
		 * @param integer ID of a security_object we need the name of
		 * @param boolean Whether an empty id should represent 'Any' or null, useful for formatting.
		 * @return string Name of the security object
		 */
		function getSecurityObjectName ($security_object_id, $any=false) {
			global $db;

			if (empty ($security_object_id)) {
				if ($any) {
					$so['name'] = 'Any';
				} else {
					$so['name'] = '';
				}
			} else {
				$so = $db->doQuery1Row ("SELECT name 
							FROM security.security_objects
							WHERE security_object_id = $security_object_id");
			}

			return $so['name'];
		}

		/**
		 * Gets the ID for a specificed security object name
		 *
		 * @access public
		 * @param string Name of the security we need the ID of
		 * @return integer ID of the security object
		 */
		function getSecurityObjectId ($name) {
			global $db;

			$so = $db->doQuery1Row ("SELECT security_object_id 
						FROM security.security_objects
						WHERE name = '$name'");

			return $so['security_object_id'];
		}

		/** 
		 * Gets a list of users that have the specified role
		 *
		 * @access public
		 * @param string Title of the role to search for
		 * @return array (count=>user_id)
		 */
		function getRolesUsers ($title) {
			global $db;

			$users = array ();

			$db->doQuery ("SELECT u.user_id, u.username, u.first_name, u.last_name, u.email_address
					FROM security.users u, security.users2roles u2r, security.roles r 
					WHERE u.user_id = u2r.user_id 
						AND u2r.role_id = r.role_id
						AND r.title = '$title'", $temp_users);

			foreach ($temp_users as $count=>$temp_user_data) {
				$users[$count] = $temp_user_data;
			}

			return $users;
		}

		/** 
		 * Gets a list of users that have the specified role to specific item
		 *
		 * @access public
		 * @param integer ID of the role to search for
		 * @return array (count=>user_id)
		 */
		function getUsersWithRoleID ($role_id, $object_type, $object_id=0) {
			global $db;

			$users = array ();

			if ($role_id > 0) {
				if (!empty($object_type)) 
					$secobj = '= ' . $this->getSecurityObjectId ($object_type);
				else 
					$secobj = 'IS NULL';

				if ($object_id > 0)
					$objid = '= ' . $object_id;
				else
					$objid = 'IS NULL';

				$db->doQuery ("SELECT u.user_id, u.username, u.first_name, u.last_name, u.email_address
						FROM security.users u, security.users2roles u2r, security.roles r 
						WHERE u.user_id = u2r.user_id 
							AND u2r.security_object_id $secobj
							AND u2r.security_object_reference_id $objid
							AND u2r.role_id = r.role_id
							AND r.role_id = '$role_id'", $temp_users);

				foreach ($temp_users as $count=>$temp_user_data) {
					$users[$count] = $temp_user_data;
				}
			}

			return $users;
		}

		/** 
		 * Gets a list of users that have the specified role to specific item
		 *
		 * @access public
		 * @param string Title of the role to search for
		 * @return array (count=>user_id)
		 */
		function getUsersWithRole ($title, $object_type, $object_id=0) {
			$this->getUsersWithRoleID ($this->getRoleID ($title), $object_type, $object_id=0);
		}

		/**
		 * Validate data and create a user if valid
		 *
		 * @access public
		 * @param string 
		 * @param string 
		 * @param string Will be tested again first password to make sure they match
		 * @param string 
		 * @param string 
		 * @param string 
		 * @param string Error message if there is a problem with parameters
		 * @return integer user_id on success, 0 on failure
		 */
		function addUser ($username, $password, $password2, $first_name, $last_name, $email_address, &$error) {
			global $db, $datainput;

			$error = '';

			if (empty ($username)) 
				$error .= 'Error: Username cannot be empty<br>';
			if (empty ($first_name)) 
				$error .= 'Error: First Name cannot be empty<br>';
			if (empty ($last_name)) 
				$error .= 'Error: Last Name cannot be empty<br>';

			if ($password != $password2) 
				$error .= 'Error: Passwords dont match<br>';
			if (strlen ($password) < 5) 
				$error .= 'Error: Password is too short<br>';

			if ($this->usernameTaken ($username)) 
				$error .= 'Error: Username already taken<br>';

			if ($this->nameTaken ($first_name, $last_name))
				$error .= 'Error: First name and Last name combined already taken<br>';

			if (!$datainput->validateEmail ($email_address)) 
				$error .= 'Error: Invalid Email Address<br>';

			if (empty ($error)) {
				$insert_query = "INSERT INTO security.users (username, password, first_name, last_name, email_address, enabled)
						VALUES ('$username', '" . md5($password) . "', 
							'$first_name', '$last_name', 
							'$email_address', 't')";

				if ($db->doUpdate ($insert_query) == 0) {
					$user_id = $db->getAutoID ('security.users_user_id_seq');
					$db->commit ();
					return $user_id;
				} else {
					$db->rollback ();
				}
			}

			return 0;
		}

		/**
		 * Checks if a username is taken
		 *
		 * @access public
		 * @param string username to test for
		 * @return boolean Whether username is taken
		 */
		function usernameTaken ($username) {
			global $db;

			$user_count = $db->doQuery ("SELECT user_id FROM security.users WHERE username = '$username'", $trash);

			if ($user_count > 0) 
				return true;
			else 
				return false;
		}

		/**
		 * Checks if a name is taken
		 *
		 * @access public
		 * @param string first_name to test for
		 * @param string last_name to test for
		 * @return boolean Whether name is taken
		 */
		function nameTaken ($first_name, $last_name) {
			global $db;

			$user_count = $db->doQuery ("SELECT user_id FROM security.users WHERE first_name = '$first_name' AND last_name = '$last_name'", $trash);

			if ($user_count > 0) 
				return true;
			else 
				return false;
		}

		/**
		 * Search and Replace some user data macros from text
		 * Currently is performed on %FIRST_NAME%, %LAST_NAME%, %USERNAME%
		 *
		 * @access private
		 * @param string text to work on
		 * @param array data grabbed from security.users
		 * @return void
		 */
		function replaceUserData (&$text, $user_data) {
			$text = str_replace ('%FIRST_NAME%', $user_data['first_name'], $text);
			$text = str_replace ('%LAST_NAME%', $user_data['last_name'], $text);
			$text = str_replace ('%USERNAME%', $user_data['username'], $text);
		}

		/**
		 * Returns a table with the users current notices and deletes those notices
		 *
		 * @access public
		 * @return string table with the users current notices
		 */
		function checkNotifications () {
			global $db, $display;

			$retval = '';

			if ((isset ($_SESSION['user_id'])) && (!empty ($_SESSION['user_id']))) {
				$count = $db->doQuery ("SELECT n.notification_id, n.when_sent, n.message, n.link_type, 
								(SELECT username 
									FROM security.users u 
									WHERE n.sent_by = u.user_id
								) as sent_by 
							FROM security.notifications n 
							WHERE n.user_id = " . $_SESSION['user_id'] . "
							ORDER BY n.when_sent DESC", $notifications);
			} else {
				$count = 0;
			}

			if ($count > 0) {
				$retval .= '
<center><h4>You have new notices!</h4></center>
<table align="center" border="1">
	<tr align="center">
		<td><b>From</b></td>
		<td><b>When Sent</b></td>
		<td><b>Notice</b></td>
	</tr>';

				for ($index = 1; $index <= $count; $index++) {
					$message = $notifications[$index]['message'];
					$link = '/administration/mail/redirect_delete_notice.php?notification_id=' . $notifications[$index]['notification_id'];

					if ($notifications[$index]['link_type'] == 'plain') {
						$message = '<a href="' . $link . '">' . $message . '</a>';
					} else if ($notifications[$index]['link_type'] == 'popup') {
						$message = $display->getNewPlainWindowLink ($message, $link, 'internal_message', 500, 300);
					} else if (!empty ($notifications[$index]['link_type'])) {
						$message = '<a href="' . $link . '" target="' . $notifications[$index]['link_type'] . '">' . $message . '</a>';
					} else {
						$message = '<a href="' . $link . '">' . $message . '</a>';
					}

					if (empty ($notifications[$index]['sent_by'])) 
						$notifications[$index]['sent_by'] = 'Admin';

					$retval .= '
	<tr align="center">
		<td>' . $notifications[$index]['sent_by'] . '</td>
		<td>' . $notifications[$index]['when_sent'] . '</td>
		<td>' . $message . '</td>
	</tr>';
				}

				$retval .= '
</table>';
			}

			return $retval;
		}

		/**
		 * Notify a user about something, admins are allowed to make message appear to come from system
		 *
		 * @access public
		 * @param integer ID of user to notify
		 * @param string Text of the notification
		 * @param string Type of link: popup, plain, or the name of a frame
		 * @param string Link itself, where to send people to view details about this notice
		 * @param boolean Whether make message appear to come from system
		 * @return boolean Whether mailing was successful
		 */
		function notifyUser ($user_id, $message, $link_type = '', $link = '', $from_system = false) {
			global $db;

			$user_data = $this->getUsersData ($user_id);

			$from = $_SESSION['user_id'];
			if (($this->checkPermissions ('Admin')) && ($from_system)) 
				$from = 'NULL';

			$this->replaceUserData ($message, $user_data);

			if (!empty ($user_data['username'])) {
				if ($db->doUpdate ("INSERT INTO security.notifications (user_id, sent_by, message, link_type, link) 
							VALUES ($user_id, $from, '$message', " . $db->orNull ($link_type) . ", " . $db->orNull ($link) . ")") == 0) {

					$db->commit ();
					return true;
				} else {
					$db->rollback ();
				}
			}

			return false;
		}

		/**
		 * Sends an internal mail to a user, admins are allowed to make message appear to come from system
		 *
		 * @access public
		 * @param integer ID of user to mail internally
		 * @param string Subject of the mail
		 * @param string Body of the mail
		 * @param boolean Whether make message appear to come from system
		 * @return boolean Whether internal mailing was successful
		 */
		function internalMailUser ($user_id, $subject, $body, $from_system = false) {
			global $db;

			$user_data = $this->getUsersData ($user_id);

			$from = $_SESSION['user_id'];
			if (($this->checkPermissions ('Admin')) && ($from_system)) 
				$from = 'NULL';

			$this->replaceUserData ($subject, $user_data);
			$this->replaceUserData ($body, $user_data);

			if (!empty ($user_data['username'])) {
				if ($db->doUpdate ("INSERT INTO security.internal_messages (user_id, sent_by, subject, body) 
							VALUES ($user_id, $from, '$subject', '$body')") == 0) {

					$message_id = $db->getAutoID ('security.internal_messages_internal_message_id_seq');

					$db->commit ();

					if ($from == 'NULL') {
						$from = 'Admin';
					} else {
						$from = $_SESSION['username'];
					}

					$this->notifyUser ($user_id, 'New Message for you from ' . $from, 'popup', '/administration/mail/internal_message.php?internal_message=' . $message_id, $from_system);

					return true;
				} else {
					$db->rollback ();
				}
			}

			return false;
		}

		/**
		 * Mails a user and logs it to the mail_logs.
		 * Search and replace is performed on %FIRST_NAME%, %LAST_NAME%, %USERNAME%
		 *
		 * @access public
		 * @param integer ID of user to mail
		 * @param string Name of mail, since either mail wont be logged this is so you can tell which mail was sent
		 * @param string Subject of the mail
		 * @param string Body of the mail
		 * @return boolean Whether mailing was successful
		 */
		function mailUser ($user_id, $mail_template, $subject, $body) {
			global $db, $config;

			$user_data = $this->getUsersData ($user_id);

			$this->replaceUserData ($subject, $user_data);
			$this->replaceUserData ($body, $user_data);

			if (!empty ($user_data['username'])) {
				if (mail ($user_data['email_address'], $subject, $body,
						"From: " . $config['MAIL_FROM'] . "\r\n"
						."Reply-To: " . $config['MAIL_FROM'] . "\r\n"
						."X-Mailer: PHP/" . phpversion())) {

					if ($db->doUpdate ("INSERT INTO security.mail_logs (user_id, when_mailed, template)
								VALUES ($user_id, CURRENT_TIMESTAMP, '$mail_template')") == 0) {

						$db->commit ();
						return true;
					} else {
						$db->rollback ();
					}
				}
			}

			return false;
		}

		/**
		 * Disables a user
		 *
		 * @access public
		 * @param integer ID of user to disable
		 * @return boolean Whether disabling was successful
		 */
		function disableUser ($user_id) {
			global $db;

			$update_success = $db->doUpdate ("UPDATE security.users SET enabled = 'f' WHERE user_id = $user_id");

			if ($update_success == 0) {
				$db->commit ();
				return true;
			} else {
				$db->rollback ();
				return false;
			}
		}

		/**
		 * Enables a user
		 *
		 * @access public
		 * @param integer ID of user to enable
		 * @return boolean Whether disabling was successful
		 */
		function enableUser ($user_id) {
			global $db;

			$update_success = $db->doUpdate ("UPDATE security.users SET enabled = 't' WHERE user_id = $user_id");

			if ($update_success == 0) {
				$db->commit ();
				return true;
			} else {
				$db->rollback ();
				return false;
			}
		}

		/**
		 * Get preference for a user
		 *
		 * @access public
		 * @param string Key name for preference
		 * @param string Default value of preference
		 * @return string Value of preference or default if none was found
		 */
		function getPreference ($key, $defaultValue) {
			if (isset ($_SESSION['preferences'][$key])) {
				return $_SESSION['preferences'][$key];
			} else {
				return $defaultValue;
			}
		}

		/**
		 * Sets a preference for a user
		 *
		 * @access public
		 * @param string Key name for preference
		 * @param string Value of preference
		 * @return string Value of preference
		 */
		function setPreference ($key, $value) {
			global $db;

			$exists = $db->doQuery1Row ("SELECT user_preference_id FROM security.user_preferences WHERE user_id = " . $_SESSION['user_id'] . " AND key = '$key'");

			if (empty ($exists)) {
				if (is_array ($value)) {
					$update_success = $db->doUpdate ("INSERT INTO security.user_preferences (user_id, key, value) VALUES (" . $_SESSION['user_id'] . ", '$key', '" . serialize ($value) . "')");
				} else {
					$update_success = $db->doUpdate ("INSERT INTO security.user_preferences (user_id, key, value) VALUES (" . $_SESSION['user_id'] . ", '$key', '$value')");
				}
			} else {
				if (is_array ($value)) {
					$update_success = $db->doUpdate ("UPDATE security.user_preferences SET value = '" . serialize ($value) . "' WHERE user_id = " . $_SESSION['user_id'] . " AND key = '$key'");
				} else {
					$update_success = $db->doUpdate ("UPDATE security.user_preferences SET value = '$value' WHERE user_id = " . $_SESSION['user_id'] . " AND key = '$key'");
				}
			}

			if ($update_success == 0) {
				$db->commit ();
				$_SESSION['preferences'][$key] = $value;
				return $value;
			} else {
				$db->rollback ();
				return '';
			}
		}

		/**
		 * Delete a preference for a user
		 *
		 * @access public
		 * @param string Key name for preference
		 * @return boolean Success
		 */
		function deletePreference ($key) {
			global $db;

			$update_success = $db->doUpdate ("DELETE FROM security.user_preferences WHERE user_id = " . $_SESSION['user_id'] . " AND key = '$key'");

			if ($update_success == 0) {
				$db->commit ();
				unset ($_SESSION['preferences'][$key]);
				return true;
			} else {
				$db->rollback ();
				return false;
			}
		}

		/**
		 * Add role to existing user
		 *
		 * @access public
		 * @param integer ID of user to add role to
		 * @param integer ID of role to add
		 * @param string Error message if there is a problem with parameters
		 * @param integer ID of security object this role refers to
		 * @param integer ID of column that security object refers to
		 * @return boolean Whether successful
		 */
		function addUserRole ($user_id, $role_id, &$error, $security_object_id = '', $security_object_reference_id = '', $allow_duplicates = true) {
			global $db;

			$error = '';

			$role = $db->doQuery1Row ("SELECT title 
						FROM security.roles 
						WHERE role_id = $role_id");

			if (($role['title'] == 'Uber Admin') && (!$this->checkPermissions ('Uber Admin')))
				$error = 'Error: Only Uber Admins can creates other Uber Admins<br>';

			if (!$allow_duplicates) {
				$has_role = false;
				$roles = $this->getUsersRoles ($user_id);
				foreach ($roles as $role_index=>$role) {
					if ($role['role_id'] == $role_id) 
						$has_role = true;
				}

				if ($has_role)
					$error = 'Error: Role Already Exists<br>';
			}

			if (empty($error)) {
				$insert_query = "INSERT INTO security.users2roles (user_id, role_id, security_object_id, security_object_reference_id)
						VALUES ($user_id, $role_id, " . $db->orNull ($security_object_id) . ", " . $db->orNull ($security_object_reference_id) . ")";

				if ($db->doUpdate ($insert_query) == 0) {
					$db->commit ();
					return true;
				} else {
					$db->rollback ();
				}
			}

			return false;
		}

		/**
		 * Returns the ID for the given role title
		 *
		 * @access public
		 * @param string Title of a role
		 * @return integer ID of the given role title
		 */
		function getRoleID ($title) {
			global $db;

			$role_data = $db->doQuery1Row ("SELECT role_id FROM security.roles WHERE title = '$title'");

			return $role_data['role_id'];
		}

		/** 
		 * Checks if data is locked, locks if available.
		 * Deletes a lock if it has gone stale.
		 * 
		 * @access public
		 * @param integer ID of a security_object to be locked
		 * @param integer ID of the object referenced by security_object to be locked
		 * @param boolean Whether to make a popup window come up to check for lock to be released
		 * @return string Empty on success, message regarding who holds lock on failure.
		 */
		function lockData ($security_object_id, $security_object_reference_id, $popup_link=true) {
			global $config;
			global $db;
			global $display;

			$db->doUpdate ("DELETE FROM security.locks 
						WHERE security_object_id = $security_object_id 
							AND security_object_reference_id = $security_object_reference_id 
							AND when_locked < now () - interval '" . $config['DATALOCKLIFESPAN'] . " minutes'");

			$db->commit ();

			$lock_data = $db->doQuery1Row ("SELECT u.user_id, u.first_name||' '||u.last_name AS name, l.when_locked, 
									l.when_locked + interval '" . $config['DATALOCKLIFESPAN'] . " minutes' - now () AS when_unlocked
								FROM security.locks l, security.users u 
								WHERE security_object_id = $security_object_id 
									AND security_object_reference_id = $security_object_reference_id 
									AND l.user_id = u.user_id");

			if (empty ($lock_data)) {
				$db->doUpdate ("INSERT INTO security.locks (
								user_id, security_object_id, security_object_reference_id
							) VALUES (
								" . $_SESSION['user_id'] . ", $security_object_id, $security_object_reference_id
							)");

				$db->commit ();

				return '';
			} else {
				if ($lock_data['user_id'] == $_SESSION['user_id']) {
					return '';
				} else {
					$text = 'Locked by ' . $lock_data['name'] . ' at ' . $lock_data['when_locked'] . '<!-- ' . $lock_data['when_unlocked'] . ' -->';

					if ($popup_link) {
						return $text . $display->popupNewPlainWindow ('/general/lock_popup.php?security_object_id=' . $security_object_id . '&security_object_reference_id=' . $security_object_reference_id, 'lock', 300, 150);
					} else {
						return $text;
					}
				}
			}
		}

		/**
		 * Delete a lock owned by current user
		 * 
		 * @access public
		 * @param integer ID of a security_object to be locked
		 * @param integer ID of the object referenced by security_object to be locked
		 * @return boolean Whether successful
		 */
		 function removeLock ($security_object_id, $security_object_reference_id) {
		 	global $db;

			$update_success = $db->doUpdate ("DELETE FROM security.locks 
								WHERE security_object_id = $security_object_id 
									AND security_object_reference_id = $security_object_reference_id 
									AND user_id = " . $_SESSION['user_id']);

			if ($update_success == 0) {
				$db->commit ();
				return true;
			} else {
				$db->rollback ();
				return false;
			}
		}

		/**
		 * Create a one use token
		 *
		 * @access public
		 * @param integer ID of user to create one use token for
		 * @return string Token created
		 */
		function createOneUseToken ($user_id) {
			global $db;

			$token = randomChars (32);

			$update_success = $db->doUpdate ("INSERT INTO security.one_use_tokens (
									user_id, token
								) VALUES (
									$user_id, '$token')");

			if ($update_success == 0) {
				$db->commit ();
				return $token;
			} else {
				$db->rollback ();
				return '';
			}
		}

		/**
		 * Delete a one use token
		 *
		 * @access public
		 * @param integer ID of user to create one use token for
		 * @param string Token that was used and needs to be removed
		 * @return boolean Whether successful
		 */
		function deleteOneUseToken ($user_id, $token) {
			global $db;

			$update_success = $db->doUpdate ("DELETE FROM security.one_use_tokens 
								WHERE user_id = $user_id 
									AND (token = '$token' OR when_created::date <= (CURRENT_DATE - 30))");

			if ($update_success == 0) {
				$db->commit ();
				return true;
			} else {
				$db->rollback ();
				return false;
			}
		}
	}
}
?>
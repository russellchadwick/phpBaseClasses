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
 * @version $Revision: 1.26 $ $Date: 2004/06/03 08:08:30 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSDATABASE')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSDATABASE', 1);

	/**
	 * The API for accessing the database
	 *
	 * This class defines the API for connecting to different databases
	 * and preparing data from user input to database query
	 *
	 * @package phpBaseClasses
	 */
	class Database {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Handle for the database connection
		 *
		 * @var integer $_dbh
		 * @access private
		 */
		var $_dbh;

		/**
		 * Holder for raw results from a database query
		 *
		 * @var array $_result
		 * @access private
		 */
		var $_result;

		/**
		 * Last error received from database server
		 *
		 * @var string $_errorMessage
		 * @access private
		 */
		var $_errorMessage;

		/**
		 * Last query that was executed
		 *
		 * @var string $_lastQuery
		 * @access private
		 */
		var $_lastQuery;

		/**
		 * Log of entire transaction so that only commited things are saved to Update log
		 *
		 * @var string $_dbTransactionLog
		 * @access private
		 */
		var $_dbTransactionLog;

		/**
		 * Path to database error log
		 *
		 * @var string $_dbErrorLog
		 * @access private
		 */
		var $_dbErrorLog;

		/**
		 * Path to database update log
		 *
		 * @var string $_dbUpdateLog
		 * @access private
		 */
		var $_dbUpdateLog;

		/**
		 * Path to database query log
		 *
		 * @var string $_dbQueryLog
		 * @access private
		 */
		var $_dbQueryLog;

		/**
		 * Path to database explain log
		 *
		 * @var string $_dbExplainLog
		 * @access private
		 */
		var $_dbExplainLog;

		/**
		 * Tablespace queries are operating within
		 *
		 * @var string $_dbName
		 * @access private
		 */
		var $_dbName;

		/**
		 * Type of database we are connecting to
		 *
		 * @var string $_dbType
		 * @access private
		 */
		var $_dbType;

		/**
		 * Whether to time queries
		 *
		 * @var boolean $_timing
		 * @access private
		 */
		var $_timing;

		/**
		 * List of datatypes to consider numeric
		 *
		 * @var array $_numericDataTypes
		 * @access private
		 */
		var $_numericDataTypes;

		/**
		 * Constructor, creates connection to the database and starts a transaction
		 *
		 * @access public
		 * @param string Can be MySQL or PgSQL
		 * @param string Username to connect to the database as
		 * @param string Password for given username
		 * @param string Database name to connect to, if empty this will be set to the user name
		 * @param string Hostname of database to connect to, if empty this will accept to connect to a local socket rather than use network
		 * @param string Port to connect to, this is unused if no host is specified
		 * @param string Path to an error log.  This file must exist and be writable by the web server.  All encountered database errors will be logged with enough information to fix it.
		 * @param string Path to a query log.  This file must exist and be writable by the web server.  This is useful for determining usage or compiling list of queries for optimization.
		 * @param string Path to an explain log.  This file must exist and be writable by the web server.  This will log the explain plan and timing information of queries to the file.
		 * @return void
		 */
		function Database ($dbtype, $dbuser, $dbpass, $dbname='', $dbhost='', $dbport='', $dberrorlog='', $dbupdatelog='', $dbquerylog='', $dbexplainlog='') {
			global $debug;

			$this->version = 0.1;
			$this->_errorMessage = '';
			$this->_lastQuery = '';
			$this->_timing = true;
			if (empty($dbname)) {
				$dbname = $dbuser;
			}
			$this->_dbName = $dbname;
			$this->_dbType = $dbtype;
			$this->_dbErrorLog = $dberrorlog;
			$this->_dbUpdateLog = $dbupdatelog;
			$this->_dbQueryLog = $dbquerylog;
			$this->_dbExplainLog = $dbexplainlog;

			if ($this->_dbType == 'PgSQL') {
				if (empty($dbport)) {
					$dbport = 5432;
				}

				$connect_string = "dbname=$dbname user=$dbuser password=$dbpass";

				if (!empty($dbhost)) {
					$connect_string .= " host=$dbhost port=$dbport";
				}

				debug ('Connecting to PgSQL with: ' . str_replace ('password=' . $dbpass, 'password=' . str_repeat ('*', strlen($dbpass)), $connect_string), 3);

				$ob = ob_get_contents ();
				ob_end_clean ();

				ob_start ();

				$debug->changeDebugLevel (2);
				$this->_dbh = pg_connect ($connect_string);
				$debug->changeDebugLevel ();

				$this->_errorMessage = str_replace ('<b>Warning</b>:', '', str_replace ('<br />', '', ob_get_contents ()));

				ob_end_clean ();

				ob_start ();
				echo $ob;

				if (!empty ($this->_dbExplainLog)) {
					if (is_writable ($this->_dbExplainLog)) {
						$this->executeSql ('VACUUM ANALYZE');
					} else {
						debug ('Database Explain Log Not Writable: ' . $this->_dbExplainLog, 2);
					}
				}

				if (empty ($this->_errorMessage)) {
					$this->doUpdate ('BEGIN');
				}

				$this->_numericDataTypes = array ('bigint', 'integer');
			} else if ($this->_dbType == 'MySQL') {
				if (empty($dbport)) {
					$dbport = 3306;
				}
				if (empty($dbhost)) {
					$connect_string = 'localhost';
				} else {
					$connect_string = $dbhost . ':' . $dbport;
				}

				debug ("Connecting to MySQL with: mysql_connect ('$dbhost', '$dbuser', '" . str_repeat ('*', strlen($dbpass)) . "') and db: '$dbhost'", 3);

				if ($this->_dbh = mysql_connect ($dbhost, $dbuser, $dbpass)) {
					mysql_select_db ($this->_dbName, $this->_dbh);
				}

				if (mysql_error ($this->_dbh) > '') {
					$this->_errorMessage = mysql_error ($this->_dbh);
				}
			} else if ($this->_dbType == 'Oracle') {
				debug ("Connecting to Oracle with: OCILogon ('$dbuser', '" . str_repeat ('*', strlen($dbpass)) . "', '$dbhost')", 3);

				$this->_dbh = OCILogon ($dbuser, $dbpass, $dbhost);

				$error = OCIError ($this->_dbh);
				if ((isset ($error['code'])) && (!empty ($error['code']))) {
					$this->_errorMessage = $error['code'] . ': ' . $error['message'];
				}
			} else if ($this->_dbtype == 'Informix') {
				debug ("Connecting to Informix with: ifx_connect ('$dbname@$dbhost', '$dbuser', '" . str_repeat ('*', strlen($dbpass)) . "')", 3);

				$this->_dbh = ifx_connect ($dbname . '@' . $dbhost, $dbuser, $dbpass);
				if (ifx_errormsg () > '') {
					ifx_textasvarchar (1);
				} else {
					$this->_errorMessage = ifx_error () . ': ' . ifx_errormsg ();
				}
			} else {
				debug ('This library supports PgSQL, MySQL, Oracle, and Informix', 1);
			}

			if (!empty($this->_errorMessage)) {
				debug ('DB Connect Error: ' . $this->_errorMessage, 1);
				unset ($this->_dbh);
			}
		}

		/**
		 * Executes a insert/update/delete query
		 *
		 * @access public
		 * @param string The query to execute
		 * @return integer Status of 0 means success, status > 0 is failure.  This means if returned value is evaluated as boolean, success will be false.  But its done opposite so that many consequtive doUpdates can be run in sucesion and their returns can be totaled.
		 */
		function doUpdate ($query) {
			global $config, $dbs, $DBREPLICATION, $DBCONNECTEDTOALL;

			debug ('Begin Update: ' . $query, -1);

			if (($this->_dbType == 'PgSQL') && ($DBREPLICATION)) {
				if (($query == 'BEGIN') || ($query == 'ROLLBACK')) {
					$this->_dbTransactionLog = '';
				} else if ($query == 'COMMIT') {
					if (!empty ($this->_dbUpdateLog)) {
						if (is_writable ($this->_dbUpdateLog)) {
							error_log ("$query\n", 3, $this->_dbUpdateLog);
						} else {
							debug ('Database Update Log Not Writable: ' . $this->_dbUpdateLog, 1);
						}
					}
				} else {
					$this->_dbTransactionLog .= $query . ";\n";

					if (!$DBCONNECTEDTOALL) {
						reset ($config['DB']);
						foreach ($config['DB'] as $dbcount=>$dbinfo) {
							if (!isset ($dbs[$dbcount])) {
								if ($dbinfo['TYPE'] == 'PgSQL') {
									debug ('Connecting for replication to DB ' . $dbcount, 3);
									$dbs[$dbcount] = new Database ($dbinfo['TYPE'], $dbinfo['USER'], $dbinfo['PASS'], $dbinfo['NAME'], $dbinfo['HOST']);
								}
							}
						}

						$DBCONNECTEDTOALL = true;					
					}
				}

				debug ('Update Query: ' . $query, 3);

				if (!empty ($config['DBLOCKFILE'])) {
					while (file_get_contents ($config['DBLOCKFILE']) == 1) {
						debug ('Sleeping until database unlocks', 2);
						sleep (1);
					}
				}

				$retval = 0;
				reset ($dbs);
				foreach ($dbs as $dbcount=>$db) {
					debug ('Running update on DB ' . $dbcount, 3);
					$retval += $db->executeSql ($query);
				}

				if ($retval > 1) {
					$retval = 1;
				}

				return $retval;
			} else {
				debug ('Update Query: ' . $query, 3);
				return $this->executeSql ($query);
			}

			debug ('End Update: ' . $query, -1);
		}

		/**
		 * Executes a select query and populates given array with the resulting data
		 *
		 * @access public
		 * @param string The query to execute
		 * @param array (row_count=>array (column_name)) Passed by reference. This variable will be initialized and then populated with data
		 * @return integer Number of rows in result
		 */
		function doQuery ($query, &$data) {
			if ($this->_timing) {
				debug ('Begin Query: ' . $query, -1);
			}

			$data = array ();
			debug ('Query: ' . $query, 3);
			$this->executeSql ($query);

			if ($this->_dbType == 'PgSQL') {
				if (!empty ($this->_result)) {
					$count = pg_numrows ($this->_result);
				} else {
					$count = 0;
				}

				for ($x=1; $x <= $count; $x++) {
					$data[$x] = pg_fetch_array ($this->_result, $x-1, PGSQL_ASSOC);
				}

				if (!empty ($this->_dbExplainLog)) {
					error_log ('DB EXPLAIN: ' . $query . "\n\tFILE: " . $_SERVER['PHP_SELF'] . '    DATE: ' . date('Y-m-d H:i:s') . "\n\tPLAN: ", 3, $this->_dbExplainLog);

					$this->executeSql ('EXPLAIN ANALYZE ' . $query);
					$explain_count = pg_numrows ($this->_result);
					for ($x=0; $x < $explain_count; $x++) {
						$plan = pg_fetch_array ($this->_result, $x, PGSQL_ASSOC);
						error_log ($plan['QUERY PLAN'] . "\n", 3, $this->_dbExplainLog);
					}

					error_log ("\n", 3, $this->_dbExplainLog);
				}

				if (!empty ($this->_result)) {
					pg_freeresult ($this->_result);
				}
			} else if ($this->_dbType == 'MySQL') {
				if (!empty ($this->_result)) {
					$count = mysql_num_rows ($this->_result);
				} else {
					$count = 0;
				}

				for ($x=1; $x <= $count; $x++) {
					$data[$x] = mysql_fetch_array ($this->_result, MYSQL_ASSOC);
				}

				if (!empty ($this->_result)) {
					mysql_free_result ($this->_result);
				}
			} else if ($this->_dbtype == 'Oracle') {
				$count = 0;
				while (OCIFetchInto ($this->_result, $row, OCI_ASSOC)) {
					$count++;
					$data[$count] = $row;
				}

				if (!empty ($this->_result)) {
					ocifreestatement ($this->_result);
				}
			} else if ($this->_dbtype == 'Informix') {
				if (!empty ($this->_result)) {
					$count = 0;

					$row = ifx_fetch_row ($this->_result, 'NEXT');
					while (is_array ($row)) {
						$count++;
						foreach ($row as $row_index => $row_data) {
							$row[$row_index] = trim ($row_data);
						}

						$data[$count] = $row;
						$row = ifx_fetch_row ($this->_result, 'NEXT');
					}
				}

				if (!empty ($this->_result)) {
					ifx_free_result ($this->_result);
				}
			} else {
				$count = -1;
			}

			if ($this->_timing) {
				debug ('End Query: ' . $query, -1);
			}

			return $count;
		}

		/**
		 * Similar to doQuery but only returns one row
		 *
		 * @access public
		 * @param string The query to execute
		 * @return array (array (column_name => value))
		 */
		function doQuery1Row ($query) {
			$retval = array ();
			$this->doQuery ($query, $retval);
			if (isset($retval[1])) {
				return $retval[1];
			} else {
				return array ();
			}
		}

		/**
		 * Similar to doQuery but only returns one column
		 *
		 * @access public
		 * @param string The query to execute
		 * @return array (row_count=>data)
		 */
		function doQuery1Column ($query) {
			$tempdatass = array ();
			$retval = array ();

			$this->doQuery ($query, $tempdatass);

			foreach ($tempdatass as $count=>$tempdatas) {
				foreach ($tempdatas as $column_name=>$tempdata) {
					$retval[$count] = $tempdata;
					break;
				}
			}

			return $retval;
		}

		/**
		 * Similar to doQuery but only returns one column
		 *
		 * @access public
		 * @param string The query to execute
		 * @return array (row_count=>data)
		 */
		function doQuery1ColumnSimple ($query) {
			$tempdatass = array ();
			$retval = array ();

			$this->doQuery ($query, $tempdatass);

			foreach ($tempdatass as $count=>$tempdatas) {
				foreach ($tempdatas as $column_name=>$tempdata) {
					array_push ($retval, $tempdata);
					break;
				}
			}

			return $retval;
		}

		/**
		 * Similar to doQuery but only returns one column of first row
		 *
		 * @access public
		 * @param string The query to execute
		 * @return string data
		 */
		function doQuery1Row1Column ($query) {
			$tempdatass = array ();
			$retval = array ();

			$this->doQuery ($query, $tempdatass);

			foreach ($tempdatass as $count=>$tempdatas) {
				foreach ($tempdatas as $column_name=>$tempdata) {
					return $tempdata;
				}
			}

			return false;
		}

		/**
		 * Similar to doQuery but only returns one column
		 *
		 * @access public
		 * @param string The query to execute
		 * @return array (data1=>data2)
		 */
		function doQuery2Columns ($query) {
			$tempdatass = array ();
			$retval = array ();

			$this->doQuery ($query, $tempdatass);

			foreach ($tempdatass as $count=>$tempdatas) {
				unset ($key);
				foreach ($tempdatas as $column_name=>$tempdata) {
					if (!isset ($key)) {
						$key = $tempdata;
					} else {
						$retval[$key] = $tempdata;
						break;
					}
				}
			}

			return $retval;
		}
		
		/**
		 * Uses system table to fully describe a given table
		 *
		 * @access public
		 * @param string Either just the table_name, or schema.table_name
		 * @return array (column_count=>array ('column_id', 'column_name', 'data_type', 'data_length', 'is_nullable', 'has_default', 'default_value', 'primary_key', 'unique_key', 'is_indexed', 'foreign_key', 'foreign_table', 'foreign_column', 'sequenced'))
		 */
		function doDescribe ($table_name='') {
			debug ('Begin Description: ' . $table_name, -1);

			$this->_timing = false;
			$description = array ();
			$table_name = strtolower ($table_name);

			if (empty ($table_name)) {
				debug ('doDescribe refusing to describe because no table name was given', 2);
			} else {
				if ($this->_dbType == 'PgSQL') {
					$schema = 'public';
					if (strpos ($table_name, '.') !== false) {
						list ($schema, $table_name) = explode ('.', $table_name, 2);
					}

					$description_count = $this->doQuery ("SELECT a.attname AS column_name, 
							format_type(a.atttypid, a.atttypmod) AS data_type, a.attnum as column_id, 
							a.attnotnull AS is_nullable, a.atthasdef AS has_default, 
							a.attlen AS data_length, '' as primary_key, '' as unique_key, 
							'' as is_indexed, '' as foreign_key, '' as foreign_table, '' as foreign_column, 
							(SELECT ad.adsrc 
								FROM pg_attrdef ad 
								WHERE ad.adrelid = c.oid
									AND a.attnum = ad.adnum 
							) AS default_value, 
							(SELECT d.description 
								FROM pg_description d
								WHERE d.objoid = c.oid
									AND d.objsubid = 0
							) AS table_comment, 
							(SELECT d.description 
								FROM pg_description d
								WHERE d.objoid = c.oid
									AND d.objsubid = a.attnum
							) AS column_comment
						FROM pg_class c, pg_attribute a 
						WHERE c.relname = '$table_name' 
							AND a.attnum > 0 
							AND a.attisdropped = 'f'
							AND a.attrelid = c.oid 
							AND c.relnamespace = (SELECT n.oid 
									FROM pg_namespace n 
									WHERE n.nspname = '$schema' 
								)
						ORDER BY a.attnum", $description);

					for ($index = 1; $index <= $description_count; $index++) {
						$nullable_temp = $description[$index]['is_nullable'];
						($description[$index]['is_nullable'] == 't') ? $description[$index]['is_nullable'] = false : $description[$index]['is_nullable'] = true;
						($description[$index]['has_default'] == 't') ? $description[$index]['has_default'] = true : $description[$index]['has_default'] = false;
						if (substr($description[$index]['data_type'], 0, 17) == 'character varying') {
							$description[$index]['data_length'] = substr($description[$index]['data_type'], 18, -1);
							$description[$index]['data_type'] = 'varchar';
						}
						if (substr($description[$index]['data_type'], 0, 9) == 'character') {
							$description[$index]['data_length'] = substr($description[$index]['data_type'], 10, -1);
							$description[$index]['data_type'] = 'char';
						}
						if (substr($description[$index]['default_value'], 0, 7) == 'nextval') {
							$description[$index]['sequenced'] = true;
						} else {
							$description[$index]['sequenced'] = false;
						}

						$description[$index]['table_name'] = $table_name;
						if ($schema != 'public') {
							$description[$index]['table_name'] = $schema . '.' . $description[$index]['table_name'];
						}
					}

					$index_count = $this->doQuery ("SELECT conname AS name, consrc, contype AS type, indkey AS column_id FROM (
									SELECT conname,  
										CASE WHEN contype='f' THEN
											pg_catalog.pg_get_constraintdef(oid)
										ELSE
											'CHECK ' || consrc
										END AS consrc, 
										contype, conrelid AS relid, NULL AS indkey
									FROM pg_catalog.pg_constraint
									WHERE contype IN ('f', 'c')
								UNION ALL
									SELECT pc.relname, NULL, 
									CASE WHEN indisprimary THEN
										'p'
									ELSE
										CASE WHEN indisunique THEN
											'u'
										ELSE 
											'i'
										END
									END, 
									pi.indrelid, indkey 
									FROM pg_catalog.pg_class pc,
										pg_catalog.pg_index pi
									WHERE pc.oid = pi.indexrelid) AS sub
							WHERE relid = (SELECT oid FROM pg_class WHERE relname='$table_name'
							                AND relnamespace = (SELECT oid FROM pg_namespace
							                WHERE nspname='$schema'))", $indexes);

					for ($index = 1; $index <= $index_count; $index++) {
						for ($index2 = 1; $index2 <= $description_count; $index2++) {
							if ($indexes[$index]['type'] == 'f') {
								$fk_pieces = explode (' ', $indexes[$index]['consrc']);
								if ($description[$index2]['column_name'] == str_replace ('"', '', substr ($fk_pieces[2], 1, -1))) {
									$fk_pieces2 = explode ('(', $fk_pieces[4]);
									$description[$index2]['foreign_key'] = str_replace ('"', '', $indexes[$index]['name']);
									$description[$index2]['foreign_table'] = str_replace ('"', '', $fk_pieces2[0]);
									$description[$index2]['foreign_column'] = str_replace ('"', '', substr ($fk_pieces2[1], 0, -1));
								}
							} else {
								if ($description[$index2]['column_id'] == $indexes[$index]['column_id']) {
									if ($indexes[$index]['type'] == 'p') {
										$description[$index2]['primary_key'] = $indexes[$index]['name'];
									} else if ($indexes[$index]['type'] == 'u') {
										$description[$index2]['unique_key'] = $indexes[$index]['name'];
									} else if ($indexes[$index]['type'] == 'i') {
										$description[$index2]['is_indexed'] = true;
									}
								}
							}
						}
					}
				} else if ($this->_dbType == 'MySQL') {
					$description_count = $this->doQuery ('SHOW COLUMNS FROM ' . $table_name, $description_temp);
					for ($index = 1; $index <= $description_count; $index++) {
						$description[$index]['column_name'] = $description_temp[$index]['Field'];
						$description[$index]['data_type'] = $description_temp[$index]['Type'];
						$description[$index]['column_id'] = $index;
						($description_temp[$index]['Null'] == 'YES') ? $description[$index]['is_nullable'] = true : $description[$index]['is_nullable'] = false;
						($description_temp[$index]['Default'] > '') ? $description[$index]['has_default'] = true : $description[$index]['has_default'] = false;
						if (strpos($description[$index]['data_type'], '(') > 0) {
							$description[$index]['data_length'] = substr($description[$index]['data_type'], strpos($description[$index]['data_type'], '(') + 1, -1);
							$description[$index]['data_type'] = substr($description[$index]['data_type'], 0, strpos($description[$index]['data_type'], '('));
						}
						$description[$index]['data_type'] = str_replace ('int', 'integer', $description[$index]['data_type']);
						$description[$index]['default_value'] = $description_temp[$index]['Default'];
						if ($description_temp[$index]['Extra'] == 'auto_increment') 
							$description[$index]['default_value'] = 'auto_increment';
						($description_temp[$index]['Key'] == 'PRI') ? $description[$index]['primary_key'] = true : $description[$index]['primary_key'] = false;
						($description_temp[$index]['Key'] == 'UNI') ? $description[$index]['unique_key'] = true : $description[$index]['unique_key'] = false;
						($description_temp[$index]['Key'] == 'MUL') ? $description[$index]['is_indexed'] = true : $description[$index]['is_indexed'] = false;
						$description[$index]['foreign_key'] = '';
						$description[$index]['foreign_table'] = '';
						$description[$index]['foreign_column'] = '';
						$description[$index]['sequenced'] = false;
					}
				}
			}

			debug ('End Description: ' . $table_name, -1);
			$this->_timing = true;

			return $description;
		}

		/**
		 * Gets last error message stored by this library
		 *
		 * @access private
		 * @param string Query to execute
		 * @return result ID
		 */
		function executeSql ($query) {
			$this->_lastQuery = $query;

			if (!empty($this->_dbh)) {
				if ($this->_dbType == 'PgSQL') {
					$this->_result = @pg_exec ($this->_dbh, $query);
					$this->_errorMessage = pg_last_error ($this->_dbh);
				} else if ($this->_dbType == 'MySQL') {
					$this->_result = mysql_query ($query, $this->_dbh);
					$this->_errorMessage = mysql_error ($this->_dbh);
				} else if ($this->_dbtype == 'Oracle') {
					$this->_result = OCIParse ($this->_dbh, $query);
					OCIExecute ($this->_result, OCI_DEFAULT);
					$error = OCIError ($this->_result);
					if (is_array ($error)) {
						$this->_errorMessage = $error['message'];
					} else {
						$this->_errorMessage = '';
					}
				} else if ($this->_dbtype == 'Informix') {
					if (strtoupper (substr ($query, 0, 6)) == 'SELECT') {
						$this->_result = ifx_query ($query, $this->_dbh, IFX_SCROLL);
					} else {
						$this->_result = ifx_query ($query, $this->_dbh);
					}

					$this->_errorMessage = ifx_errormsg ($this->_dbh);

					$acceptableMessages = array ("Bad file number\n", "No children\n");

					if (in_array ($this->_errorMessage, $acceptableMessages)) {
						$this->_errorMessage = '';
					}
				}
			} else {
				$this->_errorMessage = 'Not Connected to Database';
			}

			if ($this->_errorMessage > '') {
				$retval = 1;

				debug ('DB ERROR: ' . $this->_errorMessage . ' EXECUTING: ' . $query, 1);
				if (!empty ($this->_dbErrorLog)) {
					if (is_writable ($this->_dbErrorLog)) {
						error_log ('	DB ERROR: ' . $this->_errorMessage . "\n\tFILE: " . $_SERVER['PHP_SELF'] . '    DATE: ' . date('Y-m-d H:i:s') . "\n\tEXECUTING: $query\n\n", 3, $this->_dbErrorLog);
					} else {
						debug ('Database Error Log Not Writable: ' . $this->_dbErrorLog, 2);
					}
				}
			} else {
				if (!empty ($this->_dbQueryLog)) {
					if (is_writable ($this->_dbQueryLog)) {
						error_log ('	FILE: ' . $_SERVER['PHP_SELF'] . '    DATE: ' . date('Y-m-d H:i:s') . "\n\tEXECUTING: $query\n\n", 3, $this->_dbQueryLog);
					} else {
						debug ('Database Query Log Not Writable: ' . $this->_dbQueryLog, 2);
					}
				}

				$retval = 0;
			}

			return $retval;
		}

		/**
		 * Gets last error message stored by this library
		 *
		 * @access public
		 * @return string last error message
		 */
		function getLastError () {
			return $this->_errorMessage;
		}

		/**
		 * Gets the last ID generated from an auto_increment or sequence
		 *
		 * @access public
		 * @param string Name of the sequence last used, only required for PgSQL currently
		 * @return integer last ID on success, < 1 on failure
		 */
		function getAutoID ($sequence_name='') {
			if ($this->_dbType == 'PgSQL') {
				if (empty ($sequence_name)) {
					debug ('getAutoID requires a sequence_name for PgSQL', 2);
				} else {
					if (isset ($this->_result) && ($this->_result > 0)) {
						$sequence_count = $this->doQuery ("SELECT currval('$sequence_name')", $sequence);
						if ($sequence_count > 0) {
							return $sequence[1]['currval'];
						}
					} else {
						debug ('getAutoID called before query', 2);
					}
				}

				return false;
			} else if ($this->_dbType == 'MySQL') {
				if (isset ($this->_dbh) && ($this->_dbh > 0)) {
					return mysql_insert_id ($this->_dbh);
				} else {
					debug ('getAutoID called before query', 2);
					return false;
				}
			}
		}

		/**
		 * Performs a commit on the current database
		 *
		 * @access public
		 * @return boolean Whether commit was successful
		 */
		function commit () {
			if ($this->_dbType == 'PgSQL') {
				$this->doUpdate ('COMMIT');
				$err = pg_last_error ($this->_dbh);
				$this->doUpdate ('BEGIN');
				if (!empty ($err)) {
					$this->_errorMessage = $err;
					debug ('DB Commit Error: ' . $err, 3);
					return false;
				}
			} else if ($this->_dbType == 'Oracle') {
				OCICommit ($this->_dbh);
			}

			return true;
		}

		/**
		 * Performs a rollback on the current database
		 *
		 * @access public
		 * @return boolean Whether rollback was successful
		 */
		function rollback () {
			if ($this->_dbType == 'PgSQL') {
				$this->doUpdate ('ROLLBACK');
				$err = pg_last_error ($this->_dbh);
				$this->doUpdate ('BEGIN');
				if (!empty ($err)) {
					$this->_errorMessage = $err;
					debug ('DB Rollback Error: ' . $err, 3);
					return false;
				}
			} else if ($this->_dbType == 'Oracle') {
				OCIRollback ($this->_dbh);
			}

			return true;
		}

		/**
		 * Closes connection to database
		 *
		 * @access public
		 * @return void
		 */
		function close () {
			if ((isset ($this->_dbh)) && ($this->_dbh)) {
				if ($this->_dbType == 'PgSQL') {
					pg_close ($this->_dbh);
					debug ('Closing Connection to PgSQL', 3);
				} else if ($this->_dbType == 'MySQL') {
					mysql_close ($this->_dbh);
					debug ('Closing Connection to MySQL', 3);
				} else if ($this->_dbType == 'Oracle') {
					OCILogoff ($this->_dbh);
					debug ('Closing Connection to Oracle', 3);
				} else if ($this->_dbtype == 'Informix') {
					ifx_close ($this->_dbh);
					debug ('Closing Connection to Informix', 3);
				}
			}

			unset ($this->_dbh);
		}

		/**
		 * Eliminates escaped characters from a POST operation
		 *
		 * @access public
		 * @param string value to modify
		 * @param boolean whether new lines are acceptable in returned value
		 * @return string possibly modified value
		 */
		function sqlEscape ($text, $remove_newlines=false, $convert_quotes=false) {
			$text = str_replace ("\\", '', $text);		// solve the \" and other escaped chars
			$text = str_replace ("'", "\\'", $text);
			$text = str_replace ('…', '...', $text);	// MSWord ellipsis #8230

			if ($remove_newlines) {
				$text = str_replace (chr(13) . chr(10), '', $text);
				$text = str_replace (chr(13), '', $text);
				$text = str_replace (chr(10), '', $text);
			}

			if ($convert_quotes) {
				$text = str_replace ('”', '"', $text);		// MSWord close smartquote #8220
				$text = str_replace ('’', "\\'", $text);
				$text = str_replace ('`', "\\'", $text);  
				$text = str_replace ('“', '"', $text);		// MSWord open smartquote #8221
			}

			return $text;
		}

		/**
		 * Returns a 'NULL' if passed in data is null, otherwise quotes given data, usefull for passing in database updates or inserts
		 *
		 * @access public
		 * @param string value to modify
		 * @return string possibly modified value
		 */
		function orNull ($data) {
			if (($data == '') && (!(is_numeric ($data) && ($data === 0)))) {
				return 'NULL';
			} else {
				return "'$data'";
			}
		}

		/**
		 * Returns a 0 if passed in value is null 
		 *
		 * @access public
		 * @param string value to modify
		 * @return string possibly modified value
		 */
		function or0 ($data) {
			if (($data == '') && (!(is_numeric ($data) && ($data === 0)))) {
				return "'0'";
			} else {
				return "'$data'";
			}
		}

		/**
		 * Sets database as locked with file that can be on a share across webservers
		 *
		 * @access public
		 * @return void
		 */
		function lock () {
			global $config;

			if ($fp = fopen ($config['DBLOCKFILE'], 'w')) { 
				fwrite ($fp, 1);
			} else {
				debug ('Could not open database lock file', 1);
			}
		}

		/**
		 * Sets database as unlocked with file that can be on a share across webservers
		 *
		 * @access public
		 * @return void
		 */
		function unlock () {
			global $config;

			if ($fp = fopen ($config['DBLOCKFILE'], 'w')) { 
				fwrite ($fp, 0);
			} else {
				debug ('Could not open database lock file', 1);
			}
		}

		/**
		 * Gets list of all tables visible to current user
		 *
		 * @access public
		 * @return array Same format as doQuery1Column
		 */
		function getTableList () {
			if ($this->_dbType == 'PgSQL') {
				return $this->doQuery1Column ("SELECT CASE WHEN schemaname = 'public' THEN
													tablename 
												ELSE
													schemaname||'.'||tablename 
												END AS tablename 
											FROM pg_tables 
											WHERE schemaname != 'pg_catalog' 
											ORDER BY schemaname, tablename");
			}
		}

		/**
		 * Sets the comment on an object in the database
		 *
		 * @access public
		 * @return void
		 */
		function setComment ($type, $name, $comment) {
			if ($this->_dbType == 'PgSQL') {
				$supportedTypes = array ('TABLE', 'COLUMN');

				if (in_array (strtoupper ($type), $supportedTypes)) {
					$this->doUpdate ("COMMENT ON $type $name IS '$comment'");
					$this->commit ();
				} else {
					debug ('classDatabase->setComment does not support comment type ' . $type, 1);
				}
			} else {
				debug ('classDatabase->setComment does not support database type ' . $this->_dbType, 1);
			}
		}
	}
}
?>
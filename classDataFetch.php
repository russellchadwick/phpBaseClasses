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
 * @version $Revision: 1.4 $ $Date: 2003/09/04 20:48:04 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSDATAFETCH')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSDATAFETCH', 1);

	/**
	 * Load $QUERIES from files listed in config.php, queries are seperated by two newlines, the 
	 * name of the query is the first line and the query itself is the rest.  This feature is 
	 * quite useful if you have a DBA who can now optimize queries without programmer intervention.
	 */
	if ((isset($config['QUERY_DIRECTORY'])) && (isset($config['QUERY_FILES']))) {
		foreach ($config['QUERY_FILES'] as $filename) {
			if ($fd = @fopen ($config['QUERY_DIRECTORY'] . '/' . $filename, 'rb')) {
				$queries = explode ("\n\n", fread ($fd, filesize ($config['QUERY_DIRECTORY'] . '/' . $filename)));
				fclose ($fd);
				foreach ($queries as $query) {
					@list ($query_name, $query_string) = explode ("\n", $query, 2);
					$QUERIES[$query_name] = $query_string;
				}
			}

			unset ($query_name, $query_string, $queries, $query, $fd, $filename);
		}
	}

	/**
	 * Classes to assist in fetching data, currently can generate SQL queries and 
	 * fill in values in template queries
	 *
	 * @package phpBaseClasses
	 */
	class DataFetch {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Array for holding descriptions of tables
		 *
		 * @var array $_descriptions
		 * @access private
		 */
		var $description;

		/**
		 * Constructor which optionally takes a description.  If a description is not
		 * provided, creating queries will fail.
		 *
		 * @access public
		 * @param array Description from $db->doDescribe
		 * @return void
		 */
		function DataFetch ($description='') {
			if (is_array ($description)) 
				$this->loadDescription ($description);
		}

		/**
		 * Clears all stored descriptions and loads a description of a table.
		 *
		 * @access public
		 * @param array Description from $db->doDescribe
		 * @return void
		 */
		function loadDescription ($description) {
			$this->_descriptions = array ();
			$this->addDescription ($description);
		}

		/**
		 * Adds an description of a table.  Needed from generating joins in a query.
		 * Either provide a description or a valid table name and it will fetch the description.
		 *
		 * @access public
		 * @param array Description from $db->doDescribe
		 * @return void
		 */
		function addDescription ($description) {
			global $db;

			if (!is_array ($description)) 
				$description = $db->doDescribe ($description);

			if (is_array ($description)) 
				$this->_descriptions[count($this->_descriptions)+1] = $description;
		}

		/**
		 * Generates a select query from stored table information
		 * Search parameters array should have these fields
		 *    search_field=>string what column to search on
		 *    specific=>boolean
		 *    search_text=>what string to search for in search_field
		 *
		 * @access public
		 * @param string order_by What to order the results by
		 * @param array Search parameters
		 * @return string Select query
		 */
		function generateFetch ($order_by='', $search_parameters='') {
			$select_query = 'SELECT  ';
			$from_clause = ' FROM  ';
			$where_clause = 'WHERE ';

			$aliases_used = array ();

			if (empty($search_parameters)) 
				$search_parameters = array ();

			foreach ($this->_descriptions[1] as $key=>$description) {
				if ($key == 1) 
					$from_clause .= $description['table_name'] . ', ';

				$alias = '';
				$add_columns_of = '';
				$prefix = '';

				if (!empty ($description['foreign_key'])) {
					$alias = substr ($description['foreign_column'], 0, 3);
					if (in_array ($alias, $aliases_used)) {
						foreach (range (0, 99) as $alias_ending) {
							$alias = substr ($description['foreign_column'], 0, 2) . $alias_ending;
							if (!in_array ($alias, $aliases_used)) 
								break;
						}
					}

					foreach ($this->_descriptions as $description_count=>$one_description) {
						foreach ($one_description as $key2=>$description2) {
							if (($description['foreign_table'] == $description2['table_name']) && ($description['foreign_column'] == $description2['column_name'])) {
								$add_columns_of = $description_count;
								$prefix = str_replace ('id', '', str_replace ('_id', '', $description['column_name']));
								$from_clause .= $description['foreign_table'] . " $alias, ";
								$where_clause .= $alias . '.' . $description['foreign_column'] . ' = ' . $description['table_name'] . '.' . $description['column_name'] . ' AND  ';
								array_push ($aliases_used, $alias);
							}
						}
					}
				}

				$select_query .= $description['table_name'] . '.' . $description['column_name'] . ', ';
				if (!empty ($add_columns_of)) {
					foreach ($this->_descriptions[$add_columns_of] as $key=>$description) {
						$select_query .= $alias . '.' . $description['column_name'] . ' AS ' . $prefix . '_' . $description['column_name'] . ', ';
					}
				}

				foreach ($search_parameters as $key2=>$search_parameter) {
					if ((!empty($description['primary_key'])) && (empty($primary_key))) 
						$primary_key = $description['column_name'];

					if ((strpos (' ' . $description['data_type'], 'char') > 0) && (empty($primary_data))) 
						$primary_data = $description['column_name'];

					if (($description['column_name'] == $search_parameter['search_field']) || (($search_parameter['search_field'] == 'primary_key') && ($description['column_name'] == $primary_key))) {
						$where_clause .= $description['column_name'] . ' ';
						if ($search_parameter['specific']) {
							$where_clause .= "= '" . $search_parameter['search_text'] . "' AND  ";
						} else {
							$where_clause .= "LIKE '%" . $search_parameter['search_text'] . "%' AND  ";
						}
					}
				}
			}

			if (isset ($description)) {
				$select_query = substr ($select_query, 0, -2) . ' ' . substr ($from_clause, 0, -2) . ' ' . substr($where_clause, 0, -6);
				if (!empty ($order_by)) {
					$select_query .= ' ORDER BY ' . $order_by;
				} else if (!empty ($primary_data)) {
					$select_query .= ' ORDER BY ' . $primary_data;
				}
			} else {
				debug ('No Description Provided', 2);
				$select_query = '';
			}

			return $select_query;
		}

		/**
		 * Generates and executes a select query from stored table information
		 * Search parameters array should have these fields
		 *    search_field=>string what column to search on
		 *    specific=>boolean
		 *    search_text=>what string to search for in search_field
		 *
		 * @access public
		 * @param string order_by What to order the results by
		 * @param array Search parameters
		 * @return array Data fetched by running the generated query
		 */
		function executeFetch ($order_by='', $search_parameters='') {
			global $db;

			$select_query = $this->generateFetch ($order_by, $search_parameters);
			$db->doQuery ($select_query, $data);

			return $data;
		}

		/**
		 * Replaces a field in a given query
		 *
		 * @access public
		 * @param string original query
		 * @param string field name to replace on
		 * @param string value to put in its place
		 * @return string new query
		 */
		function queryParam ($query, $field, $value) {
			return str_replace ("#$field#", $value, $query);
		}

		/**
		 * Replaces many fields in a given query
		 * Params array has the following fields
		 *    $field_1=>$value_1
		 *    $field_2=>$value_2
		 *    $field_x=>$value_x
		 *
		 * @access public
		 * @param string original query
		 * @param array values to pass to queryParam
		 * @return string new query
		 */
		function queryParamArray ($query, $params) {
			while (list($key, $value) = each ($params)) {
				$query = $this->queryParam ($query, $key, $value);
			}

			return $query;
		}
	}
}
?>
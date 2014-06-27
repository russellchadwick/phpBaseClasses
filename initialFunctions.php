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
 * @version $Revision: 1.5 $ $Date: 2004/01/13 18:43:59 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('INITIALFUNCTIONS')) {
        return;
} else {
	define('INITIALFUNCTIONS', 1);

	function get_microtime () { 
		list ($usec, $sec) = explode (' ', microtime ()); 
		return ((float) $usec + (float) $sec); 
	} 

	function populate_defaults ($options, $default_options) {
		if (empty ($options)) {
			$options = array ();
		}

		if ((is_array ($options)) && (is_array ($default_options))) {
			foreach ($default_options as $name => $value) {
				debug ('Populate Defaults Now Evaluating: ' . $name . ' (' . @$options[$name] . ' | ' . @$default_options[$name] . ')', 3);

				if (($name == 1) && (is_array ($value))) {
					foreach ($options as $option_name => $option_value) {
						$options[$option_name] = populate_defaults ($options[$option_name], $default_options[$name]);
					}
				} else {
					if (is_array ($value)) {
						if (!isset ($options[$name])) {
							$options[$name] = array ();
						}

						$options[$name] = populate_defaults ($options[$name], $default_options[$name]);
					} else {
						if (!isset ($options[$name])) {
							$options[$name] = $default_options[$name];
						}
					}
				}
			}
		}
		return $options;
	}
}
?>
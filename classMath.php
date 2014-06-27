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
 * @version $Revision: 1.2 $ $Date: 2004/02/19 23:34:44 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSMATH')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSMATH', 1);

	/**
	 * The library with common math functions
	 *
	 * This class has functions for all common math operations
	 * not covered by built in php functions
	 *
	 * @package phpBaseClasses
	 */
	class Math {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Constructor sets all private class variables
		 *
		 * @access public
		 * @return void
		 */
		function Math () {
			$this->_version = 0.1;
		}

		/**
		 * Get the minimum value of all the numbers given
		 *
		 * @access public
		 * @param array numbers to minimum of
		 * @return integer minimum value of all the numbers given
		 */
		function min ($numbers) {
			$retval = $numbers[0];

			foreach ($numbers as $number) 
				if ($number < $retval) 
					$retval = $number;

			return $retval;
		}

		/**
		 * Get the maximum value of all the numbers given
		 *
		 * @access public
		 * @param array numbers to maximum of
		 * @return integer maximum value of all the numbers given
		 */
		function max ($numbers) {
			$retval = $numbers[0];

			foreach ($numbers as $number) 
				if ($number > $retval) 
					$retval = $number;

			return $retval;
		}

		/**
		 * Get the greatest common factor of all the numbers given
		 *
		 * @access public
		 * @param array numbers to find greatest common factor of
		 * @return integer greatest common factor of all the numbers given
		 */
		function greatest_common_factor ($numbers) {
			$max = $this->max ($numbers);

			$gcf = 1;

			for ($index = $max; $index > 0; $index--) {
				$gcf = 1;
				$gcf_test = true;
				foreach ($numbers as $number) 
					if ($number % $index !== 0)
						$gcf_test = false;

				if ($gcf_test) {
					$gcf = $index;
					break;
				}
			}

			return $gcf;
		}

		/**
		 * Alias for greatest_common_factor
		 *
		 * @access public
		 * @param array numbers to find greatest common factor of
		 * @return integer greatest common factor of all the numbers given
		 */
		function gcf ($numbers) {
			return $this->greatest_common_factor ($numbers);
		}

		/**
		 * Returns the numbers reduced by dividing each by their greatest common factor
		 *
		 * @access public
		 * @param array numbers to be reduced
		 * @return array numbers reduced by dividing each by their greatest common factor
		 */
		function reduce ($numbers) {
			$gcf = $this->greatest_common_factor ($numbers);

			foreach ($numbers as $count=>$number) 
				$numbers[$count] = $number / $gcf;

			return $numbers;
		}

		/** 
		 * Returns number of digits after the decimal point
		 *
		 * @access public
		 * @param integer number to analyze
		 * @return integer number of digits after the decimal point
		 */
		function getDecimalPlaces ($number) {
			return (strlen ($number) - (strpos ($number, '.') + 1));
		}

		/** 
		 * Convert a given decimal number to a ratio
		 *
		 * @access public
		 * @param integer number to convert to ratio
		 * @return string Ratio in the form of x:y
		 */
		function decimalToRatio ($number) {
			$decimal_places = $this->getDecimalPlaces ($number);
			$results = $this->reduce (array (substr ($number, (0 - $decimal_places)), pow (10, $decimal_places)));
			return $results[0] . ':' . $results[1];
		}

		/**
		 * Convert a given ratio to a number
		 *
		 * @access public
		 * @param string Ratio in the form of x:y
		 * @param integer Precision to round the result to
		 * @return integer Decimal number representing the ratio
		 */
		function ratioToDecimal ($ratio, $precision) {
			$ratio_pieces = explode (':', $ratio);
			return round ($ratio_pieces[0] / $ratio_pieces[1], $precision);
		}

		/**
		 * Determine if given number is prime
		 *
		 * @access public
		 * @param integer Number to test
		 * @return boolean Whether given number is prime
		 */
		function isPrime ($number) {
			$evens = array (2, 4, 6, 8, 0);
			if (in_array (substr ($number, -1), $evens)) {
				return false;
			}

			$limit = round (sqrt (abs ($number)));
			$count = 2;

			while ($count <= $limit) {
				if (fmod ($number, $count) == 0){
					debug ("Math::isPrime -- $count * " . ($number / $count) . " = $number", 4);
					return false;
				}

				$count++;
			}

			return true;
		}
	}
}
?>
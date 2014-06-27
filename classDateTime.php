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
 * @version $Revision: 1.4 $ $Date: 2004/02/20 18:19:15 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSDATETIME')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSDATETIME', 1);

	/**
	 * The library for working with dates and times
	 *
	 * This class has functions for the conversion, display and manipulation
	 * of dates well outside the usual unix epoch timestamp limitations.
	 *
	 * @package phpBaseClasses
	 */
	class pbcDateTime {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Year to begin forming timestamps from, this is also the earliest year the library can work with
		 *
		 * @var integer $_beginYear
		 * @access private
		 */
		var $_beginYear;

		/**
		 * Numeric day of the week for January 1st of _beginYear
		 *
		 * @var integer $_beginDayofWeek
		 * @access private
		 */
		var $_beginDayofWeek;

		/**
		 * List of known format names, value of an array containing a format and example element 
		 *
		 * @var array $_knownFormats (format_name, format_sample)
		 * @access private
		 */
		var $_knownFormats;

		/**
		 * Number of seconds per day
		 *
		 * @var integer $_secondsPerDay
		 * @access private
		 */
		var $_secondsPerDay;

		/**
		 * List of months and number of days in each.  Febrary has 28 in this array and is 
		 *
		 * @var array $_daysPerMonth (month_number, days_per_month)
		 * @access private
		 */
		var $_daysPerMonth;

		/**
		 * List of month numbers and associated names
		 *
		 * @var array $_monthNames (month_number, month_name)
		 * @access private
		 */
		var $_monthNames;

		/**
		 * List of days of week and associated names
		 *
		 * @var array $_dayOfWeekNames (day_number, day_name)
		 * @access private
		 */
		var $_dayOfWeekNames;

		/**
		 * The last format passed into the library, unless lockFormat is set, responses will be in this format
		 *
		 * @var string $_lastFormat
		 * @access private
		 */
		var $_lastFormat;

		/**
		 * The format all responses will be in, this being emptied disables it and lastFormat will be used
		 *
		 * @var string $_lockFormat
		 * @access private
		 */
		var $_lockFormat;

		/**
		 * Whether or not to adjust for timezone.  Useful to turn off during computations like adding days, months, years where it doesn't matter.
		 *
		 * @var boolean $_timezoneAdjust
		 * @access private
		 */
		var $_timezoneAdjust;

		/**
		 * Constructor sets all private class variables and default lastFormat
		 *
		 * @access public
		 * @return void
		 */
		function DateTime ($beginYear, $beginDayofWeek) {
			$this->_version = 0.1;

			$this->_beginYear = $beginYear;
			$this->_beginDayofWeek = $beginDayofWeek;
			$this->_knownFormats = array (
							'ISO8601 with Time'=>array (
								'format'=>'Y-m-d H:i:s',
								'example'=>'2000-01-17 13:00:00' 
							), 
							'ISO8601'=>array (
								'format'=>'Y-m-d', 
								'example'=>'2000-01-17' 
							), 
							'US with Time'=>array (
								'format'=>'m/d/Y H:i:s', 
								'example'=>'01/17/2000 13:00:00' 
							), 
							'US'=>array (
								'format'=>'m/d/Y', 
								'example'=>'01/17/2000'
							), 
							'German with Time'=>array (
								'format'=>'d.m.Y H:i:s', 
								'example'=>'17.01.2000 13:00:00' 
							), 
							'German'=>array (
								'format'=>'d.m.Y', 
								'example'=>'17.01.2000'
							), 
							'Mysql with Time'=>array (
								'format'=>'YmdHis', 
								'example'=>'20000117130000' 
							), 
							'Mysql'=>array (
								'format'=>'Ymd', 
								'example'=>'20000117' 
							), 
							'Timestamp'=>array (
								'format'=>'U', 
								'example'=>'63113982249' 
							)
						);
			$this->_secondsPerDay = 86400;
			$this->_daysPerMonth = array (0=>0, 1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
			$this->_monthNames = array (1=>'January', 2=>'February', 3=>'March', 4=>'April', 5=>'May', 6=>'June', 7=>'July', 8=>'August', 9=>'September', 10=>'October', 11=>'November', 12=>'December');
			$this->_dayOfWeekNames = array (0=>'Sunday', 1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday');
			$this->_lastFormat = 'ISO8601 with Time';
			$this->_timezoneAdjust = true;
		}

		/**
		 * Determines the format of given date and optionally sets the return type of further output of this class unless lockFormat is set
		 * Does not detect mysql date format since it has no delimiting characters
		 *
		 * @access private
		 * @param string Date to determine format of
		 * @param boolean Whether to set the lastFormat variable which would change the return type unless lockFormat is set
		 * @return string Date format
		 */
		function getDateFormat ($date, $setLastFormat=false) {
			debug ('DateTime::getDateFormat (' . $date . ')', 4);

			$hypen_count = substr_count ($date, '-');
			$colon_count = substr_count ($date, ':');
			$slash_count = substr_count ($date, '/');
			$point_count = substr_count ($date, '.');

			debug ('DateTime::getDateFormat - Hypens: ' . $hypen_count . ' Colons: ' . $colon_count . ' Slashes: ' . $slash_count . ' Points: ' . $point_count, 4);

			if (($hypen_count == 2) && ($colon_count == 2)) {
				$date_format = 'ISO8601 with Time';
			} else if (($hypen_count == 2) && ($colon_count == 0)) {
				$date_format = 'ISO8601';
			} else if (($slash_count == 2) && ($colon_count == 2)) {
				$date_format = 'US with Time';
			} else if (($slash_count == 2) && ($colon_count == 0)) {
				$date_format = 'US';
			} else if (($point_count == 2) && ($colon_count == 2)) {
				$date_format = 'German with Time';
			} else if (($point_count == 2) && ($colon_count == 0)) {
				$date_format = 'German';
			} else {
				$date_format = 'Timestamp';
			}

			if ($setLastFormat) {
				$this->_lastFormat = $date_format;
			}

			debug ('DateTime::getDateFormat - Returning: ' . $date_format, 4);

			return $date_format;
		}

		/**
		 * Returns a date correctly formatted
		 *
		 * @access private
		 * @param integer Epoch timestamp since beginYear
		 * @param string Format to return the date in, if this is not specified the lastFormat is used, unless lockFormat is specified
		 * @return string Formattted date
		 */
		function getFormattedDate ($timestamp=0, $format='', $removeTime=false) {
			debug ('DateTime::getFormattedDate (' . $timestamp . ', ' . $format . ')', 4);

			if (empty ($timestamp)) {
				$timestamp = $this->mktime ();
			}

			if (empty ($format)) {
				$format = $this->_lastFormat;
			}

			if (!empty ($this->_lockFormat)) {
				$format = $this->_lockFormat;
			}

			if ($this->getDateFormat ($timestamp, false) == $format) {
				return $timestamp;
			} else if (($format == 'ISO8601') || (($format == 'ISO8601 with Time') && ($removeTime))) {
				return $this->date ($this->_knownFormats['ISO8601']['format'], $timestamp);
			} else if ($format == 'ISO8601 with Time') {
				return $this->date ($this->_knownFormats[$format]['format'], $timestamp);
			} else if (($format == 'US') || (($format == 'US with Time') && ($removeTime))) {
				return $this->date ($this->_knownFormats['US']['format'], $timestamp);
			} else if ($format == 'US with Time') {
				return $this->date ($this->_knownFormats[$format]['format'], $timestamp);
			} else if (($format == 'German') || (($format == 'German with Time') && ($removeTime))) {
				return $this->date ($this->_knownFormats['German']['format'], $timestamp);
			} else if ($format == 'German with Time') {
				return $this->date ($this->_knownFormats[$format]['format'], $timestamp);
			} else if (($format == 'Mysql') || (($format == 'Mysql with Time') && ($removeTime))) {
				return $this->date ($this->_knownFormats['Mysql']['format'], $timestamp);
			} else if ($format == 'Mysql with Time') {
				return $this->date ($this->_knownFormats[$format]['format'], $timestamp);
			} else if ($format == 'Timestamp') {
				return $timestamp;
			}
		}

		/**
		 * Simple version of getdate that only gathers what it can from reading the date passed as text.
		 * 
		 * @access public
		 * @param string Date to be processed
		 * @param boolean Whether to set the lastFormat variable which would change the return type unless lockFormat is set
		 * @return array Date and time information (seconds, minutes, hours, mday, mon, year)
		 */
		function getdatesimple ($date, $setLastFormat=false) {
			debug ('DateTime::getdatesimple (' . $date . ')', 4);

			$format = $this->getDateFormat ($date, $setLastFormat);

			if ($format == 'Timestamp') {
				return $this->getdate ($date);
			} else if ($format == 'ISO8601 with Time') {
				list ($datepart, $timepart) = explode (' ', $date);
				list ($year, $month, $day) = explode ('-', $datepart);
				list ($hour, $minute, $second) = explode (':', $timepart);
			} else if ($format == 'ISO8601') {
				list ($year, $month, $day) = explode ('-', $date);
				$hour = $minute = $second = 0;
			} else if ($format == 'US with Time') {
				list ($datepart, $timepart) = explode (' ', $date);
				list ($month, $day, $year) = explode ('/', $datepart);
				list ($hour, $minute, $second) = explode (':', $timepart);
			} else if ($format == 'US') {
				list ($month, $day, $year) = explode ('/', $date);
				$hour = $minute = $second = 0;
			} else if ($format == 'German with Time') {
				list ($datepart, $timepart) = explode (' ', $date);
				list ($month, $day, $year) = explode ('.', $datepart);
				list ($hour, $minute, $second) = explode (':', $timepart);
			} else if ($format == 'German') {
				list ($month, $day, $year) = explode ('.', $date);
				$hour = $minute = $second = 0;
			} else if ($format == 'Mysql with Time') {
				$year = substr ($date, 0, 4);
				$month = substr ($date, 4, 2);
				$day = substr ($date, 6, 2);
				$hour = substr ($date, 8, 2);
				$minute = substr ($date, 10, 2);
				$second = substr ($date, 12, 2);
			} else if ($format == 'Mysql') {
				$year = substr ($date, 0, 4);
				$month = substr ($date, 4, 2);
				$day = substr ($date, 6, 2);
				$hour = $minute = $second = 0;
			}

			return array ('seconds'=>str_pad ($second, 2, '0', STR_PAD_LEFT), 'minutes'=>str_pad ($minute, 2, '0', STR_PAD_LEFT), 
				'hours'=>str_pad ($hour, 2, '0', STR_PAD_LEFT), 'mday'=>str_pad ($day, 2, '0', STR_PAD_LEFT), 
				'mon'=>str_pad ($month, 2, '0', STR_PAD_LEFT), 'year'=>$year);
		}

		/**
		 * Converts from one format to another using text processing
		 * 
		 * @access public
		 * @param string Date to be converted
		 * @param string Format to return the date in, if this is not specified the lastFormat is used, unless lockFormat is specified
		 * @retrun string Date in new format
		 */
		function convertDateFormat ($date, $format) {
			debug ('DateTime::convertDateFormat (' . $date . ', ' . $format . ')', 4);

			$getdatesimple = $this->getdatesimple ($date, false);

			if (empty ($format)) {
				$format = $this->_lastFormat;
			}

			if (!empty ($this->_lockFormat)) {
				$format = $this->_lockFormat;
			}

			return str_replace ('Y', $getdatesimple['year'], 
				str_replace ('m', $getdatesimple['mon'], 
				str_replace ('d', $getdatesimple['mday'], 
				str_replace ('H', $getdatesimple['hours'], 
				str_replace ('i', $getdatesimple['minutes'], 
				str_replace ('s', $getdatesimple['seconds'], 
					$this->_knownFormats[$format]['format']))))));
		}

		/**
		 * Returns a timestamp from the given date
		 *
		 * @access private
		 * @param string Date to be converted
		 * @param boolean Whether to set the lastFormat variable which would change the return type unless lockFormat is set
		 * @return integer Epoch timestamp since beginYear
		 */
		function convertToTimestamp ($date, $setLastFormat=true) {
			debug ('DateTime::convertToTimestamp (' . $date . ')', 4);

			$format = $this->getDateFormat ($date, $setLastFormat);

			if ($format == 'Timestamp') {
				return $date;
			} else {
				$getdatesimple = $this->getdatesimple ($date, $setLastFormat);

				return $this->mktime ($getdatesimple['hours'], $getdatesimple['minutes'], $getdatesimple['seconds'], $getdatesimple['mon'], $getdatesimple['mday'], $getdatesimple['year']);
			}
		}

		/**
		 * Returns number of days in month of given month and year
		 *
		 * @access private
		 * @param integer 
		 * @param integer 
		 * @return integer number of days in month of given month and year
		 */
		function getDaysPerMonth ($year, $month) {
			if (($this->isLeapYear ($year)) && ($month == 2)) {
				return (($this->_daysPerMonth[$month] + 1));
			} else {
				return ($this->_daysPerMonth[$month]);
			}
		}

		/**
		 * Returns number of seconds in month of given month and year
		 *
		 * @access private
		 * @param integer 
		 * @param integer 
		 * @return integer number of seconds in month of given month and year
		 */
		function getSecondsPerMonth ($year, $month) {
			if ($month == 13) {
				$month = 1;
			}

			if (($this->isLeapYear ($year)) && ($month == 2)) {
				return (($this->_daysPerMonth[$month] + 1) * $this->_secondsPerDay);
			} else {
				return ($this->_daysPerMonth[$month] * $this->_secondsPerDay);
			}
		}

		/**
		 * Determines if given year is a leap year
		 *
		 * @access private
		 * @param integer 
		 * @return boolean Whether given year is a leap year
		 */
		function isLeapYear ($year) {
			$leap = false;

			if (($year % 4 == 0)) {
				if (($year % 100 !== 0) || ($year % 400 == 0)) {
					$leap = true;
				}
			}

			return $leap;
		}

		/**
		 * Returns number of days per year
		 *
		 * @access private
		 * @param integer 
		 * @return integer Number of days per year
		 */
		function getDaysPerYear ($year) {
			if ($this->isLeapYear ($year)) {
				return (366);
			} else {
				return (365);
			}
		}

		/**
		 * Returns number of seconds per year
		 *
		 * @access private
		 * @param integer 
		 * @return integer Number of seconds per year
		 */
		function getSecondsPerYear ($year) {
			if ($this->isLeapYear ($year)) {
				return (366 * $this->_secondsPerDay);
			} else {
				return (365 * $this->_secondsPerDay);
			}
		}

		/**
		 * Returns date and time data for a given timestamp
		 *
		 * @access private
		 * @param integer Timestamp since January 1st of beginYear
		 * @return array Date and time data (year, month, day, hour, minute, second, dayofweek, dayofyear)
		 */
		function getTimeDataFromTimestamp ($timestamp) {
			$year = $this->_beginYear;

			$totaldays = 0;

			while ($timestamp > $this->getSecondsPerYear ($year)) {
				$timestamp -= $this->getSecondsPerYear ($year);
				$totaldays += $this->getDaysPerYear ($year);
				$year++;
			}

			$month = 1;
			$dayofyear = 0;

			while (($timestamp >= $this->getSecondsPerMonth ($year, $month)) && ($month < 13)) {
				$timestamp -= $this->getSecondsPerMonth ($year, $month);
				$dayofyear += $this->getDaysPerMonth ($year, $month);
				$month++;
			}

			if ($month == 13) {
				$year++;
				$month -= 12;
			}

			$day = floor ($timestamp / $this->_secondsPerDay) + 1;
			$dayofyear += $day - 1;
			$totaldays += $dayofyear;
			$hour = floor (($timestamp % $this->_secondsPerDay) / (3600));
			$minute = floor ((($timestamp % $this->_secondsPerDay) % 3600) / 60);
			$second = floor ((($timestamp % $this->_secondsPerDay) % 3600) % 60);

			$dayofweek = (($this->_beginDayofWeek + $totaldays) % 7);

			return array ($year, $month, $day, $hour, $minute, $second, $dayofweek, $dayofyear);
		}

		/**
		 * Checks if given format is valid and sets the lastFormat to it so future returns from this class will be in that format
		 *
		 * @access public
		 * @param string date format 
		 * @return boolean Whether successful, fails when date format is not found
		 */
		function setDateFormat ($format) {
			if (isset ($this->_knownFormats[$format])) {
				$this->_lastFormat = $format;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Checks if given format is valid and sets the lockFormat to it so all future returns from this class will be in that format
		 * Leaving format empty will unlock
		 *
		 * @access public
		 * @param string date format 
		 * @return boolean Whether successful, fails when date format is not found
		 */
		function lockDateFormat ($format='') {
			if (empty ($format)) {
				$this->_lockFormat = '';
				return true;
			} else {
				if (isset ($this->_knownFormats[$format])) {
					$this->_lockFormat = $format;
					return true;
				} else {
					return false;
				}
			}
		}

		/**
		 * Returns the timestamp since January 1st of beginYear in UTC
		 * Behaves exactly like PHP's implementation of unix mktime
		 * http://www.php.net/mktime
		 *
		 * @access public
		 * @param integer Empty will cause lookup of current hour
		 * @param integer Empty will cause lookup of current minute
		 * @param integer Empty will cause lookup of current second
		 * @param integer Empty will cause lookup of current month
		 * @param integer Empty will cause lookup of current day
		 * @param integer Empty will cause lookup of current year
		 * @return integer Timestamp since January 1st of beginYear in UTC
		 */
		function mktime ($hour='', $minute='', $second='', $month='', $day='', $year='') {
			debug ('DateTime::mktime (' . $hour . ', ' . $minute . ', ' . $second . ', ' . $month . ', ' . $day . ', ' . $year . ')', 4);

			$timestamp = 0;

			if ($year === '') {
				$year = date ('Y');
			}

			if ($year > $this->_beginYear) {
				foreach (range ($this->_beginYear, ($year - 1)) as $curyear) {
					$timestamp += $this->getSecondsPerYear ($curyear);
				}
			}

			if ($month === '') {
				$month = date ('m');
			}

			foreach (range (0, ($month - 1)) as $curmonth) {
				$timestamp += $this->getSecondsPerMonth ($year, $curmonth);
			}

			if ($day === '') {
				$day = date ('d');
			}

			$timestamp += (($day - 1) * $this->_secondsPerDay);

			if ($hour === '') {
				$hour = date ('H');
			}

			$timestamp += ($hour * 3600);

			if ($minute === '') {
				$minute = date ('i');
			}

			$timestamp += ($minute * 60);

			if ($second === '') {
				$second = date ('s');
			}

			$timestamp += $second;

			if ($this->_timezoneAdjust) {
				$timezone = date ('Z', mktime ($hour, $minute, $second, $month, $day, 2000));
				$timestamp -= $timezone;
			}

			return $timestamp;
		}

		/**
		 * Returns a formatted date
		 * Behaves exactly like PHP's implementation of unix date
		 * http://www.php.net/date
		 * 2 new parameters added
		 * X: # of weekday for this month
		 * x: Total # of weekdays for this month
		 *
		 * @access public
		 * @param string Format to return date in
		 * @param integer Timestamp since January 1st of beginYear in UTC
		 * @return string Formatted date
		 */
		function date ($format, $timestamp=0) {
			debug ('DateTime::date (' . $format . ', ' . $timestamp . ')', 4);

			if (empty ($timestamp)) {
				$timestamp = $this->mktime ();
			}

			$timestamp = $this->convertToTimestamp ($timestamp, false);

			list ($year, $month, $day, $hour, $minute, $second, $dayofweek, $dayofyear) = $this->getTimeDataFromTimestamp ($timestamp);

			if ($this->_timezoneAdjust) {
				$timezone_info = explode (' ', date ('O T Z I', mktime ($hour, $minute, $second, $month, $day, 2000)));
				$timestamp += $timezone_info[2];

				list ($year, $month, $day, $hour, $minute, $second, $dayofweek, $dayofyear) = $this->getTimeDataFromTimestamp ($timestamp);
			}

			$retval = '';

			$format = str_replace ('r', 'D, d M Y H:i:s O', $format);

			for ($index = 0; $index < strlen ($format); $index++) {
				switch (substr ($format, $index, 1)) {
					case 'a':
						if ($hour > 12) {
							$retval .= 'pm';
						} else {
							$retval .= 'am';
						}
						break;
					case 'A':
						if ($hour > 12) {
							$retval .= 'PM';
						} else {
							$retval .= 'AM';
						}
						break;
					case 'D':
						$retval .= substr ($this->_dayOfWeekNames[$dayofweek], 0, 3);
						break;
					case 'd':
						$retval .= str_pad ($day, 2, '0', STR_PAD_LEFT);
						break;
					case 'F':
						$retval .= $this->_monthNames[$month];
						break;
					case 'g':
						if ($hour > 12) {
							$retval .= $hour - 12;
						} else {
							$retval .= $hour;
						}
						break;
					case 'G':
						$retval .= $hour;
						break;
					case 'h':
						if ($hour > 12) {
							$retval .= str_pad ($hour - 12, 2, '0', STR_PAD_LEFT);
						} else {
							$retval .= str_pad ($hour, 2, '0', STR_PAD_LEFT);
						}
						break;
					case 'H':
						$retval .= str_pad ($hour, 2, '0', STR_PAD_LEFT);
						break;
					case 'I':
						$retval .= $timezone_info[3];
						break;
					case 'i':
						$retval .= str_pad ($minute, 2, '0', STR_PAD_LEFT);
						break;
					case 'j':
						$retval .= $day;
						break;
					case 'L':
						$retval .= (int) $this->isLeapYear ($year);
						break;
					case 'l':
						$retval .= $this->_dayOfWeekNames[$dayofweek];
						break;
					case 'M':
						$retval .= substr ($this->_monthNames[$month], 0, 3);
						break;
					case 'm':
						$retval .= str_pad ($month, 2, '0', STR_PAD_LEFT);
						break;
					case 'n':
						$retval .= $month;
						break;
					case 'O':
						$retval .= $timezone_info[0];
						break;
					case 'S':
						if ($day == 1) {
							$retval .= 'st';
						} else if ($day == 2) {
							$retval .= 'nd';
						} else if ($day == 3) {
							$retval .= 'rd';
						} else {
							$retval .= 'th';
						}
						break;
					case 's':
						$retval .= str_pad ($second, 2, '0', STR_PAD_LEFT);
						break;
					case 'T':
						$retval .= $timezone_info[1];
						break;
					case 't':
						$retval .= $this->getDaysPerMonth ($year, $month);
						break;
					case 'U':
						$retval .= $timestamp;
						break;
					case 'W':
						$retval .= floor ($dayofyear / 7);
						break;
					case 'w':
						$retval .= $dayofweek;
						break;
					case 'X': // # of weekday for this month
						$retval .= floor (($day - 1) / 7) + 1;
						break;
					case 'x': // Total # of weekdays for this month
						$lastdayofweek = ((7 + $dayofweek + (($this->getDaysPerMonth ($year, $month) - $day) % 7)) % 7);
						if ((($dayofweek + 7) >= ($lastdayofweek - (($this->getDaysPerMonth ($year, $month) % 7) - 1)) + 7) && (($dayofweek + 7) <= ($lastdayofweek + 7))) {
							$retval .= 5;
						} else {
							$retval .= 4;
						}
						break;
					case 'Y':
						$retval .= $year;
						break;
					case 'y':
						$retval .= substr ($year, 2);
						break;
					case 'Z':
						$retval .= $timezone_info[2];
						break;
					case 'z':
						$retval .= $dayofyear;
						break;
					default:
						$retval .= substr ($format, $index, 1);
				}
			}

			return $retval;
		}

		/**
		 * Returns an associative array of date information
		 * Behaves exactly like PHP's implementation of unix getdate
		 * http://www.php.net/getdate
		 *
		 * @access public
		 * @param integer Timestamp since January 1st of beginYear in UTC
		 * @return array Date and time information (seconds, minutes, hours, mday, wday, mon, year, yday, weekday, month)
		 */
		function getdate ($timestamp=0) {
			debug ('DateTime::getdate (' . $timestamp . ')', 4);

			if (empty ($timestamp)) {
				$timestamp = $this->mktime ();
			}

			$d = explode (' ', $this->date ('s i H d w n Y z l F', $timestamp));

			return array ('seconds'=>str_pad ($d[0], 2, '0', STR_PAD_LEFT), 'minutes'=>str_pad ($d[1], 2, '0', STR_PAD_LEFT), 
				'hours'=>str_pad ($d[2], 2, '0', STR_PAD_LEFT), 'mday'=>str_pad ($d[3], 2, '0', STR_PAD_LEFT), 
				'wday'=>$d[4], 'mon'=>str_pad ($d[5], 2, '0', STR_PAD_LEFT), 'year'=>$d[6], 'yday'=>$d[7], 'weekday'=>$d[8], 'month'=>$d[9], '0'=>$timestamp);
		}

		/**
		 * Returns the number of days between 2 dates regardless of their formats
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @param boolean Whether to only count business days
		 * @param boolean Whether to always return positive number or not
		 * @return integer Number of days between first date and second date
		 */
		function daysBetween ($date1='', $date2='', $business_days=false, $absolute_value=true) {
			if (empty ($date1)) {
				$date1 = $this->mktime ();
			}

			if (empty ($date2)) {
				$date2 = $this->mktime ();
			}

			if ($business_days) {
				$date1 = $this->getFormattedDate ($date1, $format='ISO8601');
				$date2 = $this->getFormattedDate ($date2, $format='ISO8601');

				$added_days = 0;
				if ($date1 == $date2) {
					return 0;
				} else if ($date1 > $date2) {
					do {
						$added_days++;
						$date2 = $this->addDays ($date2, 1, true);
					} while ($date1 > $date2);

					return $added_days;
				} else {
					do {
						$added_days++;
						$date1 = $this->addDays ($date1, 1, true);
					} while ($date2 > $date1);

					if ($absolute_value) {
						return $added_days;
					} else {
						return (0 - $added_days);
					}
				}
			} else {
				$date1 = $this->convertToTimestamp ($date1);
				$date2 = $this->convertToTimestamp ($date2);

				if ($absolute_value) {
					$difference = abs ($date1 - $date2);
				} else {
					$difference = $date1 - $date2;
				}

				return ceil ($difference / $this->_secondsPerDay);
			}
		}

		/**
		 * Returns the number of seconds between 2 dates regardless of their formats
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @param boolean Whether to always return positive number or not
		 * @return integer Number of seconds between first date and second date
		 */
		function secondsBetween ($date1='', $date2='', $absolute_value=true) {
			if (empty ($date1)) {
				$date1 = $this->mktime ();
			}

			if (empty ($date2)) {
				$date2 = $this->mktime ();
			}

			$date1 = $this->convertToTimestamp ($date1);
			$date2 = $this->convertToTimestamp ($date2);

			if ($absolute_value) {
				$difference = abs ($date1 - $date2);
			} else {
				$difference = $date1 - $date2;
			}

			return $difference;
		}

		/**
		 * Returns the number of minutes between 2 dates regardless of their formats
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @param boolean Whether to always return positive number or not
		 * @return integer Number of minutes between first date and second date
		 */
		function minutesBetween ($date1='', $date2='', $absolute_value=true) {
			return ($this->secondsBetween ($date1, $date2, $absolute_value) / 60);
		}

		/**
		 * Returns the number of hours between 2 dates regardless of their formats
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @param boolean Whether to always return positive number or not
		 * @return integer Number of hours between first date and second date
		 */
		function hoursBetween ($date1='', $date2='', $absolute_value=true) {
			return ($this->secondsBetween ($date1, $date2, $absolute_value) / 60 / 60);
		}

		/**
		 * Compares 2 dates regardless of their formats and returns true if second date is greater than the first date, otherwise false
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @return boolean Whether second date is greater than first date
		 */
		function isGreater ($date1='', $date2='') {
			if (empty ($date1)) {
				$date1 = $this->mktime ();
			}

			if (empty ($date2)) {
				$date2 = $this->mktime ();
			}

			$date1 = $this->convertToTimestamp ($date1);
			$date2 = $this->convertToTimestamp ($date2);

			if ($date1 < $date2) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Compares 2 dates regardless of their formats and returns true if second date is greater than or equal to the first date, otherwise false
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @return boolean Whether second date is greater than first date
		 */
		function isGreaterOrEqual ($date1='', $date2='') {
			if (empty ($date1)) {
				$date1 = $this->mktime ();
			}

			if (empty ($date2)) {
				$date2 = $this->mktime ();
			}

			$date1 = $this->convertToTimestamp ($date1);
			$date2 = $this->convertToTimestamp ($date2);

			if ($date1 <= $date2) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Compares 2 dates regardless of their format and returns 0 if 2 dates are equal, 1 if second date is greater than first date, and otherwise -1
		 *
		 * @access public
		 * @param string First Date
		 * @param string Second Date
		 * @return integer 0 if 2 dates are equal, 1 if second date is greater than first date, and otherwise -1
		 */
		function dateCompare ($date1='', $date2='') {
			if (empty ($date1)) {
				$date1 = $this->mktime ();
			}

			if (empty ($date2)) {
				$date2 = $this->mktime ();
			}

			$date1 = $this->convertToTimestamp ($date1);
			$date2 = $this->convertToTimestamp ($date2);

			if ($date1 == $date2) {
				return 0;
			} else if ($date1 < $date2) {
				return 1;
			} else {
				return -1;
			}
		}

		/** 
		 * Test if given day is a business day or not
		 * @access public
		 * @param string
		 * @return boolean True if a business day
		 */
		function isBusinessDay ($date='') {
			global $config;

			list ($mon, $mday, $weekday, $weekdayname, $weekOfYear, $weekOfMonth, $weeksOfMonth) = explode (' ', $this->date ('n d l D W X x', $date));
			$mon = str_pad ($mon, 2, '0', STR_PAD_LEFT);
			$mday = str_pad ($mday, 2, '0', STR_PAD_LEFT);

			if (($weekday == 'Saturday') || ($weekday == 'Sunday')) {
				return false;
			}

			foreach ($config['HOLIDAYS']['FIXED'] as $fixed_date => $fixed_name) {
				if ($fixed_date == $mon . '-' . $mday) {
					return false;
				}
			}

			foreach ($config['HOLIDAYS']['VARIABLE'] as $variable_date => $variable_name) {
				list ($variable_date_month, $variable_date_index, $variable_date_weekday) = explode ('-', $variable_date);
				if (($mon == $variable_date_month) && ($weekdayname == $variable_date_weekday)) {
					if ($variable_date_index == $weekOfMonth) {
						return false;
					} else if (($variable_date_index == 'L') && ($weeksOfMonth == $weekOfMonth)) {
						return false;
					}
				}
			}

			return true;
		}

		/**
		 * Add a given number of seconds to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of seconds to add
		 * @return string Adjusted date
		 */
		function addSeconds ($date='', $seconds=1) {
			if (empty ($date)) {
				$date = $this->mktime ();
			}

			$date = $this->convertToTimestamp ($date);

			$d = $this->getdate ($date);

			return $this->getFormattedDate ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'] + $seconds, $d['mon'], ($d['mday']), $d['year']));
		}

		/**
		 * Add a given number of minutes to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of minutes to add
		 * @return string Adjusted date
		 */
		function addMinutes ($date='', $minutes=1) {
			return $this->addSeconds ($date, $minutes * 60);
		}

		/**
		 * Add a given number of hours to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of hours to add
		 * @return string Adjusted date
		 */
		function addHours ($date='', $hours=1) {
			return $this->addSeconds ($date, $hours * 60 * 60);
		}

		/**
		 * Add a given number of days to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of days to add
		 * @param boolean Whether to only count business days
		 * @return string Adjusted date
		 */
		function addDays ($date='', $days=1, $business_days=false) {
			debug ('DateTime::addDays (' . $date . ', ' . $days . ')', 4);
			$this->_timezoneAdjust = false;

			if (empty ($date)) {
				$date = $this->mktime ();
			}

			$date = $this->convertToTimestamp ($date);

			$d = $this->getdate ($date);

			if (($business_days) && ($days != 0)) {
				if ($days > 0) {
					for ($index = 1; $index <= $days; $index++) {
						if (!$this->isBusinessDay ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], ($d['mday'] + $index), $d['year']))) {
							$days++;
						}
					}
				} else {
					for ($index = -1; $index >= $days; $index--) {
						if (!$this->isBusinessDay ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], ($d['mday'] + $index), $d['year']))) {
							$days--;
						}
					}
				}
			}

			$retval = $this->getFormattedDate ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], ($d['mday'] + $days), $d['year']));

			$this->_timezoneAdjust = true;

			return $retval;
		}

		/**
		 * Add a given number of weeks to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of weeks to add
		 * @return string Adjusted date
		 */
		function addWeeks ($date='', $weeks=1, $inclusive=false) {
			$this->_timezoneAdjust = false;

			if (empty ($date)) {
				$date = $this->mktime ();
			}

			$date = $this->convertToTimestamp ($date);

			$d = $this->getdate ($date);

			if ($inclusive) {
				$retval = $this->getFormattedDate ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], $d['mday'] + (7 * $weeks), $d['year'], false));
			} else {
				$retval = $this->getFormattedDate ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], $d['mday'] + (7 * $weeks - 1), $d['year'], false));
			}

			$this->_timezoneAdjust = true;

			return $retval;
		}

		/**
		 * Add a given number of months to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of months to add
		 * @return string Adjusted date
		 */
		function addMonths ($date='', $months=1) {
			debug ('DateTime::addMonths (' . $date . ', ' . $months . ')', 4);

			$this->_timezoneAdjust = false;

			if (empty ($date)) {
				$date = $this->mktime ();
			}

			$date = $this->convertToTimestamp ($date);

			$d = $this->getdate ($date);

			$retval = $this->getFormattedDate ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], ($d['mon'] + $months), $d['mday'], $d['year'], false));

			$this->_timezoneAdjust = true;

			return $retval;
		}

		/**
		 * Add a given number of years to given date and returns result in same or locked date format
		 *
		 * @access public
		 * @param string
		 * @param integer Number of years to add
		 * @return string Adjusted date
		 */
		function addYears ($date='', $years=1) {
			$this->_timezoneAdjust = false;

			if (empty ($date)) {
				$date = $this->mktime ();
			}

			$date = $this->convertToTimestamp ($date);

			$d = $this->getdate ($date);

			$retval = $this->getFormattedDate ($this->mktime ($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], $d['mday'], ($d['year'] + $years), false));

			$this->_timezoneAdjust = true;

			return $retval;
		}

		/**
		 * Returns date and time of now in the last used or locked date format
		 *
		 * @access public
		 * @param string Format to return date in
		 * @return string Date and time of now in the last used or locked date format
		 */
		function now ($format='') {
			return $this->getFormattedDate (0, $format);
		}

		/**
		 * Return the index of the day of week, for example Monday will return 1
		 *
		 * @access public
		 * @return integer Index of the day of week
		 */
		function getDayOfWeekIndex ($name) {
			foreach ($this->_dayOfWeekNames as $day_index=>$day_name) {
				if ($day_name == $name) {
					return $day_index;
				}
			}

			return false;
		}

		/**
		 * Return the number of the month given, for example Mar or March will return 3
		 * False if no match is found
		 *
		 * @access public
		 * @return integer Number of the month
		 */
		function getMonthByName ($name) {
			foreach ($this->_monthNames as $index => $month) {
				if (($name == $month) || ($name == substr ($month, 0, 3))) {
					return $index;
				}
			}

			return false;
		}
	}
}
?>
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
 * @version $Revision: 1.5 $ $Date: 2004/06/03 08:05:58 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */
 
if (defined('CLASSURLFETCH')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSURLFETCH', 1);

	/**
	 * The library for fetching and handling url data, requires curl library
	 *
	 * @package phpBaseClasses
	 */
	class URLFetch {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * URL this class is currently working on
		 *
		 * @var string $_url
		 * @access private
		 */
		var $_url;

		/**
		 * Referrer to give when fetching a URL
		 *
		 * @var string $_referrer
		 * @access private
		 */
		var $_referrer;

		/**
		 * Cookies that have been set while navigating
		 *
		 * @var array $_cookies (cookie_key, cookie_value)
		 * @access private
		 */
		var $_cookies;

		/**
		 * The contents of the fetch
		 *
		 * @var string $_page
		 * @access public
		 */
		var $_page;

		/**
		 * Browser info to give when fetching a URL
		 *
		 * @var string $_browser
		 * @access public
		 */
		var $_browser;

		/**
		 * Constructor
		 *
		 * @access public
		 * @param string calls setBrowser with this, if empty it defaults to lynx
		 * @return void
		 */
		function URLFetch ($browser_string='') {
			$this->_version = 0.1;

			if (empty ($browser_string)) {
				$browser_string = 'Lynx';
			}

			$this->setBrowser ($browser_string);
			$this->eraseCookies ();			
		}

		/**
		 * Sets browser string to give when fetching a URL
		 *
		 * @access public
		 * @param string sets browser, translates into full text for following: IE55, Lynx
		 * @return void
		 */
		function setBrowser ($browser_string) {
			if ($browser_string == 'IE55') {
				$this->_browser = 'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT 5.0; T312461)';
			} else if ($browser_string == 'Lynx') {
				$this->_browser = 'Lynx/2.8.4dev.16 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/0.9.6';
			} else {
				$this->_browser = $browser_string;
			}
		}

		/**
		 * Gets a list a cookies suitable for passing to a subsequent fetch
		 *
		 * @access private
		 * @return string list a cookies suitable for passing to a subsequent fetch
		 */
		function getCookies () {
			$cookie_data = '';

			foreach ($this->_cookies as $key=>$val) {
				$cookie_data = $key . '=' . $val . '; ' . $cookie_data;
			}

			return substr ($cookie_data, 0, -2);
		}

		/**
		 * Erases a cookie from the list
		 *
		 * @access public
		 * @param string name of cookie to delete
		 * @return void
		 */
		function eraseCookie ($cookie_name) {
			unset ($this->_cookies[$cookie_name]);
		}

		/**
		 * Erases all cookies from the list
		 *
		 * @access public
		 * @return void
		 */
		function eraseCookies () {
			$this->_cookies = array ();
		}

		/**
		 * Fetches some information about a form within a fetched page
		 *
		 * @access public
		 * @param string name of form to look for
		 * @return array (string method, string url, array data (input_name, input_value))
		 */
		function getFormData ($form_name='') {
			$form_data = array ();

			$keep_looking = 0;

			while (strpos ($this->_page, '<form ', $keep_looking) !== false) { 
				$form_begin = strpos ($this->_page, '<form ', $keep_looking);
				$form_end = strpos ($this->_page, '</form', $form_begin);
				$form_text = substr ($this->_page, $form_begin, ($form_end - $form_begin));

				$form_tag = substr ($this->_page, $form_begin, (strpos ($this->_page, '>', $form_begin) - $form_begin));
				$form_properties = $this->getHTMLProperties ($form_tag);

				if (($form_properties['name'] == $form_name) || (empty ($form_name))) {
					$form_data['method'] = $form_properties['method'];
					$form_data['url'] = $form_properties['action'];

					if (substr ($form_data['url'], 0, 7) != 'http://') {
						$form_data['url'] = $this->_url . $form_data['url'];
					}

					$begin = 0;
					while (strpos ($form_text, '<input', $begin) !== false) {
						$input_begin = strpos ($form_text, '<input', $begin);
						$input_end = strpos ($form_text, '>', $input_begin);

						$input_tag = substr ($form_text, $input_begin, ($input_end - $input_begin));
						$input_properties = $this->getHTMLProperties ($input_tag);

						$form_data['data'][$input_properties['name']] = $input_properties['value'];

						$begin = $input_end;
					}

					break;
				}

				$keep_looking = $form_end;
			}

			return $form_data;
		}

		/**
		 * Take in an html tag and returns its key->values in an array
		 *
		 * @access private
		 * @param string html tag
		 * @return array (key=>value)
		 */
		function getHTMLProperties ($tag) {
			$properties = array ();

			$tag_pieces = explode (' ', $tag);
			array_shift ($tag_pieces);

			for ($index = 0; $index < count($tag_pieces); $index++) {
				if (substr_count ($tag_pieces[$index], '"') % 2 == 1) {
					$tag_pieces[$index] .= ' ' . $tag_pieces[$index+1];
					$tag_pieces[$index+1] = '';
				}
			}

			for ($index = 0; $index < count($tag_pieces); $index++) {
				if (strpos($tag_pieces[$index], '=') !== false) {
					$name = substr ($tag_pieces[$index], 0, strpos ($tag_pieces[$index], '='));
					$value = str_replace ('&nbsp;', ' ', substr ($tag_pieces[$index], strpos($tag_pieces[$index], '=') + 1));

					if ((substr ($value, 0, 1) == '"') && (substr ($value, -1) == '"')) {
						$value = substr ($value, 1, -1);
					}

					$properties[$name] = $value;
				}
			}

			if (!isset ($properties['name'])) {
				$properties['name'] = '';
			}

			return $properties;
		}

		/**
		 * Submits a form based on form data from getFromData
		 *
		 * @access public
		 * @param array (string method, string url, array data (input_name, input_value)) from getFormData
		 * @param array (key=>value) items to override whats given from getFormData
		 * @return string data received from remote server
		 */
		function submitForm ($form_data, $override_data) {
			$post_data = '';

			$form_data['url'] .= '?';

			while (list ($name, $value) = each ($form_data['data'])) {
				if (isset ($override_data[$name])) {
					$value = $override_data[$name];
				}
				if (strtolower ($form_data['method']) == 'post') {
					$post_data .= $name . '=' . $value . '&';
				} else {
					$form_data['url'] .= $name . '=' . $value . '&';
				}
			}

			if ((substr ($form_data['url'], -1) == '&') || (substr ($form_data['url'], -1) == '?')) {
				$form_data['url'] = substr ($form_data['url'], 0, -1);
			}

			if (substr ($post_data, -1) == '&') {
				$post_data = substr ($post_data, 0, -1);
			}

			return ($this->getURL ($form_data['url'], $post_data));
		}

		/**
		 * gets a page from a remote server
		 *
		 * @access public
		 * @param string path to remote server
		 * @param string list of post vars in normal get format var1=val1&var2=val2
		 * @param boolean Whether you would like the HTTP Headers included
		 * @return string data received from remote server
		 */
		function getURL ($url, $postvars='', $headers=false) {
			$this->_url = $url;
			$relocate = '';

			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $this->_url);
			curl_setopt ($ch, CURLOPT_REFERER, $this->_referrer);
			curl_setopt ($ch, CURLOPT_HEADER, $headers);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_USERAGENT, $this->_browser);
			debug ('UrlFetch::getURL -- Cookie Data = ' . $this->getCookies (), 3);
			curl_setopt ($ch, CURLOPT_COOKIE, $this->getCookies ());

			if (!empty ($postvars)) {
				debug ('UrlFetch::getURL -- Post Variables = ' . $postvars, 3);
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $postvars);
			}

			debug ('UrlFetch::getURL -- Calling: ' . $url, 3);
			$this->_page = curl_exec ($ch);

			$cookie_count = 0;
			foreach (explode ("\n", $this->_page) as $line) {
				if (substr ($line, 0, 12) == 'Set-Cookie: ') {
					$cookie_data = substr ($line, 12, strpos ($line, ';') - 12);
					$cookie_name = substr ($cookie_data, 0, strpos ($cookie_data, '='));
					$cookie_value = substr ($cookie_data, strpos ($cookie_data, '=') + 1);
					$this->_cookies[$cookie_name] = $cookie_value;
					$cookie_count++;
					debug ('UrlFetch::getURL -- Found Cookie: ' . $cookie_name, 3);
				}
			}

			foreach (explode ("\n", $this->_page) as $line) {
				if (substr ($line, 0, 10) == 'Location: ') {
					$relocate = substr ($line, 10);
				}
			}

			if (curl_error ($ch) > '') {
				debug ('UrlFetch::getURL -- Curl Error - ' . curl_error ($ch), 1);
			}

			curl_close ($ch);

			$this->_referrer = $url;

			if (!empty ($relocate)) {
				debug ('UrlFetch::getURL -- Relocating to ' . $relocate, 3);
				$this->getURL ($relocate);
			}

			return ($this->_page);
		}
	}
}
?>
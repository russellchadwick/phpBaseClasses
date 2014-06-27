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
 * @version $Revision: 1.25 $ $Date: 2004/02/20 18:21:10 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSJABBER')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSJABBER', 1);

	/**
	 * The API for interacting with a jabber server
	 *
	 * This class defines the API for connecting with jabber servers
	 * and including authentication, sending, and receiving data
	 *
	 * @package phpBaseClasses
	 */
	class Jabber {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Handle of socket
		 * 
		 * @var integer $_sock
		 * @access private
		 */
		var $_sock;

		/**
		 * Hostname of server connecting to
		 * 
		 * @var string $_host
		 * @access private
		 */
		var $_host;

		/**
		 * Username to connect as
		 * 
		 * @var string $_username
		 * @access private
		 */
		var $_username;

		/**
		 * Password to connect as
		 *
		 * @var string $_password
		 * @access private
		 */
		var $_password;

		/** 
		 * Whether this connection is blocking
		 *
		 * @var boolean $_blocking
		 * @access private
		 */
		var $_blocking;

		/**
		 * Whether on not to connect to aim connection
		 *
		 * @var boolean $_aim
		 * @access private
		 */
		var $_aim;

		/**
		 * Stream ID of the connection, used for password digest
		 *
		 * @var integer $_stream_id
		 * @access private
		 */
		var $_stream_id;

		/**
		 * Constructor, creates connection to the jabber server and begins session, calls login ()
		 *
		 * @access public
		 * @param string Hostname of server connecting to
		 * @param string Username to connect as
		 * @param string Password to connect as
		 * @param boolean Whether to make connection blocking
		 * @param boolean Whether on not to connect to aim connection
		 * @return void
		 */
		function Jabber ($host, $username, $password, $blocking=false, $aim=false) {
			$this->_host = $host;
			$this->_username = $username;
			$this->_password = $password;
			$this->_blocking = $blocking;
			$this->_aim = $aim;

			$this->_sock = fsockopen ($this->_host, '5222');

			if ($this->_sock) {
				stream_set_blocking ($this->_sock, true);
				stream_set_timeout ($this->_sock, 31536000);  // 365 days

				$this->writeData ('<stream:stream to="' . $host . '" xmlns="jabber:client" xmlns:stream="http://etherx.jabber.org/streams" version="1.0" >');

				sleep (1);

				$stream_data = $this->readData ();
				$this->_stream_id = substr ($stream_data, (strpos ($stream_data, ' id=') + 5), ((strpos ($stream_data, ' xmlns=') - 1) - (strpos ($stream_data, ' id=') + 5)));

				$this->login ();

				stream_set_blocking ($this->_sock, $blocking);
			} else {
				debug ('No Jabber Server!', 1);
			}
		}

		/**
		 * Logs into jabber server using jabbers SHA1 encrypting loop authentication
		 * 
		 * @access public
		 * @return void
		 */
		function login () {
			$this->writeData ('<iq id="jcl_11" type="get"><query xmlns="jabber:iq:auth"><username>' . $this->_username . '</username></query></iq>');

			sleep (1);

			$hash_rawdata = $this->readData ();

			$password_hash = bin2hex (mhash (MHASH_SHA1, $this->_stream_id . $this->_password));

			$this->writeData ('<iq id="jcl_12" type="set"><query xmlns="jabber:iq:auth"><username>' . $this->_username . '</username><resource>phpBaseClasses classJabber.php</resource><digest>' . $password_hash . '</digest></query></iq>');

			sleep (2);

			$this->readData ();

			$this->writeData ('<iq id="jcl_13" type="get"><query xmlns="jabber:iq:roster"/></iq>');
			$this->readData ();
			$this->writeData ('<iq id="jcl_14" type="get"><query xmlns="jabber:iq:private"><bookmarks xmlns="storage:bookmarks"/></query></iq>');
			$this->readData ();
			$this->writeData ('<presence><status>available</status><priority>0</priority></presence>');
			$this->readData ();
			$this->writeData ('<iq id="jcl_15" to="' . $this->_host . '" type="get"><query xmlns="jabber:iq:agents"/></iq>');
			$this->readData ();
			$this->writeData ('<iq id="jcl_16" type="get"><query xmlns="jabber:iq:private"><storage xmlns="storage:imprefs"/></query></iq>');

			sleep (2);

			$this->readData ();

			if ($this->_aim) {
				$this->writeData ('<presence to="aim.localhost/registered"/>');

				sleep (2);

				$this->readData ();
			}
		}

		/**
		 * Send a message to another user on the jabber server
		 * 
		 * @access public
		 * @param string Nickname of user
		 * @param string Message to send to user
		 * return void
		 */
		function sendMessage ($nick, $message) {
			$pos = strpos ($nick, '@');
			if ($pos === false) {
				$nick .= '@' . $this->_host;
			}

			$this->writeData ("<message id='' to='$nick'><body>" . htmlspecialchars ($message) . "</body></message>");

			// Avoid being disconnected for spam, sleep after sending
			sleep (1);
		}

		/**
		 * Writes data to the jabber connection
		 *
		 * @access public
		 * @param string Data to the jabber connection
		 * @return void
		 */
		function writeData ($data) {
			debug ('Jabber::writeData (' . $data . ')', 4);

			fwrite ($this->_sock, $data . "\n");
		}

		/**
		 * Reads data from the jabber connection
		 *
		 * @access public
		 * @return Data from the jabber connection
		 */
		function readData () {
			$data = fread ($this->_sock, 16384);

			debug ('Jabber::readData () -- ' . $data, 4);

			return $data;
		}

		/**
		 * Closes connection to jabber server
		 *
		 * @access public
		 * @return void
		 */
		function Destroy () {
			$this->writeData ('</stream:stream>');
			fclose ($this->_sock);
		}
	}
}
?>
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
 * @version $Revision: 1.00 $ $Date: 2003/07/03 10:36:56 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

if (defined('CLASSSIMPLEPDFWRITE')) {
        return;
} else {
	/**
	 * Constant used to workaround buggy include_once and require_once
	 */
	define('CLASSSIMPLEPDFWRITE', 1);

	/**
	 * The API for writting simple PDFs
	 *
	 * This class contains the basics needed for writting simple
	 * documents in PDF format.
	 *
	 * @package phpBaseClasses
	 */
	class simplePDFWrite {
		/**
		 * Version of this class
		 *
		 * @var integer $_version
		 * @access private
		 */
		var $_version;

		/**
		 * Handle to PDF functions
		 *
		 * @var integer $_pdf
		 * @access private
		 */
		var $_pdf;

		/**
		 * Path to where pdf is written
		 *
		 * @var string $_filename
		 * @access private
		 */
		var $_filename;

		/**
		 * Current X position where PDF is writting
		 *
		 * @var integer $_curX
		 * @access private
		 */
		var $_curX;

		/**
		 * Current Y position where PDF is writting
		 *
		 * @var integer $_curY
		 * @access private
		 */
		var $_curY;

		/**
		 * Default X of the page
		 *
		 * @var integer $_defaultX
		 * @access private
		 */
		var $_defaultX;

		/**
		 * Default Y of the page
		 *
		 * @var integer $_defaultY
		 * @access private
		 */
		var $_defaultY;

		/**
		 * The title that will be used if the text roles into the next page
		 *
		 * @var integer $_nextTitle
		 * @access private
		 */
		var $_nextTitle;

		/**
		 * Height of the current section
		 *
		 * @var integer $_height
		 * @access private
		 */
		var $_height;

		/**
		 * The next page number
		 *
		 * @var integer $_pageNumber
		 * @access private
		 */
		var $_pageNumber;

		/**
		 * Current font name
		 *
		 * @var integer $_font
		 * @access private
		 */
		var $_font;

		/**
		 * Font encoding to use for current font
		 *
		 * @var integer $_fontEncoding
		 * @access private
		 */
		var $_fontEncoding;

		/**
		 * Constructor, opens up handles and sets up initial variables
		 *
		 * @access public
		 * @param string Specify where to write to, otherwise a random file in /tmp will be made
		 * @return void
		 */
		function simplePDFWrite ($filename='', $uprfile='/usr/local/share/pdflib/fonts/pdflib.upr') {
			$this->_version = 0.1;

			if (empty ($filename)) 
				$filename = tempnam ('/tmp', 'pdf-');

			$this->_filename = $filename;

			$this->_pdf = pdf_new ();
			pdf_open_file ($this->_pdf, $filename);

			pdf_set_parameter ($this->_pdf, 'resourcefile', $uprfile); 

			$this->_pageNumber = 1;
			$this->_fontEncoding = 'builtin';
		}

		/**
		 * Set the font and its size
		 *
		 * @access public
		 * @param string Font name to set
		 * @param integer Height of the font
		 * @return void
		 */
		function setFont ($font='', $height=10) {
			if (empty ($font)) {
				if (empty ($this->_font)) {
					$this->_font = 'Times-Roman';
				}
			} else {
				$this->_font = $font;
			}

			$this->_height = $height;

			$font = pdf_findfont ($this->_pdf, $this->_font, $this->_fontEncoding, 1);
			if ($font) 
				pdf_setfont ($this->_pdf, $font, 10);
		}

		/**
		 * Info associated with the pdf
		 *
		 * @access public
		 * @param string Author of the PDF
		 * @param string Title of the PDF
		 * @param string Subject of the PDF
		 * @param string Revision of the PDF
		 * @param string Keywords of the PDF
		 * @return void
		 */
		function info ($author='', $title='', $subject='', $revision='', $keywords='') {
			pdf_set_info ($this->_pdf, 'Author', $author);
			pdf_set_info ($this->_pdf, 'Title', $title);
			pdf_set_info ($this->_pdf, 'Creator', $author);
			pdf_set_info ($this->_pdf, 'Subject', $subject);
			pdf_set_info ($this->_pdf, 'Revision', $revision);
			pdf_set_info ($this->_pdf, 'Keywords', $keywords);
		}

		/**
		 * Begins a new page
		 *
		 * @access public
		 * @param string Text to use in the outline section of the PDF
		 * @param integer Initial X of the page
		 * @param integer Initial Y of the page
		 * @param integer Size in X of the page, default is for 8x11
		 * @param integer Size in Y of the page, default is for 8x11
		 * @return void
		 */
		function begin ($outlineText='', $startx=50, $starty=775, $sizex=595, $sizey=842) {
			$this->_curX = $startx;
			$this->_curY = $starty;

			if (!isset ($this->_defaultX)) 
				$this->_defaultX = $startx;

			if (!isset ($this->_defaultY)) 
				$this->_defaultY = $starty;

			pdf_begin_page ($this->_pdf, $sizex, $sizey);

			pdf_set_value ($this->_pdf, 'textrendering', 0);
			pdf_add_outline ($this->_pdf, $outlineText);

			$this->_pageNumber++;
		}

		/**
		 * Writes a header on the page
		 *
		 * @access public
		 * @param string Text to place in the header
		 * @param integer Height for the font of the header
		 * @param integer Initial X for the header
		 * @return void
		 */
		function header ($text='', $height=20, $startx=50) {
			$this->_curX = $startx;
			$this->setFont ('', $height);

			pdf_show_xy ($this->_pdf, $text, $this->_curX, $this->_curY);

			$this->_curY = $this->_curY - $this->_height;
			$this->checkNewPage ();
		}

		/**
		 * Writes text within the current page
		 *
		 * @access public
		 * @param string Text to write
		 * @param integer Initial X of the text
		 * @param integer Height of the text
		 * @return void
		 */
		function text ($text='', $startx=75, $height=10) {
			$this->_curX = $startx;
			$this->setFont ('', $height);

			while (strlen($text) >= 70) {
				if (strlen($text) <= 75)  
					$maxlen = 5; 
				else 
					$maxlen = 10;

				$spacepos = strrpos (substr($text, 65, $maxlen), ' ');
				if ($spacepos > 0) {
					pdf_show_xy ($this->_pdf, substr($text, 0, 65 + $spacepos), $this->_curX, $this->_curY);
					$text = substr ($text, 66 + $spacepos);
				} else {
					pdf_show_xy ($this->_pdf, substr($text, 0, 70) . '-', $this->_curX, $this->_curY);
					$text = substr ($text, 70);
				}

				$this->_curY = $this->_curY - $this->_height;
				$this->checkNewPage ();
			}

			pdf_show_xy ($this->_pdf, $text, $this->_curX, $this->_curY);
			$this->_curY = $this->_curY - $this->_height;
			$this->checkNewPage ();
		}   

		/**
		 * Sets the title to use in case the text wraps into the next page
		 *
		 * @access public
		 * @param string Text of next title
		 * @return void
		 */
		function setNextTitle ($newtitle='') {
			$this->_nextTitle = $newtitle;
		}

		/**
		 * Checks if a new page should be started, and does so
		 *
		 * @access private
		 * @return void
		 */
		function checkNewPage () {
			if ($this->_curY <= 75) {
				if (empty ($this->_nextTitle)) {
					$this->_nextTitle = 'Page ' . $this->_pageNumber;
				}

				pdf_end_page ($this->_pdf);
				$this->begin ($this->_nextTitle, $this->_defaultX, $this->_defaultY);
			}
		}

		/**
		 * Writes a line in the current page
		 *
		 * @access public
		 * @param integer Initial X of the line
		 * @param integer Offset of X for the first point
		 * @param integer Offset of X for the last point
		 * @param integer Height of the line
		 * @return void
		 */
		function line ($startx=50, $offsetx1=-15, $offsetx2=475, $height=10) {
			$this->setFont ('', $height);
			$this->_curX = $startx;

			pdf_moveto ($this->_pdf, $this->_curX + $offsetx1, $this->_curY);
			pdf_lineto ($this->_pdf, $this->_curX + $offsetx2, $this->_curY);
			pdf_stroke ($this->_pdf);

			$this->_curY = $this->_curY - $this->_height;
		}

		/**
		 * Creates a space in the current page
		 *
		 * @access public
		 * @param integer Height of the space
		 * @return void
		 */
		function space ($height=10) {
			$this->setFont ('', $height);
			$this->_curY = $this->_curY - $this->_height;
		}
  
		/**
		 * Closes the handles and file pointers
		 *
		 * @access public
		 * @return void
		 */
		function destroy () {
			pdf_end_page ($this->_pdf);        
			pdf_close ($this->_pdf);

			return $this->_filename;
		}
	}
}
?>
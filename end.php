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
 * @version $Revision: 1.6 $ $Date: 2004/06/03 08:12:11 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	debug ('End Page', -1);

	if (($config['CACHING_ENABLED']) && ($GENERATE_CACHE)) {
		if (isset($CACHE_FP)) {
			debug ('Save Cache', 3);
			fputs ($CACHE_FP, ob_get_contents (), ob_get_length ());
			flock ($CACHE_FP, LOCK_UN);
			fclose ($CACHE_FP);
		} else {
			debug ('Should save cache but file pointer is not available', 2);
		}
	}

	if (isset ($db)) {
		$db->close ();
	}
?>
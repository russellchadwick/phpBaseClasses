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
 * @version $Revision: 1.2 $ $Date: 2003/09/04 18:53:54 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

	echo '
	<br>
	<center>
		<a href="user_details.php?user_id=' . $_GET['user_id'] . '">[ User Details ]</a>
		<a href="role_details.php?user_id=' . $_GET['user_id'] . '">[ Role Details ]</a>
		<a href="permission_details.php?user_id=' . $_GET['user_id'] . '">[ Permission Details ]</a>
		<a href="preference_details.php?user_id=' . $_GET['user_id'] . '">[ Preference Details ]</a>
		<a href="user_security_log_list.php?user_id=' . $_GET['user_id'] . '">[ Security Log ]</a>
	</center>
	<br>'
?>
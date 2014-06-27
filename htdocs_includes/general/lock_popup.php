<?php
	$DEBUG = 1;
	include 'begin.php';
	echo $display->head ('Lock Popup');

	$lockdata = $security->lockData ($_GET['security_object_id'], $_GET['security_object_reference_id'], false);
	if ($lockdata == '') {
		echo 'Lock is available.  Closing this window.';
		echo $display->reloadParentAndClose ();
	} else {
		echo 'Lock is unavailable.  This window will periodically check.  When lock is available this window will close.<br><br>';
		echo $lockdata;
		echo $display->refreshWindow (20);
	}

	include 'end.php';
?>
<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     popup_init
 * Purpose:  initialize overlib
 * -------------------------------------------------------------
 */
function smarty_function_popup_init($params, &$smarty)
{
	$zindex = 1000;
	
    if (!empty($params['zindex'])) {
		$zindex = $params['zindex'];
	}
	
    if (!empty($params['src'])) {
        return '';
    } else {
        $smarty->trigger_error("popup_init: missing src parameter");
    }
}

/* vim: set expandtab: */

?>

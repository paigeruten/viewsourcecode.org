<?php

/* fix_magic_quotes.php
 *
 * This file reverses the effects of magic_quotes -- a PHP feature that
 * automagically backslash-escapes quote characters in the $_GET, $_POST, and
 * $_COOKIE globals. This file will check if magic_quotes is on, and then
 * unescape the GPC globals.
 */

// This function recursively calls stripslashes() on all items of $array, up to
// 3 levels of array nesting (which should be enough for $_GET, $_POST, and
// $_COOKIE).
function stripslashes_array(&$array, $iterations=0) {
	if ($iterations < 3) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				stripslashes_array($array[$key], $iterations + 1);
			} else {
				$array[$key] = stripslashes($array[$key]);
			}
		}
	}
}

if (get_magic_quotes_gpc()) {
	stripslashes_array($_GET);
	stripslashes_array($_POST);
	stripslashes_array($_COOKIE);
}

?>

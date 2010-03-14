<?php

/* sourceview.php
 *
 * This file contains whitelist arrays to control which files and directories
 * user can view.
 */

// Files with these extensions are all whitelisted.
$whitelist_extensions = array(
	'php',
	'css',
	'html',
	'png',
	'c',
	'cpp',
	'txt',
	'dog',
	'irc',
	'phps',
	'gz',
	'pl',
	'swf',
	'htm',
	'gif',
	'jpg',
	'ram',
	'wav'
);

// Same sort of thing as the $whitelist array, but contains the names of folders
// that can be browsed in the root directory.
$whitelist_directories = array(
	'',
	'images',
	'include',
	'code',
	'admin',
	'comment',
	'why'
);

?>

<?php

/* viewsource.php
 *
 * Displays highlighted PHP source code.
 */

include 'include/sourceview.php';

// Send header saying that we're using Unicode
header('Content-Type: text/html; charset=UTF-8');

// This function checks if a highlighted PHP file is empty or not.
function highlighted_php_empty($text) {
	$text = strip_tags($text);
	$text = preg_replace('/\s+/', '', $text);

	return empty($text);
}

// Make an array of directories used to get to the file.
$path = explode('/', trim($_GET['file']));

// Remove empty values from the array.
foreach ($path as $key => $value) {
	if (empty($value)) {
		unset($path[$key]);
	}
}

// Make the keys start back at 0
$path = array_values($path);

// Split the directory path and the filename
$filename = array_pop($path);

if (count($path)) {
	$full_path = implode('/', $path)."/$filename";
	$path_str = implode('/', $path).'/';
} else {
	$full_path = $filename;
	$path_str = '';
}

// Check if it's OK to display this file.
$whitelisted = in_array($path[0], $whitelist_directories) || count($path) == 0;
$has_dots = strpos($path_str, '.') !== false;
$is_php_file = substr($filename, -4) == '.php';
if (!$whitelisted || $has_dots || !$is_php_file) {
	$highlighted_php = 'This file is not whitelisted.';
} else if (!is_file($full_path)) {
	$highlighted_php = 'This file doesn\'t exist.';
} else {
	$highlighted_php = highlight_file($full_path, true);

	if (highlighted_php_empty($highlighted_php)) {
		$highlighted_php = 'This file is empty.';
	}
}

// If the highlighted file has an include, make it a link to view the source
// code of that file.
$highlighted_php = preg_replace('/\'([_a-z\/]+\.php)\'/', "'<a href=\"/viewsource/" . $path_str . "\\1\">\\1</a>'", $highlighted_php);

?>
<!DOCTYPE html>
<html>
	<head>
		<title>
			View Source Code - Viewing <?php echo $filename; ?>'s source
		</title>
		<link rel="stylesheet" type="text/css" href="/viewsource.css" />
	</head>
	<body>
		<div id="viewsource_topbar">
			<a href="/">Back to View Source Code</a>
		</div>
		<div id="viewsource_code">
			<?php echo $highlighted_php; ?>
		</div>
	</body>
</html>

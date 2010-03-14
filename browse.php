<?php

/* browse.php
 *
 * This page allows users to browse .php and other files that the site is made
 * up of. The whitelist for these files and directories is in
 * /include/sourceview.php
 */

$page_title = 'Browse the code';
include 'include/header.php';

$dir = trim(strval($_GET['dir']));

// The strpos() test for '.' is to make sure they don't edit the URL to go to,
// for example, "/home/viewsour/public_html/../" which means "/home/viewsour/"
// or "one directory lower than '/home/viewsour/public_html/'".
if (strpos($dir, '.') !== false) {
	$dir = '';
}

$directories = explode('/', $dir);
foreach ($directories as $key => $value) {
	if ($value == '' || $value == '/') {
		unset($directories[$key]);
	}
}
$dir = '/' . implode('/', $directories);

// These arrays will store all the directories and files to be listed. The
// reason they aren't just echoed out one at a time is because if I add them
// to arrays, I can echo one array and then echo the other array. The
// directories will all appear above the normal files.
$dir_list = array();
$file_list = array();

// This variable is used whenever we want to give PHP the path to the dir or
// file.
if ($dir == '/') {
	$real_path = '/home/viewsour/public_html/';
} else {
	$real_path = '/home/viewsour/public_html' . $dir . '/';
}

// Check if the directory exists and if it's whitelisted
$error = '';
if (!in_array($directories[0], $whitelist_directories)) {
	$error = 'This directory is not whitelisted.';
}

if (!is_dir($real_path)) {
	$error = 'This directory does not exist!';
}

if (empty($error)) {
	$files = scandir($real_path);

	// Read each filename (which could be a file or directory) one at a time.
	foreach ($files as $file) {
		$full_path = $real_path . $file;
		$short_path = $dir . '/' . $file;

		if ($dir == '/') {
			$short_path = substr($short_path, 1);
		}
		
		// Is it a directory?
		if (is_dir($full_path)) {
			// Don't display the link to the current directory.
			if ($file != '.') {
				// This special directory goes back to the current directory's parent directory.
				if ($file == '..') {
					// Change the path to the current directory into an array, remove the last directory from the array,
					// and change it back to a string.
					$dirs = $directories;
					array_pop($dirs);

					$dir_link = implode('/', $dirs);
					if (!empty($dir_link)) { $dir_link = "/$dir_link"; }
					array_push($dir_list, '<img src="/images/icons/folder.png" title="Folder" /> <a href="/browse' . $dir_link . '" style="font-weight: bold;">..</a>');
				} else {
					$dirs = $directories;
					
					if (count($dirs) == 0) $root_dir = $file;
					if (count($dirs) > 0) $root_dir = $dirs[0];
		
					// Only show directories that are in the whitelist.
					if (in_array($root_dir, $whitelist_directories)) {
						// Same as above but add the directory to the current directory's path.
						array_push($dirs, $file);
						$dir_link = implode('/', $dirs);
						array_push($dir_list, '<img src="/images/icons/folder.png" title="Folder" /> <a href="/browse/' . $dir_link . '" style="font-weight: bold;">' . $file . '</a>');
					}
				}
			}
		
		// Is it a file?
		} else if (is_file($full_path)) {
			$file_extension = substr($file, strrpos($file, '.') + 1);

			// Only show files with extentions that are in the whitelist.
			if (in_array($file_extension, $whitelist_extensions)) {
				if ($file_extension == 'php') {
					array_push($file_list, '<img src="/images/icons/' . $file_extension . '.png" title="PHP Source Code" /> <a href="/viewsource' . $short_path . '">' . $file . '</a>');
				} else {
					array_push($file_list, '<img src="/images/icons/' . $file_extension . '.png" title="File type: ' . strtoupper($file_extension) . '" /> <a href="' . $short_path . '">' . $file . '</a>');
				}
			}
		}
	}
	
	// Sort the arrays
	sort($dir_list);
	sort($file_list);

	$all_files = array('dir' => $dir_list, 'file' => $file_list);

	// Show some breadcrumbs
	echo '<div id="breadcrumbs">';
	echo '<ul>';
	echo '<li><a href="/browse">/</a></li>';
	foreach ($directories as $directory) {
		$cur_dir .= '/' . $directory;
		echo '<li><a href="/browse' . $cur_dir . '">' . $directory . '</a></li>';
	}
	echo '</ul>';
	echo '</div>';

	// Loop through directories and files, printing each one as a list item.
	foreach ($all_files as $type => $files) {
		echo '<ul class="browse_' . $type . '">';

		foreach ($files as $file) {
			echo '
				<li class="browse_' . $type . '">
					' . $file . '
				</li>
			';
		}

		echo '</ul>';
	}
} else {
	echo $error;
}

include 'include/footer.php';

?>

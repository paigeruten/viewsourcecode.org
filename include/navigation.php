<?php

/* navigation.php
 *
 * This file handles printing the navigation menu.
 */

// The navigation items are stored in an associative array. The key is the text
// displayed on the menu and the value is the URL name of that page.
$nav_items = array(
	'Home'             => '',
	'DS Homebrew'      => 'homebrew',
	'Experiments'      => 'xperiments',
	'About'            => 'about',
);

// This function extracts the current page from $_SERVER['PHP_SELF'] using
// regular expressions.
function current_page() {
	if (preg_match('/\/([a-z]+)\.php$/', $_SERVER['PHP_SELF'], $matches)) {
		$page = $matches[1];
	} else {
		$page = '';
	}

	// The homepage is a special case, to make things look nice we'll link
	// them to viewsourcecode.org/ instead of viewsourcecode.org/index.php.
	if ($page == 'index') {
		$page = '';
	}

	return $page;
}

// This function prints the $nav_items
function print_nav_list_items() {
	global $nav_items;

	$current_page = current_page();

	foreach ($nav_items as $nav_item => $page_name) {
		// If the user is on this nav item's page, style the nav item
		// differently.
		if ($current_page == $page_name) {
			$nav_class = 'nav_active';
		} else {
			$nav_class = 'nav_inactive';
		}

		echo '
			<li class="' . $nav_class . '">
				<a href="/' . $page_name . '">
					' . $nav_item . '
				</a>
			</li>
		';
	}
}


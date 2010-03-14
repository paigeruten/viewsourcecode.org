<?php

/* database.php
 *
 * A group of functions that have to do with connecting to and working with the
 * MySQL database.
 */

// The file included here gives values to $mysql_user, $mysql_password, and
// $mysql_db. These values (especially password) shouldn't be shown to any
// normal users.
include '/home/viewsour/dbinfo.php';

function database_connect() {
	global $mysql_user, $mysql_password, $mysql_db;

	// Connect to MySQL. The '@' operator makes sure that no errors are shown.
	$db = @mysql_connect('localhost', $mysql_user, $mysql_password);
	if (!$db) {
		die('Cannot connect to mysql.');
	}

	// Select the database.
	$result = @mysql_select_db($mysql_db, $db);
	if (!$result) {
		die('Cannot select database.');
	}
}

function show_mysql_error($error, $line_num) {
	echo '
		<div id="error">
			<strong>MySQL error on line #' . $line_num . ':</strong> ' . $error . '
		</div>
	';
	die;
}

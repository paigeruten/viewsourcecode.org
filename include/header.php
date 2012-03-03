<?php

/* header.php
 *
 * This file is included in pretty much every page on the site. It includes all
 * the functions each page will need, connects to the database, and prints the
 * HTML that should appear on every page.
 */

// This must be done before using $_SESSION.
session_start();

// Include files that will be needed on most pages.
include_once 'fix_magic_quotes.php';
include_once 'database.php';
include_once 'navigation.php';
include_once 'bbcode.php';
include_once 'comment.php';
include_once 'sourceview.php';

// Connect to database.
database_connect();

// Send header saying that we're using Unicode
header('Content-Type: text/html; charset=UTF-8');

// Make the title.
if (empty($page_title)) {
	$page_title = "Jeremy Ruten";
} else {
	$page_title = "Jeremy Ruten: $page_title";
}

// Go out of PHP mode and print the HTML header.
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $page_title; ?></title>
		<link rel="stylesheet" href="/style.css" />
		<link rel="stylesheet" href="/code.css" />
		<link rel="shortcut icon" href="/favicon.ico">

		<link rel="openid.server" href="http://www.myopenid.com/server">
		<link rel="openid.delegate" href="http://jeremy.ruten.myopenid.com/">
	</head>
	<body>
		<div id="container">
			<div id="top">
				<div id="banner">
					<h1>Jeremy Ruten</h1>
				</div>
				<div id="nav">
					<ul>
						<?php print_nav_list_items(); ?>
					</ul>
				</div>
			</div>
			<div id="content">
				<?php
				if ($_SESSION['commented']) {
					unset($_SESSION['commented']);
					echo '
						<div id="messagebox">
							Thankyou! Your comment will be published after its approval by the admin.
						</div>
					';
				}
				?>

<?php

/* administrate.php
 *
 * Allows an admin to add, edit, and delete content on the site.
 */

// Sessions will be used to authorize the user.
session_start();

$error = '';

// If they submitted the authorization form, authenticate them.
if (isset($_POST['pw'])) {
	include 'include/fix_magic_quotes.php';

	$sha1_password = sha1($_POST['pw']);
	$real_password = trim(file_get_contents('/home/viewsour/password'));

	if ($sha1_password == $real_password) {
		$_SESSION['is_admin'] = true;
	} else if ($_POST['pw'] == 'the admin password') {
		$error = 'Even <em>that\'s</em> not the admin password.';
	} else {
		$error = 'That\'s not the admin password.';
	}
}

$page_title = 'Admin';
include 'include/header.php';

if (!empty($error)) {
	echo '<div id="error">' . $error . '</div>';
}

// If they're not authorized as an admin, print the authorization form and then
// exit the script.
if (!$_SESSION['is_admin']) {
	echo '
		<p>
			Please enter the admin password.
		</p>

		<form action="/administrate" method="post">
			<input type="password" name="pw" />
			<input type="submit" value="Go" />
		</form>
	';

	include 'include/footer.php';
	die;
}

// Find the number of unpublished comments waiting to be approved.
$sql = "SELECT COUNT(id) AS total FROM comments WHERE NOT published";
$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
$count = mysql_fetch_assoc($result);
$num_unpublished_comments = $count['total'];

$pages = array(
	'homebrew' => 'DS Homebrew',
	'blog' => 'Blog',
	'comments' => "Comments ($num_unpublished_comments)",
	'logout' => 'Log Out',
);

if (in_array($_GET['page'], array_keys($pages))) {
	include 'admin/' . $_GET['page'] . '.php';
} else {
	echo '
		<p>
			Please select a section to edit.
		</p>

		<ul class="admin_links">
	';

	foreach ($pages as $page => $title) {
		echo '
			<li>
				<a href="/administrate/' . $page . '">
					<img src="/images/admin-icons/' . $page . '.png" />
					' . $title . '
				</a>
			</li>
		';
	}

	echo '</ul>';
}

include 'include/footer.php';

?>

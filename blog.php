<?php

/* blog.php
 *
 * Displays a blog entry and comments.
 */

session_start();

include 'include/database.php';

database_connect();

$sql = "SELECT * FROM posts WHERE url_title = '" . mysql_real_escape_string($_GET['title']) . "'";
$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

if (mysql_num_rows($result) == 0) {
	$page_title = 'Error';
	include 'include/header.php';

	echo '
		<div id="error">
			That post doesn\'t appear to exist.
		</div>
	';

	include 'include/footer.php';
	die;
}

$blog = mysql_fetch_assoc($result);

if (!$blog['publish'] && !$_SESSION['is_admin']) {
	$page_title = 'Error';
	include 'include/header.php';

	echo '
		<div id="error">
			That post isn\'t published.
		</div>
	';

	include 'include/footer.php';
	die;
}

$page_title = htmlentities($blog['title']);
include 'include/header.php';

echo '
	<h1>' . htmlentities($blog['title']) . '</h1>

	' . $blog['content'] . '
';

print_comments_box('blog', $blog['id']);

include 'include/footer.php';

?>


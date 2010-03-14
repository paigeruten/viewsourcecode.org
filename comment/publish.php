<?php

session_start();

include '../include/fix_magic_quotes.php';
include '../include/database.php';

database_connect();

$comment_id = intval($_GET['comment']);
$sql = "SELECT * FROM comments WHERE id = $comment_id";
$result_comment = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
$comment = mysql_fetch_array($result_comment);

$errors = array();
if (!$_SESSION['is_admin']) { array_push($errors, 'You must be authorized as an admin to publish comments.'); }
if (mysql_num_rows($result_comment) == 0) { array_push($errors, 'That comment doesn\'t exist.'); }

if (count($errors) == 0) {
	$sql = "UPDATE comments SET published = 1 WHERE id = " . $comment['id'];
	mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	if ($comment['type'] == 'homebrew') {
		// Get homebrew's url_name to redirect them back to it.
		$sql = "SELECT url_name FROM homebrew WHERE id = " . $comment['article'];
		$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
		$homebrew = mysql_fetch_array($result);

		header('Location: http://viewsourcecode.org/homebrew/' . $homebrew['url_name'] . '#comment' . $comment['id']);
	}

	if ($comment['type'] == 'blog') {
		$sql = "SELECT url_title FROM posts WHERE id = " . $comment['article'];
		$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
		$blog = mysql_fetch_array($result);

		header('Location: http://viewsourcecode.org/posts/' . $blog['url_title'] . '#comment' . $comment['id']);
	}
	die;
}

// The rest of the code is only executed if there was an error
$page_title = 'Publish comment';
include '../include/header.php';

foreach ($errors as $error) {
	echo '<div id="error">' . $error . '</div>';
}

include '../include/footer.php';

?>

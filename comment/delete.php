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
if (!$_SESSION['is_admin']) { array_push($errors, 'You must be authorized as an admin to delete comments.'); }
if (mysql_num_rows($result_comment) == 0) { array_push($errors, 'That comment doesn\'t exist.'); }

if (count($errors) == 0) {
	$sql = "DELETE FROM comments WHERE id = " . $comment['id'];
	mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	// ban user
	if (isset($_GET['ban'])) {
		$banned_users = array_map('trim', file('../banned_usernames'));
		$banned_users[] = $comment['author'];
		file_put_contents('../banned_usernames', implode("\n", $banned_users));
	}

	header('Location: http://viewsourcecode.org/administrate/comments');
	die;
}

// The rest of the code is only executed if there was an error
$page_title = 'Delete comment';
include '../include/header.php';

foreach ($errors as $error) {
	echo '<div id="error">' . $error . '</div>';
}

include '../include/footer.php';
?>

<?php

session_start();

include '../include/fix_magic_quotes.php';
include '../include/database.php';

database_connect();

$comment_id = intval($_GET['comment']);
$sql = "SELECT * FROM comments WHERE id = $comment_id";
$result_comment = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
$comment = mysql_fetch_array($result_comment);

// If they submitted the form, check for errors and, if there are no errors,
// insert the comment and redirect to it.
if (isset($_GET['submit'])) {
	// Errors?
	$errors = array();
	if (!$_SESSION['is_admin']) { array_push($errors, 'You must be authorized as an admin to edit comments.'); }
	if (mysql_num_rows($result_comment) == 0) { array_push($errors, 'That comment doesn\'t exist.'); }
	if (trim($_POST['comment']) == '') { array_push($errors, 'Your comment is blank.'); }
	
	// If no errors, update the comment and redirect
	if (count($errors) == 0) {
		$sql = "UPDATE comments SET content = '" . mysql_real_escape_string($_POST['comment']) . "' WHERE id = " . $comment['id'];
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

			header('Location: http://viewsourcecode.org/posts/' . $blog['url_title'] . '#comment' . $comment_id);
		}
		die;
	}
}

$page_title = 'Edit comment';
include '../include/header.php';

// Errors?
if (!isset($errors)) {
	$errors = array();
	if (!$_SESSION['is_admin']) { array_push($errors, 'You must be authorized as an admin to edit comments.'); }
	if (mysql_num_rows($result_comment) == 0) { array_push($errors, 'That comment doesn\'t exist.'); }
}

// Format errors
if (count($errors) > 0) {
	foreach ($errors as $error) {
		$errors_str .= '<div id="error">'.$error.'</div>';
	}
	$errors_str .= '<br />';
}

// Use the comment's text in the database if they didn't submit new text
if (isset($_GET['submit'])) { $comment['content'] = htmlentities($_POST['comment']); }

echo '
	'.$errors_str.'
	
	<form action="/comment/edit/' . $comment['id'] . '/submit" method="post">
		<strong>Comment</strong><br />
		<textarea name="comment" rows="10" cols="60">' . $comment['content'] . '</textarea><br /><br />
			
		<input type="submit" value="Save" />
	</form>
';

include '../include/footer.php';

?>

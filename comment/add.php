<?php

// spammers...
$banned_usernames = array_map('trim', file('../banned_usernames'));

session_start();

include '../include/fix_magic_quotes.php';
include '../include/database.php';

database_connect();

// type => column
$comment_types = array(
	'homebrew' => 'homebrew',
	'blog' => 'posts'
);

// Check for valid information... we don't have to print nice-looking
// errors, since these errors only happen when they edit the URL.
if (!in_array($_GET['type'], array_keys($comment_types))) {
	die;
}

$type = $_GET['type'];
$column = $comment_types[$type];

// Make sure the article exists
$sql = "SELECT id FROM $column WHERE id = " . intval($_GET['article']);
$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
if (mysql_num_rows($result) == 0) die;

$article_id = mysql_fetch_assoc($result);
$article_id = $article_id['id'];

// Make sure the comment they're trying to reply to is of the same article and type as the article and type they're replying to.
$sql = "SELECT id FROM comments WHERE id = " . intval($_GET['replyto']) . " AND type = '$type' AND article = '$article_id'";
$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
if (mysql_num_rows($result) == 0 && intval($_GET['replyto']) != 0) die;

// If they submitted the form, check for errors and, if there are no errors,
// insert the comment and redirect to it.
if (isset($_GET['submit'])) {
	// Errors?
	$errors = array();
	if (trim($_POST['author']) == '') { array_push($errors, 'You didn\'t supply a username.'); }
	if (strlen($_POST['author']) > 50) { array_push($errors, 'Your username is too long.'); }
	if (trim($_POST['comment']) == '') { array_push($errors, 'Your comment is blank.'); }
	if (trim($_POST['location']) != '') { array_push($errors, 'Please leave the last field blank.'); }
	if (in_array(trim($_POST['author']), $banned_usernames)) { array_push($errors, 'So sorry, that username is banned. Go ahead and choose another one. (unless you\'re a baddie)'); }
	
	// Flood protection
	$sql = "SELECT id FROM comments WHERE type = '$type' AND timestamp > " . strval(time() - 10) . " AND ip = '" . $_SERVER['REMOTE_ADDR'] . "' LIMIT 1";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
	if (mysql_num_rows($result) == 1) { array_push($errors, 'You may only post a comment every 10 seconds.'); }
	
	// Did they give their e-mail or their website, or nothing at all?
	if (trim($_POST['website_email']) == '') {
		$website_email = '';
	} else if (strpos($_POST['website_email'], '@') !== false) {
		$website_email = 'mailto:' . trim(mysql_real_escape_string($_POST['website_email']));
	} else if (strpos($_POST['website_email'], 'http://') === false) {
		$website_email = 'http://' . trim(mysql_real_escape_string($_POST['website_email']));
	} else {
		$website_email = trim(mysql_real_escape_string($_POST['website_email']));
	}
	
	// If no errors, insert the comment and redirect
	if (count($errors) == 0) {
		$sql = "INSERT INTO comments (author, type, ip, content, timestamp, parent, article, username_link, is_admin) values('" . mysql_real_escape_string($_POST['author']) . "', '$type', '" . $_SERVER['REMOTE_ADDR'] . "', '" . mysql_real_escape_string($_POST['comment']) . "', '" . time() . "', '" . mysql_real_escape_string($_GET['replyto']) . "', '$article_id', '$website_email', '" . ($_SESSION['is_admin'] ? 1 : 0) . "')";
		mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

		$comment_id = mysql_insert_id();

		$_SESSION['commented'] = true;

		if ($type == 'homebrew') {
			$sql = "SELECT url_name FROM homebrew WHERE id = $article_id";
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
			$homebrew = mysql_fetch_assoc($result);
		
			header('Location: http://viewsourcecode.org/homebrew/' . $homebrew['url_name'] . '#comment' . $comment_id);
		}

		if ($type == 'blog') {
			$sql = "SELECT url_title FROM posts WHERE id = $article_id";
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
			$blog = mysql_fetch_assoc($result);

			header('Location: http://viewsourcecode.org/posts/' . $blog['url_title'] . '#comment' . $comment_id);
		}
		die;
	}
}

$page_title = 'Add a comment';
include '../include/header.php';

// Format errors
if (count($errors) > 0) {
	foreach ($errors as $error) {
		$errors_str .= '<div id="error">' . $error . '</div>';
	}
	$errors_str .= '<br />';
}

if (intval($_GET['replyto'])) {
	$replyto_url = '/replyto/' . $_GET['replyto'];
} else {
	$replyto_url = '';
}

echo '
	'.$errors_str.'
	
	<form action="/comment/add/' . $_GET['type'] . '/' . $_GET['article'] . $replyto_url . '/submit" method="post">
		<strong>Name</strong><br />
		<input type="text" name="author" value="' . htmlentities($_POST['author']) . '" /><br /><br/ >

		<strong>Website or e-mail address</strong> (optional, your username will link to this)<br />
		<input type="text" name="website_email" value="' . htmlentities($_POST['website_email']) . '" /><br /><br />

		<strong>Comment</strong><br />
		<textarea name="comment" rows="10" cols="60">' . htmlentities($_POST['comment']) . '</textarea><br /><br />

		<div style="display: none;">
			<strong>Don\'t type anything here</strong>
			<input type="text" name="location" />
			<br /><br />
		</div>

		<input type="submit" value="Post comment" />
	</form>
';

include '../include/footer.php';

?>

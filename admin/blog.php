<?php

/* blog.php
 *
 * Allows an admin to add, edit, and delete blog posts.
 */

// This file should only be included by administrate.php, not directly accessed.
if (strstr($_SERVER['SCRIPT_FILENAME'], 'administrate.php') === false) {
	die;
}

// We're using Markdown to mark up blog posts.
include_once 'include/markdown.php';

// Makes an HTML form for adding or editing a post. $action should be 'add'
// or 'edit'.
function make_form($action) {
	switch ($action) {
		case 'add':
			$form_action = '/administrate/blog/add';
			$submit_word = 'Add';
			break;

		case 'edit':
			$form_action = '/administrate/blog/' . $_GET['name'] . '/edit';
			$submit_word = 'Edit';

			$sql = "SELECT * FROM posts WHERE url_title = '" . mysql_real_escape_string($_GET['name']) . "' LIMIT 1";
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			if (mysql_num_rows($result) == 0) {
				die('This post doesn\'t exist.');
			}

			$blog = mysql_fetch_assoc($result);

			$_POST['title'] = $blog['title'];
			$_POST['url_title'] = $blog['url_title'];
			$_POST['content'] = $blog['content_markdown'];
			$_POST['publish'] = $blog['publish'];
			break;
	}

	$form = '
		<form action="' . $form_action . '" method="post">
			<div class="form_column">
				<strong>Title</strong><br />
				<input type="text" name="title" value="' . htmlentities($_POST['title']) . '" /><br /><br />

				<strong>URL title</strong><br />
				<input type="text" name="url_title" value="' . htmlentities($_POST['url_title']) . '" /><br /><br />

				<strong>Content</strong><br />
				<textarea name="content" rows="24" cols="80">' . htmlentities($_POST['content']) . '</textarea><br /><br />

				<strong>Publish</strong><br />
				<input type="checkbox" name="publish" value="1" ' . ($_POST['publish'] == '1' ? 'checked="checked"' : '') . ' />
			</div>

			<div class="form_submit">
				<input type="submit" name="submit" value="' . $submit_word . ' Post" />
			</div>
		</form>
	';

	return $form;
}

if ($_POST) {
	if (isset($_GET['add'])) {
		$error = '';
		if (trim($_POST['title']) == '') $error = 'You must provide a title.';
		if (trim($_POST['url_title']) == '') $error = 'You must provide a URL title.';
		if (trim($_POST['content']) == '') $error = 'You must provide content.';

		if ($error) {
			echo '<div id="error">' . $error . '</div><br />';
		} else {
			$sql = "
				INSERT INTO posts
					(
						`title`,
						`url_title`,
						`content`,
						`content_markdown`,
						`timestamp`,
						`publish`
					)
					values(
						'" . mysql_real_escape_string($_POST['title']) . "',
						'" . mysql_real_escape_string($_POST['url_title']) . "',
						'" . mysql_real_escape_string(Markdown($_POST['content'])) . "',
						'" . mysql_real_escape_string($_POST['content']) . "',
						'" . time() . "',
						'" . ($_POST['publish'] == '1' ? '1' : '0') . "'
					)
			";

			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			unset($_GET['add']);
			echo '<p>The post <strong>' . htmlentities($_POST['title']) . '</strong> has been added.</p>';
		}
	}

	if (isset($_GET['edit'])) {
		$error = '';
		if (trim($_POST['title']) == '') $error = 'You must provide a title.';
		if (trim($_POST['url_title']) == '') $error = 'You must provide a URL title.';
		if (trim($_POST['content']) == '') $error = 'You must provide content.';

		if ($error) {
			echo '<div id="error">' . $error . '</div><br />';
		} else {
			$sql = "SELECT id FROM posts WHERE url_title = '" . mysql_real_escape_string($_GET['name']) . "'";
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			if (mysql_num_rows($result) == 0) {
				die('This post doesn\'t appear to exist.');
			}

			$blog = mysql_fetch_assoc($result);

			$sql = "
				UPDATE posts SET
					`title` = '" . mysql_real_escape_string($_POST['title']) . "',
					`url_title` = '" . mysql_real_escape_string($_POST['url_title'])  . "',
					`content` = '" . mysql_real_escape_string(Markdown($_POST['content']))  . "',
					`content_markdown` = '" . mysql_real_escape_string($_POST['content']) . "',
					`publish` = '" . ($_POST['publish'] == '1' ? '1' : '0')  . "'
				WHERE id = " . $blog['id'] . "
			";

			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			unset($_GET['edit']);
			echo '<p>The post <strong>' . htmlentities($_POST['title']) . '</strong> has been updated.</p>';
		}
	}

	if (isset($_GET['delete'])) {
		$sql = "SELECT id FROM posts WHERE url_title = '" . mysql_real_escape_string($_GET['name']) . "'";
		$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

		if (mysql_num_rows($result)) {
			$blog = mysql_fetch_array($result);

			$sql = "DELETE FROM posts WHERE id = " . $blog['id'];
			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			echo '<p>The post <strong>' . $blog['title'] . '</strong> has been deleted.</p>';

			unset($_GET['delete']);
			unset($_GET['title']);
		}
	}
}

if (isset($_GET['name'])) {
	$sql = "SELECT id, url_title, title FROM posts WHERE url_title = '" . mysql_real_escape_string($_GET['name']) . "'";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	if (mysql_num_rows($result)) {
		$blog = mysql_fetch_assoc($result);
	} else if (!$_POST) {
		echo '
			<div id="error">That post doesn\'t appear to exist!</div>
		';

		unset($_GET['add']);
		unset($_GET['edit']);
		unset($_GET['delete']);
	}
}

if (isset($_GET['add'])) {
	echo make_form('add');
} else if (isset($_GET['edit'])) {
	echo make_form('edit');
} else if (isset($_GET['delete'])) {
	echo '
		<p><a href="/administrate/blog"><strong>Back</strong></a></p>

		<p>Are you sure you want to delete <strong>' . htmlentities($blog['title']) . '</strong>?</p>

		<form action="/administrate/blog/' . $blog['url_title'] . '/delete" method="post">
			<input type="submit" name="submit" value="Yes, delete it." />
		</form>
	';
} else {
	echo '
		<ul class="admin_links">
			<li>
				<a href="/administrate/blog/add">
					<img src="/images/admin-icons/add.png" />
					Add a Post
				</a>
			</li>
		</ul>
	';	

	$sql = "SELECT title, url_title, publish FROM posts ORDER BY timestamp DESC";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	while ($blog = mysql_fetch_assoc($result)) {
		$colour_link = ($blog['publish'] ? '' : ' style="color: #aaa;"');

		echo '
			<div class="admin_row">
				<div class="admin_row_edit">
					<a href="/administrate/blog/' . $blog['url_title'] . '/edit">
						<img src="/images/admin-icons/edit.png" />
					</a>
				</div>
				<div class="admin_row_delete">
					<a href="/administrate/blog/' . $blog['url_title'] . '/delete">
						<img src="/images/admin-icons/delete.png" />
					</a>
				</div>
				<div class="admin_row_title">
					<a href="/posts/' . $blog['url_title'] . '"' . $colour_link . '>
						' . $blog['title'] . '
					</a>
				</div>
			</div>
		';
	}
}

?>

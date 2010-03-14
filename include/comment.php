<?php

/* comment.php
 *
 * Functions to print comments.
 */

function print_comments_box($type, $article) {
	echo '
		<div class="comments_box">
			<div class="add_comment">
				<a href="/comment/add/' . $type . '/' . $article . '"><img src="/images/admin-icons/add.png" /> Add a Comment</a>
			</div>

			<div class="comments">
	';

	$sql = "SELECT * FROM comments WHERE type = '$type' AND article = '$article' AND parent = 0 AND published ORDER BY timestamp ASC";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	while ($comment = mysql_fetch_assoc($result)) {
		print_comment($comment);
	}

	echo '
			</div>
		</div>
	';
}

function print_comment($comment, $indentation=0) {
	$admin_links = '';
	if ($_SESSION['is_admin']) {
		$admin_links = '
			<li><a href="/comment/edit/' . $comment['id'] . '">Edit</a></li>
			<li><a href="/comment/delete/' . $comment['id'] . '">Delete</a></li>
		';

		if (!$comment['published']) {
			$admin_links .= '
				<li><a href="/comment/publish/' . $comment['id'] . '">Publish</a></li>
				<li><a href="/comment/ban/' . $comment['id'] . '">Ban</a></li>
			';
		}
	}

	$comment_admin_class = '';
	if ($comment['is_admin']) {
		$comment_admin_class = '_admin';
	}
	
	if ($comment['username_link']) {
		$comment['author'] = '<a href="' . $comment['username_link'] . '">' . htmlentities($comment['author']) . '</a>';
	} else {
		$comment['author'] = htmlentities($comment['author']);
	}

	echo '
		<div class="comment' . $comment_admin_class . '" style="margin-left: ' . ($indentation * 40) . 'px" id="comment' . $comment['id'] . '">
			<div class="comment_header' . $comment_admin_class . '">
				<div class="comment_info">
					Posted by <span class="comment_author">' . $comment['author'] . '</span> on
					<span class="comment_date">' . date('F jS, Y', $comment['timestamp']) . '</span>
				</div>
				<div class="comment_links">
					<ul>
						<li><a href="/comment/add/' . $comment['type'] . '/' . $comment['article'] . '/replyto/' . $comment['id'] . '">Reply</a></li>
						' . $admin_links . '
					</ul>
				</div>
			</div>
			<div class="comment_body">
				' . bbcode($comment['content']) . '
			</div>
		</div>
	';
	
	$sql = "SELECT * FROM comments WHERE parent = " . $comment['id'] . " AND published ORDER BY timestamp ASC";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
	
	while ($comment = mysql_fetch_assoc($result)) {
		print_comment($comment, $indentation + 1);
	}
}

?>

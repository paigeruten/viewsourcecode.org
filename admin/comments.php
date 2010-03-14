<?php

/* comments.php
 *
 * Lets admin view and approve new comments.
 */

if (isset($_GET['alltrash'])) {
	session_start();
	if (!$_SESSION['is_admin']) die;

	include '../include/database.php';

	database_connect();

	$sql = "DELETE FROM comments WHERE NOT published AND timestamp < ".intval($_GET['time']);
	mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	header('Location: http://viewsourcecode.org/administrate/comments');
	die;
}

// This file should only be included by administrate.php, not directly accessed.
if (strstr($_SERVER['SCRIPT_FILENAME'], 'administrate.php') === false) {
	die;
}

$sql = "SELECT * FROM comments WHERE NOT published ORDER BY timestamp ASC";
$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

if (mysql_num_rows($result) > 0) {
	echo '
		<ul class="admin_links">
			<li>
				<a href="/administrate/comments/alltrash/'.(time() - 1).'">
					<img src="/images/admin-icons/spam_comment.png" />
					Alltrash
				</a>
			</li>
		</ul>
	';

	while ($comment = mysql_fetch_assoc($result)) {
		print_comment($comment);
	}
} else {
	echo '<p>There are no unpublished comments waiting in the queue.</p>';
}

?>

<?php

/* index.php
 *
 * This is the home page, the page you get when you go to
 * http://viewsourcecode.org/.
 */

$page_title = 'Home';
include 'include/header.php';

echo '
	<a type="application/rss+xml" href="/food">
		<div class="food">
			your RSS food
		</div><br />
	</a>
';

$sql = "SELECT * FROM posts ORDER BY timestamp DESC";
$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

$num_blogs = 0;
while ($blog = mysql_fetch_assoc($result)) {
	if ($blog['publish'] || $_SESSION['is_admin']) {
		$num_blogs++;

		$colour_link = ($blog['publish'] ? '' : ' color: #aaa;');

		echo '
			<a href="/posts/' . $blog['url_title'] . '" style="font-size: 18pt;' . $colour_link . '">' . htmlentities($blog['title']) . '</a>
			(' . date('F j, Y', $blog['timestamp']) . ')
			<br /><br />
		';
	}
}

if ($num_blogs == 0) {
	echo 'There is supposed to be a nice list of blog titles here for you to click n read, but I haven\'t written any yet.';
}

include 'include/footer.php';

?>

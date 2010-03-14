<rss version="2.0">
	<channel>
		<title>~ jeremy Ruten's abode ~</title>
		<link>http://viewsourcecode.org/</link>
		<description>an enchanted avenue, where all the snores come out of thin air</description>

		<?php

		include 'include/database.php';

		database_connect();

		$sql = "SELECT id, title, content, timestamp, url_title FROM posts WHERE publish ORDER BY timestamp DESC LIMIT 20";
		$result = mysql_query($sql) or die(mysql_error());

		while ($post = mysql_fetch_assoc($result)) {
			echo '
				<item>
					<title>' . htmlentities($post['title']) . '</title>
					<link>http://viewsourcecode.org/posts/' . $post['url_title'] . '</link>
					<description>cool whale craft</description>
				</item>
			';
		}

		?>

	</channel>
</rss>

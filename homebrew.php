<?php

/* homebrew.php
 *
 * Displays list of homebrew and details of a homebrew.
 */

if (isset($_GET['download'])) {
	if (isset($_GET['source'])) {
		$src_column = '_src';
		$src_filename = '-src';
	} else {
		$src_column = '';
		$src_filename = '';
	}

	include 'include/database.php';

	database_connect();

	$sql = "SELECT id, url_name, version FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "'";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
	$homebrew = mysql_fetch_assoc($result);

	$sql = "UPDATE homebrew SET downloads$src_column = downloads$src_column + 1 WHERE id = " . $homebrew['id'];
	mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	header('Location: http://viewsourcecode.org/downloads/' . $homebrew['url_name'] . '-' . $homebrew['version'] . $src_filename . '.zip');
	die;
}

if (isset($_GET['name'])) {
	include 'include/database.php';

	database_connect();

	$sql = "SELECT name FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "'";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	if (mysql_num_rows($result)) {
		$homebrew = mysql_fetch_assoc($result);
		$page_title = 'DS Homebrew - ' . $homebrew['name'];
	} else {
		$page_title = 'DS Homebrew - Error';
	}
} else {
	$page_title = 'DS Homebrew';
}

include 'include/header.php';

if (isset($_GET['name'])) {
	$sql = "SELECT * FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "'";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	if (!mysql_num_rows($result)) {
		echo '<div id="error">That homebrew doesn\'t appear to exist.</div>';
	} else {
		$homebrew = mysql_fetch_assoc($result);

		// Get features
		$sql = "SELECT feature FROM homebrew_features WHERE homebrew_id = " . $homebrew['id'];
		$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

		$features = array();
		while ($feature = mysql_fetch_assoc($result)) {
			array_push($features, $feature['feature']);
		}

		// Get screenshots
		$sql = "SELECT image_filename FROM homebrew_screenshots WHERE homebrew_id = " . $homebrew['id'];
		$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

		$screenshots = array();
		while ($screenshot = mysql_fetch_assoc($result)) {
			array_push($screenshots, $screenshot['image_filename']);
		}

		// Add translations downloads for DSbible
		if ($homebrew['name'] == 'DSbible') {
			$homebrew['description'] .= '
				<br />

				<div class="bible_translations">
					<strong>Bible translations / language files</strong><br />
					The following Bible translations and language files do not come with DSbible, but may be downloaded and added to DSbible by following the instructions that come with them. (A <em>Bible translation</em> is a translation of the Bible, and a <em>language (.lang) file</em> is a translation of the text in the DSbible program itself.)<br /><br />

					<table border="1" cellpadding="10" width="100%">
						<tr>
							<td>
								<strong>Language</strong>
							</td>
							<td>
								<strong>Files</strong>
							</td>
						</tr>
						<tr>
							<td valign="top">
								Afrikaans
							</td>
							<td>
								<a href="/bible/dsbible-translation-afrikaans.zip">Afrikaans 1953 Vertaling</a> formatted by <strong>Johan Kok</strong><br />
								<a href="/bible/dsbible-lang-afrikaans.zip">.lang file</a> translated by <strong>Johan Kok</strong>
							</td>
						</tr>
						<tr>
							<td valign="top">
								Deutsch
							</td>
							<td>
								<a href="/bible/dsbible-translation-deutsch.zip">Deutsch Schlachter</a> formatted by <strong>Dominikus Koch</strong><br />
								<a href="/bible/dsbible-translation-fb2004.zip">FreeBible 2004</a> formatted by <strong>Michael Mustun</strong><br />
								<a href="/bible/dsbible-translation-relb.zip">Revidierte Elberfelder</a> formatted by <strong>Jens Willms</strong><br />
								<a href="/bible/dsbible-lang-deutsch.zip">.lang file</a> translated by <strong>Jens Willms</strong>
							</td>
						</tr>
						<tr>
							<td valign="top">
								English
							</td>
							<td>
								<a href="/bible/dsbible-translation-asv.zip">American Standard Version</a> formatted by <strong>David Kienitz</strong><br />
								<a href="/bible/dsbible-translation-darby.zip">Darby Version</a> formatted by <strong>David Kienitz</strong><br />
								<a href="/bible/dsbible-translation-websters.zip">Webster\'s Bible</a> formatted by <strong>David Kienitz</strong><br />
								<a href="/bible/dsbible-translation-nkjv.zip">New King James Version</a> formatted by <strong>Adam Humphreys</strong>
							</td>
						</tr>
						<tr>
							<td valign="top">
								Español
							</td>
							<td>
								<a href="/bible/dsbible-translation-es.zip">Reina Valera (1909)</a> formatted by <strong>Brian Falco</strong><br />
								<a href="/bible/dsbible-lang-es.zip">.lang file</a> translated by <strong>Brian Falco</strong>
							</td>
						</tr>
						<tr>
							<td valign="top">
								Português
							</td>
							<td>
								<a href="/bible/dsbible-translation-nvi.zip">Bíblia NVI</a> formatted by <strong>Miguel Carlos dos Santos Junior</strong>
							</td>
						</tr>
					</table><br />

					If you want your translation(s) added, please e-mail them to <a href="mailto:jeremy.ruten@gmail.com"><strong>jeremy.ruten@gmail.com</strong></a>.
				</div>
			';
		}

		echo '
			<h1 class="homebrew_page_title">' . $homebrew['name'] . '</h1>

			<p class="homebrew_page_description">' . $homebrew['description'] . '</p>

			<h3 class="homebrew_page_subtitle">Features</h3>

			<ul class="homebrew_page_features">
		';

		foreach ($features as $feature) {
			echo '
				<li>' . $feature . '</li>
			';
		}

		echo '
			</ul>

			<h3 class="homebrew_page_subtitle">Screenshots</h3>

			<ul class="homebrew_page_screenshots">
		';

		foreach ($screenshots as $screenshot) {
			echo '
				<li><img src="/images/screenshots/' . $screenshot . '" /></li>
			';
		}

		echo '
			</ul>

			<h3 class="homebrew_page_subtitle">Downloads</h3>

			<ul class="homebrew_page_downloads">
				<li><a href="/homebrew/' . $homebrew['url_name'] . '/download">Download ' . $homebrew['name'] . ' ' . $homebrew['version'] . '</a> (<strong>' . $homebrew['downloads'] . '</strong> downloads)</li>
				<li><a href="/homebrew/' . $homebrew['url_name'] . '/download/source">Download the source code</a> (<strong>' . $homebrew['downloads_src'] . '</strong> downloads)</li>
			</ul>
		';

		print_comments_box('homebrew', $homebrew['id']);
	}
} else {
	echo '
		<p><a href="http://en.wikipedia.org/wiki/Nintendo_DS_homebrew">Nintendo DS homebrew</a> is a way of writing programs for <strong>Nintendo DS</strong>. This page lists all the DS homebrew I have released. Source code is available for all of them. Click on the <strong>More info</strong> link below each one to view features, screenshots, and comments for that homebrew.</p>
	';

	$sql = "SELECT name, url_name, version, preview_image, description FROM homebrew ORDER BY id DESC";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	while ($homebrew = mysql_fetch_array($result)) {
		echo '
			<div class="homebrew">
				<div class="homebrew_info">
					<div class="homebrew_screenshot">
						<img src="/images/homebrew-previews/' . $homebrew['preview_image'] . '" />
					</div>
					<div class="homebrew_version">
						Version ' . $homebrew['version'] . '
					</div>
				</div>
				<div class="homebrew_content">
					<div class="homebrew_title">
						' . $homebrew['name'] . '
					</div>
					<div class="homebrew_description">
						<p>' . $homebrew['description'] . '</p>

						<ul>
							<li><a href="/homebrew/' . $homebrew['url_name'] . '">&raquo; More info</a></li>
							<li><a href="/homebrew/' . $homebrew['url_name'] . '/download">&raquo; Download</a></li>
							<li><a href="/homebrew/' . $homebrew['url_name'] . '/download/source">&raquo; Download source code</a></li>
						</ul>
					</div>
				</div>
			</div>
		';
	}
}

include 'include/footer.php';

?>


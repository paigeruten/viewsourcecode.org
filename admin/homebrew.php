<?php

/* homebrew.php
 *
 * Allows an admin to add, edit, and delete homebrews.
 */

// This file should only be included by administrate.php, not directly accessed.
if (strstr($_SERVER['SCRIPT_FILENAME'], 'administrate.php') === false) {
	die;
}

// Gets all the filenames of screenshots and returns a <select> box to choose one
// of them.
function screenshots_select($n) {
	static $screenshots;

	if (!$screenshots) {
		$screenshots = scandir('/home/viewsour/public_html/images/screenshots');
	}

	$select = '<select name="screenshots[]">';
	$select .= '<option value="">n/a</option>';

	foreach ($screenshots as $screenshot) {
		if ($screenshot != '.' && $screenshot != '..') {
			if ($screenshot == $_POST['screenshots'][$n]) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}

			$select .= '<option value="' . $screenshot . '" ' . $selected . '>' . $screenshot . '</option>';
		}
	}

	$select .= '</select><br />';

	return $select;
}

// Makes an HTML form for adding or editing a homebrew. $action should be 'add'
// or 'edit'.
function make_form($action) {
	switch ($action) {
		case 'add':
			$form_action = '/administrate/homebrew/add';
			$submit_word = 'Add';
			break;

		case 'edit':
			$form_action = '/administrate/homebrew/' . $_GET['name'] . '/edit';
			$submit_word = 'Edit';

			$sql = "SELECT * FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "' LIMIT 1";
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			if (mysql_num_rows($result) == 0) {
				die('This homebrew doesn\'t exist.');
			}

			$homebrew = mysql_fetch_assoc($result);

			$_POST['name'] = $homebrew['name'];
			$_POST['url_name'] = $homebrew['url_name'];
			$_POST['description'] = $homebrew['description'];
			$_POST['version'] = $homebrew['version'];
			$_POST['preview_image'] = $homebrew['preview_image'];

			$sql = "SELECT * FROM homebrew_features WHERE homebrew_id = " . $homebrew['id'];
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			$n = 0;
			while ($feature = mysql_fetch_assoc($result)) {
				$_POST['features'][$n] = $feature['feature'];
				$n++;
			}

			$sql = "SELECT * FROM homebrew_screenshots WHERE homebrew_id = " . $homebrew['id'];
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			$n = 0;
			while ($screenshot = mysql_fetch_assoc($result)) {
				$_POST['screenshots'][$n] = $screenshot['image_filename'];
				$n++;
			}
			break;
	}

	$form = '
		<form action="' . $form_action . '" method="post">
			<div class="form_column">
				<strong>Homebrew name</strong><br />
				<input type="text" name="name" value="' . htmlentities($_POST['name']) . '" /><br /><br />

				<strong>URL name</strong><br />
				<input type="text" name="url_name" value="' . htmlentities($_POST['url_name']) . '" /><br /><br />

				<strong>Description</strong><br />
				<input type="text" name="description" value="' . htmlentities($_POST['description']) . '" /><br /><br />

				<strong>Version</strong><br />
				<input type="text" name="version" value="' . htmlentities($_POST['version']) . '" /><br /><br />

				<strong>Preview image</strong><br />
				<select name="preview_image">
	';

	$preview_images = scandir('/home/viewsour/public_html/images/homebrew-previews');

	foreach ($preview_images as $preview_image) {
		if ($preview_image != '.' && $preview_image != '..') {
			if ($preview_image == $_POST['preview_image']) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}

			$form .= '
					<option value="' . htmlentities($preview_image) . '" ' . $selected . '>' . $preview_image . '</option>
			';
		}
	}

	$form .= '
				</select>
			</div>

			<div class="form_column">
				<strong>Features</strong><br />
	';

	for ($n = 0; $n < 10; $n++) {
		$form .= '
				<input type="text" name="features[]" value="' . htmlentities($_POST['features'][$n]) . '" /><br />
		';
	}

	$form .= '
			</div>

			<div class="form_column">
				<strong>Screenshots</strong><br />
	';

	for ($n = 0; $n < 10; $n++) {
		$form .= screenshots_select($n);
	}

	$form .= '
			</div>

			<div class="form_submit">
				<input type="submit" name="submit" value="' . $submit_word . ' Homebrew" />
			</div>
		</form>
	';

	return $form;
}

if ($_POST) {
	if (isset($_GET['add'])) {
		$error = '';
		if (trim($_POST['name']) == '') $error = 'You must provide a name.';
		if (trim($_POST['url_name']) == '') $error = 'You must provide a URL name.';
		if (trim($_POST['version']) == '') $error = 'You must provide a version number.';

		if ($error) {
			echo '<div id="error">' . $error . '</div><br />';
		} else {
			$sql = "
				INSERT INTO homebrew
					(
						`name`,
						`url_name`,
						`description`,
						`version`,
						`preview_image`
					)
					values(
						'" . mysql_real_escape_string($_POST['name']) . "',
						'" . mysql_real_escape_string($_POST['url_name']) . "',
						'" . mysql_real_escape_string($_POST['description']) . "',
						'" . mysql_real_escape_string($_POST['version']) . "',
						'" . mysql_real_escape_string($_POST['preview_image']) . "'
					)
			";

			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			$homebrew_id = mysql_insert_id();

			foreach ($_POST['features'] as $feature) {
				if (trim($feature)) {
					$sql = "
						INSERT INTO homebrew_features
							(
								`homebrew_id`,
								`feature`
							)
							values(
								'$homebrew_id',
								'" . mysql_real_escape_string($feature) . "'
							)
					";

					mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
				}
			}

			foreach ($_POST['screenshots'] as $screenshot) {
				if (trim($screenshot)) {
					$sql = "
						INSERT INTO homebrew_screenshots
							(
								`homebrew_id`,
								`image_filename`
							)
							values(
								'$homebrew_id',
								'" . mysql_real_escape_string($screenshot) . "'
							)
					";

					mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
				}
			}

			unset($_GET['add']);
			echo '<p>The homebrew <strong>' . htmlentities($_POST['name']) . '</strong> has been added.</p>';
		}
	}

	if (isset($_GET['edit'])) {
		$error = '';
		if (trim($_POST['name']) == '') $error = 'You must provide a name.';
		if (trim($_POST['url_name']) == '') $error = 'You must provide a URL name.';
		if (trim($_POST['version']) == '') $error = 'You must provide a version number.';

		if ($error) {
			echo '<div id="error">' . $error . '</div><br />';
		} else {
			$sql = "SELECT id FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "'";
			$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			if (mysql_num_rows($result) == 0) {
				die('This homebrew doesn\'t appear to exist.');
			}

			$homebrew = mysql_fetch_assoc($result);

			$sql = "
				UPDATE homebrew SET
					`name` = '" . mysql_real_escape_string($_POST['name']) . "',
					`url_name` = '" . mysql_real_escape_string($_POST['url_name'])  . "',
					`description` = '" . mysql_real_escape_string($_POST['description'])  . "',
					`version` = '" . mysql_real_escape_string($_POST['version'])  . "',
					`preview_image` = '" . mysql_real_escape_string($_POST['preview_image'])  . "'
				WHERE id = " . $homebrew['id'] . "
			";

			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			// To update features and screenshots, delete the old
			// ones and add all the new ones again
			$sql = "DELETE FROM homebrew_features WHERE homebrew_id = " . $homebrew['id'];
			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			$sql = "DELETE FROM homebrew_screenshots WHERE homebrew_id = " . $homebrew['id'];
			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			foreach ($_POST['features'] as $feature) {
				if (trim($feature)) {
					$sql = "
						INSERT INTO homebrew_features
							(
								`homebrew_id`,
								`feature`
							)
							values(
								'" . $homebrew['id'] . "',
								'" . mysql_real_escape_string($feature) . "'
							)
					";

					mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
				}
			}

			foreach ($_POST['screenshots'] as $screenshot) {
				if (trim($screenshot)) {
					$sql = "
						INSERT INTO homebrew_screenshots
							(
								`homebrew_id`,
								`image_filename`
							)
							values(
								'" . $homebrew['id'] . "',
								'" . mysql_real_escape_string($screenshot) . "'
							)
					";

					mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);
				}
			}

			unset($_GET['edit']);
			echo '<p>The homebrew <strong>' . htmlentities($_POST['name']) . '</strong> has been updated.</p>';
		}
	}

	if (isset($_GET['delete'])) {
		$sql = "SELECT id FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "'";
		$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

		if (mysql_num_rows($result)) {
			$homebrew = mysql_fetch_array($result);

			$sql = "DELETE FROM homebrew WHERE id = " . $homebrew['id'];
			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			$sql = "DELETE FROM homebrew_features WHERE homebrew_id = " . $homebrew['id'];
			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			$sql = "DELETE FROM homebrew_screenshots WHERE homebrew_id = " . $homebrew['id'];
			mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

			echo '<p>The homebrew <strong>' . $homebrew['name'] . '</strong> has been deleted.</p>';

			unset($_GET['delete']);
			unset($_GET['name']);
		}
	}
}

if (isset($_GET['name'])) {
	$sql = "SELECT id, url_name, name FROM homebrew WHERE url_name = '" . mysql_real_escape_string($_GET['name']) . "'";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	if (mysql_num_rows($result)) {
		$homebrew = mysql_fetch_assoc($result);
	} else {
		echo '
			<div id="error">That homebrew doesn\'t appear to exist!</div>
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
		<p><a href="/administrate/homebrew"><strong>Back</strong></a></p>

		<p>Are you sure you want to delete the <strong>' . htmlentities($homebrew['name']) . '</strong> homebrew?</p>

		<form action="/administrate/homebrew/' . $homebrew['url_name'] . '/delete" method="post">
			<input type="submit" name="submit" value="Yes, delete it." />
		</form>
	';
} else {
	echo '
		<ul class="admin_links">
			<li>
				<a href="/administrate/homebrew/add">
					<img src="/images/admin-icons/add.png" />
					Add a Homebrew
				</a>
			</li>
		</ul>
	';	

	$sql = "SELECT name, url_name FROM homebrew";
	$result = mysql_query($sql) or show_mysql_error(mysql_error(), __LINE__);

	while ($homebrew = mysql_fetch_assoc($result)) {
		echo '
			<div class="admin_row">
				<div class="admin_row_edit">
					<a href="/administrate/homebrew/' . $homebrew['url_name'] . '/edit">
						<img src="/images/admin-icons/edit.png" />
					</a>
				</div>
				<div class="admin_row_delete">
					<a href="/administrate/homebrew/' . $homebrew['url_name'] . '/delete">
						<img src="/images/admin-icons/delete.png" />
					</a>
				</div>
				<div class="admin_row_title">
					<a href="/homebrew/' . $homebrew['url_name'] . '">
						' . $homebrew['name'] . '
					</a>
				</div>
			</div>
		';
	}
}

?>

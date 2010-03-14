<?php

/* logout.php
 *
 * Logs out of the admin area.
 */

unset($_SESSION['is_admin']);

echo 'You are now logged out of the admin area.';

?>

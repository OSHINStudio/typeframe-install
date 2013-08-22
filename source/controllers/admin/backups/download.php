<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

$filename = $_REQUEST['filename'];
$fullpath = TYPEF_DIR . '/files/secure/backups/' . $filename;
if (file_exists($fullpath)) {
	header('Content-type: application/gzip');
	header('Content-disposition: attachment; filename="' . $filename . '"');
	readfile($fullpath);
	exit;
}
// TODO: Return 404
?>
<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

// TODO: This script may need to be modified for cases where the source directory
// is outside of the web root.

if (!$_REQUEST['backupid']) {
	Typeframe::Redirect('No backup specified.', Typeframe::CurrentPage()->applicationUri(), 1);
	return;
}
$rsBackup = $db->prepare('SELECT * FROM #__backup WHERE backupid = ?');
$rsBackup->execute($_REQUEST['backupid']);
if ($rsBackup->recordcount() == 0) {
	Typeframe::Redirect('The selected backup does not exist.', Typeframe::CurrentPage()->applicationUri(), 1);
	return;
}
$rowBackup = $rsBackup->fetch_array();
$fullpath = TYPEF_DIR . '/files/secure/backups/' . $rowBackup['filename'];
if (!file_exists($fullpath)) {
	Typeframe::Redirect('The selected backup does not exist.', Typeframe::CurrentPage()->applicationUri(), 1);
	return;
}
$ftp = new FTP();
$ftp->connect(TYPEF_FTP_HOST);
$ftp->login($_SESSION['typef_ftp_user'], $_SESSION['typef_ftp_pass']);
if (file_exists(TYPEF_DIR . '/files/secure/backups/tmp')) {
	system('rm -rf ' . TYPEF_DIR . '/files/secure/backups/tmp');
}
mkdir(TYPEF_DIR . '/files/secure/backups/tmp');
/*
$archExtractor = new ArchiveExtractor();
$archExtractor->extractArchive($fullpath, TYPEF_SOURCE_DIR . '/backups/tmp');
*/
$tmpdir = tempnam(sys_get_temp_dir(), 'phrm_');
unlink($tmpdir);
mkdir($tmpdir);
exec("tar -zxvf {$fullpath} -C {$tmpdir}", $list, $retval);
if ($_POST['cmd'] == 'restore-all') {
	// Restore all files
	/*
	$list = $archExtractor->getTarGzipList($fullpath);
	*/

	foreach ($list as $l) {
		$ftp->put(TYPEF_FTP_ROOT . '/' . substr($l['filename'], strlen(TYPEF_DIR)), $tmpdir . '/' . $l['filename']);
	}
	Typeframe::Redirect('All files restored from backup.', Typeframe::CurrentPage()->applicationUri(), -1);
} elseif ($_POST['cmd'] == 'restore-selected') {
	if ( (!isset($_REQUEST['files'])) || (!is_array($_REQUEST['files'])) ) {
		Typeframe::Redirect('No files selected from backup.', Typeframe::CurrentPage()->applicationUri(), -1);
		return;
	}
	// Restore selected
	foreach ($_REQUEST['files'] as $f) {
		$ftp->put(TYPEF_FTP_ROOT . '/' . substr($f, strlen(TYPEF_DIR)), $tmpdir . '/' . $f);
	}
	Typeframe::Redirect('Selected files have been restored.', Typeframe::CurrentPage()->applicationUri(), -1);
} else {
	Typeframe::Redirect('No action performed.', Typeframe::CurrentPage()->applicationUri(), -1);
}
$ftp->close();
system('rm -rf ' . TYPEF_DIR . '/files/secure/backups/tmp');
?>
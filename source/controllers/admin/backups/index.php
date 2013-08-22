<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

Typeframe::SetPageTemplate('.');
if ( (isset($_POST['cmd'])) && ($_POST['cmd'] == 'backup-delete') ) {
	$rs = $db->prepare('SELECT * FROM #__backup WHERE backupid = ?');
	if ($row = $rs->execute_fetch($_REQUEST['backupid'])) {
		if (file_exists(TYPEF_DIR . '/files/secure/backups/' . $row['filename'])) {
			unlink(TYPEF_DIR . '/files/secure/backups/' . $row['filename']);
		}
		$rs = $db->prepare('DELETE FROM #__backup WHERE backupid = ?');
		$rs->execute($_REQUEST['backupid']);
		Typeframe::Redirect('Backup deleted.', Typeframe::CurrentPage()->applicationUri());
		return;
	} else {
		Typeframe::Redirect('Invalid backup specified.', Typeframe::CurrentPage()->applicationUri(), 1);
		return;
	}
}
$rs = $db->prepare('SELECT * FROM #__backup ORDER BY datecreated DESC');
$rs->execute();
while ($row = $rs->fetch_array()) {
	$pm->addLoop('backups', $row);
}
?>
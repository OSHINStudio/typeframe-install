<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

$pm->setVariable('typef_dir', TYPEF_DIR);
$rs = $db->prepare('SELECT * FROM #__backup WHERE backupid = ?');
$rs->execute($_REQUEST['backupid']);
if ($row = $rs->fetch_array()) {
	$pm->setVariable('backupid', $row['backupid']);
	$row['datecreated'] = $row['datecreated'];
	$pm->addLoop('backup', $row);
	exec('tar -ztf '. TYPEF_DIR . '/files/secure/backups/' . $row['filename'], $list, $result);
	if ($result == 0) {
		foreach ($list as $l) {
			$pm->addLoop('files', array('filename' => $l));
		}
	}
} else {
	Typeframe::Redirect('The requested backup is not available.', TYPEF_WEB_DIR . '/admin/backups', 3);
}
?>
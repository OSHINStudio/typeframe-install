<?php
/**
 * Typeframe Uninstall application
 *
 * admin-side index controller
 */

$dontUninstall = array('kernel', 'users', 'admin', 'install');
foreach (glob(TYPEF_SOURCE_DIR . '/packages/*.xml') as $file)
{
	$xml = simplexml_load_file($file);
	if (!$xml) continue;

	$package = array
	(
		'name'       => "{$xml['name']}",
		'title'      => "{$xml->title[0]}",
		'revision'   => "{$xml['revision']}",
		'version'    => "{$xml['version']}",
		'packagexml' => $file
	);

	if (!in_array($package['name'], $dontUninstall))
		$pm->addLoop('packages', $package);
}
?>

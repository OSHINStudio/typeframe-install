<?php
/**
 * Typeframe Install application
 *
 * admin-side updates controller
 */

// load installed packages
foreach (glob(TYPEF_SOURCE_DIR . '/packages/*.xml') as $file)
{
	$xml = simplexml_load_file($file);
	if (!$xml) continue;

	$package = array
	(
		'name'       => (string)$xml['name'],
		'title'      => (string)$xml->title[0],
		'revision'   => (string)$xml['revision'],
		'version'    => (string)$xml['version'],
		'packagexml' => basename($file)
	);

	// check for updates
	$package['newestversion'] = Install::GetNewestVersion($package['name']);
	if (false !== $package['newestversion'])
	{
		$package['packageurl']    = (TYPEF_PROVIDER . "/download/{$package['name']}-{$package['newestversion']}.tar.gz");
		$package['available']     = true;
		if (Install::EnumerateVersion($package['version']) >= Install::EnumerateVersion($package['newestversion'])) {
			$package['uptodate'] = true;
		}
	}
	else
	{
		$package['newest']    = null;
		$package['available'] = false;
	}

	// check all files in package for customizations
	$package['customized'] = false;
	foreach ($xml->file as $xfile)
	{
		$file = (string)$xfile;
		$installedHash = (string)$xfile['md5'];
		if ($installedHash && file_exists(TYPEF_DIR . "/$file"))
		{
			$currentHash = md5(file_get_contents(TYPEF_DIR . "/$file"));
			if ($installedHash != $currentHash)
			{
				$package['customized'] = true;
				break;
			}
		}
	}

	$pm->addLoop('packages', $package);
}

$pm->sortLoop('packages', 'title');

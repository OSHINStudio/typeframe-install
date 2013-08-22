<?php
/**
 * Typeframe Install application
 *
 * admin-side confirm update controller
 *
 * Present a list of files that have been modified
 * since the last package update. User can select
 * which files to overwrite with the new update.
 */

$pm->setVariableArray($_POST);

$packagexml = $_POST['packagexml'];

if (file_exists(TYPEF_SOURCE_DIR . "/packages/$packagexml"))
{
	$xml = simplexml_load_file(TYPEF_SOURCE_DIR . "/packages/$packagexml");
	foreach ($xml->file as $xfile)
	{
		$file = (string)$xfile;
		$installedHash = (string)$xfile['md5'];
		if ($installedHash && file_exists(TYPEF_DIR . '/' . $file))
		{
			$currentHash = md5(file_get_contents(TYPEF_DIR . '/' . $file));
			if ($currentHash != $installedHash)
				$pm->addLoop('customized', array('file' => $file));
		}
	}
}

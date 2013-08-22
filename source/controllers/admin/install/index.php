<?php
/**
 * Typeframe Install application
 *
 * admin-side index controller
 */

/*
 * TODO: The Installer needs to handle dependencies elegantly. This includes
 * the ability to detect when local copies of a dependency's files have been
 * customized. This process appears to handle the most scenarios:
 * 1. Query the provider for the package's dependencies.
 * 2. If the package or any of its dependencies exist, check the md5 hash
 *    on the files and look for changes. Recurse into deeper dependencies.
 * 3. If local changes exist, request confirmation for which files should be
 *    overwritten.
 */

if ('POST' == $_SERVER['REQUEST_METHOD'])
	// TODO: don't do.php
	Typeframe::IncludeScript('/admin/install/do.php');

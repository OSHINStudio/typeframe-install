<?php
/**
 * Typeframe Uninstall application
 *
 * admin-side "do" controller
 */

$typef_app_dir = (TYPEF_WEB_DIR . '/admin/uninstall');

if ('POST' == $_SERVER['REQUEST_METHOD'] == 'POST')
{
    Typeframe::Redirect('Nothing to do.', $typef_app_dir);
    return;
}

$pm->setVariable('package', $_POST['package']);
Installer::UninstallPackage($_POST['package']);
Typeframe::Redirect('Uninstall complete', $typef_app_dir, 1);
?>
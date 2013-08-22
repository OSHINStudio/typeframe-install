<?php
/**
 * This script was automatically generated. Instead of modifying it directly,
 * the best practice is to modify the corresponding <config> element in the
 * Typeframe registry and regenerate this script with the tfadmin.php tool.
 *
 * The primary purpose of this script is to document the constants defined in
 * the application registry so they are discoverable in IDEs.
 */
 

/**
 * Provider (default: 'http://typeframe.com/packages')
 */
define('TYPEF_PROVIDER', Typeframe::Registry()->getConfigValue('TYPEF_PROVIDER'));

/**
 * License Key (default: '')
 */
define('TYPEF_LICENSE_KEY', Typeframe::Registry()->getConfigValue('TYPEF_LICENSE_KEY'));

/**
 * Automatically install required dependencies (default: '1')
 */
define('INSTALL_REQUIRED_AUTO', Typeframe::Registry()->getConfigValue('INSTALL_REQUIRED_AUTO'));

/**
 * Alert administrators of available updates (default: '1')
 */
define('INSTALL_ALERTS', Typeframe::Registry()->getConfigValue('INSTALL_ALERTS'));

/**
 * Downloader to use for installing packages (default: 'CURL')
 */
define('INSTALL_DOWNLOADER', Typeframe::Registry()->getConfigValue('INSTALL_DOWNLOADER'));

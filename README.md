# typeframe-install

You'll need to set up the htaccess and config file before you can do anything.

The htaccess should look something like:

```
# Phrameworks Configuration
RewriteEngine On
RewriteBase /
RewriteCond %{SCRIPT_FILENAME} -f [OR]
RewriteCond %{SCRIPT_FILENAME} -d
RewriteRule ^(.+) - [PT,L]
RewriteRule ^(.*) index.php%{REQUEST_URI}
```

and the config (typeframe.config.php) should look something like:

```
<?php error_reporting(E_ALL);
ini_set('display_errors', false);
define('TYPEF_DIR', '/absolute/path/to/your/directory');
define('TYPEF_SOURCE_DIR', TYPEF_DIR . '/source');
define('TYPEF_WEB_DIR', '');
define('TYPEF_DB_HOST', 'localhost');
define('TYPEF_DB_USER', 'your_db_user_name');
define('TYPEF_DB_PASS', 'your_db_password');
define('TYPEF_DB_NAME', 'your_db_name');
define('DBI_PREFIX', 'typef_');
define('TYPEF_FTP_HOST', 'localhost');
define('TYPEF_FTP_ROOT', '');
define('TYPEF_FTP_USER', '');
define('TYPEF_FTP_PASS', '');
define('TYPEF_ADMIN_USERGROUPID', '1');
define('TYPEF_DEFAULT_USERGROUPID', '2');
define('TYPEF_HOST', 'something.com');
define('PAGEMILL_CACHE_DIR', TYPEF_DIR . '/files/cache/pm_cache');
```

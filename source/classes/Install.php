<?php
class Install {
	private static $_packageCache = null;
	/**
	 * Install a package from the local directory.
	 * @param string $tarball The path to the package file.
	 * @param array|bool $force An array of files that should be overwritten even if the local copy is customized,
	 * or TRUE if they should always be overwritten, or FALSE if they should never be overwritten.
	 * @param bool $getDependencies If true, look for dependency updates on the provider.
	 * @return array|boolean An array of strings describing the installation or false if the installation failed.
	 */
	public static function Package($tarball, $force = array(), $getDependencies = false) {
		$result = array();
		if (!file_exists($tarball)) {
			throw new Exception("File {$tarball} does not exist");
		}
		$dir = tempnam(sys_get_temp_dir(), 'TYPEF_');
		unlink($dir);
		mkdir($dir);
		exec('tar --directory=' . $dir . ' -zxf ' . $tarball);
		$package = '';
		// Download dependencies if a provider is available
		if (TYPEF_PROVIDER && $getDependencies) {
			if (file_exists("{$dir}/source/packages")) {
				$packages = scandir("{$dir}/source/packages");
				$dependencies = array();
				foreach ($packages as $package) {
					if (substr($package, 0, 1) != '.') {
						$pathinfo = pathinfo($package);
						if ($pathinfo['extension'] == 'xml') {
							$dependencies[] = $pathinfo['filename'];
							self::_GetDependencies($dir, $pathinfo['filename'], $dependencies);
						}
					}
				}
			}
		}
		$ftp = new Typeframe_File();
		$packed = scandir("{$dir}/source/packages");
		foreach ($packed as $pf) {
			$pathinfo = pathinfo($pf);
			if ($pathinfo['extension'] == 'xml') {
				$package = $pathinfo['filename'];
				self::_CopyFiles($ftp, $dir, '', $package, $force);
				// Always copy the package file
				$ftp->copy("{$dir}/source/packages/{$pf}", '/source/packages/' . $pf);
			}
		}
		// Make writeable directories
		$packageFiles = array();
		if (file_exists("{$dir}/source/packages")) {
			$packageFiles = scandir("{$dir}/source/packages");
		}
		foreach ($packageFiles as $pf) {
			if (substr($pf, 0, 1) != '.') {
				if (pathinfo($pf, PATHINFO_EXTENSION) == 'xml') {
					$xml = Pagemill_SimpleXmlElement::LoadFile("{$dir}/source/packages/{$pf}");
					foreach ($xml->updir as $updir) {
						$updir = trim("{$updir}");
						$parts = explode("/", $updir);
						$curDir = '';
						foreach ($parts as $part) {
							if ($part) {
								$curDir .= "/{$part}";
								if (!file_exists(TYPEF_DIR . $curDir)) {
									$ftp->mkdir($curDir);
									$ftp->chmod(0777, $curDir);
								}
							}
						}
					}
				}
			}
		}
		$ftp->close();
		// Update base models if package contained any
		if (file_exists("{$dir}/source/classes/BaseModel")) {
			$modelFiles = scandir("{$dir}/source/classes/BaseModel");
			foreach ($modelFiles as $mf) {
				if (substr($mf, 0, 1) != '.') {
					$pathinfo = pathinfo($mf);
					if ($pathinfo['extension'] == 'php') {
						$cls = "BaseModel_{$pathinfo['filename']}";
						$mod = new $cls();
						$src = Dbi_Source::GetModelSource($mod);
						$src->configureSchema($mod);
					}
				}
			}
		}
	}
	/**
	 * Download package dependencies from the specified package in a temporary isntallation directory.
	 * @param string $directory The temporary directory.
	 * @param string $package The name of the package.
	 * @param array $dependencies An array of package names that have already been downloaded.
	 */
	private static function _GetDependencies($directory, $package, &$dependencies) {
		if (!file_exists("{$directory}/source/packages/{$package}.xml")) {
			$buffer = self::_DownloadToBuffer(TYPEF_PROVIDER . '/download/newest?package=' . $package);
			$tarball = "{$directory}/{$package}.tar.gz";
			file_put_contents($tarball, $buffer);
			exec('tar --directory=' . $directory . ' -zxf ' . $tarball);
			exec('rm ' . $tarball);
		}
		$xml = Pagemill_SimpleXmlElement::LoadFile("{$directory}/source/packages/{$package}.xml");
		foreach ($xml->require as $require) {
			$require = trim("{$require}");
			if (!in_array($require, $dependencies)) {
				$dependencies[] = $require;
				if (file_exists(TYPEF_SOURCE_DIR . "/packages/{$require}.xml")) {
					// Dependency is already installed. Check if a newer version
					// is available.
					$installedXml = Pagemill_SimpleXmlElement::LoadFile(TYPEF_SOURCE_DIR . "/packages/{$require}.xml");
					$latest = self::_DownloadToBuffer(TYPEF_PROVIDER . '/newest?package=' . $require);
					if (self::EnumerateVersion($installedXml['version']) >= self::EnumerateVersion($latest)) {
						continue;
					}
				}
				$buffer = self::_DownloadToBuffer(TYPEF_PROVIDER . '/download/newest?package=' . $require);
				$tarball = "{$directory}/{$require}.tar.gz";
				file_put_contents($tarball, $buffer);
				exec('tar --directory=' . $directory . ' -zxf ' . $tarball, $output);
				exec('rm ' . $tarball);
				self::_GetDependencies($directory, $require, $dependencies);
			}
		}
	}
	/**
	 * Recurisvely copy files and directories via FTP.
	 * @param Typeframe_Ftp $ftp
	 * @param string $src The source directory.
	 * @param string $dst The destination directory.
	 */
	private static function _CopyFiles(Typeframe_File $ftp, $src, $dst, $package, $force) {
		$files = scandir($src);
		$xml = null;
		if (file_exists(TYPEF_SOURCE_DIR . '/packages/' . $package. '.xml')) {
			$xml = simplexml_load_file(TYPEF_SOURCE_DIR . '/packages/' . $package. '.xml');
		}
		foreach ($files as $file) {
			if (substr($file, 0, 1) == '.') {
				continue;
			}
			if (is_file("{$src}/{$file}")) {
				// Copy the file depending on $force rules
				$copy = false;
				if (!file_exists(TYPEF_DIR . "{$dst}/{$file}")) {
					$copy = true;
				} else {
					if ($force === true) {
						$copy = true;
					} else {
						// Check for filename in $force
						if (is_array($force) && in_array(substr("{$dst}/{$file}", 1), $force)) {
							$copy = true;
						} else {
							if ($xml) {
								foreach ($xml->file as $xfile) {
									$fn = trim("{$xfile}");
									if (substr($fn, 0, 1) == '/') $fn = substr($fn, 1);
									$fn = preg_replace('/\/+/', '/', $fn);
									$dn = "{$dst}/{$file}";
									if (substr($dn, 0, 1) == '/') $dn = substr($dn, 1);
									$dn = preg_replace('/\/+/', '/', $dn);
									if ($fn == $dn) {
										$md5 = md5_file(TYPEF_DIR . '/' . $fn);
										if ($xfile['md5'] == $md5) {
											$copy = true;
										}
										break;
									}
								}
							}
						}
					}
				}
				if ($copy) {
					$ftp->copy("{$src}/{$file}", "{$dst}/{$file}");
				}
			} else if (is_dir("{$src}/{$file}")) {
				// TODO: Make the directory if it does not exist
				if (!file_exists(TYPEF_DIR . "/{$dst}/{$file}")) {
					$ftp->mkdir("{$dst}/{$file}");
				}
				self::_CopyFiles($ftp, "{$src}/{$file}", "{$dst}/{$file}", $package, $force);
				// TODO: What if the file exists and is not a directory?
			}
			// TODO: Is there another possibility?
		}
	}
	/**
	 * Download and install a package.
	 * @param string $url The URL to the package.
	 * @param array|bool $force An array of files that should be overwritten even if the local copy is customized,
	 * or TRUE if they should always be overwritten, or FALSE if they should never be overwritten.
	 */
	public static function Download($url, $force = array()) {
		// TODO: Throw exceptions for invalid requests (specifically URLs that
		// do not return a package tarball)
		$buffer = self::_DownloadToBuffer($url);
		$file = tempnam(sys_get_temp_dir(), 'TYPEF_');
		file_put_contents($file, $buffer);
		self::Package($file, $force, true);
	}
	/**
	 * Download the contents of a URL to a buffer.
	 * @param string $url
	 * @return string
	 */
	private static function _DownloadToBuffer($url) {
		// TODO: Throw exceptions for invalid requests
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$buffer = curl_exec($curl);
		curl_close($curl);
		return $buffer;
	}
	/**
	 * Convert a version number into an integer for comparisons.
	 * @param string $version The version number in major.minor.build format.
	 * @return int The converted integer or false if format is invalid.
	 */
	public static function EnumerateVersion($version) {
		$parts = explode('.', $version);
		if (count($parts) != 3) {
			return false;
		}
		return $parts[2] + ($parts[1] * 1000) + ($parts[0] * 1000000);
	}
	public static function GetNewestVersion($packagename) {
		if (is_null(self::$_packageCache)) {
			$buffer = self::_DownloadToBuffer(TYPEF_PROVIDER . '?output=json');
			self::$_packageCache = json_decode($buffer, true);
		}
		if (isset(self::$_packageCache[$packagename])) {
			return self::$_packageCache[$packagename];
		}
		return false;
	}
}

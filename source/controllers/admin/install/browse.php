<?php
/**
 * Typeframe Install application
 *
 * admin-side browse controller
 */

// define URL, cache key
$url      = (TYPEF_PROVIDER . '/xml?license=' . TYPEF_LICENSE_KEY);
$cachekey = ('install-browse-packages-' . md5($url));

// load packages if cached
if (Cache::Get($cachekey))
{
	foreach (Cache::Get($cachekey) as $row)
		$pm->addLoop('packages', $row);
}
// otherwise, fetch packages
else
{
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);

	$cachecontents = array();

	if ($buffer)
	{
		$xml = simplexml_load_string($buffer);
		if ($xml)
		{
			$pm->setVariable('page',          (string)$xml['page']);
			$pm->setVariable('totalpages',    (string)$xml['totalpages']);
			$pm->setVariable('totalpackages', (string)$xml['totalpackages']);

			foreach ($xml->package as $p)
			{
				$cachecontents[] = $row = array
				(
					'packagename'  => (string)$p->name,
					'packagetitle' => (string)$p->title,
					'packageurl'   => (string)$p->url,
					'packagedescr' => (string)$p->description,
					'lastrevision' => (string)$p->lastrevision,
					'lastversion'  => (string)$p->lastversion,
					'lastdate'     => (string)$p->lastdate
				);

				$pm->addLoop('packages', $row);
			}

			Cache::Set($cachekey, $cachecontents);
		}
		else
		{
			// TODO: Handle unreadable XML
		}
	}
	else
	{
		// TODO: Handle empty response
	}
}

// get incoming package type and query; add to template
$packagetype = trim(@$_REQUEST['packagetype']);
$query       = trim(@$_REQUEST['query']);
$pm->setVariable('packagetype', $packagetype);
$pm->setVariable('query',       $query);

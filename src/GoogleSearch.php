<?php

namespace GoogleSearch;


/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */
final class GoogleSearch
{
	/**
	 * @var array
	 */
	private $out = [];

	/**
	 * @var string
	 */
	private $query;

	/**
	 * @var string
	 */
	private $hash;

	/**
	 * @var string
	 */
	private $dataPath;

	/**
	 * @var string
	 */
	private $cacheFile;

	/**
	 * @var array
	 */
	private $cacheMap = [];

	/**
	 * @var array
	 */
	private $cacheData = [];

	/**
	 * @var string
	 */
	private $cacheMapFile;

	/**
	 * Constructor.
	 *
	 * @param string $query
	 */
	public function __construct($query)
	{
		$this->query = $query;
		$this->hash  = sha1(trim(strtolower($query)));
		if (defined("data")) {
			$this->dataPath = realpath(data)."/google_search_data";
		} else {
			$this->dataPath = realpath(".")."/google_search_data";
		}
		is_dir($this->dataPath) or mkdir($this->dataPath);
		is_dir($this->dataPath."/cache") or mkdir($this->dataPath."/cache");
		if (! is_dir($this->dataPath)) {
			throw new \Exception("Cannot create directory {$this->dataPath}", 1);
		}
		if (! is_dir($this->dataPath."/cache")) {
			throw new \Exception("Cannot create directory {$this->dataPath}/cache", 1);
		}
		if (! is_writable($this->dataPath)) {
			throw new \Exception("{$this->dataPath} is not writeable", 1);
		}
		if (! is_writable($this->dataPath."/cache")) {
			throw new \Exception("{$this->dataPath}/cache is not writeable", 1);
		}
		$this->cacheFile = $this->dataPath."/cache/".$this->hash;
		$this->cookieFile = $this->dataPath."/cookies";
		$this->cacheMapFile = $this->dataPath."/map";
		$this->loadMap();
	}

	/**
	 * Load cache map.
	 */
	private function loadMap()
	{
		if (file_exists($this->cacheMapFile)) {
			$this->cacheMap = json_decode(file_get_contents($this->cacheMapFile), true);
			$this->cacheMap = is_array($this->cacheMap) ? $this->cacheMap : [];
		} else {
			$this->cacheMap = [];
		}
	}

	/**
	 * @return bool
	 */
	private function isCached()
	{
		if (isset($this->cacheMap[$this->hash]) && $this->cacheMap[$this->hash] > time()) {
			if (file_exists($this->cacheFile)) {
				$this->cacheData = json_decode(file_get_contents($this->cacheFile), true);
				return is_array($this->cacheData);
			}
		}
		return false;
	}

	/**
	 * @return array
	 */
	private function getCache()
	{
		return $this->cacheData;
	}

	/**
	 * @return array
	 */
	private function onlineSearch()
	{
		$ch = curl_init("https://www.google.co.id/search?q=".urlencode($this->query)."&btnG=&newwindow=1&safe=active");
		curl_setopt_array($ch, 
			[
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_COOKIEJAR	   => $this->cookieFile,
				CURLOPT_COOKIEFILE	   => $this->cookieFile,
				CURLOPT_TIMEOUT		   => 300,
				CURLOPT_CONNECTTIMEOUT => 300,
				CURLOPT_USERAGENT	   => "Opera/9.80 (Android; Opera Mini/19.0.2254/37.9389; U; en) Presto/2.12.423 Version/12.11"
			]
		);
		$out = curl_exec($ch);
		if ($ern = curl_errno($ch)) {
			throw new \Exception("Error ({$ern}): ".curl_error($ch), 1);
		}
		curl_close($ch);
		
		// $out = file_get_contents("a.tmp"); // offline debug
		
		$this->parseOutput($out);
		$this->writeCache();
		return $this->out;
	}

	/**
	 * @param string $out
	 */
	private function parseOutput($out)
	{
		$a = explode("<div class=\"_Z1m\">", $out);
		if (count($a) < 3) {
			$this->out = ["Not Found"];
			return false;
		}
		unset($a[0], $out);
		$results = [];
		foreach ($a as $val) {
			$b = explode("<a class=\"_Olt _bCp\" href=\"/url?q=", $val, 2);
			if (isset($b[1])) {
				$b = explode("\"", $b[1], 2);
				$b = explode("&amp;", $b[0], 2);
				$c = explode("\"_H1m _ees", $val, 2);
				if (isset($c[1])) {
					$c = explode(">", $c[1], 2);
					$c = explode("<", $c[1], 2);
					$d = explode("<div class=\"_H1m\">", $val, 2);
					$d = explode("<", $d[1], 2);
					$d[0] = trim(strip_tags($d[0]));
					$results[] = [
						"url"		 	=> trim(html_entity_decode($b[0], ENT_QUOTES, 'UTF-8')),
						"heading"	 	=> trim(html_entity_decode($c[0], ENT_QUOTES, 'UTF-8')),
						"description"	=> trim(html_entity_decode($d[0], ENT_QUOTES, 'UTF-8')),
					];
				}
			}
		}
		$this->out = $results;
	}

	/**
	 * Write cache
	 */
	private function writeCache()
	{
		$this->cacheMap[$this->hash] = time() + (3600*24*14);
		$handle = fopen($this->cacheFile, "w");
		flock($handle, LOCK_EX);
		fwrite($handle, json_encode($this->out, JSON_UNESCAPED_SLASHES));
		fclose($handle);
		$handle = fopen($this->cacheMapFile, "w");
		fwrite($handle, json_encode($this->cacheMap));
		fclose($handle);
	}

	/**
	 * Exec.
	 */
	public function exec()
	{
		return $this->isCached() ? $this->getCache() : $this->onlineSearch();
	}
}

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
		// file_put_contents("test", $out);die;
		// $a = file_get_contents("test");
		preg_match_all("/(?:<div><a.+href=\"\/url\?q=)(.*)\"(?:.+<div.+role=\"heading\".+>)(.*)(?:<\/div>.+<hr class=\".+\">)(.*)(?:<\/div>)/Usi", $out, $m);
		unset($out);
		if (count($m[0]) < 1) {
			$this->out = ["Not Found"];
		} else {
			$results = [];
			foreach ($m[1] as $k => $v) {
				if (count($v = explode("&amp;", $v, 2)) > 1) {
					$t = explode("\xc2\xb7", $m[3][$k]);
					if (count($t) === 3) {
						$m[3][$k] = $t[1];
					}
					$results[] = [
						"url" => html_entity_decode(urldecode($v[0]), ENT_QUOTES, "UTF-8"),
						"heading" => trim(html_entity_decode($m[2][$k], ENT_QUOTES, "UTF-8")),
						"description" => trim(htmlspecialchars_decode(strip_tags($m[3][$k])))
					];
				}
			}
			$this->out = $results;
		}
	}

	/**
	 * Write cache
	 */
	private function writeCache()
	{
		if ($this->out !== ["Not Found"]) {
			$this->cacheMap[$this->hash] = time() + (3600*24*14);
			$handle = fopen($this->cacheFile, "w");
			flock($handle, LOCK_EX);
			fwrite($handle, json_encode($this->out, JSON_UNESCAPED_SLASHES));
			fclose($handle);
			$handle = fopen($this->cacheMapFile, "w");
			fwrite($handle, json_encode($this->cacheMap));
			fclose($handle);
		}
	}

	/**
	 * Exec.
	 */
	public function exec()
	{
		return $this->isCached() ? $this->getCache() : $this->onlineSearch();
	}
}

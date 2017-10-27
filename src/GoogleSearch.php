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
	private $cookiefile;

	/**
	 * @var string
	 */
	private $errorInfo;

	/**
	 * Constructor.
	 *
	 * @param string $query
	 */
	public function __construct($query)
	{
		$this->query = $query;
		$this->hash  = sha1($query);
		$this->__init__();
	}

	/**
	 * Init data.
	 */
	private function __init__()
	{
		if (defined("data")) {
			$this->dataPath = realpath(data)."/google_search_data";
		} else {
			$this->dataPath = realpath(".")."/google_search_data";
		}
		is_dir($this->dataPath) or mkdir($this->dataPath);
		is_dir($this->dataPath."/cache") or mkdir($this->dataPath."/cache");
		if (! is_dir($this->dataPath."/cache")) {
			throw new \Exception("Cannot create directory {$this->dataPath}/cache!", 1);
		}
		$this->cookiefile = $this->dataPath."/cookiefile";
		return true;
	}

	private function search()
	{
		/*$ch = curl_init("https://www.google.com/search?client=ubuntu&channel=fs&q=".urlencode($this->query)."&ie=utf-8&oe=utf-8");
		curl_setopt_array($ch, 
			[
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_SSL_VERIFYPEER 	=> false,
				CURLOPT_SSL_VERIFYHOST 	=> false,
				CURLOPT_CONNECTTIMEOUT 	=> 15,
				CURLOPT_COOKIEFILE 		=> $this->cookiefile,
				CURLOPT_COOKIEJAR 		=> $this->cookiefile,
				CURLOPT_USERAGENT 		=> "Opera/9.80 (J2ME/MIDP; Opera Mini/4.2/28.3590; U; en) Presto/2.8.119 Version/11.10. 4.2",
				CURLOPT_TIMEOUT			=> 15
			]
		);
		$out = curl_exec($ch);
		$no  = curl_errno($ch) and $out = "Error ({$no}) : ".curl_error($ch);
		file_put_contents("a.tmp", $out);*/
		// return $out;
		return file_get_contents("a.tmp");
	}

	private function parseOutput($out)
	{
		$a = explode("<div class=\"_Z1m\">", $out);
		if (count($a) < 3) {
			$this->errorInfo();
			return false;
		}
		unset($a[0], $a[1]);
		$results = [];
		foreach ($a as $val) {
			// get url
			$b = explode("<a class=\"_Olt _bCp\" href=\"/url?q=", $val, 2);
			if (isset($b[1])) {
				$b = explode("\"", $b[1], 2);
				$b = explode("&amp;", $b[0], 2);
				$c = "";
				$results[] = [
					"url" => html_entity_decode($b[0], ENT_QUOTES, 'UTF-8')
				];
			}
		}
		var_dump($results);
		die;
	}

	/**
	 * Exec
	 * 
	 * @return string
	 */
	public function exec()
	{
		$out = $this->search();
		return $this->errorInfo ? $this->errorInfo : $this->parseOutput($out);
	}
}
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
		$ch = curl_init("https://www.google.com/search?client=ubuntu&channel=fs&q=".urlencode($this->query)."&ie=utf-8&oe=utf-8");
		curl_setopt_array($ch, 
			[
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_SSL_VERIFYPEER 	=> false,
				CURLOPT_SSL_VERIFYHOST 	=> false,
				CURLOPT_CONNECTTIMEOUT 	=> 15,
				CURLOPT_COOKIEFILE 		=> $this->cookiefile,
				CURLOPT_COOKIEJAR 		=> $this->cookiefile,
				CURLOPT_USERAGENT 		=> "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0",
				CURLOPT_TIMEOUT			=> 15
			]
		);
		$out = curl_exec($ch);
		$no  = curl_errno($ch) and $out = "Error ({$no}) : ".curl_error($ch);
		file_put_contents("a.tmp", $out);
		return $out;
	}

	/**
	 * Exec
	 * 
	 * @return string
	 */
	public function exec()
	{
		return $this->search();
	}
}
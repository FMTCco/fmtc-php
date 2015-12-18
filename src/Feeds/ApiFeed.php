<?php
namespace Fmtc\Feeds;

use Illuminate\Database\Capsule\Manager as DB;

class ApiFeed extends Feed
{
	protected $url = '';

	public function fetchUrl($url, $options = [])
	{
		$this->url = $url;

		return $this->fetchFull($options);
	}
}
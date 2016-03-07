<?php
namespace Fmtc\Feeds;

abstract class Feed
{
	protected $key;

	protected $url;

	public function __construct($key)
	{
		$this->key = $key;
	}

	public function fetchFull($options = [])
	{
		$url = $this->buildUrl($options);

		return $this->getJson($url);
	}

	public function fetchIncremental()
	{
		$url = $this->buildUrl(['incremental' => '1']);

		return $this->getJson($url);
	}

	public function processFull()
	{
		ini_set('memory_limit', '1024M');
		ini_set('max_execution_time', 900);

		$json = $this->fetchFull();

		return $this->storeFull($json);
	}

	public function processIncremental()
	{
		ini_set('memory_limit', '1024M');
		ini_set('max_execution_time', 900);

		$json = $this->fetchIncremental();

		return $this->storeIncremental($json);
	}

	protected function buildUrl($options = [])
	{
		$url = $this->url;

		$urlOptions = [
            'key' => $this->key,
        ];

		$options = array_merge($urlOptions, $options);

    	return $url . '?' . http_build_query($options);
	}

	protected function getJson($url)
	{
		ini_set('default_socket_timeout', 6000); // Increase timeout so the larger API calls don't fail.
		return file_get_contents($url);
	}

	protected function formatForInsertion($row, $columns)
	{
		$formattedRow = [];

		foreach ($columns as $column) {
			if (isset($row[$column->column])) {
				// if value is an array
				if (is_array($row[$column->column])) {
					$row[$column->column] = serialize($row[$column->column]);
				}
				$formattedRow[$column->column] = $row[$column->column];
			}
		}

		return $formattedRow;
	}
}
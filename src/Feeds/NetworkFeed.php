<?php
namespace Fmtc\Feeds;

use Illuminate\Database\Capsule\Manager as DB;

class NetworkFeed extends Feed
{
	protected $url = 'http://services.fmtc.co/v2/getNetworks';

	protected function storeFull($json)
	{
		$networks = json_decode($json, true);

		// grab networks from the database
		$dbNetworks = collect(DB::table('networks')->get(['cSlug']))->keyBy('cSlug')->toArray();

		// grab an array of columns in the networks table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_networks\'');

		// set the counters for reporting
		$insertCount = 0;
		$removeCount = 0;

		// walk through the networks from a merchant feed
		$jsonNetworksIds = [];
		foreach($networks as $network) {
			// is the network missing from the database?
			if (! isset($dbNetworks[$network['cSlug']])) {
				// insert it (this is faster than building an insert queue and bulk inserting)
				DB::table('networks')->insert($this->formatForInsertion($network, $columns));
				$insertCount++;
			}

			// collect an array of ids to aid in the remove	queue
			$jsonNetworksIds[] = $network['cSlug'];
		}

		// remove old networks showing up in the database but not in the new merchant feed.
		$removeQueue = array_diff(array_keys($dbNetworks), $jsonNetworksIds);
		$removeCount = count($removeQueue);
		foreach ($removeQueue as $networkId) {
			DB::table('networks')->where('cSlug', $networkId)->delete();
		}

		//---- debugging
		// debug($removeCount . ' removed');
		// debug($insertCount . ' inserted');
		//----- 
		
		return true;
	}

	public function processIncremental()
	{
		return false;
	} 
}
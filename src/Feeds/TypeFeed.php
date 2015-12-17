<?php
namespace Fmtc\Feeds;

use Illuminate\Database\Capsule\Manager as DB;

class TypeFeed extends Feed
{
	protected $url = 'http://services.fmtc.co/v2/getTypes';

	protected function storeFull($json)
	{	
		$types = json_decode($json, true);

		// grab types from the database
		$dbTypes = collect(DB::table('types')->get(['cSlug']))->keyBy('cSlug')->toArray();

		// grab an array of columns in the types table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_types\'');

		// set the counters for reporting
		$insertCount = 0;
		$removeCount = 0;

		// walk through the types from a merchant feed
		$jsonTypesIds = [];
		foreach($types as $type) {
			// is the type missing from the database?
			if (! isset($dbTypes[$type['cSlug']])) {
				// insert it (this is faster than building an insert queue and bulk inserting)
				DB::table('types')->insert($this->formatForInsertion($type, $columns));
				$insertCount++;
			}

			// collect an array of ids to aid in the remove	queue
			$jsonTypesIds[] = $type['cSlug'];
		}

		// remove old types showing up in the database but not in the new merchant feed.
		$removeQueue = array_diff(array_keys($dbTypes), $jsonTypesIds);
		$removeCount = count($removeQueue);
		foreach ($removeQueue as $typeId) {
			DB::table('types')->where('cSlug', $typeId)->delete();
		}

		//---- debugging
		debug($removeCount . ' removed');
		debug($insertCount . ' inserted');
		//----- 
		
		return true;
	}

	public function processIncremental()
	{
		return false;
	} 
}